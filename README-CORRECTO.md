# Connectivity Plans - eSIM Only v5.1.0

## ✅ BASADO 100% EN API DE BILLIONCONNECT

### ⚠️ IMPORTANTE: QUÉ HACE Y QUÉ NO HACE EL PLUGIN

#### ✅ EL PLUGIN SÍ HACE:
1. **Sincroniza productos** de Billionconnect (F002, F003)
2. **Crea productos por país** en WooCommerce
3. **Envía órdenes a Billionconnect** (F040) cuando el cliente paga
4. **Recibe webhooks** de Billionconnect (N009, N010, N013)
5. **Sistema de recargas** basado en F052 y F007

#### ❌ EL PLUGIN NO HACE:
1. **NO envía emails** - Los emails los envía Billionconnect automáticamente
2. **NO inventa opciones** - Todo viene de la API (días, GB, precios)
3. **NO personaliza emails** - Usamos los de Billionconnect
4. **NO maneja pagos** - Eso es WooCommerce/Stripe

---

## 📧 EMAILS (BILLIONCONNECT LOS ENVÍA)

### Flujo de Email:
```
1. Cliente paga orden
2. Plugin envía a Billionconnect (F040)
3. Billionconnect genera eSIM
4. Billionconnect ENVÍA EMAIL con QR code automáticamente
5. Cliente recibe email de Billionconnect
```

### Si necesitas reenviar:
- Usa F041 desde el admin de la orden
- Billionconnect reenviará el email

---

## 🔄 SISTEMA DE RECARGAS

### Cómo Funciona:

**Paso 1: Cliente va a `/recargar-esim`**
```
Cliente ingresa su ICCID
Plugin llama F052 (Query eSIM recharge plans)
API devuelve lista de SKUs disponibles
```

**Paso 2: Mostrar Planes**
```
Plugin obtiene detalles de cada SKU
Muestra:
- Nombre del plan
- Datos (viene de API)
- Días (viene de API) 
- Precio (viene de API)
- Tipo: Paquete o Pase Diario
```

**Paso 3: Cliente Selecciona y Paga**
```
Cliente elige un plan
Se añade al carrito
Cliente paga (tu método de pago)
```

**Paso 4: Procesar Recarga**
```
Plugin detecta orden completada
Plugin llama F007 (Create top-up order) con:
- ICCID del cliente
- SKU seleccionado
Billionconnect procesa la recarga
```

---

## 📊 DÍAS VARIABLES (VIENEN DE LA API)

### ❌ INCORRECTO:
```
Días fijos: 7, 15, 30, 60
```

### ✅ CORRECTO:
```
Días dinámicos de la API:
- 1 día
- 3 días
- 7 días
- 10 días
- 15 días
- 30 días
- 60 días
- 90 días
- Lo que venga en el campo "days" de F002
```

El plugin lee `plan['days']` y usa ese valor exacto.

---

## 🎯 ESTRUCTURA DE PRODUCTOS

### Un Producto por País
```
España eSIM
├─ Descripción con TODA la info de la API:
│  ├─ Países cubiertos (country array)
│  ├─ Operadores (operatorInfo)
│  ├─ Tabla de opciones disponibles
│  └─ Explicación de paquetes vs pases
│
└─ Metadata guardada:
   ├─ _esim_total_packages: JSON con todos los paquetes
   └─ _esim_daily_passes: JSON con todos los pases
```

### Configurador (Frontend)
Cliente selecciona:
1. Tipo: Paquete Total o Pase Diario
2. GB (opciones de la API)
3. Días (opciones de la API)
4. Precio se calcula automáticamente

---

## 🔌 ENDPOINTS USADOS

### Productos:
- **F002**: Get plans (filtra solo eSIM: 230, 3105, 3106)
- **F003**: Get prices

### Órdenes:
- **F040**: Create eSIM order ← PRINCIPAL
- **F041**: Resend email
- **F011**: Query order info

### Recargas:
- **F052**: Query recharge plans (por ICCID)
- **F007**: Create top-up order

### Webhooks:
- **N009**: eSIM QR code notice
- **N010**: Email sent notice
- **N013**: Top-up result notice

---

## 📦 INSTALACIÓN

1. Subir ZIP y activar
2. Configurar API (Key + Secret)
3. Probar conexión
4. Sincronizar productos
5. Configurar webhook en Billionconnect:
   ```
   https://tu-sitio.com/?billionconnect-webhook=1
   ```

---

## 🧪 FLUJO COMPLETO DE PRUEBA

### Compra de eSIM:
```
1. Cliente ve "España eSIM"
2. Cliente configura: 5GB, 15 días, Paquete Total
3. Cliente paga
4. WooCommerce marca orden como "Completed"
5. Plugin detecta y llama F040
6. Billionconnect crea eSIM
7. Billionconnect ENVÍA EMAIL al cliente con QR
8. Cliente recibe email
9. Cliente escanea QR
10. ¡Funciona!
```

### Recarga de eSIM:
```
1. Cliente va a /recargar-esim
2. Cliente ingresa ICCID: 89860012018500000085
3. Plugin llama F052 con ese ICCID
4. API devuelve: ["SKU-123", "SKU-456", "SKU-789"]
5. Plugin obtiene detalles de esos SKUs
6. Muestra opciones al cliente
7. Cliente selecciona "5GB - 30 días - $50"
8. Cliente paga
9. Plugin llama F007 con ICCID + SKU
10. Billionconnect procesa recarga
11. ¡Recarga aplicada!
```

---

## ✅ CHECKLIST

- [ ] API Key configurada
- [ ] Webhook configurado
- [ ] Productos sincronizados
- [ ] Orden de prueba procesada
- [ ] Email recibido (de Billionconnect)
- [ ] Página de recargas creada
- [ ] Recarga de prueba procesada

---

**TODO viene de la API, nada inventado** ✅
