<?php
/**
 * Order Processor v2.0
 * 
 * Procesa órdenes de WooCommerce y las envía a Billionconnect
 * Usa API F040 para crear la eSIM
 * 
 * @package Connectivity_Plans
 */

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_Order_Processor {
    
    private static $instance = null;
    private $api_client;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->api_client = new Connectivity_Plans_API_Client();
        
        // Procesar cuando la orden está completa (pagada)
        add_action('woocommerce_order_status_processing', array($this, 'process_order'), 10, 1);
        add_action('woocommerce_order_status_completed', array($this, 'process_order'), 10, 1);
        
        // Acciones administrativas
        add_action('woocommerce_order_actions', array($this, 'add_order_actions'));
        add_action('woocommerce_order_action_resend_esim_email', array($this, 'resend_esim_email'));
        add_action('woocommerce_order_action_query_esim_status', array($this, 'query_esim_status'));
    }
    
    /**
     * Procesar orden - ENVIAR A BILLIONCONNECT
     */
    public function process_order($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            error_log("Order #$order_id not found");
            return;
        }
        
        // Verificar si ya fue procesada
        if ($order->get_meta('_billionconnect_processed')) {
            error_log("Order #$order_id already processed");
            return;
        }
        
        // Obtener productos eSIM
        $esim_items = $this->get_esim_items($order);
        
        if (empty($esim_items)) {
            error_log("Order #$order_id has no eSIM products");
            return;
        }
        
        error_log("Processing Order #$order_id with " . count($esim_items) . " eSIM items");
        
        // Preparar sub-órdenes para F040
        $sub_orders = array();
        
        foreach ($esim_items as $item_id => $item_data) {
            $sub_order = array(
                'channelSubOrderId' => 'WC-' . $order_id . '-' . $item_id,
                'skuId' => $item_data['sku_id'],
                'quantity' => (string)$item_data['quantity'],
                'email' => $order->get_billing_email(),
                'emailLanguage' => '2' // English
            );
            
            // Agregar información adicional si está disponible
            if (!empty($item_data['estimated_use_time'])) {
                $sub_order['estimatedUseTime'] = $item_data['estimated_use_time'];
            }
            
            $sub_orders[] = $sub_order;
            
            error_log("Sub-order prepared: SKU={$item_data['sku_id']}, Qty={$item_data['quantity']}, Email={$order->get_billing_email()}");
        }
        
        // ENVIAR A BILLIONCONNECT (F040)
        error_log("Calling Billionconnect API F040 for Order #$order_id");
        $result = $this->api_client->create_esim_order($order, $sub_orders);
        
        if (is_wp_error($result)) {
            $error_msg = $result->get_error_message();
            error_log("Billionconnect API Error for Order #$order_id: $error_msg");
            
            $order->add_order_note("❌ Error creando eSIM: $error_msg");
            $order->update_status('on-hold', "Error en Billionconnect: $error_msg");
            
            // Enviar email al admin
            $this->notify_admin_error($order, $error_msg);
            
            return;
        }
        
        // Verificar respuesta
        $trade_code = $result['tradeCode'] ?? '';
        
        if ($trade_code !== '1000') {
            $error_msg = $result['tradeMsg'] ?? 'Unknown error';
            error_log("Billionconnect returned error for Order #$order_id: Code=$trade_code, Message=$error_msg");
            
            $order->add_order_note("❌ Billionconnect error: $error_msg (Code: $trade_code)");
            $order->update_status('on-hold', "Error de Billionconnect: $error_msg");
            
            $this->notify_admin_error($order, $error_msg);
            
            return;
        }
        
        // ¡ÉXITO!
        $bc_order_id = $result['tradeData']['orderId'] ?? '';
        
        error_log("SUCCESS! Order #$order_id processed. Billionconnect Order ID: $bc_order_id");
        
        // Guardar información de Billionconnect
        $order->update_meta_data('_billionconnect_processed', true);
        $order->update_meta_data('_billionconnect_order_id', $bc_order_id);
        $order->update_meta_data('_billionconnect_channel_order_id', 'WC-' . $order_id . '-' . time());
        $order->update_meta_data('_billionconnect_processed_date', current_time('mysql'));
        $order->update_meta_data('_billionconnect_sub_orders', json_encode($sub_orders));
        $order->save();
        
        // Nota en la orden
        $order->add_order_note(
            "✅ eSIM creada exitosamente en Billionconnect\n\n" .
            "Billionconnect Order ID: $bc_order_id\n" .
            "Email del cliente: " . $order->get_billing_email() . "\n\n" .
            "El cliente recibirá el código QR por email cuando Billionconnect lo genere (webhook N009)."
        );
        
        // Cambiar a completado
        if ($order->get_status() === 'processing') {
            $order->update_status('completed', 'eSIM en proceso de entrega por Billionconnect');
        }
        
        error_log("Order #$order_id completed successfully");
    }
    
    /**
     * Obtener items eSIM de la orden
     */
    private function get_esim_items($order) {
        $esim_items = array();
        
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            if (!$product) {
                continue;
            }
            
            // Verificar si es producto eSIM
            $is_esim = get_post_meta($product->get_id(), '_is_esim_product', true);
            
            if ($is_esim !== 'yes') {
                continue;
            }
            
            // Obtener configuración del cliente (SKU seleccionado)
            $sku_id = $item->get_meta('_esim_sku_id');
            
            if (empty($sku_id)) {
                error_log("Warning: Item #$item_id has no SKU ID");
                continue;
            }
            
            $esim_items[$item_id] = array(
                'sku_id' => $sku_id,
                'quantity' => $item->get_quantity(),
                'product_name' => $product->get_name(),
                'estimated_use_time' => $item->get_meta('_esim_estimated_use_time')
            );
            
            error_log("Found eSIM item: Item#$item_id, SKU=$sku_id, Qty=" . $item->get_quantity());
        }
        
        return $esim_items;
    }
    
    /**
     * Agregar acciones administrativas
     */
    public function add_order_actions($actions) {
        global $theorder;
        
        if ($theorder && $theorder->get_meta('_billionconnect_order_id')) {
            $actions['resend_esim_email'] = '📧 Reenviar Email eSIM';
            $actions['query_esim_status'] = '🔍 Consultar Estado eSIM';
        }
        
        return $actions;
    }
    
    /**
     * Reenviar email de eSIM
     */
    public function resend_esim_email($order) {
        $bc_order_id = $order->get_meta('_billionconnect_order_id');
        
        if (empty($bc_order_id)) {
            $order->add_order_note('❌ No se encontró Billionconnect Order ID');
            return;
        }
        
        error_log("Resending eSIM email for Order #{$order->get_id()}, BC Order: $bc_order_id");
        
        $result = $this->api_client->resend_esim_email(
            $bc_order_id,
            $order->get_billing_email()
        );
        
        if (is_wp_error($result)) {
            $order->add_order_note('❌ Error reenviando email: ' . $result->get_error_message());
        } elseif (($result['tradeCode'] ?? '') === '1000') {
            $order->add_order_note('✅ Email de eSIM reenviado exitosamente');
        } else {
            $order->add_order_note('❌ Error: ' . ($result['tradeMsg'] ?? 'Unknown error'));
        }
    }
    
    /**
     * Consultar estado de eSIM
     */
    public function query_esim_status($order) {
        $bc_order_id = $order->get_meta('_billionconnect_order_id');
        
        if (empty($bc_order_id)) {
            $order->add_order_note('❌ No se encontró Billionconnect Order ID');
            return;
        }
        
        error_log("Querying eSIM status for Order #{$order->get_id()}, BC Order: $bc_order_id");
        
        $result = $this->api_client->get_order_info('WC-' . $order->get_id() . '-' . time(), true);
        
        if (is_wp_error($result)) {
            $order->add_order_note('❌ Error consultando estado: ' . $result->get_error_message());
        } elseif (($result['tradeCode'] ?? '') === '1000') {
            $data = $result['tradeData'] ?? array();
            $order->add_order_note('✅ Estado consultado: ' . wp_json_encode($data));
        } else {
            $order->add_order_note('❌ Error: ' . ($result['tradeMsg'] ?? 'Unknown error'));
        }
    }
    
    /**
     * Notificar error al administrador
     */
    private function notify_admin_error($order, $error_msg) {
        $admin_email = get_option('admin_email');
        $subject = 'Error procesando eSIM - Orden #' . $order->get_id();
        
        $message = "Ha ocurrido un error al procesar la orden de eSIM:\n\n";
        $message .= "Orden: #" . $order->get_id() . "\n";
        $message .= "Cliente: " . $order->get_billing_email() . "\n";
        $message .= "Error: " . $error_msg . "\n\n";
        $message .= "Por favor, revisa la orden en: " . $order->get_edit_order_url();
        
        wp_mail($admin_email, $subject, $message);
    }
}
