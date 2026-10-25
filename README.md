# 🖥️ OpenDLC — tienda web de componentes de PC

Proyecto de fin de ciclo (DAM): una tienda online de componentes de ordenador y PCs premontados, con panel de administración y chatbot con IA.

> **▶ Demo online:** _(pon aquí tu enlace, por ejemplo `https://tunombre.duckdns.org`)_
> Pulsa **"Entrar como cliente demo"** en la página de acceso y ya estás dentro, sin registrarte.

<!-- Añade aquí 2 o 3 capturas o un GIF: ![Tienda](docs/captura-tienda.png) -->

## ✨ Qué incluye
- Catálogo con filtros, valoraciones y stock en tiempo real.
- Registro, inicio de sesión y recuperación de contraseña por correo (código de 6 dígitos).
- Carrito, compra **simulada** con correo de confirmación, y devoluciones.
- Panel de administración: alta, baja, modificación y consulta de productos, y gráficos de ventas.
- Chatbot: responde con una IA gratuita usando el catálogo real; si no sabe, o el cliente pide un administrador, la conversación pasa a un administrador.
- Tareas programadas en la base de datos (reposición de stock y proceso de devoluciones).

## 🧱 Tecnologías
PHP 8.4 + Apache · MariaDB 10.11 · Flask (Python) · JavaScript · Docker Compose · Caddy (HTTPS automático) · PHPMailer

## 🚀 Probarlo en tu ordenador
Solo necesitas [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```powershell
git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git
cd TU_REPOSITORIO
Copy-Item .env.example .env      # y edita .env: pon contraseñas largas inventadas
docker compose up -d --build
```

Abre **http://localhost:8080**. La base de datos se crea sola la primera vez, con datos de ejemplo.
(phpMyAdmin, solo en tu equipo: http://localhost:8081)

**Cuentas de demostración**

| Rol | Usuario | Contraseña |
|---|---|---|
| Cliente | `demo@opendlc.test` (o el botón "Entrar como cliente demo") | `Demo-Cliente-2026` |
| Administrador | `demo_admin` | `Demo-Admin-2026` |

Todos los datos son ficticios. El correo de recuperación/compra y la IA del chatbot son opcionales: sin configurarlos, la web funciona igual (el chatbot usa respuestas por palabras clave).

## 💳 La zona de pago es una simulación
**No se realiza ningún cobro y no debes escribir una tarjeta real.** Usa un número inventado (por ejemplo `4242424242424242`), cualquier fecha futura y un código como `123`. Los datos de la tarjeta ni siquiera salen del navegador: no se envían ni se guardan.

En una tienda real, el pago lo gestiona una **pasarela de pago contratada con un banco o proveedor autorizado** (por ejemplo Redsys o Stripe). Los datos de la tarjeta se escriben en la página segura de esa entidad, nunca en el servidor de la tienda, y es el banco quien autoriza el cobro. Integrarlo requiere un contrato con la entidad y cumplir la normativa de pagos (PCI DSS); por eso aquí se simula.

## 🔒 Seguridad (lo que se ha trabajado)
- Contraseñas con **hash bcrypt y sal aleatoria** (`password_hash`), nunca en texto plano.
- **Consultas preparadas** en el login, el registro, la ayuda y el panel de administración; en el resto, los datos se escapan o se convierten a número. Todo lo que se muestra va escapado (XSS).
- **Límite de intentos:** en el login, 5 fallos por cuenta (da igual si se escribe el nombre o el email) y 20 por conexión cada 15 minutos; también hay límites en recuperación de contraseña, registro, ayuda y chat.
- Sesiones: cookie `HttpOnly`, `SameSite`, marcada como segura bajo HTTPS, id renovado al entrar y cierre de sesión real.
- Páginas de cliente y de administrador protegidas en el servidor (la sesión se comprueba contra la base de datos).
- Subida de imágenes validada (solo imágenes reales, nombre decidido por el servidor, sin ejecución de PHP en esas carpetas).
- Secretos fuera del código (`.env`), nunca en Git. Ficheros internos bloqueados por Apache.
- Base de datos sin puerto expuesto y con un usuario que solo puede leer y escribir datos (no alterar tablas).
- El chatbot (Flask) no es accesible desde fuera: solo la web habla con él, con un token secreto.
- Cabeceras de seguridad y HTTPS automático con Caddy.

## 📂 Estructura
```
├── UID/                 acceso, registro y panel de administración
├── carrito/             carrito y pago simulado
├── Ayuda/               formulario de ayuda
├── db/init/             esquema y datos de ejemplo (se carga solo la 1ª vez)
├── docker/              Dockerfiles (web, chatbot) y configuración de Apache y Caddy
├── docs/                guías: HTTPS/despliegue y cómo subirlo a GitHub
├── auth.php             control de acceso     ├── limitador.php   límite de intentos
├── chat_*.php           chatbot (IA)          └── server.py       servidor del chatbot (Flask)
```

## 🌐 Publicarlo en internet con HTTPS
Guía paso a paso en [`docs/DESPLIEGUE_HTTPS.md`](docs/DESPLIEGUE_HTTPS.md).

## ⚖️ Aviso
Proyecto educativo y sin ánimo de lucro. Las marcas, logotipos e imágenes de productos que aparecen pertenecen a sus propietarios y se usan solo con fines de demostración.

## Cómo probarlo en local

Requisito: Docker Desktop.

1. Clona el repositorio y entra en la carpeta:
   ```
   git clone https://github.com/Yesa920/OpenDLC.git
   cd OpenDLC
   ```
2. Crea tu archivo de configuración y cambia las contraseñas:
   ```
   copy .env.example .env
   ```
   Edita `.env` y pon valores propios en `DB_PASS`, `DB_ROOT_PASS` y `CHAT_TOKEN`.
   Con `DEMO_MODE=1` aparece el botón "Entrar como cliente demo".
3. Arranca todo:
   ```
   docker compose up -d --build
   ```
4. Abre en el navegador:
   - Tienda: http://localhost:8080
   - Panel de administración: http://localhost:8080/UID/iniciar_sesion/acceso.php
   - phpMyAdmin (solo local): http://localhost:8081
5. Chatbot con IA (opcional): rellena `IA_URL`, `IA_MODEL` e `IA_KEY` en `.env`.
   Sin clave, el chatbot responde con reglas por palabras clave.
6. Para pararlo: `docker compose down`

Si no quiere descargarse nada y no sabe como exportar al XAMPP el proyecto aquí un video de cómo es la página:

