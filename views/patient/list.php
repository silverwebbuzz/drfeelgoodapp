<?php
ob_start();
$page_title = 'Patients - Dr. Feelgood';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i> Patient Management
    </h1>
</div>

<!-- DATATABLE SECTION -->
<div class="datatable-container">
    <!-- HEADER WITH SEARCH & CONTROLS -->
    <div class="datatable-header">
        <div class="datatable-search">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input
                    type="text"
                    class="form-control"
                    id="tableSearch"
                    placeholder="Search patients by name, contact, or ID..."
                >
            </div>
        </div>
    </div>

    <!-- TABLE WRAPPER -->
    <div class="datatable-table-wrapper">
        <?php if (isset($response['success']) && $response['success'] && !empty($response['data'])): ?>
            <table class="datatable-table" id="patientsTable">
                <thead>
                    <tr>
                        <th class="sortable" data-column="patient_id" data-type="text">Patient ID</th>
                        <th class="sortable" data-column="name" data-type="text">Name</th>
                        <th class="sortable" data-column="contact_no" data-type="text">Contact</th>
                        <th class="sortable" data-column="gender" data-type="text">Gender</th>
                        <th class="sortable" data-column="dob" data-type="date">DOB</th>
                        <th>Chief Complaint</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php foreach ($response['data'] as $patient): ?>
                        <tr>
                            <td>
                                <code><?php echo htmlspecialchars($patient['patient_id'] ?? $patient['id']); ?></code>
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
                                    <span class="badge badge-male"><i class="fas fa-mars"></i> Male</span>
                                <?php elseif ($patient['gender'] === 'F'): ?>
                                    <span class="badge badge-female"><i class="fas fa-venus"></i> Female</span>
                                <?php else: ?>
                                    <span class="badge badge-primary">Other</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($patient['dob'] ?? 'N/A'); ?></td>
                            <td>
                                <span style="color: var(--gray-600);">
                                    <?php echo htmlspecialchars(substr($patient['chief'] ?? 'N/A', 0, 40)); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="/patient/<?php echo $patient['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="datatable-empty">
                <i class="fas fa-inbox"></i>
                <p>No patients found</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER WITH PAGINATION & INFO -->
    <div class="datatable-footer">
        <div class="datatable-info">
            Showing <span id="startEntry">1</span> to <span id="endEntry">10</span> of <span id="totalEntries">0</span> entries
        </div>

        <div class="datatable-controls">
            <div class="datatable-entries-select">
                <label for="entriesPerPage">Show</label>
                <select id="entriesPerPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entries</span>
            </div>
        </div>

        <div class="datatable-pagination" id="pagination"></div>
    </div>
</div>

<script>
class DataTable {
    constructor(options) {
        this.tableBody = document.getElementById(options.bodyId);
        this.table = document.getElementById(options.tableId);
        this.searchInput = document.getElementById(options.searchId);
        this.paginationContainer = document.getElementById(options.paginationId);
        this.entriesSelect = document.getElementById(options.entriesSelectId);
        this.allRows = Array.from(this.tableBody.querySelectorAll('tr'));
        this.currentPage = 1;
        this.entriesPerPage = 10;
        this.sortColumn = null;
        this.sortDirection = 'asc';

        this.init();
    }

    init() {
        this.updateTotalEntries();
        this.attachSearchListener();
        this.attachSortListeners();
        this.attachEntriesSelectListener();
        this.render();
    }

    attachSearchListener() {
        this.searchInput.addEventListener('input', (e) => {
            this.currentPage = 1;
            this.filterRows(e.target.value.toLowerCase());
        });
    }

    filterRows(query) {
        this.visibleRows = this.allRows.filter(row => {
            return row.innerText.toLowerCase().includes(query);
        });
        this.updateTotalEntries();
        this.currentPage = 1;
        this.render();
    }

    attachSortListeners() {
        const headers = this.table.querySelectorAll('th.sortable');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.column;
                if (this.sortColumn === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = column;
                    this.sortDirection = 'asc';
                }

                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                header.classList.add(this.sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');

                this.sortRows();
                this.currentPage = 1;
                this.render();
            });
        });
    }

    sortRows() {
        this.visibleRows.sort((a, b) => {
            const aValue = a.cells[this.getColumnIndex(this.sortColumn)].innerText.trim();
            const bValue = b.cells[this.getColumnIndex(this.sortColumn)].innerText.trim();

            let aNum = parseFloat(aValue) || aValue;
            let bNum = parseFloat(bValue) || bValue;

            if (aNum < bNum) return this.sortDirection === 'asc' ? -1 : 1;
            if (aNum > bNum) return this.sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    getColumnIndex(columnName) {
        const headers = Array.from(this.table.querySelectorAll('th.sortable'));
        return headers.findIndex(h => h.dataset.column === columnName);
    }

    attachEntriesSelectListener() {
        this.entriesSelect.addEventListener('change', (e) => {
            this.entriesPerPage = parseInt(e.target.value);
            this.currentPage = 1;
            this.render();
        });
    }

    updateTotalEntries() {
        this.visibleRows = this.visibleRows || this.allRows;
        document.getElementById('totalEntries').textContent = this.visibleRows.length;
    }

    render() {
        this.tableBody.innerHTML = '';

        const start = (this.currentPage - 1) * this.entriesPerPage;
        const end = start + this.entriesPerPage;
        const paginatedRows = this.visibleRows.slice(start, end);

        if (paginatedRows.length === 0) {
            this.tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--gray-500);">No records found</td></tr>';
        } else {
            paginatedRows.forEach(row => this.tableBody.appendChild(row.cloneNode(true)));
        }

        this.updateInfo(start, end);
        this.renderPagination();
    }

    updateInfo(start, end) {
        const total = this.visibleRows.length;
        document.getElementById('startEntry').textContent = total === 0 ? 0 : start + 1;
        document.getElementById('endEntry').textContent = Math.min(end, total);
        document.getElementById('totalEntries').textContent = total;
    }

    renderPagination() {
        const totalPages = Math.ceil(this.visibleRows.length / this.entriesPerPage);
        let html = '';

        // Previous button
        if (this.currentPage > 1) {
            html += `<a href="#" onclick="return table.goToPage(${this.currentPage - 1})"><i class="fas fa-chevron-left"></i></a>`;
        } else {
            html += `<span class="disabled"><i class="fas fa-chevron-left"></i></span>`;
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === this.currentPage) {
                html += `<span class="active">${i}</span>`;
            } else if (i <= 3 || i > totalPages - 2 || (i > this.currentPage - 2 && i < this.currentPage + 2)) {
                html += `<a href="#" onclick="return table.goToPage(${i})">${i}</a>`;
            } else if (i === 4 || i === totalPages - 2) {
                html += `<span>...</span>`;
            }
        }

        // Next button
        if (this.currentPage < totalPages) {
            html += `<a href="#" onclick="return table.goToPage(${this.currentPage + 1})"><i class="fas fa-chevron-right"></i></a>`;
        } else {
            html += `<span class="disabled"><i class="fas fa-chevron-right"></i></span>`;
        }

        this.paginationContainer.innerHTML = html;
    }

    goToPage(page) {
        this.currentPage = page;
        this.render();
        return false;
    }
}

// Initialize table
const table = new DataTable({
    tableId: 'patientsTable',
    bodyId: 'tableBody',
    searchId: 'tableSearch',
    paginationId: 'pagination',
    entriesSelectId: 'entriesPerPage'
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
