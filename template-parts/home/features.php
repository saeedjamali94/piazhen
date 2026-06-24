<?php
/**
 * Homepage: Features Section
 * 4-column grid, each with icon & title
 */
$features = array(
    array(
        'icon'  => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#FBCA38" fill-opacity="0.15"/><path d="M14 18L24 14L34 18V30L24 34L14 30V18Z" stroke="#FBCA38" stroke-width="2" stroke-linejoin="round"/><path d="M24 24V34" stroke="#FBCA38" stroke-width="2"/><path d="M14 18L24 24L34 18" stroke="#FBCA38" stroke-width="2" stroke-linejoin="round"/></svg>',
        'title' => __('کیفیت تضمینی', 'piazhen'),
    ),
    array(
        'icon'  => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#FBCA38" fill-opacity="0.15"/><path d="M14 30L18 34L30 20" stroke="#FBCA38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="24" cy="24" r="10" stroke="#FBCA38" stroke-width="2"/></svg>',
        'title' => __('ضمانت بازگشت', 'piazhen'),
    ),
    array(
        'icon'  => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#FBCA38" fill-opacity="0.15"/><path d="M34 20L34 14L28 14" stroke="#FBCA38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M30 18L24 24" stroke="#FBCA38" stroke-width="2" stroke-linecap="round"/><path d="M20 28L14 34L14 28" stroke="#FBCA38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 30L24 24" stroke="#FBCA38" stroke-width="2" stroke-linecap="round"/></svg>',
        'title' => __('ارسال سریع', 'piazhen'),
    ),
    array(
        'icon'  => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#FBCA38" fill-opacity="0.15"/><path d="M18 30C18 30 20 28 24 28C28 28 30 30 30 30" stroke="#FBCA38" stroke-width="2" stroke-linecap="round"/><circle cx="19" cy="19" r="2" fill="#FBCA38"/><circle cx="30" cy="19" r="2" fill="#FBCA38"/><path d="M14 24C14 24 18 20 24 20C30 20 34 24 34 24" stroke="#FBCA38" stroke-width="2" stroke-linecap="round"/></svg>',
        'title' => __('پشتیبانی ۲۴/۷', 'piazhen'),
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
