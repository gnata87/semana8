<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "Natalia";
$password = "Nata1987";
$dbname = "AGENCIA";


// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear base de datos y tablas si no existen
$sql = "CREATE DATABASE IF NOT EXISTS AGENCIA";
$conn->query($sql);

$sql = "USE AGENCIA";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS VUELO (
    id_vuelo INT AUTO_INCREMENT PRIMARY KEY,
    origen VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    plazas_disponibles INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS HOTEL (
    id_hotel INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ubicación VARCHAR(100) NOT NULL,
    habitaciones_disponibles INT NOT NULL,
    tarifa_noche DECIMAL(10, 2) NOT NULL
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS RESERVA (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    id_vuelo INT,
    id_hotel INT,
    FOREIGN KEY (id_vuelo) REFERENCES VUELO(id_vuelo),
    FOREIGN KEY (id_hotel) REFERENCES HOTEL(id_hotel)
)";
$conn->query($sql);

// Insertar registros de prueba en las tablas VUELO y HOTEL
$sql = "INSERT INTO VUELO (origen, destino, fecha, plazas_disponibles, precio) VALUES
        ('Madrid', 'Barcelona', '2024-07-20', 100, 50.00),
        ('Paris', 'Londres', '2024-07-21', 150, 75.00),
        ('Nueva York', 'Los Angeles', '2024-07-22', 200, 100.00)";
$conn->query($sql);

$sql = "INSERT INTO HOTEL (nombre, ubicación, habitaciones_disponibles, tarifa_noche) VALUES
        ('Hotel Madrid', 'Madrid', 50, 80.00),
        ('Hotel Paris', 'Paris', 60, 90.00),
        ('Hotel Nueva York', 'Nueva York', 70, 100.00)";
$conn->query($sql);

// Procesar datos del formulario de vuelos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tipo']) && $_POST['tipo'] == 'vuelo') {
    $origen = $_POST["origen"];
    $destino = $_POST["destino"];
    $fecha = $_POST["fecha"];
    $plazas_disponibles = $_POST["plazas_disponibles"];
    $precio = $_POST["precio"];

    $sql = "INSERT INTO VUELO (origen, destino, fecha, plazas_disponibles, precio) 
            VALUES ('$origen', '$destino', '$fecha', '$plazas_disponibles', '$precio')";
    if ($conn->query($sql) === TRUE) {
        echo "Nuevo vuelo agregado exitosamente<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Procesar datos del formulario de hoteles
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tipo']) && $_POST['tipo'] == 'hotel') {
    $nombre = $_POST["nombre"];
    $ubicacion = $_POST["ubicacion"];
    $habitaciones_disponibles = $_POST["habitaciones_disponibles"];
    $tarifa_noche = $_POST["tarifa_noche"];

    $sql = "INSERT INTO HOTEL (nombre, ubicación, habitaciones_disponibles, tarifa_noche) 
            VALUES ('$nombre', '$ubicacion', '$habitaciones_disponibles', '$tarifa_noche')";
    if ($conn->query($sql) === TRUE) {
        echo "Nuevo hotel agregado exitosamente<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Insertar registros de reserva asegurando que los vuelos y hoteles existen
$reservas = [
    [1, '2024-07-01', 1, 1],
    [2, '2024-07-02', 2, 2],
    [3, '2024-07-03', 3, 3],
    [4, '2024-07-04', 1, 2],
    [5, '2024-07-05', 2, 3],
    [6, '2024-07-06', 3, 1],
    [7, '2024-07-07', 1, 3],
    [8, '2024-07-08', 2, 1],
    [9, '2024-07-09', 3, 2],
    [10, '2024-07-10', 1, 1]
];

foreach ($reservas as $reserva) {
    // Verificar existencia del vuelo
    $sql = "SELECT COUNT(*) AS count FROM VUELO WHERE id_vuelo = " . $reserva[2];
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        echo "El vuelo con id " . $reserva[2] . " no existe. No se puede agregar la reserva.<br>";
        continue;
    }

    // Verificar existencia del hotel
    $sql = "SELECT COUNT(*) AS count FROM HOTEL WHERE id_hotel = " . $reserva[3];
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        echo "El hotel con id " . $reserva[3] . " no existe. No se puede agregar la reserva.<br>";
        continue;
    }

    // Insertar reserva
    $sql = "INSERT INTO RESERVA (id_cliente, fecha_reserva, id_vuelo, id_hotel) 
            VALUES ('$reserva[0]', '$reserva[1]', '$reserva[2]', '$reserva[3]')";
    if ($conn->query($sql) === TRUE) {
        echo "Nueva reserva agregada exitosamente<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Consultar el contenido de las tablas
$tables = ['VUELO', 'HOTEL', 'RESERVA'];

foreach ($tables as $table) {
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);
    echo "<h2>Contenido de la tabla $table</h2>";
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $column => $value) {
                echo "<td>$column: $value</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "0 resultados<br>";
    }
}

// Consultas avanzadas: Mostrar hoteles con más de dos reservas
$sql = "SELECT H.nombre, COUNT(R.id_reserva) AS num_reservas 
        FROM HOTEL H
        JOIN RESERVA R ON H.id_hotel = R.id_hotel
        GROUP BY H.id_hotel
        HAVING COUNT(R.id_reserva) > 2";
$result = $conn->query($sql);

echo "<h2>Hoteles con más de dos reservas</h2>";
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>Hotel: " . $row["nombre"] . "</td>";
        echo "<td>Número de Reservas: " . $row["num_reservas"] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "0 resultados<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Vuelos y Hoteles</title>
    <script>
        function validarFormularioVuelo() {
            var origen = document.forms["formVuelo"]["origen"].value;
            var destino = document.forms["formVuelo"]["destino"].value;
            var fecha = document.forms["formVuelo"]["fecha"].value;
            var plazas = document.forms["formVuelo"]["plazas_disponibles"].value;
            var precio = document.forms["formVuelo"]["precio"].value;

            if (origen == "" || destino == "" || fecha == "" || plazas == "" || precio == "") {
                alert("Todos los campos son obligatorios");
                return false;
            }
            return true;
        }

        function validarFormularioHotel() {
            var nombre = document.forms["formHotel"]["nombre"].value;
            var ubicacion = document.forms["formHotel"]["ubicacion"].value;
            var habitaciones = document.forms["formHotel"]["habitaciones_disponibles"].value;
            var tarifa = document.forms["formHotel"]["tarifa_noche"].value;

            if (nombre == "" || ubicacion == "" || habitaciones == "" || tarifa == "") {
                alert("Todos los campos son obligatorios");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h1>Gestión de Vuelos y Hoteles</h1>

    <h2>Agregar Vuelo</h2>
    <form name="formVuelo" action="" method="post" onsubmit="return validarFormularioVuelo();">
        <input type="hidden" name="tipo" value="vuelo">
        Origen: <input type="text" name="origen"><br>
        Destino: <input type="text" name="destino"><br>
        Fecha: <input type="date" name="fecha"><br>
        Plazas Disponibles: <input type="number" name="plazas_disponibles"><br>
        Precio: <input type="text" name="precio"><br>
        <input type="submit" value="Agregar Vuelo">
    </form>

    <h2>Agregar Hotel</h2>
    <form name="formHotel" action="" method="post" onsubmit="return validarFormularioHotel();">
        <input type="hidden" name="tipo" value="hotel">
        Nombre: <input type="text" name="nombre"><br>
        Ubicación: <input type="text" name="ubicacion"><br>
        Habitaciones Disponibles: <input type="number" name="habitaciones_disponibles"><br>
        Tarifa por Noche: <input type="text" name="tarifa_noche"><br>
        <input type="submit" value="Agregar Hotel">
    </form>
</body>
</html>
