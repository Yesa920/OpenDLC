# Imagen del chatbot: Python con Flask (ya no hace falta flask-cors: el navegador no llama aqui)
FROM python:3.12-slim

WORKDIR /app
RUN pip install --no-cache-dir flask

# server.py no se copia aqui: docker-compose.yml lo conecta desde tu carpeta
CMD ["python", "server.py"]
