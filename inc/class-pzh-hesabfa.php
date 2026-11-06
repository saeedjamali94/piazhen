<?php
/**
 * Hesabfa API client — product stock (quantity) lookup.
 *
 * Endpoint (per https://hesabfa.com/help/api/Item):
 *   POST https://api.hesabfa.com/v1/item/GetQuantity
 *   Body (flat JSON): { apiKey, loginToken, warehouseCode?, codes?: string[] }
 *   Response: { Success, ErrorCode, ErrorMessage, Result: [{ Code, Tag,
 *               Quantity, Barcode, SellPrice, ProductCode, … }] }
 *   When `codes` is omitted all items are returned; omitting warehouseCode
 *   returns the total stock.
 *
 * Credentials live in the DB (تنظیمات پیاژن → حسابفا,
 * option pzh_settings_hesabfa) — never hard-coded. Quantity responses are
 * transient-cached (default 5 minutes) so page renders never wait on the
 * API; failed requests are cached empty for 60 seconds so the API is not
 * hammered during outages.
 *
 * WooCommerce mapping: a product's Hesabfa item code is its SKU, or an
 * explicit _pzh_hesabfa_code meta when set.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

class PZH_Hesabfa {

    const ENDPOINT = 'https://api.hesabfa.com/v1/item/GetQuantity';

    /**
     * API key from settings.
     *
     * @return string Empty when not configured.
     */
    public static function api_key() {
        $key = pzh_setting('hesabfa', 'api_key');
        return is_string($key) ? trim($key) : '';
    }

    /**
     * Login token from settings.
     *
     * @return string Empty when not configured.
     */
    public static function login_token() {
        $token = pzh_setting('hesabfa', 'login_token');
        return is_string($token) ? trim($token) : '';
    }

    /**
     * Cache lifetime in minutes from settings (default 5).
     *
     * @return int Seconds.
     */
    public static function cache_ttl() {
        $ttl = absint(pzh_setting('hesabfa', 'cache_ttl'));
        return $ttl ? $ttl * MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS;
    }

    /**
     * Fetch current quantities for the given item codes.
     *
     * @param string[] $codes   Hesabfa item codes (کد کالا).
     * @param bool     $refresh Skip the transient and hit the API.
     * @return array code => quantity (float). Empty on failure / missing credentials.
     */
    public static function get_quantities($codes, $refresh = false) {
        $codes = array_values(array_unique(array_filter(array_map('strval', (array) $codes))));
        if (empty($codes) || !self::api_key() || !self::login_token()) {
            return array();
        }

        $transient = 'pzh_hesabfa_qty_' . md5(implode('|', $codes));

        if (!$refresh) {
            $cached = get_transient($transient);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $response = wp_remote_post(self::ENDPOINT, array(
            'timeout' => 10,
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => wp_json_encode(array(
                'apiKey'     => self::api_key(),
                'loginToken' => self::login_token(),
                'codes'      => $codes,
            )),
        ));

        $fail = function () use ($transient) {
            // Don't hammer the API during an outage — cache the failure briefly.
            set_transient($transient, array(), MINUTE_IN_SECONDS);
            return array();
        };

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return $fail();
        }

        $json = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($json) || empty($json['Success']) || !isset($json['Result'])) {
            return $fail();
        }

        $out = array();
        foreach ((array) $json['Result'] as $row) {
            if (is_array($row) && isset($row['Code'], $row['Quantity'])) {
                $out[(string) $row['Code']] = (float) $row['Quantity'];
            }
        }

        set_transient($transient, $out, self::cache_ttl());
        return $out;
    }

    /**
     * Hesabfa item code for a WooCommerce product / variation.
     *
     * An explicit _pzh_hesabfa_code meta wins; otherwise the SKU is used.
     *
     * @param int $product_id Product or variation ID.
     * @return string Empty when the product is not mapped to a Hesabfa item.
     */
    public static function product_code($product_id) {
        $product_id = intval($product_id);
        if (!$product_id) {
            return '';
        }
        $code = (string) get_post_meta($product_id, '_pzh_hesabfa_code', true);
        if ($code !== '') {
            return trim($code);
        }
        return trim((string) get_post_meta($product_id, '_sku', true));
    }

    /**
     * Stock of one product / variation.
     *
     * @param int  $product_id Product or variation ID.
     * @param bool $refresh    Skip the quantity cache.
     * @return array|null array('quantity' => float, 'in_stock' => bool), or
     *                    null when unmapped / the item wasn't found.
     */
    public static function product_stock($product_id, $refresh = false) {
        $code = self::product_code($product_id);
        if ($code === '') {
            return null;
        }
        $quantities = self::get_quantities(array($code), $refresh);
        if (!array_key_exists($code, $quantities)) {
            return null;
        }
        $quantity = $quantities[$code];
        return array('quantity' => $quantity, 'in_stock' => $quantity > 0);
    }

    /**
     * Batched stock lookup for many products/variations — one API request
     * per unique code set.
     *
     * @param int[] $product_ids
     * @param bool  $refresh Skip the quantity cache.
     * @return array product_id => array('quantity' => float, 'in_stock' => bool)
     */
    public static function stocks($product_ids, $refresh = false) {
        $by_code = array();
        foreach ((array) $product_ids as $id) {
            $id   = intval($id);
            $code = self::product_code($id);
            if ($code === '') {
                continue;
            }
            if (!isset($by_code[$code])) {
                $by_code[$code] = array();
            }
            $by_code[$code][] = $id;
        }
        if (empty($by_code)) {
            return array();
        }

        $quantities = self::get_quantities(array_keys($by_code), $refresh);

        $out = array();
        foreach ($by_code as $code => $ids) {
            if (!array_key_exists($code, $quantities)) {
                continue;
            }
            $quantity = $quantities[$code];
            foreach ($ids as $id) {
                $out[$id] = array('quantity' => $quantity, 'in_stock' => $quantity > 0);
            }
        }
        return $out;
    }
}
