<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Buscar materias
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $sql = "SELECT * FROM materias WHERE nombre LIKE :search ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam);
    $stmt->execute();
    $result = $stmt;
} else {
    $sql = "SELECT * FROM materias ORDER BY nombre";
    $result = $conn->query($sql);
}

// Obtener estadísticas por materia
$sql_stats = "SELECT m.id, COUNT(ev.id) as total_evaluaciones, 
              AVG(ev.calificacion) as promedio,
              SUM(CASE WHEN ev.calificacion >= 6 THEN 1 ELSE 0 END) as aprobados,
              SUM(CASE WHEN ev.calificacion < 6 THEN 1 ELSE 0 END) as reprobados
              FROM materias m
              LEFT JOIN evaluaciones ev ON m.id = ev.materia_id
              GROUP BY m.id";
$result_stats = $conn->query($sql_stats);

$estadisticas = array();
while ($row = $result_stats->fetch(PDO::FETCH_ASSOC)) {
    $estadisticas[$row['id']] = $row;
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Materias</h1>
        <a href="crear.php" class="btn btn-success">
            <i data-lucide="plus" class="me-1"></i> Nueva Materia
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Buscar Materias</h5>
            </div>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre de materia..." value="<?php echo $search; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i data-lucide="search" class="me-1"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Materias</h5>
                <?php if ($result->rowCount() > 0): ?>
                    <span class="badge bg-info"><?php echo $result->rowCount(); ?> registros</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if ($result->rowCount() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estadísticas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                <?php 
                                $stats = isset($estadisticas[$row['id']]) ? $estadisticas[$row['id']] : null;
                                $tiene_evaluaciones = $stats && $stats['total_evaluaciones'] > 0;
                                $promedio = $tiene_evaluaciones ? $stats['promedio'] : null;
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['nombre']; ?></td>
                                    <td><?php echo $row['descripcion']; ?></td>
                                    <td>
                                        <?php if ($tiene_evaluaciones): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <small class="d-block">Promedio</small>
                                                    <span class="badge <?php echo $promedio >= 6 ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo number_format($promedio, 2); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <small class="d-block">Aprobación</small>
                                                    <div class="progress" style="width: 100px; height: 10px;">
                                                        <div class="progress-bar <?php echo ($stats['aprobados'] / $stats['total_evaluaciones'] * 100) >= 60 ? 'bg-success' : 'bg-warning'; ?>"
                                                            role="progressbar"
                                                            style="width: <?php echo ($stats['aprobados'] / $stats['total_evaluaciones'] * 100); ?>%"
                                                            aria-valuenow="<?php echo ($stats['aprobados'] / $stats['total_evaluaciones'] * 100); ?>"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Sin evaluaciones</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../evaluaciones/index.php?materia_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info mb-1" title="Ver evaluaciones">
                                            <i data-lucide="clipboard-list" class="icon-sm"></i>
                                        </a>
                                        <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                            <i data-lucide="edit-2" class="icon-sm"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="confirmarEliminar(<?php echo $row['id']; ?>)" class="btn btn-sm btn-danger mb-1">
                                            <i data-lucide="trash-2" class="icon-sm"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No se encontraron materias <?php echo $search ? 'con el término "' . $search . '"' : ''; ?>.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar iconos de Lucide
        lucide.createIcons();
    });

    function confirmarEliminar(id) {
        if (confirm('¿Está seguro de que desea eliminar esta materia? Esta acción también eliminará todas las evaluaciones asociadas.')) {
            window.location.href = `eliminar.php?id=${id}`;
        }
    }
</script>

<?php include '../includes/footer.php'; ?>

