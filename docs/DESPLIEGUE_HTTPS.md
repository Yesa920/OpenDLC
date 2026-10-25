# Publicar la web con HTTPS

HTTPS es el candadito del navegador: cifra todo lo que viaja entre la persona y tu web (contraseñas, sesión…). Necesita un **certificado**, y el que usamos es gratuito (Let's Encrypt). No tienes que pedirlo a mano: lo hace **Caddy**, que ya está incluido en `docker-compose.yml`.

Para que Caddy consiga el certificado hacen falta tres cosas:
1. Un **dominio** (puede ser un subdominio gratuito).
2. Que el dominio apunte a la **IP pública** del equipo donde corre la web.
3. Que los **puertos 80 y 443** de ese equipo sean accesibles desde internet.

## Opción A (recomendada): una máquina virtual gratuita siempre encendida
Un enlace de LinkedIn tiene que funcionar a cualquier hora, así que la web no puede depender de tu ordenador. Una de las opciones gratuitas más generosas es **Oracle Cloud "Always Free"** (te pide una tarjeta solo para verificar tu identidad; la disponibilidad de máquinas depende de la región y a veces hay que reintentar). Las condiciones de los planes gratuitos cambian: compruébalas antes de empezar.

1. **Crea la máquina virtual** con Ubuntu. Descarga la clave SSH que te da.
2. **Abre los puertos 80 y 443** en el panel de red del proveedor (reglas de entrada TCP). En las imágenes de Ubuntu de Oracle suele hacer falta abrirlos también dentro de la máquina:
   ```bash
   sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
   sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
   sudo netfilter-persistent save
   ```
3. **Consigue un dominio gratis**: por ejemplo un subdominio de [DuckDNS](https://www.duckdns.org) (`tunombre.duckdns.org`), apuntándolo a la IP pública de tu máquina. Si ya tienes un dominio, crea un registro tipo `A` con esa IP.
4. **Instala Docker** en la máquina (conéctate por SSH):
   ```bash
   curl -fsSL https://get.docker.com | sudo sh
   sudo usermod -aG docker $USER      # cierra la sesión SSH y vuelve a entrar
   ```
5. **Descarga el proyecto y configura el `.env`**:
   ```bash
   git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git
   cd TU_REPOSITORIO
   cp .env.example .env
   nano .env
   ```
   En el `.env` pon contraseñas **largas y distintas** (puedes generarlas con `openssl rand -hex 24`), `DOMINIO=tunombre.duckdns.org` (sin `https://`) y `DEMO_MODE=1`.
6. **Arranca** (sin phpMyAdmin, que no debe estar en internet):
   ```bash
   docker compose --profile produccion up -d --build web db chatbot caddy
   docker compose logs -f caddy
   ```
   Cuando veas que el certificado se ha obtenido, abre `https://tunombre.duckdns.org`.
7. **Cambia la clave del administrador de demostración** (la del README es pública):
   ```bash
   docker compose exec web php db/cambiar_clave_admin.php demo_admin UnaClaveLargaSoloTuya
   ```

**Actualizar después:** `git pull` y repite el comando del paso 6.
**Volver a dejar la demo limpia:** `docker compose down -v` y de nuevo el paso 6 (borra todos los datos).
**Copia de seguridad:** `docker compose exec db mariadb-dump -uroot -p pccomponentes > copia.sql`

## Opción B: desde tu ordenador con ngrok (rápida, pero provisional)
Con ngrok ya tienes HTTPS sin certificado ni dominio:
```powershell
docker compose up -d
ngrok http 8080
```
Sirve para enseñarlo un rato, pero tu ordenador tiene que estar encendido y el plan gratuito puede mostrar una pantalla de aviso antes de la web. Para un enlace permanente en LinkedIn, usa la opción A.

## Probar HTTPS en tu propio ordenador
Con `DOMINIO=localhost` en el `.env`, ejecuta `docker compose --profile produccion up -d` y abre `https://localhost`. Caddy usa un certificado propio, así que el navegador avisará de que no es de confianza: es normal en local.

## Comprobaciones al terminar
- Se ve el candado, y `http://tudominio` redirige solo a `https://`.
- En el navegador (F12 → Aplicación → Cookies) la cookie `PHPSESSID` aparece con **Secure** y **HttpOnly**.
- `docker compose ps`: no hay ningún puerto abierto salvo 80 y 443 (ni la base de datos, ni el chatbot, ni phpMyAdmin).
- Entra con el cliente demo, haz una compra de prueba y prueba el chatbot.
