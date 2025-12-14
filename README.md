# Connectivity Plans - eSIM Only v5.1.0

Plugin de WooCommerce para vender eSIMs integrado con Billionconnect API.

## 🎯 Características

### ✅ Un Producto por País
- España eSIM
- Francia eSIM  
- México eSIM
- etc...

### ✅ Configurador con Pestañas
- **Paquetes Totales**: Cliente elige GB + Días
- **Pases Diarios**: Cliente elige GB/día + Días

### ✅ Descripción Completa
Cada producto incluye:
- Lista de países cubiertos
- Operadores disponibles
- Qué es eSIM
- Cómo funciona
- Compatibilidad de dispositivos
- Instrucciones de instalación

### ✅ Integración API Completa
- **F002**: Obtener planes eSIM (filtrado automático)
- **F003**: Obtener precios
- **F040**: Crear orden eSIM ← CRÍTICO
- **F041**: Reenviar email
- **F011**: Consultar estado
- **N009**: Webhook QR code

### ✅ Flujo Completo
1. Cliente configura su plan
2. Añade al carrito
3. Completa pago (tu método)
4. **Plugin envía orden a Billionconnect (F040)**
5. **Billionconnect genera eSIM**
6. **Webhook envía QR code (N009)**
7. **Cliente recibe email con QR**

## 📦 Instalación

1. Subir ZIP en WordPress
2. Activar plugin
3. Ir a Connectivity Plans → Configuración
4. Ingresar API Key y Secret de Billionconnect
5. Probar conexión
6. Sincronizar productos

## ⚙️ Configuración

### Credenciales API
- API Key (Partner)
- API Secret
- Sales Method: 1 (Retail)

### Webhook URL
Configura en Billionconnect:
```
https://tu-sitio.com/?billionconnect-webhook=1
```

## 🔄 Sincronización

El plugin sincroniza automáticamente cada 24 horas.

También puedes sincronizar manualmente en:
`Connectivity Plans → Sincronizar`

## 📱 Productos Creados

Por cada país, se crea un producto con:
- Nombre: `[País] eSIM`
- Tipo: Simple
- Precio: Desde el más económico
- Descripción: Completa y profesional
- Metadata: Todos los planes disponibles

## 🛒 Proceso de Compra

### Frontend
Cliente ve producto → Configura plan → Añade al carrito → Paga

### Backend  
WooCommerce marca orden como "Completed" → Plugin detecta → Envía a Billionconnect F040 → Guarda Billionconnect Order ID → Espera webhook N009 → Cliente recibe QR

## 📝 Logs

Los logs se guardan automáticamente en:
- WooCommerce → Estado → Registros
- Buscar: `billionconnect`

## 🔧 Troubleshooting

### Orden no se procesa
1. Verificar que orden está en estado "Completed"
2. Revisar logs de WooCommerce
3. Verificar credenciales API

### QR no llega al cliente
1. Verificar que webhook está configurado
2. Revisar que Billionconnect procesó la orden
3. Usar "Reenviar Email eSIM" en la orden

## 📊 Metadata Guardada

Cada producto guarda:
- `_country_esim_name`: Nombre del país
- `_is_esim_product`: yes
- `_esim_total_packages`: JSON con paquetes totales
- `_esim_daily_passes`: JSON con pases diarios
- `_esim_countries`: JSON con países cubiertos

Cada orden guarda:
- `_billionconnect_processed`: true
- `_billionconnect_order_id`: ID de Billionconnect
- `_billionconnect_channel_order_id`: ID del canal
- `_billionconnect_processed_date`: Fecha de proceso

## 🎓 Soporte

Para soporte, contactar a través de:
- Email: soporte@heroesim.com
- Web: https://heroesim.com

---

**Versión**: 5.1.0  
**Autor**: HeroeSIM  
**Licencia**: GPL v2 or later
