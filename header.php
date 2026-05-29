<?php
// exit if accessed directly
if( !defined('ABSPATH') ){
    exit;
}

$site_title = get_bloginfo('name');
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="pzh_header">
    <div class="container">
        <div class="top d-flex align-items-center justify-content-between py-4">
            <a class="lightText">درباره ما</a>
            <a class="lightText">سوالات متداول</a>
        </div>

        <div class="bottom d-flex align-items-center justify-content-between py-3">
            <a href="<?= SITE_URL ?>">
                <img src="<?= PZH_THEME_URI ?>/assets/images/logo.png" width="167" height="60" alt="<?= $site_title ?>">
            </a>

            <div class="d-flex gap-4 align-items-center justify-content-center">
                <nav>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_class'     => 'main-menu d-flex align-items-center gap-4',
                        'container'      => 'ul',
                    ]);
                    ?>
                </nav>

                <?= get_template_part('template-parts/global/search' , 'box'); ?>
            </div>

            <div>
                <a href="<?= wc_get_cart_url() ?>">
                    <svg class="icon stroke primary"><use xlink:href="<?= SPRITE_URL ?>#cart"></use></svg>
                </a>

                <a class="ms-2" href="<?= pzhDashboardUrl() ?>">
                    <svg class="icon stroke primary"><use xlink:href="<?= SPRITE_URL ?>#user"></use></svg>
                </a>
            </div>
        </div>
    </div>
</header>