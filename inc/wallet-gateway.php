<?php
/**
 * Piazhen Wallet Payment Gateway — پرداخت با کیف پول
 *
 * Pays the order from the user's wallet balance (user meta:
 * pzh_wallet_balance / pzh_wallet_transactions, stored in the same unit as
 * the order total).
 *
 * Correctness guarantees:
 *  - Only offered to logged-in users with a positive balance.
 *  - Pessimistic locking: the balance row is locked with
 *    SELECT ... FOR UPDATE inside a DB transaction, so concurrent payments
 *    can never overspend the wallet.
 *  - Idempotency: the order is marked _pzh_wallet_paid before the commit;
 *    a retry (double submit, duplicate callback) returns success without
 *    charging the wallet twice.
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
     * Append the current balance to the checkout label.
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

            // Idempotency re-check under the lock (a retry racing the first payment)
            $paid = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta}
                 WHERE post_id = %d AND meta_key = '_pzh_wallet_paid'",
                $order_id
            ));
            if ($paid === '1') {
                $wpdb->query('COMMIT');
                return $this->wallet_success($order);
            }

            if ($amount > 0 && $balance < $amount) {
                $wpdb->query('ROLLBACK');
                throw new Exception(sprintf(
                    __('موجودی کیف پول کافی نیست. موجودی: %s تومان — مبلغ سفارش: %s تومان', 'piazhen'),
                    pzh_fa_num(number_format($balance)),
                    pzh_fa_num(number_format($amount))
                ));
            }

            $new_balance = $amount > 0 ? $balance - $amount : $balance;

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
                    'amount' => $amount,
                    'status' => 'success',
                    'date'   => current_time('mysql'),
                );
                update_user_meta($user_id, 'pzh_wallet_transactions', $tx);
            }

            // Idempotency markers + note — committed atomically with the deduction
            $order->update_meta_data('_pzh_wallet_paid', '1');
            $order->update_meta_data('_pzh_wallet_paid_amount', (string) $amount);
            $order->add_order_note(sprintf(__('پرداخت از کیف پول به مبلغ %s تومان انجام شد.', 'piazhen'), number_format($amount)));
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
}

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
