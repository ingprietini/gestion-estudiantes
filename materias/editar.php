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

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Obtener los datos de la materia
try {
    $stmt = $conn->prepare("SELECT * FROM materias WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: index.php");
        exit();
    }
    
    $materia = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "Error al obtener los datos de la materia: " . $e->getMessage();
    $tipo_mensaje = "error";
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    
    if (empty($nombre)) {
        $mensaje = "Por favor, complete el nombre de la materia.";
        $tipo_mensaje = "error";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE materias SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $mensaje = "Materia actualizada con éxito.";
            $tipo_mensaje = "success";
            
            // Actualizar los datos de la materia
            $materia['nombre'] = $nombre;
            $materia['descripcion'] = $descripcion;
        } catch(PDOException $e) {
            $mensaje = "Error al actualizar la materia: " . $e->getMessage();
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
    <title>Editar Materia - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Editar Materia</h2>
                <a href="index.php" class="btn">Volver a la lista</a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="form-materia" method="POST" action="editar.php?id=<?php echo $id; ?>">
                    <div class="form-group">
                        <label for="nombre">Nombre: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($materia['nombre']); ?>" required>
                        <div id="nombre-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($materia['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Actualizar Materia</button>
                    </div>
                </form>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

