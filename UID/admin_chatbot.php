<?php require_once __DIR__ . '/../auth.php'; requerir_admin(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin Chat Pro</title>
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#e5ddd5; margin: 0; padding: 20px; }
    
    h2 { text-align: center; color: #444; margin-bottom: 20px; }

    .chat-container {
        max-width: 600px;
        margin: auto;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .msg-card {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-left: 5px solid #007bff;
    }

    .user-info { font-weight: bold; color: #007bff; margin-bottom: 5px; display: flex; align-items: center; gap: 5px; }
    .question { font-size: 16px; color: #333; margin-bottom: 12px; background: #f8f9fa; padding: 10px; border-radius: 8px; }

    .reply-area { display: flex; gap: 8px; margin-top: 10px; }

    input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        outline: none;
        transition: border 0.3s;
    }

    input:focus { border-color: #007bff; }

    button {
        padding: 10px 18px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s;
    }

    button:hover { background: #0056b3; }

    .answered {
        background: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
        border-left: 5px solid #28a745;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn {
        padding: 10px 18px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        transition: background 0.3s;
    }

    .btn:hover { background: #0056b3; }

    .btn.inicio {
        background: #28a745;
    }

    .btn.inicio:hover {
        background: #1e7e34;
    }

    .btn.danger {
        background: #dc3545;
    }

    .btn.danger:hover {
        background: #c82333;
    }
</style>
</head>

<body>
<div class="top-bar">
  <a href="../logout.php" class="btn inicio">🚪 Cerrar sesión</a>

  <div class="nav-right">
	<a href="insert.php" class="btn">➕ Insert</a>
    <a href="update.php" class="btn">✏️ Update</a>
    <a href="delete.php" class="btn danger">🗑 Delete</a>
    <a href="consultar.php" class="btn">📄 Consultar</a>
	<a href="ventas_globales.php" class="btn">📊 Ventas</a>
  </div>
</div>
<h2>📩 Panel de Control Chat</h2>

<div class="chat-container" id="chat">
    <!-- Los mensajes se cargan aquí -->
</div>

<script>
// Usamos una constante para no repetir la URL mil veces
const URL = "chat_admin_api.php";
// Convierte texto en texto seguro (evita que un mensaje malicioso ejecute codigo en tu navegador)
function esc(t) {
    return String(t ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
let lastData = "";

// Quien respondio: IA, respuesta automatica o un administrador
function etiqueta(o) {
    return o === "ia" ? " · 🤖 IA" : o === "auto" ? " · ⚙️ automática" : o === "admin" ? " · 👤 admin" : "";
}

async function loadMessages() {
    const headers = {
        "Content-Type": "application/json",
        "ngrok-skip-browser-warning": "69420"
    };

    try {
        const res = await fetch(URL, { headers }); 
        if (!res.ok) return;

        const data = await res.json();
        const json = JSON.stringify(data);
        if (json === lastData) return;

        lastData = json;
        const chat = document.getElementById("chat");
        chat.innerHTML = "";

        // Mostramos los más nuevos arriba (.reverse())
        data.reverse().forEach(m => {
            if (!m.id) return;
            let div = document.createElement("div");
            div.className = "msg-card";
            
            div.innerHTML = `
                <div class="user-info">👤 Usuario: ${esc(m.user_id)}</div>
                <div class="question">💬 ${esc(m.pregunta)}</div>
                ${m.respuesta ? 
                    `<div class="answered"><b>✔ Respuesta${etiqueta(m.origen)}:</b> ${esc(m.respuesta)}</div>` :
                    `<div class="reply-area">
                        <input id="r_${m.id}" 
                               placeholder="Escribe una respuesta y pulsa Enter..." 
                               onkeypress="checkEnter(event, '${m.id}')">
                        <button onclick="send('${m.id}')">Enviar</button>
                    </div>`
                }
            `;
            chat.appendChild(div);
        });
    } catch (error) {
        console.error("Error conectando:", error);
    }
}

// Función para detectar la tecla Enter
function checkEnter(event, id) {
    if (event.key === "Enter") {
        send(id);
    }
}

async function send(id) {
    const input = document.getElementById("r_" + id);
    const respuestaTexto = input.value.trim();

    if (!respuestaTexto) return;

    // Desactivamos el input mientras envía para evitar doble clic
    input.disabled = true;

    try {
        const res = await fetch(URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "ngrok-skip-browser-warning": "69420"
            },
            body: JSON.stringify({
                id: id,
                respuesta: respuestaTexto
            })
        });

        if (res.ok) {
            loadMessages(); 
        }
    } catch (err) {
        console.error("Error al enviar:", err);
        input.disabled = false;
    }
}

setInterval(loadMessages, 2000);
loadMessages();

</script>
</body>
</html>
