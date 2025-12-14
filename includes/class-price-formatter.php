<?php
/**
 * Price Formatter
 * 
 * Formats prices to show "Desde $X.XX" instead of ranges
 * 
 * @package Connectivity_Plans
 * @version 5.0.0
 */

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_Price_Formatter {
    
    public static function init() {
        add_filter('woocommerce_variable_price_html', array(__CLASS__, 'format_price'), 10, 2);
    }
    
    public static function format_price($price, $product) {
        // Check if it's our product
        $categories = $product->get_category_ids();
        $is_our_product = false;
        
        foreach ($categories as $cat_id) {
            $category = get_term($cat_id, 'product_cat');
            if ($category && in_array($category->slug, array('esim-internacional', 'recargas-datos'))) {
                $is_our_product = true;
                break;
            }
        }
        
        if (!$is_our_product) {
            return $price;
        }
        
        // Get minimum price
        $min_price = $product->get_variation_price('min', true);
        
        if ($min_price) {
            return sprintf(
                '<span class="price"><span style="font-size: 0.9em; color: #666;">Desde </span>%s</span>',
                wc_price($min_price)
            );
        }
        
        return $price;
    }
}
