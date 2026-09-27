<?php
/**
 * Instagram feed — pulls the newest posts of the shop's Instagram account
 * and caches them in transients.
 *
 * Instagram serves public profile media as JSON without auth at:
 *   https://www.instagram.com/api/v1/users/web_profile_info/?username=…
 * The response shape varies between endpoints/versions, so both the
 * web_profile_info (`data.user.media.nodes`) and the GraphQL-ish
 * (`data.user.edge_owner_to_timeline_media.edges`) layouts are parsed.
 *
 * The fetch never runs inline during a page render (Instagram can be slow or
 * unreachable from some hosts — e.g. Iranian networks — and the homepage
 * must not wait on it):
 *   - pzh_instagram_feed() only reads the cache; when it's missing/expired it
 *     schedules a background refresh via a WP-Cron single event (spawned with
 *     a 0.01s timeout, so page loads aren't delayed).
 *   - Fresh cache lives 6 hours, a stale backup 7 days (served while a
 *     refresh is pending or failing), so the section never goes dark.
 *   - The settings page "purge" button fetches synchronously so the admin
 *     gets immediate feedback (post count or a connection error).
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Instagram username from settings (defaults to 'piazhen').
 */
function pzh_instagram_username() {
    $username = pzh_setting('instagram', 'instagram_username');
    if (!$username) {
        $username = 'piazhen';
    }
    return str_replace('@', '', trim($username, " \t\n\r\0\x0B/"));
}

/**
 * Instagram profile URL (from the contact settings, falls back to the
 * username-based URL).
 */
function pzh_instagram_profile_url() {
    $url = pzh_setting('contact', 'instagram');
    return $url ? $url : 'https://instagram.com/' . pzh_instagram_username();
}

/**
 * Get the cached Instagram posts — never blocks the page.
 *
 * When the fresh cache is missing or expired, a background refresh is
 * scheduled and the stale copy (or an empty array) is returned immediately.
 *
 * @param int $limit Number of posts to return (max 12).
 * @return array[]   Each item: url, image, caption.
 */
function pzh_instagram_feed($limit = 6) {
    $limit  = max(1, min(12, (int) $limit));
    $cached = get_transient('pzh_instagram_feed');

    if (is_array($cached)) {
        return array_slice($cached, 0, $limit);
    }

    // No fresh cache — refresh in the background, don't block the page.
    // Retry quickly when a stale copy exists, slowly when we have nothing.
    if (!wp_next_scheduled('pzh_instagram_refresh_event')) {
        $stale = get_transient('pzh_instagram_feed_stale');
        wp_schedule_single_event(time() + (is_array($stale) ? 10 : 15 * MINUTE_IN_SECONDS), 'pzh_instagram_refresh_event');
    }

    $stale = get_transient('pzh_instagram_feed_stale');
    return is_array($stale) ? array_slice($stale, 0, $limit) : array();
}

/**
 * Cron callback: fetch the feed and refresh the caches.
 */
add_action('pzh_instagram_refresh_event', 'pzh_instagram_refresh_cache');

/**
 * Fetch the feed from Instagram and store it (fresh + stale transients).
 */
function pzh_instagram_refresh_cache() {
    $posts = pzh_instagram_fetch_remote();
    if (!empty($posts)) {
        set_transient('pzh_instagram_feed', $posts, 6 * HOUR_IN_SECONDS);
        set_transient('pzh_instagram_feed_stale', $posts, 7 * DAY_IN_SECONDS);
    }
}

/**
 * Fetch the newest posts directly from Instagram.
 *
 * @return array[] Posts (url, image, caption); empty on failure.
 */
function pzh_instagram_fetch_remote() {
    $response = wp_remote_get(
        'https://www.instagram.com/api/v1/users/web_profile_info/?username=' . rawurlencode(pzh_instagram_username()),
        array(
            'timeout'     => 10,
            'redirection' => 5,
            'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'headers'     => array(
                'Accept'           => 'application/json, text/plain, */*',
                'Accept-Language'  => 'en-US,en;q=0.9',
                'X-IG-App-ID'      => '936619743392459',
                'X-Requested-With' => 'XMLHttpRequest',
            ),
        )
    );

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return array();
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return is_array($body) ? pzh_instagram_parse_posts($body) : array();
}

/**
 * Turn a web_profile_info response body into an array of posts.
 *
 * @param array $body Decoded JSON response.
 * @return array[]
 */
function pzh_instagram_parse_posts($body) {
    $posts = array();
    $nodes = array();

    // Shape 1: web_profile_info — data.user.media.nodes[].node
    if (isset($body['data']['user']['media']['nodes']) && is_array($body['data']['user']['media']['nodes'])) {
        $nodes = $body['data']['user']['media']['nodes'];
    }
    // Shape 2: GraphQL edges — data.user.edge_owner_to_timeline_media.edges[].node
    if (empty($nodes) && isset($body['data']['user']['edge_owner_to_timeline_media']['edges']) && is_array($body['data']['user']['edge_owner_to_timeline_media']['edges'])) {
        foreach ($body['data']['user']['edge_owner_to_timeline_media']['edges'] as $edge) {
            if (isset($edge['node']) && is_array($edge['node'])) {
                $nodes[] = $edge['node'];
            }
        }
    }

    foreach ($nodes as $node) {
        if (!is_array($node)) {
            continue;
        }

        $shortcode = isset($node['shortcode']) ? $node['shortcode'] : '';
        $image     = isset($node['display_url']) ? $node['display_url'] : '';
        if (!$image && isset($node['thumbnail_src'])) {
            $image = $node['thumbnail_src']; // videos
        }
        if (!$image || !$shortcode) {
            continue;
        }

        $caption = '';
        if (isset($node['caption']['text'])) {
            $caption = $node['caption']['text'];
        } elseif (isset($node['edge_media_to_caption']['edges'][0]['node']['text'])) {
            $caption = $node['edge_media_to_caption']['edges'][0]['node']['text'];
        }

        $posts[] = array(
            'url'     => 'https://www.instagram.com/p/' . rawurlencode($shortcode) . '/',
            'image'   => $image,
            'caption' => wp_strip_all_tags($caption),
        );

        if (count($posts) >= 12) {
            break;
        }
    }

    return $posts;
}

/**
 * Clear the Instagram feed caches (fresh + stale).
 */
function pzh_instagram_clear_cache() {
    delete_transient('pzh_instagram_feed');
    delete_transient('pzh_instagram_feed_stale');
}
