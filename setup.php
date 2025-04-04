<?php
require_once 'config/db.php';

$mensaje = '';

// Verificar si ya existe un usuario administrador
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'administrador'");
$admin_exists = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

if ($admin_exists) {
    $mensaje = 'Ya existe un usuario administrador. Esta página solo está disponible para la configuración inicial.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    
    if (empty($nombre_usuario) || empty($contrasena) || empty($confirmar_contrasena)) {
        $mensaje = 'Por favor, complete todos los campos.';
    } elseif ($contrasena !== $confirmar_contrasena) {
        $mensaje = 'Las contraseñas no coinciden.';
    } else {
        try {
            // Hashear la contraseña
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            
            // Insertar el usuario administrador
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES (:nombre_usuario, :contrasena, 'administrador')");
            $stmt->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt->bindParam(':contrasena', $contrasena_hash);
            $stmt->execute();
            
            $mensaje = 'Usuario administrador creado con éxito. <a href="login.php">Iniciar sesión</a>';
        } catch(PDOException $e) {
            $mensaje = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="setup-page">
    <div class="setup-container">
        <div class="setup-form">
            <h1>Sistema de Gestión de Estudiantes</h1>
            <h2>Configuración Inicial</h2>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert <?php echo strpos($mensaje, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$admin_exists): ?>
                <p>Cree un usuario administrador para comenzar a utilizar el sistema.</p>
                
                <form method="POST" action="setup.php">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre de Usuario:</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasena">Confirmar Contraseña:</label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Crear Usuario Administrador</button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="setup-footer">
                <p><a href="login.php">Volver al inicio de sesión</a></p>
            </div>
        </div>
    </div>
</body>
</html>

