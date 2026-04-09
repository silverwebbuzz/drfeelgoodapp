<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Dr. Feelgood'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/datatable.css" rel="stylesheet">
</head>
<body>
    <div class="app-wrapper">
        <!-- HEADER -->
        <header class="app-header">
            <a href="/dashboard" class="app-brand">
                <i class="fas fa-heart"></i>
                <span>Dr. Feelgood</span>
            </a>
            <div>
                <span style="color: var(--gray-600); margin-right: 20px;">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User'); ?>
                </span>
                <a href="/logout" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>

        <div class="app-container">
            <!-- SIDEBAR -->
            <aside class="app-sidebar">
                <nav>
                    <ul class="sidebar-menu">
                        <li>
                            <a href="/dashboard" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'active' : ''; ?>">
                                <i class="fas fa-chart-line"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="/patients" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'patients') !== false) ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i>
                                <span>Patients</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="alert('Coming soon')" style="cursor: not-allowed; opacity: 0.6;">
                                <i class="fas fa-file-medical"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="alert('Coming soon')" style="cursor: not-allowed; opacity: 0.6;">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- MAIN CONTENT -->
            <main class="app-content">
                <?php echo $content; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
