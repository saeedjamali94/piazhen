#Piazhen woocommerce shop project Development Document

## a shop working with woocommerce

## language of texts and titles in pages html : persian

## assets 

1. jQuery

2. Bootstrap

3. sass

4. swiper for carousels and sliders

## should be fully responsive



## pages and needs

1. Header 
    
    the same header.php on all the site 

    containing : 
    
    logo
    
    navigation with a mega menu with grid of woocommerce categories and sub categories (wp_nav_menu)

    ajax search box for products

    cart icon (containing cart count number and dropdown of products in cart , product count and price and variables, total cart price , discount and pay price , with cart link)
    the cart number and products and total shoul be loaded by ajax

    dashboard/login icon

2. Homepage 

    containing:

    a. Hero : a grid of banners with image , title and link. the first and large banner with col-md-8 should have also sub title and buy button

    b. most selling products (carousel with 5 items in view) . shoul automatically load most sell items in woocommerce

    c. a two-columns section containing: 

        col-md-6 : newest products (carousel which has 4 items (two column of two row) products in a view)

        col-md-6 : on Sale (having discount) products (carousel which has 4 items (two column of two row) products in a view)

    d. brands logos section (grid of 4 cols * 2 rows)

    e. a 4 cols grid containing each item (icon & title)

    f. section of two cols : 

        1. piazhen instagram posts with free api if available (carousel of 1 item per view)

        2. piazhen blog posts (carousel of 1 item per view)

3. products archive page (category items , brand products , ...)

    containing: 

    a. section of categories items or sub categories with links (a carousel which has a see more button to see next items)

    b. sidebar with ajax filters:

        brands checkboxes filter 

        price range slider 

        loading variables dynamically as filters with ajax

    c. on top of products grid : 

        sort radio buttons by : price ASC , price DESC , most sells, newest , most discounts

        count of total products sync with ajax filters

    d. grid of 4 cols product items , (image , add to favorite with ajax icon , title , price , add to cart button with ajax)

    e. seo description box (content is wordpress category descriptionb)


4. single product page 

    containing: 

    breadcrumb

    product image and gallery images , favorite btn , compare btn and whatsapp share btn on image

    title , subtitle 

    brand , category and other specifications set in woocommerce

    variations set to be selected and price change if needed automatically

    tabs including : (all tab items are links with anchor id in bottom of page)

    technical description

    description

    comments 

    related products (in same category and specs)

    faq





