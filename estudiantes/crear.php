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
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    
    if (empty($nombre) || empty($apellido) || empty($email)) {
        $mensaje = "Por favor, complete los campos obligatorios.";
        $tipo_mensaje = "error";
    } else {
        try {
            // Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM estudiantes WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $email_existe = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
            
            if ($email_existe) {
                $mensaje = "El email ya está registrado para otro estudiante.";
                $tipo_mensaje = "error";
            } else {
                $stmt = $conn->prepare("INSERT INTO estudiantes (nombre, apellido, email, fecha_nacimiento) VALUES (:nombre, :apellido, :email, :fecha_nacimiento)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellido', $apellido);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                $stmt->execute();
                
                $mensaje = "Estudiante creado con éxito.";
                $tipo_mensaje = "success";
                
                // Limpiar los campos después de la inserción exitosa
                $nombre = $apellido = $email = $fecha_nacimiento = '';
            }
        } catch(PDOException $e) {
            $mensaje = "Error al crear el estudiante: " . $e->getMessage();
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
    <title>Crear Estudiante - Sistema de Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Crear Estudiante</h2>
                <a href="index.php" class="btn">Volver a la lista</a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="form-estudiante" method="POST" action="crear.php">
                    <div class="form-group">
                        <label for="nombre">Nombre: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                        <div id="nombre-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido">Apellido: <span class="required">*</span></label>
                        <input type="text" id="apellido" name="apellido" value="<?php echo isset($apellido) ? htmlspecialchars($apellido) : ''; ?>" required>
                        <div id="apellido-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email: <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        <div id="email-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo isset($fecha_nacimiento) ? htmlspecialchars($fecha_nacimiento) : ''; ?>">
                        <div id="fecha_nacimiento-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Guardar Estudiante</button>
                    </div>
                </form>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

