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
            this.allVariations = this.buildVariationsMap();
        },
        
        buildVariationsMap: function() {
            const map = {
                daily: {},
                total: {}
            };
            
            // Build from daily plans
            $('.cp-plan-section[data-plan-type="daily"] .cp-data-btn').each(function() {
                const dataKey = $(this).data('data-key');
                const planType = 'daily';
                
                if (!map[planType][dataKey]) {
                    map[planType][dataKey] = {};
                }
                
                // Get durations for this data type from buttons
                $('.cp-plan-section[data-plan-type="daily"] .cp-duration-btn').each(function() {
                    const copies = $(this).data('copies');
                    map[planType][dataKey][copies] = {
                        variation_id: $(this).data('variation-id'),
                        price: $(this).data('price')
                    };
                });
            });
            
            // Build from total plans
            $('.cp-plan-section[data-plan-type="total"] .cp-data-btn').each(function() {
                const dataKey = $(this).data('data-key');
                const planType = 'total';
                
                if (!map[planType][dataKey]) {
                    map[planType][dataKey] = {};
                }
            });
            
            return map;
        },
        
        bindEvents: function() {
            if (!this.$container.length) return;
            
            this.$tabs.on('click', this.handleTabClick.bind(this));
            this.$dataButtons.on('click', this.handleDataClick.bind(this));
            this.$durationButtons.on('click', this.handleDurationButtonClick.bind(this));
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
            // This is a simplified version
            // In reality, we need to rebuild options based on selected data
            
            if (planType === 'daily') {
                // For dropdown, keep same options but update variation IDs
                // (In real implementation, fetch from variations map)
            } else {
                // For buttons, update based on selected data
                // (In real implementation, rebuild buttons)
            }
        },
        
        
        handleDurationButtonClick: function(e) {
            const $btn = $(e.currentTarget);
            
            // Update active state
            this.$durationButtons.removeClass('active');
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
            const activeTab = this.$tabs.filter('.active').data('type') || 'daily';
            const $activeDurationBtn = this.$durationButtons.filter('.active');
            
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
