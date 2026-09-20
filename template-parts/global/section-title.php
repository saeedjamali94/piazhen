<?php
$texts = get_query_var('texts') ?: array();
?>
<div class="section-title">

    <?php if (!empty($texts['heading'])): ?>
        <p class="fs-36 whiteGradientVertical bold"><?= esc_html($texts['heading']); ?></p>
    <?php endif; ?>

</div>