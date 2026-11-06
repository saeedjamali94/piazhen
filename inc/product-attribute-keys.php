<?php
/**
 * One-time migration: canonical variation attribute keys.
 *
 * WooCommerce addresses variation attributes as attribute_ + sanitize_title()
 * of the attribute name — for Persian names that canonical form is
 * URL-encoded (attribute_pa_%d9%85%d8%af%d9%84 for pa_مدل). Variations whose
 * metas were imported with raw (decoded) keys don't line up with the
 * parent-derived canonical keys, so wc_get_product_variation_attributes()
 * drops their values and matching fails («ترکیب انتخاب شده موجود نیست»).
 *
 * This rewrites every non-canonical variation attribute meta key once to the
 * sanitize_title form (idempotent — canonical keys are left untouched) and
 * flushes the product transients so min/max price caches rebuild.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

function pzh_migrate_variation_attribute_keys() {
    // Drop the flag of an earlier (wrong-direction) migration attempt.
    delete_option('pzh_attr_keys_migrated');

    if (get_option('pzh_attr_keys_canonicalized')) {
        return;
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT meta_id, post_id, meta_key FROM {$wpdb->postmeta}
         WHERE post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product_variation')
           AND meta_key LIKE 'attribute_%'"
    );

    $moved = 0;
    foreach ($rows as $row) {
        $canonical = 'attribute_' . sanitize_title(substr($row->meta_key, 10));
        if ($canonical === $row->meta_key) {
            continue;
        }

        // Canonical key already exists? Drop this duplicate instead.
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $row->post_id,
            $canonical
        ));
        if ($exists) {
            $wpdb->delete($wpdb->postmeta, array('meta_id' => $row->meta_id));
            continue;
        }

        $wpdb->update(
            $wpdb->postmeta,
            array('meta_key' => $canonical),
            array('meta_id' => $row->meta_id)
        );
        $moved++;
    }

    update_option('pzh_attr_keys_canonicalized', 1);

    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients();
    }

    if ($moved && function_exists('wc_get_logger')) {
        wc_get_logger()->info(
            sprintf('[piazhen] migrated %d variation attribute keys to the canonical form', $moved),
            array('source' => 'pzh-attribute-keys')
        );
    }
}
add_action('init', 'pzh_migrate_variation_attribute_keys');
