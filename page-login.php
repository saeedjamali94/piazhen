<?php
/**
 * Template Name: ورود و ثبت‌نام
 *
 * Login / Registration with SMS OTP (Melipayamak) — custom AJAX, no plugins.
 * Steps: phone → OTP code → profile completion (new users) → success.
 * Design per login.png: full-bleed split — cover image 60.5% + #121212 panel
 * holding an airy white card (no site header/footer on this page).
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

// Already logged in? Go to the account page.
if (is_user_logged_in()) {
    wp_safe_redirect(pzh_auth_redirect_url());
    exit;
}

$redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class('pzh-auth-body'); ?>>
<?php wp_body_open(); ?>

<main class="pzh-auth-page" data-auth-page="1">

    <!-- Cover side (60.5% width per the mockup, full-bleed) -->
    <div class="pzh-auth-cover" style="background-image:url('<?php echo esc_url(pzh_login_cover_url()); ?>');">
        <img class="pzh-auth-cover__logo"
             src="<?php echo esc_url(pzh_logo_url()); ?>"
             alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    </div>

    <!-- Form panel (39.5% width, flat #121212) -->
    <div class="pzh-auth-panel">
        <div class="pzh-auth-card">

            <!-- ============ Step 1: Phone ============ -->
            <section class="auth-step auth-step--phone" data-step="phone">
                <h2 class="auth-title"><?php _e('ورود / ثبت نام', 'piazhen'); ?></h2>

                <p class="auth-subtitle">
                    <?php _e('شماره موبایل خود را وارد کنید', 'piazhen'); ?>
                </p>

                <input type="tel" id="auth-phone" class="auth-phone-field"
                       placeholder="<?php esc_attr_e('۰۹', 'piazhen'); ?>" maxlength="13" inputmode="numeric" autocomplete="tel" dir="ltr">

                <div class="auth-error" id="auth-error-phone"></div>

                <button type="button" class="auth-submit" id="auth-send">
                    <?php _e('ادامه', 'piazhen'); ?>
                </button>
            </section>

            <!-- ============ Step 2: OTP ============ -->
            <section class="auth-step" data-step="otp" hidden>
                <h2 class="auth-title"><?php _e('کد تایید را وارد کنید', 'piazhen'); ?></h2>
                <p class="auth-subtitle">
                    <?php _e('کد ۵ رقمی پیامک شده به شماره', 'piazhen'); ?>
                    <strong id="auth-otp-phone" class="auth-phone-display" dir="ltr"></strong>
                </p>

                <div class="auth-otp-boxes" dir="ltr">
                    <input class="auth-otp-input" type="tel" maxlength="1" inputmode="numeric" aria-label="1">
                    <input class="auth-otp-input" type="tel" maxlength="1" inputmode="numeric" aria-label="2">
                    <input class="auth-otp-input" type="tel" maxlength="1" inputmode="numeric" aria-label="3">
                    <input class="auth-otp-input" type="tel" maxlength="1" inputmode="numeric" aria-label="4">
                    <input class="auth-otp-input" type="tel" maxlength="1" inputmode="numeric" aria-label="5">
                </div>

                <div class="auth-error" id="auth-error-otp"></div>

                <div class="auth-otp-timer">
                    <?php _e('ارسال مجدد کد تا', 'piazhen'); ?>
                    <span id="auth-timer" dir="ltr">02:00</span>
                </div>

                <button type="button" class="auth-resend" id="auth-resend" disabled>
                    <?php _e('ارسال مجدد کد', 'piazhen'); ?>
                </button>

                <button type="button" class="auth-edit-phone" id="auth-edit-phone">
                    <?php _e('ویرایش شماره موبایل', 'piazhen'); ?>
                </button>

                <button type="button" class="auth-submit" id="auth-verify" disabled>
                    <?php _e('تایید کد', 'piazhen'); ?>
                </button>
            </section>

            <!-- ============ Step 3: Profile completion ============ -->
            <section class="auth-step" data-step="register" hidden>
                <h2 class="auth-title"><?php _e('تکمیل اطلاعات', 'piazhen'); ?></h2>
                <p class="auth-subtitle">
                    <?php _e('برای ساخت حساب کاربری، اطلاعات زیر را تکمیل کنید.', 'piazhen'); ?>
                </p>

                <div class="auth-fields">
                    <div class="auth-field-row d-flex gap-2">
                        <div class="auth-field">
                            <label for="auth-first-name"><?php _e('نام', 'piazhen'); ?> <span class="required">*</span></label>
                            <input type="text" id="auth-first-name" class="auth-input" placeholder="<?php _e('نام', 'piazhen'); ?>">
                        </div>
                        <div class="auth-field">
                            <label for="auth-last-name"><?php _e('نام خانوادگی', 'piazhen'); ?> <span class="required">*</span></label>
                            <input type="text" id="auth-last-name" class="auth-input" placeholder="<?php _e('نام خانوادگی', 'piazhen'); ?>">
                        </div>
                    </div>
                    <div class="auth-field">
                        <label for="auth-email"><?php _e('ایمیل', 'piazhen'); ?> <span class="optional">(<?php _e('اختیاری', 'piazhen'); ?>)</span></label>
                        <input type="email" id="auth-email" class="auth-input" placeholder="example@email.com" dir="ltr">
                    </div>
                    <div class="auth-field">
                        <label for="auth-password"><?php _e('رمز عبور', 'piazhen'); ?> <span class="required">*</span></label>
                        <input type="password" id="auth-password" class="auth-input" placeholder="<?php _e('حداقل ۶ کاراکتر', 'piazhen'); ?>">
                    </div>
                    <div class="auth-field">
                        <label for="auth-password-confirm"><?php _e('تکرار رمز عبور', 'piazhen'); ?> <span class="required">*</span></label>
                        <input type="password" id="auth-password-confirm" class="auth-input" placeholder="<?php _e('تکرار رمز عبور', 'piazhen'); ?>">
                    </div>
                </div>

                <div class="auth-error" id="auth-error-register"></div>

                <button type="button" class="auth-submit" id="auth-register-btn">
                    <?php _e('ثبت‌نام و ورود', 'piazhen'); ?>
                </button>
            </section>

            <!-- ============ Step 4: Success ============ -->
            <section class="auth-step" data-step="success" hidden>
                <div class="auth-success-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h2 class="auth-title"><?php _e('خوش آمدید!', 'piazhen'); ?></h2>
                <p class="auth-subtitle auth-subtitle--center" id="auth-success-text">
                    <?php _e('ورود با موفقیت انجام شد. در حال انتقال به حساب کاربری...', 'piazhen'); ?>
                </p>
                <a href="<?php echo esc_url(pzh_auth_redirect_url()); ?>" class="auth-submit auth-submit--link">
                    <?php _e('ورود به حساب کاربری', 'piazhen'); ?>
                </a>
            </section>

            <!-- Logo pinned to the card bottom (per the mockup) -->
            <div class="pzh-auth-card__logo">
                <a href="<?php echo esc_url(SITE_URL); ?>">
                    <img src="<?php echo esc_url(pzh_logo_url()); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                </a>
            </div>

            <input type="hidden" id="auth-phone-stored" value="">
            <input type="hidden" id="auth-code-stored" value="">
            <input type="hidden" id="auth-redirect" value="<?php echo esc_attr($redirect_to); ?>">
        </div>
    </div>
</main>

<?php wp_footer(); ?>
</body>
</html>
