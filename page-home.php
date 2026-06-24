<?php
/**
 * Template Name: Homepage
 *
 * @package Piazhen
 */

get_header(); ?>

<main class="homepage">
    <!-- a. Hero Banners Grid -->
    <?php get_template_part('template-parts/home/hero'); ?>

    <!-- b. Most Selling Products Carousel (5 items per view) -->
    <?php get_template_part('template-parts/home/most', 'selling'); ?>

    <!-- c. Newest + On Sale Products (two-column section) -->
    <section class="home_products_sections py-5">
        <div class="container">
            <div class="row g-4">
                <?php get_template_part('template-parts/home/newest', 'products'); ?>
                <?php get_template_part('template-parts/home/on-sale', 'products'); ?>
            </div>
        </div>
    </section>

    <!-- d. Brands Logos Grid (4 cols × 2 rows) -->
    <?php get_template_part('template-parts/home/brands'); ?>

    <!-- e. Features Grid (4 cols: icon + title) -->
    <?php get_template_part('template-parts/home/features'); ?>

    <!-- f. Instagram + Blog (two-column section with carousels) -->
    <?php get_template_part('template-parts/home/instagram', 'blog'); ?>
</main>

<?php get_footer(); ?>
