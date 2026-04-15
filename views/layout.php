<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Dr. Feelgood'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/css/style.css?v=<?php echo @filemtime(dirname(__DIR__).'/css/style.css') ?: time(); ?>" rel="stylesheet">
    <link href="/css/datatable.css" rel="stylesheet">
</head>
<body>
    <?php
    $layoutRole   = (isset($_SESSION['role']) && $_SESSION['role'] !== '') ? $_SESSION['role'] : 'doctor';
    $isDoctor     = $layoutRole === 'doctor';
    $isAsstDoctor = $layoutRole === 'asst_doctor';
    $isReception  = $layoutRole === 'reception';
    $canReports   = $isDoctor || $isAsstDoctor;
    $uri          = $_SERVER['REQUEST_URI'];
    ?>
    <div class="app-wrapper">

        <!-- ── HEADER ─────────────────────────────────── -->
        <header class="app-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="/dashboard" class="app-brand">
                    <i class="fas fa-heart"></i>
                    <span>Dr. Feelgood</span>
                </a>
            </div>

            <div class="header-user">
                <div class="header-name">
                    <span class="full-name"><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                    <span class="role-pill"><?php echo htmlspecialchars(\App\Models\User::roleLabel($layoutRole)); ?></span>
                </div>
                <a href="/logout" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="logout-label">Logout</span>
                </a>
            </div>
        </header>

        <!-- ── OVERLAY (mobile drawer backdrop) ──────── -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="app-container">

            <!-- ── SIDEBAR ───────────────────────────── -->
            <aside class="app-sidebar" id="appSidebar">
                <nav>
                    <ul class="sidebar-menu">

                        <li>
                            <a href="/dashboard" class="<?php echo strpos($uri, 'dashboard') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-th-large"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href="/patients" class="<?php echo (strpos($uri, 'patient') !== false && strpos($uri, 'reports') === false) ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i>
                                <span>Patients</span>
                            </a>
                        </li>

                        <li>
                            <a href="/queue" class="<?php echo (strpos($uri, 'queue') !== false || strpos($uri, 'walkin') !== false) ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-check"></i>
                                <span>Appointments</span>
                            </a>
                        </li>

                        <?php if ($canReports): ?>
                        <?php $onReports = strpos($uri, '/reports') !== false; ?>
                        <li class="has-submenu <?php echo $onReports ? 'open' : ''; ?>">
                            <a href="#" class="<?php echo $onReports ? 'active' : ''; ?>" onclick="toggleSubmenu(this);return false;">
                                <i class="fas fa-chart-bar"></i>
                                <span>Reports</span>
                                <i class="fas fa-chevron-down submenu-arrow"></i>
                            </a>
                            <ul class="submenu">
                                <li><a href="/reports/patients"     class="<?php echo strpos($uri,'/reports/patients')!==false?'active':''; ?>"><i class="fas fa-users"></i> Patients</a></li>
                                <!--li><a href="/reports/income"       class="<?php echo strpos($uri,'/reports/income')!==false?'active':''; ?>"><i class="fas fa-rupee-sign"></i> Income</a></li>
                                <li><a href="/reports/queue"        class="<?php echo strpos($uri,'/reports/queue')!==false?'active':''; ?>"><i class="fas fa-list-ol"></i> Queue / Ops</a></li>
                                <li><a href="/reports/medicines"    class="<?php echo strpos($uri,'/reports/medicines')!==false?'active':''; ?>"><i class="fas fa-pills"></i> Medicines</a></li>
                                <li><a href="/reports/productivity" class="<?php echo strpos($uri,'/reports/productivity')!==false?'active':''; ?>"><i class="fas fa-stethoscope"></i> Productivity</a></li-->
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if ($isDoctor): ?>
                        <li>
                            <a href="/users" class="<?php echo strpos($uri, '/users') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-users-cog"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="/clinic-settings" class="<?php echo strpos($uri, 'clinic-settings') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="/help" class="<?php echo strpos($uri, '/help') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-book-open"></i>
                                <span>Help</span>
                            </a>
                        </li>
                        <?php endif; ?>

                    </ul>
                </nav>
            </aside>

            <!-- ── MAIN CONTENT ──────────────────────── -->
            <main class="app-content">
                <?php echo $content; ?>
            </main>

        </div><!-- /.app-container -->
    </div><!-- /.app-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle  = document.getElementById('sidebarToggle');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });

        overlay.addEventListener('click', closeSidebar);

        // Close drawer on nav link click (mobile)
        sidebar.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });

        // Close drawer on window resize to desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) closeSidebar();
        });
    })();

    function toggleSubmenu(el) {
        el.closest('.has-submenu').classList.toggle('open');
    }
    </script>
</body>
</html>
