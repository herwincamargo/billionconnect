# 🔍 COMPARACIÓN: ¿QUÉ CAMBIÓ?

## ❓ PREGUNTA IMPORTANTE

Mencionaste: **"siempre hemos hecho pruebas con salesMethod 5 y ha funcionado"**

Esto significa que:
1. ✅ Tienes credenciales válidas de Billionconnect
2. ✅ El salesMethod "5" es correcto para tu cuenta
3. ✅ La API funcionaba antes con este código

## 🔎 ¿QUÉ PLUGIN USABAS ANTES?

**Opción A:** ¿Usabas una versión anterior de ESTE plugin?
- Si es así, ¿cuál era el nombre del archivo .zip?
- ¿Puedes compartir el código que funcionaba?

**Opción B:** ¿Usabas otro plugin diferente?
- ¿Cuál era el nombre?
- ¿Cómo se llamaba el método para sincronizar?

## 🎯 LO QUE NECESITAMOS COMPARAR

Para encontrar el problema, necesito ver:

### 1. **Código que funcionaba antes**
```php
// ¿Cómo se generaba el request antes?
// ¿Qué headers se enviaban?
// ¿Cómo se calculaba la firma?
```

### 2. **Log de cuando funcionaba**
```
[fecha] API Request: {... lo que se enviaba antes ...}
[fecha] API Response: {"tradeCode":"1000",...}  ← Éxito
```

### 3. **Código actual (que falla)**
```php
// API Request actual
{
    "tradeType": "F002",
    "tradeTime": "2025-12-13 14:47:25",
    "tradeData": {
        "salesMethod": "5"
    }
}
```

## 🔧 CAMBIOS QUE HE HECHO

### Versión 1 (UTC):
```php
'tradeTime' => gmdate('Y-m-d H:i:s')  // Hora UTC
```

### Si falla, probar Versión 2 (hora local de WordPress):
```php
'tradeTime' => current_time('Y-m-d H:i:s', true)  // UTC vía WordPress
```

### Si falla, probar Versión 3 (hora local):
```php
'tradeTime' => current_time('Y-m-d H:i:s')  // Hora local
```

## 📊 POSIBLES DIFERENCIAS CON CÓDIGO ANTERIOR

| Elemento | Antes (?) | Ahora |
|----------|-----------|-------|
| tradeTime | ¿? | gmdate('Y-m-d H:i:s') |
| Firma MD5 | ¿? | md5(apiKey + json(tradeData) + apiSecret) |
| Headers | ¿? | apiKey, sign, Content-Type |
| JSON encoding | ¿? | JSON_UNESCAPED_SLASHES \| JSON_UNESCAPED_UNICODE |

## 🎯 ACCIÓN REQUERIDA

**Por favor proporciona:**

1. El nombre/versión del plugin que usabas antes
2. O el código del método que hacía el request a F002
3. O un log de cuando funcionaba

Con eso puedo comparar y encontrar exactamente qué cambió.

## 🔥 SOLUCIÓN RÁPIDA (mientras tanto)

He cambiado `tradeTime` a usar **hora UTC** (`gmdate()`).

Prueba el nuevo plugin y dime:
- ¿Qué hora muestra en el log ahora?
- ¿Sigue dando error 1003?
- ¿Qué diferencia hay entre la hora del log y la hora real UTC?
