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

    if ($filterForm.length) {

        // --- Price Range: dual-handle slider ↔ number inputs sync ---
        var $priceMin    = $filterForm.find('#price-min');
        var $priceMax    = $filterForm.find('#price-max');
        var $sliderMin   = $filterForm.find('#price-range-min');
        var $sliderMax   = $filterForm.find('#price-range-max');
        var $sliderWrap  = $filterForm.find('.price-range__slider-wrapper');
        var sliderMinAttr = parseFloat($sliderMin.attr('min')) || 0;
        var sliderMaxAttr = parseFloat($sliderMax.attr('max')) || 50000000;
        var sliderRange   = sliderMaxAttr - sliderMinAttr || 1;
        var priceTimeout;

        // Clamp a value between min and max
        function clamp(val, lo, hi) {
            return Math.max(lo, Math.min(hi, val));
        }

        // Update the colored track between the two thumbs
        function updateSliderTrack() {
            var minV = parseFloat($sliderMin.val()) || sliderMinAttr;
            var maxV = parseFloat($sliderMax.val()) || sliderMaxAttr;
            if (minV > maxV) { var t = minV; minV = maxV; maxV = t; }
            // Percentage relative to slider's actual range
            var pctMin = ((minV - sliderMinAttr) / sliderRange) * 100;
            var pctMax = ((maxV - sliderMinAttr) / sliderRange) * 100;
            pctMin = clamp(pctMin, 0, 100);
            pctMax = clamp(pctMax, 0, 100);
            $sliderWrap.css('background',
                'linear-gradient(to left, ' +
                $grayTrack + ' 0%, ' + $grayTrack + ' ' + (100 - pctMax) + '%, ' +
                $activeTrack + ' ' + (100 - pctMax) + '%, ' + $activeTrack + ' ' + (100 - pctMin) + '%, ' +
                $grayTrack + ' ' + (100 - pctMin) + '%, ' + $grayTrack + ' 100%)'
            );
        }

        // CSS color values for the track (must match SCSS)
        var $grayTrack   = '#dee2e6';   // $gray-300
        var $activeTrack = '#FBCA38';   // $siteYellow

        // Sync number inputs → range sliders
        function syncSlidersFromInputs() {
            var minV = parseFloat($priceMin.val()) || sliderMinAttr;
            var maxV = parseFloat($priceMax.val()) || sliderMaxAttr;
            minV = clamp(minV, sliderMinAttr, sliderMaxAttr);
            maxV = clamp(maxV, sliderMinAttr, sliderMaxAttr);
            if (minV > maxV) { var t = minV; minV = maxV; maxV = t; }
            $sliderMin.val(minV);
            $sliderMax.val(maxV);
            updateSliderTrack();
        }

        // Sync range sliders → number inputs
        function syncInputsFromSliders() {
            var minV = clamp(parseFloat($sliderMin.val()) || sliderMinAttr, sliderMinAttr, sliderMaxAttr);
            var maxV = clamp(parseFloat($sliderMax.val()) || sliderMaxAttr, sliderMinAttr, sliderMaxAttr);
            // Prevent cross-over
            if (parseFloat($sliderMin.val()) > parseFloat($sliderMax.val())) {
                $sliderMin.val(maxV);
                $sliderMax.val(minV);
                var t = minV; minV = maxV; maxV = t;
            }
            $priceMin.val(minV || '');
            $priceMax.val(maxV >= sliderMaxAttr ? '' : maxV);
            updateSliderTrack();
        }

        // Trigger filter after price change (debounced)
        function triggerPriceFilter() {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(function () { pzhApplyFilters(1); }, 500);
        }

        // Slider min handle → update min input + filter
        if ($sliderMin.length) {
            $sliderMin.on('input', function () {
                syncInputsFromSliders();
                triggerPriceFilter();
            });
        }

        // Slider max handle → update max input + filter
        if ($sliderMax.length) {
            $sliderMax.on('input', function () {
                syncInputsFromSliders();
                triggerPriceFilter();
            });
        }

        // Number input min → sync sliders + filter
        if ($priceMin.length) {
            $priceMin.on('input', function () {
                syncSlidersFromInputs();
                triggerPriceFilter();
            });
        }

        // Number input max → sync sliders + filter
        if ($priceMax.length) {
            $priceMax.on('input', function () {
                syncSlidersFromInputs();
                triggerPriceFilter();
            });
        }

        // Initial track render
        if ($sliderMin.length && $sliderMax.length) {
            updateSliderTrack();
        }

        // --- Checkbox / radio change → trigger filter ---
        $filterForm.on('change', 'input[type="checkbox"], input[type="radio"]', function () {
            pzhApplyFilters(1);
        });

        // --- Sort radio buttons ---
        $filterForm.on('change', 'input[name="sort"]', function () {
            pzhApplyFilters(1);
        });

        // --- APPLY button ---
        $filterForm.on('click', '.apply-filters-btn', function (e) {
            e.preventDefault();
            pzhApplyFilters(1);
        });

        // --- RESET button ---
        $filterForm.on('click', '.reset-filters-btn', function (e) {
            e.preventDefault();
            // Clear all checkboxes
            $filterForm.find('input[type="checkbox"]').prop('checked', false);
            // Clear price inputs
            $filterForm.find('#price-min').val('');
            $filterForm.find('#price-max').val('');
            if ($sliderMin.length) $sliderMin.val(sliderMinAttr);
            if ($sliderMax.length) $sliderMax.val(sliderMaxAttr);
            if (typeof updateSliderTrack === 'function') updateSliderTrack();
            // Reset sort to default
            $filterForm.find('input[name="sort"][value="newest"]').prop('checked', true);
            // Trigger filter
            pzhApplyFilters(1);
        });

        // --- Pagination clicks (delegated) ---
        $(document).on('click', '.products-pagination__btn', function () {
            var page = $(this).data('page');
            pzhApplyFilters(page);
            if ($productsContainer.length) {
                $('html, body').animate({ scrollTop: $productsContainer.offset().top - 100 }, 300);
            }
        });
    }

    function pzhApplyFilters(page) {
        page = page || 1;

        // Collect filter values
        var orderby  = $filterForm.find('input[name="sort"]:checked').val() || 'date';
        var brands   = $filterForm.find('input[name="brands[]"]:checked').map(function () { return $(this).val(); }).get();
        var minPrice = $filterForm.find('#price-min').val() || 0;
        var maxPrice = $filterForm.find('#price-max').val() || 0;
        var categoryId = $filterForm.data('category-id') || '';

        // Also collect dynamic attribute filters
        var attributes = {};
        $filterForm.find('input[id^="attr_"][type="checkbox"]:checked').each(function () {
            var name = $(this).attr('name'); // e.g. "attr_pa_color[]"
            if (name) {
                var cleanName = name.replace('[]', '');
                if (!attributes[cleanName]) attributes[cleanName] = [];
                attributes[cleanName].push($(this).val());
            }
        });

        // Determine sort order from orderby
        var order = 'DESC';
        if (orderby === 'price-asc')          { orderby = 'price'; order = 'ASC'; }
        else if (orderby === 'price-desc')    { orderby = 'price'; order = 'DESC'; }
        else if (orderby === 'newest')        { orderby = 'date'; order = 'DESC'; }
        else if (orderby === 'popularity' || orderby === 'most-sells') { orderby = 'popularity'; }
        else if (orderby === 'discount')      { orderby = 'discount'; }

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'pzh_filter_products',
                page: page,
                per_page: 12,
                orderby: orderby,
                order: order,
                brands: brands,
                min_price: minPrice,
                max_price: maxPrice,
                category_id: categoryId,
                attributes: attributes,
                nonce: pzh_options.nonce
            },
            beforeSend: function () {
                if ($productsContainer.length) $productsContainer.addClass('loading');
            },
            success: function (response) {
                if ($productsContainer.length) $productsContainer.removeClass('loading');
                if (response && response.success) {
                    if ($productsContainer.length) $productsContainer.html(response.data.html);
                    if ($productsCount.length) $productsCount.text(response.data.total);
                }
            },
            error: function () {
                if ($productsContainer.length) $productsContainer.removeClass('loading');
            }
        });
    }

    // ========================================================================
    // Variation Popup for Archive Products
    // ========================================================================
    var $modal    = $('#variation-modal');
    var $modalInner = $('#variation-modal-inner');

    // Open popup when clicking variable product's add-to-cart button
    $(document).on('click', '.product-card__add-to-cart--variable', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var productId = $btn.data('product-id');

        if (!productId || !$modal.length) return;

        // Show loading
        $modal.show();
        $modalInner.html('<div class="variation-modal__loading">لطفاً صبر کنید...</div>');

        // Fetch variation form via AJAX
        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'pzh_get_variation_popup',
                product_id: productId,
                nonce: pzh_options.nonce
            },
            success: function (response) {
                if (response && response.success) {
                    $modalInner.html(response.data.html);
                    // Init variation select change handlers
                    initVariationPopup(productId);
                } else {
                    $modalInner.html('<p class="text-center py-4 text-danger">خطا در بارگذاری اطلاعات محصول.</p>');
                }
            },
            error: function () {
                $modalInner.html('<p class="text-center py-4 text-danger">خطا در ارتباط با سرور.</p>');
            }
        });
    });

    // Close modal
    $(document).on('click', '.variation-popup__close', function () {
        $modal.hide();
        $modalInner.html('');
    });

    $modal.on('click', function (e) {
        if (e.target === this) {
            $modal.hide();
            $modalInner.html('');
        }
    });

    // Prevent close when clicking inside modal
    $(document).on('click', '.variation-modal', function (e) {
        e.stopPropagation();
    });

    function initVariationPopup(productId) {
        var $popup    = $('#variation-modal-inner');
        var $selects  = $popup.find('.variation-select');
        var $message  = $popup.find('.variation-popup__message');
        var $price    = $popup.find('.variation-popup__price');
        var $submit   = $popup.find('.variation-popup__submit');

        // When a variation select changes, find the matching variation
        $selects.on('change', function () {
            $message.html('');

            // Build selected attributes
            var attrs = {};
            $selects.each(function () {
                var attrName = $(this).data('attribute_name');
                var val = $(this).val();
                if (val) attrs[attrName] = val;
            });

            // Check if all attributes are selected
            var allSelected = true;
            $selects.each(function () {
                if (!$(this).val()) allSelected = false;
            });

            if (!allSelected) {
                $submit.prop('disabled', true).text('لطفاً همه گزینه‌ها را انتخاب کنید');
                return;
            }

            // Try to find matching variation via AJAX
            $.ajax({
                url: pzh_options.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'pzh_get_variation_match',
                    product_id: productId,
                    attributes: attrs,
                    nonce: pzh_options.nonce
                },
                success: function (resp) {
                    if (resp && resp.success) {
                        $message.html('<span class="text-success">' + resp.data.availability + '</span>');
                        if (resp.data.price_html) {
                            $price.html(resp.data.price_html);
                        }
                        $submit.prop('disabled', false)
                               .text('افزودن به سبد خرید')
                               .data('variation-id', resp.data.variation_id)
                               .data('attributes', JSON.stringify(attrs));
                    } else {
                        $message.html('<span class="text-danger">این ترکیب موجود نیست.</span>');
                        $submit.prop('disabled', true).text('ناموجود');
                    }
                }
            });
        });

        // Submit: add to cart from popup
        $submit.on('click', function () {
            var $btn = $(this);
            var variationId = $btn.data('variation-id');
            var attributes  = $btn.data('attributes') || '{}';
            var qty         = parseInt($popup.find('#popup-qty').val(), 10) || 1;

            if (typeof attributes === 'string') {
                try { attributes = JSON.parse(attributes); } catch(e) { attributes = {}; }
            }

            if (!variationId) {
                pzhToast('لطفاً گزینه‌های محصول را انتخاب کنید.', 'error');
                return;
            }

            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: pzh_options.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'pzh_add_to_cart',
                    product_id: productId,
                    variation_id: variationId,
                    variation: attributes,
                    quantity: qty,
                    nonce: pzh_options.nonce
                },
                success: function (resp) {
                    $btn.removeClass('loading').prop('disabled', false);
                    if (resp && resp.success) {
                        pzhUpdateCartBadge(resp.data.cart_count);
                        pzhToast(resp.data.message || 'محصول به سبد خرید اضافه شد.');
                        if ($('.cart-dropdown').hasClass('active')) pzhRefreshMiniCart();
                        $(document.body).trigger('added_to_cart');
                        // Close modal
                        $modal.hide();
                        $modalInner.html('');
                    } else {
                        pzhToast((resp && resp.data && resp.data.message) || 'خطا در افزودن به سبد خرید.', 'error');
                    }
                },
                error: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                    pzhToast('خطا در ارتباط با سرور.', 'error');
                }
            });
        });
    }

    // ========================================================================
    // Swiper Carousels Initialization
    // ========================================================================
    if (typeof Swiper !== 'undefined') {

        // Helper: generic carousel init
        function initSwiper(selector, options) {
            var el = document.querySelector(selector);
            if (el) { return new Swiper(selector, options); }
            return null;
        }

        // Most Selling Products Carousel (loop + 5 items desktop)
        initSwiper('.most-selling-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            loop: true,
            autoplay: { delay: 4000, disableOnInteraction: false },
            navigation: { nextEl: '.most-selling-next', prevEl: '.most-selling-prev' },
            breakpoints: {
                576:  { slidesPerView: 2 },
                768:  { slidesPerView: 3 },
                992:  { slidesPerView: 4 },
                1200: { slidesPerView: 5 },
            },
        });

        // Newest Products Carousel (2×2 grid, no loop – grid+loop conflict prevention)
        initSwiper('.newest-products-swiper', {
            slidesPerView: 2,
            spaceBetween: 16,
            loop: false,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: { nextEl: '.newest-next', prevEl: '.newest-prev' },
            grid: { rows: 2, fill: 'row' },
            breakpoints: {
                0:   { slidesPerView: 1, grid: { rows: 2, fill: 'row' } },
                768: { slidesPerView: 2, grid: { rows: 2, fill: 'row' } },
            },
        });

        // On Sale Products Carousel (2×2 grid)
        initSwiper('.on-sale-swiper', {
            slidesPerView: 2,
            spaceBetween: 16,
            loop: false,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: { nextEl: '.sale-next', prevEl: '.sale-prev' },
            grid: { rows: 2, fill: 'row' },
            breakpoints: {
                0:   { slidesPerView: 1, grid: { rows: 2, fill: 'row' } },
                768: { slidesPerView: 2, grid: { rows: 2, fill: 'row' } },
            },
        });

        // Instagram Carousel (1 item)
        initSwiper('.instagram-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: { delay: 4000, disableOnInteraction: false },
            navigation: { nextEl: '.instagram-next', prevEl: '.instagram-prev' },
        });

        // Blog Posts Carousel (1 item)
        initSwiper('.blog-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: { nextEl: '.blog-next', prevEl: '.blog-prev' },
        });

        // Categories Carousel (Archive page)
        initSwiper('.categories-swiper', {
            slidesPerView: 2,
            spaceBetween: 16,
            loop: false,
            navigation: { nextEl: '.categories-next', prevEl: '.categories-prev' },
            breakpoints: {
                576:  { slidesPerView: 3 },
                768:  { slidesPerView: 4 },
                992:  { slidesPerView: 6 },
                1200: { slidesPerView: 8 },
            },
        });

    } // end typeof Swiper check

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

    // --- Gallery Thumbnail Swiper + Click-to-Switch Main Image ---
    var $thumbSwiperEl = document.querySelector('.product-thumbs-swiper');
    var $mainImage = document.getElementById('main-product-image');

    if ($thumbSwiperEl && typeof Swiper !== 'undefined') {

        // Helper: update main image from a slide element
        function updateMainImage(slideEl) {
            if (!slideEl || !$mainImage) return;
            var fullSrc = slideEl.getAttribute('data-full');
            var zoomSrc = slideEl.getAttribute('data-zoom');
            if (fullSrc) {
                $mainImage.setAttribute('src', fullSrc);
                $mainImage.setAttribute('data-zoom', zoomSrc || fullSrc);
            }
            // Update active thumbnail class
            var $thumbs = document.querySelectorAll('.product-gallery__thumb');
            var idx = parseInt(slideEl.getAttribute('data-index'), 10);
            $thumbs.forEach(function (t, i) {
                t.classList.toggle('active', i === idx);
            });
        }

        var thumbSwiper = new Swiper('.product-thumbs-swiper', {
            slidesPerView: 4,
            spaceBetween: 10,
            slideToClickedSlide: true,
            watchSlidesProgress: true,
            navigation: {
                nextEl: '.thumb-nav--next',
                prevEl: '.thumb-nav--prev',
            },
            breakpoints: {
                576:  { slidesPerView: 5, spaceBetween: 10 },
                768:  { slidesPerView: 4, spaceBetween: 12 },
                992:  { slidesPerView: 5, spaceBetween: 12 },
            },
            on: {
                // Swiper's click handler – fires reliably even with touch
                click: function (swiper) {
                    var clickedSlide = swiper.clickedSlide;
                    if (clickedSlide) {
                        updateMainImage(clickedSlide);
                    }
                },
                // When slide changes by swipe, also update
                slideChange: function (swiper) {
                    var activeSlide = swiper.slides[swiper.activeIndex];
                    if (activeSlide) {
                        updateMainImage(activeSlide);
                    }
                }
            }
        });

        // Also handle keyboard Enter/Space on thumb slides
        $thumbSwiperEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                var activeSlide = thumbSwiper.slides[thumbSwiper.activeIndex];
                if (activeSlide) updateMainImage(activeSlide);
            }
        });

        // Fallback jQuery delegated click (for non-Swiper clicks / edge cases)
        $(document).on('click', '.product-gallery__thumb', function () {
            var $slide = $(this).closest('.swiper-slide');
            if ($slide.length) {
                updateMainImage($slide[0]);
            }
        });

    } else if ($mainImage) {
        // No Swiper – simple click fallback
        $(document).on('click', '.product-gallery__thumb', function () {
            var $thumb = $(this);
            var $slide = $thumb.closest('.swiper-slide');
            var fullSrc = $slide.length ? $slide.data('full') : $thumb.data('full');
            var zoomSrc = $slide.length ? $slide.data('zoom') : $thumb.data('zoom');
            if (fullSrc) {
                $mainImage.src = fullSrc;
                $mainImage.setAttribute('data-zoom', zoomSrc || fullSrc);
            }
            $('.product-gallery__thumb').removeClass('active');
            $thumb.addClass('active');
        });
    }

    // --- Quantity +/- Buttons (Simple Products) ---
    $(document).on('click', '.qty-btn', function (e) {
        e.preventDefault();
        var $input = $(this).siblings('.qty-input');
        var currentVal = parseInt($input.val(), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 999;
        var min = parseInt($input.attr('min'), 10) || 1;

        if ($(this).hasClass('qty-plus') && currentVal < max) {
            $input.val(currentVal + 1).trigger('change');
        } else if ($(this).hasClass('qty-minus') && currentVal > min) {
            $input.val(currentVal - 1).trigger('change');
        }
    });

    // --- Simple Product Add to Cart ---
    $(document).on('click', '.add-to-cart-single', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var qty = parseInt($('#single-qty').val(), 10) || 1;

        if (!productId) return;

        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'pzh_add_to_cart',
                product_id: productId,
                quantity: qty,
                nonce: pzh_options.nonce
            },
            success: function (response) {
                $btn.removeClass('loading').prop('disabled', false);
                if (response && response.success) {
                    pzhUpdateCartBadge(response.data.cart_count);
                    pzhToast(response.data.message || 'محصول به سبد خرید اضافه شد.');
                    if ($('.cart-dropdown').hasClass('active')) {
                        pzhRefreshMiniCart();
                    }
                    // Trigger WooCommerce cart fragments
                    $(document.body).trigger('added_to_cart');
                } else {
                    pzhToast((response && response.data && response.data.message) || 'خطا در افزودن به سبد خرید.', 'error');
                }
            },
            error: function () {
                $btn.removeClass('loading').prop('disabled', false);
                pzhToast('خطا در ارتباط با سرور.', 'error');
            }
        });
    });

    // --- Tabs Navigation: Smooth Scroll + Active State ---
    var $tabLinks = $('.tab-nav-link');
    var $tabSections = $('.single-product-section');
    var $tabsNav = $('#product-tabs-nav');

    if ($tabLinks.length && $tabSections.length) {
        // Click → smooth scroll
        $tabLinks.on('click', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            var $target = $(target);
            if ($target.length) {
                var navH = $tabsNav.length ? $tabsNav.outerHeight(true) : 0;
                var offset = navH + 100;
                $('html, body').animate({
                    scrollTop: $target.offset().top - offset
                }, 400);
            }
        });

        // Scroll → highlight active tab
        var scrollTimeout;
        $(window).on('scroll', function () {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function () {
                var navH = $tabsNav.length ? $tabsNav.outerHeight(true) : 0;
                var scrollPos = $(window).scrollTop() + navH + 130;

                var currentId = null;
                $tabSections.each(function () {
                    var $sec = $(this);
                    var top = $sec.offset().top;
                    if (scrollPos >= top) {
                        currentId = $sec.attr('id');
                    }
                });

                if (currentId) {
                    $tabLinks.removeClass('active');
                    $tabLinks.filter('[href="#' + currentId + '"]').addClass('active');
                }
            }, 50);
        });
    }

    // --- FAQ Accordion ---
    $(document).on('click', '.faq-item__question', function () {
        var $faqItem = $(this).closest('.faq-item');
        var wasOpen = $faqItem.hasClass('open');
        // Close all
        $faqItem.parent().find('.faq-item').removeClass('open');
        // Toggle clicked
        if (!wasOpen) {
            $faqItem.addClass('open');
        }
    });

    // --- Compare Button ---
    $(document).on('click', '.compare-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var productId = $btn.data('product-id').toString();
        var compareList = [];

        try {
            compareList = JSON.parse(localStorage.getItem('pzh_compare') || '[]');
        } catch (ex) {
            compareList = [];
        }

        if (compareList.indexOf(productId) === -1) {
            if (compareList.length >= 4) {
                pzhToast('حداکثر ۴ محصول قابل مقایسه است.', 'error');
                return;
            }
            compareList.push(productId);
            $btn.addClass('active');
            pzhToast('محصول به لیست مقایسه اضافه شد.');
        } else {
            compareList = compareList.filter(function (id) { return id !== productId; });
            $btn.removeClass('active');
            pzhToast('محصول از لیست مقایسه حذف شد.');
        }

        try {
            localStorage.setItem('pzh_compare', JSON.stringify(compareList));
        } catch (ex) {}
    });

    // Restore compare button states
    (function () {
        var compareList = [];
        try {
            compareList = JSON.parse(localStorage.getItem('pzh_compare') || '[]');
        } catch (ex) {}
        compareList.forEach(function (id) {
            $('.compare-btn[data-product-id="' + id + '"]').addClass('active');
        });
    })();

    // --- Related Products Swiper ---
    if ($('.related-products-swiper').length && typeof Swiper !== 'undefined') {
        new Swiper('.related-products-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            navigation: {
                nextEl: '.related-next',
                prevEl: '.related-prev',
            },
            breakpoints: {
                576:  { slidesPerView: 2 },
                768:  { slidesPerView: 3 },
                992:  { slidesPerView: 3 },
                1200: { slidesPerView: 4 },
            },
        });
    }

    // --- WooCommerce Variation Form: ensure it initializes ---
    // WooCommerce's add-to-cart-variation.js looks for .variations_form
    // If it hasn't initialized (e.g. AJAX-loaded content), trigger manually
    if ($('.variations_form').length && typeof wc_add_to_cart_variation_params !== 'undefined') {
        $('.variations_form').each(function () {
            if (!$(this).data('product_variations')) {
                $(this).wc_variation_form();
            }
        });
    }

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
