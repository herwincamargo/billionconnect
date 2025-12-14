<?php
/**
 * Billionconnect API Client v2.0
 * 
 * Cliente completo para API de Billionconnect
 * Incluye todos los endpoints necesarios para eSIM
 * 
 * @package Connectivity_Plans
 */

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_API_Client {
    
    private $api_url;
    private $api_key;
    private $api_secret;
    private $sales_method;
    private $language;
    
    public function __construct() {
        $this->api_url = get_option('connectivity_plans_api_url', 'https://api-flow.billionconnect.com/Flow/saler/2.0/invoke');
        $this->api_key = get_option('connectivity_plans_api_partner', '');
        $this->api_secret = get_option('connectivity_plans_api_secret', '');
        $this->sales_method = get_option('connectivity_plans_sales_method', '5');
        $this->language = get_option('connectivity_plans_api_language', '2'); // Default: English
    }
    
    /**
     * Generar firma MD5 para autenticación
     * Según documentación oficial: MD5(appSecret + request_body_completo)
     */
    private function generate_signature($request_body) {
        // CORRECTO según documentación:
        // sign = MD5(appSecret + JSON_del_body_completo)
        $json_body = json_encode($request_body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $sign_str = $this->api_secret . $json_body;
        $signature = md5($sign_str);
        
        error_log("=== SIGNATURE DEBUG (OFICIAL) ===");
        error_log("API Secret: " . (empty($this->api_secret) ? 'EMPTY!' : substr($this->api_secret, 0, 10) . "..."));
        error_log("Request Body JSON: " . substr($json_body, 0, 100) . "...");
        error_log("Sign String: appSecret + request_body");
        error_log("Sign String Length: " . strlen($sign_str));
        error_log("MD5 Signature (x-sign-value): " . $signature);
        error_log("==================================");
        
        return $signature;
    }
    
    /**
     * Hacer request a la API
     */
    private function request($trade_type, $trade_data = array()) {
        $trade_time = gmdate('Y-m-d H:i:s'); // UTC time
        
        $request_data = array(
            'tradeType' => $trade_type,
            'tradeTime' => $trade_time,
            'tradeData' => $trade_data
        );
        
        // Generar firma con el body completo
        $signature = $this->generate_signature($request_data);
        
        // Headers CORRECTOS según documentación oficial
        $args = array(
            'body' => json_encode($request_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'headers' => array(
                'Content-Type' => 'application/json;charset=UTF-8',
                'x-channel-id' => $this->api_key,      // appKey
                'x-sign-method' => 'md5',               // método de firma
                'x-sign-value' => $signature            // firma MD5
            ),
            'timeout' => 120,
            'sslverify' => true
        );
        
        error_log("Billionconnect API Request ($trade_type): " . json_encode($request_data));
        error_log("Request Headers: x-channel-id=" . substr($this->api_key, 0, 10) . "..., x-sign-method=md5, x-sign-value=" . $signature);
        
        $response = wp_remote_post($this->api_url, $args);
        
        if (is_wp_error($response)) {
            error_log("Billionconnect API Error: " . $response->get_error_message());
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        error_log("Billionconnect API Response ($trade_type): " . $body);
        
        return $data;
    }
    
    /**
     * F002 - Obtener planes eSIM
     * Filtra automáticamente solo tipos eSIM: 230, 3105, 3106
     */
    public function get_plans() {
        $params = array(
            'salesMethod' => $this->sales_method,
            'language' => $this->language  // 2 = English
        );
        
        $response = $this->request('F002', $params);
        
        // Filtrar solo eSIM
        if (!is_wp_error($response) && isset($response['tradeData'])) {
            $esim_types = array('230', '3105', '3106');
            $filtered = array();
            
            foreach ($response['tradeData'] as $plan) {
                if (in_array($plan['type'] ?? '', $esim_types)) {
                    $filtered[] = $plan;
                }
            }
            
            $response['tradeData'] = $filtered;
            error_log("Filtered eSIM plans: " . count($filtered) . " products");
        }
        
        return $response;
    }
    
    /**
     * F003 - Obtener precios de planes
     * IMPORTANTE: Siempre usa salesMethod=1 (retail) para obtener precios base
     * El salesMethod real del distribuidor se usa solo en F052 (crear orden)
     */
    public function get_plans_prices() {
        return $this->request('F003', array(
            'salesMethod' => $this->sales_method,
            'language' => $this->language
        ));
    }

    /**
     * F001 - Obtener lista de países
     */
    public function get_countries() {
        $params = array(
            'salesMethod' => $this->sales_method,
            'language' => $this->language
        );
        return $this->request('F001', $params);
    }
    
    /**
     * F040 - Crear orden eSIM (CRÍTICO)
     * Este es el endpoint principal que crea la eSIM en Billionconnect
     */
    public function create_esim_order($order, $sub_orders) {
        $channel_order_id = 'WC-' . $order->get_id() . '-' . time();
        
        $params = array(
            'channelOrderId' => $channel_order_id,
            'orderTime' => current_time('Y-m-d H:i:s'),
            'subOrderList' => $sub_orders
        );
        
        error_log("Creating eSIM order in Billionconnect for WC Order #{$order->get_id()}");
        error_log("Sub-orders: " . json_encode($sub_orders));
        
        return $this->request('F040', $params);
    }
    
    /**
     * F041 - Reenviar email de eSIM
     */
    public function resend_esim_email($billionconnect_order_id, $email) {
        return $this->request('F041', array(
            'orderId' => $billionconnect_order_id,
            'email' => $email,
            'emailLanguage' => $this->language
        ));
    }
    
    /**
     * F011 - Consultar información de orden
     */
    public function get_order_info($order_id, $is_channel_order = true) {
        $params = array();
        
        if ($is_channel_order) {
            $params['channelOrderId'] = $order_id;
        } else {
            $params['orderId'] = $order_id;
        }
        
        return $this->request('F011', $params);
    }
    
    /**
     * F042 - Consultar estado del perfil eSIM
     */
    public function get_esim_profile_status($iccid) {
        return $this->request('F042', array(
            'iccid' => $iccid
        ));
    }
    
    /**
     * F046 - Consultar uso de datos
     */
    public function get_data_usage($iccid) {
        return $this->request('F046', array(
            'iccid' => $iccid
        ));
    }
    
    /**
     * F052 - Consultar planes de recarga disponibles
     */
    /**
     * F052 - Query eSIM recharge plans (por ICCID)
     */
    public function get_recharge_plans($iccid) {
        $params = array(
            'iccid' => $iccid
        );
        
        return $this->request('F052', $params);
    }
    
    /**
     * F007 - Crear orden de recarga (top-up)
     */
    public function create_topup_order($order, $iccid, $sku_id, $quantity = 1) {
        $channel_order_id = 'WC-TOPUP-' . $order->get_id() . '-' . time();
        
        $params = array(
            'channelOrderId' => $channel_order_id,
            'orderTime' => current_time('Y-m-d H:i:s'),
            'subOrderList' => array(
                array(
                    'channelSubOrderId' => $channel_order_id . '-1',
                    'iccid' => $iccid,
                    'skuId' => $sku_id,
                    'quantity' => (string)$quantity
                )
            )
        );
        
        return $this->request('F007', $params);
    }
    
    /**
     * F008 - Cancelar orden
     */
    public function cancel_order($channel_order_id) {
        return $this->request('F008', array(
            'channelOrderId' => $channel_order_id
        ));
    }
    
    /**
     * F014 - Consultar balance de cuenta
     */
    public function get_account_balance() {
        return $this->request('F014', array());
    }
    
    /**
     * Test de conexión API
     */
    public function test_connection() {
        $result = $this->get_account_balance();
        
        if (is_wp_error($result)) {
            return array(
                'success' => false,
                'message' => $result->get_error_message()
            );
        }
        
        if (($result['tradeCode'] ?? '') === '1000') {
            return array(
                'success' => true,
                'message' => 'Conexión exitosa con Billionconnect API',
                'balance' => $result['tradeData']['balance'] ?? 'N/A'
            );
        }
        
        return array(
            'success' => false,
            'message' => $result['tradeMsg'] ?? 'Error desconocido'
        );
    }
}
