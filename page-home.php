<?php
/**
 * Template Name: Homepage
 *
 * @package Piazhen
 */

get_header(); ?>

<main class="homepage">

    <!-- Hero Banners Grid (col-md-8 large + col-md-4 stacked cards) -->
    <?php get_template_part('template-parts/home/hero'); ?>

    <!-- Most Selling Products Carousel (5 items per view) -->
    <?php get_template_part('template-parts/home/newest', 'products'); ?>

    <!-- Newest + On Sale Products (two-column section, 2x2 grids) -->
    <section class="home_products_sections py-5">
        <div class="container">
            <div class="row g-4">
                <?php get_template_part('template-parts/home/most-selling', 'products'); ?>
                <?php get_template_part('template-parts/home/on-sale', 'products'); ?>
            </div>
        </div>
    </section>

    <!-- Brands Logos Grid (4 cols × 2 rows) -->
    <?php get_template_part('template-parts/home/brands'); ?>

    <!-- Features Grid (4 cols: icon + title) -->
    <?php get_template_part('template-parts/home/features'); ?>

    <!-- Instagram + Blog (two-column section with carousels) -->
    <?php get_template_part('template-parts/home/instagram', 'blog'); ?>

</main>

<?php get_footer(); ?>

