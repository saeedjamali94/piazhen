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

        // Hide search results / close search panel on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.pzh_search_box').length) {
                $searchResults.removeClass('active');
            }
            if (!$(e.target).closest('.search-wrapper').length) {
                $('.search-wrapper').removeClass('open');
            }
        });

        // Show results on focus if has value
        $searchInput.on('focus', function () {
            if ($(this).val().trim().length >= 2 && $searchResults.html().trim()) {
                $searchResults.addClass('active');
            }
        });

        // Toggle the header search panel
        $('.searchToggle').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $wrapper = $(this).closest('.search-wrapper');
            $wrapper.toggleClass('open');
            if ($wrapper.hasClass('open')) {
                $wrapper.find('input[type="search"]').focus();
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
    // Product Filters (Archive Page) — custom AJAX, no plugins
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
        var $priceDisplay= $filterForm.find('.price-range__display');
        var sliderMinAttr = parseFloat($sliderMin.attr('min')) || 0;
        var sliderMaxAttr = parseFloat($sliderMax.attr('max')) || 50000000;
        var sliderRange   = sliderMaxAttr - sliderMinAttr || 1;
        var priceTimeout;

        // CSS color values for the track (must match SCSS)
        var $grayTrack   = '#dee2e6';   // $gray-300
        var $activeTrack = '#FBCA38';   // $siteYellow

        // Clamp a value between min and max
        function clamp(val, lo, hi) {
            return Math.max(lo, Math.min(hi, val));
        }

        // Update the colored track between the two thumbs
        function updateSliderTrack() {
            var minV = parseFloat($sliderMin.val()) || sliderMinAttr;
            var maxV = parseFloat($sliderMax.val()) || sliderMaxAttr;
            if (minV > maxV) { var t = minV; minV = maxV; maxV = t; }
            var pctMin = clamp(((minV - sliderMinAttr) / sliderRange) * 100, 0, 100);
            var pctMax = clamp(((maxV - sliderMinAttr) / sliderRange) * 100, 0, 100);
            $sliderWrap.css('background',
                'linear-gradient(to left, ' +
                $grayTrack + ' 0%, ' + $grayTrack + ' ' + (100 - pctMax) + '%, ' +
                $activeTrack + ' ' + (100 - pctMax) + '%, ' + $activeTrack + ' ' + (100 - pctMin) + '%, ' +
                $grayTrack + ' ' + (100 - pctMin) + '%, ' + $grayTrack + ' 100%)'
            );
        }

        // "از X تا Y تومان" label under the slider
        function updatePriceDisplay() {
            if (!$priceDisplay.length) return;
            var minV = parseFloat($priceMin.val()) || sliderMinAttr;
            var maxV = parseFloat($priceMax.val()) || sliderMaxAttr;
            var fmt = function (v) { return Number(v).toLocaleString('fa-IR'); };
            $priceDisplay.text('از ' + fmt(minV) + ' تا ' + fmt(maxV) + ' تومان');
        }

        // Sync number inputs → range sliders
        function syncSlidersFromInputs() {
            var minV = clamp(parseFloat($priceMin.val()) || sliderMinAttr, sliderMinAttr, sliderMaxAttr);
            var maxV = clamp(parseFloat($priceMax.val()) || sliderMaxAttr, sliderMinAttr, sliderMaxAttr);
            if (minV > maxV) { var t = minV; minV = maxV; maxV = t; }
            $sliderMin.val(minV);
            $sliderMax.val(maxV);
            updateSliderTrack();
            updatePriceDisplay();
        }

        // Sync range sliders → number inputs
        function syncInputsFromSliders() {
            var minV = clamp(parseFloat($sliderMin.val()) || sliderMinAttr, sliderMinAttr, sliderMaxAttr);
            var maxV = clamp(parseFloat($sliderMax.val()) || sliderMaxAttr, sliderMinAttr, sliderMaxAttr);
            if (parseFloat($sliderMin.val()) > parseFloat($sliderMax.val())) {
                $sliderMin.val(maxV);
                $sliderMax.val(minV);
                var t = minV; minV = maxV; maxV = t;
            }
            $priceMin.val(minV || '');
            $priceMax.val(maxV >= sliderMaxAttr ? '' : maxV);
            updateSliderTrack();
            updatePriceDisplay();
        }

        // Trigger filter after price change (debounced)
        function triggerPriceFilter() {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(function () { pzhApplyFilters(1, false); }, 500);
        }

        // Slider / input listeners
        if ($sliderMin.length) $sliderMin.on('input', function () { syncInputsFromSliders(); triggerPriceFilter(); });
        if ($sliderMax.length) $sliderMax.on('input', function () { syncInputsFromSliders(); triggerPriceFilter(); });
        if ($priceMin.length)   $priceMin.on('input', function () { syncSlidersFromInputs(); triggerPriceFilter(); });
        if ($priceMax.length)   $priceMax.on('input', function () { syncSlidersFromInputs(); triggerPriceFilter(); });

        // Initial track + display render
        if ($sliderMin.length && $sliderMax.length) updateSliderTrack();
        updatePriceDisplay();

        // --- Collect current filter state from the sidebar inputs ---
        function collectFilters() {
            var attributes = {};
            $filterForm.find('input[id^="attr_"][type="checkbox"]:checked').each(function () {
                var name = $(this).attr('name'); // e.g. "attr_pa_color[]"
                if (name) {
                    var cleanName = name.replace('[]', '');
                    if (!attributes[cleanName]) attributes[cleanName] = [];
                    attributes[cleanName].push($(this).val());
                }
            });

            return {
                sort:       $filterForm.find('input[name="sort"]:checked').val() || 'popularity',
                brands:     $filterForm.find('input[name="brands[]"]:checked').map(function () { return $(this).val(); }).get(),
                in_stock:   $filterForm.find('input[name="in_stock"]:checked').val() || '',
                min_price:  $priceMin.val() || 0,
                max_price:  $priceMax.val() || 0,
                attributes: attributes
            };
        }

        // --- Keep the URL in sync with the active filters (deep-linkable state) ---
        function syncUrl(page) {
            try {
                var f = collectFilters();
                var params = new URLSearchParams();
                $.each(f.brands, function (_, v) { params.append('brands[]', v); });
                $.each(f.attributes, function (tax, slugs) {
                    $.each(slugs, function (_, s) { params.append('attr_' + tax + '[]', s); });
                });
                if (f.min_price) params.set('min_price', f.min_price);
                if (f.max_price) params.set('max_price', f.max_price);
                if (f.in_stock) params.set('in_stock', '1');
                if (f.sort && f.sort !== 'popularity') params.set('sort', f.sort);
                if (page && page > 1) params.set('page', page);

                var qs = params.toString();
                var url = window.location.pathname + (qs ? '?' + qs : '');
                window.history.replaceState(null, '', url);
            } catch (ex) {}
        }

        // --- Apply filters via AJAX ---
        function pzhApplyFilters(page, scrollToGrid) {
            page = page || 1;
            var f = collectFilters();
            var perPage = parseInt($filterForm.data('per-page'), 10) || 12;
            var categoryId = $filterForm.data('category-id') || '';

            $.ajax({
                url: pzh_options.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'pzh_filter_products',
                    page: page,
                    per_page: perPage,
                    sort: f.sort,
                    brands: f.brands,
                    attributes: f.attributes,
                    in_stock: f.in_stock,
                    min_price: f.min_price,
                    max_price: f.max_price,
                    category_id: categoryId,
                    nonce: pzh_options.nonce
                },
                beforeSend: function () {
                    if ($productsContainer.length) $productsContainer.addClass('loading');
                },
                success: function (response) {
                    if ($productsContainer.length) $productsContainer.removeClass('loading');
                    if (response && response.success) {
                        if ($productsContainer.length) $productsContainer.html(response.data.html);
                        if ($productsCount.length) {
                            $productsCount.text(Number(response.data.total).toLocaleString('fa-IR'));
                        }
                        syncUrl(page);
                        if (scrollToGrid && $productsContainer.length) {
                            $('html, body').animate({ scrollTop: $productsContainer.offset().top - 130 }, 250);
                        }
                    }
                },
                error: function () {
                    if ($productsContainer.length) $productsContainer.removeClass('loading');
                    pzhToast('خطا در ارتباط با سرور.', 'error');
                }
            });
        }

        // --- Checkbox / radio change → auto-apply (except sort radios inside sort-options handled below) ---
        $filterForm.on('change', 'input[type="checkbox"]', function () {
            pzhApplyFilters(1, false);
        });

        // --- Sort radio buttons ---
        $filterForm.on('change', 'input[name="sort"]', function () {
            pzhApplyFilters(1, true);
        });

        // --- APPLY button ---
        $filterForm.on('click', '.apply-filters-btn', function (e) {
            e.preventDefault();
            pzhApplyFilters(1, true);
        });

        // --- RESET / CLEAR ALL (works from sidebar and from empty-grid message) ---
        $(document).on('click', '.reset-filters-btn, .clear-all-filters', function (e) {
            e.preventDefault();
            $filterForm.find('input[type="checkbox"]').prop('checked', false);
            $filterForm.find('#price-min').val('');
            $filterForm.find('#price-max').val('');
            if ($sliderMin.length) $sliderMin.val(sliderMinAttr);
            if ($sliderMax.length) $sliderMax.val(sliderMaxAttr);
            if (typeof updateSliderTrack === 'function') updateSliderTrack();
            updatePriceDisplay();
            $filterForm.find('input[name="sort"][value="popularity"]').prop('checked', true);
            pzhApplyFilters(1, true);
        });

        // --- Pagination clicks (delegated) ---
        $(document).on('click', '.products-pagination__btn', function () {
            var page = $(this).data('page');
            if (!page) return;
            pzhApplyFilters(page, true);
        });

        // --- Mobile filter panel toggle ---
        var $filterAside = $filterForm.closest('aside');
        function isMobile() { return $(window).width() < 992; }

        // Hide the filter panel by default on mobile (desktop always visible)
        if (isMobile()) {
            $filterForm.hide();
        }
        $(window).on('resize', function () {
            if (isMobile()) {
                if (!$filterAside.hasClass('open')) $filterForm.hide();
            } else {
                $filterForm.show();
            }
        });

        $('.mobile-filters-toggle').on('click', function () {
            $filterAside.toggleClass('open');
            $filterForm.stop(true, true).slideToggle(250);
            $(this).toggleClass('active');
        });

        // --- Category tree expand/collapse ---
        $filterForm.on('click', '.category-tree__toggle', function () {
            var $btn = $(this);
            var $children = $btn.closest('.category-tree__item').find('> .category-tree__children');
            $children.slideToggle(200);
            $btn.toggleClass('open');
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

    // --- Gallery: vertical thumbnail strip + click-to-switch main image ---
    var $thumbsContainer = $('#product-thumbs');
    var $mainImage = document.getElementById('main-product-image');

    if ($thumbsContainer.length) {

        function updateMainImage($thumb) {
            if (!$thumb || !$thumb.length || !$mainImage) return;
            var fullSrc = $thumb.data('full');
            var zoomSrc = $thumb.data('zoom');
            if (fullSrc) {
                $mainImage.src = fullSrc;
                $mainImage.setAttribute('data-zoom', zoomSrc || fullSrc);
            }
            $thumbsContainer.find('.product-gallery__thumb').removeClass('active');
            $thumb.addClass('active');
        }

        $thumbsContainer.on('click', '.product-gallery__thumb', function () {
            updateMainImage($(this));
        });

        // Up/down scroll buttons for the vertical strip
        var $thumbsCol = $('.product-gallery__thumbs-col');
        $thumbsCol.find('.thumb-nav--up').on('click', function () {
            $thumbsContainer.stop(true, true).animate({ scrollTop: '-=140' }, 200);
        });
        $thumbsCol.find('.thumb-nav--down').on('click', function () {
            $thumbsContainer.stop(true, true).animate({ scrollTop: '+=140' }, 200);
        });
    }

    // --- Custom Variation Picker (AJAX — no plugin) ---
    var $variationPicker = $('.pzh-variation-picker');
    window.pzhVarState = { attributes: {}, variationId: 0 };

    if ($variationPicker.length) {
        var variationProductId = $variationPicker.data('product-id');
        var $priceBox = $('#product-price');
        var $stickyPrice = $('#sticky-price');
        var $availability = $('#product-availability');
        var $status = $variationPicker.find('.variation-picker__status');
        var basePriceHtml = $priceBox.length ? $priceBox.data('base-price') : '';

        function resetVariationState() {
            window.pzhVarState = { attributes: {}, variationId: 0 };
            $('#selected-variation-id').val('');
            if (basePriceHtml && $priceBox.length) $priceBox.html(basePriceHtml);
            if (basePriceHtml && $stickyPrice.length) $stickyPrice.html(basePriceHtml);
            $status.html('');
        }

        function collectSelectedAttributes() {
            var attrs = {};
            $variationPicker.find('.variation-chip.selected, .swatch-option.selected').each(function () {
                attrs[$(this).data('attr')] = $(this).data('value');
            });
            return attrs;
        }

        function allAttributesSelected() {
            return $variationPicker.find('.variation-group').length ===
                   $variationPicker.find('.variation-chip.selected, .swatch-option.selected').length;
        }

        // Chip / swatch selection
        $variationPicker.on('click', '.variation-chip, .swatch-option', function () {
            var $btn = $(this);
            var attrName = $btn.data('attr');

            // Deselect if clicking the selected option
            if ($btn.hasClass('selected')) {
                $btn.removeClass('selected');
            } else {
                // Only one option per attribute
                $variationPicker.find('[data-attr="' + attrName + '"]').removeClass('selected');
                $btn.addClass('selected');
            }

            // Update the selected label next to the attribute title
            var $group = $btn.closest('.variation-group');
            var selectedLabel = $group.find('.selected').length
                ? $group.find('.selected').first().attr('title') || $group.find('.selected').first().text().trim()
                : '';
            $group.find('.variation-group__selected').text(selectedLabel);

            var attrs = collectSelectedAttributes();
            window.pzhVarState.attributes = attrs;

            if (!allAttributesSelected()) {
                window.pzhVarState.variationId = 0;
                $('#selected-variation-id').val('');
                if (basePriceHtml && $priceBox.length) $priceBox.html(basePriceHtml);
                if (basePriceHtml && $stickyPrice.length) $stickyPrice.html(basePriceHtml);
                $status.html('');
                return;
            }

            // All attributes selected → find the matching variation
            $status.html('<span class="text-muted">در حال بررسی...</span>');
            $.ajax({
                url: pzh_options.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'pzh_get_variation_match',
                    product_id: variationProductId,
                    attributes: attrs,
                    nonce: pzh_options.nonce
                },
                success: function (resp) {
                    if (resp && resp.success) {
                        window.pzhVarState.variationId = resp.data.variation_id;
                        $('#selected-variation-id').val(resp.data.variation_id);
                        if (resp.data.price_html) {
                            if ($priceBox.length) $priceBox.html(resp.data.price_html);
                            if ($stickyPrice.length) $stickyPrice.html(resp.data.price_html);
                        }
                        if (resp.data.availability) {
                            var inStock = resp.data.in_stock;
                            var icon = inStock ? '<i class="fa-solid fa-circle-check"></i> ' : '<i class="fa-solid fa-circle-xmark"></i> ';
                            if ($availability.length) {
                                $availability.html(icon + resp.data.availability);
                                $availability.toggleClass('in-stock', inStock).toggleClass('out-of-stock', !inStock);
                            }
                            $status.html('<span class="' + (inStock ? 'text-success' : 'text-danger') + '">' + resp.data.availability + '</span>');
                        }
                        // Swap main image if the variation has its own image
                        if (resp.data.image && $mainImage) {
                            $mainImage.src = resp.data.image;
                            $mainImage.setAttribute('data-zoom', resp.data.image);
                            $thumbsContainer.find('.product-gallery__thumb').removeClass('active');
                        }
                    } else {
                        window.pzhVarState.variationId = 0;
                        $('#selected-variation-id').val('');
                        $status.html('<span class="text-danger">' + ((resp && resp.data && resp.data.message) || 'این ترکیب موجود نیست.') + '</span>');
                    }
                },
                error: function () {
                    $status.html('<span class="text-danger">خطا در ارتباط با سرور.</span>');
                }
            });
        });
    }

    // --- Quantity +/- Buttons ---
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

    // --- Add to Cart (simple + variable, AJAX) ---
    $(document).on('click', '.add-to-cart-single', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var qty = parseInt($('#single-qty').val(), 10) || 1;
        var isVariable = $btn.data('variable');
        var variationId = 0;
        var variation = {};

        if (isVariable) {
            variationId = parseInt($('#selected-variation-id').val(), 10) || 0;
            variation = window.pzhVarState.attributes || {};
            if (!variationId) {
                pzhToast('لطفاً مشخصات محصول را انتخاب کنید.', 'error');
                if ($('.pzh-variation-picker').length) {
                    $('html, body').animate({ scrollTop: $('.pzh-variation-picker').offset().top - 140 }, 300);
                }
                return;
            }
        }

        if (!productId) return;

        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: pzh_options.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'pzh_add_to_cart',
                product_id: productId,
                variation_id: variationId,
                variation: variation,
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

    // ========================================================================
    // Cart Page (AJAX qty / remove / coupon — no plugins)
    // ========================================================================
    var $cartPage = $('.pzh-cart-page');

    if ($cartPage.length) {

        // Send a cart action and refresh the page fragments
        function pzhCartAction(cartAction, params, toastMessage) {
            $.ajax({
                url: pzh_options.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: $.extend({
                    action: 'pzh_cart_ajax',
                    cart_action: cartAction,
                    nonce: pzh_options.nonce
                }, params),
                beforeSend: function () {
                    $cartPage.addClass('cart-loading');
                },
                success: function (response) {
                    $cartPage.removeClass('cart-loading');
                    if (response && response.success) {
                        pzhUpdateCartBadge(response.data.count);
                        pzhRefreshMiniCart();

                        if (response.data.is_empty) {
                            $cartPage.replaceWith(response.data.empty_html);
                            $cartPage = $('.pzh-cart-page'); // now empty
                        } else {
                            $cartPage.find('.pzh-cart-items__rows').html(response.data.items_html);
                            $cartPage.find('.pzh-cart-totals').html(response.data.totals_html);
                        }

                        var msg = response.data.message || toastMessage;
                        if (msg) pzhToast(msg);
                    } else {
                        var errMsg = (response && response.data && response.data.message) || 'خطا در به‌روزرسانی سبد خرید.';
                        pzhToast(errMsg, 'error');
                    }
                },
                error: function () {
                    $cartPage.removeClass('cart-loading');
                    pzhToast('خطا در ارتباط با سرور.', 'error');
                }
            });
        }

        // Quantity +/- buttons
        $cartPage.on('click', '.cart-qty__btn', function () {
            var $btn = $(this);
            var key = $btn.data('cart-key');
            var $input = $cartPage.find('.cart-qty__input[data-cart-key="' + key + '"]');
            var current = parseInt($input.val(), 10) || 1;
            var max = parseInt($input.attr('max'), 10) || 99;
            var next = $btn.hasClass('cart-qty__btn--plus')
                ? Math.min(current + 1, max)
                : Math.max(current - 1, 1);

            if (next === current) return;
            $input.val(next);
            pzhCartAction('set_qty', { cart_key: key, qty: next });
        });

        // Quantity input (debounced on change)
        var qtyTimeout;
        $cartPage.on('input', '.cart-qty__input', function () {
            var $input = $(this);
            var key = $input.data('cart-key');
            clearTimeout(qtyTimeout);
            qtyTimeout = setTimeout(function () {
                var qty = parseInt($input.val(), 10);
                if (qty > 0) {
                    pzhCartAction('set_qty', { cart_key: key, qty: qty });
                }
            }, 600);
        });

        // Remove item
        $cartPage.on('click', '.cart-item__remove', function () {
            var key = $(this).data('cart-key');
            pzhCartAction('remove', { cart_key: key });
        });

        // Apply coupon
        $cartPage.on('submit', '.coupon-form', function (e) {
            e.preventDefault();
            var code = $(this).find('input[name="coupon_code"]').val().trim();
            if (!code) return;
            pzhCartAction('coupon', { code: code });
            $(this).find('input[name="coupon_code"]').val('');
        });

        // Remove coupon
        $cartPage.on('click', '.applied-coupon__remove', function () {
            var code = $(this).data('code');
            pzhCartAction('remove_coupon', { code: code });
        });
    }

    // ========================================================================
    // Checkout — mobile: move the summary card above the form for readability
    // ========================================================================
    function pzhCheckoutSummaryOrder() {
        if ($(window).width() < 992) {
            var $layout = $('.pzh-checkout-layout');
            if ($layout.length && !$layout.hasClass('reordered')) {
                $layout.addClass('reordered');
                $layout.css('display', 'flex');
                $layout.css('flex-direction', 'column');
                $layout.find('.pzh-checkout-summary').css('order', '-1');
            }
        }
    }
    pzhCheckoutSummaryOrder();

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
