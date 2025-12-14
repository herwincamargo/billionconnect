/**
 * Admin Panel v4.0.0 - JavaScript
 * Manejo de interacciones del panel de administración
 */

(function($) {
    'use strict';
    
    const CPAdmin = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Sync Products
            $('#cp-btn-sync').on('click', this.syncProducts);
            
            // Cleanup Catalog
            $('#cp-btn-cleanup').on('click', this.cleanupCatalog);
            
            // Test API
            $('#cp-btn-test-api').on('click', this.testAPI);
            
            // Reset
            $('#cp-btn-reset').on('click', this.resetPlugin);
        },
        
        /**
         * Sincronizar Productos
         */
        syncProducts: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $status = $('#cp-sync-status');
            const $log = $('#cp-sync-log');
            const $progress = $('#cp-progress-fill');
            
            // Confirmar
            if (!confirm('¿Estás seguro de que deseas sincronizar los productos?\n\nEsto puede tomar varios minutos.')) {
                return;
            }
            
            // Deshabilitar botón
            $btn.prop('disabled', true).html('<span class="cp-loading"></span> Sincronizando...');
            
            // Mostrar status
            $status.show();
            $log.html('');
            $progress.css('width', '10%');
            
            // Log inicial
            CPAdmin.addLog($log, 'info', 'Iniciando sincronización...');
            $progress.css('width', '20%');
            
            // AJAX Request
            $.ajax({
                url: cpAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_sync_products',
                    nonce: cpAdmin.nonce
                },
                success: function(response) {
                    $progress.css('width', '100%');
                    
                    if (response.success) {
                        CPAdmin.addLog($log, 'success', '✅ ' + response.data.message);
                        CPAdmin.addLog($log, 'info', 'Productos creados: ' + response.data.products_created);
                        CPAdmin.addLog($log, 'info', 'Productos actualizados: ' + response.data.products_updated);
                        
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        CPAdmin.addLog($log, 'error', '❌ Error: ' + response.data);
                    }
                },
                error: function(xhr) {
                    $progress.css('width', '100%').css('background', '#dc3545');
                    CPAdmin.addLog($log, 'error', '❌ Error de conexión: ' + xhr.statusText);
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Sincronizar Ahora');
                }
            });
        },
        
        /**
         * Limpiar Catálogo
         */
        cleanupCatalog: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const count = $btn.data('count');
            const $status = $('#cp-cleanup-status');
            const $log = $('#cp-cleanup-log');
            const $progress = $('#cp-cleanup-progress');
            
            // Confirmar
            if (!confirm('⚠️ ADVERTENCIA\n\nEsta acción eliminará PERMANENTEMENTE ' + count + ' productos.\n\nEsta acción NO se puede deshacer.\n\n¿Estás SEGURO de que deseas continuar?')) {
                return;
            }
            
            // Doble confirmación
            const confirmation = prompt('Para confirmar, escribe "ELIMINAR" en mayúsculas:');
            if (confirmation !== 'ELIMINAR') {
                alert('Operación cancelada.');
                return;
            }
            
            // Deshabilitar botón
            $btn.prop('disabled', true).html('<span class="cp-loading"></span> Eliminando...');
            
            // Mostrar status
            $status.show();
            $log.html('');
            $progress.css('width', '10%');
            
            // Log inicial
            CPAdmin.addLog($log, 'info', 'Iniciando limpieza...');
            $progress.css('width', '30%');
            
            // AJAX Request
            $.ajax({
                url: cpAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_cleanup_catalog',
                    nonce: cpAdmin.nonce
                },
                success: function(response) {
                    $progress.css('width', '100%');
                    
                    if (response.success) {
                        CPAdmin.addLog($log, 'success', '✅ ' + response.data.message);
                        CPAdmin.addLog($log, 'info', 'Total eliminado: ' + response.data.deleted);
                        
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        CPAdmin.addLog($log, 'error', '❌ Error: ' + response.data);
                    }
                },
                error: function(xhr) {
                    $progress.css('width', '100%').css('background', '#dc3545');
                    CPAdmin.addLog($log, 'error', '❌ Error de conexión: ' + xhr.statusText);
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Eliminar ' + count + ' Productos');
                }
            });
        },
        
        /**
         * Probar API
         */
        testAPI: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $result = $('#cp-test-api-result');
            
            // Deshabilitar botón
            $btn.prop('disabled', true).html('<span class="cp-loading"></span> Probando...');
            
            // AJAX Request
            $.ajax({
                url: cpAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_test_api',
                    nonce: cpAdmin.nonce
                },
                success: function(response) {
                    $result.show();
                    
                    if (response.success) {
                        $result.removeClass('error').addClass('success');
                        let html = '<strong>' + response.data.message + '</strong><br>';
                        html += 'Planes encontrados: ' + response.data.plans_found;
                        
                        if (response.data.diagnostic) {
                            html += response.data.diagnostic;
                        }
                        
                        $result.html(html);
                    } else {
                        $result.removeClass('success').addClass('error');
                        $result.html('<strong>❌ Error:</strong> ' + response.data);
                    }
                },
                error: function(xhr) {
                    $result.show().removeClass('success').addClass('error');
                    $result.html('<strong>❌ Error de conexión:</strong> ' + xhr.statusText);
                },
                complete: function() {
                    $btn.prop('disabled', false).html('🔌 Probar Conexión');
                }
            });
        },
        
        /**
         * Reset Plugin
         */
        resetPlugin: function(e) {
            e.preventDefault();
            
            if (!confirm('⚠️ ADVERTENCIA: Esto eliminará TODOS los productos y atributos de eSIM. Esta acción NO se puede deshacer. ¿Estás seguro?')) {
                return;
            }
            
            const $btn = $(e.currentTarget);
            $btn.prop('disabled', true).text('🗑️ Eliminando...');
            
            $.ajax({
                url: CPAdminData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'cp_reset_all',
                    nonce: CPAdminData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.data.message);
                        location.reload();
                    } else {
                        alert('❌ Error: ' + response.data);
                        $btn.prop('disabled', false).text('🗑️ Reset Completo');
                    }
                },
                error: function() {
                    alert('❌ Error de comunicación con el servidor');
                    $btn.prop('disabled', false).text('🗑️ Reset Completo');
                }
            });
        },
        
        /**
         * Agregar entrada al log
         */
        addLog: function($log, type, message) {
            const timestamp = new Date().toLocaleTimeString();
            const className = 'cp-log-' + type;
            
            $log.append(
                '<div class="cp-log-entry ' + className + '">' +
                '[' + timestamp + '] ' + message +
                '</div>'
            );
            
            // Auto-scroll al final
            $log.scrollTop($log[0].scrollHeight);
        }
    };
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        CPAdmin.init();
    });
    
})(jQuery);
