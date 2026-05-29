<section class="whyUs">
    <img class="leftShadow" src="<?= BOZY_THEME_URI ?>/assets/images/home/left-shadow.svg">
    <img class="rightShadow" src="<?= BOZY_THEME_URI ?>/assets/images/home/right-shadow.svg">
    <img class="middleShadow" src="<?= BOZY_THEME_URI ?>/assets/images/home/middle-shadow.svg">
    <img class="lineStar" src="<?= BOZY_THEME_URI ?>/assets/images/home/liner-star.svg">
    <div class="container">
        <?php  
        set_query_var("texts" , [
            "topBtnText" => "Why Bozy?",
            "heading" => "Clarity Where Decisions Matter Most",
            "text" => "Instead of juggling disconnected data, signals, and risk across multiple systems, <br>everything comes together inside one continuous, decision‑ready intelligence layer.",
        ]);

        echo get_template_part("template-parts/global/section", "title");
        ?>

        <div class="carousel owl-carousel homeWhyUsCarousel">
            <?= get_template_part("template-parts/home/whyUs/items"); ?>
        </div>
    </div>
</section>