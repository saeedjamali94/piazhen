<?php
/**
 * Homepage Hero Section
 * Grid of banners: large banner (col-md-8, with badge/title/subtitle/buy button)
 * + two stacked cards (col-md-4, title + link).
 * Banners data comes from pzh_hero_banners() in functions.php.
 */

$hero = pzh_hero_banners();
?>
<section class="heroSection">
    <div class="container">
        <div class="row g-4 align-items-stretch">

            <!-- Large Banner -->
            <div class="col-md-8">
                <a href="<?= esc_url($hero['main']['link']); ?>" class="heroSection__main-banner">
                    <img src="<?= esc_url($hero['main']['image']); ?>"
                         alt="<?= esc_attr($hero['main']['title']); ?>"
                         class="heroSection__main-img">
                    <div class="heroSection__content">
                        <?php if (!empty($hero['main']['badge'])): ?>
                            <span class="heroSection__badge">
                                <svg width="13" height="13" viewBox="0 0 14 14" fill="currentColor">
                                    <path d="M6.8 13.6C6.8 7.5 6.8 7.5 6.8 13.6C6.4 7.5 6.1 7.2 0 6.8C6.1 6.8 6.1 6.8 0 6.8C6.1 6.4 6.4 6.1 6.8 0C6.8 6.1 6.8 6.1 6.8 0C7.2 6.1 7.5 6.4 13.6 6.8C7.5 6.8 7.5 6.8 13.6 6.8C7.5 7.2 7.2 7.5 6.8 13.6Z"/>
                                </svg>
                                <?= esc_html($hero['main']['badge']); ?>
                            </span>
                        <?php endif; ?>

                        <h2 class="heroSection__title"><?= esc_html($hero['main']['title']); ?></h2>
                        <p class="heroSection__subtitle"><?= esc_html($hero['main']['subtitle']); ?></p>

                        <span class="heroSection__btn">
                            <?= esc_html($hero['main']['cta']); ?>
                            <i class="fa-solid fa-arrow-left"></i>
                        </span>
                    </div>
                </a>
            </div>

            <!-- Two Stacked Side Cards -->
            <div class="col-md-4">
                <div class="d-flex flex-column gap-4 h-100">
                    <?php foreach ($hero['side_cards'] as $card): ?>
                        <a class="heroSection__side"
                           href="<?= esc_url($card['link']); ?>">
                            <img src="<?= esc_url($card['image']); ?>"
                                 alt="<?= esc_attr($card['title']); ?>">
                            <h2 class="heroSection__side-title">
                                <?= esc_html($card['title']); ?>
                                <span class="heroSection__side-arrow">
                                    <i class="fa-solid fa-arrow-left"></i>
                                </span>
                            </h2>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</section>
