# 🧪 PRUEBA: DIFERENTES SALES METHODS

El error 1003 "参数错误" persiste. Ahora vamos a probar diferentes combinaciones.

## 🎯 ESTRATEGIA DE PRUEBA

### Paso 1: Verificar salesMethod actual
Ve a: **Connectivity Plans → Settings**
- ¿Qué valor tiene "Sales Method"?
- Actualmente está en: **"5"**

### Paso 2: Probar otros valores

El salesMethod puede variar según tu cuenta en Billionconnect:

| Valor | Significado | ¿Cuándo usar? |
|-------|-------------|---------------|
| "1" | Distribución directa | Cuenta estándar |
| "2" | Distribución mayorista | Distribuidores |
| "3" | White label | Partners white label |
| "4" | OEM | Fabricantes |
| "5" | API/Integration | Integraciones API (Hero eSIM) |

### Paso 3: Cambiar en Settings

1. **Ve a:** WP Admin → Connectivity Plans → Settings
2. **Cambia "Sales Method" a:** **"1"** (primero)
3. **Guarda cambios**
4. **Haz clic en "Sincronizar Productos"**
5. **Revisa el log** (debug.log)

### Paso 4: Si "1" no funciona, probar en orden:
- "1" → Distribución directa
- "3" → White label  
- "2" → Mayorista
- "4" → OEM

---

## 📞 CONTACTAR BILLIONCONNECT

Si ningún salesMethod funciona, necesitas contactar soporte:

**Pregunta clave:**
> "Estoy recibiendo error 1003 (参数错误) al llamar F002. 
> ¿Cuál es el salesMethod correcto para mi cuenta?"

**Tu información:**
- API Partner ID: [tu apiKey]
- Endpoint: https://api-flow.billionconnect.com/Flow/saler/2.0/invoke
- Error: tradeCode 1003 en F002

**Request que envías:**
```json
{
    "tradeType": "F002",
    "tradeTime": "2025-12-13 14:47:25",
    "tradeData": {
        "salesMethod": "5"
    }
}
```

---

## 🔍 OTRA POSIBILIDAD: API URL INCORRECTA

Verifica en Settings que la API URL sea:
```
https://api-flow.billionconnect.com/Flow/saler/2.0/invoke
```

Algunas cuentas usan endpoints diferentes:
- `https://api.billionconnect.com/...` (sin -flow)
- `https://api-flow.billionconnect.com/Flow/saler/1.0/invoke` (versión 1.0)

---

## ✅ CUANDO FUNCIONE

El log mostrará:
```
API Response (F002): {"tradeCode":"1000","tradeMsg":"Success","tradeData":[...]}
```

En lugar de:
```
API Response (F002): {"tradeCode":"1003","tradeMsg":"参数错误","tradeData":null}
```
