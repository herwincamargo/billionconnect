<?php
/**
 * Region Detector
 * 
 * Detects region name from list of countries
 * 
 * @package Connectivity_Plans
 * @version 5.0.0
 */

if (!defined('ABSPATH')) exit;

class Connectivity_Plans_Region_Detector {
    
    /**
     * Detect region from countries
     * 
     * @param array $countries
     * @return string
     */
    public static function detect($countries) {
        if (empty($countries)) {
            return 'Internacional';
        }
        
        // Use MCC codes to get Spanish names (API returns Chinese names even with language=2)
        $country_names_es = array();
        foreach ($countries as $country) {
            $mcc = $country['mcc'] ?? '';
            $translated = self::translate_mcc_to_spanish($mcc);
            if ($translated) {
                $country_names_es[] = $translated;
            }
        }
        
        if (empty($country_names_es)) {
            return 'Internacional';
        }
        
        $count = count($country_names_es);
        
        // Single country
        if ($count === 1) {
            return $country_names_es[0];
        }
        
        // Two countries
        if ($count === 2) {
            return $country_names_es[0] . ' y ' . $country_names_es[1];
        }
        
        // Three countries
        if ($count === 3) {
            return $country_names_es[0] . ', ' . 
                   $country_names_es[1] . ' y ' . 
                   $country_names_es[2];
        }
        
        // Detect region for 4+ countries
        $region = self::detect_region_from_names($country_names_es);
        if ($region) {
            return "$region ($count países)";
        }
        
        return "Multi-país ($count países)";
    }
    
    /**
     * Detect region from country names (in Spanish)
     */
    private static function detect_region_from_names($country_names) {
        $regions = array(
            'Europa' => array('Alemania', 'Austria', 'Bélgica', 'Bulgaria', 'Chipre', 'Croacia', 'Dinamarca', 'Eslovaquia', 'Eslovenia', 'España', 'Estonia', 'Finlandia', 'Francia', 'Grecia', 'Hungría', 'Irlanda', 'Islandia', 'Italia', 'Letonia', 'Lituania', 'Luxemburgo', 'Malta', 'Noruega', 'Países Bajos', 'Polonia', 'Portugal', 'Reino Unido', 'República Checa', 'Rumania', 'Suecia', 'Suiza'),
            
            'Sudamérica' => array('Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia', 'Ecuador', 'Guyana', 'Guyana Francesa', 'Martinica', 'Paraguay', 'Perú', 'Surinam', 'Uruguay', 'Venezuela'),
            
            'Norteamérica' => array('Canadá', 'Estados Unidos', 'México'),
            
            'Centroamérica y Caribe' => array('Belice', 'Costa Rica', 'Cuba', 'El Salvador', 'Guatemala', 'Haití', 'Honduras', 'Jamaica', 'Nicaragua', 'Panamá', 'Puerto Rico', 'República Dominicana'),
            
            'Asia' => array('Camboya', 'China', 'Corea del Sur', 'Filipinas', 'Hong Kong', 'India', 'Indonesia', 'Japón', 'Laos', 'Malasia', 'Singapur', 'Tailandia', 'Taiwán', 'Vietnam'),
            
            'Oceanía' => array('Australia', 'Nueva Zelanda', 'Fiji')
        );
        
        $count = count($country_names);
        
        foreach ($regions as $region_name => $region_countries) {
            $matches = 0;
            foreach ($country_names as $country) {
                if (in_array($country, $region_countries)) {
                    $matches++;
                }
            }
            
            $percentage = $matches / $count;
            if ($percentage >= 0.6) {
                return $region_name;
            }
        }
        
        return false;
    }
    
    /**
     * Translate English country name to Spanish (kept for fallback)
     */
    private static function translate_to_spanish($name_en) {
        $translations = array(
            // North America
            'United States' => 'Estados Unidos',
            'USA' => 'Estados Unidos',
            'US' => 'Estados Unidos',
            'U.S.A' => 'Estados Unidos',
            'Canada' => 'Canadá',
            'Mexico' => 'México',
            
            // South America
            'Argentina' => 'Argentina',
            'Bolivia' => 'Bolivia',
            'Brazil' => 'Brasil',
            'Chile' => 'Chile',
            'Colombia' => 'Colombia',
            'Ecuador' => 'Ecuador',
            'French Guiana' => 'Guyana Francesa',
            'Guyana' => 'Guyana',
            'Paraguay' => 'Paraguay',
            'Peru' => 'Perú',
            'Suriname' => 'Surinam',
            'Uruguay' => 'Uruguay',
            'Venezuela' => 'Venezuela',
            
            // Central America & Caribbean
            'Belize' => 'Belice',
            'Costa Rica' => 'Costa Rica',
            'Cuba' => 'Cuba',
            'El Salvador' => 'El Salvador',
            'Guatemala' => 'Guatemala',
            'Haiti' => 'Haití',
            'Honduras' => 'Honduras',
            'Jamaica' => 'Jamaica',
            'Martinique' => 'Martinica',
            'Martinique Island' => 'Martinica',
            'Nicaragua' => 'Nicaragua',
            'Panama' => 'Panamá',
            'Puerto Rico' => 'Puerto Rico',
            'Dominican Republic' => 'República Dominicana',
            
            // Europe
            'Austria' => 'Austria',
            'Belgium' => 'Bélgica',
            'Bulgaria' => 'Bulgaria',
            'Croatia' => 'Croacia',
            'Cyprus' => 'Chipre',
            'Czech' => 'República Checa',
            'Czech Republic' => 'República Checa',
            'Czechia' => 'República Checa',
            'Denmark' => 'Dinamarca',
            'Estonia' => 'Estonia',
            'Finland' => 'Finlandia',
            'France' => 'Francia',
            'Germany' => 'Alemania',
            'Greece' => 'Grecia',
            'Hungary' => 'Hungría',
            'Iceland' => 'Islandia',
            'Ireland' => 'Irlanda',
            'Republic of Ireland' => 'Irlanda',
            'Italy' => 'Italia',
            'Latvia' => 'Letonia',
            'Lithuania' => 'Lituania',
            'Luxembourg' => 'Luxemburgo',
            'Malta' => 'Malta',
            'Netherlands' => 'Países Bajos',
            'Norway' => 'Noruega',
            'Poland' => 'Polonia',
            'Portugal' => 'Portugal',
            'Romania' => 'Rumania',
            'Slovakia' => 'Eslovaquia',
            'Slovenia' => 'Eslovenia',
            'Spain' => 'España',
            'Sweden' => 'Suecia',
            'Switzerland' => 'Suiza',
            'United Kingdom' => 'Reino Unido',
            'UK' => 'Reino Unido',
            
            // Asia
            'Cambodia' => 'Camboya',
            'China' => 'China',
            'P.R. China' => 'China',
            'Hong Kong' => 'Hong Kong',
            'India' => 'India',
            'Indonesia' => 'Indonesia',
            'Japan' => 'Japón',
            'Laos' => 'Laos',
            'Malaysia' => 'Malasia',
            'Philippines' => 'Filipinas',
            'Singapore' => 'Singapur',
            'South Korea' => 'Corea del Sur',
            'Taiwan' => 'Taiwán',
            'Thailand' => 'Tailandia',
            'Vietnam' => 'Vietnam',
            
            // Oceania
            'Australia' => 'Australia',
            'Fiji' => 'Fiji',
            'New Zealand' => 'Nueva Zelanda',
            
            // Middle East
            'United Arab Emirates' => 'Emiratos Árabes Unidos',
            'UAE' => 'Emiratos Árabes Unidos',
            'Saudi Arabia' => 'Arabia Saudita',
            'Qatar' => 'Catar',
            'Israel' => 'Israel',
            'Turkey' => 'Turquía',
            
            // Africa
            'South Africa' => 'Sudáfrica',
            'Egypt' => 'Egipto',
            'Morocco' => 'Marruecos',
            'Kenya' => 'Kenia',
            'Nigeria' => 'Nigeria'
        );
        
        return $translations[$name_en] ?? $name_en;
    }
    
    /**
     * Convert MCC code to Spanish country name (PUBLIC for use in descriptions)
     * This is the primary method since API returns Chinese names
     */
    public static function translate_mcc_to_spanish($mcc) {
        $mcc_map = array(
            // North America
            'US' => 'Estados Unidos',
            'USA' => 'Estados Unidos',
            'CA' => 'Canadá',
            'MX' => 'México',
            
            // South America
            'AR' => 'Argentina',
            'BO' => 'Bolivia',
            'BR' => 'Brasil',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'EC' => 'Ecuador',
            'GF' => 'Guyana Francesa',
            'GY' => 'Guyana',
            'MQ' => 'Martinica',
            'PE' => 'Perú',
            'PY' => 'Paraguay',
            'SR' => 'Surinam',
            'UY' => 'Uruguay',
            'VE' => 'Venezuela',
            
            // Central America & Caribbean
            'BZ' => 'Belice',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'DO' => 'República Dominicana',
            'GT' => 'Guatemala',
            'HN' => 'Honduras',
            'HT' => 'Haití',
            'JM' => 'Jamaica',
            'NI' => 'Nicaragua',
            'PA' => 'Panamá',
            'PR' => 'Puerto Rico',
            'SV' => 'El Salvador',
            
            // Europe
            'AT' => 'Austria',
            'BE' => 'Bélgica',
            'BG' => 'Bulgaria',
            'CH' => 'Suiza',
            'CY' => 'Chipre',
            'CZ' => 'República Checa',
            'DE' => 'Alemania',
            'DK' => 'Dinamarca',
            'EE' => 'Estonia',
            'ES' => 'España',
            'FI' => 'Finlandia',
            'FR' => 'Francia',
            'GB' => 'Reino Unido',
            'UK' => 'Reino Unido',
            'GR' => 'Grecia',
            'HR' => 'Croacia',
            'HU' => 'Hungría',
            'IE' => 'Irlanda',
            'IS' => 'Islandia',
            'IT' => 'Italia',
            'LT' => 'Lituania',
            'LU' => 'Luxemburgo',
            'LV' => 'Letonia',
            'MT' => 'Malta',
            'NL' => 'Países Bajos',
            'NO' => 'Noruega',
            'PL' => 'Polonia',
            'PT' => 'Portugal',
            'RO' => 'Rumania',
            'SE' => 'Suecia',
            'SI' => 'Eslovenia',
            'SK' => 'Eslovaquia',
            'TR' => 'Turquía',
            
            // Asia
            'AE' => 'Emiratos Árabes Unidos',
            'CN' => 'China',
            'HK' => 'Hong Kong',
            'ID' => 'Indonesia',
            'IL' => 'Israel',
            'IN' => 'India',
            'JP' => 'Japón',
            'KH' => 'Camboya',
            'KR' => 'Corea del Sur',
            'LA' => 'Laos',
            'MY' => 'Malasia',
            'PH' => 'Filipinas',
            'QA' => 'Catar',
            'SA' => 'Arabia Saudita',
            'SG' => 'Singapur',
            'TH' => 'Tailandia',
            'TW' => 'Taiwán',
            'VN' => 'Vietnam',
            
            // Oceania
            'AU' => 'Australia',
            'FJ' => 'Fiji',
            'NZ' => 'Nueva Zelanda',
            
            // Africa
            'EG' => 'Egipto',
            'KE' => 'Kenia',
            'MA' => 'Marruecos',
            'NG' => 'Nigeria',
            'ZA' => 'Sudáfrica'
        );
        
        return $mcc_map[$mcc] ?? $mcc;
    }
}
