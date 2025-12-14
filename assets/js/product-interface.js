/**
 * Custom Product Interface JavaScript
 * Handles tab switching, selection, dynamic pricing, and add to cart
 */

(function($) {
    'use strict';
    
    const CPProduct = {
        
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initializePrice();
        },
        
        cacheDom: function() {
            this.$container = $('.cp-product-interface');
            if (!this.$container.length) return;
            
            this.$tabs = $('.cp-tab');
            this.$sections = $('.cp-plan-section');
            this.$dataButtons = $('.cp-data-btn');
            this.$durationButtons = $('.cp-duration-btn');
            this.$qtyMinus = $('.cp-qty-minus');
            this.$qtyPlus = $('.cp-qty-plus');
            this.$qtyInput = $('.cp-quantity-input');
            this.$addToCart = $('.cp-add-to-cart-btn');
            this.$priceDisplay = $('.cp-price-display');
            this.$selectedVariation = $('.cp-selected-variation');
            this.$currentPrice = $('.cp-current-price');
            this.$loading = $('.cp-loading');
            this.$message = $('.cp-message');
            
            this.productId = this.$container.data('product-id');
            this.allVariations = window.productVariations || { daily: {}, total: {} };
        },
        
        bindEvents: function() {
            if (!this.$container.length) return;
            
            this.$tabs.on('click', this.handleTabClick.bind(this));
            this.$container.on('click', '.cp-data-btn', this.handleDataClick.bind(this));
            this.$container.on('click', '.cp-duration-btn', this.handleDurationButtonClick.bind(this));
            this.$qtyMinus.on('click', this.decrementQuantity.bind(this));
            this.$qtyPlus.on('click', this.incrementQuantity.bind(this));
            this.$qtyInput.on('change', this.updatePrice.bind(this));
            this.$addToCart.on('click', this.addToCart.bind(this));
        },
        
        handleTabClick: function(e) {
            const $tab = $(e.currentTarget);
            const type = $tab.data('type');
            
            // Update tabs
            this.$tabs.removeClass('active');
            $tab.addClass('active');
            
            // Show/hide sections
            this.$sections.hide();
            $(`.cp-plan-section[data-plan-type="${type}"]`).show();
            
            // Reset and update selection
            this.resetSelection(type);
            this.updatePrice();
        },
        
        handleDataClick: function(e) {
            const $btn = $(e.currentTarget);
            const planType = $btn.data('plan-type');
            const dataKey = $btn.data('data-key');
            
            // Update active state
            $(`.cp-data-btn[data-plan-type="${planType}"]`).removeClass('active');
            $btn.addClass('active');
            
            // Update durations for this data type
            this.updateDurationsForData(planType, dataKey);
            this.updatePrice();
        },
        
        updateDurationsForData: function(planType, dataKey) {
            const durations = this.allVariations[planType][dataKey].durations;
            const $durationContainer = $(`.cp-plan-section[data-plan-type="${planType}"] .cp-duration-buttons`);
            
            $durationContainer.empty();

            if (durations && durations.length > 0) {
                durations.forEach((duration, index) => {
                    const $btn = $('<button>', {
                        type: 'button',
                        class: 'cp-duration-btn' + (index === 0 ? ' active' : ''),
                        'data-variation-id': duration.variation_id,
                        'data-price': duration.price,
                        'data-copies': duration.copies,
                        text: duration.copies
                    });
                    $durationContainer.append($btn);
                });
            }

        },
        
        
        handleDurationButtonClick: function(e) {
            const $btn = $(e.currentTarget);
            
            // Update active state
            $btn.siblings().removeClass('active');
            $btn.addClass('active');
            
            this.updatePrice();
        },
        
        incrementQuantity: function() {
            const currentQty = parseInt(this.$qtyInput.val()) || 1;
            const maxQty = parseInt(this.$qtyInput.attr('max')) || 99;
            
            if (currentQty < maxQty) {
                this.$qtyInput.val(currentQty + 1);
                this.updatePrice();
            }
        },
        
        decrementQuantity: function() {
            const currentQty = parseInt(this.$qtyInput.val()) || 1;
            const minQty = parseInt(this.$qtyInput.attr('min')) || 1;
            
            if (currentQty > minQty) {
                this.$qtyInput.val(currentQty - 1);
                this.updatePrice();
            }
        },
        
        getCurrentSelection: function() {
            const activeTab = this.$tabs.filter('.active').data('type') || (this.allVariations.daily ? 'daily' : 'total');
            const $activeSection = $(`.cp-plan-section[data-plan-type="${activeTab}"]`);
            const $activeDurationBtn = $activeSection.find('.cp-duration-btn.active');
            
            const variationId = $activeDurationBtn.data('variation-id');
            const price = $activeDurationBtn.data('price');
            
            return {
                variation_id: variationId,
                price: parseFloat(price) || 0,
                quantity: parseInt(this.$qtyInput.val()) || 1
            };
        },
        
        updatePrice: function() {
            const selection = this.getCurrentSelection();
            const totalPrice = selection.price * selection.quantity;
            
            // Update display
            this.$priceDisplay.text('USD ' + totalPrice.toFixed(2));
            this.$selectedVariation.val(selection.variation_id);
            this.$currentPrice.val(selection.price);
        },
        
        initializePrice: function() {
            this.updatePrice();
        },
        
        resetSelection: function(type) {
            if (type === 'daily') {
                $('.cp-data-btn[data-plan-type="daily"]').first().addClass('active');
                this.$durationSelect.prop('selectedIndex', 0);
            } else {
                $('.cp-data-btn[data-plan-type="total"]').first().addClass('active');
                this.$durationButtons.removeClass('active').first().addClass('active');
            }
            this.$qtyInput.val(1);
        },
        
        showLoading: function() {
            this.$loading.show();
        },
        
        hideLoading: function() {
            this.$loading.hide();
        },
        
        showMessage: function(message, type) {
            this.$message
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .slideDown();
            
            setTimeout(() => {
                this.$message.slideUp();
            }, 3000);
        },
        
        addToCart: function() {
            const selection = this.getCurrentSelection();
            
            if (!selection.variation_id) {
                this.showMessage('Por favor selecciona un plan', 'error');
                return;
            }
            
            this.showLoading();
            
            $.ajax({
                url: cpData.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_add_to_cart',
                    nonce: cpData.nonce,
                    product_id: this.productId,
                    variation_id: selection.variation_id,
                    quantity: selection.quantity
                },
                success: (response) => {
                    this.hideLoading();
                    
                    if (response.success) {
                        this.showMessage(
                            response.data.message + ' <a href="' + response.data.cart_url + '" style="color: #0073aa; font-weight: bold;">Ver Carrito →</a>', 
                            'success'
                        );
                        
                        // Update cart count if available
                        $(document.body).trigger('wc_fragment_refresh');
                        
                        // Optional: Auto-redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = response.data.cart_url;
                        }, 2000);
                    } else {
                        this.showMessage(response.data, 'error');
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showMessage('Error al agregar al carrito', 'error');
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        CPProduct.init();
    });
    
})(jQuery);
