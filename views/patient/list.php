<?php
$page_title = 'Patients - Dr. Feelgood';
ob_start();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Patients</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="color: #333; margin: 0;">
                <i class="fas fa-users"></i> Patient List
            </h2>
            <a href="/patient/create" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add Patient
            </a>
        </div>
    </div>
</div>

<?php if (isset($response) && $response['success']): ?>
    <!-- Search Box -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-6">
            <div class="search-box">
                <input type="text" class="form-control" id="patientSearch" placeholder="Search by name or contact...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($response['data'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Chief Complaint</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($response['data'] as $patient): ?>
                                        <tr>
                                            <td>
                                                <strong class="patient-name">
                                                    <?php echo htmlspecialchars($patient['fname'] . ' ' . ($patient['lname'] ?? '')); ?>
                                                </strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['contact_no'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($patient['age'] ?? 'N/A'); ?></td>
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

                        <!-- Pagination -->
                        <?php if (isset($response['pagination'])): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $response['pagination']['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo ($i == $response['pagination']['current_page']) ? 'active' : ''; ?>">
                                            <a class="page-link" href="/patients?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: #999; text-align: center; padding: 40px;">
                            <i class="fas fa-info-circle"></i> No patients found
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> Error loading patient list
        <?php if (isset($response['message'])): ?>
            <br><?php echo htmlspecialchars($response['message']); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
document.getElementById('patientSearch').addEventListener('keyup', function(e) {
    const query = this.value;
    if (query.length < 2) return;

    fetch(`/api/patient/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Search results:', data.data);
                // Update table with search results
                const tableBody = document.querySelector('table tbody');
                tableBody.innerHTML = '';

                if (data.data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No results found</td></tr>';
                    return;
                }

                data.data.forEach(patient => {
                    const row = `
                        <tr>
                            <td><strong class="patient-name">${patient.fname} ${patient.lname}</strong></td>
                            <td>${patient.contact_no || 'N/A'}</td>
                            <td>N/A</td>
                            <td>${patient.gender === 'M' ? '<span class="badge bg-light text-dark"><i class="fas fa-mars"></i> Male</span>' : '<span class="badge bg-light text-dark"><i class="fas fa-venus"></i> Female</span>'}</td>
                            <td>${patient.chief || 'N/A'}</td>
                            <td><a href="/patient/${patient.id}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View</a></td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }
        })
        .catch(error => console.error('Search error:', error));
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
