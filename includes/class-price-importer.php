<?php
/**
 * Importador de Precios desde Excel de Billionconnect
 * 
 * Lee archivos Excel con estructura:
 * Plan ID | Plan Name | Country/Region | Data | Days | Settlement Price
 * 
 * @package Connectivity_Plans
 */

if (!defined('ABSPATH')) exit;

require_once(ABSPATH . 'wp-admin/includes/file.php');

class Connectivity_Plans_Price_Importer {
    
    /**
     * Importar precios desde archivo Excel
     */
    public function import_from_excel($file_path) {
        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', 'Archivo no encontrado');
        }
        
        // Verificar si tenemos la librería PhpSpreadsheet
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            return new WP_Error('library_missing', 'PhpSpreadsheet no está disponible');
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $prices_data = array();
            $current_sku = null;
            $row_count = 0;
            
            foreach ($worksheet->getRowIterator() as $row) {
                $row_count++;
                
                // Skip header
                if ($row_count == 1) {
                    continue;
                }
                
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $cells = array();
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }
                
                // Estructura: Plan ID, Plan Name, Single/Multi, Country, Data, Days, Settlement Price
                $plan_id = $cells[0] ?? null;
                $days = $cells[5] ?? null;
                $settlement_price = $cells[6] ?? null;
                
                if ($plan_id) {
                    $current_sku = $plan_id;
                    $prices_data[$current_sku] = array(
                        'plan_name' => $cells[1] ?? '',
                        'country' => $cells[3] ?? '',
                        'data' => $cells[4] ?? '',
                        'prices' => array()
                    );
                }
                
                if ($current_sku && $days && $settlement_price) {
                    $prices_data[$current_sku]['prices'][] = array(
                        'copies' => (string)intval($days),
                        'settlementPrice' => (string)number_format($settlement_price, 2, '.', ''),
                        'retailPrice' => (string)number_format($this->calculate_retail_price($settlement_price), 2, '.', '')
                    );
                }
            }
            
            // Guardar en base de datos
            $this->save_prices_to_db($prices_data);
            
            return array(
                'success' => true,
                'total_skus' => count($prices_data),
                'message' => count($prices_data) . ' SKUs importados correctamente'
            );
            
        } catch (Exception $e) {
            return new WP_Error('import_failed', 'Error al importar: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcular precio retail basado en settlement price + margen
     */
    private function calculate_retail_price($settlement_price) {
        // Obtener margen de ganancia de configuración (default 30%)
        $margin_percentage = floatval(get_option('connectivity_plans_price_margin', 30));
        
        $retail = $settlement_price * (1 + ($margin_percentage / 100));
        
        return $retail;
    }
    
    /**
     * Guardar precios en base de datos
     */
    private function save_prices_to_db($prices_data) {
        update_option('connectivity_plans_imported_prices', $prices_data);
        update_option('connectivity_plans_prices_last_import', current_time('mysql'));
        
        error_log("💾 Precios importados: " . count($prices_data) . " SKUs guardados");
    }
    
    /**
     * Obtener precios importados
     */
    public function get_imported_prices() {
        return get_option('connectivity_plans_imported_prices', array());
    }
    
    /**
     * Obtener precios para un SKU específico
     */
    public function get_prices_for_sku($sku_id) {
        $all_prices = $this->get_imported_prices();
        return $all_prices[$sku_id] ?? null;
    }
    
    /**
     * Verificar si hay precios importados
     */
    public function has_imported_prices() {
        $prices = $this->get_imported_prices();
        return !empty($prices);
    }
}
