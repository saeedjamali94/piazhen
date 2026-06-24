<?php
/**
 * Homepage Hero Section
 * Grid of banners: 1 large (col-lg-8) + 2 small (col-lg-4)
 */
?>
<section class="home_hero">
    <div class="container">
        <div class="row g-3">
            <!-- Large Banner (col-lg-8) -->
            <div class="col-lg-8">
                <div class="hero-banner hero-banner--large">
                    <div class="hero-banner__bg">
                        <img src="<?= PZH_THEME_URI ?>/assets/images/hero-banner-1.jpg"
                             alt="<?php _e('بنر اصلی', 'piazhen'); ?>"
                             class="hero-banner__image"
                             onerror="this.style.display='none'; this.parentElement.style.backgroundColor='#f0f0f0';">
                    </div>
                    <div class="hero-banner__content">
                        <h2 class="hero-banner__title"><?php _e('عنوان بنر اصلی', 'piazhen'); ?></h2>
                        <p class="hero-banner__subtitle"><?php _e('زیرعنوان بنر اصلی - توضیحات بیشتر درباره این پیشنهاد ویژه', 'piazhen'); ?></p>
                        <a href="<?= SITE_URL ?>/shop" class="hero-banner__cta mainBtn">
                            <?php _e('همین حالا بخرید', 'piazhen'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Small Banners (col-lg-4) -->
            <div class="col-lg-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="hero-banner hero-banner--small">
                            <div class="hero-banner__bg">
                                <img src="<?= PZH_THEME_URI ?>/assets/images/hero-banner-2.jpg"
                                     alt="<?php _e('بنر دوم', 'piazhen'); ?>"
                                     class="hero-banner__image"
                                     onerror="this.style.display='none'; this.parentElement.style.backgroundColor='#e8f4f8';">
                            </div>
                            <div class="hero-banner__content">
                                <h3 class="hero-banner__title"><?php _e('عنوان بنر دوم', 'piazhen'); ?></h3>
                                <a href="<?= SITE_URL ?>/shop" class="hero-banner__link">
                                    <?php _e('مشاهده محصولات', 'piazhen'); ?>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 4L14 8L10 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M14 8H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="hero-banner hero-banner--small">
                            <div class="hero-banner__bg">
                                <img src="<?= PZH_THEME_URI ?>/assets/images/hero-banner-3.jpg"
                                     alt="<?php _e('بنر سوم', 'piazhen'); ?>"
                                     class="hero-banner__image"
                                     onerror="this.style.display='none'; this.parentElement.style.backgroundColor='#fef3e4';">
                            </div>
                            <div class="hero-banner__content">
                                <h3 class="hero-banner__title"><?php _e('عنوان بنر سوم', 'piazhen'); ?></h3>
                                <a href="<?= SITE_URL ?>/shop" class="hero-banner__link">
                                    <?php _e('مشاهده محصولات', 'piazhen'); ?>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 4L14 8L10 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M14 8H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
