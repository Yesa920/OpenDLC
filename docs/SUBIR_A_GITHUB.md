# Subir el proyecto a GitHub sin filtrar nada

La regla de oro: **lo que se sube a Git es público y casi imposible de borrar del todo**. Por eso primero se limpia y se comprueba, y solo después se sube.

## 1. Limpia lo que no debe subirse
Desde la carpeta del proyecto, en PowerShell:
```powershell
powershell -ExecutionPolicy Bypass -File .\limpiar_para_github.ps1
```
Te enseña qué va a borrar y pide confirmación. Elimina: el volcado antiguo con contraseñas (`pccomponentes (2).sql`), `chat.json` (conversaciones), scripts de un solo uso, carpetas "por si acaso" y las carpetas de ejemplos y pruebas de PHPMailer.

## 2. Qué NO se sube nunca (ya está en `.gitignore`)
`.env` (todas tus claves), `chat.json`, ficheros `.zip`, copias `.bak`, `__pycache__`, tus notas. **Y no mandes tu carpeta entera en un `.zip` a nadie**: llevaría tu `.env`.

## 3. Configura tu identidad (sin enseñar tu correo real)
En GitHub: Settings → Emails → marca "Keep my email addresses private" y copia tu correo `…@users.noreply.github.com`:
```powershell
git config --global user.name "Tu Nombre"
git config --global user.email "ID+usuario@users.noreply.github.com"
```

## 4. Crea el repositorio y súbelo
1. En GitHub: **New repository**, nombre `opendlc`, **Private** (lo harás público al final), sin README ni .gitignore (ya los tienes).
2. En la carpeta del proyecto:
   ```powershell
   git init
   git branch -M main
   git add .
   git status
   ```
3. **Antes de hacer commit, comprueba**:
   ```powershell
   git check-ignore -v .env              # debe decir que .env está ignorado
   git ls-files | Select-String "env"    # solo debe salir .env.example
   git ls-files | Select-String "\.sql"  # solo debe salir db/init/pccomponentes.sql
   ```
4. Sube:
   ```powershell
   git commit -m "OpenDLC: primera version"
   git remote add origin https://github.com/TU_USUARIO/opendlc.git
   git push -u origin main
   ```

## 5. Revisa en la web de GitHub
Abre el repositorio (aún privado) y comprueba que no hay `.env`, ni `chat.json`, ni correos reales. Usa el buscador de GitHub (`/`) con `gmail`, `password` y `token`.

## 6. Hazlo público
Settings → General → Danger Zone → **Change visibility → Public**. Añade al README tu enlace de la demo y capturas o un GIF, y en "About" una descripción y los temas (`php`, `docker`, `mariadb`, `flask`).

## Si subiste un secreto sin querer
Borrarlo en un commit nuevo **no basta**: sigue en el historial. Lo primero es **revocar y cambiar esa clave** (Google, ngrok, la de la IA, las contraseñas del `.env`). Después, si el repositorio es nuevo, lo más fácil es borrarlo y crearlo de nuevo.
