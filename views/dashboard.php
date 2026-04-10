<?php
ob_start();
$page_title = 'Dashboard - Dr. Feelgood';

function dashFmt($v) {
    if ($v === null) return 'N/A';
    $v = is_string($v) ? trim($v) : $v;
    if ($v === '' || $v === '0000-00-00') return 'N/A';
    return $v;
}

function dashFmtName($f, $l) {
    $full = trim(trim($f ?? '') . ' ' . trim($l ?? ''));
    return $full === '' ? 'N/A' : $full;
}
?>

<?php if (isset($recentPatients) && $recentPatients['success']): ?>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i> Dashboard
        </h1>
    </div>

    <!-- STATISTICS ROW -->
    <div class="row mb-24">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Patients</h3>
                    <p class="stat-value">8,312</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> Active Records
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div class="stat-content">
                    <h3>Progress Reports</h3>
                    <p class="stat-value">605K+</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> Complete History
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-content">
                    <h3>Assessments</h3>
                    <p class="stat-value">8,180</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> Recorded
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="stat-content">
                    <h3>Doctors/Staff</h3>
                    <p class="stat-value">2</p>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> Active Users
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- TODAY'S APPOINTMENTS SECTION -->
    <?php
    $todayQueue = $todayQueueData['queue'] ?? [];
    $todayStats = $todayQueueData['stats'] ?? [];
    $queue   = $todayQueue;   // required by _queue_table.php
    $compact = true;
    $tableId = 'dashQueueTable';
    ?>
    <div class="row mb-24">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-calendar-day"></i> Today's Appointments</span>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <!-- mini stats -->
                        <span style="font-size:12px;color:#6b7280;">
                            <span style="color:#d97706;font-weight:700;"><?php echo (int)($todayStats['waiting'] ?? 0); ?></span> waiting &nbsp;
                            <span style="color:#2563eb;font-weight:700;"><?php echo (int)($todayStats['in_consultation'] ?? 0); ?></span> in consult &nbsp;
                            <span style="color:#16a34a;font-weight:700;"><?php echo (int)($todayStats['completed'] ?? 0); ?></span> done
                        </span>
                        <a href="/queue" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> Full Queue</a>
                        <a href="/walkin" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Walk-in</a>
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <style>
                    .token-badge { display:inline-block; width:32px; height:32px; border-radius:50%; background:var(--primary); color:#fff; font-weight:700; font-size:12px; line-height:32px; text-align:center; }
                    .queue-row td { vertical-align:middle; }
                    .status-btns .btn { padding:3px 8px; font-size:11px; }
                    .queue-row[data-status="in_consultation"] { background:#eff6ff; }
                    .queue-row[data-status="completed"] { opacity:.75; }
                    </style>
                    <?php require __DIR__ . '/appointment/_queue_table.php'; ?>
                </div>
                <?php if (!empty($todayQueue)): ?>
                <div style="padding:10px 16px;text-align:right;border-top:1px solid #f3f4f6;">
                    <a href="/queue" style="font-size:12px;color:var(--primary);text-decoration:none;"><i class="fas fa-arrow-right"></i> View full queue</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RECENT PATIENTS SECTION -->
    <div class="row mb-24">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Recent Patients
                </div>
                <div class="card-body">
                    <?php if (!empty($recentPatients['data'])): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Contact</th>
                                        <th>Gender</th>
                                        <th>Chief Complaint</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPatients['data'] as $patient): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars(dashFmtName($patient['fname'] ?? '', $patient['lname'] ?? '')); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars(dashFmt($patient['contact_no'] ?? null)); ?></td>
                                            <td>
                                                <?php if (($patient['gender'] ?? '') === 'M'): ?>
                                                    <span class="badge badge-male">
                                                        <i class="fas fa-mars"></i> Male
                                                    </span>
                                                <?php elseif (($patient['gender'] ?? '') === 'F'): ?>
                                                    <span class="badge badge-female">
                                                        <i class="fas fa-venus"></i> Female
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: var(--gray-400);">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php $chief = trim($patient['chief'] ?? ''); ?>
                                                <span style="color: var(--gray-600);">
                                                    <?php echo $chief === '' ? '<span style="color: var(--gray-400);">N/A</span>' : htmlspecialchars(mb_strimwidth($chief, 0, 40, '...')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/patient/<?php echo $patient['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="/patients" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-right"></i> View All Patients
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 20px; color: var(--gray-500);">
                            <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 12px; display: block;"></i>
                            No recent patients found
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS SECTION -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row gap-16">
                        <div class="col-md-6 col-lg-3">
                            <a href="/patients" class="btn btn-primary w-100" style="padding: 12px; height: auto;">
                                <i class="fas fa-search"></i>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600;">Search Patient</div>
                                    <div style="font-size: 0.8rem; opacity: 0.9;">Find existing patient</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <button class="btn btn-secondary w-100" style="padding: 12px; height: auto;" onclick="alert('Feature coming soon')">
                                <i class="fas fa-file-pdf"></i>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600;">View Reports</div>
                                    <div style="font-size: 0.8rem; opacity: 0.9;">Generate reports</div>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <button class="btn btn-secondary w-100" style="padding: 12px; height: auto;" onclick="alert('Feature coming soon')">
                                <i class="fas fa-user-cog"></i>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600;">Settings</div>
                                    <div style="font-size: 0.8rem; opacity: 0.9;">Manage account</div>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="/logout" class="btn btn-danger w-100" style="padding: 12px; height: auto;">
                                <i class="fas fa-sign-out-alt"></i>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600;">Logout</div>
                                    <div style="font-size: 0.8rem; opacity: 0.9;">Sign out</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> Error loading dashboard data
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
