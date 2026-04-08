<?php
$page_title = 'Dashboard - Dr. Feelgood';
?>

<?php
if (isset($recentPatients) && $recentPatients['success']): ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active"><i class="fas fa-home"></i> Dashboard</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12">
            <h2 style="margin-bottom: 30px; color: #333;">
                <i class="fas fa-chart-line"></i> Dashboard
            </h2>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #0F6E56;">
                <div class="card-body">
                    <div style="text-align: center;">
                        <i class="fas fa-users" style="font-size: 2rem; color: #0F6E56;"></i>
                        <div style="margin-top: 15px;">
                            <div style="color: #999; font-size: 0.9rem;">Total Patients</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #333;">8,312</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #1D9E75;">
                <div class="card-body">
                    <div style="text-align: center;">
                        <i class="fas fa-file-medical" style="font-size: 2rem; color: #1D9E75;"></i>
                        <div style="margin-top: 15px;">
                            <div style="color: #999; font-size: 0.9rem;">Progress Reports</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #333;">605K+</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #00796B;">
                <div class="card-body">
                    <div style="text-align: center;">
                        <i class="fas fa-calendar-alt" style="font-size: 2rem; color: #00796B;"></i>
                        <div style="margin-top: 15px;">
                            <div style="color: #999; font-size: 0.9rem;">Assessments</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #333;">8,180</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #004D40;">
                <div class="card-body">
                    <div style="text-align: center;">
                        <i class="fas fa-stethoscope" style="font-size: 2rem; color: #004D40;"></i>
                        <div style="margin-top: 15px;">
                            <div style="color: #999; font-size: 0.9rem;">Doctors/Staff</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #333;">2</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Patients -->
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Recent Patients
                </div>
                <div class="card-body">
                    <?php if (!empty($recentPatients['data'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
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
                                                <strong><?php echo htmlspecialchars($patient['fname'] . ' ' . ($patient['lname'] ?? '')); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['contact_no'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($patient['gender'] === 'M'): ?>
                                                    <span class="badge bg-light text-dark"><i class="fas fa-mars"></i> Male</span>
                                                <?php elseif ($patient['gender'] === 'F'): ?>
                                                    <span class="badge bg-light text-dark"><i class="fas fa-venus"></i> Female</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['chief'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="/patient/<?php echo $patient['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: center;">
                            <a href="/patients" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-right"></i> View All Patients
                            </a>
                        </div>
                    <?php else: ?>
                        <p style="color: #999; text-align: center;">No recent patients</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lightning-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="/patient/create" class="btn btn-primary w-100" style="padding: 15px;">
                                <i class="fas fa-user-plus"></i> Add New Patient
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/patients" class="btn btn-outline-primary w-100" style="padding: 15px;">
                                <i class="fas fa-search"></i> Search Patient
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100" style="padding: 15px;" onclick="alert('Reports feature coming soon!')">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </button>
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
