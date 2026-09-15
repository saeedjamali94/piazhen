<?php
/**
 * Homepage: Features Section
 * 4-column grid, each with icon & title
 */
$features = array(
    array(
        'icon'  => '<svg width="54" height="54" viewBox="0 0 54 54" fill="none"><circle cx="27" cy="27" r="27" fill="#FBCA38"/><path d="M11 35h19v-9H11v9z" fill="#1a1a1a"/><path d="M30 25h10l3 5v5H30v-10z" fill="#1a1a1a"/><circle cx="16" cy="36.5" r="3.6" fill="#fff"/><circle cx="36" cy="36.5" r="3.6" fill="#fff"/><path d="M8 26h4M14 21h4" stroke="#1a1a1a" stroke-width="2.4" stroke-linecap="round"/></svg>',
        'title' => __('ارسال سریع', 'piazhen'),
    ),
    array(
        'icon'  => '<svg width="54" height="54" viewBox="0 0 54 54" fill="none"><circle cx="27" cy="27" r="27" fill="#FBCA38"/><rect x="8" y="16" width="38" height="22" rx="3.5" fill="#1a1a1a"/><path d="M8 23h38" stroke="#FBCA38" stroke-width="2.6"/><path d="M14 32h6M26 32h4" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg>',
        'title' => __('پرداخت امن', 'piazhen'),
    ),
    array(
        'icon'  => '<svg width="54" height="54" viewBox="0 0 54 54" fill="none"><circle cx="27" cy="27" r="27" fill="#FBCA38"/><path d="M27 10l13 4.5v9.5c0 7.5-5.2 12.6-13 15.8-7.8-3.2-13-8.3-13-15.8v-9.5L27 10z" fill="#1a1a1a"/><path d="M21.5 27l3.6 3.6 7.4-7.4" stroke="#FBCA38" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'title' => __('ضمانت اصالت کالا', 'piazhen'),
    ),
    array(
        'icon'  => '<svg width="54" height="54" viewBox="0 0 54 54" fill="none"><circle cx="27" cy="27" r="27" fill="#FBCA38"/><path d="M14 28.5v-2.2a13 13 0 0 1 26 0v2.2" stroke="#1a1a1a" stroke-width="3.2" fill="none" stroke-linecap="round"/><rect x="12.5" y="27" width="7.5" height="9.5" rx="3.2" fill="#1a1a1a"/><rect x="34" y="27" width="7.5" height="9.5" rx="3.2" fill="#1a1a1a"/><path d="M37.7 35.5v2.7a5 5 0 0 1-5 5h-3" stroke="#1a1a1a" stroke-width="3.2" stroke-linecap="round" fill="none"/></svg>',
        'title' => __('پشتیبانی ۲۴ ساعته', 'piazhen'),
    ),
);
?>
<section class="home_features py-5">
    <div class="container">
        <div class="features-grid">
            <?php foreach ($features as $feature): ?>
                <div class="feature-item text-center">
                    <div class="feature-item__icon">
                        <?= $feature['icon']; ?>
                    </div>
                    <h4 class="feature-item__title"><?= esc_html($feature['title']); ?></h4>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
