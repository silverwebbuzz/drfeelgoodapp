<?php
ob_start();
$page_title = 'Patient Profile - Dr. Feelgood';

function fmt($value, $fallback = 'N/A') {
    if ($value === null) return $fallback;
    $value = is_string($value) ? trim($value) : $value;
    if ($value === '' || $value === '0000-00-00' || $value === '1970-01-01') return $fallback;
    return $value;
}

function fmtDate($value) {
    if ($value === null || $value === '' || $value === '0000-00-00' || strpos((string)$value, '0000') === 0 || $value === '1970-01-01') {
        return 'N/A';
    }
    $ts = strtotime($value);
    return $ts ? date('d M Y', $ts) : 'N/A';
}

function fmtGender($g) {
    if ($g === 'M') return 'Male';
    if ($g === 'F') return 'Female';
    return 'N/A';
}

function fmtMaritalStatus($s) {
    $map = ['S' => 'Single', 'M' => 'Married', 'D' => 'Divorced', 'W' => 'Widowed'];
    return $map[$s] ?? 'N/A';
}

function fmtVeg($v) {
    $map = ['V' => 'Vegetarian', 'NV' => 'Non-Vegetarian', 'EV' => 'Eggetarian'];
    return $map[$v] ?? 'N/A';
}

function fmtName($fname, $lname) {
    $full = trim(trim($fname ?? '') . ' ' . trim($lname ?? ''));
    return $full === '' ? 'N/A' : $full;
}
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
            <?php echo htmlspecialchars(fmtName($patient['fname'] ?? '', $patient['lname'] ?? '')); ?>
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
                                <?php echo htmlspecialchars(fmt($patient['patient_id'] ?? null)); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Date of Birth</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(fmtDate($patient['dob'] ?? '')); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Age</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(!empty($patient['age']) ? $patient['age'] . ' years' : 'N/A'); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Gender</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(fmtGender($patient['gender'] ?? '')); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Marital Status</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(fmtMaritalStatus($patient['mrg_status'] ?? '')); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Contact Number</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php $contact = trim($patient['contact_no'] ?? ''); ?>
                                <?php if ($contact !== ''): ?>
                                    <a href="tel:<?php echo htmlspecialchars($contact); ?>"><?php echo htmlspecialchars($contact); ?></a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Date of Registration</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(fmtDate($patient['dor'] ?? '')); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Occupation</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(fmt($patient['occupation'] ?? null)); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Education</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(fmt($patient['education'] ?? null)); ?>
                            </p>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Religion</label>
                            <p style="margin: 4px 0; font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars(fmt($patient['religion'] ?? null)); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <hr style="margin: 20px 0;">
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Address</label>
                    <p style="margin: 4px 0; font-size: 1rem;">
                        <?php echo htmlspecialchars(fmt($patient['address'] ?? null)); ?>
                    </p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Referred By</label>
                    <p style="margin: 4px 0; font-size: 1rem;">
                        <?php echo htmlspecialchars(fmt($patient['refered_by'] ?? null)); ?>
                    </p>
                </div>
                <div>
                    <label style="color: var(--gray-500); font-size: 0.85rem; text-transform: uppercase;">Chief Complaint</label>
                    <p style="margin: 8px 0; font-size: 1rem; white-space: pre-line;">
                        <?php echo htmlspecialchars(fmt($patient['chief'] ?? null)); ?>
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
                                        <?php echo htmlspecialchars(fmtDate($report['date'] ?? '')); ?>
                                    </p>
                                    <p style="margin: 0 0 4px 0; color: var(--gray-700);">
                                        <strong>Medicines:</strong> <?php echo htmlspecialchars(fmt($report['medicins'] ?? null)); ?>
                                    </p>
                                    <p style="margin: 0; color: var(--gray-700);">
                                        <strong>Amount:</strong> <?php echo !empty($report['amt']) ? '₹' . htmlspecialchars($report['amt']) : 'N/A'; ?>
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
                        <?php echo !empty($reports) ? htmlspecialchars(fmtDate($reports[0]['date'] ?? '')) : 'No visits'; ?>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <div style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 4px;">Address</div>
                    <div style="font-size: 0.95rem;">
                        <?php echo htmlspecialchars(fmt($patient['address'] ?? null)); ?>
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
