<section class="features">
    <div class="container">
        <?php  
        set_query_var("texts" , [
            "topBtnText" => "Features",
            "heading" => "Core Capabilities Across <br>the Bozy Platform",
            "text" => "Bozy keeps traders inside a single workspace where curves, positions and market signals stay connected in real time",
        ]);

        echo get_template_part("template-parts/global/section" , "title");
        ?>

        <div class="row pt-5">
            <div class="col-lg-5">
                <div class="features__box">
                    <h2 class="title fs-36">Curve Workspace</h2>
                    <p>
                        This is where traders actually work. You adjust curve values, refine inputs and immediately see how every change affects your P&L.
                    </p>
                    <ul class="mt-5">
                        <li class="d-flex align-items-center justify-content-between mb-3 textColor li">
                            <div class="d-flex align-items-center">
                                <span class="tickBox">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M2.5 7L4.25 8.75L9.5 3.25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                Editable curve cells just like Excel
                            </div>
                            <svg class="arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8.66602 12L11.4659 8.64018C11.721 8.33408 11.8485 8.18103 11.8485 8C11.8485 7.81898 11.721 7.66592 11.4659 7.35982L8.66602 4" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 12L6.79985 8.64018C7.05493 8.33408 7.18248 8.18103 7.18248 8C7.18248 7.81898 7.05493 7.66592 6.79985 7.35982L4 4" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>

                        <li class="d-flex align-items-center justify-content-between mb-3 textColor li">
                            <div class="d-flex align-items-center">
                                <span class="tickBox">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M2.5 7L4.25 8.75L9.5 3.25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                Live P&L visible while adjusting values
                            </div>
                            <svg class="arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8.66602 12L11.4659 8.64018C11.721 8.33408 11.8485 8.18103 11.8485 8C11.8485 7.81898 11.721 7.66592 11.4659 7.35982L8.66602 4" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 12L6.79985 8.64018C7.05493 8.33408 7.18248 8.18103 7.18248 8C7.18248 7.81898 7.05493 7.66592 6.79985 7.35982L4 4" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>

                        <li class="d-flex align-items-center justify-content-between mb-3 textColor li">
                            <div class="d-flex align-items-center">
                                <span class="tickBox">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M2.5 7L4.25 8.75L9.5 3.25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                Real‑time updates across the desk
                            </div>
                            <svg class="arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8.66602 12L11.4659 8.64018C11.721 8.33408 11.8485 8.18103 11.8485 8C11.8485 7.81898 11.721 7.66592 11.4659 7.35982L8.66602 4" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 12L6.79985 8.64018C7.05493 8.33408 7.18248 8.18103 7.18248 8C7.18248 7.81898 7.05493 7.66592 6.79985 7.35982L4 4" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="features__box box2">
                    <img src="<?= BOZY_THEME_URI ?>/assets/images/home/frame2.png" alt="">
                </div>
            </div>

            <div class="col-lg-12">
                <div class="features__box box3">
                    <div class="items d-flex align-items-center justify-content-between my-5">
                        <div class="item">
                            <div class="logoBox">
                                <img src="<?= BOZY_THEME_URI ?>/assets/images/home/brent.svg" alt="Brent">
                            </div>
                            <div class="textBox">
                                <div class="titleBar">Brent Oil</div>
                                <div class="textBar">Swap</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="logoBox">
                                <img src="<?= BOZY_THEME_URI ?>/assets/images/home/brent.svg" alt="Brent">
                            </div>
                            <div class="textBox">
                                <div class="titleBar">Naphta/Brent</div>
                                <div class="textBar">Diff</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="logoBox">
                                <img src="<?= BOZY_THEME_URI ?>/assets/images/home/brent.svg" alt="Brent">
                            </div>
                            <div class="textBox">
                                <div class="titleBar">Naphtha</div>
                                <div class="textBar">Swap</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="logoBox">
                                <img src="<?= BOZY_THEME_URI ?>/assets/images/home/brent.svg" alt="Brent">
                            </div>
                            <div class="textBox">
                                <div class="titleBar">MOPJ/Naphta</div>
                                <div class="textBar">Diff</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="logoBox">
                                <img src="<?= BOZY_THEME_URI ?>/assets/images/home/brent.svg" alt="Brent">
                            </div>
                            <div class="textBox">
                                <div class="titleBar">MOPJ</div>
                                <div class="textBar">Swap</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="logoBox">
                                <img src="<?= BOZY_THEME_URI ?>/assets/images/home/brent.svg" alt="Brent">
                            </div>
                            <div class="textBox">
                                <div class="titleBar">FEI C3/MPOJ</div>
                                <div class="textBar">Diff</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="logoBox">
                                <img src="<?= BOZY_THEME_URI ?>/assets/images/home/brent.svg" alt="Brent">
                            </div>
                            <div class="textBox">
                                <div class="titleBar">FEI C3</div>
                                <div class="textBar">Swap</div>
                            </div>
                        </div>
                    </div>
                    <h2 class="title fs-26">Ready‑to‑Use Trading Templates</h2>
                    <p>
                        Start with pre‑built LPG, crude or naphtha curves and adjust the structure to match your own trading logic.
                    </p>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="features__box box4" dir="rtl">
                    <div class="row mt-3">
                        <div class="col-md-1"></div>
                        <div class="col-md-7 text-start">
                            <h2 class="title fs-26">Real‑Time Curve Sharing</h2>
                            <p>
                                Share a column with a colleague and their updates appear instantly in your curve.
                                No files, no version conflicts.
                            </p>
                        </div>
                    </div>
                    <img class="menu" src="<?= BOZY_THEME_URI ?>/assets/images/home/menu.png" alt="">
                    <img class="menu_back" src="<?= BOZY_THEME_URI ?>/assets/images/home/menu_back.png" alt="">
                </div>
            </div>

            <div class="col-lg-5">
                <div class="features__box">
                    <img src="<?= BOZY_THEME_URI ?>/assets/images/home/menu2.png" alt="" width="100%">
                    <div class="">
                        <h2 class="title fs-26">Instant Curve Updates</h2>
                        <p>
                            Reset your curve to the latest market close with one click, so every trading day starts from the right baseline.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-5">
                <div class="features__box glowBox px-5 threeBoxes justify-content-center">
                    <svg class="homeHeroGlowIcon" xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="none">
                        <path d="M48 37.333C49.1476 42.6772 53.3218 46.8523 58.666 48C53.3218 49.1477 49.1476 53.3228 48 58.667C46.8524 53.3227 42.6773 49.1477 37.333 48C42.6773 46.8523 46.8524 42.6773 48 37.333ZM24.1914 5.86621C26.622 17.1853 35.4641 26.0282 46.7832 28.459C35.4642 30.8898 26.622 39.7317 24.1914 51.0508C21.7607 39.7315 12.9189 30.8896 1.59961 28.459C12.9189 26.0283 21.7607 17.1855 24.1914 5.86621ZM50.666 5.33301C51.5267 9.34115 54.6579 12.4722 58.666 13.333C54.6579 14.1937 51.5268 17.325 50.666 21.333C49.8052 17.3249 46.6741 14.1937 42.666 13.333C46.6742 12.4723 49.8053 9.34124 50.666 5.33301Z" fill="url(#paint0_linear_783_2563)"/>
                        <defs>
                            <linearGradient id="paint0_linear_783_2563" x1="30.1328" y1="5.33301" x2="30.1328" y2="58.667" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2EA8E6"/>
                                <stop offset="1" stop-color="#9DC2FF"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="fw-bold fs-36 glowBox__title">
                        Market Intelligence & Chart Builder
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="features__box threeBoxes rightPart">
                    <img class="list" src="<?= BOZY_THEME_URI ?>/assets/images/home/list.svg" alt="">
                    <div class="fw-bold fs-18 title">
                        Position‑Aware Market Intelligence
                    </div>
                    <p>
                        News filtered automatically based on the products and exposures you are trading.
                    </p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="features__box threeBoxes rightPart">
                    <img class="chart" src="<?= BOZY_THEME_URI ?>/assets/images/home/chart.png" alt="">
                    <div class="fw-bold fs-18 title">
                        Advanced Market Charts
                    </div>
                    <p class="mb-0">
                        Analyze spreads, seasonal patterns and price structures directly from the same trading environment.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>