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

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Obtener los datos del usuario
try {
    $stmt = $conn->prepare("SELECT id, nombre_usuario, rol FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: index.php");
        exit();
    }
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "Error al obtener los datos del usuario: " . $e->getMessage();
    $tipo_mensaje = "error";
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    $rol = $_POST['rol'] ?? '';
    
    if (empty($nombre_usuario) || empty($rol)) {
        $mensaje = "Por favor, complete los campos obligatorios.";
        $tipo_mensaje = "error";
    } elseif (!empty($contrasena) && $contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
    } else {
        try {
            // Verificar si el nombre de usuario ya existe para otro usuario
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = :nombre_usuario AND id != :id");
            $stmt->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $usuario_existe = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
            
            if ($usuario_existe) {
                $mensaje = "El nombre de usuario ya está registrado para otro usuario.";
                $tipo_mensaje = "error";
            } else {
                // Si se proporcionó una nueva contraseña, actualizarla
                if (!empty($contrasena)) {
                    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = :nombre_usuario, contrasena = :contrasena, rol = :rol WHERE id = :id");
                    $stmt->bindParam(':contrasena', $contrasena_hash);
                } else {
                    $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = :nombre_usuario, rol = :rol WHERE id = :id");
                }
                
                $stmt->bindParam(':nombre_usuario', $nombre_usuario);
                $stmt->bindParam(':rol', $rol);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $mensaje = "Usuario actualizado con éxito.";
                $tipo_mensaje = "success";
                
                // Actualizar los datos del usuario
                $usuario['nombre_usuario'] = $nombre_usuario;
                $usuario['rol'] = $rol;
            }
        } catch(PDOException $e) {
            $mensaje = "Error al actualizar el usuario: " . $e->getMessage();
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
    <title>Editar Usuario - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Editar Usuario</h2>
                <a href="index.php" class="btn">Volver a la lista</a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="form-usuario" method="POST" action="editar.php?id=<?php echo $id; ?>">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre de Usuario: <span class="required">*</span></label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
                        <div id="nombre_usuario-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña: <small>(Dejar en blanco para mantener la actual)</small></label>
                        <input type="password" id="contrasena" name="contrasena">
                        <div id="contrasena-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasena">Confirmar Contraseña:</label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena">
                        <div id="confirmar_contrasena-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol: <span class="required">*</span></label>
                        <select id="rol" name="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="administrador" <?php echo ($usuario['rol'] === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="profesor" <?php echo ($usuario['rol'] === 'profesor') ? 'selected' : ''; ?>>Profesor</option>
                        </select>
                        <div id="rol-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

