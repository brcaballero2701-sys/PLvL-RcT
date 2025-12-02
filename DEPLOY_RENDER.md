# 🚀 Guía de Despliegue en Render

## Requisitos Previos

✅ Repositorio en GitHub: https://github.com/brcaballero2701-sys/PLvL-RcT  
✅ Dockerfile configurado  
✅ render.yaml actualizado  
✅ Cuenta en Render: https://render.com

---

## 📋 PASO 1: Conectar GitHub con Render

1. Ve a https://render.com y haz login
2. Click en **New** → **Blueprint**
3. Selecciona **Connect repository**
4. Autoriza GitHub y selecciona: `brcaballero2701-sys/PLvL-RcT`
5. Render detectará automáticamente `render.yaml`

---

## 🐳 PASO 2: Configuración Automática

Render creará automáticamente:

✅ **Servicio Web** (PLvL-RcT-SENA)
- Puerto: 8080
- Dockerfile: `./Dockerfile`
- Plan: Free

✅ **Base de Datos MySQL** (sena-db)
- Versión: 8
- Plan: Free

---

## 🔑 PASO 3: Variables de Entorno

Render proporciona automáticamente:
- `APP_KEY` (generada)
- `DB_HOST`, `DB_USER`, `DB_PASSWORD` (de la BD MySQL)

Solo verifica que en el dashboard esté marcado:
- ✅ Auto-deploy on push

---

## 🗄️ PASO 4: Migraciones Automáticas

El Dockerfile ejecutará automáticamente:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

---

## ✅ PASO 5: Verificar Despliegue

Una vez desplegado, verifica en:

1. **Logs de Render**: Busca "Server running"
2. **URL de tu aplicación**: `https://tudominio.onrender.com`
3. **Base de datos**: Las migraciones se ejecutaron correctamente

---

## 🛠️ Solucionar Problemas

### Error: "App key not set"
✅ Render genera automáticamente APP_KEY

### Error: "Connection refused to MySQL"
✅ Espera 2-3 minutos a que se levante la BD

### Error: "Migrations failed"
✅ Verifica los logs en Render Dashboard → Logs

### Error: "npm run build failed"
✅ Verifica que `package.json` y `vite.config.js` estén correctos

---

## 📊 Monitoreo

En el dashboard de Render puedes ver:
- ✅ CPU Usage
- ✅ Memory Usage
- ✅ Disk Usage
- ✅ Logs en tiempo real

---

## 🔄 Actualizaciones Futuras

Solo necesitas hacer `git push` a `main`:

```bash
git add .
git commit -m "Cambios"
git push origin main
```

Render se redesplegará automáticamente en < 5 minutos.

---

**Tu aplicación estará disponible en la URL proporcionada por Render después del despliegue.** 🎉