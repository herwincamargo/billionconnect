# 🔐 PROBAR DIFERENTES MÉTODOS DE FIRMA

El error 1003 persiste. Vamos a probar diferentes formas de calcular la firma MD5.

## 📋 MÉTODOS A PROBAR

### MÉTODO 1 (Actual - NO funciona):
```php
$sign_str = apiKey + JSON(tradeData) + apiSecret
MD5("XXXXX" + '{"salesMethod":"5"}' + "YYYYY")
```

### MÉTODO 2 (Alternativa - probar):
```php
$sign_str = apiKey + tradeType + tradeTime + apiSecret
MD5("XXXXX" + "F002" + "2025-12-13 13:54:09" + "YYYYY")
```

### MÉTODO 3 (Alternativa - probar):
```php
$sign_str = apiKey + JSON(request_completo) + apiSecret
MD5("XXXXX" + '{"tradeType":"F002",...}' + "YYYYY")
```

### MÉTODO 4 (Alternativa - probar):
```php
$sign_str = apiKey + tradeTime + tradeType + JSON(tradeData) + apiSecret
```

## 🎯 INSTRUCCIONES

He agregado logs detallados. Cuando sincronices, verás en debug.log:

```
=== SIGNATURE DEBUG ===
API Key: XXXXXXXXXX...
API Secret: YYYYYYYYYY...
Trade Data JSON: {"salesMethod":"5"}
Sign String: XXXXXXXXXX{"salesMethod":"5"}YYYYYY...
MD5 Signature: abc123def456...
======================
```

**POR FAVOR COPIA ESA SECCIÓN COMPLETA Y ENVÍAMELA**

Con eso puedo:
1. Verificar que apiKey y apiSecret estén configurados
2. Ver exactamente cómo se está calculando la firma
3. Probar el cálculo correcto según documentación Billionconnect

## 🔍 VERIFICACIÓN RÁPIDA

En Settings, verifica que tengas:
- ✅ API Partner ID (apiKey) - NO debe estar vacío
- ✅ API Secret Key - NO debe estar vacío
- ✅ Sales Method = "5"

Si alguno está vacío, ahí está el problema.

## 📞 SI TODO ESTÁ CONFIGURADO

Y el error persiste, entonces necesitamos:

1. La documentación exacta de Billionconnect sobre cómo calcular `sign`
2. O un ejemplo de request exitoso que hayas usado antes
3. O contactar soporte de Billionconnect para confirmar método de firma
