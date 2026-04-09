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

function fmtName($fname, $lname) {
    $full = trim(trim($fname ?? '') . ' ' . trim($lname ?? ''));
    return $full === '' ? 'N/A' : $full;
}
?>

<?php if (isset($response) && $response['success']):
    $patient = $response['patient'];
    $reports = $response['progress_reports'] ?? [];
    $totalReports = $response['total_reports'] ?? count($reports);
    $patientId = $patient['id'];
?>

<style>
/* ── Patient Detail Page ── */
.patient-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 16px;
}
.patient-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    font-weight: 700;
    flex-shrink: 0;
}
.patient-header-info h2 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--gray-900);
}
.patient-header-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 4px;
}
.patient-header-meta span {
    font-size: 0.85rem;
    color: var(--gray-600);
}
.patient-header-meta span i {
    margin-right: 4px;
    color: var(--gray-400);
}
.patient-header-actions {
    margin-left: auto;
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

/* ── Compact info grid ── */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0;
}
.info-item {
    padding: 10px 14px;
    border-right: 1px solid var(--gray-100);
    border-bottom: 1px solid var(--gray-100);
}
.info-item:last-child { border-right: none; }
.info-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--gray-500);
    margin-bottom: 2px;
}
.info-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--gray-800);
}
.info-value a { color: var(--primary); text-decoration: none; }
.info-full {
    grid-column: 1 / -1;
    padding: 10px 14px;
    border-bottom: 1px solid var(--gray-100);
}

/* ── Two-panel workspace ── */
.workspace {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 16px;
    align-items: start;
}
@media (max-width: 1024px) {
    .workspace { grid-template-columns: 1fr; }
}

/* ── Add Report Form ── */
.report-form-card .card-header {
    background: var(--primary);
    color: white;
}
.report-form-card .card-header i { margin-right: 6px; }
.form-row-inline {
    display: grid;
    grid-template-columns: 1fr 140px;
    gap: 12px;
}
.report-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    font-size: 0.95rem;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
    transition: border-color 0.2s;
}
.report-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.amount-input {
    min-height: unset;
    height: 44px;
    resize: none;
}
.date-input {
    min-height: unset;
    height: 44px;
    resize: none;
}
.save-btn {
    width: 100%;
    padding: 12px;
    font-size: 1rem;
    font-weight: 600;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 4px;
}
.save-btn:hover { background: var(--primary-dark, #1d4ed8); }
.save-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.save-success {
    background: var(--success, #16a34a);
    color: white;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 0.9rem;
    display: none;
    margin-top: 8px;
}

/* ── History Panel ── */
.history-panel {
    position: sticky;
    top: 16px;
}
.history-list {
    max-height: calc(100vh - 280px);
    overflow-y: auto;
}
.history-item {
    padding: 12px 16px;
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.15s;
}
.history-item:last-child { border-bottom: none; }
.history-item:hover { background: var(--gray-50); }
.history-item.latest {
    background: #eff6ff;
    border-left: 3px solid var(--primary);
}
.history-date {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 4px;
}
.history-meds {
    font-size: 0.9rem;
    color: var(--gray-800);
    font-weight: 500;
    margin-bottom: 2px;
}
.history-amt {
    font-size: 0.82rem;
    color: var(--gray-500);
}
.history-report-num {
    float: right;
    font-size: 0.75rem;
    color: var(--gray-400);
}

/* ── Inline Edit ── */
.editable-field {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    border-radius: 4px;
    padding: 1px 4px;
    transition: background 0.15s;
}
.editable-field:hover { background: var(--gray-100); }
.editable-field .edit-icon {
    opacity: 0;
    font-size: 0.7rem;
    color: var(--gray-400);
    transition: opacity 0.15s;
}
.editable-field:hover .edit-icon { opacity: 1; }
.inline-edit-input {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--gray-800);
    border: 1px solid var(--primary);
    border-radius: 4px;
    padding: 2px 6px;
    width: 100%;
    font-family: inherit;
}
</style>

<!-- ── COMPACT PATIENT HEADER ── -->
<div class="patient-header">
    <div class="patient-avatar">
        <?php echo strtoupper(substr($patient['fname'] ?? 'P', 0, 1)); ?>
    </div>
    <div class="patient-header-info">
        <h2><?php echo htmlspecialchars(fmtName($patient['fname'] ?? '', $patient['lname'] ?? '')); ?></h2>
        <div class="patient-header-meta">
            <span><i class="fas fa-id-badge"></i> ID: <?php echo htmlspecialchars($patient['patient_id'] ?? $patient['id']); ?></span>
            <?php if (!empty($patient['age']) && $patient['age'] > 0): ?>
            <span><i class="fas fa-birthday-cake"></i> <?php echo $patient['age']; ?> yrs</span>
            <?php endif; ?>
            <span><i class="fas fa-venus-mars"></i> <?php echo fmtGender($patient['gender'] ?? ''); ?></span>
            <?php $contact = trim($patient['contact_no'] ?? ''); if ($contact !== ''): ?>
            <span><i class="fas fa-phone"></i> <a href="tel:<?php echo htmlspecialchars($contact); ?>"><?php echo htmlspecialchars($contact); ?></a></span>
            <?php endif; ?>
            <span><i class="fas fa-calendar-check"></i> Reg: <?php echo fmtDate($patient['dor'] ?? ''); ?></span>
            <span><i class="fas fa-file-medical"></i> <?php echo $totalReports; ?> report<?php echo $totalReports != 1 ? 's' : ''; ?></span>
        </div>
    </div>
    <div class="patient-header-actions">
        <a href="/patients" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<!-- ── COMPACT INFO CARD ── -->
<div class="card mb-16" id="infoCard">
    <div class="card-header" style="cursor:pointer; user-select:none;" onclick="toggleInfo()">
        <i class="fas fa-id-card"></i> Patient Information
        <span style="float:right; font-size:0.8rem; color: var(--gray-400);" id="infoToggleLabel">click to expand</span>
    </div>
    <div id="infoBody" style="display:none;">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Marital Status</div>
                <div class="info-value"><?php echo htmlspecialchars(fmtMaritalStatus($patient['mrg_status'] ?? '')); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Occupation</div>
                <div class="info-value editable-field" data-field="occupation" data-id="<?php echo $patientId; ?>">
                    <span class="field-text"><?php echo htmlspecialchars(fmt($patient['occupation'] ?? null)); ?></span>
                    <i class="fas fa-pen edit-icon"></i>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Education</div>
                <div class="info-value editable-field" data-field="education" data-id="<?php echo $patientId; ?>">
                    <span class="field-text"><?php echo htmlspecialchars(fmt($patient['education'] ?? null)); ?></span>
                    <i class="fas fa-pen edit-icon"></i>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Religion</div>
                <div class="info-value"><?php echo htmlspecialchars(fmt($patient['religion'] ?? null)); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Referred By</div>
                <div class="info-value editable-field" data-field="refered_by" data-id="<?php echo $patientId; ?>">
                    <span class="field-text"><?php echo htmlspecialchars(fmt($patient['refered_by'] ?? null)); ?></span>
                    <i class="fas fa-pen edit-icon"></i>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">DOB</div>
                <div class="info-value"><?php echo htmlspecialchars(fmtDate($patient['dob'] ?? '')); ?></div>
            </div>
            <div class="info-full">
                <div class="info-label">Address</div>
                <div class="info-value editable-field" data-field="address" data-id="<?php echo $patientId; ?>">
                    <span class="field-text"><?php echo htmlspecialchars(fmt($patient['address'] ?? null)); ?></span>
                    <i class="fas fa-pen edit-icon"></i>
                </div>
            </div>
            <div class="info-full">
                <div class="info-label">Chief Complaint</div>
                <div class="info-value editable-field" data-field="chief" data-id="<?php echo $patientId; ?>" style="font-weight: 400; white-space: pre-line;">
                    <span class="field-text"><?php echo htmlspecialchars(fmt($patient['chief'] ?? null)); ?></span>
                    <i class="fas fa-pen edit-icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── MAIN WORKSPACE: Add Report LEFT | History RIGHT ── -->
<div class="workspace">

    <!-- LEFT: Add New Report -->
    <div class="card report-form-card">
        <div class="card-header">
            <i class="fas fa-plus-circle"></i> Add Today's Report
        </div>
        <div class="card-body" style="padding: 16px;">
            <div style="margin-bottom: 12px;">
                <label class="info-label" style="display:block; margin-bottom:6px;">Date</label>
                <input type="date" id="reportDate" class="report-input date-input"
                    value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div style="margin-bottom: 12px;">
                <label class="info-label" style="display:block; margin-bottom:6px;">Medicines / Notes</label>
                <textarea id="reportMedicins" class="report-input" placeholder="e.g. PULS-200, S.L, follow-up..." rows="4"></textarea>
            </div>
            <div style="margin-bottom: 12px;">
                <label class="info-label" style="display:block; margin-bottom:6px;">Amount (₹)</label>
                <input type="number" id="reportAmt" class="report-input amount-input" placeholder="0" min="0">
            </div>
            <button class="save-btn" id="saveReportBtn" onclick="saveReport(<?php echo $patientId; ?>)">
                <i class="fas fa-save"></i> Save Report
            </button>
            <div class="save-success" id="saveSuccess">
                <i class="fas fa-check-circle"></i> Report saved successfully!
            </div>
        </div>
    </div>

    <!-- RIGHT: History Panel -->
    <div class="history-panel">
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <span><i class="fas fa-history"></i> Visit History</span>
                <span style="font-size:0.8rem; color: var(--gray-400);" id="reportCountBadge">
                    <?php echo $totalReports; ?> total
                </span>
            </div>
            <div class="history-list" id="historyList">
                <?php if (!empty($reports)): ?>
                    <?php foreach ($reports as $index => $report): ?>
                    <div class="history-item <?php echo $index === 0 ? 'latest' : ''; ?>" id="report-<?php echo $report['id']; ?>">
                        <div class="history-report-num">#<?php echo htmlspecialchars($report['id']); ?></div>
                        <div class="history-date">
                            <i class="fas fa-calendar-day"></i>
                            <?php echo htmlspecialchars(fmtDate($report['date'] ?? '')); ?>
                        </div>
                        <div class="history-meds"><?php echo htmlspecialchars(fmt($report['medicins'] ?? null, '—')); ?></div>
                        <?php if (!empty($report['amt']) && $report['amt'] > 0): ?>
                        <div class="history-amt">₹<?php echo htmlspecialchars($report['amt']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:40px 20px; color:var(--gray-400);" id="noReportsMsg">
                        <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                        No visits yet
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- end workspace -->

<script>
// ── Toggle info panel ──
function toggleInfo() {
    const body = document.getElementById('infoBody');
    const label = document.getElementById('infoToggleLabel');
    const isHidden = body.style.display === 'none';
    body.style.display = isHidden ? 'block' : 'none';
    label.textContent = isHidden ? 'click to collapse' : 'click to expand';
}

// ── Save Report ──
function saveReport(patientId) {
    const date = document.getElementById('reportDate').value;
    const medicins = document.getElementById('reportMedicins').value.trim();
    const amt = document.getElementById('reportAmt').value || 0;
    const btn = document.getElementById('saveReportBtn');
    const successMsg = document.getElementById('saveSuccess');

    if (!medicins) {
        document.getElementById('reportMedicins').focus();
        document.getElementById('reportMedicins').style.borderColor = '#ef4444';
        setTimeout(() => document.getElementById('reportMedicins').style.borderColor = '', 2000);
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const formData = new FormData();
    formData.append('date', date);
    formData.append('medicins', medicins);
    formData.append('amt', amt);

    fetch(`/api/patient/${patientId}/report`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Prepend new report to history list
            const historyList = document.getElementById('historyList');
            const noMsg = document.getElementById('noReportsMsg');
            if (noMsg) noMsg.remove();

            // Remove 'latest' from previous first item
            const prevLatest = historyList.querySelector('.latest');
            if (prevLatest) prevLatest.classList.remove('latest');

            const amtHtml = amt > 0 ? `<div class="history-amt">₹${amt}</div>` : '';
            const formattedDate = new Date(date).toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'numeric'});
            const newItem = document.createElement('div');
            newItem.className = 'history-item latest';
            newItem.id = 'report-' + (data.report_id || '');
            newItem.innerHTML = `
                <div class="history-report-num">#${data.report_id || ''}</div>
                <div class="history-date"><i class="fas fa-calendar-day"></i> ${formattedDate}</div>
                <div class="history-meds">${escHtml(medicins)}</div>
                ${amtHtml}
            `;
            historyList.prepend(newItem);

            // Update count
            const badge = document.getElementById('reportCountBadge');
            const cur = parseInt(badge.textContent) || 0;
            badge.textContent = (cur + 1) + ' total';

            // Clear form
            document.getElementById('reportMedicins').value = '';
            document.getElementById('reportAmt').value = '';
            document.getElementById('reportDate').value = new Date().toISOString().split('T')[0];

            // Show success
            successMsg.style.display = 'block';
            setTimeout(() => { successMsg.style.display = 'none'; }, 3000);
        } else {
            alert('Error: ' + (data.message || 'Could not save report'));
        }
    })
    .catch(err => {
        alert('Network error. Please try again.');
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Report';
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// ── Inline Edit ──
document.querySelectorAll('.editable-field').forEach(el => {
    el.addEventListener('click', function() {
        if (this.querySelector('input, textarea')) return; // already editing

        const field = this.dataset.field;
        const patientId = this.dataset.id;
        const textSpan = this.querySelector('.field-text');
        const currentVal = textSpan.textContent === 'N/A' ? '' : textSpan.textContent;
        const isMultiline = field === 'chief' || field === 'address';

        const input = document.createElement(isMultiline ? 'textarea' : 'input');
        input.className = 'inline-edit-input';
        input.value = currentVal;
        if (isMultiline) { input.rows = 3; input.style.display = 'block'; input.style.width = '100%'; }

        this.innerHTML = '';
        this.appendChild(input);
        input.focus();

        const save = () => {
            const newVal = input.value.trim();
            const formData = new FormData();
            formData.append(field, newVal);

            fetch(`/api/patient/${patientId}/update`, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = `<span class="field-text">${escHtml(newVal || 'N/A')}</span><i class="fas fa-pen edit-icon"></i>`;
                } else {
                    this.innerHTML = `<span class="field-text">${escHtml(currentVal || 'N/A')}</span><i class="fas fa-pen edit-icon"></i>`;
                    alert('Save failed: ' + (data.message || ''));
                }
            })
            .catch(() => {
                this.innerHTML = `<span class="field-text">${escHtml(currentVal || 'N/A')}</span><i class="fas fa-pen edit-icon"></i>`;
            });
        };

        input.addEventListener('blur', save);
        input.addEventListener('keydown', e => {
            if (!isMultiline && e.key === 'Enter') { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') {
                this.innerHTML = `<span class="field-text">${escHtml(currentVal || 'N/A')}</span><i class="fas fa-pen edit-icon"></i>`;
            }
        });
    });
});
</script>

<?php else: ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($response['message'] ?? 'Patient not found'); ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
