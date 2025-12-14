# 🎯 FLUJO REAL COMPLETO - BASADO 100% EN API

## 1️⃣ COMPRA DE eSIM (Primera vez)

### Cliente en la Tienda:
```
1. Cliente visita: tu-sitio.com/product/espana-esim
2. Ve toda la información (países, operadores, planes)
3. Selecciona configuración:
   - Tipo: Paquete Total
   - Datos: 5GB
   - Días: 15
4. Precio mostrado: $35 (viene de F003)
5. Añade al carrito
6. Va al checkout
7. Paga con Stripe (tu implementación)
8. WooCommerce crea Orden #123
9. Estado: "Completed"
```

### Backend Automático:
```
10. Plugin detecta orden #123
11. Plugin lee metadata del item:
    - SKU ID: ESP-5GB-15D (guardado al añadir carrito)
    - Cantidad: 1
    - Email: cliente@mail.com
    
12. Plugin prepara request F040:
    {
      "tradeType": "F040",
      "tradeData": {
        "channelOrderId": "WC-123-1702409876",
        "subOrderList": [{
          "channelSubOrderId": "WC-123-1",
          "skuId": "ESP-5GB-15D",
          "quantity": "1",
          "email": "cliente@mail.com",
          "emailLanguage": "2"
        }]
      }
    }
    
13. Plugin envía a Billionconnect

14. Billionconnect responde:
    {
      "tradeCode": "1000",
      "tradeMsg": "success",
      "tradeData": {
        "orderId": "BC-789456"
      }
    }
    
15. Plugin guarda en orden:
    - _billionconnect_order_id: "BC-789456"
    - _billionconnect_processed: true
    
16. Plugin añade nota:
    "✅ eSIM creada en Billionconnect
    Order ID: BC-789456
    Email: cliente@mail.com
    El cliente recibirá el QR por email de Billionconnect"
```

### Billionconnect Procesa:
```
17. Billionconnect genera eSIM
18. Billionconnect crea QR code
19. Billionconnect ENVÍA EMAIL automáticamente a cliente@mail.com
    
    Email contiene:
    - Asunto: Your eSIM QR Code
    - QR code para escanear
    - ICCID: 89860012018500000085
    - Instrucciones de instalación
    - Información de APN (si necesario)
    
20. Billionconnect envía webhook N009:
    POST https://tu-sitio.com/?billionconnect-webhook=1
    {
      "tradeType": "N009",
      "tradeData": {
        "orderId": "BC-789456",
        "subOrderList": [{
          "iccid": "89860012018500000085",
          "qrCodeContent": "LPA:1$...",
          "apn": "internet",
          "pin": "1234",
          "validTime": "2025-12-27"
        }]
      }
    }
    
21. Plugin recibe webhook
22. Plugin guarda ICCID y QR en orden
23. Plugin añade nota: "✅ QR code recibido"
```

### Cliente Recibe:
```
24. Cliente ve email de Billionconnect en su bandeja
25. Cliente abre email
26. Cliente ve QR code
27. Cliente sigue instrucciones
28. Cliente escanea QR
29. eSIM se instala
30. ¡Cliente tiene datos!
```

---

## 2️⃣ RECARGA DE eSIM (Cliente se quedó sin datos)

### Cliente Necesita Más Datos:
```
1. Cliente se quedó sin datos
2. Cliente va a: tu-sitio.com/recargar-esim
3. Cliente ve formulario:
   "Ingresa tu ICCID"
4. Cliente busca su ICCID:
   - iPhone: Configuración > General > Información
   - Android: Configuración > SIM
   - O en el email original
5. Cliente ingresa: 89860012018500000085
6. Cliente hace clic: "Verificar ICCID"
```

### Plugin Consulta Billionconnect:
```
7. Plugin recibe ICCID
8. Plugin llama F052:
   POST https://api-flow.billionconnect.com/...
   {
     "tradeType": "F052",
     "tradeData": {
       "iccid": "89860012018500000085"
     }
   }
   
9. Billionconnect responde:
   {
     "tradeCode": "1000",
     "tradeData": {
       "skuId": [
         "ESP-3GB-15D",
         "ESP-5GB-30D",
         "ESP-10GB-60D"
       ]
     }
   }
   
10. Plugin tiene lista de SKUs disponibles para recarga
11. Plugin llama F002 para obtener detalles de esos SKUs
12. Plugin obtiene:
    - ESP-3GB-15D: "España 3GB - 15 días" - $25
    - ESP-5GB-30D: "España 5GB - 30 días" - $45  
    - ESP-10GB-60D: "España 10GB - 60 días" - $80
```

### Cliente Selecciona Plan:
```
13. Plugin muestra al cliente:
    
    ┌────────────────────────────────┐
    │ España 3GB - 15 días           │
    │ 📦 Paquete Total               │
    │ 💾 3GB totales                 │
    │ 📅 15 días                     │
    │ 💰 $25.00                      │
    │ [Recargar con este plan]      │
    └────────────────────────────────┘
    
    ┌────────────────────────────────┐
    │ España 5GB - 30 días           │
    │ 📦 Paquete Total               │
    │ 💾 5GB totales                 │
    │ 📅 30 días                     │
    │ 💰 $45.00                      │
    │ [Recargar con este plan] ← Elige este
    └────────────────────────────────┘
    
    ┌────────────────────────────────┐
    │ España 10GB - 60 días          │
    │ 📦 Paquete Total               │
    │ 💾 10GB totales                │
    │ 📅 60 días                     │
    │ 💰 $80.00                      │
    │ [Recargar con este plan]      │
    └────────────────────────────────┘

14. Cliente hace clic en "España 5GB - 30 días - $45"
15. Plugin añade al carrito con metadata:
    - esim_recharge: true
    - esim_iccid: 89860012018500000085
    - esim_sku_id: ESP-5GB-30D
16. Cliente va al checkout
17. Cliente paga $45
18. WooCommerce crea Orden #456
19. Estado: "Completed"
```

### Plugin Procesa Recarga:
```
20. Plugin detecta orden #456
21. Plugin ve que es recarga (esim_recharge: true)
22. Plugin prepara request F007:
    {
      "tradeType": "F007",
      "tradeData": {
        "channelOrderId": "WC-TOPUP-456-1702410000",
        "subOrderList": [{
          "channelSubOrderId": "WC-TOPUP-456-1",
          "iccid": ["89860012018500000085"],
          "skuId": "ESP-5GB-30D",
          "copies": "1"
        }]
      }
    }
    
23. Plugin envía a Billionconnect

24. Billionconnect responde:
    {
      "tradeCode": "1000",
      "tradeMsg": "Success",
      "tradeData": {
        "orderId": "BC-TOP-123456"
      }
    }
    
25. Plugin guarda en orden:
    - _billionconnect_topup_id: "BC-TOP-123456"
    
26. Plugin añade nota:
    "✅ Recarga procesada
    ICCID: 89860012018500000085
    Plan: España 5GB - 30 días
    Top-up Order ID: BC-TOP-123456"
```

### Billionconnect Aplica Recarga:
```
27. Billionconnect procesa la recarga
28. Billionconnect añade 5GB y 30 días a ese ICCID
29. Billionconnect envía webhook N013:
    {
      "tradeType": "N013",
      "tradeData": {
        "orderId": "BC-TOP-123456",
        "iccid": "89860012018500000085",
        "status": "success"
      }
    }
    
30. Plugin recibe webhook
31. Plugin confirma recarga exitosa
32. ¡Cliente tiene 5GB más por 30 días!
```

---

## 3️⃣ DÍAS VARIABLES (VIENEN DE LA API)

### Lo que hace la API:
```
F002 devuelve:
{
  "tradeData": [
    {
      "skuId": "ESP-1GB-1D",
      "days": "1",       ← 1 DÍA
      "capacity": "1048576"
    },
    {
      "skuId": "ESP-2GB-3D",
      "days": "3",       ← 3 DÍAS
      "capacity": "2097152"
    },
    {
      "skuId": "ESP-5GB-7D",
      "days": "7",       ← 7 DÍAS
      "capacity": "5242880"
    },
    {
      "skuId": "ESP-10GB-15D",
      "days": "15",      ← 15 DÍAS
      "capacity": "10485760"
    },
    {
      "skuId": "ESP-20GB-30D",
      "days": "30",      ← 30 DÍAS
      "capacity": "20971520"
    }
  ]
}
```

### Lo que hace el plugin:
```
1. Lee el campo "days" de cada plan
2. NO asume nada
3. Muestra exactamente lo que viene de la API
4. Si mañana Billionconnect agrega planes de 2 días, 90 días, 180 días
   → El plugin los mostrará automáticamente
```

---

## 4️⃣ EMAILS (BILLIONCONNECT LOS ENVÍA)

### ❌ Lo que NO hace el plugin:
```
- NO crea plantillas de email
- NO envía emails al cliente
- NO personaliza el contenido del email
```

### ✅ Lo que SÍ hace el plugin:
```
1. Envía orden a Billionconnect (F040 o F007)
2. Guarda Order ID cuando responde
3. Espera webhook N009/N013
4. Guarda ICCID/QR cuando llega webhook
```

### ✅ Lo que hace Billionconnect:
```
1. Recibe orden del plugin
2. Genera eSIM
3. Genera QR code
4. ENVÍA EMAIL automáticamente al cliente
5. Email incluye:
   - Subject: Your eSIM QR Code
   - QR code (imagen)
   - ICCID
   - APN info
   - Instrucciones
6. Envía webhook al plugin confirmando
```

### Si el cliente NO recibe el email:
```
1. Admin va a WooCommerce → Orden #123
2. Orden tiene acción: "📧 Resend eSIM Email"
3. Admin hace clic
4. Plugin llama F041:
   {
     "tradeType": "F041",
     "tradeData": {
       "orderId": "BC-789456",
       "email": "cliente@mail.com"
     }
   }
5. Billionconnect REENVÍA el email
6. Cliente recibe email nuevamente
```

---

## 5️⃣ RESUMEN EJECUTIVO

### El Plugin:
✅ Sincroniza productos de Billionconnect
✅ Muestra opciones dinámicas (días, GB, precios)
✅ Envía órdenes a Billionconnect (F040, F007)
✅ Recibe webhooks (N009, N010, N013)
✅ Permite recargas consultando F052
✅ Todo basado en datos reales de la API

### Billionconnect:
✅ Provee los planes (F002)
✅ Provee los precios (F003)
✅ Crea eSIMs (F040)
✅ Envía emails con QR automáticamente
✅ Procesa recargas (F007)
✅ Notifica vía webhooks

### Lo que TÚ implementas:
✅ Método de pago (Stripe ya lo tienes)
✅ Diseño/tema de la tienda
✅ Páginas informativas
✅ Política de devoluciones
✅ Soporte al cliente

---

**TODO funciona con datos reales de la API** ✅  
**NADA es inventado por nosotros** ✅  
**Los emails los envía Billionconnect** ✅
