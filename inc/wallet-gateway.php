<?php
/**
 * Piazhen Wallet Payment Gateway — پرداخت با کیف پول
 *
 * Pays the order from the user's wallet balance (user meta:
 * pzh_wallet_balance / pzh_wallet_transactions, stored in the same unit as
 * the order total).
 *
 * When the balance does not cover the order total, the payment splits:
 * the order is reduced by the wallet's share (a negative fee) and the
 * customer pays only the remainder through the Zarinpal gateway. The wallet
 * is charged only AFTER the gateway verifies its payment
 * (pzh_wallet_split_deduct_on_payment_complete on woocommerce_payment_complete),
 * so a failed or cancelled gateway attempt never touches the wallet.
 *
 * Correctness guarantees:
 *  - Only offered to logged-in users with a positive balance.
 *  - Pessimistic locking: the balance row is locked with
 *    SELECT ... FOR UPDATE inside a DB transaction, so concurrent payments
 *    can never overspend the wallet.
 *  - Idempotency: the order is marked _pzh_wallet_paid before the commit;
 *    a retry (double submit, duplicate callback) returns success without
 *    charging the wallet twice. The split discount is applied only once
 *    (_pzh_wallet_pending_amount) and the deferred deduction only once
 *    (_pzh_wallet_split_deducted).
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_PZH_Wallet_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'pzh_wallet';
        $this->icon               = '';
        $this->has_fields         = false;
        $this->method_title       = __('پرداخت با کیف پول', 'piazhen');
        $this->method_description = __('پرداخت سفارش از موجودی کیف پول کاربر. فقط برای کاربران واردشده با موجودی مثبت نمایش داده می‌شود.', 'piazhen');
        $this->supports           = array('products');

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option('title', __('پرداخت با کیف پول', 'piazhen'));
        $this->description = $this->get_option('description', '');
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('فعال/غیرفعال', 'piazhen'),
                'type'    => 'checkbox',
                'label'   => __('فعال‌سازی پرداخت با کیف پول', 'piazhen'),
                'default' => 'yes',
            ),
            'title' => array(
                'title'   => __('عنوان', 'piazhen'),
                'type'    => 'text',
                'default' => __('پرداخت با کیف پول', 'piazhen'),
            ),
            'description' => array(
                'title'   => __('توضیحات', 'piazhen'),
                'type'    => 'textarea',
                'default' => '',
            ),
        );
    }

    /**
     * Visible only for logged-in users with a positive wallet balance.
     */
    public function is_available() {
        if (!parent::is_available()) {
            return false;
        }
        if (!is_user_logged_in()) {
            return false;
        }
        return pzh_wallet_balance(get_current_user_id()) > 0;
    }

    /**
     * Append the current balance to the checkout label, plus a split hint
     * when the balance covers only part of the order.
     */
    public function get_title() {
        $title = parent::get_title();
        if (is_checkout() && is_user_logged_in()) {
            $balance = pzh_wallet_balance(get_current_user_id());
            $title  .= ' <span class="pzh-wallet-balance">(' . sprintf(
                /* translators: %s = wallet balance */
                __('موجودی: %s تومان', 'piazhen'),
                pzh_fa_num(number_format($balance))
            ) . ')</span>';

            if ($balance > 0 && WC()->cart && !WC()->cart->is_empty()) {
                $total = intval(round(floatval(WC()->cart->get_total('edit'))));
                if ($total > $balance) {
                    $title .= ' <span class="pzh-wallet-split-hint">(' . sprintf(
                        /* translators: %s = the amount paid via the online gateway */
                        __('مابه‌التفاوت %s تومان از درگاه پرداخت می‌شود', 'piazhen'),
                        pzh_fa_num(number_format($total - $balance))
                    ) . ')</span>';
                }
            }
        }
        return $title;
    }

    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wptexturize(esc_html($this->description)));
        }
    }

    /**
     * Pay the order from the wallet.
     *
     * Critical section: BEGIN → lock balance row (FOR UPDATE) → idempotency
     * re-check → balance check → deduct → record → mark order → COMMIT.
     */
    public function process_payment($order_id) {
        global $wpdb;

        $order   = wc_get_order($order_id);
        $user_id = $order ? intval($order->get_user_id()) : 0;
        if (!$order || !$user_id) {
            throw new Exception(__('سفارش نامعتبر است. پرداخت با کیف پول فقط برای کاربران واردشده ممکن است.', 'piazhen'));
        }

        $amount = intval(round(floatval($order->get_total())));

        // Idempotency fast path — already paid, nothing else to do
        if ($order->get_meta('_pzh_wallet_paid')) {
            return $this->wallet_success($order);
        }

        // Ensure the balance row exists so FOR UPDATE always has a row to lock
        add_user_meta($user_id, 'pzh_wallet_balance', 0, true);

        $wpdb->query('START TRANSACTION');

        try {
            // Pessimistic lock on the wallet balance row
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT umeta_id, meta_value FROM {$wpdb->usermeta}
                 WHERE user_id = %d AND meta_key = 'pzh_wallet_balance' FOR UPDATE",
                $user_id
            ));
            if (!$row) {
                throw new Exception(__('خطا در خواندن موجودی کیف پول.', 'piazhen'));
            }

            $balance = intval($row->meta_value);

            // Idempotency re-check under the lock (a retry racing the first
            // payment). The site runs HPOS, so re-read the order meta directly
            // from the data store instead of querying postmeta — the object
            // cache may still hold stale values from before a racing request
            // committed.
            $order->read_meta_data(true);
            $paid           = $order->get_meta('_pzh_wallet_paid');
            $pending        = intval($order->get_meta('_pzh_wallet_pending_amount'));
            $split_deducted = $order->get_meta('_pzh_wallet_split_deducted');

            if ($paid === '1') {
                $wpdb->query('COMMIT');
                return $this->wallet_success($order);
            }

            // If a split was applied on an earlier attempt and its wallet share
            // is still pending, this payment must cover that share too (the
            // order total no longer includes it).
            $to_charge = $amount;
            if ($pending > 0 && $split_deducted !== '1') {
                $to_charge += $pending;
            }

            // Split payment: the balance does not cover what is owed. The order
            // is reduced by the wallet's share and the remainder goes through
            // the Zarinpal gateway. The wallet is NOT charged here — only after
            // the gateway verifies its payment (see the woocommerce_payment_complete
            // hook at the bottom of this file).
            if ($to_charge > 0 && $balance < $to_charge) {
                if ($balance <= 0) {
                    $wpdb->query('ROLLBACK');
                    throw new Exception(sprintf(
                        __('موجودی کیف پول کافی نیست. موجودی: %s تومان — مبلغ سفارش: %s تومان', 'piazhen'),
                        pzh_fa_num(number_format($balance)),
                        pzh_fa_num(number_format($amount))
                    ));
                }
                if (!$this->split_gateway_available()) {
                    $wpdb->query('ROLLBACK');
                    throw new Exception(__('درگاه پرداخت آنلاین در دسترس نیست. لطفاً روش پرداخت دیگری را انتخاب کنید.', 'piazhen'));
                }
                // Apply under the balance lock so a double submit can never add
                // the wallet discount twice.
                $this->apply_wallet_split_to_order($order, $amount, $balance, $pending);
                $wpdb->query('COMMIT');
                return $this->split_gateway_redirect($order);
            }

            $new_balance = $to_charge > 0 ? $balance - $to_charge : $balance;

            // Deduct — the balance row is already locked by us
            update_user_meta($user_id, 'pzh_wallet_balance', $new_balance);

            // Transaction record, idempotent by a unique per-order id
            $tx     = get_user_meta($user_id, 'pzh_wallet_transactions', true);
            $tx     = is_array($tx) ? $tx : array();
            $tx_id  = 'order-' . $order_id;
            $exists = false;
            foreach ($tx as $entry) {
                if (isset($entry['id']) && $entry['id'] === $tx_id) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $tx[] = array(
                    'id'     => $tx_id,
                    'title'  => sprintf(__('پرداخت سفارش #%s', 'piazhen'), $order->get_order_number()),
                    'desc'   => __('پرداخت با کیف پول', 'piazhen'),
                    'amount' => $to_charge,
                    'status' => 'success',
                    'date'   => current_time('mysql'),
                );
                update_user_meta($user_id, 'pzh_wallet_transactions', $tx);
            }

            // Idempotency markers + note — committed atomically with the deduction
            $order->update_meta_data('_pzh_wallet_paid', '1');
            $order->update_meta_data('_pzh_wallet_paid_amount', (string) $to_charge);
            $order->add_order_note(sprintf(__('پرداخت از کیف پول به مبلغ %s تومان انجام شد.', 'piazhen'), number_format($to_charge)));

            // Covering the pending share of an earlier split? Mark it settled so
            // the payment-complete hook does not deduct it a second time.
            if ($pending > 0 && $split_deducted !== '1') {
                $order->update_meta_data('_pzh_wallet_split_deducted', '1');
                $order->add_order_note(sprintf(
                    __('سهم کیف پول از پرداخت ترکیبی قبلی (%s تومان) همراه این پرداخت تسویه شد.', 'piazhen'),
                    number_format($pending)
                ));
            }
            $order->save();

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            // A rolled-back attempt may have touched the user-meta object cache —
            // drop it so the next read goes to the DB.
            wp_cache_delete($user_id, 'user_meta');
            throw $e;
        }

        // Payment is done — complete the order outside the transaction
        $order->payment_complete();

        return $this->wallet_success($order);
    }

    /**
     * Standard success result (redirects to the thank-you page).
     */
    private function wallet_success($order) {
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    /**
     * Whether the online gateway (Zarinpal) is available to take the
     * remainder of a split payment.
     */
    private function split_gateway_available() {
        $gateways = WC()->payment_gateways()->get_available_payment_gateways();
        return !empty($gateways['WC_ZPal']);
    }

    /**
     * Reduce the order by the wallet's share (negative fee) and record the
     * pending amount — idempotent: an earlier attempt reuses the stored split.
     *
     * @param int $pending Fresh value of _pzh_wallet_pending_amount read under
     *                     the balance lock (0 when no split was applied yet).
     */
    private function apply_wallet_split_to_order($order, $amount, $balance, $pending = 0) {
        if ($pending > 0) {
            return; // already applied on an earlier attempt
        }

        $wallet_amount  = $balance;
        $gateway_amount = $amount - $balance;

        $fee = new WC_Order_Item_Fee();
        $fee->set_name(__('پرداخت از کیف پول', 'piazhen'));
        $fee->set_tax_status('none');
        $fee->set_total(-$wallet_amount);
        $order->add_item($fee);

        $order->update_meta_data('_pzh_wallet_pending_amount', (string) $wallet_amount);
        $order->calculate_totals();
        $order->save();

        $order->add_order_note(sprintf(
            __('پرداخت ترکیبی: %s تومان از کیف پول + %s تومان از درگاه (کسر از کیف پول پس از تأیید درگاه).', 'piazhen'),
            number_format($wallet_amount),
            number_format($gateway_amount)
        ));
    }

    /**
     * Hand the remainder over to the Zarinpal gateway. The receipt page
     * fires woocommerce_receipt_WC_ZPal, which redirects the customer
     * straight to the gateway for the reduced order total.
     */
    private function split_gateway_redirect($order) {
        $order->set_payment_method('WC_ZPal');
        $order->save();

        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        );
    }
}

// ============================================================================
// Split payment: deferred wallet deduction
// ============================================================================

/**
 * Deduct the wallet's share of a split payment — only after the online
 * gateway has verified its part (woocommerce_payment_complete).
 *
 * A failed or cancelled gateway attempt never reaches this hook, so the
 * wallet is only ever charged for orders whose gateway part was verified.
 * Same pessimistic-lock + idempotency pattern as the full-wallet path.
 */
function pzh_wallet_split_deduct_on_payment_complete($order_id, $transaction_id = '') {
    global $wpdb;

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $pending = intval($order->get_meta('_pzh_wallet_pending_amount'));
    if ($pending <= 0 || $order->get_meta('_pzh_wallet_split_deducted')) {
        return;
    }

    $user_id = intval($order->get_customer_id());
    if (!$user_id) {
        return;
    }

    // Ensure the balance row exists so FOR UPDATE always has a row to lock
    add_user_meta($user_id, 'pzh_wallet_balance', 0, true);

    $wpdb->query('START TRANSACTION');

    try {
        // Pessimistic lock on the wallet balance row
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT umeta_id, meta_value FROM {$wpdb->usermeta}
             WHERE user_id = %d AND meta_key = 'pzh_wallet_balance' FOR UPDATE",
            $user_id
        ));
        if (!$row) {
            throw new Exception(__('خطا در خواندن موجودی کیف پول.', 'piazhen'));
        }

        // Idempotency re-check under the lock (e.g. duplicate gateway callback).
        // HPOS-aware fresh read from the data store, not postmeta.
        $order->read_meta_data(true);
        if ($order->get_meta('_pzh_wallet_split_deducted') === '1') {
            $wpdb->query('COMMIT');
            return;
        }

        $balance = intval($row->meta_value);

        // Deduct up to the pending share — never below zero (the user may have
        // spent part of the wallet while the gateway payment was pending).
        $deduct = min($balance, $pending);

        update_user_meta($user_id, 'pzh_wallet_balance', $balance - $deduct);

        // Transaction record, idempotent by a unique per-order id
        $tx     = get_user_meta($user_id, 'pzh_wallet_transactions', true);
        $tx     = is_array($tx) ? $tx : array();
        $tx_id  = 'order-' . $order_id;
        $exists = false;
        foreach ($tx as $entry) {
            if (isset($entry['id']) && $entry['id'] === $tx_id) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $tx[] = array(
                'id'     => $tx_id,
                'title'  => sprintf(__('پرداخت سفارش #%s', 'piazhen'), $order->get_order_number()),
                'desc'   => __('سهم کیف پول — پس از تأیید پرداخت درگاه', 'piazhen'),
                'amount' => $deduct,
                'status' => 'success',
                'date'   => current_time('mysql'),
            );
            update_user_meta($user_id, 'pzh_wallet_transactions', $tx);
        }

        $order->update_meta_data('_pzh_wallet_split_deducted', '1');
        $order->update_meta_data('_pzh_wallet_paid_amount', (string) $deduct);
        $order->add_order_note(sprintf(
            __('سهم کیف پول (%s تومان) پس از تأیید پرداخت درگاه کسر شد.', 'piazhen'),
            number_format($deduct)
        ));
        if ($deduct < $pending) {
            $order->add_order_note(sprintf(
                __('توجه: موجودی کیف پول هنگام تأیید درگاه کمتر از سهم ثبت‌شده بود — ثبت‌شده: %s تومان، کسرشده: %s تومان.', 'piazhen'),
                number_format($pending),
                number_format($deduct)
            ));
        }
        $order->save();

        $wpdb->query('COMMIT');
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_cache_delete($user_id, 'user_meta');

        // Never break the gateway's own payment-complete flow — log the
        // failure and leave a note on the order for the admin to fix manually.
        if (function_exists('wc_get_logger')) {
            wc_get_logger()->error(
                sprintf('[piazhen wallet] split deduction failed for order #%s: %s', $order_id, $e->getMessage()),
                array('source' => 'pzh-wallet')
            );
        }
        $order->add_order_note(sprintf(
            __('خطا در کسر سهم کیف پول (%s تومان) پس از پرداخت درگاه: %s', 'piazhen'),
            number_format($pending),
            $e->getMessage()
        ));
    }
}
add_action('woocommerce_payment_complete', 'pzh_wallet_split_deduct_on_payment_complete', 10, 2);

/**
 * WooCommerce instantiates payment gateways lazily, but the order receipt
 * page (checkout/order-pay/{id}/?key=…) fires woocommerce_receipt_{gateway}
 * to hand the customer over to the gateway. Make sure the gateways are
 * constructed before that template renders, so the receipt action always
 * has a handler (used by both the Zarinpal gateway itself and the wallet
 * split flow).
 */
function pzh_ensure_gateways_on_order_receipt() {
    if (is_admin()) {
        return;
    }
    global $wp;
    if (!empty($wp->query_vars['order-pay']) && isset($_GET['key']) && !isset($_GET['pay_for_order'])) {
        WC()->payment_gateways();
    }
}
add_action('wp', 'pzh_ensure_gateways_on_order_receipt', 5);

// ============================================================================
// Registration
// ============================================================================

function pzh_wallet_gateway_register($gateways) {
    $gateways[] = 'WC_PZH_Wallet_Gateway';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'pzh_wallet_gateway_register');

/**
 * Wallet first in the list whenever it is available, so a user with balance
 * lands on wallet payment by default (others keep their usual default).
 *
 * Two parts, because WC orders gateways by the woocommerce_gateway_order
 * option (position keys), not by the registration order:
 *  1. put pzh_wallet at position 0 in that option;
 *  2. also unshift it in the load list (used when the option has no entry).
 */
function pzh_wallet_gateway_order_first() {
    $ordering = (array) get_option('woocommerce_gateway_order');
    // Insert at position 0 only when missing — never override a manual
    // reorder done in WooCommerce settings.
    if (!isset($ordering['pzh_wallet'])) {
        $ordering['pzh_wallet'] = 0;
        update_option('woocommerce_gateway_order', $ordering);
    }
}
add_action('init', 'pzh_wallet_gateway_order_first');

function pzh_wallet_gateway_first($gateways) {
    $wallet = 'WC_PZH_Wallet_Gateway';
    $pos    = array_search($wallet, $gateways, true);
    if ($pos !== false) {
        unset($gateways[$pos]);
        array_unshift($gateways, $wallet);
    }
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'pzh_wallet_gateway_first', 20);

/**
 * Payment methods are part of the design again — render them inside the
 * order review (above the totals + CTA) instead of the hidden block in the
 * form column. The default hook at priority 20 was removed by the theme;
 * re-add it before the review table at priority 5.
 */
function pzh_wallet_gateway_show_payment_in_review() {
    add_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 5);
}
add_action('init', 'pzh_wallet_gateway_show_payment_in_review');
