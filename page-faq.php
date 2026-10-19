<?php
/**
 * Template Name: سوالات متداول
 *
 * FAQ page: accordion of the question/answer repeater from the site
 * settings (تنظیمات پیاژن → سوالات متداول). Same accordion design and
 * behavior as the single-product FAQ section.
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$faq_items = pzh_faq_items();
?>

<main class="faq-page">
    <div class="container">

        <!-- Page Title -->
        <header class="faq-page__head">
            <h1 class="faq-page__title"><?php the_title(); ?></h1>
        </header>

        <!-- FAQ Accordion (single-product design) -->
        <?php if (!empty($faq_items)): ?>
            <div class="faq-accordion">
                <?php foreach ($faq_items as $index => $item): ?>
                    <?php if (empty($item['question']) && empty($item['answer'])) continue; ?>
                    <div class="faq-item <?php echo $index === 0 ? 'open' : ''; ?>">
                        <button class="faq-item__question" type="button">
                            <span><?php echo esc_html($item['question']); ?></span>
                            <svg class="faq-item__icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div class="faq-item__answer">
                            <p><?php echo wp_kses_post($item['answer']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="faq-page__empty"><?php _e('هنوز سوالی ثبت نشده است.', 'piazhen'); ?></p>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
