<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/db.php';

// Definir la ruta base para los enlaces en el header
$base_path = '../';

// Eliminar usuario
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // No permitir eliminar el propio usuario
    if ($id == $_SESSION['usuario_id']) {
        $mensaje = "No puede eliminar su propio usuario.";
        $tipo_mensaje = "error";
    } else {
        try {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $mensaje = "Usuario eliminado con éxito.";
            $tipo_mensaje = "success";
        } catch(PDOException $e) {
            $mensaje = "Error al eliminar: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// Obtener la lista de usuarios
try {
    $stmt = $conn->query("SELECT id, nombre_usuario, rol FROM usuarios ORDER BY nombre_usuario");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "Error al obtener los usuarios: " . $e->getMessage();
    $tipo_mensaje = "error";
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Gestión de Usuarios</h2>
                <a href="crear.php" class="btn btn-success">Nuevo Usuario</a>
            </div>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Usuario</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                    <td>
                                        <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm">Editar</a>
                                        <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                            <a href="index.php?eliminar=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger" onclick="confirmarEliminar(event, '¿Está seguro de que desea eliminar este usuario?')">Eliminar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

