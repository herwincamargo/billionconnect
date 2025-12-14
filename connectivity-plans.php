<?php
/**
 * Plugin Name: Connectivity Plans - Billionconnect API (eSIM Only)
 * Plugin URI: https://heroesim.com
 * Description: Complete WooCommerce integration with Billionconnect API for eSIM sales ONLY. Filters out physical SIM cards automatically.
 * Version: 7.2.1
 * Author: HeroeSIM
 * Author URI: https://heroesim.com
 * Text Domain: connectivity-plans
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 * 
 * @package Connectivity_Plans
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin version
define('CONNECTIVITY_PLANS_VERSION', '7.0.0');

// Plugin paths
define('CONNECTIVITY_PLANS_PLUGIN_FILE', __FILE__);
define('CONNECTIVITY_PLANS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CONNECTIVITY_PLANS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Check if WooCommerce is active
 */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>Connectivity Plans</strong> requires WooCommerce to be installed and active.';
        echo '</p></div>';
    });
    return;
}

/**
 * Main Plugin Class
 */
class Connectivity_Plans {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files - ORDEN CORRECTO
     */
    private function load_dependencies() {
        // API Client PRIMERO (otros lo necesitan)
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-api-client.php';
        
        // Utilities
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-region-detector.php';
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-price-formatter.php';
        
        // Core classes
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-country-product-sync.php';
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-order-processor.php';
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-admin-interface.php';
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-product-display.php';
        
        // Additional features
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-cart-display.php';
        require_once CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-esim-recharge.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Declare HPOS compatibility
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
        
        // Admin interface
        if (is_admin()) {
            Connectivity_Plans_Admin_Interface::get_instance();
        }
        
        // Order processing
        Connectivity_Plans_Order_Processor::get_instance();
        
        // Cart display
        Connectivity_Plans_Cart_Display::get_instance();
        
        // eSIM Recharge system
        Connectivity_Plans_eSIM_Recharge::get_instance();
        
        // Product display (frontend)
        Connectivity_Plans_Product_Display::get_instance();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom attributes
        $this->create_custom_attributes();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        if (!get_option('connectivity_plans_api_key')) {
            update_option('connectivity_plans_api_key', '');
        }
        if (!get_option('connectivity_plans_api_secret')) {
            update_option('connectivity_plans_api_secret', '');
        }
        if (!get_option('connectivity_plans_api_url')) {
            update_option('connectivity_plans_api_url', 'https://api-flow.billionconnect.com/Flow/saler/2.0/invoke');
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create custom product attributes
     */
    private function create_custom_attributes() {
        $attributes = array(
            array(
                'name' => 'Data Amount',
                'slug' => 'data_amount',
                'type' => 'select'
            ),
            array(
                'name' => 'Duration (Days)',
                'slug' => 'duration_days',
                'type' => 'select'
            ),
            array(
                'name' => 'Plan Type',
                'slug' => 'plan_type',
                'type' => 'select'
            )
        );
        
        foreach ($attributes as $attr_data) {
            $attr_id = wc_attribute_taxonomy_id_by_name('pa_' . $attr_data['slug']);
            
            if (!$attr_id) {
                wc_create_attribute(array(
                    'name' => $attr_data['name'],
                    'slug' => $attr_data['slug'],
                    'type' => $attr_data['type'],
                    'order_by' => 'menu_order',
                    'has_archives' => false
                ));
                
                delete_transient('wc_attribute_taxonomies');
            }
        }
    }
    
    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }
}

/**
 * Initialize the plugin
 */
function connectivity_plans_init() {
    return Connectivity_Plans::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'connectivity_plans_init');
