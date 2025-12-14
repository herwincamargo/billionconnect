<?php
/**
 * Custom Product Display
 * 
 * Creates custom interface for eSIM products with tabs and dynamic pricing
 * 
 * @package Connectivity_Plans
 * @version 5.0.0
 */

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_Product_Display {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Replace default add to cart with custom interface
        add_action('woocommerce_single_product_summary', array($this, 'maybe_remove_default'), 1);
        add_action('woocommerce_before_add_to_cart_form', array($this, 'render_custom_interface'), 5);
        
        // Hide default variations
        add_filter('woocommerce_is_purchasable', array($this, 'make_purchasable'), 10, 2);
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_cp_get_plan_price', array($this, 'ajax_get_price'));
        add_action('wp_ajax_nopriv_cp_get_plan_price', array($this, 'ajax_get_price'));
        add_action('wp_ajax_cp_add_to_cart', array($this, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_cp_add_to_cart', array($this, 'ajax_add_to_cart'));
    }
    
    /**
     * Remove default add to cart for our products
     */
    public function maybe_remove_default() {
        global $product;
        
        if ($this->is_connectivity_product($product)) {
            remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
            remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
        }
    }
    
    /**
     * Render custom interface
     */
    public function render_custom_interface() {
        global $product;
        
        if (!$this->is_connectivity_product($product)) {
            return;
        }
        
        // Get all variations
        $variations = $this->get_organized_variations($product);
        
        if (empty($variations)) {
            return;
        }
        
        include CONNECTIVITY_PLANS_PLUGIN_DIR . 'templates/custom-product-interface.php';
    }
    
    /**
     * Check if product is ours
     */
    private function is_connectivity_product($product) {
        if (!$product || !is_object($product)) {
            return false;
        }
        
        $categories = $product->get_category_ids();
        foreach ($categories as $cat_id) {
            $category = get_term($cat_id, 'product_cat');
            if ($category && in_array($category->slug, array('esim-internacional', 'recargas-datos'))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get variations organized by plan type and data
     */
    private function get_organized_variations($product) {
        $variations = $product->get_available_variations();
        $organized = array(
            'daily' => array(), // planType = 1
            'total' => array()  // planType = 0
        );
        
        foreach ($variations as $variation) {
            $variation_obj = wc_get_product($variation['variation_id']);
            if (!$variation_obj) continue;
            
            $plan_type = $variation_obj->get_meta('_bc_plan_type');
            $sku_id = $variation_obj->get_meta('_bc_sku_id');
            $copies = $variation_obj->get_meta('_bc_copies');
            $retail_price = $variation_obj->get_meta('_bc_retail_price');
            
            // Extract data amount from attributes
            $attributes = $variation_obj->get_attributes();
            $data_label = $attributes['pa_datos'] ?? '';
            
            $type_key = $plan_type === '1' ? 'daily' : 'total';
            
            if (!isset($organized[$type_key][$data_label])) {
                $organized[$type_key][$data_label] = array(
                    'label' => $this->format_data_label($data_label),
                    'durations' => array()
                );
            }
            
            $organized[$type_key][$data_label]['durations'][$copies] = array(
                'variation_id' => $variation['variation_id'],
                'sku_id' => $sku_id,
                'copies' => $copies,
                'price' => $retail_price,
                'label' => intval($copies) === 1 ? '1 día' : intval($copies) . ' días'
            );
        }
        
        // Sort durations numerically (smallest to largest)
        foreach ($organized as $type => $data_plans) {
            foreach ($data_plans as $data_key => $plan_data) {
                ksort($organized[$type][$data_key]['durations'], SORT_NUMERIC);
            }
        }
        
        // Sort data packages by size (smallest to largest)
        foreach ($organized as $type => $data_plans) {
            uksort($organized[$type], array($this, 'sort_data_packages'));
        }
        
        return $organized;
    }
    
    /**
     * Sort data packages by size
     */
    private function sort_data_packages($a, $b) {
        // Extract numbers and units
        preg_match('/(\d+(\.\d+)?)\s*(KB|MB|GB)/i', $a, $matches_a);
        preg_match('/(\d+(\.\d+)?)\s*(KB|MB|GB)/i', $b, $matches_b);
        
        if (!isset($matches_a[1]) || !isset($matches_b[1])) {
            return 0;
        }
        
        $num_a = floatval($matches_a[1]);
        $num_b = floatval($matches_b[1]);
        
        $unit_a = isset($matches_a[3]) ? strtoupper($matches_a[3]) : '';
        $unit_b = isset($matches_b[3]) ? strtoupper($matches_b[3]) : '';
        
        // Convert to bytes
        $multipliers = array('KB' => 1024, 'MB' => 1048576, 'GB' => 1073741824);
        $bytes_a = $num_a * ($multipliers[$unit_a] ?? 1);
        $bytes_b = $num_b * ($multipliers[$unit_b] ?? 1);
        
        return $bytes_a <=> $bytes_b;
    }
    
    /**
     * Format data label for display
     */
    private function format_data_label($label) {
        // Remove 'pa_' prefix and sanitization
        $clean = str_replace(array('pa_', '-'), array('', ' '), $label);
        return ucfirst(trim($clean));
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!is_product()) {
            return;
        }
        
        wp_enqueue_style(
            'connectivity-plans-product',
            CONNECTIVITY_PLANS_PLUGIN_URL . 'assets/css/product-interface.css',
            array(),
            CONNECTIVITY_PLANS_VERSION
        );
        
        wp_enqueue_script(
            'connectivity-plans-product',
            CONNECTIVITY_PLANS_PLUGIN_URL . 'assets/js/product-interface.js',
            array('jquery'),
            CONNECTIVITY_PLANS_VERSION,
            true
        );
        
        wp_localize_script('connectivity-plans-product', 'cpData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cp_product_nonce')
        ));
    }
    
    /**
     * AJAX: Get price for selection
     */
    public function ajax_get_price() {
        check_ajax_referer('cp_product_nonce', 'nonce');
        
        $variation_id = intval($_POST['variation_id'] ?? 0);
        
        if (!$variation_id) {
            wp_send_json_error('Invalid variation');
        }
        
        $variation = wc_get_product($variation_id);
        
        if (!$variation) {
            wp_send_json_error('Variation not found');
        }
        
        $price = $variation->get_price();
        
        wp_send_json_success(array(
            'price' => $price,
            'formatted_price' => wc_price($price)
        ));
    }
    
    /**
     * AJAX: Add to cart
     */
    public function ajax_add_to_cart() {
        check_ajax_referer('cp_product_nonce', 'nonce');
        
        $variation_id = intval($_POST['variation_id'] ?? 0);
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if (!$variation_id || !$product_id) {
            wp_send_json_error('Invalid product data');
        }
        
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
        
        if ($cart_item_key) {
            wp_send_json_success(array(
                'message' => '¡Producto agregado al carrito!',
                'cart_url' => wc_get_cart_url()
            ));
        } else {
            wp_send_json_error('Error al agregar al carrito');
        }
    }
    
    /**
     * Make product purchasable
     */
    public function make_purchasable($purchasable, $product) {
        if ($this->is_connectivity_product($product)) {
            return true;
        }
        return $purchasable;
    }
}

// Initialize
Connectivity_Plans_Product_Display::get_instance();
