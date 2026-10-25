<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Área Cliente</title>
  
  <style>
    body { font-family: Arial, sans-serif; }
	/* Solo aplicamos esto si el input está DENTRO del wrapper del chat */
	#pccom-chatbot-wrapper .chat-input input {
    all: unset; /* Resetea todas las propiedades heredadas de la página principal */
    flex: 1;
    padding: 10px !important;
    border: none !important;
    background: white !important;
    color: black !important;
    box-shadow: none !important;
    font-family: Arial, sans-serif !important;
    font-size: 14px !important;
	}

	/* Aseguramos que el botón del chat no herede estilos de botones de la página */
    #pccom-chatbot-wrapper .chat-input button {
    all: unset;
    padding: 10px 15px;
    background: #007bff !important;
    color: white !important;
    cursor: pointer;
    font-weight: bold;
    border-radius: 0 0 10px 0;
	}

    /* Chat */
    .chat-container {
      z-index: 9999;
      width: 320px;
      position: fixed;
      bottom: 110px; 
      right: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      background: white;
      display: none; /* oculto por defecto */
    }

    .chat-header {
      background: #007bff;
      color: white;
      padding: 10px;
      font-weight: bold;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .close-btn {
      cursor: pointer;
      font-weight: bold;
    }

    .chat-body {
      height: 300px;
      overflow-y: auto;
      padding: 10px;
      background: #f9f9f9;
    }

    .chat-message { margin: 8px 0; display: flex; }

    .user { justify-content: flex-end; }
    .bot { justify-content: flex-start; }

    .bubble {
      max-width: 75%;
      padding: 10px 14px;
      border-radius: 18px;
      position: relative;
      font-size: 14px;
      line-height: 1.4;
    }

    /* Usuario (azul) */
    .user .bubble {
      background: #007bff;
      color: white;
      border-bottom-right-radius: 4px;
    }

    /* Bot (blanco) */
    .bot .bubble {
      background: white;
      color: black;
      border: 1px solid #ddd;
      border-bottom-left-radius: 4px;
    }
    .user { text-align: right; }
    .bot { text-align: left; }

    .chat-input {
      display: flex;
      border-top: 1px solid #ccc;
    }

    .chat-input input {
      flex: 1;
      padding: 10px;
      border: none;
      outline: none;
    }

    .chat-input button {
      padding: 10px;
      border: none;
      background: #007bff;
      color: white;
      cursor: pointer;
    }

    /* Botón flotante */
    .chat-toggle {
	  z-index: 9999;
      position: fixed;
      bottom: 50px;
      right: 20px;
      background: #007bff;
      color: white;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 24px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }


  </style>
</head>
<body>
<div id="pccom-chatbot-wrapper">
	<!-- BOTÓN PARA ABRIR CHAT -->
	<div class="chat-toggle" onclick="toggleChat()">💬</div>

	<!-- CHATBOT -->
	<div class="chat-container" id="chatContainer">
	  <div class="chat-header">
		Asistente PC
		<span class="close-btn" onclick="toggleChat()">✖</span>
	  </div>
	  <div class="chat-body" id="chatBody"></div>
	  <div class="chat-input">
		<input type="text" id="userInput" placeholder="Escribe tu pregunta...">
		<button onclick="sendMessage()">Enviar</button>
	  </div>
	</div>
</div>



<script>
// Usamos una constante para no repetir la URL mil veces
// --- CONFIGURACIÓN ---
const API_URL = "<?php require_once __DIR__ . '/chat_lib.php'; echo chat_base_url(); ?>/chat_api.php";
let idsProcesados = new Set(); 
let cargaInicialRealizada = false;
let saludoEnviado = false;

// --- RESPUESTAS AUTOMÁTICAS DEL BOT ---
const respuestasAuto = {
        "hola": "¡Hola! Bienvenido a OpenDLC. 🖥️ Tenemos **ordenadores premontados** listos para usar o puedes **comprar las piezas por separado** y montarlo tú mismo. ¿Qué prefieres?",

    "ordenador": "Tienes dos opciones:\n• **Premontado:** Lo recibes listo para encender, con garantía de montaje. Ideal si no quieres complicarte.\n• **Piezas por separado:** Más barato en total, lo personalizas a tu gusto y aprendes cómo funciona por dentro.\n¿Cuál te interesa más? 🖥️",

    "premontado": "Nuestros ordenadores premontados vienen configurados y probados. Solo enchúfalo y úsalo. Perfectos para trabajo, estudio o gaming sin complicaciones. ¡Échales un vistazo en el catálogo! 🛒",

    "piezas": "Comprando las piezas por separado puedes ahorrar entre un 10% y un 20% respecto a un premontado equivalente, además de elegir exactamente cada componente. Tenemos **procesadores, RAM, gráficas, discos, fuentes y más**. ¿Por dónde empezamos? 🔧",

    "precio": "El precio depende de para qué lo uses:\n• **Ofimática/estudio:** desde ~400€\n• **Gaming 1080p:** desde ~700€\n• **Gaming 1440p o edición de vídeo:** desde ~1.200€\n¿Cuál es tu presupuesto? 💰",

    "portatil o torre": "Depende de tu uso:\n• **Portátil:** Ideal si necesitas movilidad o estudias fuera.\n• **Torre:** Mejor rendimiento por euro, más fácil de reparar y actualizar.\n¿Para qué lo vas a usar? 💻",

    "ddr4 o ddr5": "La **DDR5** es más rápida y eficiente, pero requiere placas base modernas. La **DDR4** sigue siendo excelente para gaming económico. ¡Si montas algo desde cero, ve a por DDR5! ⚡",

    "windows": "**Windows 11** es el sistema más usado y compatible con todos los juegos y programas. Es la opción más recomendable si no tienes experiencia con otros sistemas. Lo incluimos preinstalado en nuestros premontados. 🪟",

    "linux": "**Linux** es gratuito, muy estable y perfecto para programadores o servidores. Sin embargo, no todos los juegos son compatibles (aunque Steam con Proton ha mejorado mucho). ¿Lo necesitas para trabajo, programación o gaming? 🐧",

    "windows o linux": "Depende del uso:\n• **Windows:** Mejor para gaming, diseño y compatibilidad total con programas.\n• **Linux:** Gratis, más ligero y seguro, ideal para programadores.\nPara la mayoría de usuarios recomendamos **Windows**. Si programas o quieres experimentar, **Linux** es una gran opción. ¿Cuál es tu caso? 🖥️",

    "ssd": "El **SSD** es mucho más rápido que un disco duro tradicional. Arranca el sistema en segundos y abre programas al instante. Totalmente imprescindible hoy en día. Tenemos desde **256 GB hasta 2 TB**. ¿Cuánto espacio necesitas? ⚡",

    "hdd": "El **HDD** es más lento pero mucho más barato por GB. Ideal para almacenar fotos, vídeos y archivos que no necesitas abrir rápido. Lo recomendamos como **disco secundario** junto a un SSD principal. 💾",

    "nvme": "El **NVMe M.2** es el disco más rápido del mercado, hasta 7 veces más rápido que un SSD SATA normal. Si quieres lo mejor para el sistema operativo y juegos, este es tu disco. 🚀",

    "disco duro": "Tienes tres opciones según tus necesidades:\n• **NVMe M.2:** El más rápido, ideal para el sistema y juegos.\n• **SSD SATA:** Rápido y económico, gran equilibrio.\n• **HDD:** Lento pero barato, solo para almacenamiento masivo.\n¿Qué uso le vas a dar? 💾",

    "grafica": "Para jugar a **1080p** recomendamos la serie **RTX 4060** o similar. Para **1440p o 4K** sube a una RTX 4070 o superior. ¿Qué resolución tienes en mente? 🎮",

    "procesador": "Para gaming lo mejor ahora mismo es **AMD Ryzen 5/7** o **Intel Core i5/i7** de última generación. Si también editas vídeo, prioriza más núcleos. ¿Gaming, trabajo o los dos? ⚙️",

    "amd o intel": "Ambos son excelentes:\n• **AMD Ryzen:** Mejor rendimiento por precio, ideal para multitarea y edición.\n• **Intel Core:** Ligeramente mejor en gaming puro y compatibilidad.\nHoy en día cualquiera de los dos es una gran elección. ¿Para qué lo usarás? ⚙️",

    "ram": "Para gaming y uso general con **16 GB** tienes más que suficiente. Si editas vídeo o usas muchos programas a la vez, sube a **32 GB**. 🧠",

    "fuente": "Nunca ahorres en la fuente. Busca siempre una con certificación **80 Plus Bronze** o superior para proteger tus componentes de subidas de tensión. 🔌",

    "refrigeracion": "Tienes dos opciones:\n• **Aire:** Más barato, silencioso y fiable a largo plazo.\n• **Líquida (AIO):** Mejor rendimiento térmico y aspecto visual, ideal para procesadores de gama alta.\nPara la mayoría de usuarios el **aire** es más que suficiente. ❄️",

    "placa base": "La placa base es el corazón del PC. Asegúrate de que sea compatible con tu procesador (socket AM5 para Ryzen, LGA1700 para Intel) y que tenga los puertos que necesitas. ¿Ya tienes procesador elegido? 🔩",

    "limpieza": "Recomendamos una limpieza interna y cambio de **pasta térmica** cada 1-2 años para evitar que el PC se caliente y pierda potencia. 🔥",

    "garantia": "Todos nuestros productos incluyen la **garantía oficial del fabricante** (mínimo 2 años). Los premontados además tienen garantía de montaje. 🛡️",

    "envio": "Realizamos envíos a toda España. El plazo habitual es de **2 a 5 días laborables**. Para pedidos urgentes consulta con nosotros. 📦",
	
	"devolucion": "Puedes solicitar una devolución desde el menú **👤 → Devoluciones**. Solo aparecen compras de las últimas 24 horas. Elige el punto de recogida y la fecha disponible, y nosotros nos encargamos del resto. ¡Es muy sencillo! 📦↩️",

    "como devolver": "Para devolver un producto:\n1. Entra en tu cuenta 👤\n2. Ve a **Devoluciones**\n3. Selecciona el producto que quieres devolver\n4. Elige un **punto de recogida** y una **fecha y hora**\n5. Pulsa **Reservar devolución**\nRecibirás un correo de confirmación con todos los detalles. ✅",

    "plazo devolucion": "Puedes solicitar la devolución dentro de las **24 horas** siguientes a la compra. Pasado ese plazo el producto ya no aparecerá en la sección de devoluciones. ⏰",

    "pagar": "Aceptamos los siguientes métodos de pago:\n• **MasterCard** 💳\n• **Visa** 💳\n• **American Express** 💳\nTodos los pagos son seguros. Al finalizar la compra recibirás un **correo de confirmación** con el resumen del pedido. 🔒",

    "como pagar": "Para pagar:\n1. Añade los productos al **carrito** 🛒\n2. Ve al carrito y pulsa **Comprar**\n3. Elige tu tarjeta (**MasterCard, Visa o AmEx**)\n4. Rellena los datos de facturación\n5. Opcionalmente indica una dirección de entrega en el mapa 📍\n6. Pulsa **Comprar** y listo\nRecibirás un correo con la confirmación al instante. ✅",

    "tarjeta": "Aceptamos **MasterCard**, **Visa** y **American Express**. Necesitarás el número de tarjeta, fecha de caducidad y código de seguridad (CVV). Todo el proceso es seguro. 💳🔒",

    "como comprar": "Comprar en OpenDLC es muy fácil:\n1. Navega por el **catálogo** y elige tus productos\n2. Pulsa **Añadir al carrito** 🛒\n3. Cuando estés listo ve al carrito\n4. Elige el método de pago\n5. Rellena tus datos y confirma\n¡Y listo! Recibirás un correo con tu pedido. 📧",

    "carrito": "Para añadir productos al carrito pulsa el botón **Añadir al carrito** en cualquier producto del catálogo. Desde el carrito puedes **ajustar cantidades** o **eliminar productos** antes de pagar. 🛒",

    "cuenta": "Para acceder a tu cuenta pulsa el icono 👤 en la parte superior. Desde ahí puedes ver tu **carrito**, solicitar **devoluciones**, contactar con **ayuda** o cerrar sesión. Si no tienes cuenta aún, puedes registrarte gratis. 👤",

    "registro": "Registrarse es gratis y rápido. Ve a la página de inicio y pulsa **Registrarse**. Solo necesitas nombre, apellido, email y contraseña. Con tu cuenta podrás comprar, ver tu historial y gestionar devoluciones. ✍️",

    "correo confirmacion": "Tras cada compra o devolución te enviamos un **correo de confirmación** al email con el que te registraste. Si no lo ves en la bandeja de entrada, revisa la carpeta de **spam o correo no deseado**. 📧",

    "contacto": "Si tienes algún problema que no puedo resolver, ve al menú 👤 y pulsa **Ayuda**. Puedes enviarnos un formulario y te responderemos lo antes posible. 📩",

    "ayuda": "Puedo ayudarte con:\n**🛒 Compras:**\n• **como comprar** · **carrito** · **como pagar** · **tarjeta** · **pagar**\n\n**↩️ Devoluciones:**\n• **devolucion** · **como devolver** · **plazo devolucion**\n\n**👤 Cuenta:**\n• **cuenta** · **registro** · **correo confirmacion** · **contacto**\n\n**🖥️ Componentes:**\n• **ordenador** · **premontado** · **piezas** · **precio**\n• **windows** · **linux** · **ssd** · **hdd** · **nvme**\n• **grafica** · **procesador** · **amd o intel**\n• **ram** · **ddr4 o ddr5** · **fuente** · **placa base** · **refrigeracion**\n\n**📦 Otros:**\n• **garantia** · **envio** · **limpieza**\n\nEscribe cualquier tema y te ayudo. 😊 \n O puedes hablar con un administrador directamente."
};

// --- FUNCIONES DEL CHAT ---

function toggleChat() {
    const chat = document.getElementById("chatContainer");
    const estaAbierto = (chat.style.display === "none" || chat.style.display === "");
    
    chat.style.display = estaAbierto ? "block" : "none";

    // Al abrir, comprobamos si hay historial o si debemos saludar
    if (estaAbierto) {
        syncChat();
    }
}

function enviarSaludoBot() {
    if (saludoEnviado) return;
    const saludo = "¡Buenas! Soy el asistente virtual de OpenDLC. ¿Cómo te puedo ayudar?\n\nPuedes preguntarme sobre:\n• **ordenador** · **precio** · **portatil o torre**\n• **grafica** · **procesador** · **ram**\n• **disco duro** · **fuente** · **garantia** · **envio**\n• O escribe **ayuda** para ver todos los temas";
    
    setTimeout(() => {
        addMessage(saludo, "bot");
        saludoEnviado = true;
    }, 500);
}

function addMessage(text, sender) {
    const chatBody = document.getElementById("chatBody");
    const msg = document.createElement("div");
    msg.className = "chat-message " + sender;

    const bubble = document.createElement("div");
    bubble.className = "bubble";
    // Soporte para negritas y saltos de línea
    // Se escapa el HTML ANTES de dar formato: asi un mensaje con <script> o <img> no se ejecuta
    const seguro = String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    bubble.innerHTML = seguro.replace(/\n/g, "<br>").replace(/\*\*(.*?)\*\*/g, "<b>$1</b>");

    msg.appendChild(bubble);
    chatBody.appendChild(msg);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Mensajes temporales: "Escribiendo..." (la IA piensa) o "Esperando administrador"
const esperas = {};

function quitarEspera(id) {
    if (esperas[id]) { esperas[id].remove(); delete esperas[id]; }
}

function ponerEspera(id, texto) {
    if (esperas[id]) { esperas[id].querySelector(".bubble").textContent = texto; return; }
    const chatBody = document.getElementById("chatBody");
    const msg = document.createElement("div");
    msg.className = "chat-message bot";
    const bubble = document.createElement("div");
    bubble.className = "bubble";
    bubble.textContent = texto;
    msg.appendChild(bubble);
    chatBody.appendChild(msg);
    chatBody.scrollTop = chatBody.scrollHeight;
    esperas[id] = msg;
}

function sendMessage() {
    const input = document.getElementById("userInput");
    const originalText = input.value.trim();
    if (!originalText) return;

    // Respuesta de reserva por palabras clave: el servidor solo la usa si la IA no esta disponible
    let textoLower = originalText.toLowerCase();
    let botReply = "";
    if (respuestasAuto[textoLower]) {
        botReply = respuestasAuto[textoLower];
    } else {
        for (let clave in respuestasAuto) {
            if (textoLower.includes(clave)) {
                botReply = respuestasAuto[clave];
                break;
            }
        }
    }

    input.value = "";
    fetch(API_URL + "?accion=enviar", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ pregunta: originalText, respuesta: botReply })
    }).then(() => syncChat()).catch(() => syncChat());

    setTimeout(syncChat, 300);   // enseña tu mensaje enseguida mientras la IA piensa
}

async function syncChat() {
    try {
        const res = await fetch(API_URL + "?accion=mios", {
            cache: "no-store",
            headers: { "ngrok-skip-browser-warning": "69420" }
        });
        
        if (!res.ok) return;
        const data = await res.json();

        // Filtrar mensajes de este usuario
        const uid = typeof user_id !== 'undefined' ? user_id : 'invitado';
        const misMensajes = Array.isArray(data) ? data : [];   // el servidor ya devuelve solo los tuyos

        // LÓGICA DE CARGA
        if (misMensajes.length > 0) {
            // Si hay mensajes en el servidor, cargamos historial y anulamos saludo inicial
            if (!cargaInicialRealizada) {
                document.getElementById("chatBody").innerHTML = ""; 
                cargaInicialRealizada = true;
                saludoEnviado = true;
            }
            
            misMensajes.forEach(m => {
                // Pintar pregunta si no existe en pantalla
                if (!idsProcesados.has(m.id)) {
                    addMessage(m.pregunta, "user");
                    idsProcesados.add(m.id);
                }
                const idRespuesta = m.id + "_resp";
                if (m.respuesta) {
                    quitarEspera(m.id);
                    if (!idsProcesados.has(idRespuesta)) {
                        addMessage(m.respuesta, "bot");
                        idsProcesados.add(idRespuesta);
                    }
                } else if (m.estado === "pensando") {
                    ponerEspera(m.id, "✍️ Escribiendo…");
                } else {
                    ponerEspera(m.id, "⏳ Un administrador te responderá en cuanto pueda.");
                }
            });
        } 
        else {
            // Si el servidor está vacío y el chat está abierto, lanzamos saludo
            if (document.getElementById("chatContainer").style.display === "block") {
                enviarSaludoBot();
            }
        }
    } catch (error) {
        console.log("Sincronizando...");
    }
}

// Escuchar tecla Enter
document.getElementById("userInput").addEventListener("keypress", function(e) {
    if (e.key === "Enter") sendMessage();
});

// Refresco cada 2 segundos para mayor fluidez
setInterval(syncChat, 2000);
</script>


<div>
</body>
</html>
