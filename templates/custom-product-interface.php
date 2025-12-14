<?php
/**
 * Custom Product Interface Template
 * 
 * Displays custom tabs and selection interface
 * Variables available: $product, $variations
 */

if (!defined('ABSPATH')) exit;

$has_daily = !empty($variations['daily']);
$has_total = !empty($variations['total']);
$flag_url = get_post_meta($product->get_id(), '_esim_country_flag_url', true);
?>
<script>
    window.productVariations = <?php echo json_encode($variations); ?>;
</script>

<div class="cp-product-interface" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
    
    <div class="cp-product-header">
        <?php if ($flag_url): ?>
            <img src="<?php echo esc_url($flag_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" class="cp-country-flag">
        <?php endif; ?>
        <h2 class="cp-product-title"><?php echo esc_html($product->get_name()); ?></h2>
    </div>

    <?php if ($has_daily && $has_total): ?>
    <!-- Tabs for planType selection -->
    <div class="cp-tabs">
        <button type="button" class="cp-tab active" data-type="daily">Pase diario</button>
        <button type="button" class="cp-tab" data-type="total">Paquete Fijo</button>
    </div>
    <?php endif; ?>
    
    <div class="cp-selection-box">
        
        <!-- Daily Plans (planType = 1) -->
        <?php if ($has_daily): ?>
        <div class="cp-plan-section" data-plan-type="daily" style="<?php echo $has_daily && $has_total ? '' : 'display: block;'; ?>">
            
            <h3 class="cp-section-title">Seleccionar Plan de Datos</h3>
            
            <div class="cp-data-options">
                <?php 
                $first_daily = true;
                foreach ($variations['daily'] as $data_key => $data_plan): 
                    $first_duration = reset($data_plan['durations']);
                ?>
                <button 
                    type="button" 
                    class="cp-data-btn <?php echo $first_daily ? 'active' : ''; ?>"
                    data-plan-type="daily"
                    data-data-key="<?php echo esc_attr($data_key); ?>"
                    data-first-variation="<?php echo esc_attr($first_duration['variation_id']); ?>"
                >
                    <?php echo esc_html($data_plan['label']); ?>
                </button>
                <?php 
                    $first_daily = false;
                endforeach; 
                ?>
            </div>
            
            <h3 class="cp-section-title">Seleccionar Día(s)</h3>
            
            <div class="cp-duration-buttons">
                <?php 
                $first_data_plan = reset($variations['daily']);
                $first_duration_daily = true;
                foreach ($first_data_plan['durations'] as $duration): 
                ?>
                <button 
                    type="button" 
                    class="cp-duration-btn <?php echo $first_duration_daily ? 'active' : ''; ?>"
                    data-variation-id="<?php echo esc_attr($duration['variation_id']); ?>"
                    data-price="<?php echo esc_attr($duration['price']); ?>"
                    data-copies="<?php echo esc_attr($duration['copies']); ?>"
                >
                    <?php echo esc_html($duration['copies']); ?>
                </button>
                <?php 
                    $first_duration_daily = false;
                endforeach; 
                ?>
            </div>
            
        </div>
        <?php endif; ?>
        
        <!-- Total Plans (planType = 0) -->
        <?php if ($has_total): ?>
        <div class="cp-plan-section" data-plan-type="total" style="display: none;">
            
            <h3 class="cp-section-title">Seleccionar Plan de Datos</h3>
            
            <div class="cp-data-options cp-data-grid">
                <?php 
                $first_total = true;
                foreach ($variations['total'] as $data_key => $data_plan): 
                    $first_duration = reset($data_plan['durations']);
                ?>
                <button 
                    type="button" 
                    class="cp-data-btn <?php echo $first_total ? 'active' : ''; ?>"
                    data-plan-type="total"
                    data-data-key="<?php echo esc_attr($data_key); ?>"
                    data-first-variation="<?php echo esc_attr($first_duration['variation_id']); ?>"
                >
                    <?php echo esc_html($data_plan['label']); ?>
                </button>
                <?php 
                    $first_total = false;
                endforeach; 
                ?>
            </div>
            
            <h3 class="cp-section-title">Seleccionar Día(s)</h3>
            
            <div class="cp-duration-buttons">
                <?php 
                $first_data_plan = reset($variations['total']);
                $first_duration_total = true;
                foreach ($first_data_plan['durations'] as $duration): 
                ?>
                <button 
                    type="button" 
                    class="cp-duration-btn <?php echo $first_duration_total ? 'active' : ''; ?>"
                    data-variation-id="<?php echo esc_attr($duration['variation_id']); ?>"
                    data-price="<?php echo esc_attr($duration['price']); ?>"
                    data-copies="<?php echo esc_attr($duration['copies']); ?>"
                >
                    <?php echo esc_html($duration['copies']); ?>
                </button>
                <?php 
                    $first_duration_total = false;
                endforeach; 
                ?>
            </div>
            
        </div>
        <?php endif; ?>
        
        <!-- Quantity -->
        <h3 class="cp-section-title">Cantidad</h3>
        <div class="cp-quantity-wrapper">
            <button type="button" class="cp-qty-btn cp-qty-minus">−</button>
            <input type="number" class="cp-quantity-input" value="1" min="1" max="99">
            <button type="button" class="cp-qty-btn cp-qty-plus">+</button>
        </div>
        
        <!-- Add to Cart Button with Price -->
        <button type="button" class="cp-add-to-cart-btn">
            <span class="cp-price-display">USD 0.00</span>
            <span class="cp-btn-text">Compra</span>
        </button>
        
        <!-- Hidden data -->
        <input type="hidden" class="cp-selected-variation" value="">
        <input type="hidden" class="cp-current-price" value="0">
        
    </div>
    
    <!-- Loading overlay -->
    <div class="cp-loading" style="display: none;">
        <div class="cp-spinner"></div>
    </div>
    
</div>

<!-- Message container -->
<div class="cp-message" style="display: none;"></div>
