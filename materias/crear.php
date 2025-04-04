<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Definir la ruta base para los enlaces en el header
$base_path = '../';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    
    if (empty($nombre)) {
        $mensaje = "Por favor, complete el nombre de la materia.";
        $tipo_mensaje = "error";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO materias (nombre, descripcion) VALUES (:nombre, :descripcion)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            
            $mensaje = "Materia creada con éxito.";
            $tipo_mensaje = "success";
            
            // Limpiar los campos después de la inserción exitosa
            $nombre = $descripcion = '';
        } catch(PDOException $e) {
            $mensaje = "Error al crear la materia: " . $e->getMessage();
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
    <title>Crear Materia - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Crear Materia</h2>
                <a href="index.php" class="btn">Volver a la lista</a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="form-materia" method="POST" action="crear.php">
                    <div class="form-group">
                        <label for="nombre">Nombre: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                        <div id="nombre-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Guardar Materia</button>
                    </div>
                </form>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

