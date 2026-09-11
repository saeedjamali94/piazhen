<?php
/**
 * Homepage Hero Section
 * Large banner (right) + 2 side cards (left) + 3 bottom cards.
 * Banners data comes from pzh_hero_banners() in functions.php.
 */

$hero = pzh_hero_banners();
?>
<section class="heroSection">
    <div class="container">
        <div class="heroSection__main">
            <!-- Large Banner -->
            <div class="right"
                 style="background-image: url('<?= esc_url($hero['main']['image']); ?>');">
                <h2><?= esc_html($hero['main']['title']); ?></h2>
                <p><?= esc_html($hero['main']['subtitle']); ?></p>
                <a class="button" href="<?= esc_url($hero['main']['link']); ?>">
                    <?= esc_html($hero['main']['cta']); ?>
                </a>
            </div>

            <!-- Two Side Cards -->
            <div class="left">
                <?php foreach ($hero['side_cards'] as $card): ?>
                    <a class="card"
                       href="<?= esc_url($card['link']); ?>"
                       style="background-image: url('<?= esc_url($card['image']); ?>');">
                        <h2>
                            <?= esc_html($card['title']); ?>
                            <i class="fa-solid fa-arrow-right"></i>
                        </h2>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Three Bottom Cards -->
        <div class="bottom">
            <?php foreach ($hero['bottom_cards'] as $card): ?>
                <a class="card"
                   href="<?= esc_url($card['link']); ?>"
                   style="background-image: url('<?= esc_url($card['image']); ?>');">
                    <h2>
                        <?= esc_html($card['title']); ?>
                        <i class="fa-solid fa-arrow-right"></i>
                    </h2>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
