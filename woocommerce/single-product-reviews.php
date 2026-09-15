<?php
/**
 * Single Product Reviews (custom design, standard WordPress comments — no plugins)
 *
 * @package Piazhen
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;
if (!$product) $product = wc_get_product(get_the_ID());
$product_id   = $product->get_id();
$avg_rating   = $product->get_average_rating();
$review_count = $product->get_review_count();
$breakdown    = pzh_get_review_breakdown($product_id);

$reviews = get_comments(array(
    'post_id' => $product_id,
    'status'  => 'approve',
    'orderby' => 'comment_date_gmt',
    'order'   => 'DESC',
    'number'  => 20,
));

$show_form = false;
if (get_option('woocommerce_review_rating_verification_required') === 'no' || wc_customer_bought_product('', get_current_user_id(), $product_id)) {
    $show_form = true;
}
?>
<div class="product-reviews">

    <!-- Summary Panel -->
    <div class="product-reviews__summary">
        <div class="product-reviews__score">
            <div class="product-reviews__score-number"><?php echo $review_count > 0 ? number_format($avg_rating, 1) : '0.0'; ?></div>
            <div class="product-reviews__score-stars"><?php echo pzh_stars_html($avg_rating); ?></div>
            <div class="product-reviews__score-count">
                <?php printf(__('از مجموع %s دیدگاه', 'piazhen'), pzh_fa_num($review_count)); ?>
            </div>
        </div>
        <div class="product-reviews__breakdown">
            <?php for ($star = 5; $star >= 1; $star--): ?>
                <?php
                $count = $breakdown[$star];
                $percent = $review_count > 0 ? round(($count / $review_count) * 100) : 0;
                ?>
                <div class="review-bar">
                    <span class="review-bar__label"><?php echo pzh_fa_num($star); ?> <i class="fa-solid fa-star"></i></span>
                    <span class="review-bar__track">
                        <span class="review-bar__fill" style="width: <?php echo $percent; ?>%;"></span>
                    </span>
                    <span class="review-bar__count"><?php echo pzh_fa_num($count); ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Review Cards -->
    <?php if (!empty($reviews)): ?>
        <div class="product-reviews__list">
            <?php foreach ($reviews as $review):
                $rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
            ?>
                <div class="review-card">
                    <div class="review-card__header">
                        <div class="review-card__avatar">
                            <?php echo get_avatar($review, 44); ?>
                        </div>
                        <div class="review-card__meta">
                            <div class="review-card__author"><?php echo esc_html($review->comment_author); ?></div>
                            <div class="review-card__date">
                                <?php echo get_comment_date('Y/m/d', $review); ?>
                            </div>
                        </div>
                        <div class="review-card__stars"><?php echo pzh_stars_html($rating); ?></div>
                    </div>
                    <div class="review-card__text">
                        <?php echo esc_html($review->comment_content); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!comments_open()): ?>
        <p class="product-reviews__closed"><?php _e('امکان ثبت دیدگاه برای این محصول وجود ندارد.', 'piazhen'); ?></p>
    <?php else: ?>
        <p class="product-reviews__empty"><?php _e('هنوز دیدگاهی ثبت نشده است؛ اولین نفری باشید که دیدگاه خود را ثبت می‌کند.', 'piazhen'); ?></p>
    <?php endif; ?>

    <!-- Review Form (standard WP comments + WooCommerce rating field) -->
    <?php if ($show_form && comments_open()): ?>
        <div class="product-reviews__form">
            <h4 class="product-reviews__form-title"><?php _e('ثبت دیدگاه جدید', 'piazhen'); ?></h4>

            <form action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" id="review-form" class="review-form">
                <?php
                $commenter = wp_get_current_commenter();
                ?>
                <div class="review-form__row">
                    <label class="review-form__label" for="rating"><?php _e('امتیاز شما', 'piazhen'); ?> <span class="required">*</span></label>
                    <div class="star-rating-input" id="star-rating-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star-<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                            <label for="star-<?php echo $i; ?>" title="<?php echo pzh_fa_num($i); ?>"><i class="fa-solid fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="review-form__grid">
                    <?php if (!is_user_logged_in()): ?>
                        <div class="review-form__field">
                            <label class="review-form__label" for="author"><?php _e('نام', 'piazhen'); ?> <span class="required">*</span></label>
                            <input type="text" name="author" id="author" value="<?php echo esc_attr($commenter['comment_author']); ?>" required>
                        </div>
                        <div class="review-form__field">
                            <label class="review-form__label" for="email"><?php _e('ایمیل', 'piazhen'); ?> <span class="required">*</span></label>
                            <input type="email" name="email" id="email" value="<?php echo esc_attr($commenter['comment_author_email']); ?>" required>
                        </div>
                    <?php endif; ?>
                    <div class="review-form__field review-form__field--full">
                        <label class="review-form__label" for="comment"><?php _e('متن دیدگاه', 'piazhen'); ?> <span class="required">*</span></label>
                        <textarea name="comment" id="comment" rows="5" required placeholder="<?php _e('تجربه خود از این محصول را بنویسید...', 'piazhen'); ?>"></textarea>
                    </div>
                </div>

                <input type="hidden" name="comment_post_ID" value="<?php echo $product_id; ?>">
                <input type="hidden" name="comment_parent" value="0">
                <?php wp_nonce_field('comment-' . $product_id, 'comment_nonce'); ?>

                <button type="submit" class="review-form__submit mainBtn mainBtn--yellow"><?php _e('ثبت دیدگاه', 'piazhen'); ?></button>
            </form>
        </div>
    <?php elseif (get_option('woocommerce_review_rating_verification_required') === 'yes' && !wc_customer_bought_product('', get_current_user_id(), $product_id)): ?>
        <p class="product-reviews__closed">
            <?php _e('برای ثبت دیدگاه باید این محصول را خریداری کرده باشید.', 'piazhen'); ?>
        </p>
    <?php endif; ?>
</div>
