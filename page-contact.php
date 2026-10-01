<?php
/**
 * Template Name: تماس با ما
 *
 * Contact page per contact.png: welcome heading + store intro, two orange
 * branch lines, wholesale copy, centered «تماس با ما» divider, the white
 * «راه‌های ارتباطی» panel (آدرس / شماره تماس / فضای مجازی) beside a map,
 * an orange «پاسخگوی شما هستیم» heading with rules, and the 2-column ticket
 * form (AJAX into the admin inbox). Uses the shared site header/footer.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$phones        = pzh_phones();
$address_lines = pzh_contact_card_lines('address');
$social        = pzh_social_links();
$subjects      = pzh_contact_subjects();
?>

<main class="pzh-cnt">
    <div class="pzh-cnt__wrap">

        <!-- ============ H1 + store intro ============ -->
        <h1 class="pzh-cnt__h1"><?php _e('به فروشگاه اینترنتی و حضوری پیازن خوش آمدید!', 'piazhen'); ?></h1>

        <p class="pzh-cnt__body pzh-cnt__p">
            <?php _e('فروشگاه پیازن با ۱۳ سال تجربه درخشان در بازار بزرگ تهران، به عنوان یکی از معتبرترین و پرطرفدارترین مراکز فروش لوازم آرایشی و بهداشتی شناخته می‌شود. از روز اول تاسیس، هدف ما ارائه بهترین و باکیفیت‌ترین محصولات آرایشی و بهداشتی به مشتریان عزیزمان بوده است. در طی این سال‌ها، تخصص ما در ارائه انواع لوازم برقی آرایشی و آرایشگاهی باعث شده است تا به انتخاب اول بسیاری از حرفه‌ای‌های این حوزه تبدیل شویم.', 'piazhen'); ?>
        </p>

        <p class="pzh-cnt__body pzh-cnt__p">
            <?php _e('قدرت و اعتبار ما در این است که همواره جنس اصلی و اورجینال را به دست مشتریانمان می‌رسانیم. به عنوان وارد کننده مستقیم، ما تضمین می‌کنیم که تمامی محصولات موجود در فروشگاه پیازن از بالاترین کیفیت برخوردار هستند و اصالت کالاها را تأیید می‌کنیم. این تعهد به کیفیت و اصالت، یکی از دلایل اصلی اعتماد و وفاداری مشتریان به فروشگاه پیازن است.', 'piazhen'); ?>
        </p>

        <!-- ============ Branches ============ -->
        <h2 class="pzh-cnt__h2 pzh-cnt__h2--gap-70"><?php _e('دو شعبه فعال در تهران', 'piazhen'); ?></h2>

        <p class="pzh-cnt__branch"><?php _e('شعبه اول: بازار بزرگ تهران، پاساژ منصور', 'piazhen'); ?></p>
        <p class="pzh-cnt__branch"><?php _e('شعبه دوم: جمهوری، پاساژ علاءالدین ۴', 'piazhen'); ?></p>

        <!-- ============ Wholesale ============ -->
        <h2 class="pzh-cnt__h2 pzh-cnt__h2--gap-50"><?php _e('فروش جزئی، فروش عمده', 'piazhen'); ?></h2>

        <p class="pzh-cnt__body pzh-cnt__p">
            <?php _e('فروشگاه پیازن علاوه بر فروش جزئی، به فروش عمده و همکاری با سایر همکاران تجاری نیز می‌پردازد. در این زمینه، تخصص و تجربه ویژه‌ای داریم و همواره تلاش کرده‌ایم تا نیازهای همکاران تجاری خود را با بهترین شرایط و قیمت‌ها تامین کنیم. راه‌اندازی این سایت نیز به منظور حذف واسطه‌ها و تسهیل فرآیند خرید برای مشتریان عزیزمان بوده است. هدف ما این است که شما بتوانید به راحتی و با اطمینان خاطر، محصولات مورد نیاز خود را مستقیماً از وارد کننده تهیه کنید.', 'piazhen'); ?>
        </p>

        <p class="pzh-cnt__body pzh-cnt__p">
            <?php _e('ما در پیازن به ارائه خدمات عالی و تجربه خریدی دلپذیر به مشتریان خود متعهد هستیم. تیم حرفه‌ای ما همیشه آماده پاسخگویی به سوالات شما و ارائه مشاوره‌های تخصصی در زمینه انتخاب محصولات مناسب است. همچنین، ما به‌روزترین و جدیدترین محصولات آرایشی و بهداشتی را به فروشگاه خود اضافه می‌کنیم تا همیشه از بهترین‌ها بهره‌مند شوید.', 'piazhen'); ?>
        </p>

        <!-- ============ Centered divider «تماس با ما» ============ -->
        <div class="pzh-cnt__divider" aria-hidden="true">
            <span class="pzh-cnt__divider-line pzh-cnt__divider-line--r"></span>
            <span class="pzh-cnt__divider-label"><?php _e('تماس با ما', 'piazhen'); ?></span>
            <span class="pzh-cnt__divider-line pzh-cnt__divider-line--l"></span>
        </div>

        <!-- ============ Map (Neshan / OSM) + contact panel ============ -->
        <section class="pzh-cnt__contact">
            <!-- Map first in DOM = right column in RTL -->
            <div id="pzh-cnt-map" class="pzh-cnt-map" aria-label="<?php esc_attr_e('موقعیت فروشگاه روی نقشه', 'piazhen'); ?>"></div>

            <!-- White «راه‌های ارتباطی» panel -->
            <div class="pzh-cnt-panel">
                <div class="pzh-cnt-panel__bar"><?php _e('راه‌های ارتباطی', 'piazhen'); ?></div>

                <!-- آدرس -->
                <div class="pzh-cnt-panel__card pzh-cnt-panel__card--address">
                    <div class="pzh-cnt-panel__card-head">
                        <span class="pzh-cnt-panel__card-icon"><i class="fa-solid fa-store"></i></span>
                        <span class="pzh-cnt-panel__card-title"><?php _e('آدرس', 'piazhen'); ?></span>
                    </div>
                    <?php foreach ($address_lines as $line): ?>
                        <p class="pzh-cnt-panel__card-line"><?php echo esc_html($line); ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- شماره تماس -->
                <div class="pzh-cnt-panel__card pzh-cnt-panel__card--phone">
                    <div class="pzh-cnt-panel__card-head">
                        <span class="pzh-cnt-panel__card-icon pzh-cnt-panel__card-icon--phone"><i class="fa-solid fa-phone"></i></span>
                        <span class="pzh-cnt-panel__card-title"><?php _e('شماره تماس', 'piazhen'); ?></span>
                    </div>
                    <p class="pzh-cnt-panel__card-line pzh-cnt-panel__card-line--ltr" dir="ltr"><?php echo esc_html($phones); ?></p>
                </div>

                <!-- فضای مجازی -->
                <div class="pzh-cnt-panel__card pzh-cnt-panel__card--social">
                    <div class="pzh-cnt-panel__card-head">
                        <span class="pzh-cnt-panel__card-title"><?php _e('فضای مجازی', 'piazhen'); ?></span>
                        <span class="pzh-cnt-panel__socials">
                            <a href="<?php echo esc_url($social['telegram']); ?>" target="_blank" rel="noopener" aria-label="Telegram">
                                <i class="fa-brands fa-telegram"></i>
                            </a>
                            <a href="<?php echo esc_url($social['whatsapp']); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                            <a href="<?php echo esc_url($social['instagram']); ?>" target="_blank" rel="noopener" aria-label="Instagram">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ Form heading «پاسخگوی شما هستیم» ============ -->
        <div class="pzh-cnt-formhead" aria-hidden="true">
            <span class="pzh-cnt-formhead__line"></span>
            <h2 class="pzh-cnt-formhead__title"><?php _e('پاسخگوی شما هستیم', 'piazhen'); ?></h2>
            <span class="pzh-cnt-formhead__line"></span>
        </div>

        <!-- ============ Ticket form ============ -->
        <form class="pzh-cnt__form" id="pzh-cnt-form" method="post" novalidate>

            <!-- Honeypot: hidden from humans, bots fill it -->
            <div class="pzh-cnt__hp" aria-hidden="true">
                <input type="text" name="cnt_website" value="" tabindex="-1" autocomplete="off">
            </div>

            <div class="pzh-cnt__form-grid">
                <!-- Row 1: موضوع (right) / شماره سفارش (left) -->
                <div class="pzh-cnt-field">
                    <label class="pzh-cnt__field-label" for="cnt-subject">
                        <?php _e('موضوع', 'piazhen'); ?> <span class="pzh-cnt__field-req">*</span>
                    </label>
                    <div class="pzh-cnt__select">
                        <select id="cnt-subject" name="cnt_subject" class="pzh-cnt__field-input">
                            <?php foreach ($subjects as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="pzh-cnt-field">
                    <label class="pzh-cnt__field-label" for="cnt-order"><?php _e('شماره سفارش', 'piazhen'); ?></label>
                    <input type="text" id="cnt-order" name="cnt_order" class="pzh-cnt__field-input" maxlength="60">
                </div>

                <!-- Row 2: نام (right) / نام خانوادگی (left) -->
                <div class="pzh-cnt-field">
                    <label class="pzh-cnt__field-label" for="cnt-name">
                        <?php _e('نام', 'piazhen'); ?> <span class="pzh-cnt__field-req">*</span>
                    </label>
                    <input type="text" id="cnt-name" name="cnt_name" class="pzh-cnt__field-input" maxlength="120"
                           placeholder="<?php esc_attr_e('نام', 'piazhen'); ?>">
                </div>
                <div class="pzh-cnt-field">
                    <label class="pzh-cnt__field-label" for="cnt-family">
                        <?php _e('نام خانوادگی', 'piazhen'); ?> <span class="pzh-cnt__field-req">*</span>
                    </label>
                    <input type="text" id="cnt-family" name="cnt_family" class="pzh-cnt__field-input" maxlength="120"
                           placeholder="<?php esc_attr_e('نام خانوادگی', 'piazhen'); ?>">
                </div>

                <!-- Row 3: شماره تماس (right, LTR) / ایمیل (left, LTR) -->
                <div class="pzh-cnt-field">
                    <label class="pzh-cnt__field-label" for="cnt-phone">
                        <?php _e('شماره تماس', 'piazhen'); ?> <span class="pzh-cnt__field-req">*</span>
                    </label>
                    <input type="tel" id="cnt-phone" name="cnt_phone" class="pzh-cnt__field-input pzh-cnt__field-input--ltr" maxlength="40"
                           placeholder="<?php esc_attr_e('۰۹', 'piazhen'); ?>">
                </div>
                <div class="pzh-cnt-field">
                    <label class="pzh-cnt__field-label" for="cnt-email">
                        <?php _e('ایمیل', 'piazhen'); ?> <span class="pzh-cnt__field-req">*</span>
                    </label>
                    <input type="email" id="cnt-email" name="cnt_email" class="pzh-cnt__field-input pzh-cnt__field-input--ltr" maxlength="120"
                           placeholder="example@mail.com">
                </div>

                <!-- متن پیام -->
                <div class="pzh-cnt-field pzh-cnt__field--wide">
                    <label class="pzh-cnt__field-label" for="cnt-message">
                        <?php _e('متن پیام', 'piazhen'); ?> <span class="pzh-cnt__field-req">*</span>
                    </label>
                    <textarea id="cnt-message" name="cnt_message" class="pzh-cnt__field-input pzh-cnt__field-input--area" maxlength="3000"
                              placeholder="<?php esc_attr_e('بنویسید…', 'piazhen'); ?>"></textarea>
                </div>
            </div>

            <div class="pzh-cnt__form-actions">
                <div class="pzh-cnt__form-message" role="alert" aria-live="polite"></div>

                <button type="submit" class="pzh-cnt__submit"><?php _e('ثبت و ارسال', 'piazhen'); ?></button>
            </div>
        </form>
    </div>
</main>

<script>
(function ($) {
    var hui = function (key) {
        return (window.pzh_options && pzh_options.hui && pzh_options.hui[key]) ? pzh_options.hui[key] : '';
    };

    // ---- Shop map (Neshan with API key, plain Leaflet + OSM otherwise) ----
    if (typeof L !== 'undefined' && $('#pzh-cnt-map').length) {
        var cntCenter = (window.pzh_options && pzh_options.map_center) || [35.7219, 51.3347];
        var cntZoom   = (window.pzh_options && pzh_options.map_zoom) || 13;
        var cntKey    = (window.pzh_options && pzh_options.neshan_key) || '';
        var cntMap;

        if (cntKey) {
            cntMap = new L.Map('pzh-cnt-map', {
                key: cntKey,
                maptype: 'dreamy',
                poi: true,
                traffic: false,
                center: cntCenter,
                zoom: cntZoom,
                zoomControl: true,
                scrollWheelZoom: false
            });
        } else {
            cntMap = L.map('pzh-cnt-map', {
                center: cntCenter,
                zoom: cntZoom,
                zoomControl: true,
                attributionControl: false,
                scrollWheelZoom: false
            });
            L.tileLayer((window.pzh_options && pzh_options.map_tiles) || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, minZoom: 5 }).addTo(cntMap);
        }

        // Shop pin at the map center
        L.marker(cntCenter, {
            icon: L.divIcon({
                className: 'pzh-map-pin',
                html: '<div class="pzh-map-pin__inner"><span class="pin-head"></span><span class="pin-dot"></span></div>',
                iconSize: [34, 48],
                iconAnchor: [17, 46]
            })
        }).addTo(cntMap);
    }

    // ---- Form submission (AJAX into the admin inbox) ----
    var $form = $('#pzh-cnt-form');
    var $btn  = $form.find('.pzh-cnt__submit');
    var $msg  = $form.find('.pzh-cnt__form-message');
    var btnText = $btn.text();

    $form.on('submit', function (e) {
        e.preventDefault();
        $msg.removeClass('is-error is-success').hide().text('');

        var name    = $.trim($form.find('[name="cnt_name"]').val());
        var family  = $.trim($form.find('[name="cnt_family"]').val());
        var phone   = $.trim($form.find('[name="cnt_phone"]').val());
        var email   = $.trim($form.find('[name="cnt_email"]').val());
        var subject = $form.find('[name="cnt_subject"]').val();
        var order   = $.trim($form.find('[name="cnt_order"]').val());
        var message = $.trim($form.find('[name="cnt_message"]').val());

        var error = '';
        if (!name)    error = hui('contact_name_required');
        else if (!family) error = hui('contact_family_required');
        else if (!subject) error = hui('contact_subject_required');
        else if (phone.replace(/[^0-9۰-۹]/g, '').length < 10) error = hui('contact_phone_invalid');
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) error = hui('contact_email_invalid');
        else if (message.length < 5) error = hui('contact_message_short');
        if (error) {
            $msg.addClass('is-error').text(error).show();
            return;
        }

        $btn.prop('disabled', true).text(hui('contact_sending') || btnText);

        $.ajax({
            url: window.pzh_options.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'pzh_contact_submit',
                nonce: window.pzh_options.nonce,
                cnt_name: name,
                cnt_family: family,
                cnt_phone: phone,
                cnt_email: email,
                cnt_subject: subject,
                cnt_order: order,
                cnt_message: message,
                cnt_website: $form.find('[name="cnt_website"]').val()
            },
            success: function (res) {
                if (res && res.success) {
                    $msg.addClass('is-success').text(res.data && res.data.message ? res.data.message : '').show();
                    $form[0].reset();
                } else {
                    $msg.addClass('is-error').text(res && res.data && res.data.message ? res.data.message : hui('contact_send_error')).show();
                }
            },
            error: function () {
                $msg.addClass('is-error').text(hui('contact_send_error')).show();
            },
            complete: function () {
                $btn.prop('disabled', false).text(btnText);
            }
        });
    });
})(jQuery);
</script>

<?php get_footer(); ?>
