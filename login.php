<?php
session_start();
require_once 'config/db.php';

$error = '';

// Si ya hay sesión, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (empty($nombre_usuario) || empty($contrasena)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, nombre_usuario, contrasena, rol FROM usuarios WHERE nombre_usuario = :nombre_usuario");
            $stmt->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar la contraseña
                if (password_verify($contrasena, $usuario['contrasena'])) {
                    // Iniciar sesión
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                    $_SESSION['rol'] = $usuario['rol'];
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'Usuario no encontrado.';
            }
        } catch(PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <h1>Sistema de Gestión de Estudiantes</h1>
            <h2>Iniciar Sesión</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="nombre_usuario">Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                </div>
                
                <div class="form-group">
                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>
            
            <div class="login-footer">
                <p>¿No tienes una cuenta? Contacta al administrador.</p>
                <p><a href="setup.php">Configuración inicial</a></p>
            </div>
        </div>
    </div>
</body>
</html>

