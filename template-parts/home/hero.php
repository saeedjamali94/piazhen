<?php
/**
 * Homepage Hero Section (per hero.png)
 * Row 1: large yellow-gradient hero (col-md-8, right) + two stacked photo tiles (col-md-4)
 * Row 2: three photo tiles.
 * Banners data comes from pzh_hero_banners() in functions.php.
 */

$hero = pzh_hero_banners();
?>
<section class="heroSection">
    <div class="container">
        <div class="row g-4 align-items-stretch">

            <!-- Large Hero Carousel (RTL first = right side) -->
            <div class="col-md-8">
                <div class="swiper hero-swiper" data-hero-swiper="1">
                    <div class="swiper-wrapper">
                        <?php foreach ($hero['slides'] as $slide): ?>
                            <div class="swiper-slide">
                                <a href="<?= esc_url($slide['link']); ?>" class="heroSection__main-banner">
                                    <img src="<?= esc_url($slide['image']); ?>"
                                         alt="<?= esc_attr($slide['title']); ?>"
                                         class="heroSection__main-img">

                                    <div class="heroSection__content">
                                        <h2 class="heroSection__title"><?= esc_html($slide['title']); ?></h2>
                                        <div class="heroSection__subtitle">
                                            <?php foreach ((array) $slide['subtitle'] as $line): ?>
                                                <p><?= esc_html($line); ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                        <span class="heroSection__btn"><?= esc_html($slide['cta']); ?></span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Carousel dots (white, Swiper pagination) -->
                    <div class="heroSection__dots"></div>
                </div>
            </div>

            <!-- Two Stacked Photo Tiles -->
            <div class="col-md-4">
                <div class="d-flex flex-column gap-3 h-100">
                    <?php foreach ($hero['side_cards'] as $card): ?>
                        <a class="heroSection__tile heroSection__tile--fill"
                           href="<?= esc_url($card['link']); ?>"
                           style="background-image: url('<?= esc_url($card['image']); ?>');">
                            <span class="heroSection__tile-title"><?= esc_html($card['title']); ?></span>
                            <span class="heroSection__tile-arrow">
                                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M9 7h8v8"/></svg>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Bottom Row: Three Photo Tiles -->
            <div class="col-12">
                <div class="row g-4">
                    <?php foreach ($hero['bottom_cards'] as $card): ?>
                        <div class="col-md-4">
                            <a class="heroSection__tile"
                               href="<?= esc_url($card['link']); ?>"
                               style="background-image: url('<?= esc_url($card['image']); ?>');">
                                <span class="heroSection__tile-title"><?= esc_html($card['title']); ?></span>
                                <span class="heroSection__tile-arrow">
                                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M9 7h8v8"/></svg>
                                </span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</section>
