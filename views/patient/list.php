<?php
ob_start();
$page_title = 'Patients - Dr. Feelgood';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i> Patient List
    </h1>
</div>

<!-- SEARCH SECTION -->
<div class="row mb-24">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text" style="background: white; border-right: none;">
                        <i class="fas fa-search" style="color: var(--gray-400);"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control"
                        id="patientSearch"
                        placeholder="Search by name, contact, or ID..."
                        style="border-left: none;"
                    >
                </div>
                <div id="searchResults" style="margin-top: 12px; display: none;">
                    <div style="padding: 12px; color: var(--gray-600);">
                        <span id="searchResultsCount">0</span> results found
                    </div>
                    <div id="searchList" style="max-height: 300px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PATIENTS TABLE -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                Patients (Page <?php echo htmlspecialchars($_GET['page'] ?? 1); ?>)
            </div>
            <div class="card-body">
                <?php if (isset($response['success']) && $response['success'] && !empty($response['data'])): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Gender</th>
                                    <th>DOB</th>
                                    <th>Chief Complaint</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($response['data'] as $patient): ?>
                                    <tr>
                                        <td>
                                            <code style="background: var(--gray-100); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                                <?php echo htmlspecialchars($patient['patient_id'] ?? $patient['id']); ?>
                                            </code>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($patient['fname'] . ' ' . ($patient['lname'] ?? '')); ?></strong>
                                        </td>
                                        <td>
                                            <a href="tel:<?php echo htmlspecialchars($patient['contact_no']); ?>">
                                                <?php echo htmlspecialchars($patient['contact_no'] ?? 'N/A'); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($patient['gender'] === 'M'): ?>
                                                <span class="badge badge-male">
                                                    <i class="fas fa-mars"></i> Male
                                                </span>
                                            <?php elseif ($patient['gender'] === 'F'): ?>
                                                <span class="badge badge-female">
                                                    <i class="fas fa-venus"></i> Female
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-primary">Other</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($patient['dob'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <span style="color: var(--gray-600);">
                                                <?php echo htmlspecialchars(substr($patient['chief'] ?? 'N/A', 0, 35)); ?>
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

                    <!-- PAGINATION -->
                    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 20px;">
                        <?php
                        $currentPage = (int)($_GET['page'] ?? 1);
                        if ($currentPage > 1): ?>
                            <a href="/patients?page=<?php echo $currentPage - 1; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <span style="display: flex; align-items: center; color: var(--gray-600);">
                            Page <?php echo $currentPage; ?>
                        </span>

                        <a href="/patients?page=<?php echo $currentPage + 1; ?>" class="btn btn-outline-primary">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--gray-500);">
                        <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 12px; display: block;"></i>
                        No patients found
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('patientSearch').addEventListener('input', async function(e) {
    const query = e.target.value.trim();

    if (query.length < 2) {
        document.getElementById('searchResults').style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`/api/patient/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            let html = '';
            data.data.forEach(patient => {
                html += `
                    <div style="padding: 12px; border-bottom: 1px solid var(--gray-200); cursor: pointer;" onclick="window.location.href='/patient/${patient.id}'">
                        <strong>${patient.fname} ${patient.lname || ''}</strong>
                        <br>
                        <small style="color: var(--gray-500);">${patient.contact_no} • ${patient.dob}</small>
                    </div>
                `;
            });
            document.getElementById('searchList').innerHTML = html;
            document.getElementById('searchResultsCount').textContent = data.data.length;
            document.getElementById('searchResults').style.display = 'block';
        } else {
            document.getElementById('searchResults').style.display = 'none';
        }
    } catch (error) {
        console.error('Search error:', error);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
