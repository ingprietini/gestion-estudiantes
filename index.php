<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php';

// Obtener estadísticas generales
$query_estadisticas = "SELECT 
    COUNT(DISTINCT e.id) as total_estudiantes,
    COUNT(DISTINCT m.id) as total_materias,
    COUNT(ev.id) as total_evaluaciones,
    AVG(ev.calificacion) as promedio_general
FROM evaluaciones ev
LEFT JOIN estudiantes e ON e.id = ev.estudiante_id
LEFT JOIN materias m ON m.id = ev.materia_id";

$result_estadisticas = $conn->query($query_estadisticas);
$estadisticas = $result_estadisticas->fetch(PDO::FETCH_ASSOC);

// Obtener estudiantes con mejor y peor desempeño
$query_mejores = "SELECT e.nombre, e.apellido, AVG(ev.calificacion) as promedio
FROM evaluaciones ev
JOIN estudiantes e ON e.id = ev.estudiante_id
GROUP BY e.id
ORDER BY promedio DESC
LIMIT 3";

$result_mejores = $conn->query($query_mejores);
$mejores_estudiantes = [];
while ($row = $result_mejores->fetch(PDO::FETCH_ASSOC)) {
    $mejores_estudiantes[] = $row;
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Panel de Control</h1>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Estudiantes</h5>
                    <h2 class="card-text"><?php echo $estadisticas['total_estudiantes']; ?></h2>
                    <a href="estudiantes/index.php" class="text-white">Ver detalles <i class="ms-1" data-lucide="arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Materias</h5>
                    <h2 class="card-text"><?php echo $estadisticas['total_materias']; ?></h2>
                    <a href="materias/index.php" class="text-white">Ver detalles <i class="ms-1" data-lucide="arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Evaluaciones</h5>
                    <h2 class="card-text"><?php echo $estadisticas['total_evaluaciones']; ?></h2>
                    <a href="evaluaciones/index.php" class="text-white">Ver detalles <i class="ms-1" data-lucide="arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Promedio General</h5>
                    <h2 class="card-text"><?php echo number_format($estadisticas['promedio_general'], 2); ?></h2>
                    <a href="evaluaciones/index.php" class="text-white">Ver evaluaciones <i class="ms-1" data-lucide="arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Mejores Estudiantes
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($mejores_estudiantes as $estudiante): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $estudiante['nombre'] . ' ' . $estudiante['apellido']; ?>
                                <span class="badge bg-success rounded-pill">
                                    <?php echo number_format($estudiante['promedio'], 2); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Accesos Rápidos
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="estudiantes/crear.php" class="btn btn-outline-primary">
                            <i class="me-2" data-lucide="user-plus"></i> Nuevo Estudiante
                        </a>
                        <a href="materias/crear.php" class="btn btn-outline-success">
                            <i class="me-2" data-lucide="book"></i> Nueva Materia
                        </a>
                        <a href="evaluaciones/crear.php" class="btn btn-outline-info">
                            <i class="me-2" data-lucide="clipboard-list"></i> Nueva Evaluación
                        </a>
                        <a href="evaluaciones/index.php?view=reportes" class="btn btn-outline-secondary">
                            <i class="me-2" data-lucide="bar-chart"></i> Reportes y Estadísticas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar iconos de Lucide
        lucide.createIcons();
    });
</script>

<?php include 'includes/footer.php'; ?>

