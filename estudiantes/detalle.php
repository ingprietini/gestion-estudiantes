<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$estudiante_id = $_GET['id'];

// Obtener datos del estudiante
$sql = "SELECT * FROM estudiantes WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $estudiante_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header("Location: index.php");
    exit();
}

$estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener estadísticas del estudiante
$sql_stats = "SELECT 
    COUNT(ev.id) as total_evaluaciones,
    AVG(ev.calificacion) as promedio_general,
    SUM(CASE WHEN ev.calificacion >= 6 THEN 1 ELSE 0 END) as aprobadas,
    SUM(CASE WHEN ev.calificacion < 6 THEN 1 ELSE 0 END) as reprobadas
FROM evaluaciones ev
WHERE ev.estudiante_id = :estudiante_id";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bindParam(':estudiante_id', $estudiante_id);
$stmt_stats->execute();
$estadisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Obtener evaluaciones del estudiante
$sql_evaluaciones = "SELECT 
    ev.id, 
    ev.calificacion, 
    ev.fecha, 
    m.nombre as materia
FROM evaluaciones ev
JOIN materias m ON ev.materia_id = m.id
WHERE ev.estudiante_id = :estudiante_id
ORDER BY ev.fecha DESC";

$stmt_evaluaciones = $conn->prepare($sql_evaluaciones);
$stmt_evaluaciones->bindParam(':estudiante_id', $estudiante_id);
$stmt_evaluaciones->execute();

// Obtener estadísticas por materia
$sql_materias = "SELECT 
    m.nombre as materia,
    COUNT(ev.id) as total,
    AVG(ev.calificacion) as promedio,
    MIN(ev.calificacion) as minima,
    MAX(ev.calificacion) as maxima
FROM evaluaciones ev
JOIN materias m ON ev.materia_id = m.id
WHERE ev.estudiante_id = :estudiante_id
GROUP BY m.id
ORDER BY promedio DESC";

$stmt_materias = $conn->prepare($sql_materias);
$stmt_materias->bindParam(':estudiante_id', $estudiante_id);
$stmt_materias->execute();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detalle del Estudiante</h1>
        <div>
            <a href="index.php" class="btn btn-secondary me-2">
                <i data-lucide="arrow-left" class="me-1"></i> Volver
            </a>
            <a href="../evaluaciones/crear.php?estudiante_id=<?php echo $estudiante_id; ?>" class="btn btn-success">
                <i data-lucide="plus" class="me-1"></i> Nueva Evaluación
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información del estudiante -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder bg-light rounded-circle mx-auto mb-3" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="user" style="width: 50px; height: 50px; color: #6c757d;"></i>
                        </div>
                        <h4><?php echo $estudiante['nombre'] . ' ' . $estudiante['apellido']; ?></h4>
                        <p class="text-muted mb-0">ID: <?php echo $estudiante['id']; ?></p>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i data-lucide="mail" class="me-2 icon-sm"></i> Email</span>
                            <span class="text-primary"><?php echo $estudiante['email'] ?: 'No registrado'; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i data-lucide="calendar" class="me-2 icon-sm"></i> Fecha de Nacimiento</span>
                            <span><?php echo $estudiante['fecha_nacimiento'] ? date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) : 'No registrada'; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i data-lucide="award" class="me-2 icon-sm"></i> Promedio General</span>
                            <?php if ($estadisticas['total_evaluaciones'] > 0): ?>
                                <span class="badge <?php echo $estadisticas['promedio_general'] >= 6 ? 'bg-success' : 'bg-danger'; ?> rounded-pill">
                                    <?php echo number_format($estadisticas['promedio_general'], 2); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Sin evaluaciones</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-grid gap-2">
                        <a href="editar.php?id=<?php echo $estudiante_id; ?>" class="btn btn-outline-primary">
                            <i data-lucide="edit-3" class="me-1"></i> Editar Información
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas del estudiante -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Rendimiento Académico</h5>
                </div>
                <div class="card-body">
                    <?php if ($estadisticas['total_evaluaciones'] > 0): ?>
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <div class="border rounded p-3 h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted">Total Evaluaciones</h6>
                                    <h3><?php echo $estadisticas['total_evaluaciones']; ?></h3>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="border rounded p-3 h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted">Promedio</h6>
                                    <h3 class="<?php echo $estadisticas['promedio_general'] >= 6 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo number_format($estadisticas['promedio_general'], 2); ?>
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="border rounded p-3 h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted">Aprobadas</h6>
                                    <h3 class="text-success"><?php echo $estadisticas['aprobadas']; ?></h3>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="border rounded p-3 h-100 d-flex flex-column justify-content-center">
                                    <h6 class="text-muted">Reprobadas</h6>
                                    <h3 class="text-danger"><?php echo $estadisticas['reprobadas']; ?></h3>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">Rendimiento por Materia</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th class="text-center">Evaluaciones</th>
                                        <th class="text-center">Promedio</th>
                                        <th class="text-center">Mínima</th>
                                        <th class="text-center">Máxima</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($materia = $stmt_materias->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo $materia['materia']; ?></td>
                                            <td class="text-center"><?php echo $materia['total']; ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $materia['promedio'] >= 6 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo number_format($materia['promedio'], 2); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $materia['minima'] >= 6 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo number_format($materia['minima'], 2); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $materia['maxima'] >= 6 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo number_format($materia['maxima'], 2); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i data-lucide="info" class="me-2"></i>
                            Este estudiante aún no tiene evaluaciones registradas.
                        </div>
                        <div class="text-center mt-4">
                            <a href="../evaluaciones/crear.php?estudiante_id=<?php echo $estudiante_id; ?>" class="btn btn-primary">
                                <i data-lucide="plus-circle" class="me-1"></i> Registrar Primera Evaluación
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de evaluaciones -->
    <?php if ($estadisticas['total_evaluaciones'] > 0): ?>
    <div class="card mb-4">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Evaluaciones</h5>
                <span class="badge bg-info"><?php echo $estadisticas['total_evaluaciones']; ?> evaluaciones</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Materia</th>
                            <th>Calificación</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($evaluacion = $stmt_evaluaciones->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $evaluacion['id']; ?></td>
                                <td><?php echo $evaluacion['materia']; ?></td>
                                <td class="fw-bold"><?php echo $evaluacion['calificacion']; ?></td>
                                <td>
                                    <?php if ($evaluacion['calificacion'] >= 6): ?>
                                        <span class="badge bg-success">Aprobado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Reprobado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($evaluacion['fecha'])); ?></td>
                                <td>
                                    <a href="../evaluaciones/editar.php?id=<?php echo $evaluacion['id']; ?>" class="btn btn-sm btn-primary">
                                        <i data-lucide="edit-2" class="icon-sm"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmarEliminarEvaluacion(<?php echo $evaluacion['id']; ?>)" class="btn btn-sm btn-danger">
                                        <i data-lucide="trash-2" class="icon-sm"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gráfico de rendimiento -->
    <?php if ($estadisticas['total_evaluaciones'] > 0): ?>
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Gráfico de Rendimiento</h5>
        </div>
        <div class="card-body">
            <canvas id="rendimientoChart" height="300"></canvas>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar iconos de Lucide
        lucide.createIcons();
        
        <?php if ($estadisticas['total_evaluaciones'] > 0): ?>
        // Datos para el gráfico
        const ctx = document.getElementById('rendimientoChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $stmt_materias->execute(); // Reiniciar el cursor
                    while ($materia = $stmt_materias->fetch(PDO::FETCH_ASSOC)) {
                        echo "'" . $materia['materia'] . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Promedio por Materia',
                    data: [
                        <?php 
                        $stmt_materias->execute(); // Reiniciar el cursor
                        while ($materia = $stmt_materias->fetch(PDO::FETCH_ASSOC)) {
                            echo number_format($materia['promedio'], 2) . ", ";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        <?php 
                        $stmt_materias->execute(); // Reiniciar el cursor
                        while ($materia = $stmt_materias->fetch(PDO::FETCH_ASSOC)) {
                            echo $materia['promedio'] >= 6 ? "'rgba(40, 167, 69, 0.7)'" : "'rgba(220, 53, 69, 0.7)'";
                            echo ", ";
                        }
                        ?>
                    ],
                    borderColor: [
                        <?php 
                        $stmt_materias->execute(); // Reiniciar el cursor
                        while ($materia = $stmt_materias->fetch(PDO::FETCH_ASSOC)) {
                            echo $materia['promedio'] >= 6 ? "'rgba(40, 167, 69, 1)'" : "'rgba(220, 53, 69, 1)'";
                            echo ", ";
                        }
                        ?>
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Promedio: ' + context.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });

    function confirmarEliminarEvaluacion(id) {
        if (confirm('¿Está seguro de que desea eliminar esta evaluación?')) {
            window.location.href = `../evaluaciones/eliminar.php?id=${id}&redirect=estudiante&estudiante_id=<?php echo $estudiante_id; ?>`;
        }
    }
</script>

<?php include '../includes/footer.php'; ?>

