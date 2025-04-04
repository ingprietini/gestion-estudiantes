<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/db.php';

// Definir la ruta base para los enlaces en el header
$base_path = '../';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    $rol = $_POST['rol'] ?? '';
    
    if (empty($nombre_usuario) || empty($contrasena) || empty($confirmar_contrasena) || empty($rol)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje = "error";
    } elseif ($contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
    } else {
        try {
            // Verificar si el nombre de usuario ya existe
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = :nombre_usuario");
            $stmt->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt->execute();
            $usuario_existe = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
            
            if ($usuario_existe) {
                $mensaje = "El nombre de usuario ya está registrado.";
                $tipo_mensaje = "error";
            } else {
                // Hashear la contraseña
                $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES (:nombre_usuario, :contrasena, :rol)");
                $stmt->bindParam(':nombre_usuario', $nombre_usuario);
                $stmt->bindParam(':contrasena', $contrasena_hash);
                $stmt->bindParam(':rol', $rol);
                $stmt->execute();
                
                $mensaje = "Usuario creado con éxito.";
                $tipo_mensaje = "success";
                
                // Limpiar los campos después de la inserción exitosa
                $nombre_usuario = $contrasena = $confirmar_contrasena = $rol = '';
            }
        } catch(PDOException $e) {
            $mensaje = "Error al crear el usuario: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Crear Usuario</h2>
                <a href="index.php" class="btn">Volver a la lista</a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="form-usuario" method="POST" action="crear.php">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre de Usuario: <span class="required">*</span></label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo isset($nombre_usuario) ? htmlspecialchars($nombre_usuario) : ''; ?>" required>
                        <div id="nombre_usuario-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña: <span class="required">*</span></label>
                        <input type="password" id="contrasena" name="contrasena" required>
                        <div id="contrasena-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasena">Confirmar Contraseña: <span class="required">*</span></label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        <div id="confirmar_contrasena-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol: <span class="required">*</span></label>
                        <select id="rol" name="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="administrador" <?php echo (isset($rol) && $rol === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="profesor" <?php echo (isset($rol) && $rol === 'profesor') ? 'selected' : ''; ?>>Profesor</option>
                        </select>
                        <div id="rol-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    </div>
                </form>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

