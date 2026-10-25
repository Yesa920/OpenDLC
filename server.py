from flask import Flask, request, jsonify
import json, os, sys, time, uuid, hmac, threading

app = Flask(__name__)

CHAT_FILE = os.environ.get("CHAT_FILE", "chat.json")
TOKEN = os.environ.get("CHAT_TOKEN", "")
if len(TOKEN) < 20:
    sys.exit("Falta CHAT_TOKEN en el .env (minimo 20 caracteres). El chatbot no arranca sin el.")

ESTADOS = ("pensando", "pendiente", "respondido")
bloqueo = threading.Lock()   # evita que dos peticiones escriban el fichero a la vez

if not os.path.exists(CHAT_FILE):
    with open(CHAT_FILE, "w", encoding="utf-8"):
        pass


@app.before_request
def comprobar_token():
    # Este servidor NO esta abierto al publico: solo lo llama la web (PHP) dentro de
    # la red de Docker, y siempre con el token secreto. Sin token -> 403.
    enviado = request.headers.get("X-Chat-Token", "")
    if not hmac.compare_digest(enviado.encode(), TOKEN.encode()):
        return jsonify({"error": "no autorizado"}), 403


@app.after_request
def sin_cache(response):
    response.headers["Cache-Control"] = "no-store"
    return response


def leer_todo():
    datos = []
    try:
        with open(CHAT_FILE, "r", encoding="utf-8") as f:
            for linea in f:
                linea = linea.strip()
                if linea:
                    datos.append(json.loads(linea))
    except (OSError, ValueError):
        pass
    return datos


def guardar_todo(mensajes):
    with open(CHAT_FILE, "w", encoding="utf-8") as f:
        for m in mensajes:
            f.write(json.dumps(m, ensure_ascii=False) + "\n")


@app.route("/mensaje", methods=["POST"])
def mensaje():
    data = request.get_json(silent=True) or {}
    respuesta = str(data.get("respuesta", ""))[:1000]
    estado = data.get("estado")
    if estado not in ESTADOS:
        estado = "respondido" if respuesta else "pendiente"
    msg = {
        "id": str(uuid.uuid4()),
        "user_id": str(data.get("user_id", ""))[:40],
        "pregunta": str(data.get("pregunta", ""))[:500],
        "respuesta": respuesta,
        "estado": estado,                      # pensando | pendiente | respondido
        "origen": str(data.get("origen", ""))[:10],   # ia | auto | admin
        "timestamp": time.time(),
    }
    if not msg["pregunta"].strip():
        return jsonify({"error": "pregunta vacia"}), 400
    with bloqueo:
        with open(CHAT_FILE, "a", encoding="utf-8") as f:
            f.write(json.dumps(msg, ensure_ascii=False) + "\n")
    return jsonify({"ok": True, "id": msg["id"]})


@app.route("/mensajes", methods=["GET"])
def mensajes():
    # Con ?user_id=... solo devuelve los mensajes de ese usuario
    uid = request.args.get("user_id")
    with bloqueo:
        datos = leer_todo()
    if uid is not None:
        datos = [m for m in datos if str(m.get("user_id")) == uid]
    return jsonify(datos)


@app.route("/responder", methods=["POST"])
def responder():
    data = request.get_json(silent=True) or {}
    msg_id = data.get("id")
    respuesta = str(data.get("respuesta", ""))[:1000]
    origen = str(data.get("origen", "admin"))[:10]
    estado = data.get("estado")
    if estado not in ESTADOS:
        estado = "respondido" if respuesta else "pendiente"
    with bloqueo:
        mensajes = leer_todo()
        for m in mensajes:
            if m.get("id") == msg_id:
                m["respuesta"] = respuesta
                m["estado"] = estado
                if respuesta:
                    m["origen"] = origen
        guardar_todo(mensajes)
    return jsonify({"ok": True})


if __name__ == "__main__":
    # debug=False: con debug=True cualquiera podria ejecutar codigo en el servidor
    app.run(host="0.0.0.0", port=5000, debug=False)
