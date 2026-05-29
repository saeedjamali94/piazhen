<section class="requestDemo">
    <div class="gradLine noBlur"></div>
    <div class="container">
        <?php  
        set_query_var("texts" , [
            "topBtnText" => false,
            "heading" => "Experience Continuous Market Context",
            "text" => "Request a demo to see how Bozy operates as a decision intelligence layer <br> across professional commodity trading desks.",
        ]);

        echo get_template_part("template-parts/global/section" , "title");
        ?>

        <div class="text-center">
            <a class="mainBtn me-3">Request a Demo</a>
        </div>

    </div>
    <div class="gradLine noBlur"></div>
</section>