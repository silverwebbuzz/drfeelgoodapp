<?php
ob_start();
$page_title = 'Add Patient - Dr. Feelgood';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-user-plus"></i> Add New Patient
    </h1>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                Patient Information
            </div>
            <div class="card-body">
                <form id="createPatientForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="fname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="lname">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number *</label>
                            <input type="text" class="form-control" name="contact_no" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" name="dob" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">Select Gender</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="O">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient ID</label>
                            <input type="text" class="form-control" name="patient_id" placeholder="Auto-generated if left empty">
                        </div>
                    </div>

                    <div class mb-3">
                        <label class="form-label">Chief Complaint</label>
                        <textarea class="form-control" name="chief" rows="3" placeholder="Main reason for visit..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" name="zip">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Create Patient
                            </button>
                            <a href="/patients" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('createPatientForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

    const formData = new FormData(this);
    const data = new URLSearchParams(formData);

    try {
        const response = await fetch('/patient/create', {
            method: 'POST',
            body: data
        });

        const result = await response.json();

        if (result.success) {
            alert('Patient created successfully!');
            window.location.href = '/patient/' + result.patient_id;
        } else {
            alert('Error: ' + (result.message || 'Failed to create patient'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while creating the patient');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Patient';
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
