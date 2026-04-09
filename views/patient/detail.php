<?php
ob_start();
$page_title = 'Patient Profile - Dr. Feelgood';
?>

<?php if (isset($response) && $response['success']):
    $patient = $response['patient'];
    $reports = $response['progress_reports'] ?? [];
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">
            <i class="fas fa-user-circle"></i>
            <?php echo htmlspecialchars($patient['fname'] . ' ' . ($patient['lname'] ?? '')); ?>
        </h1>
        <a href="/patients" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Patients
        </a>
    </div>
</div>

<!-- PATIENT PROFILE SECTION -->
<div class="row mb-24">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-id-card"></i> Basic Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Patient ID</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars($patient['patient_id'] ?? 'N/A'); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Date of Birth</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars($patient['dob'] ?? 'N/A'); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Gender</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php
                                if ($patient['gender'] === 'M') echo 'Male';
                                elseif ($patient['gender'] === 'F') echo 'Female';
                                else echo htmlspecialchars($patient['gender'] ?? 'N/A');
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Contact Number</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <a href="tel:<?php echo htmlspecialchars($patient['contact_no'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($patient['contact_no'] ?? 'N/A'); ?>
                                </a>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">City</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars($patient['city'] ?? 'N/A'); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">State</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars($patient['state'] ?? 'N/A'); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <hr style="margin: 20px 0;">
                <div>
                    <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Chief Complaint</label>
                    <p style="margin: 8px 0; font-size: 1rem;">
                        <?php echo htmlspecialchars($patient['chief'] ?? 'N/A'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Progress Reports -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-medical"></i> Progress Reports (<?php echo count($reports); ?>)
            </div>
            <div class="card-body">
                <?php if (!empty($reports)): ?>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <?php foreach ($reports as $report): ?>
                            <div style="padding: 16px; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <p style="margin: 0 0 8px 0; font-weight: 600; color: var(--gray-900);">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo htmlspecialchars($report['date'] ?? 'N/A'); ?>
                                    </p>
                                    <p style="margin: 0 0 4px 0; color: var(--gray-700);">
                                        <strong>Medicines:</strong> <?php echo htmlspecialchars($report['medicins'] ?? 'N/A'); ?>
                                    </p>
                                    <p style="margin: 0; color: var(--gray-700);">
                                        <strong>Amount:</strong> <?php echo htmlspecialchars($report['amt'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                                <span class="badge badge-primary">Report #<?php echo htmlspecialchars($report['id']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--gray-500);">
                        <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 12px; display: block;"></i>
                        No progress reports found
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SIDEBAR INFO -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Summary
            </div>
            <div class="card-body">
                <div style="margin-bottom: 16px;">
                    <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Total Reports</div>
                    <div style="font-size: 1.8rem; font-weight: 700; color: var(--primary);">
                        <?php echo count($reports); ?>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Last Visit</div>
                    <div style="font-size: 1.1rem; font-weight: 600;">
                        <?php echo !empty($reports) ? htmlspecialchars($reports[0]['date'] ?? 'N/A') : 'No visits'; ?>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Address</div>
                    <div style="font-size: 0.95rem;">
                        <?php echo htmlspecialchars(($patient['address'] ?? '') . ', ' . ($patient['city'] ?? '') . ', ' . ($patient['state'] ?? '')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt"></i> Actions
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 8px;">
                <button class="btn btn-primary w-100" onclick="alert('Coming soon')">
                    <i class="fas fa-plus"></i> Add Report
                </button>
                <button class="btn btn-secondary w-100" onclick="alert('Coming soon')">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <button class="btn btn-secondary w-100" onclick="alert('Coming soon')">
                    <i class="fas fa-file-pdf"></i> Download History
                </button>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($response['message'] ?? 'Patient not found'); ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
