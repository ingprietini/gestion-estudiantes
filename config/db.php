<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'gestion_estudiantes';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// Opciones para PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Crear conexión PDO
try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        $options
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

