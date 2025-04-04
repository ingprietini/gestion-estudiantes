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

// Obtener la lista de estudiantes
try {
    $stmt = $conn->query("SELECT id, CONCAT(nombre, ' ', apellido) as nombre_completo FROM estudiantes ORDER BY apellido, nombre");
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "Error al obtener los estudiantes: " . $e->getMessage();
    $tipo_mensaje = "error";
    $estudiantes = [];
}

// Obtener la lista de materias
try {
    $stmt = $conn->query("SELECT id, nombre FROM materias ORDER BY nombre");
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "Error al obtener las materias: " . $e->getMessage();
    $tipo_mensaje = "error";
    $materias = [];
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estudiante_id = $_POST['estudiante_id'] ?? '';
    $materia_id = $_POST['materia_id'] ?? '';
    $calificacion = $_POST['calificacion'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    
    if (empty($estudiante_id) || empty($materia_id) || empty($calificacion) || empty($fecha)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje = "error";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO evaluaciones (estudiante_id, materia_id, calificacion, fecha) VALUES (:estudiante_id, :materia_id, :calificacion, :fecha)");
            $stmt->bindParam(':estudiante_id', $estudiante_id);
            $stmt->bindParam(':materia_id', $materia_id);
            $stmt->bindParam(':calificacion', $calificacion);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->execute();
            
            $mensaje = "Evaluación creada con éxito.";
            $tipo_mensaje = "success";
            
            // Limpiar los campos después de la inserción exitosa
            $estudiante_id = $materia_id = $calificacion = $fecha = '';
        } catch(PDOException $e) {
            $mensaje = "Error al crear la evaluación: " . $e->getMessage();
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
    <title>Crear Evaluación - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Crear Evaluación</h2>
                <a href="index.php" class="btn">Volver a la lista</a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="form-evaluacion" method="POST" action="crear.php">
                    <div class="form-group">
                        <label for="estudiante_id">Estudiante: <span class="required">*</span></label>
                        <select id="estudiante_id" name="estudiante_id" required>
                            <option value="">Seleccione un estudiante</option>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <option value="<?php echo $estudiante['id']; ?>" <?php echo (isset($estudiante_id) && $estudiante_id == $estudiante['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($estudiante['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="estudiante_id-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="materia_id">Materia: <span class="required">*</span></label>
                        <select id="materia_id" name="materia_id" required>
                            <option value="">Seleccione una materia</option>
                            <?php foreach ($materias as $materia): ?>
                                <option value="<?php echo $materia['id']; ?>" <?php echo (isset($materia_id) && $materia_id == $materia['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($materia['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="materia_id-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="calificacion">Calificación: <span class="required">*</span></label>
                        <input type="number" id="calificacion" name="calificacion" step="0.01" min="0" max="10" value="<?php echo isset($calificacion) ? htmlspecialchars($calificacion) : ''; ?>" required>
                        <div id="calificacion-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha">Fecha: <span class="required">*</span></label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo isset($fecha) ? htmlspecialchars($fecha) : date('Y-m-d'); ?>" required>
                        <div id="fecha-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Guardar Evaluación</button>
                    </div>
                </form>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

