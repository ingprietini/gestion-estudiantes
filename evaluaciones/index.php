<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Parámetros de filtro
$materia_id = isset($_GET['materia_id']) ? $_GET['materia_id'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;
$nota_min = isset($_GET['nota_min']) ? $_GET['nota_min'] : null;
$nota_max = isset($_GET['nota_max']) ? $_GET['nota_max'] : null;
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null;
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null;
$estudiante_id = isset($_GET['estudiante_id']) ? $_GET['estudiante_id'] : null;

// Vista actual (normal o reportes)
$view = isset($_GET['view']) ? $_GET['view'] : 'normal';

// Construir la consulta SQL base
$sql = "SELECT ev.id, e.nombre, e.apellido, m.nombre as materia, ev.calificacion, ev.fecha 
        FROM evaluaciones ev 
        JOIN estudiantes e ON ev.estudiante_id = e.id 
        JOIN materias m ON ev.materia_id = m.id";

// Añadir filtros a la consulta
$where_clauses = array();

if ($materia_id) {
    $where_clauses[] = "ev.materia_id = " . $conn->quote($materia_id);
}

if ($estado == 'aprobados') {
    $where_clauses[] = "ev.calificacion >= 6.00";
} elseif ($estado == 'reprobados') {
    $where_clauses[] = "ev.calificacion < 6.00";
}

if ($nota_min) {
    $where_clauses[] = "ev.calificacion >= " . $conn->quote($nota_min);
}

if ($nota_max) {
    $where_clauses[] = "ev.calificacion <= " . $conn->quote($nota_max);
}

if ($fecha_desde) {
    $where_clauses[] = "ev.fecha >= " . $conn->quote($fecha_desde);
}

if ($fecha_hasta) {
    $where_clauses[] = "ev.fecha <= " . $conn->quote($fecha_hasta);
}

if ($estudiante_id) {
    $where_clauses[] = "ev.estudiante_id = " . $conn->quote($estudiante_id);
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY ev.id DESC";

$result = $conn->query($sql);

// Obtener materias para el filtro
$sql_materias = "SELECT id, nombre FROM materias ORDER BY nombre";
$result_materias = $conn->query($sql_materias);

// Obtener estadísticas si estamos en vista reportes
$estadisticas = array();
if ($view == 'reportes') {
    // Total aprobados
    $sql_aprobados = "SELECT COUNT(*) as total FROM evaluaciones WHERE calificacion >= 6.00";
    $result_aprobados = $conn->query($sql_aprobados);
    $estadisticas['aprobados'] = $result_aprobados->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total reprobados
    $sql_reprobados = "SELECT COUNT(*) as total FROM evaluaciones WHERE calificacion < 6.00";
    $result_reprobados = $conn->query($sql_reprobados);
    $estadisticas['reprobados'] = $result_reprobados->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Promedio general
    $sql_promedio = "SELECT AVG(calificacion) as promedio FROM evaluaciones";
    $result_promedio = $conn->query($sql_promedio);
    $estadisticas['promedio'] = $result_promedio->fetch(PDO::FETCH_ASSOC)['promedio'];
    
    // Estadísticas por materia
    $sql_materias_stats = "SELECT m.nombre, COUNT(ev.id) as total, 
                          AVG(ev.calificacion) as promedio,
                          SUM(CASE WHEN ev.calificacion >= 6 THEN 1 ELSE 0 END) as aprobados,
                          SUM(CASE WHEN ev.calificacion < 6 THEN 1 ELSE 0 END) as reprobados
                          FROM materias m
                          LEFT JOIN evaluaciones ev ON m.id = ev.materia_id
                          GROUP BY m.id
                          ORDER BY m.nombre";
    $result_materias_stats = $conn->query($sql_materias_stats);
    $estadisticas['por_materia'] = array();
    while ($row = $result_materias_stats->fetch(PDO::FETCH_ASSOC)) {
        $estadisticas['por_materia'][] = $row;
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $view == 'reportes' ? 'Reportes y Estadísticas' : 'Gestión de Evaluaciones'; ?></h1>
        <div>
            <?php if ($view != 'reportes'): ?>
                <a href="index.php?view=reportes" class="btn btn-secondary me-2">
                    <i data-lucide="bar-chart-2" class="me-1"></i> Ver Estadísticas
                </a>
                <a href="crear.php" class="btn btn-success">
                    <i data-lucide="plus" class="me-1"></i> Nueva Evaluación
                </a>
            <?php else: ?>
                <a href="index.php" class="btn btn-primary">
                    <i data-lucide="list" class="me-1"></i> Ver Evaluaciones
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($view == 'normal'): ?>
        <!-- Filtros para evaluaciones -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Filtros de búsqueda</h5>
            </div>
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="materia_id" class="form-label">Materia</label>
                        <select name="materia_id" id="materia_id" class="form-select">
                            <option value="">Todas las materias</option>
                            <?php while ($materia = $result_materias->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $materia['id']; ?>" <?php echo ($materia_id == $materia['id']) ? 'selected' : ''; ?>>
                                    <?php echo $materia['nombre']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="aprobados" <?php echo ($estado == 'aprobados') ? 'selected' : ''; ?>>Aprobados (≥ 6.00)</option>
                            <option value="reprobados" <?php echo ($estado == 'reprobados') ? 'selected' : ''; ?>>Reprobados (< 6.00)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rango de Calificación</label>
                        <div class="row">
                            <div class="col">
                                <input type="number" class="form-control" name="nota_min" placeholder="Mínima" step="0.01" min="0" max="10" value="<?php echo $nota_min; ?>">
                            </div>
                            <div class="col">
                                <input type="number" class="form-control" name="nota_max" placeholder="Máxima" step="0.01" min="0" max="10" value="<?php echo $nota_max; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_desde" class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo $fecha_desde; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo $fecha_hasta; ?>">
                    </div>
                     <div class="col-md-4">
                        <label for="estudiante_id" class="form-label">Estudiante</label>
                        <input type="number" class="form-control" id="estudiante_id" name="estudiante_id" value="<?php echo $estudiante_id; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i data-lucide="filter" class="me-1"></i> Filtrar
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i data-lucide="x" class="me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de evaluaciones -->
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Evaluaciones</h5>
                    <?php if ($result && $result->rowCount() > 0): ?>
                        <span class="badge bg-info"><?php echo $result->rowCount(); ?> resultados</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($result && $result->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Estudiante</th>
                                    <th>Materia</th>
                                    <th>Calificación</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></td>
                                        <td><?php echo $row['materia']; ?></td>
                                        <td class="text-center fw-bold"><?php echo $row['calificacion']; ?></td>
                                        <td>
                                            <?php if ($row['calificacion'] >= 6): ?>
                                                <span class="badge bg-success">Aprobado</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Reprobado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                        <td>
                                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                <i data-lucide="edit-2" class="icon-sm"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmarEliminar(<?php echo $row['id']; ?>)" class="btn btn-sm btn-danger">
                                                <i data-lucide="trash-2" class="icon-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No se encontraron evaluaciones con los filtros seleccionados.</div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Vista de estadísticas y reportes -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?php echo $estadisticas['aprobados']; ?></h1>
                        <h5>Evaluaciones Aprobadas</h5>
                        <p class="mb-0">Calificación ≥ 6.00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?php echo $estadisticas['reprobados']; ?></h1>
                        <h5>Evaluaciones Reprobadas</h5>
                        <p class="mb-0">Calificación < 6.00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body text-center">
                        <h1 class="display-4"><?php echo number_format($estadisticas['promedio'], 2); ?></h1>
                        <h5>Promedio General</h5>
                        <p class="mb-0">De todas las evaluaciones</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Estadísticas por Materia</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th class="text-center">Total Evaluaciones</th>
                                <th class="text-center">Promedio</th>
                                <th class="text-center">Aprobados</th>
                                <th class="text-center">Reprobados</th>
                                <th class="text-center">% Aprobación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadisticas['por_materia'] as $materia): ?>
                                <?php 
                                $total = $materia['total'] ?: 1; // Evitar división por cero
                                $porcentaje_aprobacion = ($materia['aprobados'] / $total) * 100;
                                ?>
                                <tr>
                                    <td><?php echo $materia['nombre']; ?></td>
                                    <td class="text-center"><?php echo $materia['total']; ?></td>
                                    <td class="text-center">
                                        <?php if ($materia['promedio']): ?>
                                            <span class="badge <?php echo $materia['promedio'] >= 6 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo number_format($materia['promedio'], 2); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo $materia['aprobados']; ?></td>
                                    <td class="text-center"><?php echo $materia['reprobados']; ?></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?php echo $porcentaje_aprobacion >= 60 ? 'bg-success' : 'bg-warning'; ?>" 
                                                role="progressbar" 
                                                style="width: <?php echo $porcentaje_aprobacion; ?>%;" 
                                                aria-valuenow="<?php echo $porcentaje_aprobacion; ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                                <?php echo number_format($porcentaje_aprobacion, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Gráfico de barras para visualización de datos -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Rendimiento por Materia</h5>
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
        
        <?php if ($view == 'reportes'): ?>
        // Datos para el gráfico
        const ctx = document.getElementById('rendimientoChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    foreach ($estadisticas['por_materia'] as $materia) {
                        echo "'" . $materia['nombre'] . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Promedio',
                    data: [
                        <?php 
                        foreach ($estadisticas['por_materia'] as $materia) {
                            echo $materia['promedio'] ? number_format($materia['promedio'], 2) : 0 . ", ";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        <?php 
                        foreach ($estadisticas['por_materia'] as $materia) {
                            echo $materia['promedio'] >= 6 ? "'rgba(40, 167, 69, 0.7)'" : "'rgba(220, 53, 69, 0.7)'";
                            echo ", ";
                        }
                        ?>
                    ],
                    borderColor: [
                        <?php 
                        foreach ($estadisticas['por_materia'] as $materia) {
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

    function confirmarEliminar(id) {
        if (confirm('¿Está seguro de que desea eliminar esta evaluación?')) {
            window.location.href = `eliminar.php?id=${id}`;
        }
    }
</script>

<?php include '../includes/footer.php'; ?>

