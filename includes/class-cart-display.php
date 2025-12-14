<?php
/**
 * Cart Display Enhancements
 * 
 * Muestra información clara en el carrito sobre qué está comprando
 * 
 * @package Connectivity_Plans
 */

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_Cart_Display {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Mostrar información detallada en el carrito
        add_filter('woocommerce_get_item_data', array($this, 'display_cart_item_data'), 10, 2);
        
        // Agregar meta data al item de orden
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
        
        // Mostrar en página de orden (thank you page)
        add_filter('woocommerce_order_item_name', array($this, 'display_order_item_name'), 10, 2);
    }
    
    /**
     * Mostrar información en el carrito
     */
    public function display_cart_item_data($item_data, $cart_item) {
        $product = $cart_item['data'];
        
        if (!$product) {
            return $item_data;
        }
        
        // Verificar si es producto eSIM
        $is_esim = get_post_meta($product->get_id(), '_is_esim_product', true);
        
        if ($is_esim !== 'yes') {
            return $item_data;
        }
        
        // Obtener configuración del cliente
        $sku_id = $cart_item['esim_sku_id'] ?? '';
        $plan_type = $cart_item['esim_plan_type'] ?? 'total'; // 'total' o 'daily'
        $data_amount = $cart_item['esim_data_amount'] ?? '';
        $days = $cart_item['esim_days'] ?? '';
        
        if (empty($data_amount) || empty($days)) {
            return $item_data;
        }
        
        // INFORMACIÓN CLARA
        if ($plan_type === 'daily') {
            // Pase Diario
            $total_data = floatval($data_amount) * intval($days);
            
            $item_data[] = array(
                'key' => '📊 Plan',
                'value' => '<strong>Pase Diario</strong>'
            );
            
            $item_data[] = array(
                'key' => '🔄 Datos Diarios',
                'value' => '<strong>' . $data_amount . ' GB cada día</strong>'
            );
            
            $item_data[] = array(
                'key' => '📅 Duración',
                'value' => '<strong>' . $days . ' días</strong>'
            );
            
            $item_data[] = array(
                'key' => '💾 Total Disponible',
                'value' => '<strong>' . $total_data . ' GB</strong> (' . $data_amount . 'GB × ' . $days . ' días)'
            );
            
            $item_data[] = array(
                'key' => '⏰ Renovación',
                'value' => 'Cada día a las 00:00 recibes ' . $data_amount . 'GB frescos'
            );
            
            $item_data[] = array(
                'key' => '✅ Qué recibes',
                'value' => 'eSIM con ' . $data_amount . 'GB diarios durante ' . $days . ' días. Los datos no usados NO se acumulan.'
            );
            
        } else {
            // Paquete Total
            $item_data[] = array(
                'key' => '📊 Plan',
                'value' => '<strong>Paquete Total</strong>'
            );
            
            $item_data[] = array(
                'key' => '💾 Datos',
                'value' => '<strong>' . $data_amount . ' GB totales</strong>'
            );
            
            $item_data[] = array(
                'key' => '📅 Duración',
                'value' => '<strong>' . $days . ' días</strong>'
            );
            
            $item_data[] = array(
                'key' => '⏰ Validez',
                'value' => 'Activo por ' . $days . ' días desde la activación'
            );
            
            $item_data[] = array(
                'key' => '✅ Qué recibes',
                'value' => 'eSIM con ' . $data_amount . 'GB para usar como quieras durante ' . $days . ' días'
            );
        }
        
        // Información de entrega
        $item_data[] = array(
            'key' => '📧 Entrega',
            'value' => '<strong>Código QR por email</strong> (instantáneo)'
        );
        
        $item_data[] = array(
            'key' => '🚀 Activación',
            'value' => 'Inmediata al escanear el QR'
        );
        
        return $item_data;
    }
    
    /**
     * Agregar meta data a la orden
     */
    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        // Guardar configuración del cliente
        if (isset($values['esim_sku_id'])) {
            $item->add_meta_data('_esim_sku_id', $values['esim_sku_id']);
        }
        
        if (isset($values['esim_plan_type'])) {
            $item->add_meta_data('_esim_plan_type', $values['esim_plan_type']);
        }
        
        if (isset($values['esim_data_amount'])) {
            $item->add_meta_data('_esim_data_amount', $values['esim_data_amount']);
        }
        
        if (isset($values['esim_days'])) {
            $item->add_meta_data('_esim_days', $values['esim_days']);
        }
        
        // Información legible
        $plan_type = $values['esim_plan_type'] ?? 'total';
        $data = $values['esim_data_amount'] ?? '';
        $days = $values['esim_days'] ?? '';
        
        if ($plan_type === 'daily') {
            $description = $data . 'GB/día durante ' . $days . ' días (Total: ' . (floatval($data) * intval($days)) . 'GB)';
        } else {
            $description = $data . 'GB válidos por ' . $days . ' días';
        }
        
        $item->add_meta_data('Plan eSIM', $description, true);
        $item->add_meta_data('Entrega', 'Código QR por email', true);
        $item->add_meta_data('Activación', 'Inmediata', true);
    }
    
    /**
     * Mostrar en página de orden
     */
    public function display_order_item_name($item_name, $item) {
        $plan_description = $item->get_meta('Plan eSIM');
        
        if (!empty($plan_description)) {
            $item_name .= '<br><small style="color: #666;">📱 ' . esc_html($plan_description) . '</small>';
        }
        
        return $item_name;
    }
}
