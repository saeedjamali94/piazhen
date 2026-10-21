<?php
/**
 * FX price sync — a twice-daily background job that updates the Toman price
 * of every product / variation carrying an FX price pair
 * (_pzh_fx_currency + _pzh_fx_price), using the live Netarz sell rate:
 *
 *   toman = fx_value × sell_rate   (rounded to the nearest 1000, filterable)
 *
 * Design (no plugins, WP-Cron only):
 *   - Recurring event 'pzh_fx_price_update' ('twicedaily', anchored at
 *     PZH_FX_SYNC_HOUR o'clock → runs at 03:00 and 15:00 in the WP timezone).
 *   - Each tick processes one batch (PZH_FX_SYNC_BATCH_SIZE items); when the
 *     queue isn't finished, a single follow-up event fires 30 s later
 *     ('pzh_fx_price_update_continue') — page requests stay short and huge
 *     catalogs are walked in small steps instead of one long request.
 *   - A lock option prevents overlapping workers when WP-Cron double-fires.
 *   - Rates are fetched ONCE per run and snapshotted, so every product in a
 *     run is priced from the same rate; a fetch failure retries in 30 min.
 *   - Variable PARENT products are skipped (their prices live in the
 *     variations); after each batch, affected parents are re-synced (min/max
 *     variation prices + transients cleared).
 *   - The sync status (started / finished / updated / failed) is stored in
 *     the 'pzh_fx_price_sync_status' option and shown in
 *     تنظیمات پیاژن → نرخ ارز.
 *
 * The sale price is cleared on sync — the FX value is the product's actual
 * price, so a manual WooCommerce sale would otherwise silently override it.
 *
 * WP-Cron runs only when the site receives traffic. For guaranteed timing in
 * production, add a real crontab hitting wp-cron.php every few minutes:
 *   1-59/5 * * * * wget -q -O /dev/null https://SITE/wp-cron.php?doing_wp_cron
 *
 * Manual run / test:  wp cron event run pzh_fx_price_update
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

// First daily run hour in the WP timezone — the second run is 12 h later
// (03:00 and 15:00 by default).
if (!defined('PZH_FX_SYNC_HOUR')) {
    define('PZH_FX_SYNC_HOUR', 3);
}

// Products processed per cron tick — keeps each request short.
if (!defined('PZH_FX_SYNC_BATCH_SIZE')) {
    define('PZH_FX_SYNC_BATCH_SIZE', 30);
}

// ============================================================================
// Scheduling
// ============================================================================

/**
 * Anchor the twice-daily sync at PZH_FX_SYNC_HOUR o'clock (and +12 h).
 */
function pzh_fx_price_sync_schedule() {
    if (wp_next_scheduled('pzh_fx_price_update')) {
        return;
    }

    $tz    = wp_timezone();
    $now   = time();
    $first = new DateTime('today ' . PZH_FX_SYNC_HOUR . ':00:00', $tz);
    if ($first->getTimestamp() <= $now) {
        $first = new DateTime('tomorrow ' . PZH_FX_SYNC_HOUR . ':00:00', $tz);
    }

    wp_schedule_event($first->getTimestamp(), 'twicedaily', 'pzh_fx_price_update');
}
add_action('init', 'pzh_fx_price_sync_schedule');

/**
 * Drop the cron events and leftover queue when the theme is switched away.
 */
add_action('switch_theme', function () {
    wp_clear_scheduled_hook('pzh_fx_price_update');
    wp_clear_scheduled_hook('pzh_fx_price_update_continue');
    delete_option('pzh_fx_price_sync_queue');
    delete_option('pzh_fx_price_sync_rates');
    delete_option('pzh_fx_price_sync_lock');
});

// ============================================================================
// Queue
// ============================================================================

/**
 * Build the queue: every product / variation post with a currency meta set.
 * Variable parents are skipped — their prices live in the variations.
 *
 * @return array[] array('id' => post_id, 'currency' => ISO code)
 */
function pzh_fx_price_sync_build_queue() {
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT pm.post_id AS id, p.post_type, pm.meta_value AS currency
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_pzh_fx_currency'
           AND pm.meta_value != ''
           AND p.post_type IN ('product', 'product_variation')"
    );

    $queue = array();
    foreach ($rows as $row) {
        $id = (int) $row->id;

        if ($row->post_type === 'product') {
            $product = wc_get_product($id);
            if (!$product || $product->is_type('variable')) {
                continue;
            }
        }

        $queue[] = array('id' => $id, 'currency' => (string) $row->currency);
    }
    return $queue;
}

// ============================================================================
// Item processing
// ============================================================================

/**
 * Update one product / variation price from the FX snapshot.
 *
 * @param array $item  array('id' => ..., 'currency' => ...)
 * @param array $rates Snapshot from PZH_FX::get_rates().
 * @return string 'updated' | 'skipped' | 'failed'
 */
function pzh_fx_price_sync_process_item($item, $rates) {
    $currency = strtoupper((string) $item['currency']);

    if (empty($rates[$currency]['sell']) || floatval($rates[$currency]['sell']) <= 0) {
        return 'skipped'; // rate unavailable for this currency
    }

    $value = floatval(get_post_meta((int) $item['id'], '_pzh_fx_price', true));
    if ($value <= 0) {
        return 'skipped';
    }

    $toman = $value * floatval($rates[$currency]['sell']);
    // Round to the nearest 1000 Toman (filterable).
    $toman = apply_filters('pzh_fx_price_sync_round', round($toman / 1000) * 1000, $toman);

    $product = wc_get_product((int) $item['id']);
    if (!$product) {
        return 'failed';
    }

    try {
        $product->set_regular_price($toman);
        $product->set_sale_price('');
        $product->save();
    } catch (Exception $e) {
        return 'failed';
    }

    return 'updated';
}

// ============================================================================
// Main job
// ============================================================================

/**
 * Process one batch of the sync queue. Attached to both the recurring daily
 * event and the continuation single events.
 */
function pzh_fx_price_update() {
    if (!function_exists('wc_get_product') || !PZH_FX::api_key()) {
        return;
    }

    // Don't overlap with a running worker (WP-Cron can double-fire).
    $lock = (int) get_option('pzh_fx_price_sync_lock', 0);
    if ($lock && (time() - $lock) < 15 * MINUTE_IN_SECONDS) {
        return;
    }
    update_option('pzh_fx_price_sync_lock', time());

    @set_time_limit(0);

    // Build the queue on the first tick of a run.
    $queue = get_option('pzh_fx_price_sync_queue', array());
    if (!is_array($queue)) {
        $queue = array();
    }
    if (empty($queue)) {
        $queue = pzh_fx_price_sync_build_queue();
        update_option('pzh_fx_price_sync_queue', $queue);
        delete_option('pzh_fx_price_sync_rates');
        update_option('pzh_fx_price_sync_status', array(
            'started'  => time(),
            'finished' => 0,
            'total'    => count($queue),
            'updated'  => 0,
            'failed'   => 0,
            'skipped'  => 0,
        ));
    }

    if (empty($queue)) {
        // Nothing to sync — mark the run finished and clean up.
        $status = get_option('pzh_fx_price_sync_status', array());
        $status['finished'] = time();
        update_option('pzh_fx_price_sync_status', $status);
        delete_option('pzh_fx_price_sync_queue');
        delete_option('pzh_fx_price_sync_rates');
        delete_option('pzh_fx_price_sync_lock');
        return;
    }

    // Fetch rates once per run (bypassing the cache) — every product in the
    // run is priced from the same snapshot.
    $rates = get_option('pzh_fx_price_sync_rates', array());
    if (!is_array($rates) || empty($rates)) {
        $codes = array_values(array_unique(array_map(function ($item) {
            return strtoupper($item['currency']);
        }, $queue)));
        $rates = PZH_FX::get_rates($codes, true);

        if (empty($rates)) {
            // API unreachable — keep the queue and retry in 30 minutes.
            delete_option('pzh_fx_price_sync_lock');
            if (!wp_next_scheduled('pzh_fx_price_update_continue')) {
                wp_schedule_single_event(time() + 30 * MINUTE_IN_SECONDS, 'pzh_fx_price_update_continue');
            }
            return;
        }
        update_option('pzh_fx_price_sync_rates', $rates);
    }

    // Process one batch.
    $status = get_option('pzh_fx_price_sync_status', array());
    $batch  = array_splice($queue, 0, PZH_FX_SYNC_BATCH_SIZE);

    $touched_parents = array();
    foreach ($batch as $item) {
        $result = pzh_fx_price_sync_process_item($item, $rates);

        if ($result === 'updated') {
            $status['updated'] = (isset($status['updated']) ? $status['updated'] : 0) + 1;
            if (get_post_type((int) $item['id']) === 'product_variation') {
                $touched_parents[] = (int) wp_get_post_parent_id((int) $item['id']);
            }
        } elseif ($result === 'skipped') {
            $status['skipped'] = (isset($status['skipped']) ? $status['skipped'] : 0) + 1;
        } else {
            $status['failed'] = (isset($status['failed']) ? $status['failed'] : 0) + 1;
        }
    }

    // Re-sync affected variable parents (min/max variation prices + transients).
    foreach (array_unique(array_filter($touched_parents)) as $parent_id) {
        if (class_exists('WC_Product_Variable')) {
            WC_Product_Variable::sync($parent_id);
        }
        wc_delete_product_transients($parent_id);
    }

    update_option('pzh_fx_price_sync_queue', $queue);
    update_option('pzh_fx_price_sync_status', $status);

    if (!empty($queue)) {
        // Queue remains — continue with the next batch in 30 s.
        if (!wp_next_scheduled('pzh_fx_price_update_continue')) {
            wp_schedule_single_event(time() + 30, 'pzh_fx_price_update_continue');
        }
    } else {
        // Run complete.
        $status['finished'] = time();
        update_option('pzh_fx_price_sync_status', $status);
        delete_option('pzh_fx_price_sync_queue');
        delete_option('pzh_fx_price_sync_rates');
    }

    delete_option('pzh_fx_price_sync_lock');
}
add_action('pzh_fx_price_update', 'pzh_fx_price_update');
add_action('pzh_fx_price_update_continue', 'pzh_fx_price_update');
