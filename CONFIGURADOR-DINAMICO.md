# 🎛️ CONFIGURADOR DINÁMICO DE PRECIOS

## 📋 CONCEPTO

El cliente ve:

```
┌─────────────────────────────────────────────┐
│ España eSIM                                 │
│ Desde $15.00 USD                            │
└─────────────────────────────────────────────┘

[Descripción del producto con tabla de ejemplos]

┌─────────────────────────────────────────────┐
│ 🎯 CONFIGURA TU PLAN                        │
│                                             │
│ Tipo de Plan:                               │
│ ( ) 📦 Paquete Total                        │
│ (•) 🔄 Pase Diario                          │
│                                             │
│ Datos:                                      │
│ [Dropdown: 500MB/día, 1GB/día, 2GB/día...] │
│                                             │
│ Duración:                                   │
│ [Dropdown: 1 día, 3 días, 7 días, 15...]   │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ PRECIO TOTAL:                           │ │
│ │ $28.50 USD                              │ │
│ │                                         │ │
│ │ ✅ 1GB cada día durante 15 días         │ │
│ │ 💾 Total: 15GB                          │ │
│ │ 📅 Válido por 15 días desde activación  │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ [AÑADIR AL CARRITO]                         │
└─────────────────────────────────────────────┘
```

---

## 🔧 CÓMO FUNCIONA

### 1. **Metadata Guardada en Producto**
```php
// Guardado en el producto:
_esim_total_packages: [
    {
        "skuId": "ESP-5GB-15D",
        "capacity": 5242880,  // 5GB en KB
        "days": 15,
        "prices": [
            {"quantity": 1, "retailPrice": 35.00}
        ]
    },
    ...
]

_esim_daily_passes: [
    {
        "skuId": "ESP-1GB-15D-DAILY",
        "highFlowSize": 1048576,  // 1GB en KB
        "days": 15,
        "prices": [
            {"quantity": 1, "retailPrice": 28.50}
        ]
    },
    ...
]
```

### 2. **JavaScript Lee Metadata**
```javascript
// En la página del producto:
const productData = {
    totalPackages: JSON.parse(productMeta._esim_total_packages),
    dailyPasses: JSON.parse(productMeta._esim_daily_passes)
};

// Cliente selecciona:
planType = 'daily';  // o 'total'
dataAmount = 1;      // GB
days = 15;

// JavaScript busca el precio:
const matchingPlan = findPlan(planType, dataAmount, days);
const price = matchingPlan.prices[0].retailPrice;

// Actualiza UI:
document.getElementById('total-price').textContent = '$' + price + ' USD';
```

### 3. **Al Añadir al Carrito**
```javascript
// Se guarda en cart item:
{
    esim_plan_type: 'daily',
    esim_data_amount: '1',
    esim_days: '15',
    esim_sku_id: 'ESP-1GB-15D-DAILY',
    esim_price: 28.50
}

// WooCommerce calcula el precio total automáticamente
```

---

## 💰 ACTUALIZACIÓN DE PRECIO EN TIEMPO REAL

### Cuando el cliente cambia cualquier opción:

```javascript
function updatePrice() {
    const planType = document.querySelector('input[name="plan_type"]:checked').value;
    const dataAmount = parseFloat(document.getElementById('data_amount').value);
    const days = parseInt(document.getElementById('days').value);
    
    // Buscar plan exacto
    let plans = planType === 'daily' ? productData.dailyPasses : productData.totalPackages;
    
    const matchingPlan = plans.find(plan => {
        const planData = planType === 'daily' 
            ? plan.highFlowSize / 1048576  // Convert to GB
            : plan.capacity / 1048576;
        
        return planData === dataAmount && parseInt(plan.days) === days;
    });
    
    if (matchingPlan && matchingPlan.prices && matchingPlan.prices.length > 0) {
        const price = matchingPlan.prices[0].retailPrice;
        
        // Actualizar UI
        document.getElementById('price-display').innerHTML = 
            '<strong>$' + price.toFixed(2) + ' USD</strong>';
        
        // Actualizar resumen
        updateSummary(planType, dataAmount, days, price);
        
        // Guardar para el carrito
        currentSelection = {
            planType: planType,
            dataAmount: dataAmount,
            days: days,
            skuId: matchingPlan.skuId,
            price: price
        };
    }
}

// Escuchar cambios
document.querySelectorAll('input[name="plan_type"]').forEach(radio => {
    radio.addEventListener('change', updatePrice);
});

document.getElementById('data_amount').addEventListener('change', updatePrice);
document.getElementById('days').addEventListener('change', updatePrice);
```

---

## 📊 EJEMPLOS DE INTERACCIÓN

### Ejemplo 1: Pase Diario
```
Cliente selecciona:
- Tipo: 🔄 Pase Diario
- Datos: 1GB/día
- Duración: 15 días

JavaScript busca en dailyPasses:
- highFlowSize: 1048576 (1GB)
- days: 15
- Encuentra: skuId "ESP-1GB-15D-DAILY"
- prices[0].retailPrice: 28.50

Muestra:
┌─────────────────────────────────┐
│ PRECIO TOTAL: $28.50 USD        │
│                                 │
│ ✅ 1GB cada día durante 15 días │
│ 💾 Total: 15GB                  │
│ 🔄 Renovación diaria            │
└─────────────────────────────────┘
```

### Ejemplo 2: Paquete Total
```
Cliente selecciona:
- Tipo: 📦 Paquete Total
- Datos: 5GB
- Duración: 30 días

JavaScript busca en totalPackages:
- capacity: 5242880 (5GB)
- days: 30
- Encuentra: skuId "ESP-5GB-30D"
- prices[0].retailPrice: 52.00

Muestra:
┌─────────────────────────────────┐
│ PRECIO TOTAL: $52.00 USD        │
│                                 │
│ ✅ 5GB totales                  │
│ 📅 Válido por 30 días           │
│ 💡 Usa cuando quieras           │
└─────────────────────────────────┘
```

### Ejemplo 3: Cliente Cambia Días
```
Estado inicial: 1GB/día × 15 días = $28.50

Cliente cambia duración a: 30 días

JavaScript re-busca:
- highFlowSize: 1048576 (1GB)
- days: 30  ← NUEVO
- Encuentra: skuId "ESP-1GB-30D-DAILY"
- prices[0].retailPrice: 54.00  ← NUEVO

Actualiza automáticamente:
PRECIO TOTAL: $54.00 USD ← Cambia instantáneamente
✅ 1GB cada día durante 30 días ← Actualiza texto
💾 Total: 30GB ← Actualiza total
```

---

## 🎨 RESUMEN VISUAL

### Información SIEMPRE Visible:

```
┌──────────────────────────────────┐
│ 💵 RESUMEN DE TU COMPRA          │
├──────────────────────────────────┤
│ Plan: Pase Diario                │
│ Datos: 1GB/día                   │
│ Duración: 15 días                │
│ Total datos: 15GB                │
│                                  │
│ PRECIO: $28.50 USD               │
│                                  │
│ ⚠️ Los precios están en USD      │
│ El cargo en tu tarjeta depende   │
│ del tipo de cambio de tu banco   │
└──────────────────────────────────┘
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

Frontend (JavaScript):
- [ ] Leer metadata de producto (_esim_total_packages, _esim_daily_passes)
- [ ] Radio buttons para tipo de plan
- [ ] Dropdown dinámico para datos (según tipo)
- [ ] Dropdown dinámico para días (según tipo)
- [ ] Función updatePrice() que busca precio exacto
- [ ] Mostrar "PRECIO TOTAL: $X.XX USD" en grande
- [ ] Mostrar resumen de lo que recibirá
- [ ] Botón "Añadir al Carrito" con metadata correcta

Backend (PHP):
- [x] Metadata guardada correctamente en productos
- [x] Precios reales de F003 en metadata
- [ ] Hook para procesar configuración al añadir carrito
- [ ] Mostrar configuración en carrito con USD
- [ ] Guardar SKU correcto en orden

---

## 🎯 RESULTADO FINAL

**El cliente:**
1. Ve "España eSIM - Desde $15.00 USD"
2. Lee tabla de ejemplos (5-6 opciones)
3. Usa configurador para elegir EXACTAMENTE lo que quiere
4. Ve el precio actualizado EN TIEMPO REAL en USD
5. Añade al carrito
6. Ve resumen claro con precio en USD
7. Paga
8. Recibe eSIM de Billionconnect

**TODO con precios reales de la API** ✅
**TODO en USD** ✅
**Precio actualizado dinámicamente** ✅
