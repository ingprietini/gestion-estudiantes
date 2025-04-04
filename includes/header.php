<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
$current_directory = basename(dirname($_SERVER['SCRIPT_NAME']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Estudiantes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo $current_directory != 'gestion-estudiantes' ? '../assets/css/styles.css' : 'assets/css/styles.css'; ?>">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Sistema de Gestión de Estudiantes</h1>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <span class="me-3">Bienvenido, <?php echo $_SESSION['nombre_usuario']; ?></span>
                    <a href="<?php echo $current_directory != 'gestion-estudiantes' ? '../logout.php' : 'logout.php'; ?>" class="btn btn-outline-light btn-sm">
                        Cerrar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if (isset($_SESSION['usuario_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_directory == 'gestion-estudiantes' && $current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo $current_directory != 'gestion-estudiantes' ? '../index.php' : 'index.php'; ?>">
                            <i data-lucide="home" class="me-1" style="width: 18px; height: 18px;"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_directory == 'estudiantes' ? 'active' : ''; ?>" href="<?php echo $current_directory != 'gestion-estudiantes' ? '../estudiantes/index.php' : 'estudiantes/index.php'; ?>">
                            <i data-lucide="users" class="me-1" style="width: 18px; height: 18px;"></i> Estudiantes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_directory == 'materias' ? 'active' : ''; ?>" href="<?php echo $current_directory != 'gestion-estudiantes' ? '../materias/index.php' : 'materias/index.php'; ?>">
                            <i data-lucide="book" class="me-1" style="width: 18px; height: 18px;"></i> Materias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_directory == 'evaluaciones' ? 'active' : ''; ?>" href="<?php echo $current_directory != 'gestion-estudiantes' ? '../evaluaciones/index.php' : 'evaluaciones/index.php'; ?>">
                            <i data-lucide="clipboard-list" class="me-1" style="width: 18px; height: 18px;"></i> Evaluaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_directory == 'usuarios' ? 'active' : ''; ?>" href="<?php echo $current_directory != 'gestion-estudiantes' ? '../usuarios/index.php' : 'usuarios/index.php'; ?>">
                            <i data-lucide="user-cog" class="me-1" style="width: 18px; height: 18px;"></i> Usuarios
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

