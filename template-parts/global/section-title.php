<?php
$texts = get_query_var('texts') ?: array();
?>
<div class="section-title text-center">
    <?php if (!empty($texts['topBtnText'])): ?>
        <span class="topBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M6.81252 13.625C6.81252 7.50923 6.81252 7.50923 6.81252 13.625C6.42544 7.50923 6.08997 7.19957 0 6.8125C6.11578 6.8125 6.11578 6.8125 0 6.8125C6.11578 6.42543 6.42544 6.08996 6.81252 0C6.81252 6.11577 6.81252 6.11577 6.81252 0C7.19959 6.11577 7.53503 6.42543 13.625 6.8125C7.50922 6.8125 7.50922 6.8125 13.625 6.8125C7.53503 7.19957 7.2254 7.50923 6.81252 13.625Z" fill="currentColor"/>
            </svg>
            <?= esc_html($texts['topBtnText']); ?>
        </span>
    <?php endif; ?>

    <?php if (!empty($texts['heading'])): ?>
        <p class="fs-36 whiteGradientVertical bold"><?= esc_html($texts['heading']); ?></p>
    <?php endif; ?>

    <?php if (!empty($texts['text'])): ?>
        <p class="my-3"><?= esc_html($texts['text']); ?></p>
    <?php endif; ?>
</div>