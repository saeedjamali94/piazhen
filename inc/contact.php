<?php
/**
 * Contact page — page bootstrap, AJAX form submission, admin inbox
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// Page bootstrap (self-heals exactly like the compare page)
// ============================================================================

/**
 * Contact page (page-contact.php template) — resolve its id, creating the
 * page on first run so menu links and pzh_contact_page_url() always have a
 * target. Also self-heals an existing «تماس با ما»/contact page whose
 * template assignment is missing.
 */
function pzh_contact_page_id() {
    $assign_template = function ($page_id) {
        if ($page_id && get_page_template_slug($page_id) !== 'page-contact.php') {
            update_post_meta($page_id, '_wp_page_template', 'page-contact.php');
        }
        update_option('pzh_contact_page_id', (int) $page_id);
        return (int) $page_id;
    };

    $id = (int) get_option('pzh_contact_page_id');
    if ($id && get_post_status($id) === 'publish') {
        return $assign_template($id);
    }

    $pages = get_pages(array('meta_key' => '_wp_page_template', 'meta_value' => 'page-contact.php', 'number' => 1));
    if ($pages) {
        return $assign_template($pages[0]->ID);
    }

    $page = get_page_by_path('contact');
    if ($page) {
        return $assign_template($page->ID);
    }

    $found = new WP_Query(array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'title'          => __('تماس با ما', 'piazhen'),
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ));
    if (!empty($found->posts)) {
        return $assign_template($found->posts[0]);
    }

    $id = wp_insert_post(array(
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => __('تماس با ما', 'piazhen'),
        'post_name'    => 'contact',
        'post_content' => '',
    ));
    if ($id && !is_wp_error($id)) {
        return $assign_template($id);
    }
    return (int) $id;
}
add_action('init', 'pzh_contact_page_id');

/** URL of the contact page. */
function pzh_contact_page_url() {
    $id = pzh_contact_page_id();
    return $id ? get_permalink($id) : home_url('/contact/');
}

// ============================================================================
// Content helpers
// ============================================================================

/**
 * Info-card lines for a contact setting (phones / emails / address / hours).
 * Phone numbers are dash-separated in the settings value; both dashes and
 * newlines become card lines, so each card row is its own string.
 */
function pzh_contact_card_lines($key) {
    $value = (string) pzh_setting('contact', $key);
    $value = preg_replace('/\s*-\s*/', "\n", $value);
    $lines = preg_split('/\r\n|\r|\n/', $value);
    return array_values(array_filter(array_map('trim', $lines)));
}

/**
 * Subject select options (value => label), per the contact.png mockup
 * (the first option «پیگیری سفارشات» is the select's default chosen value).
 */
function pzh_contact_subjects() {
    return apply_filters('pzh_contact_subjects', array(
        'order_track' => __('پیگیری سفارشات', 'piazhen'),
        'new_order'   => __('ثبت سفارش', 'piazhen'),
        'support'     => __('پشتیبانی و گارانتی', 'piazhen'),
        'feedback'    => __('انتقاد و پیشنهاد', 'piazhen'),
        'other'       => __('سایر موارد', 'piazhen'),
    ));
}

/**
 * FAQ accordion items (question / answer). Editable via the
 * 'pzh_contact_faq_items' filter.
 */
function pzh_contact_faq_items() {
    $items = array(
        array(
            'q' => __('هزینه ارسال سفارش‌ها چگونه محاسبه می‌شود؟', 'piazhen'),
            'a' => __('ارسال سفارش‌های بالای ۵ میلیون تومان رایگان است. برای سفارش‌های کمتر، هزینه ارسال بر اساس مقصد و روش ارسال در مرحله تسویه حساب محاسبه و نمایش داده می‌شود.', 'piazhen'),
        ),
        array(
            'q' => __('سفارش من چه زمانی ارسال می‌شود؟', 'piazhen'),
            'a' => __('سفارش‌هایی که تا ساعت ۱۴ ثبت شوند همان روز و بقیه سفارش‌ها روز کاری بعد ارسال می‌شوند. زمان تحویل بسته به مقصد معمولاً بین ۱ تا ۳ روز کاری است.', 'piazhen'),
        ),
        array(
            'q' => __('آیا امکان مرجوع کردن کالا وجود دارد؟', 'piazhen'),
            'a' => __('بله؛ طبق قوانین فروشگاه، تا ۷ روز پس از دریافت کالا در صورت سالم بودن بسته‌بندی و استفاده نشدن از محصول، امکان بازگشت کالا و عودت وجه وجود دارد.', 'piazhen'),
        ),
        array(
            'q' => __('گارانتی محصولات به چه صورت است؟', 'piazhen'),
            'a' => __('تمامی محصولات پیاژن اورجینال هستند و شامل گارانتی معتبر شرکتی (۱۲ تا ۲۴ ماه) می‌شوند. برای استفاده از گارانتی کافی است کارت گارانتی همراه محصول را نگه دارید.', 'piazhen'),
        ),
        array(
            'q' => __('چطور می‌توانم سفارش خود را پیگیری کنم؟', 'piazhen'),
            'a' => __('پس از ارسال سفارش، کد رهگیری مرسوله از طریق پیامک برای شما ارسال می‌شود. همچنین می‌توانید از بخش «سفارش‌ها» در حساب کاربری خود وضعیت سفارش را مشاهده کنید.', 'piazhen'),
        ),
    );
    return apply_filters('pzh_contact_faq_items', $items);
}

// ============================================================================
// AJAX: form submission
// ============================================================================

/**
 * Contact form submission (AJAX). Validates, rate-limits, persists to the
 * admin inbox and notifies by email (best effort) — no plugins.
 */
function pzh_contact_submit() {
    check_ajax_referer('pzh_ajax_nonce', 'nonce');

    // Honeypot — bots fill the hidden field; pretend success silently
    if (!empty($_POST['cnt_website'])) {
        wp_send_json_success(array('message' => __('پیام شما با موفقیت ارسال شد.', 'piazhen')));
    }

    // Rate limit: max 3 submissions per hour per IP
    $ip     = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $rl_key = 'pzh_cnt_rl_' . md5($ip);
    $rl_now = (int) get_transient($rl_key);
    if ($rl_now >= 3) {
        wp_send_json_error(array('message' => __('تعداد درخواست‌های شما بیش از حد مجاز است؛ لطفاً کمی بعد دوباره تلاش کنید.', 'piazhen')));
    }

    $order   = isset($_POST['cnt_order']) ? sanitize_text_field(wp_unslash($_POST['cnt_order'])) : '';
    $name    = isset($_POST['cnt_name']) ? sanitize_text_field(wp_unslash($_POST['cnt_name'])) : '';
    $family  = isset($_POST['cnt_family']) ? sanitize_text_field(wp_unslash($_POST['cnt_family'])) : '';
    $phone   = isset($_POST['cnt_phone']) ? sanitize_text_field(wp_unslash($_POST['cnt_phone'])) : '';
    $email   = isset($_POST['cnt_email']) ? sanitize_email(wp_unslash($_POST['cnt_email'])) : '';
    $subject = isset($_POST['cnt_subject']) ? sanitize_key(wp_unslash($_POST['cnt_subject'])) : '';
    $message = isset($_POST['cnt_message']) ? sanitize_textarea_field(wp_unslash($_POST['cnt_message'])) : '';

    if ($name === '') {
        wp_send_json_error(array('message' => __('نام الزامی است.', 'piazhen')));
    }
    if ($family === '') {
        wp_send_json_error(array('message' => __('نام خانوادگی الزامی است.', 'piazhen')));
    }
    $phone_digits = preg_replace('/[^0-9۰-۹]/u', '', $phone);
    $phone_digits = strtr($phone_digits, array('۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9'));
    if (strlen($phone_digits) < 10) {
        wp_send_json_error(array('message' => __('شماره تماس معتبر نیست. (مثال: 09123456789)', 'piazhen')));
    }
    if (!is_email($email)) {
        wp_send_json_error(array('message' => __('ایمیل وارد شده معتبر نیست.', 'piazhen')));
    }
    if (mb_strlen($message) < 5) {
        wp_send_json_error(array('message' => __('متن پیام خیلی کوتاه است.', 'piazhen')));
    }

    $subjects = pzh_contact_subjects();
    if (!isset($subjects[$subject])) {
        $subject = 'other';
    }

    set_transient($rl_key, $rl_now + 1, HOUR_IN_SECONDS);

    // Persist to the admin inbox (option array like wallet withdrawals —
    // newest first, capped at 500)
    $messages = get_option('pzh_contact_messages', array());
    if (!is_array($messages)) {
        $messages = array();
    }
    array_unshift($messages, array(
        'id'          => uniqid('msg_'),
        'order_code'  => mb_substr($order, 0, 60),
        'name'        => mb_substr($name, 0, 120),
        'family'      => mb_substr($family, 0, 120),
        'phone'       => mb_substr($phone, 0, 40),
        'email'       => $email,
        'subject'     => $subjects[$subject],
        'subject_key' => $subject,
        'message'     => mb_substr($message, 0, 3000),
        'ip'          => $ip,
        'status'      => 'unread',
        'date'        => current_time('mysql'),
    ));
    update_option('pzh_contact_messages', array_slice($messages, 0, 500));

    // Best-effort email to the admin — local mail setups may not send,
    // but the message is always safe in the inbox
    $to = apply_filters('pzh_contact_notify_email', '');
    if (!is_email($to)) {
        $lines = pzh_contact_card_lines('emails');
        $to = !empty($lines[0]) && is_email($lines[0]) ? $lines[0] : get_option('admin_email');
    }
    if (is_email($to)) {
        try {
            $body = sprintf(
                "نام: %s %s\nایمیل: %s\nتلفن: %s\nشماره سفارش: %s\nموضوع: %s\n\n%s",
                $name,
                $family,
                $email,
                $phone !== '' ? $phone : '-',
                $order !== '' ? $order : '-',
                $subjects[$subject],
                $message
            );
            wp_mail($to, sprintf('[%s] %s', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), $subjects[$subject]), $body);
        } catch (\Throwable $e) {
            // Ignore — the inbox has the message
        }
    }

    wp_send_json_success(array(
        'message' => __('پیام شما با موفقیت ارسال شد؛ در سریع‌ترین زمان با شما تماس می‌گیریم.', 'piazhen'),
    ));
}
add_action('wp_ajax_pzh_contact_submit', 'pzh_contact_submit');
add_action('wp_ajax_nopriv_pzh_contact_submit', 'pzh_contact_submit');

// ============================================================================
// Admin inbox
// ============================================================================

/**
 * All stored contact messages, newest first.
 */
function pzh_contact_messages() {
    $messages = get_option('pzh_contact_messages', array());
    return is_array($messages) ? $messages : array();
}

/**
 * Number of unread messages (menu badge).
 */
function pzh_contact_unread_count() {
    $n = 0;
    foreach (pzh_contact_messages() as $m) {
        if (empty($m['status']) || 'unread' === $m['status']) {
            $n++;
        }
    }
    return $n;
}

/**
 * Admin menu: contact messages inbox with an unread badge.
 */
function pzh_admin_contact_messages_menu() {
    $unread     = pzh_contact_unread_count();
    $menu_title = __('پیام‌های تماس', 'piazhen');
    if ($unread) {
        $menu_title .= ' <span class="awaiting-mod count-' . esc_attr($unread) . '"><span class="pending-count">' . pzh_fa_num($unread) . '</span></span>';
    }

    add_menu_page(
        __('پیام‌های تماس', 'piazhen'),
        $menu_title,
        'manage_options',
        'pzh-contact-messages',
        'pzh_admin_contact_messages_render',
        'dashicons-email-alt',
        57
    );
}
add_action('admin_menu', 'pzh_admin_contact_messages_menu');

/**
 * Admin inbox render: bulk actions (read / unread / delete) + detail view.
 */
function pzh_admin_contact_messages_render() {
    $messages = pzh_contact_messages();

    // Bulk actions
    if (isset($_POST['pzh_cnt_action'], $_POST['pzh_cnt_ids']) && check_admin_referer('pzh_contact_messages')) {
        $action  = sanitize_key(wp_unslash($_POST['pzh_cnt_action']));
        $ids     = array_map('sanitize_text_field', wp_unslash($_POST['pzh_cnt_ids']));
        $ids     = array_filter($ids);
        $allowed = array('read', 'unread', 'delete');
        if (in_array($action, $allowed, true) && !empty($ids)) {
            foreach ($messages as $i => $m) {
                if (empty($m['id']) || !in_array($m['id'], $ids, true)) {
                    continue;
                }
                if ('delete' === $action) {
                    unset($messages[$i]);
                } else {
                    $messages[$i]['status'] = $action;
                }
            }
            $messages = array_values($messages);
            update_option('pzh_contact_messages', $messages);
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('انجام شد.', 'piazhen') . '</p></div>';
        }
    }

    // Detail view (opening a message marks it read)
    $view_id = isset($_GET['view']) ? sanitize_text_field(wp_unslash($_GET['view'])) : '';
    $view    = null;
    $view_i  = -1;
    foreach ($messages as $i => $m) {
        if (!empty($m['id']) && $m['id'] === $view_id) {
            $view   = $m;
            $view_i = $i;
            break;
        }
    }
    if ($view && (empty($view['status']) || 'unread' === $view['status'])) {
        $messages[$view_i]['status'] = 'read';
        update_option('pzh_contact_messages', $messages);
    }

    $list_url = admin_url('admin.php?page=pzh-contact-messages');
    ?>
    <div class="wrap">
        <h1><?php _e('پیام‌های تماس', 'piazhen'); ?></h1>

        <?php if ($view): ?>
            <?php
            $rows = array(
                array(__('نام', 'piazhen'), trim(($view['name'] ?? '') . ' ' . ($view['family'] ?? ''))),
                array(__('ایمیل', 'piazhen'), $view['email'] ?? ''),
                array(__('تلفن', 'piazhen'), $view['phone'] ?? ''),
                array(__('شماره سفارش', 'piazhen'), $view['order_code'] ?? ''),
                array(__('موضوع', 'piazhen'), $view['subject'] ?? ''),
                array(__('تاریخ', 'piazhen'), $view['date'] ?? ''),
                array(__('آی‌پی', 'piazhen'), $view['ip'] ?? ''),
            );
            ?>
            <p><a href="<?php echo esc_url($list_url); ?>">&larr; <?php _e('بازگشت به فهرست پیام‌ها', 'piazhen'); ?></a></p>

            <div class="card" style="max-width: 860px; margin-top: 12px;">
                <h2 style="margin: 0 0 12px;"><?php echo esc_html($view['subject'] ?? ''); ?></h2>
                <table class="form-table" role="presentation" style="margin-top: 0;">
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <th style="width: 120px;"><?php echo esc_html($row[0]); ?></th>
                            <td><?php echo esc_html($row[1] ?: '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th style="width: 120px;"><?php _e('متن پیام', 'piazhen'); ?></th>
                        <td><?php echo nl2br(esc_html($view['message'] ?? '')); ?></td>
                    </tr>
                </table>
            </div>
        <?php elseif (empty($messages)): ?>
            <p><?php _e('هنوز پیامی ثبت نشده است.', 'piazhen'); ?></p>
        <?php else: ?>
            <form method="post" action="">
                <?php wp_nonce_field('pzh_contact_messages'); ?>
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="pzh_cnt_action">
                            <option value="-1"><?php _e('عملیات گروهی', 'piazhen'); ?></option>
                            <option value="read"><?php _e('علامت‌گذاری به‌عنوان خوانده‌شده', 'piazhen'); ?></option>
                            <option value="unread"><?php _e('علامت‌گذاری به‌عنوان خوانده‌نشده', 'piazhen'); ?></option>
                            <option value="delete"><?php _e('حذف', 'piazhen'); ?></option>
                        </select>
                        <button type="submit" class="button action"><?php _e('اعمال', 'piazhen'); ?></button>
                    </div>
                </div>

                <table class="widefat striped" style="margin-top: 8px;">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column"><input type="checkbox" id="pzh-cnt-cb-all"></td>
                            <th><?php _e('وضعیت', 'piazhen'); ?></th>
                            <th><?php _e('تاریخ', 'piazhen'); ?></th>
                            <th><?php _e('نام', 'piazhen'); ?></th>
                            <th><?php _e('ایمیل', 'piazhen'); ?></th>
                            <th><?php _e('تلفن', 'piazhen'); ?></th>
                            <th><?php _e('شماره سفارش', 'piazhen'); ?></th>
                            <th><?php _e('موضوع', 'piazhen'); ?></th>
                            <th><?php _e('پیام', 'piazhen'); ?></th>
                            <th><?php _e('عملیات', 'piazhen'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $m): ?>
                            <?php $unread = (empty($m['status']) || 'unread' === $m['status']); ?>
                            <tr style="<?php echo $unread ? 'font-weight: 600;' : ''; ?>">
                                <th class="check-column">
                                    <input type="checkbox" name="pzh_cnt_ids[]" value="<?php echo esc_attr($m['id'] ?? ''); ?>">
                                </th>
                                <td>
                                    <?php if ($unread): ?>
                                        <span style="color: #d15418;">● <?php _e('خوانده‌نشده', 'piazhen'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #828282;">● <?php _e('خوانده‌شده', 'piazhen'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td dir="ltr"><?php echo esc_html($m['date'] ?? ''); ?></td>
                                <td><?php echo esc_html(trim(($m['name'] ?? '') . ' ' . ($m['family'] ?? ''))); ?></td>
                                <td dir="ltr"><?php echo esc_html($m['email'] ?? ''); ?></td>
                                <td dir="ltr"><?php echo esc_html($m['phone'] ?? ''); ?></td>
                                <td dir="ltr"><?php echo esc_html($m['order_code'] ?? ''); ?></td>
                                <td><?php echo esc_html($m['subject'] ?? ''); ?></td>
                                <td><?php echo esc_html(wp_trim_words($m['message'] ?? '', 12)); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg('view', rawurlencode($m['id'] ?? ''), $list_url)); ?>">
                                        <?php _e('مشاهده', 'piazhen'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            <script>
            (function () {
                var all = document.getElementById('pzh-cnt-cb-all');
                if (!all) return;
                all.addEventListener('change', function () {
                    document.querySelectorAll('input[name="pzh_cnt_ids[]"]').forEach(function (cb) {
                        cb.checked = all.checked;
                    });
                });
            })();
            </script>
        <?php endif; ?>
    </div>
    <?php
}
