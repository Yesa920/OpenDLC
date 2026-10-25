<?php require_once __DIR__ . '/../auth.php'; requerir_admin(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>📊 Ventas - Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../css/UID.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      text-align: center;
      padding: 20px;
    }

    h1 { color: #2c3e50; }

    .top-info {
      display: flex;
      justify-content: space-around;
      margin-bottom: 20px;
    }

    .top-info div {
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      flex: 1;
      margin: 0 10px;
      font-weight: bold;
      font-size: 1.1em;
    }

 .charts {
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
  }

  .charts .contenedor {
    flex: 0 1 450px; /* ancho fijo más pequeño */
    margin: 0 15px;
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  /* Establecemos un tamaño fijo para los canvas, más pequeño */
  canvas {
    max-width: 450px;
    max-height: 450px;
	width: 100%;     
    height: auto;    
  }
  </style>
</head>
<body>
<div class="top-bar">


  <a href="../logout.php" class="btn inicio">🚪 Cerrar sesión</a>
  <div id="adminTop" style="text-align:center; font-weight:bold;"></div>
  <div class="nav-right">
	<a href="insert.php" class="btn">➕ Insert</a>
    <a href="update.php" class="btn">✏️ Update</a>
    <a href="delete.php" class="btn danger">🗑 Delete</a>
    <a href="consultar.php" class="btn">📄 Consultar</a>
  </div>
</div>

<h1>📊 Dashboard de Ventas</h1>

<div class="top-info">
  <div id="topAdmin">🏆 TOP Administrador: Cargando...</div>
  <div id="topProducto">🏆 Producto más vendido: Cargando...</div>
</div>

<div class="charts">
  <div class="contenedor">
    <h3>Ventas por Administrador</h3>
    <canvas id="graficoAdmins"></canvas>
  </div>

  <div class="contenedor">
    <h3>Ventas por Tipo de Hardware</h3>
    <canvas id="graficoTipos"></canvas>
  </div>
</div>
<a href="admin_chatbot.php" class="admin-float-btn">
    <div class="admin-icon-circle">
        <span class="admin-emoji">⚙️</span>
    </div>
    <span class="admin-label">Panel Gestión Chat</span>
</a>
<script>
const colores = [
  '#3498db','#2ecc71','#f1c40f','#e74c3c','#9b59b6','#1abc9c',
  '#e67e22','#e91e63','#00bcd4','#8bc34a','#ff5722','#607d8b',
  '#673ab7','#009688','#ffc107','#f44336','#3f51b5','#cddc39',
  '#795548','#ff9800'
];
 
// Cargar gráficos
fetch('ventas/datos_grafico.php')
.then(res => res.json())
.then(data => {
	const ctxAdmin = document.getElementById('graficoAdmins').getContext('2d');
	new Chart(ctxAdmin, {
		type: 'pie',
		data: {
			labels: data.admin.labels, // Ejemplo: ["TarjGraf"]
			datasets: [{ 
				data: data.admin.datos, // Ejemplo: [600.00]
				unidades: data.admin.unidades, // <--- ESTO ES CLAVE: pasamos el array [1, ...]
				backgroundColor: colores
			}]
		},
		options: { 
			responsive: true, 
			plugins: {
				legend: { position: 'bottom' },
				tooltip: {
					callbacks: {
						label: function(context) {
							const label = context.label || '';
							const totalEuros = context.parsed || 0;
							
							// Extraemos la cantidad usando el índice actual
							const cantidad = context.dataset.unidades[context.dataIndex] || 0; 
							
							return `${label}: ${totalEuros.toFixed(2)} € (${cantidad} unidades)`;
						}
					}
				}
			} 
		}
	});

	const ctxTipo = document.getElementById('graficoTipos').getContext('2d');
	new Chart(ctxTipo, {
		type: 'pie',
		data: {
			labels: data.tipo.labels, // Ejemplo: ["TarjGraf", "RAM"]
			datasets: [{ 
				data: data.tipo.datos, // Los importes en €
				unidades: data.tipo.unidades, // <--- Importante: pasamos las unidades desde el JSON
				backgroundColor: colores
			}]
		},
		options: { 
			responsive: true, 
			plugins: {
				legend: { position: 'bottom' },
				tooltip: {
					callbacks: {
						label: function(context) {
							const label = context.label || '';
							const totalEuros = context.parsed || 0;
							
							// Obtenemos las unidades del dataset 'tipo' usando el índice actual
							const cantidad = context.dataset.unidades[context.dataIndex] || 0; 
							
							// Formateamos a 2 decimales para el dinero
							return `${label}: ${totalEuros.toFixed(2)} € (${cantidad} unidades)`;
						}
					}
				}
			} 
		}
	});

});

// TOP ADMIN
fetch('ventas/top_admin.php')
.then(res => res.text())
.then(data => { document.getElementById('topAdmin').innerHTML = "🏆 TOP Administrador: " + data; });

// TOP PRODUCTO
fetch('ventas/top_producto.php')
.then(res => res.text())
.then(data => { document.getElementById('topProducto').innerHTML = "🏆 Producto más vendido: " + data; });
</script>

</body>
</html>