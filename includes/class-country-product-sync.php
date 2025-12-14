<?php

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_Country_Product_Sync {
    
    private $api_client;
    
    public function __construct() {
        $this->api_client = new Connectivity_Plans_API_Client();
    }
    
    public function sync_all() {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');
        
        error_log("=== INICIANDO SINCRONIZACIÓN POR PAÍS ===");

        // 1. Obtener lista de países con banderas (F001)
        $countries_response = $this->api_client->get_countries();
        $countries_map = array();
        if (!is_wp_error($countries_response) && ($countries_response['tradeCode'] ?? '') === '1000') {
            foreach ($countries_response['tradeData'] as $country) {
                $countries_map[$country['mcc']] = $country;
            }
            error_log("F001: Successfully fetched " . count($countries_map) . " countries with their data.");
        } else {
            error_log("F001: Could not fetch the country list. Proceeding without flag URLs.");
        }
        
        // Obtener planes
        $plans_response = $this->api_client->get_plans();
        
        if (is_wp_error($plans_response)) {
            error_log("Error obteniendo planes: " . $plans_response->get_error_message());
            return array('error' => $plans_response->get_error_message());
        }
        
        if (($plans_response['tradeCode'] ?? '') !== '1000') {
            error_log("API error: " . ($plans_response['tradeMsg'] ?? 'Unknown'));
            return array('error' => $plans_response['tradeMsg'] ?? 'Unknown error');
        }
        
        // Obtener precios
        $prices_response = $this->api_client->get_plans_prices();
        $prices_map = array();
        
        error_log("=== F003 PRICING RESPONSE ===");
        error_log("Prices Response Code: " . ($prices_response['tradeCode'] ?? 'N/A'));
        error_log("Prices Response Msg: " . ($prices_response['tradeMsg'] ?? 'N/A'));
        
        $f003_has_prices = false;
        
        if (!is_wp_error($prices_response) && isset($prices_response['tradeData'])) {
            error_log("F003 returned " . count($prices_response['tradeData']) . " price items");
            foreach ($prices_response['tradeData'] as $price_item) {
                $sku_id = $price_item['skuId'] ?? 'UNKNOWN';
                $price_array = $price_item['price'] ?? array();
                if (!empty($price_array)) {
                    $prices_map[$sku_id] = $price_array;
                    $f003_has_prices = true;
                    error_log("SKU $sku_id has " . count($price_array) . " price tiers (copies)");
                }
            }
        } else {
            error_log("⚠️ F003 FAILED or returned no tradeData");
            if (is_wp_error($prices_response)) {
                error_log("F003 Error: " . $prices_response->get_error_message());
            }
        }
        
        // FALLBACK: Generar precios de prueba si F003 falla
        if (!$f003_has_prices) {
            error_log("⚠️ F003 NO TIENE PRECIOS - Generando precios de PRUEBA");
            error_log("🔧 MODO DE PRUEBA ACTIVADO - Contacta a Billionconnect para habilitar F003");
            
            // Generar precios de prueba para cada plan
            foreach ($plans_response['tradeData'] as $plan) {
                $sku_id = $plan['skuId'] ?? '';
                if (empty($sku_id)) continue;
                
                $prices_map[$sku_id] = $this->generate_demo_prices($plan);
            }
            
            error_log("✅ Precios de PRUEBA generados: " . count($prices_map) . " SKUs");
            error_log("⚠️ ESTOS SON PRECIOS DEMO - NO USAR EN PRODUCCIÓN");
            
            // Activar flag de modo demo
            set_transient('connectivity_plans_demo_mode', true, WEEK_IN_SECONDS);
        } else {
            // Desactivar flag de modo demo si F003 funciona
            delete_transient('connectivity_plans_demo_mode');
        }
        
        error_log("Total SKUs with pricing: " . count($prices_map));
        error_log("=============================");
        
        // Agrupar planes por país
        $plans_by_country = $this->group_plans_by_country($plans_response['tradeData'] ?? array(), $prices_map, $countries_map);
        
        error_log("Países encontrados: " . count($plans_by_country));
        
        // Crear/actualizar productos
        $results = array(
            'created' => 0,
            'updated' => 0,
            'errors' => array()
        );
        
        foreach ($plans_by_country as $country_name => $country_data) {
            $result = $this->create_or_update_country_product($country_name, $country_data);
            
            if ($result['success']) {
                if ($result['action'] === 'created') {
                    $results['created']++;
                } else {
                    $results['updated']++;
                }
            } else {
                $results['errors'][] = "$country_name: " . $result['error'];
            }
        }
        
        error_log("Sincronización completada: Creados={$results['created']}, Actualizados={$results['updated']}, Errores=" . count($results['errors']));
        
        update_option('connectivity_plans_last_sync', current_time('mysql'));
        update_option('connectivity_plans_last_sync_stats', $results);
        
        return $results;
    }
    
    /**
     * Generar precios de PRUEBA cuando F003 no funciona
     * SOLO PARA TESTING - NO USAR EN PRODUCCIÓN
     */
    private function generate_demo_prices($plan) {
        $plan_type = $plan['planType'] ?? '0';
        
        // Precio base según el tipo de datos
        if ($plan_type === '1') {
            // Pase diario - precio por GB/día
            $high_flow_gb = floatval($plan['highFlowSize'] ?? 0) / 1048576; // KB a GB
            $price_per_day = max(5, $high_flow_gb * 5); // $5 mínimo o $5 por GB
        } else {
            // Paquete total - precio por GB total
            $capacity_gb = floatval($plan['capacity'] ?? 0) / 1048576; // KB a GB
            $price_per_day = max(3, $capacity_gb * 2); // $3 mínimo o $2 por GB
        }
        
        // Copies comunes: 1, 2, 3, 5, 7, 10, 15, 30 días
        $copies_options = array(1, 2, 3, 5, 7, 10, 15, 30);
        
        $demo_prices = array();
        foreach ($copies_options as $copies) {
            $settlement = round($price_per_day * $copies, 2);
            $retail = round($settlement * 1.30, 2); // 30% margen
            
            $demo_prices[] = array(
                'copies' => (string)$copies,
                'settlementPrice' => (string)$settlement,
                'retailPrice' => (string)$retail
            );
        }
        
        return $demo_prices;
    }
    
    private function group_plans_by_country($plans, $prices_map, $countries_map) {
        $grouped = array();
        
        foreach ($plans as $plan) {
            $countries = $plan['country'] ?? array();

            // Enriquecer los datos de los países con la información de F001 (banderas)
            foreach ($countries as $i => $country) {
                if (isset($countries_map[$country['mcc']])) {
                    $countries[$i]['url'] = $countries_map[$country['mcc']]['url'];
                }
            }
            
            if (empty($countries)) {
                continue;
            }
            
            // CLAVE: Usar SKU como identificador ÚNICO de cada producto
            // Billionconnect asigna el MISMO skuId a planes del MISMO producto
            // Diferentes skuIds = Diferentes productos
            $sku_id = $plan['skuId'] ?? '';
            
            if (empty($sku_id)) {
                continue;
            }
            
            // Extraer prefijo del SKU para agrupar (ej: "LAT001-1GB-7D" → "LAT001")
            // O usar productName si existe
            $product_name = $plan['productName'] ?? null;
            
            if ($product_name && !empty($product_name)) {
                // Usar nombre de producto
                $group_key = $product_name;
                $uses_product_name = true;
            } else {
                // Usar nombre del primer país como agrupador
                // Si Billionconnect agrupa varios SKUs con los mismos países, se agruparán aquí
                $group_key = $countries[0]['name'] ?? 'Unknown';
                $uses_product_name = false;
            }
            
            if (!isset($grouped[$group_key])) {
                $country_count = count($countries);
                error_log("Producto: '$group_key' - Países: $country_count - " . implode(', ', array_column($countries, 'name')));
                
                $grouped[$group_key] = array(
                    'countries' => $countries,
                    'total_packages' => array(),
                    'daily_passes' => array(),
                    'uses_product_name' => $uses_product_name
                );
            }
            
            $plan['prices'] = $prices_map[$plan['skuId']] ?? array();
            $plan_type = $plan['planType'] ?? '0';
            
            if ($plan_type === '1') {
                $grouped[$group_key]['daily_passes'][] = $plan;
            } else {
                $grouped[$group_key]['total_packages'][] = $plan;
            }
        }
        
        error_log("Total de productos agrupados: " . count($grouped));
        
        return $grouped;
    }
    
    private function create_or_update_country_product($country_name, $country_data) {
        global $wpdb;
        
        // Buscar producto existente
        $product_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_country_esim_name' AND meta_value = %s LIMIT 1",
            $country_name
        ));
        
        if ($product_id) {
            return $this->update_country_product($product_id, $country_name, $country_data);
        } else {
            return $this->create_country_product($country_name, $country_data);
        }
    }
    private function translate_country_name($country_name) {
        $translations = array(
            // Regiones multi-país (tal como vienen de la API)
            'Latin America' => 'América Latina',
            'Europe' => 'Europa',
            'Asia' => 'Asia',
            'North America' => 'América del Norte',
            
            // Nombres específicos que pueden venir combinados
            '美国・普拉蒂・加拿大' => 'América del Norte',
            '美國・普拉蒂・加拿大' => 'América del Norte',
            '美国・加拿大' => 'América del Norte',
            '美國・加拿大' => 'América del Norte',
            
            // América
            '美国' => 'Estados Unidos',
            '美國' => 'Estados Unidos',
            'United States' => 'Estados Unidos',
            'USA' => 'Estados Unidos',
            '加拿大' => 'Canadá',
            'Canada' => 'Canadá',
            '墨西哥' => 'México',
            'Mexico' => 'México',
            '巴西' => 'Brasil',
            'Brazil' => 'Brasil',
            '阿根廷' => 'Argentina',
            'Argentina' => 'Argentina',
            '智利' => 'Chile',
            'Chile' => 'Chile',
            '哥伦比亚' => 'Colombia',
            '哥倫比亞' => 'Colombia',
            'Colombia' => 'Colombia',
            '秘鲁' => 'Perú',
            '秘魯' => 'Perú',
            'Peru' => 'Perú',
            '普拉蒂' => 'Puerto Rico',
            '波多黎各' => 'Puerto Rico',
            'Puerto Rico' => 'Puerto Rico',
            
            // Asia
            '中国' => 'China',
            '中國' => 'China',
            'China' => 'China',
            '日本' => 'Japón',
            'Japan' => 'Japón',
            '韩国' => 'Corea del Sur',
            '韓國' => 'Corea del Sur',
            'Korea' => 'Corea del Sur',
            'South Korea' => 'Corea del Sur',
            '泰国' => 'Tailandia',
            '泰國' => 'Tailandia',
            'Thailand' => 'Tailandia',
            '新加坡' => 'Singapur',
            'Singapore' => 'Singapur',
            '马来西亚' => 'Malasia',
            '馬來西亞' => 'Malasia',
            'Malaysia' => 'Malasia',
            '印度尼西亚' => 'Indonesia',
            '印度尼西亞' => 'Indonesia',
            'Indonesia' => 'Indonesia',
            '菲律宾' => 'Filipinas',
            '菲律賓' => 'Filipinas',
            'Philippines' => 'Filipinas',
            '越南' => 'Vietnam',
            'Vietnam' => 'Vietnam',
            '柬埔寨' => 'Camboya',
            'Cambodia' => 'Camboya',
            '老挝' => 'Laos',
            'Laos' => 'Laos',
            '緬甸' => 'Myanmar',
            '缅甸' => 'Myanmar',
            'Myanmar' => 'Myanmar',
            '印度' => 'India',
            'India' => 'India',
            '巴基斯坦' => 'Pakistán',
            'Pakistan' => 'Pakistán',
            '孟加拉国' => 'Bangladesh',
            '孟加拉國' => 'Bangladesh',
            'Bangladesh' => 'Bangladesh',
            '斯里兰卡' => 'Sri Lanka',
            '斯里蘭卡' => 'Sri Lanka',
            'Sri Lanka' => 'Sri Lanka',
            '尼泊尔' => 'Nepal',
            '尼泊爾' => 'Nepal',
            'Nepal' => 'Nepal',
            '香港' => 'Hong Kong',
            'Hong Kong' => 'Hong Kong',
            
            // Europa
            '英国' => 'Reino Unido',
            '英國' => 'Reino Unido',
            'United Kingdom' => 'Reino Unido',
            'UK' => 'Reino Unido',
            '法国' => 'Francia',
            '法國' => 'Francia',
            'France' => 'Francia',
            '德国' => 'Alemania',
            '德國' => 'Alemania',
            'Germany' => 'Alemania',
            '意大利' => 'Italia',
            'Italy' => 'Italia',
            '西班牙' => 'España',
            'Spain' => 'España',
            '葡萄牙' => 'Portugal',
            'Portugal' => 'Portugal',
            '荷兰' => 'Países Bajos',
            '荷蘭' => 'Países Bajos',
            'Netherlands' => 'Países Bajos',
            '比利时' => 'Bélgica',
            '比利時' => 'Bélgica',
            'Belgium' => 'Bélgica',
            '瑞士' => 'Suiza',
            'Switzerland' => 'Suiza',
            '奥地利' => 'Austria',
            '奧地利' => 'Austria',
            'Austria' => 'Austria',
            '瑞典' => 'Suecia',
            'Sweden' => 'Suecia',
            '挪威' => 'Noruega',
            'Norway' => 'Noruega',
            '丹麦' => 'Dinamarca',
            '丹麥' => 'Dinamarca',
            'Denmark' => 'Dinamarca',
            '芬兰' => 'Finlandia',
            '芬蘭' => 'Finlandia',
            'Finland' => 'Finlandia',
            '爱尔兰' => 'Irlanda',
            '愛爾蘭' => 'Irlanda',
            'Ireland' => 'Irlanda',
            '俄罗斯' => 'Rusia',
            '俄羅斯' => 'Rusia',
            'Russia' => 'Rusia',
            '波兰' => 'Polonia',
            '波蘭' => 'Polonia',
            'Poland' => 'Polonia',
            '捷克' => 'República Checa',
            'Czech Republic' => 'República Checa',
            '匈牙利' => 'Hungría',
            'Hungary' => 'Hungría',
            '罗马尼亚' => 'Rumania',
            '羅馬尼亞' => 'Rumania',
            'Romania' => 'Rumania',
            '希腊' => 'Grecia',
            '希臘' => 'Grecia',
            'Greece' => 'Grecia',
            '土耳其' => 'Turquía',
            'Turkey' => 'Turquía',
            '欧洲' => 'Europa',
            '歐洲' => 'Europa',
            
            // Medio Oriente
            '以色列' => 'Israel',
            'Israel' => 'Israel',
            '阿联酋' => 'Emiratos Árabes Unidos',
            '阿聯酋' => 'Emiratos Árabes Unidos',
            'UAE' => 'Emiratos Árabes Unidos',
            'United Arab Emirates' => 'Emiratos Árabes Unidos',
            '沙特阿拉伯' => 'Arabia Saudita',
            'Saudi Arabia' => 'Arabia Saudita',
            '埃及' => 'Egipto',
            'Egypt' => 'Egipto',
            
            // África
            '南非' => 'Sudáfrica',
            'South Africa' => 'Sudáfrica',
            '肯尼亚' => 'Kenia',
            '肯尼亞' => 'Kenia',
            'Kenya' => 'Kenia',
            
            // Oceanía
            '澳大利亚' => 'Australia',
            '澳大利亞' => 'Australia',
            'Australia' => 'Australia',
            '新西兰' => 'Nueva Zelanda',
            '新西蘭' => 'Nueva Zelanda',
            'New Zealand' => 'Nueva Zelanda',
            
            // Otras regiones
            '亚洲' => 'Asia',
            '亞洲' => 'Asia'
        );
        
        // Devolver traducción si existe, sino devolver el nombre original
        return $translations[$country_name] ?? $country_name;
    }
    
    private function create_country_product($country_name, $country_data) {
        try {
            // CREAR PRODUCTO VARIABLE (no simple)
            $product = new WC_Product_Variable();

            // Traducir el nombre del país para el título del producto
            $translated_name = $this->translate_country_name($country_name);
            $product_title = $translated_name . ' eSIM';
            
            $product->set_name($product_title);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_virtual(true);
            $product->set_downloadable(false);
            
            // IMPORTANTE: Stock management
            $product->set_manage_stock(false);
            $product->set_stock_status('instock');
            
            // Descripción completa y profesional
            $description = $this->build_product_description($product_title, $country_data);
            $product->set_description($description);
            
            // Descripción corta
            $short_desc = $this->build_short_description($product_title, $country_data);
            $product->set_short_description($short_desc);
            
            // Precio desde (se actualizará con las variaciones)
            $min_price = $this->get_minimum_price($country_data);
            if ($min_price > 0) {
                $product->set_regular_price($min_price);
                $product->set_price($min_price);
            }
            
            $product_id = $product->save();
            
            // Guardar metadata
            $this->save_country_product_meta($product_id, $country_name, $country_data);
            
            // Asignar categorías
            $this->assign_categories($product_id, $product_title, $country_data);
            
            // CREAR ATRIBUTOS Y VARIACIONES
            $this->create_product_attributes($product_id, $country_data);
            $this->create_product_variations($product_id, $country_data);
            
            // Actualizar precio después de crear variaciones
            WC_Product_Variable::sync($product_id);
            
            // IMPORTANTE: Forzar que solo muestre precio mínimo (no rango)
            // WooCommerce por defecto muestra "$min - $max"
            // Queremos solo "Desde $min"
            $product = wc_get_product($product_id);
            if ($product) {
                $min_price = $this->get_minimum_price($country_data);
                
                // Eliminar precio máximo para que solo muestre "Desde"
                delete_post_meta($product_id, '_price');
                delete_post_meta($product_id, '_min_variation_price');
                delete_post_meta($product_id, '_max_variation_price');
                delete_post_meta($product_id, '_min_variation_regular_price');
                delete_post_meta($product_id, '_max_variation_regular_price');
                
                // Establecer solo el precio mínimo
                update_post_meta($product_id, '_price', $min_price);
                update_post_meta($product_id, '_min_variation_price', $min_price);
                update_post_meta($product_id, '_min_variation_regular_price', $min_price);
            }
            
            error_log("Producto variable creado: $product_title (ID: $product_id)");
            
            return array(
                'success' => true,
                'action' => 'created',
                'product_id' => $product_id
            );
            
        } catch (Exception $e) {
            error_log("Error creando producto $country_name: " . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    private function update_country_product($product_id, $country_name, $country_data) {
        try {
            $product = wc_get_product($product_id);
            
            if (!$product) {
                return array('success' => false, 'error' => 'Product not found');
            }

            // Traducir el nombre del país para el título del producto
            $translated_name = $this->translate_country_name($country_name);
            $product_title = $translated_name . ' eSIM';
            $product->set_name($product_title);
            
            // Actualizar descripciones
            $description = $this->build_product_description($translated_name, $country_data);
            $product->set_description($description);
            
            $short_desc = $this->build_short_description($country_name, $country_data);
            $product->set_short_description($short_desc);
            
            // Actualizar precio
            $min_price = $this->get_minimum_price($country_data);
            if ($min_price > 0) {
                $product->set_regular_price($min_price);
                $product->set_price($min_price);
            }
            
            $product->save();
            
            // Actualizar metadata
            $this->save_country_product_meta($product_id, $country_name, $country_data);
            
            // RECREAR ATRIBUTOS Y VARIACIONES
            $this->delete_product_variations($product_id);
            $this->create_product_attributes($product_id, $country_data);
            $this->create_product_variations($product_id, $country_data);
            
            // Sincronizar precios
            WC_Product_Variable::sync($product_id);
            
            // IMPORTANTE: Forzar que solo muestre precio mínimo
            $min_price = $this->get_minimum_price($country_data);
            delete_post_meta($product_id, '_max_variation_price');
            delete_post_meta($product_id, '_max_variation_regular_price');
            update_post_meta($product_id, '_price', $min_price);
            update_post_meta($product_id, '_min_variation_price', $min_price);
            update_post_meta($product_id, '_min_variation_regular_price', $min_price);
            
            error_log("Producto variable actualizado: $translated_name eSIM (ID: $product_id)");
            
            return array(
                'success' => true,
                'action' => 'updated',
                'product_id' => $product_id
            );
            
        } catch (Exception $e) {
            error_log("Error actualizando producto $country_name: " . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    private function build_product_description($country_name, $country_data) {
        $html = '<div class="esim-product-description">';
        
        // Intro
        $html .= '<h2>🌐 eSIM para ' . esc_html($country_name) . '</h2>';
        $html .= '<p class="esim-intro">Conectividad instantánea con eSIM digital. Sin SIM física, sin esperas. Recibe tu código QR por email y actívalo en segundos.</p>';
        
        // Qué es eSIM
        $html .= '<div class="esim-what-is">';
        $html .= '<h3>📱 ¿Qué es una eSIM?</h3>';
        $html .= '<p>Una eSIM (SIM integrada) es una tarjeta SIM digital que te permite activar un plan de datos sin necesidad de una tarjeta SIM física. Todo se hace mediante un código QR que escaneas con tu dispositivo.</p>';
        

        $html .= '</div>';
        
        // Cobertura
        $html .= '<div class="esim-coverage">';
        $html .= '<h3>🗺️ Cobertura</h3>';
        
        $countries = $country_data['countries'] ?? array();
        $country_count = count($countries);
        
        // Si es multi-país, destacar primero
        if ($country_count > 1) {
            $html .= '<p class="coverage-highlight"><strong>✈️ Cobertura en ' . $country_count . ' países</strong></p>';
        }
        
        $html .= '<p><strong>Países incluidos:</strong></p>';
        $html .= '<ul class="country-list">';
        
        foreach ($countries as $country) {
            // Traducir nombre del país
            $translated_country = $this->translate_country_name($country['name']);
            
            $operators = array();
            if (!empty($country['operatorInfo'])) {
                foreach ($country['operatorInfo'] as $op) {
                    $operators[] = $op['operator'] . ' (' . $op['network'] . ')';
                }
            }
            
            $html .= '<li><strong>' . esc_html($translated_country) . '</strong>';
            if (!empty($operators)) {
                $html .= '<br><span class="operators">Operadores: ' . implode(', ', $operators) . '</span>';
            }
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        
        if ($country_count > 1) {
            $html .= '<p class="coverage-note"><em>💡 Un solo plan para todos estos destinos. Cambia de país sin cambiar de SIM.</em></p>';
        }
        

        $html .= '</div>';
        
        // Tipos de planes disponibles
        $has_packages = !empty($country_data['total_packages']);
        $has_passes = !empty($country_data['daily_passes']);
        
        $html .= '<div class="esim-plan-types">';
        $html .= '<h3>📊 Tipos de Planes Disponibles</h3>';
        
        if ($has_packages) {
            $html .= '<div class="plan-type-card">';
            $html .= '<h4>📦 Paquetes Totales</h4>';
            $html .= '<p><strong>Flexibilidad Total:</strong> Elige la cantidad de GB y los días de validez. Usa tus datos como quieras, cuando quieras.</p>';
            $html .= '<ul>';
            $html .= '<li>✅ Elige tus GB (1GB, 3GB, 5GB, 10GB, 20GB, 50GB)</li>';
            $html .= '<li>✅ Elige tus días (7, 15, 30, 60, 90 días)</li>';
            $html .= '<li>✅ Usa los datos a tu ritmo</li>';
            $html .= '<li>✅ Ideal para viajes cortos con uso variable</li>';
            $html .= '</ul>';
        

            $html .= '</div>';
        }
        
        if ($has_passes) {
            $html .= '<div class="plan-type-card">';
            $html .= '<h4>🔄 Pases Diarios</h4>';
            $html .= '<p><strong>Renovación Diaria:</strong> Recibe una cantidad fija de GB cada día. Perfecto para uso constante.</p>';
            $html .= '<ul>';
            $html .= '<li>✅ GB diarios que se renuevan automáticamente</li>';
            $html .= '<li>✅ Disponible en 500MB/día, 1GB/día, 2GB/día, 5GB/día</li>';
            $html .= '<li>✅ Elige la duración (7, 15, 30, 60 días)</li>';
            $html .= '<li>✅ Ideal para viajes largos con uso predecible</li>';
            $html .= '</ul>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Cómo funciona
        $html .= '<div class="esim-how-it-works">';
        $html .= '<h3>🚀 ¿Cómo Funciona?</h3>';
        $html .= '<ol class="steps-list">';
        $html .= '<li><strong>Compra:</strong> Selecciona tu plan y completa el pago</li>';
        $html .= '<li><strong>Recibe:</strong> Código QR enviado a tu email en minutos</li>';
        $html .= '<li><strong>Escanea:</strong> Abre la configuración de tu dispositivo y escanea el QR</li>';
        $html .= '<li><strong>Activa:</strong> Tu eSIM se instala automáticamente</li>';
        $html .= '<li><strong>Conecta:</strong> ¡Listo! Ya tienes datos en ' . esc_html($country_name) . '</li>';
        $html .= '</ol>';
        

        $html .= '</div>';
        
        // Características
        $html .= '<div class="esim-features">';
        $html .= '<h3>✨ Características</h3>';
        $html .= '<ul class="features-list">';
        $html .= '<li>✅ <strong>Activación instantánea</strong> - Código QR por email</li>';
        $html .= '<li>✅ <strong>Sin SIM física</strong> - 100% digital</li>';
        $html .= '<li>✅ <strong>Sin contrato</strong> - Prepago sin compromisos</li>';
        $html .= '<li>✅ <strong>Fácil instalación</strong> - Solo escanea el QR</li>';
        $html .= '<li>✅ <strong>Mantén tu número</strong> - Dual SIM compatible</li>';
        $html .= '<li>✅ <strong>Soporte 24/7</strong> - Estamos aquí para ayudarte</li>';
        $html .= '</ul>';
        

        $html .= '</div>';
        
        // Compatibilidad
        $html .= '<div class="esim-compatibility">';
        $html .= '<h3>📱 Compatibilidad</h3>';
        $html .= '<p><strong>Dispositivos compatibles:</strong></p>';
        $html .= '<ul>';
        $html .= '<li>iPhone XS, XR y modelos posteriores</li>';
        $html .= '<li>Samsung Galaxy S20, S21, S22, S23, S24 y posteriores</li>';
        $html .= '<li>Google Pixel 3, 4, 5, 6, 7, 8 y posteriores</li>';
        $html .= '<li>iPad Pro (2018 y posteriores), iPad Air, iPad Mini</li>';
        $html .= '<li>Huawei P40, Mate 40 y posteriores</li>';
        $html .= '<li>Y muchos más dispositivos con soporte eSIM</li>';
        $html .= '</ul>';
        $html .= '<p><em>Verifica si tu dispositivo soporta eSIM en la configuración.</em></p>';
        

        $html .= '</div>';
        
        // Importante
        $html .= '<div class="esim-important">';
        $html .= '<h3>⚠️ Importante</h3>';
        $html .= '<ul>';
        $html .= '<li>Tu dispositivo debe ser compatible con eSIM</li>';
        $html .= '<li>Necesitas conexión WiFi para activar la eSIM</li>';
        $html .= '<li>El QR code es de un solo uso</li>';
        $html .= '<li>Guarda el email con el QR en un lugar seguro</li>';
        $html .= '</ul>';
        

        $html .= '</div>';
        
        

        $html .= '</div>';
        
        return $html;
    }
    
    private function build_short_description($country_name, $country_data) {
        $country_count = count($country_data['countries'] ?? array());
        $min_price = $this->get_minimum_price($country_data);
        
        $desc = 'eSIM digital para ' . esc_html($country_name) . '. ';
        $desc .= 'Activación instantánea con código QR. ';
        $desc .= 'Sin SIM física, sin esperas. ';
        
        if ($country_count > 1) {
            $desc .= 'Cobertura en ' . $country_count . ' países. ';
        }
        
        $desc .= 'Configura tu plan perfecto: elige datos y duración según tus necesidades. ';
        
        if ($min_price < PHP_FLOAT_MAX) {
            $desc .= '<strong>Desde $' . number_format($min_price, 2) . ' USD</strong>';
        }
        
        return $desc;
    }
    
    private function get_minimum_price($country_data) {
        $min_price = PHP_FLOAT_MAX;
        
        // Buscar en paquetes totales
        foreach ($country_data['total_packages'] ?? array() as $plan) {
            foreach ($plan['prices'] ?? array() as $price) {
                $retail = floatval($price['retailPrice'] ?? 0);
                if ($retail > 0 && $retail < $min_price) {
                    $min_price = $retail;
                }
            }
        }
        
        // Buscar en pases diarios
        foreach ($country_data['daily_passes'] ?? array() as $plan) {
            foreach ($plan['prices'] ?? array() as $price) {
                $retail = floatval($price['retailPrice'] ?? 0);
                if ($retail > 0 && $retail < $min_price) {
                    $min_price = $retail;
                }
            }
        }
        
        return ($min_price === PHP_FLOAT_MAX) ? 0 : $min_price;
    }
    
    private function save_country_product_meta($product_id, $country_name, $country_data) {
        // Identificador de país
        update_post_meta($product_id, '_country_esim_name', $country_name);
        update_post_meta($product_id, '_is_esim_product', 'yes');
        
        // Guardar todos los planes como JSON
        update_post_meta($product_id, '_esim_total_packages', json_encode($country_data['total_packages']));
        update_post_meta($product_id, '_esim_daily_passes', json_encode($country_data['daily_passes']));
        
        // Información de países
        update_post_meta($product_id, '_esim_countries', json_encode($country_data['countries']));

        // Guardar la bandera del primer país como la bandera principal del producto
        if (!empty($country_data['countries'][0]['url'])) {
            update_post_meta($product_id, '_esim_country_flag_url', $country_data['countries'][0]['url']);
        }
        
        // Timestamp
        update_post_meta($product_id, '_esim_last_sync', current_time('mysql'));
    }
    
    private function assign_categories($product_id, $country_name, $country_data) {
        $categories = array();
        
        // Solo categoría principal eSIM
        $esim_cat = $this->ensure_category('eSIM');
        if ($esim_cat) {
            $categories[] = $esim_cat;
        }
        
        // NO crear subcategorías automáticamente con nombres en otros idiomas
        // El usuario puede asignar categorías manualmente si lo desea
        
        wp_set_object_terms($product_id, array_unique($categories), 'product_cat');
    }
    
    private function ensure_category($name, $parent = 0) {
        $term = get_term_by('name', $name, 'product_cat');
        
        if (!$term) {
            $result = wp_insert_term($name, 'product_cat', array(
                'parent' => $parent
            ));
            
            if (is_wp_error($result)) {
                return null;
            }
            
            return $result['term_id'];
        }
        
        return $term->term_id;
    }
    
    /**
     * Crear atributos del producto (pa_plan_type, pa_datos, pa_dias)
     */
    private function create_product_attributes($product_id, $country_data) {
        error_log("=== CREANDO ATRIBUTOS PARA PRODUCTO $product_id ===");
        
        $all_data_options = array();
        $all_days_options = array();
        
        // Recopilar todas las opciones únicas de datos y días
        $total_packages = $country_data['total_packages'] ?? array();
        error_log("Total packages disponibles: " . count($total_packages));
        
        foreach ($total_packages as $plan) {
            $capacity_gb = $this->format_capacity_to_gb($plan['capacity'] ?? '0');
            if (!$capacity_gb) continue; // Ignorar planes con 0GB

            $capacity_kb = floatval($plan['capacity'] ?? '0');
            error_log("Paquete Total - Capacidad: $capacity_gb ($capacity_kb KB)");
            if ($capacity_gb && !isset($all_data_options[$capacity_gb])) {
                $all_data_options[$capacity_gb] = $capacity_kb; // Guardar KB para ordenar
            }
            
            // CRÍTICO: Extraer días de COPIES en pricing, no del nombre
            $prices = $plan['prices'] ?? array();
            foreach ($prices as $price_item) {
                $copies = intval($price_item['copies'] ?? 0);
                if ($copies > 0) {
                    $days_label = $copies . ($copies == 1 ? ' Día' : ' Días');
                    if (!isset($all_days_options[$days_label])) {
                        $all_days_options[$days_label] = $copies;
                        error_log("Paquete Total - Días desde copies: $copies");
                    }
                }
            }
        }
        
        $daily_passes = $country_data['daily_passes'] ?? array();
        error_log("Daily passes disponibles: " . count($daily_passes));
        
        foreach ($daily_passes as $plan) {
            $high_flow = $this->format_capacity_to_gb($plan['highFlowSize'] ?? '0');
            if (!$high_flow) continue; // Ignorar planes con 0GB

            $high_flow_kb = floatval($plan['highFlowSize'] ?? '0');
            error_log("Pase Diario - High Flow: $high_flow ($high_flow_kb KB)");
            $data_label = $high_flow . '/día';
            if (!isset($all_data_options[$data_label])) {
                $all_data_options[$data_label] = $high_flow_kb; // Guardar KB para ordenar
            }
            
            // CRÍTICO: Extraer días de COPIES en pricing, no del nombre
            $prices = $plan['prices'] ?? array();
            foreach ($prices as $price_item) {
                $copies = intval($price_item['copies'] ?? 0);
                if ($copies > 0) {
                    $days_label = $copies . ($copies == 1 ? ' Día' : ' Días');
                    if (!isset($all_days_options[$days_label])) {
                        $all_days_options[$days_label] = $copies;
                        error_log("Pase Diario - Días desde copies: $copies");
                    }
                }
            }
        }
        
        // ORDENAR de menor a mayor por valor en KB/días
        asort($all_data_options);
        asort($all_days_options);
        
        // Convertir a arrays simples solo con las etiquetas
        $all_data_options = array_keys($all_data_options);
        $all_days_options = array_keys($all_days_options);
        
        error_log("Opciones de DATOS: " . implode(', ', $all_data_options));
        error_log("Opciones de DÍAS: " . implode(', ', $all_days_options));
        
        // Registrar atributos globales si no existen
        $this->register_global_attribute('pa_plan_type', 'Tipo de Plan');
        $this->register_global_attribute('pa_datos', 'Datos');
        $this->register_global_attribute('pa_dias', 'Días');
        
        // Crear términos para tipo de plan
        $this->create_attribute_term('pa_plan_type', 'Paquetes Totales');
        $this->create_attribute_term('pa_plan_type', 'Pases Diarios');
        
        // Crear términos para cada opción
        foreach ($all_data_options as $option) {
            $this->create_attribute_term('pa_datos', $option);
            error_log("Término creado pa_datos: $option");
        }
        
        foreach ($all_days_options as $option) {
            $this->create_attribute_term('pa_dias', $option);
            error_log("Término creado pa_dias: $option");
        }
        
        // CRÍTICO: Limpiar cache de taxonomías para que WooCommerce vea los nuevos términos
        wp_cache_flush();
        clean_term_cache(array(), array('pa_plan_type', 'pa_datos', 'pa_dias'));
        error_log("✅ Cache de taxonomías limpiado - Términos listos para usar");
        
        // Asignar atributos al producto
        $product_attributes = array();
        
        $product_attributes['pa_plan_type'] = array(
            'name' => 'pa_plan_type',
            'value' => '',
            'position' => 0,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 1
        );
        
        $product_attributes['pa_datos'] = array(
            'name' => 'pa_datos',
            'value' => '',
            'position' => 1,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 1
        );
        
        $product_attributes['pa_dias'] = array(
            'name' => 'pa_dias',
            'value' => '',
            'position' => 2,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 1
        );
        
        update_post_meta($product_id, '_product_attributes', $product_attributes);
        
        // Asignar términos al producto
        wp_set_object_terms($product_id, array('Paquetes Totales', 'Pases Diarios'), 'pa_plan_type');
        wp_set_object_terms($product_id, $all_data_options, 'pa_datos');
        wp_set_object_terms($product_id, $all_days_options, 'pa_dias');
        
        error_log("Atributos asignados correctamente al producto $product_id");
        error_log("==============================================");
    }
    
    /**
     * Registrar atributo global
     */
    private function register_global_attribute($attribute_name, $attribute_label) {
        global $wpdb;
        
        $attribute_id = $wpdb->get_var($wpdb->prepare(
            "SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
            str_replace('pa_', '', $attribute_name)
        ));
        
        if (!$attribute_id) {
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_attribute_taxonomies',
                array(
                    'attribute_name' => str_replace('pa_', '', $attribute_name),
                    'attribute_label' => $attribute_label,
                    'attribute_type' => 'select',
                    'attribute_orderby' => 'menu_order',
                    'attribute_public' => 0
                )
            );
            
            delete_transient('wc_attribute_taxonomies');
        }
    }
    
    /**
     * Crear término de atributo
     */
    private function create_attribute_term($taxonomy, $term_name) {
        // Limpiar el nombre del término
        $term_name = trim($term_name);
        
        if (empty($term_name)) {
            error_log("⚠️ Término vacío para taxonomía $taxonomy");
            return false;
        }
        
        // Verificar si el término ya existe
        $term = term_exists($term_name, $taxonomy);
        
        if (!$term) {
            // Crear el término
            $result = wp_insert_term($term_name, $taxonomy);
            
            if (is_wp_error($result)) {
                error_log("❌ Error creando término '$term_name' en $taxonomy: " . $result->get_error_message());
                return false;
            }
            
            error_log("✅ Término creado: '$term_name' en $taxonomy (ID: {$result['term_id']})");
            return $result['term_id'];
        }
        
        return is_array($term) ? $term['term_id'] : $term;
    }
    
    /**
     * Crear variaciones del producto
     */
    private function create_product_variations($product_id, $country_data) {
        $variation_count = 0;
        
        error_log("=== CREANDO VARIACIONES PARA PRODUCTO $product_id ===");
        
        // Crear variaciones para paquetes totales
        $total_packages = $country_data['total_packages'] ?? array();
        error_log("Paquetes totales encontrados: " . count($total_packages));
        
        foreach ($total_packages as $plan) {
            $prices = $plan['prices'] ?? array();
            error_log("Plan SKU: " . ($plan['skuId'] ?? 'N/A') . " - Precios: " . count($prices));
            
            foreach ($prices as $price) {
                $var_id = $this->create_single_variation($product_id, $plan, $price, '0');
                error_log("Variación creada: ID=$var_id");
                $variation_count++;
            }
        }
        
        // Crear variaciones para pases diarios
        $daily_passes = $country_data['daily_passes'] ?? array();
        error_log("Pases diarios encontrados: " . count($daily_passes));
        
        foreach ($daily_passes as $plan) {
            $prices = $plan['prices'] ?? array();
            error_log("Plan SKU: " . ($plan['skuId'] ?? 'N/A') . " - Precios: " . count($prices));
            
            foreach ($prices as $price) {
                $var_id = $this->create_single_variation($product_id, $plan, $price, '1');
                error_log("Variación creada: ID=$var_id");
                $variation_count++;
            }
        }
        
        error_log("TOTAL variaciones creadas: $variation_count para producto $product_id");
        error_log("==============================================");
    }
    
    /**
     * Crear una variación individual
     */
    private function create_single_variation($product_id, $plan, $price, $plan_type) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        $variation->set_status('publish');
        $variation->set_virtual(true);
        
        // IMPORTANTE: Marcar como en stock
        $variation->set_manage_stock(false);
        $variation->set_stock_status('instock');
        
        // Determinar tipo de plan y datos
        if ($plan_type === '1') {
            // Pase diario
            $plan_type_label = 'Pases Diarios';
            $high_flow = $this->format_capacity_to_gb($plan['highFlowSize'] ?? '0');
            $data_attr = $high_flow . '/día';
        } else {
            // Paquete total
            $plan_type_label = 'Paquetes Totales';
            $data_attr = $this->format_capacity_to_gb($plan['capacity'] ?? '0');
        }
        
        // CRÍTICO: Usar COPIES del pricing como días
        // copies = cantidad de días que el usuario compra
        $copies = intval($price['copies'] ?? 1);
        $days_attr = $copies . ($copies == 1 ? ' Día' : ' Días');
        
        error_log("SKU: {$plan['skuId']} - Copies: $copies - Datos: $data_attr");
        
        // Obtener los term IDs (no los nombres)
        $plan_type_term = get_term_by('name', $plan_type_label, 'pa_plan_type');
        $datos_term = get_term_by('name', $data_attr, 'pa_datos');
        $dias_term = get_term_by('name', $days_attr, 'pa_dias');
        
        if (!$plan_type_term || !$datos_term || !$dias_term) {
            error_log("❌ ERROR: No se encontraron los términos necesarios");
            error_log("Plan Type Term: " . ($plan_type_term ? 'OK' : 'MISSING'));
            error_log("Datos Term: " . ($datos_term ? 'OK' : 'MISSING - ' . $data_attr));
            error_log("Días Term: " . ($dias_term ? 'OK' : 'MISSING - ' . $days_attr));
            return false;
        }
        
        // Asignar atributos usando los slugs de los términos
        $variation->set_attributes(array(
            'pa_plan_type' => $plan_type_term->slug,
            'pa_datos' => $datos_term->slug,
            'pa_dias' => $dias_term->slug
        ));
        
        // Precio
        $retail_price = floatval($price['retailPrice'] ?? 0);
        $variation->set_regular_price($retail_price);
        $variation->set_price($retail_price);
        
        // Metadata de Billionconnect
        $variation_id = $variation->save();
        
        update_post_meta($variation_id, '_bc_sku_id', $plan['skuId'] ?? '');
        update_post_meta($variation_id, '_bc_plan_type', $plan_type);
        update_post_meta($variation_id, '_bc_copies', $copies); // Cantidad de días comprados
        update_post_meta($variation_id, '_bc_retail_price', $retail_price);
        update_post_meta($variation_id, '_bc_settlement_price', $price['settlementPrice'] ?? '0');
        update_post_meta($variation_id, '_bc_plan_name', $plan['name'] ?? ''); // Guardar nombre completo
        
        if ($plan_type === '1') {
            update_post_meta($variation_id, '_bc_high_flow_size', $plan['highFlowSize'] ?? '0');
        } else {
            update_post_meta($variation_id, '_bc_capacity', $plan['capacity'] ?? '0');
        }
        
        return $variation_id;
    }
    
    /**
     * Eliminar variaciones existentes
     */
    private function delete_product_variations($product_id) {
        $product = wc_get_product($product_id);
        if (!$product || $product->get_type() !== 'variable') {
            return;
        }
        
        $variations = $product->get_children();
        foreach ($variations as $variation_id) {
            wp_delete_post($variation_id, true);
        }
    }
    
    /**
     * Formatear capacidad a GB para mostrar
     */
    private function format_capacity_to_gb($capacity_kb) {
        $kb = floatval($capacity_kb);
        
        if ($kb <= 0) {
            return false;
        }
        
        // Convertir KB a GB
        $gb = $kb / 1024 / 1024;
        
        if ($gb < 1) {
            // Mostrar en MB
            $mb = $kb / 1024;
            return round($mb) . 'MB';
        }
        
        // Mostrar en GB
        if ($gb >= 10) {
            return round($gb) . 'GB';
        }
        
        return number_format($gb, 1) . 'GB';
    }

}
