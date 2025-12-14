<?php
/**
 * eSIM Recharge System
 * 
 * Sistema de recargas basado 100% en API de Billionconnect:
 * - F052: Query eSIM recharge plans (obtiene planes disponibles para un ICCID)
 * - F007: Create top-up order (crea la orden de recarga)
 * - N013: Top-up order result notice (webhook)
 * 
 * @package Connectivity_Plans
 */

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_eSIM_Recharge {
    
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
        
        // Shortcode para página de recargas
        add_shortcode('esim_recharge_form', array($this, 'recharge_form_shortcode'));
        
        // AJAX: Validar ICCID y obtener planes disponibles
        add_action('wp_ajax_validate_esim_iccid', array($this, 'ajax_validate_iccid'));
        add_action('wp_ajax_nopriv_validate_esim_iccid', array($this, 'ajax_validate_iccid'));
        
        // AJAX: Procesar recarga (añadir al carrito)
        add_action('wp_ajax_add_recharge_to_cart', array($this, 'ajax_add_recharge_to_cart'));
        add_action('wp_ajax_nopriv_add_recharge_to_cart', array($this, 'ajax_add_recharge_to_cart'));
        
        // Procesar orden de recarga cuando se completa el pago
        add_action('woocommerce_order_status_completed', array($this, 'process_recharge_order'), 10, 1);
        
        // Crear página de recargas
        register_activation_hook(CONNECTIVITY_PLANS_PLUGIN_FILE, array($this, 'create_recharge_page'));
    }
    
    /**
     * Crear página de recargas
     */
    public function create_recharge_page() {
        // Verificar si ya existe
        $page_id = get_option('esim_recharge_page_id');
        
        if ($page_id && get_post($page_id)) {
            return;
        }
        
        // Crear página
        $page_id = wp_insert_post(array(
            'post_title' => 'Recargar eSIM',
            'post_content' => '[esim_recharge_form]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'recargar-esim'
        ));
        
        if ($page_id && !is_wp_error($page_id)) {
            update_option('esim_recharge_page_id', $page_id);
        }
    }
    
    /**
     * Shortcode del formulario de recarga
     */
    public function recharge_form_shortcode() {
        ob_start();
        ?>
        <div class="esim-recharge-container">
            <h2>🔄 Recargar mi eSIM</h2>
            
            <div class="esim-recharge-step esim-recharge-step-1">
                <h3>Paso 1: Ingresa tu ICCID</h3>
                <p>El ICCID es el número de tu eSIM. Lo encuentras en:</p>
                <ul>
                    <li>📱 <strong>iPhone:</strong> Configuración > General > Información > ICCID</li>
                    <li>📱 <strong>Android:</strong> Configuración > Conexiones > Administrador de SIM > ICCID</li>
                    <li>📧 <strong>Email original</strong> que recibiste con el código QR</li>
                </ul>
                
                <div class="iccid-input-group">
                    <input 
                        type="text" 
                        id="esim-iccid-input" 
                        placeholder="Ejemplo: 89860012018500000085" 
                        maxlength="22"
                        style="width: 100%; padding: 12px; font-size: 16px; border: 2px solid #ddd; border-radius: 6px;"
                    />
                    <button 
                        id="validate-iccid-btn" 
                        class="button button-primary"
                        style="margin-top: 15px; padding: 12px 30px; font-size: 16px;"
                    >
                        Verificar ICCID
                    </button>
                </div>
                
                <div id="iccid-validation-result" style="margin-top: 20px;"></div>
            </div>
            
            <div class="esim-recharge-step esim-recharge-step-2" style="display: none; margin-top: 30px;">
                <h3>Paso 2: Selecciona tu plan de recarga</h3>
                <div id="recharge-plans-list"></div>
            </div>
            
            <style>
                .esim-recharge-container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 30px;
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                
                .esim-recharge-step {
                    margin: 30px 0;
                }
                
                .esim-recharge-step h3 {
                    color: #2c3e50;
                    border-bottom: 2px solid #3498db;
                    padding-bottom: 10px;
                }
                
                .esim-recharge-step ul {
                    background: #f8f9fa;
                    padding: 20px 20px 20px 40px;
                    border-radius: 6px;
                    line-height: 2;
                }
                
                .recharge-plan-card {
                    border: 2px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 15px 0;
                    transition: all 0.3s;
                }
                
                .recharge-plan-card:hover {
                    border-color: #3498db;
                    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
                }
                
                .recharge-plan-card.selected {
                    border-color: #27ae60;
                    background: #e8f5e9;
                }
                
                .recharge-plan-card h4 {
                    margin-top: 0;
                    color: #2c3e50;
                }
                
                .recharge-plan-info {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin: 15px 0;
                }
                
                .recharge-plan-info-item {
                    background: #f8f9fa;
                    padding: 10px;
                    border-radius: 6px;
                }
                
                .recharge-plan-info-item strong {
                    color: #555;
                    display: block;
                    font-size: 12px;
                    text-transform: uppercase;
                    margin-bottom: 5px;
                }
                
                .recharge-plan-info-item span {
                    font-size: 18px;
                    color: #2c3e50;
                    font-weight: 600;
                }
                
                .recharge-plan-price {
                    font-size: 24px;
                    color: #27ae60;
                    font-weight: bold;
                    margin: 15px 0;
                }
                
                .add-recharge-btn {
                    background: #27ae60;
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: background 0.3s;
                }
                
                .add-recharge-btn:hover {
                    background: #229954;
                }
                
                .notice {
                    padding: 15px;
                    border-radius: 6px;
                    margin: 15px 0;
                }
                
                .notice-success {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }
                
                .notice-error {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }
                
                .notice-info {
                    background: #d1ecf1;
                    border: 1px solid #bee5eb;
                    color: #0c5460;
                }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                let selectedIccid = '';
                let availablePlans = [];
                
                // Validar ICCID
                $('#validate-iccid-btn').on('click', function() {
                    const iccid = $('#esim-iccid-input').val().trim();
                    const resultDiv = $('#iccid-validation-result');
                    const btn = $(this);
                    
                    if (!iccid || iccid.length < 18) {
                        resultDiv.html('<div class="notice notice-error">⚠️ Por favor ingresa un ICCID válido (mínimo 18 dígitos)</div>');
                        return;
                    }
                    
                    btn.prop('disabled', true).text('Verificando...');
                    resultDiv.html('<div class="notice notice-info">🔍 Consultando planes disponibles para tu eSIM...</div>');
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'validate_esim_iccid',
                            iccid: iccid,
                            nonce: '<?php echo wp_create_nonce('esim_recharge'); ?>'
                        },
                        success: function(response) {
                            btn.prop('disabled', false).text('Verificar ICCID');
                            
                            if (response.success) {
                                selectedIccid = iccid;
                                availablePlans = response.data.plans;
                                
                                resultDiv.html('<div class="notice notice-success">✅ ICCID válido. Se encontraron ' + availablePlans.length + ' planes de recarga disponibles.</div>');
                                
                                displayRechargePlans(availablePlans);
                                $('.esim-recharge-step-2').slideDown();
                            } else {
                                resultDiv.html('<div class="notice notice-error">❌ ' + response.data.message + '</div>');
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false).text('Verificar ICCID');
                            resultDiv.html('<div class="notice notice-error">❌ Error al conectar con el servidor. Por favor intenta nuevamente.</div>');
                        }
                    });
                });
                
                // Mostrar planes disponibles
                function displayRechargePlans(plans) {
                    const container = $('#recharge-plans-list');
                    container.html('');
                    
                    if (plans.length === 0) {
                        container.html('<div class="notice notice-info">ℹ️ No hay planes de recarga disponibles para este ICCID.</div>');
                        return;
                    }
                    
                    plans.forEach(function(plan) {
                        const planHtml = createPlanCard(plan);
                        container.append(planHtml);
                    });
                }
                
                // Crear card de plan
                function createPlanCard(plan) {
                    const planType = plan.planType === '1' ? '🔄 Pase Diario' : '📦 Paquete Total';
                    let dataInfo = '';
                    
                    if (plan.planType === '1') {
                        // Pase diario
                        const dailyGB = (plan.highFlowSize / 1048576).toFixed(1);
                        const totalGB = (dailyGB * plan.days).toFixed(1);
                        dataInfo = dailyGB + ' GB/día (' + totalGB + ' GB total)';
                    } else {
                        // Paquete total
                        const totalGB = (plan.capacity / 1048576).toFixed(1);
                        dataInfo = totalGB + ' GB totales';
                    }
                    
                    return `
                        <div class="recharge-plan-card" data-sku-id="${plan.skuId}">
                            <h4>${plan.name}</h4>
                            <div class="recharge-plan-info">
                                <div class="recharge-plan-info-item">
                                    <strong>Tipo</strong>
                                    <span>${planType}</span>
                                </div>
                                <div class="recharge-plan-info-item">
                                    <strong>Datos</strong>
                                    <span>${dataInfo}</span>
                                </div>
                                <div class="recharge-plan-info-item">
                                    <strong>Duración</strong>
                                    <span>${plan.days} días</span>
                                </div>
                            </div>
                            <div class="recharge-plan-price">$${plan.price}</div>
                            <button class="add-recharge-btn" data-sku-id="${plan.skuId}">
                                🛒 Recargar con este plan
                            </button>
                        </div>
                    `;
                }
                
                // Añadir recarga al carrito
                $(document).on('click', '.add-recharge-btn', function() {
                    const skuId = $(this).data('sku-id');
                    const btn = $(this);
                    
                    btn.prop('disabled', true).text('Agregando...');
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'add_recharge_to_cart',
                            iccid: selectedIccid,
                            sku_id: skuId,
                            nonce: '<?php echo wp_create_nonce('esim_recharge'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = '<?php echo wc_get_cart_url(); ?>';
                            } else {
                                alert('Error: ' + response.data.message);
                                btn.prop('disabled', false).text('🛒 Recargar con este plan');
                            }
                        },
                        error: function() {
                            alert('Error al agregar al carrito');
                            btn.prop('disabled', false).text('🛒 Recargar con este plan');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX: Validar ICCID y obtener planes disponibles
     */
    public function ajax_validate_iccid() {
        check_ajax_referer('esim_recharge', 'nonce');
        
        $iccid = sanitize_text_field($_POST['iccid'] ?? '');
        
        if (empty($iccid)) {
            wp_send_json_error(array('message' => 'ICCID no válido'));
        }
        
        // Llamar a F052 para obtener planes disponibles
        $response = $this->api_client->get_recharge_plans($iccid);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Error al consultar planes: ' . $response->get_error_message()));
        }
        
        if (($response['tradeCode'] ?? '') !== '1000') {
            wp_send_json_error(array('message' => $response['tradeMsg'] ?? 'Error desconocido'));
        }
        
        $sku_ids = $response['tradeData']['skuId'] ?? array();
        
        if (empty($sku_ids)) {
            wp_send_json_error(array('message' => 'No hay planes de recarga disponibles para este ICCID'));
        }
        
        // Obtener detalles de los planes
        $plans = $this->get_plans_details($sku_ids);
        
        wp_send_json_success(array(
            'plans' => $plans,
            'iccid' => $iccid
        ));
    }
    
    /**
     * Obtener detalles de los planes de recarga
     */
    private function get_plans_details($sku_ids) {
        // Obtener todos los planes
        $all_plans = $this->api_client->get_plans();
        $prices = $this->api_client->get_plans_prices();
        
        if (is_wp_error($all_plans) || is_wp_error($prices)) {
            return array();
        }
        
        // Indexar precios
        $prices_map = array();
        foreach ($prices['tradeData'] ?? array() as $price_item) {
            $prices_map[$price_item['skuId']] = $price_item['price'] ?? array();
        }
        
        // Filtrar planes disponibles
        $available_plans = array();
        
        foreach ($all_plans['tradeData'] ?? array() as $plan) {
            if (in_array($plan['skuId'], $sku_ids)) {
                $plan_data = array(
                    'skuId' => $plan['skuId'],
                    'name' => $plan['name'] ?? '',
                    'days' => $plan['days'] ?? '',
                    'capacity' => $plan['capacity'] ?? '',
                    'highFlowSize' => $plan['highFlowSize'] ?? '',
                    'planType' => $plan['planType'] ?? '0',
                    'price' => 0
                );
                
                // Obtener precio
                if (isset($prices_map[$plan['skuId']]) && !empty($prices_map[$plan['skuId']])) {
                    $first_price = $prices_map[$plan['skuId']][0] ?? array();
                    $plan_data['price'] = $first_price['retailPrice'] ?? 0;
                }
                
                $available_plans[] = $plan_data;
            }
        }
        
        return $available_plans;
    }
    
    /**
     * AJAX: Añadir recarga al carrito
     */
    public function ajax_add_recharge_to_cart() {
        check_ajax_referer('esim_recharge', 'nonce');
        
        $iccid = sanitize_text_field($_POST['iccid'] ?? '');
        $sku_id = sanitize_text_field($_POST['sku_id'] ?? '');
        
        if (empty($iccid) || empty($sku_id)) {
            wp_send_json_error(array('message' => 'Datos incompletos'));
        }
        
        // Crear producto virtual para la recarga
        $recharge_product_id = $this->create_recharge_product($iccid, $sku_id);
        
        if (!$recharge_product_id) {
            wp_send_json_error(array('message' => 'Error al crear producto de recarga'));
        }
        
        // Añadir al carrito
        WC()->cart->add_to_cart($recharge_product_id, 1, 0, array(), array(
            'esim_recharge' => true,
            'esim_iccid' => $iccid,
            'esim_sku_id' => $sku_id
        ));
        
        wp_send_json_success(array('message' => 'Recarga añadida al carrito'));
    }
    
    /**
     * Crear producto virtual para recarga
     */
    private function create_recharge_product($iccid, $sku_id) {
        // Buscar si ya existe
        global $wpdb;
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_recharge_sku_id' AND meta_value = %s LIMIT 1",
            $sku_id
        ));
        
        if ($existing) {
            return $existing;
        }
        
        // Obtener detalles del plan
        $plans = $this->get_plans_details(array($sku_id));
        
        if (empty($plans)) {
            return false;
        }
        
        $plan = $plans[0];
        
        // Crear producto
        $product = new WC_Product_Simple();
        $product->set_name('Recarga eSIM - ' . $plan['name']);
        $product->set_regular_price($plan['price']);
        $product->set_virtual(true);
        $product->set_sold_individually(true);
        $product->set_catalog_visibility('hidden');
        
        $product_id = $product->save();
        
        // Guardar metadata
        update_post_meta($product_id, '_recharge_sku_id', $sku_id);
        update_post_meta($product_id, '_is_recharge_product', 'yes');
        
        return $product_id;
    }
    
    /**
     * Procesar orden de recarga
     */
    public function process_recharge_order($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Buscar items de recarga
        foreach ($order->get_items() as $item_id => $item) {
            $iccid = $item->get_meta('esim_iccid');
            $sku_id = $item->get_meta('esim_sku_id');
            
            if (empty($iccid) || empty($sku_id)) {
                continue;
            }
            
            // Llamar a F007 para crear la recarga
            $result = $this->api_client->create_topup_order($order, $iccid, $sku_id, 1);
            
            if (is_wp_error($result)) {
                $order->add_order_note('❌ Error en recarga: ' . $result->get_error_message());
                continue;
            }
            
            if (($result['tradeCode'] ?? '') === '1000') {
                $order->add_order_note('✅ Recarga procesada para ICCID: ' . $iccid);
            } else {
                $order->add_order_note('❌ Error en recarga: ' . ($result['tradeMsg'] ?? 'Unknown'));
            }
        }
    }
}
