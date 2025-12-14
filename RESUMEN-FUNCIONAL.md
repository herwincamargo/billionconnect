# 🚀 PLUGIN eSIM - RESUMEN FUNCIONAL

## ✅ QUÉ HACE EL PLUGIN

### 1. SINCRONIZACIÓN AUTOMÁTICA
- Conecta con Billionconnect API (F002, F003)
- Filtra SOLO productos eSIM (tipos 230, 3105, 3106)
- Agrupa por país: **Un producto por país**
- Cada producto = "[País] eSIM"

### 2. PRODUCTOS CON CONFIGURADOR
Cada producto incluye:
- **Descripción completa**: Qué es eSIM, cómo funciona, países cubiertos
- **Lista de países**: Todos los países con operadores
- **Dos tipos de planes**:
  - 📦 Paquetes Totales (elige GB + días)
  - 🔄 Pases Diarios (elige GB/día + días)

### 3. PROCESO DE COMPRA
```
Cliente → Configura plan → Añade al carrito → Paga (tu método)
     ↓
Orden WooCommerce = "Completed"
     ↓
Plugin detecta orden nueva
     ↓
Plugin envía a Billionconnect API (F040) ← AQUÍ
     ↓
Billionconnect procesa y genera eSIM
     ↓
Billionconnect envía webhook (N009) con QR code
     ↓
Cliente recibe email con QR code
```

### 4. INTEGRACIÓN BILLIONCONNECT
**Endpoints Implementados:**
- ✅ F002: Obtener planes
- ✅ F003: Obtener precios
- ✅ F040: **CREAR ORDEN ESIM** ← Principal
- ✅ F041: Reenviar email
- ✅ F011: Consultar estado
- ✅ N009: Webhook QR code (recibir)

---

## 📦 EJEMPLO REAL

### Producto Creado:
```
Nombre: España eSIM
Precio: Desde $10
SKU: ESIM-ES

Descripción incluye:
✅ Qué es eSIM (explicación completa)
✅ Cómo funciona (paso a paso)
✅ Países cubiertos: España
✅ Operadores: Movistar, Vodafone, Orange
✅ Redes: 4G/5G
✅ Compatibilidad de dispositivos
✅ Instrucciones de instalación
✅ Preguntas frecuentes
```

### Cliente Compra:
```
1. Cliente va a: "España eSIM"
2. Selecciona pestaña: "Paquetes Totales"
3. Elige: 5GB + 15 días
4. Precio calculado: $35
5. Añade al carrito
6. Completa pago (Stripe, PayPal, etc)
```

### Backend Automático:
```
1. WooCommerce: Orden #1234 = "Completed"
2. Plugin detecta orden
3. Plugin lee configuración: 5GB, 15 días, España
4. Plugin busca SKU correcto en metadata
5. Plugin llama API Billionconnect F040:
   {
     "channelOrderId": "WC-1234-timestamp",
     "subOrderList": [{
       "skuId": "ESP-5GB-15D",
       "quantity": "1",
       "email": "cliente@example.com"
     }]
   }
6. Billionconnect responde: Order ID BC-789
7. Plugin guarda en orden: _billionconnect_order_id = "BC-789"
8. Plugin añade nota: "✅ eSIM creada en Billionconnect"
9. Orden cambia a "Completed"
```

### Webhook N009 (cuando Billionconnect genera QR):
```
Billionconnect → https://tu-sitio.com/?billionconnect-webhook=1
{
  "tradeType": "N009",
  "tradeData": {
    "orderId": "BC-789",
    "subOrderList": [{
      "iccid": "89860012018500000085",
      "qrCodeContent": "LPA:1$...",
      "apn": "internet",
      "pin": "1234",
      "validTime": "2025-01-01"
    }]
  }
}

Plugin recibe → Guarda QR → Envía email al cliente
```

---

## 🎯 LO QUE DEBES HACER

### 1. Instalar Plugin
- Subir ZIP
- Activar

### 2. Configurar API
```
WordPress → Connectivity Plans → Configuración
- API Key (Partner): tu-api-key
- API Secret: tu-api-secret
- Test Connection
```

### 3. Sincronizar
```
Connectivity Plans → Sincronizar
Click: "Sincronizar Ahora"
Esperar: 1-2 minutos
Resultado: Productos creados por país
```

### 4. Configurar Webhook en Billionconnect
```
En panel de Billionconnect, configurar:
Webhook URL: https://tu-sitio.com/?billionconnect-webhook=1
Eventos: N009 (QR code notice)
```

### 5. Probar
```
1. Ver producto en tienda
2. Configurar plan de prueba
3. Hacer compra de prueba
4. Verificar que orden se envía a Billionconnect
5. Revisar logs en WooCommerce → Estado → Registros
```

---

## 🔍 VERIFICAR QUE FUNCIONA

### Logs a Revisar:
```
WooCommerce → Estado → Registros
Buscar: "billionconnect"

Verás:
✅ "Calling Billionconnect API F040 for Order #123"
✅ "SUCCESS! Order #123 processed. Billionconnect Order ID: BC-789"
✅ "Order #123 completed successfully"
```

### En la Orden:
```
Orden #123
Estado: Completed
Notas:
- "✅ eSIM creada exitosamente en Billionconnect"
- "Billionconnect Order ID: BC-789"
- "Email del cliente: cliente@example.com"
- "El cliente recibirá el código QR por email..."
```

---

## ⚠️ IMPORTANTE

### El plugin NO maneja pagos
- Tú implementas el método de pago (Stripe, PayPal, etc)
- Plugin solo procesa cuando orden = "Completed"

### El plugin NO envía emails directamente
- Billionconnect envía el email con QR code
- Plugin recibe webhook y lo guarda
- Puedes personalizar email de confirmación de WooCommerce

### Datos en tiempo real
- Todo viene de API de Billionconnect
- Productos se actualizan diariamente
- Precios siempre actualizados

---

## 📊 METADATA

### Por Producto:
```
_country_esim_name: "España"
_is_esim_product: "yes"
_esim_total_packages: JSON con todos los paquetes
_esim_daily_passes: JSON con todos los pases
_esim_countries: JSON con países
```

### Por Orden:
```
_billionconnect_processed: true
_billionconnect_order_id: "BC-789"
_billionconnect_channel_order_id: "WC-123-timestamp"
_billionconnect_processed_date: "2024-12-12 20:00:00"
_esim_sku_id: SKU seleccionado por cliente
```

---

## ✅ CHECKLIST FINAL

Antes de lanzar en producción:

- [ ] API configurada y probada
- [ ] Sincronización ejecutada
- [ ] Productos visibles en tienda
- [ ] Webhook configurado en Billionconnect
- [ ] Orden de prueba procesada exitosamente
- [ ] Cliente de prueba recibió QR code
- [ ] Logs verificados sin errores
- [ ] Email de confirmación personalizado
- [ ] Políticas de devolución configuradas
- [ ] Términos y condiciones actualizados

---

**Todo listo para vender eSIMs reales con Billionconnect** 🚀
