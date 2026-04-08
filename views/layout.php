<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Dr. Feelgood'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0F6E56;
            --secondary-color: #1D9E75;
            --danger-color: #d32f2f;
            --light-bg: #f5f7fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .sidebar {
            background: white;
            border-right: 1px solid #e0e0e0;
            min-height: 100vh;
            padding: 20px 0;
        }

        .sidebar .nav-link {
            color: #666;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--light-bg);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--light-bg);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }

        .main-content {
            padding: 20px;
        }

        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: var(--light-bg);
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 0.875rem;
        }

        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--light-bg);
        }

        .table th {
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
            color: var(--primary-color);
        }

        .table tbody tr:hover {
            background-color: var(--light-bg);
        }

        .badge {
            padding: 5px 10px;
            font-size: 0.75rem;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-top: 10px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(15, 110, 86, 0.25);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-right: 40px;
        }

        .search-box .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .patient-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }

        .patient-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(15, 110, 86, 0.1);
        }

        .patient-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .footer {
            background-color: white;
            border-top: 1px solid #e0e0e0;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
        }

        .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-heartbeat"></i> Dr. Feelgood
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (\App\Controllers\AuthController::isLoggedIn()): ?>
                        <?php $user = \App\Controllers\AuthController::getCurrentUser(); ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <strong><?php echo htmlspecialchars($user['fullname']); ?></strong></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row" style="min-height: 100vh;">
            <!-- Sidebar -->
            <?php if (\App\Controllers\AuthController::isLoggedIn()): ?>
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'active' : ''; ?>"
                               href="/dashboard">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'patients') !== false) ? 'active' : ''; ?>"
                               href="/patients">
                                <i class="fas fa-users"></i> Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/patient/create">
                                <i class="fas fa-user-plus"></i> Add Patient
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <?php endif; ?>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="main-content">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php echo $content; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Dr. Feelgood. All rights reserved. | v<?php echo APP_VERSION; ?></p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-close alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        });
    </script>
</body>
</html>
