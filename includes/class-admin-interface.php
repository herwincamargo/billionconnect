<?php
if (!defined('ABSPATH')) exit;

/**
 * Admin Panel v5.0.0
 * Panel de administración completo y profesional
 */
class Connectivity_Plans_Admin_Interface {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_cp_sync_products', array($this, 'ajax_sync_products'));
        add_action('wp_ajax_cp_cleanup_catalog', array($this, 'ajax_cleanup_catalog'));
        add_action('wp_ajax_cp_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_cp_reset_all', array($this, 'ajax_reset_all'));
    }
    
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            'Connectivity Plans',
            'Connectivity Plans',
            'manage_options',
            'connectivity-plans',
            array($this, 'render_dashboard'),
            'dashicons-smartphone',
            56
        );
        
        // Submenús
        add_submenu_page(
            'connectivity-plans',
            'Dashboard',
            '📊 Dashboard',
            'manage_options',
            'connectivity-plans',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'connectivity-plans',
            'Configuración',
            '⚙️ Configuración',
            'manage_options',
            'connectivity-plans-settings',
            array($this, 'render_settings')
        );
        
        add_submenu_page(
            'connectivity-plans',
            'Sincronizar',
            '🔄 Sincronizar',
            'manage_options',
            'connectivity-plans-sync',
            array($this, 'render_sync')
        );
        
        add_submenu_page(
            'connectivity-plans',
            'Limpiar Catálogo',
            '🧹 Limpiar Catálogo',
            'manage_options',
            'connectivity-plans-cleanup',
            array($this, 'render_cleanup')
        );
        
        add_submenu_page(
            'connectivity-plans',
            'Herramientas',
            '🔧 Herramientas',
            'manage_options',
            'connectivity-plans-tools',
            array($this, 'render_tools')
        );
        
        add_submenu_page(
            'connectivity-plans',
            'Shortcodes',
            '📝 Shortcodes',
            'manage_options',
            'connectivity-plans-shortcodes',
            array($this, 'render_shortcodes')
        );
        
        add_submenu_page(
            'connectivity-plans',
            'Diagnóstico API',
            '🔍 Diagnóstico API',
            'manage_options',
            'connectivity-plans-diagnostic',
            array($this, 'render_diagnostic')
        );
    }
    
    public function register_settings() {
        // API Settings
        register_setting('connectivity_plans_settings', 'connectivity_plans_api_partner');
        register_setting('connectivity_plans_settings', 'connectivity_plans_api_secret');
        register_setting('connectivity_plans_settings', 'connectivity_plans_api_url');
        register_setting('connectivity_plans_settings', 'connectivity_plans_sales_method');
        register_setting('connectivity_plans_settings', 'connectivity_plans_api_language');
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'connectivity-plans') === false) {
            return;
        }
        
        wp_enqueue_style(
            'connectivity-plans-admin-v4',
            CONNECTIVITY_PLANS_PLUGIN_URL . 'assets/css/admin-v4.css',
            array(),
            CONNECTIVITY_PLANS_VERSION
        );
        
        wp_enqueue_script(
            'connectivity-plans-admin-v4',
            CONNECTIVITY_PLANS_PLUGIN_URL . 'assets/js/admin-v4.js',
            array('jquery'),
            CONNECTIVITY_PLANS_VERSION,
            true
        );
        
        wp_localize_script('connectivity-plans-admin-v4', 'cpAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cp_admin_nonce')
        ));
    }
    
    /**
     * DASHBOARD
     */
    public function render_dashboard() {
        global $wpdb;
        
        // Obtener estadísticas
        $total_products = wp_count_posts('product')->publish;
        
        $esim_count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_connectivity_type'
            AND pm.meta_value IN ('230', '3105', '3106', '110', '111')
        ");
        
        $non_esim_count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_connectivity_type'
            AND pm.meta_value NOT IN ('230', '3105', '3106', '110', '111')
            AND pm.meta_value != ''
        ");
        
        $last_sync = get_option('connectivity_plans_last_sync', 'Nunca');
        $api_configured = !empty(get_option('connectivity_plans_api_partner'));
        
        ?>
        <div class="wrap cp-admin-v4">
            <h1>📱 Connectivity Plans - Dashboard</h1>
            
            <div class="cp-dashboard-grid">
                
                <?php
                // Verificar si está en modo demo
                $demo_mode = get_transient('connectivity_plans_demo_mode');
                if ($demo_mode):
                ?>
                <div class="cp-card" style="grid-column: 1 / -1; border-left: 4px solid #ff9800;">
                    <h2>⚠️ MODO DE PRUEBA ACTIVADO</h2>
                    <div class="cp-status cp-status-warning">
                        <span class="dashicons dashicons-warning"></span>
                        <div>
                            <strong>Los precios son FICTICIOS - Solo para testing</strong>
                            <p style="margin: 10px 0 0 0;">
                                F003 (pricing) no está devolviendo datos. El plugin generó precios de prueba 
                                para que veas cómo funciona la estructura de productos.
                            </p>
                            <p style="margin: 10px 0 0 0;">
                                <strong>Acción requerida:</strong> Contacta a Billionconnect para habilitar F003 en tu cuenta 
                                antes de usar en producción.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Card: Estado -->
                <div class="cp-card">
                    <h2>📊 Estado del Sistema</h2>
                    <div class="cp-stats">
                        <div class="cp-stat">
                            <span class="cp-stat-label">Total Productos:</span>
                            <span class="cp-stat-value"><?php echo $total_products; ?></span>
                        </div>
                        <div class="cp-stat <?php echo $esim_count > 0 ? 'cp-stat-success' : ''; ?>">
                            <span class="cp-stat-label">📱 Productos eSIM:</span>
                            <span class="cp-stat-value"><?php echo $esim_count; ?></span>
                        </div>
                        <?php if ($non_esim_count > 0): ?>
                        <div class="cp-stat cp-stat-warning">
                            <span class="cp-stat-label">⚠️ Productos NO eSIM:</span>
                            <span class="cp-stat-value"><?php echo $non_esim_count; ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="cp-stat">
                            <span class="cp-stat-label">Última Sincronización:</span>
                            <span class="cp-stat-value"><?php echo esc_html($last_sync); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Card: API -->
                <div class="cp-card">
                    <h2>🔌 Configuración API</h2>
                    <?php if ($api_configured): ?>
                        <div class="cp-status cp-status-success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong>API Configurada</strong>
                        </div>
                        <p>Partner Code: <code><?php echo esc_html(get_option('connectivity_plans_api_partner')); ?></code></p>
                        <p>Sales Method: <code><?php echo esc_html(get_option('connectivity_plans_sales_method', '5')); ?></code></p>
                        <a href="<?php echo admin_url('admin.php?page=connectivity-plans-settings'); ?>" class="button">
                            ⚙️ Editar Configuración
                        </a>
                    <?php else: ?>
                        <div class="cp-status cp-status-error">
                            <span class="dashicons dashicons-warning"></span>
                            <strong>API No Configurada</strong>
                        </div>
                        <p>Necesitas configurar las credenciales del API para comenzar.</p>
                        <a href="<?php echo admin_url('admin.php?page=connectivity-plans-settings'); ?>" class="button button-primary">
                            ⚙️ Configurar Ahora
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Card: Acciones Rápidas -->
                <div class="cp-card">
                    <h2>⚡ Acciones Rápidas</h2>
                    <div class="cp-quick-actions">
                        <a href="<?php echo admin_url('admin.php?page=connectivity-plans-sync'); ?>" class="button button-primary button-large">
                            <span class="dashicons dashicons-update"></span>
                            Sincronizar Productos
                        </a>
                        <?php if ($non_esim_count > 0): ?>
                        <a href="<?php echo admin_url('admin.php?page=connectivity-plans-cleanup'); ?>" class="button button-large">
                            <span class="dashicons dashicons-trash"></span>
                            Limpiar Catálogo
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="button button-large">
                            <span class="dashicons dashicons-products"></span>
                            Ver Productos
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=connectivity-plans-tools'); ?>" class="button button-large">
                            <span class="dashicons dashicons-admin-tools"></span>
                            Herramientas
                        </a>
                    </div>
                </div>
                
                <!-- Card: Información -->
                <div class="cp-card cp-card-info">
                    <h2>ℹ️ Información</h2>
                    <p><strong>Versión del Plugin:</strong> <?php echo CONNECTIVITY_PLANS_VERSION; ?></p>
                    <p><strong>Tipos de Producto Soportados:</strong></p>
                    <ul>
                        <li>📱 eSIM puro (tipo 230)</li>
                        <li>📱 eSIM + Datos Auto-selección (tipo 3105)</li>
                        <li>📱 eSIM + Datos Fijos (tipo 3106)</li>
                        <li>🔄 Plan de recarga Auto-selección (tipo 110)</li>
                        <li>🔄 Plan de recarga Fijo (tipo 111)</li>
                    </ul>
                    <p><strong>Webhook URL:</strong></p>
                    <code style="word-break: break-all;">
                        <?php echo rest_url('connectivity-plans/v1/webhook'); ?>
                    </code>
                    <p style="margin-top: 15px;">
                        <small>Configura esta URL en el panel de Billionconnect para recibir notificaciones.</small>
                    </p>
                </div>
                
            </div>
            
            <?php if ($non_esim_count > 0): ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Atención:</strong> Tienes <?php echo $non_esim_count; ?> productos que NO son eSIM en tu catálogo. 
                <a href="<?php echo admin_url('admin.php?page=connectivity-plans-cleanup'); ?>">Haz clic aquí para limpiar tu catálogo</a> y mostrar solo productos eSIM.</p>
            </div>
            <?php endif; ?>
            
        </div>
        <?php
    }
    
    /**
     * SETTINGS PAGE
     */
    public function render_settings() {
        if (isset($_POST['cp_save_settings']) && check_admin_referer('cp_settings_nonce')) {
            update_option('connectivity_plans_api_partner', sanitize_text_field($_POST['api_partner']));
            update_option('connectivity_plans_api_secret', sanitize_text_field($_POST['api_secret']));
            update_option('connectivity_plans_api_url', esc_url_raw($_POST['api_url']));
            update_option('connectivity_plans_sales_method', sanitize_text_field($_POST['sales_method']));
            update_option('connectivity_plans_api_language', sanitize_text_field($_POST['api_language']));
            
            echo '<div class="notice notice-success"><p>✅ Configuración guardada correctamente.</p></div>';
        }
        
        $api_partner = get_option('connectivity_plans_api_partner', '');
        $api_secret = get_option('connectivity_plans_api_secret', '');
        $api_url = get_option('connectivity_plans_api_url', 'https://api-flow.billionconnect.com/Flow/saler/2.0/invoke');
        $sales_method = get_option('connectivity_plans_sales_method', '5');
        
        ?>
        <div class="wrap cp-admin-v4">
            <h1>⚙️ Configuración</h1>
            
            <form method="post" class="cp-settings-form">
                <?php wp_nonce_field('cp_settings_nonce'); ?>
                
                <div class="cp-card">
                    <h2>🔌 Credenciales API</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="api_partner">Partner Code (App Key)</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="api_partner" 
                                       name="api_partner" 
                                       value="<?php echo esc_attr($api_partner); ?>" 
                                       class="regular-text" 
                                       required>
                                <p class="description">Tu Partner Code de Billionconnect (ejemplo: Hero)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="api_secret">API Secret</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="api_secret" 
                                       name="api_secret" 
                                       value="<?php echo esc_attr($api_secret); ?>" 
                                       class="regular-text" 
                                       required>
                                <p class="description">Tu App Secret de Billionconnect</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="api_url">API URL</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="api_url" 
                                       name="api_url" 
                                       value="<?php echo esc_attr($api_url); ?>" 
                                       class="regular-text" 
                                       placeholder="https://api-flow.billionconnect.com/Flow/saler/2.0/invoke"
                                       required>
                                
                                <div style="margin-top: 10px;">
                                    <button type="button" class="button button-secondary" onclick="document.getElementById('api_url').value='https://api-flow.billionconnect.com/Flow/saler/2.0/invoke'">
                                        🌐 Usar Producción
                                    </button>
                                    <button type="button" class="button button-secondary" onclick="document.getElementById('api_url').value='https://api-flow-ts.billionconnect.com/Flow/saler/2.0/invoke'">
                                        🧪 Usar Test
                                    </button>
                                </div>
                                
                                <p class="description" style="margin-top: 10px;">
                                    <strong>URLs disponibles:</strong><br>
                                    <code style="font-size: 11px;">https://api-flow.billionconnect.com/Flow/saler/2.0/invoke</code> (Producción)<br>
                                    <code style="font-size: 11px;">https://api-flow-ts.billionconnect.com/Flow/saler/2.0/invoke</code> (Test)
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="sales_method">Sales Method</label>
                            </th>
                            <td>
                                <select id="sales_method" name="sales_method">
                                    <option value="1" <?php selected($sales_method, '1'); ?>>
                                        1 - Retailing (precios de venta al público)
                                    </option>
                                    <option value="2" <?php selected($sales_method, '2'); ?>>
                                        2 - Agent (agente de ventas)
                                    </option>
                                    <option value="3" <?php selected($sales_method, '3'); ?>>
                                        3 - Wholesale (venta al por mayor)
                                    </option>
                                    <option value="4" <?php selected($sales_method, '4'); ?>>
                                        4 - Partner (socio comercial)
                                    </option>
                                    <option value="5" <?php selected($sales_method, '5'); ?>>
                                        5 - Distribution (distribuidor con saldo prepago)
                                    </option>
                                    <option value="6" <?php selected($sales_method, '6'); ?>>
                                        6 - Custom (personalizado)
                                    </option>
                                </select>
                                <p class="description">Modo de ventas según tu tipo de cuenta en Billionconnect</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="api_language">🌐 Idioma de la API</label>
                            </th>
                            <td>
                                <?php $api_language = get_option('connectivity_plans_api_language', '2'); ?>
                                <select id="api_language" name="api_language">
                                    <option value="1" <?php selected($api_language, '1'); ?>>
                                        中文 (Chino) - Los nombres vendrán en chino
                                    </option>
                                    <option value="2" <?php selected($api_language, '2'); ?>>
                                        English (Inglés) - Los nombres vendrán en inglés ✓
                                    </option>
                                </select>
                                <p class="description">
                                    <strong>Importante:</strong> Define el idioma en que Billionconnect enviará los nombres de productos y países.<br>
                                    <strong>Recomendado:</strong> Inglés (2) - Los nombres serán traducibles y legibles.
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="cp_save_settings" class="button button-primary button-large">
                            💾 Guardar Configuración
                        </button>
                    </p>
                </div>
            </form>
            
            <div class="cp-card cp-card-info">
                <h2>ℹ️ Información sobre Sales Method</h2>
                
                <h3>Sales Method 1 (Retailing):</h3>
                <ul>
                    <li>Para venta directa al consumidor final</li>
                    <li>Precios sugeridos de venta al público</li>
                    <li>Modelo tradicional de ecommerce</li>
                </ul>
                
                <h3>Sales Method 2 (Agent):</h3>
                <ul>
                    <li>Para agentes de ventas</li>
                    <li>Comisiones por venta</li>
                    <li>Modelo de representación comercial</li>
                </ul>
                
                <h3>Sales Method 3 (Wholesale):</h3>
                <ul>
                    <li>Para venta al por mayor</li>
                    <li>Precios especiales para grandes volúmenes</li>
                    <li>Modelo B2B (Business to Business)</li>
                </ul>
                
                <h3>Sales Method 4 (Partner):</h3>
                <ul>
                    <li>Para socios comerciales</li>
                    <li>Acuerdos especiales de precios</li>
                    <li>Colaboración estratégica</li>
                </ul>
                
                <h3>Sales Method 5 (Distribution):</h3>
                <ul>
                    <li>Para distribuidores autorizados</li>
                    <li>Sistema de saldo prepago</li>
                    <li>retailPrice incluye margen de ganancia</li>
                    <li>settlementPrice = costo que se descuenta del saldo</li>
                </ul>
                
                <h3>Sales Method 6 (Custom):</h3>
                <ul>
                    <li>Configuración personalizada</li>
                    <li>Términos específicos según acuerdo</li>
                    <li>Contacta a Billionconnect para detalles</li>
                </ul>
                
                <p style="margin-top: 20px;"><strong>💡 Recomendación:</strong> Usa el Sales Method que te asignó Billionconnect al crear tu cuenta. Si tienes dudas, consulta con tu ejecutivo de cuenta.</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * SYNC PAGE
     */
    public function render_sync() {
        ?>
        <div class="wrap cp-admin-v4">
            <h1>🔄 Sincronizar Productos</h1>
            
            <div class="cp-card">
                <h2>Sincronización de Productos eSIM</h2>
                <p>Este proceso obtendrá todos los planes eSIM disponibles desde la API de Billionconnect y creará/actualizará los productos en WooCommerce.</p>
                
                <div class="cp-sync-info">
                    <p><strong>✅ Se sincronizarán:</strong></p>
                    <ul>
                        <li>📱 eSIM puros (tipo 230)</li>
                        <li>📱 eSIM + Datos Auto-selección (tipo 3105)</li>
                        <li>📱 eSIM + Datos Fijos (tipo 3106)</li>
                        <li>🔄 Planes de recarga Auto-selección (tipo 110)</li>
                        <li>🔄 Planes de recarga Fijos (tipo 111)</li>
                    </ul>
                    
                    <p><strong>❌ Se ignorarán:</strong></p>
                    <ul>
                        <li>💳 SIM cards físicas</li>
                        <li>📡 Dispositivos MiFi</li>
                        <li>📦 Otros productos físicos</li>
                    </ul>
                </div>
                
                <div id="cp-sync-status" style="display: none;">
                    <div class="cp-progress-bar">
                        <div class="cp-progress-fill" id="cp-progress-fill"></div>
                    </div>
                    <div id="cp-sync-log"></div>
                </div>
                
                <p class="submit">
                    <button id="cp-btn-sync" class="button button-primary button-hero">
                        <span class="dashicons dashicons-update"></span>
                        Sincronizar Ahora
                    </button>
                </p>
            </div>
            
            <div class="cp-card cp-card-warning">
                <h3>⚠️ Antes de sincronizar:</h3>
                <ul>
                    <li>Asegúrate de tener las credenciales API configuradas</li>
                    <li>La sincronización puede tomar varios minutos</li>
                    <li>No cierres esta ventana durante el proceso</li>
                    <li>Se recomienda hacer respaldo antes de sincronizar</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * CLEANUP PAGE
     */
    public function render_cleanup() {
        global $wpdb;
        
        $non_esim_products = $wpdb->get_results("
            SELECT p.ID, p.post_title, pm.meta_value as type
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND pm.meta_key = '_connectivity_type'
            AND pm.meta_value NOT IN ('230', '3105', '3106', '110', '111')
            AND pm.meta_value != ''
        ");
        
        $type_names = array(
            '110' => 'Plan Auto-selección',
            '111' => 'Plan Fijo',
            '210' => '💳 SIM Un Solo Uso',
            '211' => '💳 SIM Multiuso',
            '212' => '💳 SIM Física Dura',
            '220' => '📡 MIFI Venta',
            '221' => '📡 MIFI Alquiler',
            '311' => '💳 SIM Dura + Datos',
            '3101' => '💳 SIM Un Uso + Datos Auto',
            '3102' => '💳 SIM Un Uso + Datos Fijos',
            '3103' => '💳 SIM Multiuso + Datos Auto',
            '3104' => '💳 SIM Multiuso + Datos Fijos',
            '3201' => '📡 MIFI Venta + Datos Auto',
            '3202' => '📡 MIFI Venta + Datos Fijos',
            '3211' => '📡 MIFI Alquiler + Datos Auto',
            '3212' => '📡 MIFI Venta + Datos Fijos',
        );
        
        ?>
        <div class="wrap cp-admin-v4">
            <h1>🧹 Limpiar Catálogo</h1>
            
            <?php if (empty($non_esim_products)): ?>
                
                <div class="cp-card">
                    <div class="cp-status cp-status-success" style="text-align: center; padding: 40px;">
                        <span class="dashicons dashicons-yes-alt" style="font-size: 64px;"></span>
                        <h2>✅ ¡Catálogo Limpio!</h2>
                        <p>Tu catálogo solo contiene productos eSIM. No hay nada que limpiar.</p>
                    </div>
                </div>
                
            <?php else: ?>
                
                <div class="cp-card">
                    <h2>Productos NO eSIM Encontrados: <?php echo count($non_esim_products); ?></h2>
                    <p>Los siguientes productos serán eliminados permanentemente:</p>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($non_esim_products as $product): ?>
                            <tr>
                                <td><?php echo $product->ID; ?></td>
                                <td><?php echo esc_html($product->post_title); ?></td>
                                <td><?php echo $type_names[$product->type] ?? 'Tipo ' . $product->type; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div id="cp-cleanup-status" style="display: none; margin-top: 20px;">
                        <div class="cp-progress-bar">
                            <div class="cp-progress-fill" id="cp-cleanup-progress"></div>
                        </div>
                        <div id="cp-cleanup-log"></div>
                    </div>
                    
                    <p class="submit">
                        <button id="cp-btn-cleanup" class="button button-primary button-large" 
                                data-count="<?php echo count($non_esim_products); ?>">
                            <span class="dashicons dashicons-trash"></span>
                            Eliminar <?php echo count($non_esim_products); ?> Productos
                        </button>
                    </p>
                </div>
                
                <div class="cp-card cp-card-warning">
                    <h3>⚠️ ADVERTENCIA:</h3>
                    <ul>
                        <li>Esta acción es <strong>PERMANENTE</strong> y no se puede deshacer</li>
                        <li>Se eliminarán <?php echo count($non_esim_products); ?> productos</li>
                        <li>Solo se mantendrán productos eSIM (tipos 230, 3105, 3106)</li>
                        <li>Se recomienda hacer respaldo antes de continuar</li>
                    </ul>
                </div>
                
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * TOOLS PAGE
     */
    public function render_tools() {
        ?>
        <div class="wrap cp-admin-v4">
            <h1>🔧 Herramientas</h1>
            
            <div class="cp-dashboard-grid">
                
                <!-- Card: Regenerate Prices -->
                <div class="cp-card">
                    <h2>💰 Regenerar Precios</h2>
                    <p>Si los productos muestran rangos ($8.50 - $202.00) en lugar de "Desde $8.50", usa este botón.</p>
                    <div id="cp-regenerate-result" style="display: none; margin-top: 15px;"></div>
                    <p class="submit">
                        <button id="cp-btn-regenerate-prices" class="button button-primary">
                            🔄 Regenerar Precios
                        </button>
                    </p>
                </div>
                
                <!-- Card: Test API -->
                <div class="cp-card">
                    <h2>🧪 Probar Conexión API</h2>
                    <p>Verifica que las credenciales API estén configuradas correctamente.</p>
                    <div id="cp-test-api-result" style="display: none;"></div>
                    <p class="submit">
                        <button id="cp-btn-test-api" class="button button-secondary">
                            🔌 Probar Conexión
                        </button>
                    </p>
                </div>
                
                <!-- Card: Logs -->
                <div class="cp-card">
                    <h2>📝 Ver Logs</h2>
                    <p>Ver los últimos 50 registros del log de errores de WordPress.</p>
                    <p class="submit">
                        <a href="<?php echo admin_url('admin.php?page=connectivity-plans-tools&action=view_logs'); ?>" class="button button-secondary">
                            📄 Ver Logs
                        </a>
                    </p>
                </div>
                
                <!-- Card: Reset -->
                <div class="cp-card cp-card-danger">
                    <h2>🗑️ Reset Completo</h2>
                    <p>Eliminar TODOS los productos de conectividad y reiniciar la configuración.</p>
                    <p class="submit">
                        <button id="cp-btn-reset" class="button button-secondary">
                            🗑️ Reset Completo
                        </button>
                    </p>
                </div>
                
                <!-- Card: Info -->
                <div class="cp-card cp-card-info">
                    <h2>ℹ️ Información del Sistema</h2>
                    <table class="form-table">
                        <tr>
                            <th>Versión del Plugin:</th>
                            <td><?php echo CONNECTIVITY_PLANS_VERSION; ?></td>
                        </tr>
                        <tr>
                            <th>WordPress:</th>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <th>WooCommerce:</th>
                            <td><?php echo defined('WC_VERSION') ? WC_VERSION : 'No instalado'; ?></td>
                        </tr>
                        <tr>
                            <th>PHP:</th>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <th>Webhook URL:</th>
                            <td><code><?php echo rest_url('connectivity-plans/v1/webhook'); ?></code></td>
                        </tr>
                    </table>
                </div>
                
            </div>
        </div>
        <?php
        
        // Show logs if requested
        if (isset($_GET['action']) && $_GET['action'] === 'view_logs') {
            $this->display_logs();
        }
    }
    
    /**
     * SHORTCODES PAGE
     */
    public function render_shortcodes() {
        ?>
        <div class="wrap cp-admin-v4">
            <h1>📝 Shortcodes Disponibles</h1>
            
            <div class="cp-card">
                <h2>🔄 Mostrar Recargas</h2>
                <p>Usa este shortcode para mostrar solo productos de recarga:</p>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[product_category category="recargas-datos"]</pre>
                
                <h3>Con opciones:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[product_category category="recargas-datos" per_page="12" columns="4" orderby="title"]</pre>
                
                <p><strong>Parámetros disponibles:</strong></p>
                <ul>
                    <li><code>per_page</code> - Productos por página (default: 12)</li>
                    <li><code>columns</code> - Número de columnas (default: 4)</li>
                    <li><code>orderby</code> - Ordenar por: title, date, price, popularity</li>
                    <li><code>order</code> - ASC o DESC</li>
                </ul>
            </div>
            
            <div class="cp-card">
                <h2>📱 Mostrar eSIM</h2>
                <p>Usa este shortcode para mostrar solo productos eSIM completos:</p>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[product_category category="esim-internacional"]</pre>
                
                <h3>Con opciones:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[product_category category="esim-internacional" per_page="16" columns="4"]</pre>
            </div>
            
            <div class="cp-card">
                <h2>⭐ Productos Destacados</h2>
                
                <h3>Recargas destacadas:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[featured_products category="recargas-datos" per_page="4"]</pre>
                
                <h3>eSIM destacados:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[featured_products category="esim-internacional" per_page="4"]</pre>
                
                <p><small>💡 Para marcar productos como destacados, edita el producto y marca la casilla "Producto destacado"</small></p>
            </div>
            
            <div class="cp-card">
                <h2>🆕 Productos Recientes</h2>
                
                <h3>Últimas recargas:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[recent_products category="recargas-datos" per_page="4"]</pre>
                
                <h3>Últimos eSIM:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[recent_products category="esim-internacional" per_page="4"]</pre>
            </div>
            
            <div class="cp-card">
                <h2>💰 Productos en Oferta</h2>
                
                <h3>Recargas en oferta:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[sale_products category="recargas-datos" per_page="4"]</pre>
                
                <h3>eSIM en oferta:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[sale_products category="esim-internacional" per_page="4"]</pre>
            </div>
            
            <div class="cp-card">
                <h2>🔝 Mejores Vendidos</h2>
                
                <h3>Recargas más vendidas:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[top_rated_products category="recargas-datos" per_page="4"]</pre>
                
                <h3>eSIM más vendidos:</h3>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[top_rated_products category="esim-internacional" per_page="4"]</pre>
            </div>
            
            <div class="cp-card">
                <h2>🎯 Producto Específico</h2>
                <p>Muestra un producto específico por su ID:</p>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[product id="123"]</pre>
                
                <p>Muestra varios productos por sus IDs:</p>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">[products ids="123,456,789"]</pre>
            </div>
            
            <div class="cp-card cp-card-info">
                <h2>📚 Cómo Usar los Shortcodes</h2>
                
                <h3>En Páginas:</h3>
                <ol>
                    <li>Ve a <strong>Páginas → Añadir nueva</strong></li>
                    <li>Escribe tu contenido</li>
                    <li>Inserta el shortcode donde quieras mostrar productos</li>
                    <li>Publica</li>
                </ol>
                
                <h3>En Widgets:</h3>
                <ol>
                    <li>Ve a <strong>Apariencia → Widgets</strong></li>
                    <li>Agrega un widget "HTML personalizado" o "Shortcode"</li>
                    <li>Pega el shortcode</li>
                    <li>Guarda</li>
                </ol>
                
                <h3>En Elementor:</h3>
                <ol>
                    <li>Agrega widget <strong>"Shortcode"</strong></li>
                    <li>Pega el shortcode</li>
                    <li>O usa widget <strong>"Productos por Categoría"</strong> (WooCommerce)</li>
                </ol>
            </div>
            
            <div class="cp-card cp-card-warning">
                <h2>💡 Ejemplos de Páginas</h2>
                
                <h3>Página "Recargas":</h3>
                <pre style="background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap;">
&lt;h1&gt;🔄 Recargas de Datos&lt;/h1&gt;
&lt;p&gt;¿Ya tienes un eSIM? Recárgalo con más datos sin comprar uno nuevo.&lt;/p&gt;

[product_category category="recargas-datos" per_page="12" columns="4"]
                </pre>
                
                <h3>Página "eSIM Internacional":</h3>
                <pre style="background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap;">
&lt;h1&gt;📱 eSIM Internacional&lt;/h1&gt;
&lt;p&gt;Conectividad instantánea para tus viajes. Recibe tu eSIM por email.&lt;/p&gt;

[product_category category="esim-internacional" per_page="16" columns="4"]
                </pre>
                
                <h3>Página "Inicio" con secciones:</h3>
                <pre style="background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap;">
&lt;h2&gt;🆕 Últimos eSIM Disponibles&lt;/h2&gt;
[recent_products category="esim-internacional" per_page="4"]

&lt;h2&gt;🔄 Recargas Más Populares&lt;/h2&gt;
[top_rated_products category="recargas-datos" per_page="4"]
                </pre>
            </div>
            
            <div class="cp-card">
                <h2>🔗 Enlaces Directos a Categorías</h2>
                <p>También puedes usar enlaces directos a las categorías:</p>
                
                <p><strong>Recargas:</strong></p>
                <code style="background: #f5f5f5; padding: 5px 10px; border-radius: 3px; display: inline-block;"><?php echo home_url('/product-category/recargas-datos/'); ?></code>
                
                <p style="margin-top: 15px;"><strong>eSIM Internacional:</strong></p>
                <code style="background: #f5f5f5; padding: 5px 10px; border-radius: 3px; display: inline-block;"><?php echo home_url('/product-category/esim-internacional/'); ?></code>
                
                <p style="margin-top: 20px;">
                    <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="button button-primary">
                        Ver Todos los Productos →
                    </a>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=product_cat&post_type=product'); ?>" class="button">
                        Ver Categorías →
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
    
    private function display_logs() {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($log_file)) {
            echo '<div class="cp-card"><p>No se encontró archivo de logs.</p></div>';
            return;
        }
        
        $lines = file($log_file);
        $last_50 = array_slice($lines, -50);
        
        echo '<div class="cp-card">';
        echo '<h2>📝 Últimos 50 Registros del Log</h2>';
        echo '<pre style="background: #f5f5f5; padding: 15px; overflow: auto; max-height: 500px; font-size: 12px;">';
        echo htmlspecialchars(implode('', $last_50));
        echo '</pre>';
        echo '</div>';
    }
    
    /**
     * AJAX: Sync Products
     */
    public function ajax_sync_products() {
        check_ajax_referer('cp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No permissions');
        }
        
        $syncer = new Connectivity_Plans_Country_Product_Sync();
        $result = $syncer->sync_all();
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        update_option('connectivity_plans_last_sync', current_time('mysql'));
        
        wp_send_json_success(array(
            'message' => 'Sincronización completada',
            'products_created' => $result['synced'] ?? 0,
            'errors' => $result['errors'] ?? array()
        ));
    }
    
    /**
     * AJAX: Cleanup Catalog
     */
    public function ajax_cleanup_catalog() {
        check_ajax_referer('cp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No permissions');
        }
        
        global $wpdb;
        
        // Delete products that don't have Billionconnect metadata
        $esim_products = $wpdb->get_results("
            SELECT p.ID
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_bc_sku_id'
            WHERE p.post_type IN ('product', 'product_variation')
            AND pm.meta_value IS NULL
        ");
        
        $deleted = 0;
        foreach ($esim_products as $product) {
            if (wp_delete_post($product->ID, true)) {
                $deleted++;
            }
        }
        
        wp_send_json_success(array(
            'message' => "Se eliminaron {$deleted} productos",
            'deleted' => $deleted
        ));
    }
    
    /**
     * AJAX: Reset All
     */
    public function ajax_reset_all() {
        check_ajax_referer('cp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No permissions');
        }
        
        global $wpdb;
        
        // Eliminar TODOS los productos (no solo variaciones)
        $all_products = $wpdb->get_results("
            SELECT ID FROM {$wpdb->posts}
            WHERE post_type IN ('product', 'product_variation')
        ");
        
        $deleted = 0;
        foreach ($all_products as $product) {
            if (wp_delete_post($product->ID, true)) {
                $deleted++;
            }
        }
        
        // Limpiar términos de atributos
        $wpdb->query("DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (
            SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} 
            WHERE taxonomy IN ('pa_plan_type', 'pa_datos', 'pa_dias')
        )");
        
        $wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ('pa_plan_type', 'pa_datos', 'pa_dias')");
        $wpdb->query("DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL");
        
        // Limpiar opciones
        delete_option('connectivity_plans_last_sync');
        delete_option('connectivity_plans_last_sync_stats');
        
        wp_send_json_success(array(
            'message' => "Reset completo: se eliminaron {$deleted} productos y se limpiaron los atributos",
            'deleted' => $deleted
        ));
    }
    
    /**
     * AJAX: Test API
     */
    public function ajax_test_api() {
        check_ajax_referer('cp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No permissions');
        }
        
        require_once(CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-api-client.php');
        
        $api = new Connectivity_Plans_API_Client();
        $sales_method = get_option('connectivity_plans_sales_method', '5');
        $result = $api->get_plans($sales_method);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Verificar respuesta del API
        $trade_code = $result['tradeCode'] ?? 'N/A';
        $trade_msg = $result['tradeMsg'] ?? 'N/A';
        $trade_data = $result['tradeData'] ?? array();
        $plans_count = count($trade_data);
        
        // Diagnosticar por qué 0 planes
        $diagnostic = '';
        if ($plans_count === 0) {
            $diagnostic = '<br><br><strong>⚠️ Diagnóstico:</strong><br>';
            $diagnostic .= '• Trade Code: ' . $trade_code . '<br>';
            $diagnostic .= '• Trade Message: ' . $trade_msg . '<br>';
            
            if ($trade_code !== '0000' && $trade_code !== '1000' && $trade_code !== 0 && $trade_code !== 1000) {
                $diagnostic .= '• <span style="color: red;">El API devolvió un código de error</span><br>';
            } else {
                $diagnostic .= '• Sales Method actual: ' . $sales_method . '<br>';
                $diagnostic .= '• <span style="color: orange;">El API responde OK pero sin planes</span><br>';
                $diagnostic .= '• Posibles causas:<br>';
                $diagnostic .= '  - Sales Method incorrecto<br>';
                $diagnostic .= '  - No hay planes disponibles para este Sales Method<br>';
                $diagnostic .= '  - Tu cuenta no tiene planes asignados<br>';
            }
        }
        
        wp_send_json_success(array(
            'message' => '✅ Conexión exitosa',
            'plans_found' => $plans_count,
            'diagnostic' => $diagnostic,
            'trade_code' => $trade_code,
            'trade_msg' => $trade_msg
        ));
    }
    
    /**
     * DIAGNOSTIC PAGE
     */
    public function render_diagnostic() {
        // Obtener configuración
        $partner = get_option('connectivity_plans_api_partner', '');
        $secret = get_option('connectivity_plans_api_secret', '');
        $url = get_option('connectivity_plans_api_url', '');
        $sales_method = get_option('connectivity_plans_sales_method', '5');
        
        ?>
        <div class="wrap cp-admin-v4">
            <h1>🔍 Diagnóstico Completo del API</h1>
            
            <div class="cp-card">
                <h2>📋 Configuración Actual</h2>
                <table class="form-table">
                    <tr>
                        <th>Partner Code:</th>
                        <td><code><?php echo esc_html($partner); ?></code></td>
                    </tr>
                    <tr>
                        <th>API Secret:</th>
                        <td>
                            <?php if (empty($secret)): ?>
                                <span style="color: red;">❌ No configurado</span>
                            <?php else: ?>
                                <span style="color: green;">✅ Configurado (<?php echo strlen($secret); ?> caracteres)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>API URL:</th>
                        <td><code><?php echo esc_html($url); ?></code></td>
                    </tr>
                    <tr>
                        <th>Sales Method:</th>
                        <td><strong><?php echo esc_html($sales_method); ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <div class="cp-card">
                <h2>🔌 Prueba de Conexión</h2>
                <p>Probando con <strong>Sales Method <?php echo $sales_method; ?></strong></p>
                
                <?php
                require_once(CONNECTIVITY_PLANS_PLUGIN_DIR . 'includes/class-api-client.php');
                $api = new Connectivity_Plans_API_Client();
                $result = $api->get_plans($sales_method);
                
                if (is_wp_error($result)):
                ?>
                    <div class="notice notice-error">
                        <p><strong>❌ Error:</strong> <?php echo $result->get_error_message(); ?></p>
                    </div>
                <?php else:
                    $trade_code = $result['tradeCode'] ?? 'N/A';
                    $trade_msg = $result['tradeMsg'] ?? 'N/A';
                    $trade_data = $result['tradeData'] ?? array();
                    $plans_count = count($trade_data);
                ?>
                    
                    <table class="form-table">
                        <tr>
                            <th>Trade Code:</th>
                            <td><code><?php echo esc_html($trade_code); ?></code></td>
                        </tr>
                        <tr>
                            <th>Trade Message:</th>
                            <td><?php echo esc_html($trade_msg); ?></td>
                        </tr>
                        <tr>
                            <th>Planes Recibidos:</th>
                            <td><strong style="font-size: 20px; color: <?php echo $plans_count > 0 ? 'green' : 'red'; ?>;">
                                <?php echo $plans_count; ?>
                            </strong></td>
                        </tr>
                    </table>
                    
                    <?php if ($plans_count === 0): ?>
                        
                        <div class="cp-card cp-card-warning">
                            <h3>⚠️ Sin Planes - Probando Otros Sales Methods</h3>
                            <p>Buscando en qué Sales Method tienes planes disponibles...</p>
                            
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Sales Method</th>
                                        <th>Planes</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($sm = 1; $sm <= 6; $sm++):
                                        $test_result = $api->get_plans($sm);
                                        if (is_wp_error($test_result)) continue;
                                        
                                        $test_count = count($test_result['tradeData'] ?? array());
                                        $test_code = $test_result['tradeCode'] ?? 'N/A';
                                    ?>
                                        <tr <?php echo $sm == $sales_method ? 'style="background: #fff3cd;"' : ''; ?>>
                                            <td>
                                                <strong><?php echo $sm; ?></strong>
                                                <?php if ($sm == $sales_method) echo '<span style="color: orange;"> (ACTUAL)</span>'; ?>
                                            </td>
                                            <td><strong style="font-size: 16px;"><?php echo $test_count; ?></strong></td>
                                            <td>
                                                <?php if ($test_count > 0): ?>
                                                    <span style="color: green; font-weight: bold;">✅ DISPONIBLE</span>
                                                <?php else: ?>
                                                    <span style="color: gray;">⚪ Sin planes (Code: <?php echo $test_code; ?>)</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                            
                            <h3>💡 Recomendación:</h3>
                            <?php
                            // Encontrar el mejor sales method
                            $best_sm = null;
                            $best_count = 0;
                            for ($sm = 1; $sm <= 6; $sm++) {
                                $test_result = $api->get_plans($sm);
                                if (!is_wp_error($test_result)) {
                                    $test_count = count($test_result['tradeData'] ?? array());
                                    if ($test_count > $best_count) {
                                        $best_count = $test_count;
                                        $best_sm = $sm;
                                    }
                                }
                            }
                            
                            if ($best_sm && $best_sm != $sales_method):
                            ?>
                                <p style="background: #d4edda; padding: 15px; border-left: 4px solid #28a745;">
                                    <strong>✅ Encontramos planes en Sales Method <?php echo $best_sm; ?>!</strong><br>
                                    Hay <strong><?php echo $best_count; ?> planes</strong> disponibles.<br><br>
                                    <a href="<?php echo admin_url('admin.php?page=connectivity-plans-settings'); ?>" class="button button-primary button-large">
                                        ⚙️ Cambiar a Sales Method <?php echo $best_sm; ?>
                                    </a>
                                </p>
                            <?php else: ?>
                                <p style="background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;">
                                    <strong>❌ No se encontraron planes en ningún Sales Method</strong><br>
                                    Posibles causas:<br>
                                    • Tu cuenta no tiene planes asignados<br>
                                    • Problema con las credenciales del API<br>
                                    • Necesitas contactar a Billionconnect<br>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                    <?php else: ?>
                        
                        <div class="notice notice-success">
                            <p><strong>✅ Excelente!</strong> Tienes <?php echo $plans_count; ?> planes disponibles para sincronizar.</p>
                        </div>
                        
                        <div class="cp-card">
                            <h3>📊 Estadísticas por Tipo</h3>
                            <?php
                            $types_count = array();
                            $type_names = array(
                                '110' => '🔄 Plan Auto-selección (Recarga)',
                                '111' => '🔄 Plan Fijo (Recarga)',
                                '230' => '📱 eSIM Puro',
                                '3105' => '📱 eSIM + Datos Auto',
                                '3106' => '📱 eSIM + Datos Fijos',
                                '210' => '💳 SIM Física Un Uso',
                                '211' => '💳 SIM Física Multiuso',
                                '212' => '💳 SIM Física Dura',
                                '220' => '📡 MiFi Venta',
                                '221' => '📡 MiFi Alquiler'
                            );
                            
                            foreach ($trade_data as $plan) {
                                $type = $plan['type'] ?? 'unknown';
                                $types_count[$type] = ($types_count[$type] ?? 0) + 1;
                            }
                            ksort($types_count);
                            ?>
                            
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>Descripción</th>
                                        <th>Se Sincroniza</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($types_count as $type => $count):
                                        $type_name = $type_names[$type] ?? 'Tipo ' . $type;
                                        $will_sync = in_array($type, array('110', '111', '230', '3105', '3106'));
                                    ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($type); ?></strong></td>
                                            <td><strong><?php echo $count; ?></strong></td>
                                            <td><?php echo esc_html($type_name); ?></td>
                                            <td>
                                                <?php if ($will_sync): ?>
                                                    <span style="color: green; font-weight: bold;">✅ SÍ</span>
                                                <?php else: ?>
                                                    <span style="color: gray;">⚪ NO (productos físicos)</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <p style="margin-top: 20px;">
                                <a href="<?php echo admin_url('admin.php?page=connectivity-plans-sync'); ?>" class="button button-primary button-large">
                                    🔄 Ir a Sincronizar
                                </a>
                            </p>
                        </div>
                        
                        <div class="cp-card">
                            <h3>📋 Muestra de Planes (primeros 10)</h3>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Países</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sample = array_slice($trade_data, 0, 10);
                                    foreach ($sample as $plan):
                                        $sku = $plan['skuId'] ?? 'N/A';
                                        $name = $plan['name'] ?? 'N/A';
                                        $type = $plan['type'] ?? 'N/A';
                                        $countries = $plan['country'] ?? array();
                                    ?>
                                        <tr>
                                            <td><code><?php echo esc_html($sku); ?></code></td>
                                            <td><?php echo esc_html($name); ?></td>
                                            <td><strong><?php echo esc_html($type); ?></strong></td>
                                            <td><?php echo count($countries); ?> países</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
            
            <div class="cp-card cp-card-info">
                <h2>ℹ️ Información</h2>
                <p>Este diagnóstico prueba la conexión con el API de Billionconnect y te muestra qué planes están disponibles.</p>
                <p>Si ves "0 planes", el diagnóstico automáticamente probará con todos los Sales Methods (1-6) para encontrar el correcto.</p>
            </div>
        </div>
        <?php
    }
}
