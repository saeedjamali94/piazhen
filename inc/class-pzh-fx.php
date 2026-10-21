<?php
/**
 * Netarz FX API client — live currency rates for the shop.
 *
 * API docs: https://netarz.ir/docs/fx/quickstart
 *
 *   GET {base}/rates?codes=USD,EUR,AED
 *   Authorization: Bearer fx-ntz-v1-…
 *
 * Response:
 *   { data: [{ code, buy, sell, change_24h_percent }],
 *     meta: { as_of, is_delayed, delayed_minutes } }
 *
 * The API key is stored in the database (تنظیمات پیاژن → نرخ ارز,
 * option pzh_settings_fx.api_key) — never hard-coded. Responses are
 * transient-cached for pzh_settings_fx.cache_ttl minutes so page renders
 * never wait on the API; failed requests are cached empty for 60 seconds
 * so the API is not hammered during outages.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

class PZH_FX {

    const DEFAULT_BASE_URL = 'https://netarz.ir/api/fx/v1';

    /**
     * API key from settings (never hard-coded).
     *
     * @return string Empty when not configured in admin.
     */
    public static function api_key() {
        $key = pzh_setting('fx', 'api_key');
        return is_string($key) ? trim($key) : '';
    }

    /**
     * API base URL from settings.
     *
     * @return string
     */
    public static function base_url() {
        $url = pzh_setting('fx', 'base_url');
        if (!$url || !is_string($url)) {
            $url = self::DEFAULT_BASE_URL;
        }
        return untrailingslashit($url);
    }

    /**
     * Cache lifetime in minutes from settings (default 10).
     *
     * @return int
     */
    public static function cache_ttl() {
        $ttl = absint(pzh_setting('fx', 'cache_ttl'));
        return $ttl ? $ttl * MINUTE_IN_SECONDS : 10 * MINUTE_IN_SECONDS;
    }

    /**
     * Normalize a list of currency codes to unique uppercase ISO codes.
     *
     * @param string|string[] $codes Comma-separated string or array.
     * @return string[]
     */
    private static function normalize_codes($codes) {
        if (is_string($codes)) {
            $codes = explode(',', $codes);
        }
        $out = array();
        foreach ((array) $codes as $code) {
            $code = strtoupper(trim((string) $code));
            if (preg_match('/^[A-Z]{3}$/', $code)) {
                $out[] = $code;
            }
        }
        return array_values(array_unique($out));
    }

    /**
     * Fetch rates for the given codes from the API (uncached).
     *
     * @param string[] $codes Normalized ISO codes.
     * @return array|null array('data' => rows, 'meta' => meta) or null on failure.
     */
    private static function fetch($codes) {
        $response = wp_remote_get(
            add_query_arg('codes', implode(',', $codes), self::base_url() . '/rates'),
            array(
                'timeout' => 10,
                'headers' => array(
                    'Authorization' => 'Bearer ' . self::api_key(),
                    'Accept'        => 'application/json',
                ),
            )
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body) || isset($body['error'])) {
            return null;
        }

        return array(
            'data' => isset($body['data']) && is_array($body['data']) ? $body['data'] : array(),
            'meta' => isset($body['meta']) && is_array($body['meta']) ? $body['meta'] : array(),
        );
    }

    /**
     * Get rates for one or more currencies.
     *
     * @param string|string[] $codes   Comma-separated string or array (e.g. 'USD,EUR').
     * @param bool            $refresh Skip the transient and hit the API.
     * @return array Rate rows keyed by code:
     *               code => array(code, buy, sell, change_24h_percent).
     *               Empty on failure / missing key.
     */
    public static function get_rates($codes, $refresh = false) {
        $codes = self::normalize_codes($codes);
        if (empty($codes) || !self::api_key()) {
            return array();
        }

        $transient = 'pzh_fx_rates_' . md5(implode(',', $codes));

        if (!$refresh) {
            $cached = get_transient($transient);
            if (is_array($cached) && isset($cached['data'])) {
                return $cached['data'];
            }
        }

        $result = self::fetch($codes);

        // Don't hammer the API during an outage — cache the failure briefly.
        $ttl = $result ? self::cache_ttl() : MINUTE_IN_SECONDS;
        if (!$result) {
            $result = array('data' => array(), 'meta' => array());
        }
        set_transient($transient, $result, $ttl);

        return $result['data'];
    }

    /**
     * Get the rate row for a single currency.
     *
     * @param string $code ISO code (e.g. 'USD').
     * @param bool   $refresh Skip the cache.
     * @return array|null array(code, buy, sell, change_24h_percent) or null.
     */
    public static function get_rate($code, $refresh = false) {
        $rates = self::get_rates($code, $refresh);
        $code  = strtoupper(trim((string) $code));
        return isset($rates[$code]) ? $rates[$code] : null;
    }

    /**
     * Metadata of the last fetched response (as_of, is_delayed, delayed_minutes).
     *
     * @param string|string[] $codes Same code set passed to get_rates().
     * @return array Empty when no successful fetch happened yet.
     */
    public static function get_meta($codes) {
        $codes     = self::normalize_codes($codes);
        $transient = 'pzh_fx_rates_' . md5(implode(',', $codes));
        $cached    = get_transient($transient);
        if (is_array($cached) && isset($cached['meta'])) {
            return $cached['meta'];
        }
        return array();
    }

    /**
     * Convert an amount between two currencies (cross rate via sell prices).
     *
     * @param float  $amount
     * @param string $from ISO code of the source currency.
     * @param string $to   ISO code of the target currency.
     * @return float|null Converted amount, or null when a rate is unavailable.
     */
    public static function convert($amount, $from, $to) {
        $amount = floatval($amount);
        if ($amount <= 0) {
            return null;
        }

        $from = strtoupper(trim((string) $from));
        $to   = strtoupper(trim((string) $to));
        if ($from === $to) {
            return $amount;
        }

        $rates = self::get_rates(array($from, $to));
        $sell  = function ($code) use ($rates) {
            return isset($rates[$code]['sell']) ? floatval($rates[$code]['sell']) : 0.0;
        };

        $from_sell = $sell($from);
        $to_sell   = $sell($to);
        if ($from_sell <= 0 || $to_sell <= 0) {
            return null;
        }

        return $amount * $from_sell / $to_sell;
    }

    /**
     * Convert an amount of a foreign currency to IRR/Toman (sell rate).
     *
     * @param float  $amount Amount in the foreign currency.
     * @param string $code   ISO code of the foreign currency.
     * @return float|null Amount in Toman, or null when the rate is unavailable.
     */
    public static function to_irr($amount, $code) {
        $rate = self::get_rate($code);
        if (!$rate || floatval($rate['sell']) <= 0) {
            return null;
        }
        return floatval($amount) * floatval($rate['sell']);
    }

    /**
     * Convert an amount of IRR/Toman to a foreign currency (sell rate).
     *
     * @param float  $amount_irr Amount in Toman.
     * @param string $code       ISO code of the target currency.
     * @return float|null Amount in the foreign currency, or null when unavailable.
     */
    public static function from_irr($amount_irr, $code) {
        $rate = self::get_rate($code);
        if (!$rate || floatval($rate['sell']) <= 0) {
            return null;
        }
        return floatval($amount_irr) / floatval($rate['sell']);
    }
}
