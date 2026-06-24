/**
 * Piazhen Theme - Main JavaScript
 * Handles: AJAX search, cart, favorites, filters, Swiper carousels, mega menu, mobile nav
 */
var $ = jQuery;

// Clean URL
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// ============================================================================
// Utility Functions
// ============================================================================

/** Debounce helper */
function pzhDebounce(fn, delay) {
    var timer;
    return function () {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () { fn.apply(context, args); }, delay);
    };
}

/** Show a toast notification */
function pzhToast(message, type) {
    type = type || 'success';
    var toast = $('<div class="pzh-toast pzh-toast--' + type + '">' + message + '</div>');
    $('body').append(toast);
    setTimeout(function () { toast.addClass('show'); }, 10);
    setTimeout(function () {
        toast.removeClass('show');
        setTimeout(function () { toast.remove(); }, 300);
    }, 3000);
}

/** Update cart count badge */
function pzhUpdateCartBadge(count) {
    var $badge = $('.cart-count');
    if (count > 0) {
        if ($badge.length) {
            $badge.text(count).show();
        } else {
            $('.cart-icon-wrapper').append('<span class="cart-count">' + count + '</span>');
        }
    } else {
        $badge.hide();
    }
}

/** Refresh mini-cart dropdown content */
function pzhRefreshMiniCart() {
    $.ajax({
        url: pzh_options.ajax_url,
        type: 'POST',
        data: {
            action: 'pzh_get_mini_cart',
            nonce: pzh_options.nonce
        },
        success: function (response) {
            if (response.success) {
                $('.cart-dropdown').html(response.data.html);
                pzhUpdateCartBadge(response.data.count);
            }
        }
    });
}

// ============================================================================
// Document Ready
// ============================================================================
$(document).ready(function () {

    // ========================================================================
    // AJAX Search
    // ========================================================================
    var $searchInput = $('.pzh_search_box input[type="search"]');
    var $searchResults = $('.search-results');

    if ($searchInput.length) {
        $searchInput.on('input', pzhDebounce(function () {
            var term = $(this).val().trim();

            if (term.length < 2) {
                $searchResults.removeClass('active').html('');
                return;
            }

            $.ajax({
                url: pzh_options.ajax_url,
                type: 'POST',
                data: {
                    action: 'pzh_ajax_search',
                    term: term,
                    nonce: pzh_options.nonce
                },
                beforeSend: function () {
                    $searchResults.addClass('loading');
                },
                success: function (response) {
                    $searchResults.removeClass('loading');
                    if (response.success) {
                        $searchResults.html(response.data.html).addClass('active');
                    }
                }
            });
        }, 300));

        // Hide search results on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.pzh_search_box').length) {
                $searchResults.removeClass('active');
            }
        });

        // Show results on focus if has value
        $searchInput.on('focus', function () {
            if ($(this).val().trim().length >= 2 && $searchResults.html().trim()) {
                $searchResults.addClass('active');
            }
        });
    }

    // ========================================================================
    // Cart Dropdown Toggle & AJAX
    // ========================================================================
    var $cartIcon = $('.cart-icon-wrapper');
    var $cartDropdown = $('.cart-dropdown');

    $cartIcon.on('click', function (e) {
        e.preventDefault();
        $cartDropdown.toggleClass('active');
        // Refresh cart content each time it opens
        if ($cartDropdown.hasClass('active')) {
            pzhRefreshMiniCart();
        }
    });

    // Close cart dropdown on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.cart-icon-wrapper').length && !$(e.target).closest('.cart-dropdown').length) {
            $cartDropdown.removeClass('active');
        }
    });

    // Remove from cart (delegated)
    $(document).on('click', '.mini-cart__remove', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var cartKey = $(this).data('cart-key');

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            data: {
                action: 'pzh_remove_from_cart',
                cart_key: cartKey,
                nonce: pzh_options.nonce
            },
            success: function (response) {
                if (response.success) {
                    pzhRefreshMiniCart();
                    pzhUpdateCartBadge(response.data.cart_count);
                    pzhToast(response.data.message);
                }
            }
        });
    });

    // ========================================================================
    // Add to Cart (delegated - product cards)
    // ========================================================================
    $(document).on('click', '.product-card__add-to-cart', function () {
        var $btn = $(this);
        var productId = $btn.data('product-id');

        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            data: {
                action: 'pzh_add_to_cart',
                product_id: productId,
                nonce: pzh_options.nonce
            },
            success: function (response) {
                $btn.removeClass('loading').prop('disabled', false);
                if (response.success) {
                    pzhUpdateCartBadge(response.data.cart_count);
                    pzhToast(response.data.message);
                    // Refresh mini-cart if open
                    if ($cartDropdown.hasClass('active')) {
                        pzhRefreshMiniCart();
                    }
                } else {
                    pzhToast(response.data.message, 'error');
                }
            },
            error: function () {
                $btn.removeClass('loading').prop('disabled', false);
                pzhToast('خطا در ارتباط با سرور.', 'error');
            }
        });
    });

    // ========================================================================
    // Favorite Toggle (delegated)
    // ========================================================================
    $(document).on('click', '.product-card__favorite', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var productId = $btn.data('product-id');

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            data: {
                action: 'pzh_toggle_favorite',
                product_id: productId,
                nonce: pzh_options.nonce
            },
            success: function (response) {
                if (response.success) {
                    if (response.data.action === 'added') {
                        $btn.addClass('active');
                    } else {
                        $btn.removeClass('active');
                    }
                    pzhToast(response.data.message);
                }
            }
        });
    });

    // ========================================================================
    // Product Filters (Archive Page)
    // ========================================================================
    var $filterForm = $('.archive-filters');
    var $productsContainer = $('.products-grid-wrapper');
    var $productsCount = $('.products-count');
    var $paginationContainer = $('.products-pagination-wrapper');

    if ($filterForm.length) {
        // Filter change
        $filterForm.on('change', 'input[type="checkbox"], input[type="radio"]', function () {
            pzhApplyFilters(1);
        });

        // Price range slider
        var $priceSlider = $('#price-range-slider');
        var $priceMin = $('#price-min');
        var $priceMax = $('#price-max');

        if ($priceSlider.length) {
            // Price slider change triggers filter
            var priceTimeout;
            $priceSlider.on('input', function () {
                clearTimeout(priceTimeout);
                priceTimeout = setTimeout(pzhApplyFilters, 500);
            });
        }

        // Sort radio buttons
        $filterForm.on('change', 'input[name="sort"]', function () {
            pzhApplyFilters(1);
        });

        // Pagination (delegated)
        $(document).on('click', '.products-pagination__btn', function () {
            var page = $(this).data('page');
            pzhApplyFilters(page);
            $('html, body').animate({ scrollTop: $productsContainer.offset().top - 100 }, 300);
        });
    }

    function pzhApplyFilters(page) {
        page = page || 1;

        // Collect filter values
        var orderby = $filterForm.find('input[name="sort"]:checked').val() || 'date';
        var brands = $filterForm.find('input[name="brands[]"]:checked').map(function () { return $(this).val(); }).get();
        var minPrice = $filterForm.find('#price-min').val() || 0;
        var maxPrice = $filterForm.find('#price-max').val() || 0;
        var category = $filterForm.data('category') || '';

        // Determine sort order from orderby
        var order = 'DESC';
        if (orderby === 'price-asc') { orderby = 'price'; order = 'ASC'; }
        else if (orderby === 'price-desc') { orderby = 'price'; order = 'DESC'; }
        else if (orderby === 'newest') { orderby = 'date'; order = 'DESC'; }
        else if (orderby === 'popularity' || orderby === 'most-sells') { orderby = 'popularity'; }
        else if (orderby === 'discount') { orderby = 'discount'; }

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            data: {
                action: 'pzh_filter_products',
                page: page,
                per_page: 12,
                orderby: orderby,
                order: order,
                brands: brands,
                min_price: minPrice,
                max_price: maxPrice,
                category: category,
                nonce: pzh_options.nonce
            },
            beforeSend: function () {
                $productsContainer.addClass('loading');
            },
            success: function (response) {
                $productsContainer.removeClass('loading');
                if (response.success) {
                    $productsContainer.html(response.data.html);
                    $productsCount.text(response.data.total);
                    updateUrlParams(response.data);
                }
            }
        });
    }

    function updateUrlParams(data) {
        // Update browser URL without reload (for SEO/shareability)
        if (window.history.replaceState) {
            var url = new URL(window.location);
            url.searchParams.set('paged', data.page);
            window.history.replaceState(null, null, url.toString());
        }
    }

    // ========================================================================
    // Swiper Carousels Initialization
    // ========================================================================

    // Most Selling Products Carousel
    if ($('.most-selling-swiper').length) {
        new Swiper('.most-selling-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.most-selling-next',
                prevEl: '.most-selling-prev',
            },
            breakpoints: {
                576: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                992: { slidesPerView: 4 },
                1200: { slidesPerView: 5 },
            },
        });
    }

    // Newest Products Carousel (2x2 grid)
    if ($('.newest-products-swiper').length) {
        new Swiper('.newest-products-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.newest-next',
                prevEl: '.newest-prev',
            },
            breakpoints: {
                768: { slidesPerView: 2 },
                1200: { slidesPerView: 2 },
            },
            slidesPerGroup: 2,
            grid: {
                rows: 2,
                fill: 'row',
            },
        });
    }

    // On Sale Products Carousel (2x2 grid)
    if ($('.on-sale-swiper').length) {
        new Swiper('.on-sale-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.sale-next',
                prevEl: '.sale-prev',
            },
            breakpoints: {
                768: { slidesPerView: 2 },
                1200: { slidesPerView: 2 },
            },
            slidesPerGroup: 2,
            grid: {
                rows: 2,
                fill: 'row',
            },
        });
    }

    // Instagram Carousel (1 item per view)
    if ($('.instagram-swiper').length) {
        new Swiper('.instagram-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.instagram-next',
                prevEl: '.instagram-prev',
            },
        });
    }

    // Blog Posts Carousel (1 item per view)
    if ($('.blog-swiper').length) {
        new Swiper('.blog-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.blog-next',
                prevEl: '.blog-prev',
            },
        });
    }

    // Categories Carousel (Archive page)
    if ($('.categories-swiper').length) {
        new Swiper('.categories-swiper', {
            slidesPerView: 2,
            spaceBetween: 16,
            loop: false,
            navigation: {
                nextEl: '.categories-next',
                prevEl: '.categories-prev',
            },
            breakpoints: {
                576: { slidesPerView: 3 },
                768: { slidesPerView: 4 },
                992: { slidesPerView: 6 },
                1200: { slidesPerView: 8 },
            },
        });
    }

    // ========================================================================
    // Mega Menu Interactions
    // ========================================================================
    var $megaMenuItems = $('.main-menu .mega-menu, .main-menu .menu-item-has-children');

    if ($(window).width() >= 992) {
        // Desktop: hover to open mega menu
        $megaMenuItems.on('mouseenter', function () {
            $(this).find('.mega-menu, > .sub-menu').stop(true, true).slideDown(200);
        });
        $megaMenuItems.on('mouseleave', function () {
            $(this).find('.mega-menu, > .sub-menu').stop(true, true).slideUp(150);
        });
    } else {
        // Mobile: click to toggle
        $megaMenuItems.on('click', '> a', function (e) {
            e.preventDefault();
            $(this).siblings('.mega-menu, .sub-menu').slideToggle(200);
        });
    }

    // ========================================================================
    // Mobile Navigation
    // ========================================================================
    $('.menuBtn, .menuClose').on('click', function () {
        $('.mobileNav').toggleClass('open');
        $('body').toggleClass('menu-open');
    });

    // ========================================================================
    // Footer Navigation Toggle (Mobile)
    // ========================================================================
    $('.footerNavToggle').on('click', function () {
        var id = $(this).attr('data-nav');
        $('.footer-nav[data-nav="' + id + '"]').toggleClass('open');
        $(this).toggleClass('active');
    });

    // ========================================================================
    // SEO Description Box Toggle
    // ========================================================================
    $('.seo-section .showMore').on('click', function () {
        var $this = $(this);
        var $box = $this.parents('.seo-section').find('.textBox');
        $box.toggleClass('open');

        if ($box.hasClass('open')) {
            $this.html('بستن <svg class="ms-2" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 10L7.29289 6.70711C7.62623 6.37377 7.79289 6.20711 8 6.20711C8.20711 6.20711 8.37377 6.37377 8.70711 6.70711L12 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>');
        } else {
            $this.html('مشاهده بیشتر <svg class="ms-2" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6L7.29289 9.29289C7.62623 9.62623 7.79289 9.79289 8 9.79289C8.20711 9.79289 8.37377 9.62623 8.70711 9.29289L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>');
        }
    });

    // ========================================================================
    // Single Product Page
    // ========================================================================

    // Gallery Thumbnail Click → Update Main Image
    $(document).on('click', '.product-gallery__thumb', function () {
        var $thumb = $(this);
        var fullSrc = $thumb.data('full');
        var zoomSrc = $thumb.data('zoom');

        $('.product-gallery__thumb').removeClass('active');
        $thumb.addClass('active');

        $('#main-product-image').attr('src', fullSrc).data('zoom', zoomSrc);
    });

    // Product Thumbnails Swiper
    if ($('.product-thumbs-swiper').length) {
        new Swiper('.product-thumbs-swiper', {
            slidesPerView: 4,
            spaceBetween: 10,
            navigation: {
                nextEl: '.thumb-nav--next',
                prevEl: '.thumb-nav--prev',
            },
            breakpoints: {
                576: { slidesPerView: 5, spaceBetween: 10 },
                768: { slidesPerView: 4, spaceBetween: 12 },
                992: { slidesPerView: 5, spaceBetween: 12 },
            },
        });
    }

    // Quantity +/- Buttons
    $(document).on('click', '.qty-btn', function () {
        var $input = $(this).siblings('.qty-input');
        var currentVal = parseInt($input.val(), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 99;
        var min = parseInt($input.attr('min'), 10) || 1;

        if ($(this).hasClass('qty-plus') && currentVal < max) {
            $input.val(currentVal + 1).trigger('change');
        } else if ($(this).hasClass('qty-minus') && currentVal > min) {
            $input.val(currentVal - 1).trigger('change');
        }
    });

    // Simple Product Add to Cart (single product page)
    $(document).on('click', '.add-to-cart-single', function () {
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var qty = parseInt($('#single-qty').val(), 10) || 1;

        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            data: {
                action: 'pzh_add_to_cart',
                product_id: productId,
                quantity: qty,
                nonce: pzh_options.nonce
            },
            success: function (response) {
                $btn.removeClass('loading').prop('disabled', false);
                if (response.success) {
                    pzhUpdateCartBadge(response.data.cart_count);
                    pzhToast(response.data.message);
                    // Refresh mini-cart if open
                    if ($('.cart-dropdown').hasClass('active')) {
                        pzhRefreshMiniCart();
                    }
                } else {
                    pzhToast(response.data.message, 'error');
                }
            },
            error: function () {
                $btn.removeClass('loading').prop('disabled', false);
                pzhToast('خطا در ارتباط با سرور.', 'error');
            }
        });
    });

    // Tabs Navigation - Smooth Scroll + Active State
    var $tabLinks = $('.tab-nav-link');
    var $tabSections = $('.single-product-section');
    var $tabsNav = $('#product-tabs-nav');

    if ($tabLinks.length && $tabSections.length) {
        // Click: smooth scroll to section
        $tabLinks.on('click', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            var $target = $(target);
            if ($target.length) {
                var offset = $tabsNav.length ? $tabsNav.height() + 110 : 100;
                $('html, body').animate({
                    scrollTop: $target.offset().top - offset
                }, 400);
            }
        });

        // Scroll: update active tab
        $(window).on('scroll', pzhDebounce(function () {
            var scrollPos = $(window).scrollTop() + ($tabsNav.length ? $tabsNav.height() + 130 : 120);

            $tabSections.each(function () {
                var $section = $(this);
                var top = $section.offset().top;
                var bottom = top + $section.outerHeight();

                if (scrollPos >= top && scrollPos < bottom) {
                    var id = '#' + $section.attr('id');
                    $tabLinks.removeClass('active');
                    $tabLinks.filter('[href="' + id + '"]').addClass('active');
                }
            });
        }, 100));
    }

    // FAQ Accordion
    $(document).on('click', '.faq-item__question', function () {
        var $faqItem = $(this).closest('.faq-item');
        $faqItem.toggleClass('open');
        // Close other FAQ items (accordion behavior)
        $faqItem.siblings('.faq-item.open').removeClass('open');
    });

    // Compare Button - Store product IDs in localStorage
    $(document).on('click', '.compare-btn', function () {
        var productId = $(this).data('product-id').toString();
        var compareList = JSON.parse(localStorage.getItem('pzh_compare') || '[]');

        if (compareList.indexOf(productId) === -1) {
            if (compareList.length >= 4) {
                pzhToast('حداکثر ۴ محصول قابل مقایسه است.', 'error');
                return;
            }
            compareList.push(productId);
            $(this).addClass('active');
            pzhToast('محصول به لیست مقایسه اضافه شد.');
        } else {
            compareList = compareList.filter(function (id) { return id !== productId; });
            $(this).removeClass('active');
            pzhToast('محصول از لیست مقایسه حذف شد.');
        }

        localStorage.setItem('pzh_compare', JSON.stringify(compareList));
        updateCompareCount(compareList.length);
    });

    // Initialize compare buttons state
    var compareList = JSON.parse(localStorage.getItem('pzh_compare') || '[]');
    compareList.forEach(function (id) {
        $('.compare-btn[data-product-id="' + id + '"]').addClass('active');
    });

    function updateCompareCount(count) {
        // Update compare count in header/nav if exists
        var $compareBadge = $('.compare-count');
        if ($compareBadge.length) {
            if (count > 0) {
                $compareBadge.text(count).show();
            } else {
                $compareBadge.hide();
            }
        }
    }

    // Sticky Add-to-Cart Bar (Mobile)
    var $stickyBar = $('.sticky-add-to-cart');
    if ($stickyBar.length) {
        var productInfoBottom = $('.product-info').offset().top + $('.product-info').outerHeight();
        $(window).on('scroll', pzhDebounce(function () {
            if ($(window).scrollTop() > productInfoBottom) {
                $stickyBar.addClass('visible');
            } else {
                $stickyBar.removeClass('visible');
            }
        }, 50));
    }

    // Related Products Swiper
    if ($('.related-products-swiper').length) {
        new Swiper('.related-products-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            navigation: {
                nextEl: '.related-next',
                prevEl: '.related-prev',
            },
            breakpoints: {
                576: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                992: { slidesPerView: 3 },
                1200: { slidesPerView: 4 },
            },
        });
    }

    // Variation swatch clicks (for custom color/image swatches)
    $(document).on('click', '.variation-swatches .swatch-option', function () {
        var $swatch = $(this);
        var $select = $('#' + $swatch.data('attribute'));
        var value = $swatch.data('value');

        $swatch.siblings().removeClass('selected');
        $swatch.addClass('selected');

        if ($select.length) {
            $select.val(value).trigger('change');
        }
    });

    // ========================================================================
    // Scroll Animations
    // ========================================================================
    $(window).on('scroll', function () {
        // Features section animation
        var $featuresRow = $('.home-features .items');
        if ($featuresRow.length) {
            var featuresPos = $featuresRow.offset().top;
            if (window.scrollY >= (featuresPos - 700)) {
                $featuresRow.addClass('animated');
            }
        }
    });

}); // end document ready

// ========================================================================
// WooCommerce Cart Fragments - Auto-update cart count
// ========================================================================
$(document).on('added_to_cart removed_from_cart updated_cart_totals', function () {
    $.ajax({
        url: pzh_options.ajax_url,
        type: 'POST',
        data: {
            action: 'pzh_get_cart_data',
            nonce: pzh_options.nonce
        },
        success: function (response) {
            if (response.success) {
                pzhUpdateCartBadge(response.data.count);
            }
        }
    });
});
