<nav class="mobileNav">
    <button class="menuClose fs-54">&times;</button>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'primary',
        'menu_id' => 'primary-menu',
        'menu_class' => 'topNav d-flex align-items-center gap-5',
        'container' => 'ul',
        'container_class' => 'd-flex align-items-center gap-5',
    ));
    ?>
</nav>
