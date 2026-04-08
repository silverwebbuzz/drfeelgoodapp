<?php
$page_title = 'Patient Profile - Dr. Feelgood';
ob_start();
?>

<?php if (isset($response) && $response['success']):
    $patient = $response['patient'];
    $reports = $response['progress_reports'] ?? [];
    $healthSummary = $response['health_summary'] ?? [];
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/patients">Patients</a></li>
        <li class="breadcrumb-item active">
            <?php echo htmlspecialchars($patient['fname'] . ' ' . ($patient['lname'] ?? '')); ?>
        </li>
    </ol>
</nav>

<!-- Patient Header -->
<div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #0F6E56, #1D9E75);">
    <div class="card-body" style="color: white;">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 style="color: white; margin: 0; margin-bottom: 10px;">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($patient['fname'] . ' ' . ($patient['lname'] ?? '')); ?>
                </h2>
                <p style="margin: 5px 0; color: rgba(255,255,255,0.9);">
                    <i class="fas fa-id-card"></i> ID: <?php echo htmlspecialchars($patient['patient_id']); ?>
                </p>
            </div>
            <div class="col-md-6" style="text-align: right;">
                <button class="btn btn-light" onclick="editPatient()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Basic Info -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Basic Information
            </div>
            <div class="card-body">
                <table class="table table-borderless" style="margin: 0;">
                    <tr>
                        <td style="font-weight: 600; width: 40%; color: #0F6E56;">Date of Birth:</td>
                        <td><?php echo htmlspecialchars($patient['dob'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Age:</td>
                        <td><?php echo htmlspecialchars($patient['age'] ?? 'N/A'); ?> years</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Gender:</td>
                        <td>
                            <?php
                            if ($patient['gender'] === 'M') {
                                echo '<i class="fas fa-mars"></i> Male';
                            } elseif ($patient['gender'] === 'F') {
                                echo '<i class="fas fa-venus"></i> Female';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Marital Status:</td>
                        <td><?php echo htmlspecialchars($patient['mrg_status'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Contact:</td>
                        <td><?php echo htmlspecialchars($patient['contact_no'] ?? 'N/A'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marker-alt"></i> Contact Details
            </div>
            <div class="card-body">
                <table class="table table-borderless" style="margin: 0;">
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56; width: 40%;">Address:</td>
                        <td><?php echo htmlspecialchars($patient['address'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Religion:</td>
                        <td><?php echo htmlspecialchars($patient['religion'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Occupation:</td>
                        <td><?php echo htmlspecialchars($patient['occupation'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Education:</td>
                        <td><?php echo htmlspecialchars($patient['education'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #0F6E56;">Registered:</td>
                        <td><?php echo htmlspecialchars($patient['dor'] ?? 'N/A'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chief Complaint & Health Info -->
<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-stethoscope"></i> Chief Complaint & Presentation
            </div>
            <div class="card-body">
                <p style="margin-bottom: 20px;">
                    <strong>Chief Complaint:</strong><br>
                    <span style="color: #666;">
                        <?php echo htmlspecialchars($patient['chief'] ?? 'Not recorded'); ?>
                    </span>
                </p>

                <?php if ($healthSummary): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 style="color: #0F6E56; font-weight: 600; margin-bottom: 10px;">Physical Examination</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td style="font-weight: 600;">Temperature:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['temperature'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Blood Pressure:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['blood_pressure'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Pulse:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['pulse'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Respiration:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['respiration'] ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 style="color: #0F6E56; font-weight: 600; margin-bottom: 10px;">Vital Stats</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td style="font-weight: 600;">Weight:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['weight'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Height:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['height'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Appetite:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['appetite'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Sleep:</td>
                                    <td><?php echo htmlspecialchars($healthSummary['sleep'] ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Progress Reports -->
<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-history"></i> Progress Reports (<?php echo $response['total_reports']; ?> total)</span>
                <button class="btn btn-sm btn-primary" onclick="addReport()">
                    <i class="fas fa-plus"></i> Add Report
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($reports)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Medicines</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['report_date'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($report['medicins'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($report['amt'] ?? '0'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">
                        <i class="fas fa-info-circle"></i> No progress reports yet
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function editPatient() {
    alert('Edit functionality coming soon!');
}

function addReport() {
    const medicines = prompt('Enter medicines:');
    if (!medicines) return;

    const amount = prompt('Enter amount:');
    const patientId = <?php echo $patient['id']; ?>;

    fetch(`/api/patient/${patientId}/report`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({medicins: medicines, amt: amount || 0})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Report added successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(e => alert('Error: ' + e));
}
</script>

<?php else: ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> Patient not found or error loading details
        <?php if (isset($response['message'])): ?>
            <br><?php echo htmlspecialchars($response['message']); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
