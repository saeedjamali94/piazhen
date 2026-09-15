<form action="<?= SITE_URL ?>" class="pzh_search_box" method="GET">
    <input type="search" class="mainInput" placeholder="<?php _e('جستجو در محصولات...', 'piazhen'); ?>" name="s">
    <input type="hidden" name="post_type" value="product">
    <button type="submit" aria-label="<?php _e('جستجو', 'piazhen'); ?>">
        <svg class="icon stroke lightText"><use xlink:href="<?= SPRITE_URL ?>#search"></use></svg>
    </button>
</form>