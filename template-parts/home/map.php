<section class="homeMap">
    <div class="container">
        <?php  
        set_query_var("texts" , [
            "topBtnText" => false,
            "heading" => "Institutional Awareness <br> Beyond Individual Desks",
            "text" => "As trading organizations grow, blind spots appear between desks and teams. <br>
            Market signals, risk exposure and operational decisions often remain distributed across disconnected systems.",
        ]);

        echo get_template_part("template-parts/global/section" , "title");
        ?>

        <div class="gradLine"></div>

        <p class="gradText mb-4 text-center">
            Bozy provides a continuous intelligence layer that aggregates signals across the organization,<br>
            enabling leadership and desk managers to maintain situational awareness without disrupting existing trading workflows.
        </p>

        <div class="mapContainer my-5">
            <img class="map" src="<?= BOZY_THEME_URI ?>/assets/images/home/cerc.png" alt="World Map">
            <div class="location tokyo" data-value="Tokyo"></div>
            <div class="location london" data-value="London"></div>
            <div class="location amsterdam" data-value="Amsterdam"></div>
            <div class="location dubai" data-value="Dubai"></div>
        </div>
    </div>
</section>