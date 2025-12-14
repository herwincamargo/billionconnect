# 🔍 DIAGNÓSTICO DE ERROR API 1003

## ❌ ERROR ACTUAL

```
API Response: {"tradeCode":"1003","tradeMsg":"参数错误","tradeData":null}
Traducción: "Error de parámetros"
```

## 🔧 CORRECCIÓN APLICADA

**Problema:** El parámetro `networkOperatorScope` puede no ser aceptado por tu cuenta.

**Solución:** Eliminado `networkOperatorScope` del request F002.

### ANTES:
```php
$params = array(
    'salesMethod' => '5',
    'language' => '2',
    'networkOperatorScope' => '2'  // ← Este parámetro causaba error
);
```

### AHORA:
```php
$params = array(
    'salesMethod' => '5',
    'language' => '2'
    // networkOperatorScope eliminado
);
```

---

## 🧪 SI EL ERROR PERSISTE

### Verificación 1: Credenciales API
Ir a: **WP Admin → Connectivity Plans → Settings**

Verificar que estén configurados:
- ✅ API Partner ID (apiKey)
- ✅ API Secret Key
- ✅ Sales Method = "5"

### Verificación 2: Probar Request Manual

Usar Postman o similar para probar:

```
POST https://api-flow.billionconnect.com/Flow/saler/2.0/invoke

Headers:
- Content-Type: application/json
- apiKey: [TU_API_KEY]
- sign: [FIRMA_MD5]

Body:
{
    "tradeType": "F002",
    "tradeTime": "2025-12-12 22:00:00",
    "tradeData": {
        "salesMethod": "5",
        "language": "2"
    }
}
```

**Firma MD5:**
```
MD5(apiKey + tradeTime + tradeType + apiSecret)
```

### Verificación 3: Otros Códigos de Error

| Código | Significado | Solución |
|--------|-------------|----------|
| 1000 | Éxito | ✅ Todo bien |
| 1001 | Firma inválida | Verificar apiKey y apiSecret |
| 1002 | Tiempo expirado | Sincronizar hora del servidor |
| 1003 | Parámetros incorrectos | Verificar formato de params |
| 1004 | Sin permisos | Contactar soporte Billionconnect |

### Verificación 4: Valores de salesMethod

Según tu cuenta, el salesMethod puede ser diferente:

```php
// Intentar en este orden:
'salesMethod' => '5'  // Hero eSIM (predeterminado)
'salesMethod' => '1'  // Distribución directa
'salesMethod' => '3'  // Otro método
```

Para cambiar, ve a: **Settings → Sales Method**

---

## 📞 CONTACTAR SOPORTE BILLIONCONNECT

Si el error persiste después de las verificaciones:

1. **Confirma tu cuenta tiene acceso a F002**
2. **Confirma tu salesMethod correcto**
3. **Pide ejemplo de request válido para F002**

Información para proporcionar:
- API Partner ID (apiKey)
- Código de error: 1003
- Request enviado: (ver debug.log)
- Fecha/hora del error

---

## 🔍 VER LOGS DETALLADOS

Para ver qué se está enviando exactamente:

```bash
# En el servidor
tail -f /path/to/wp-content/debug.log | grep "Billionconnect"
```

Buscar líneas como:
```
Billionconnect API Request (F002): {"tradeType":"F002",...}
Billionconnect API Response (F002): {"tradeCode":"1003",...}
```

---

## ✅ CUANDO FUNCIONE

Deberías ver en el log:
```
API Response: {"tradeCode":"1000","tradeMsg":"Success","tradeData":[...]}
Filtered eSIM plans: 150 products
Países encontrados: 45
Sincronización completada: Creados=45, Actualizados=0
```
