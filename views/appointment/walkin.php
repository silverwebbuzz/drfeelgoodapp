<?php
$page_title = 'Walk-in Token';
ob_start();
?>
<style>
.walkin-wrap { max-width:600px; margin:0 auto; }
.search-result-item { padding:7px 12px; cursor:pointer; border-bottom:1px solid #f3f4f6; font-size:12px; }
.search-result-item:hover { background:#f0f4ff; }
#patientResults { border:1px solid #d1d5db; border-radius:6px; background:#fff; max-height:200px; overflow-y:auto; display:none; }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h1 class="page-title" style="margin:0;">New Walk-in Token</h1>
    <a href="/queue" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Queue</a>
</div>

<div class="walkin-wrap">
<div class="card">
<div class="card-body">

<form id="walkinForm">
    <!-- Patient lookup -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Patient</label>
        <div style="display:flex;gap:8px;margin-bottom:6px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by name or phone..." autocomplete="off">
            <button type="button" class="btn btn-secondary btn-sm" onclick="clearPatient()">Clear</button>
        </div>
        <div id="patientResults"></div>
        <input type="hidden" name="patient_id" id="patientId">
        <div id="selectedPatient" style="display:none;padding:8px 12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;font-size:12px;margin-top:4px;"></div>
        <div style="font-size:11px;color:#6b7280;margin-top:4px;">Leave empty if patient is new / not registered</div>
    </div>

    <!-- Patient name (for non-registered) -->
    <div class="mb-3" id="nameRow">
        <label class="form-label">Patient Name <span style="color:#9ca3af;font-size:11px;">(if not found above)</span></label>
        <input type="text" name="patient_name" id="patientNameInput" class="form-control" placeholder="Full name">
    </div>
    <div class="mb-3" id="phoneRow">
        <label class="form-label">Phone</label>
        <input type="text" name="patient_phone" id="patientPhoneInput" class="form-control" placeholder="Contact number">
    </div>

    <div class="mb-3">
        <label class="form-label">Chief Complaint</label>
        <input type="text" name="chief_complaint" class="form-control" placeholder="Reason for visit">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;" class="mb-3">
        <div>
            <label class="form-label">Date</label>
            <input type="date" name="appt_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <label class="form-label">Follow-up?</label>
            <select name="is_followup" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>

    <div id="formMsg" style="display:none;padding:8px 12px;border-radius:6px;font-size:12px;margin-bottom:10px;"></div>

    <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-ticket-alt"></i> Generate Token</button>
        <a href="/queue" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<!-- Token display -->
<div id="tokenDisplay" style="display:none;text-align:center;padding:30px 0;">
    <div style="font-size:12px;color:#6b7280;margin-bottom:8px;">Token Number</div>
    <div id="tokenNum" style="font-size:72px;font-weight:800;color:var(--primary);line-height:1;"></div>
    <div id="tokenName" style="font-size:14px;color:#374151;margin-top:8px;"></div>
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:center;">
        <button class="btn btn-primary" onclick="resetForm()"><i class="fas fa-plus"></i> New Token</button>
        <a href="/queue" class="btn btn-secondary"><i class="fas fa-list"></i> View Queue</a>
    </div>
</div>

</div>
</div>
</div>

<script>
let searchTimeout;

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const q = this.value.trim();
    if (q.length < 2) { hideResults(); return; }
    searchTimeout = setTimeout(() => searchPatients(q), 300);
});

function searchPatients(q) {
    fetch('/api/patient/search?q=' + encodeURIComponent(q))
    .then(r => r.json())
    .then(data => {
        const el = document.getElementById('patientResults');
        if (!data.success || !data.data.length) { el.style.display='none'; return; }
        el.innerHTML = data.data.slice(0,8).map(p => {
            const name = ((p.fname||'') + ' ' + (p.lname||'')).trim() || 'Unknown';
            return `<div class="search-result-item" onclick="selectPatient(${p.id},'${name.replace(/'/g,"\\'")}','${(p.contact_no||'').replace(/'/g,"\\'")}')">
                <strong>${name}</strong> <span style="color:#6b7280;">${p.contact_no||''}</span>
                <span style="float:right;color:#9ca3af;">ID: ${p.patient_id||p.id}</span>
            </div>`;
        }).join('');
        el.style.display = 'block';
    });
}

function selectPatient(id, name, phone) {
    document.getElementById('patientId').value = id;
    document.getElementById('patientNameInput').value = name;
    document.getElementById('patientPhoneInput').value = phone;
    document.getElementById('selectedPatient').innerHTML = `<i class="fas fa-user-check" style="color:#16a34a;"></i> <strong>${name}</strong> &nbsp; ${phone}`;
    document.getElementById('selectedPatient').style.display = 'block';
    document.getElementById('searchInput').value = name;
    hideResults();
    document.getElementById('nameRow').style.opacity = '0.5';
    document.getElementById('phoneRow').style.opacity = '0.5';
}

function clearPatient() {
    document.getElementById('patientId').value = '';
    document.getElementById('patientNameInput').value = '';
    document.getElementById('patientPhoneInput').value = '';
    document.getElementById('selectedPatient').style.display = 'none';
    document.getElementById('searchInput').value = '';
    document.getElementById('nameRow').style.opacity = '1';
    document.getElementById('phoneRow').style.opacity = '1';
    hideResults();
}

function hideResults() {
    document.getElementById('patientResults').style.display = 'none';
}

document.addEventListener('click', e => {
    if (!e.target.closest('#patientResults') && !e.target.closest('#searchInput')) hideResults();
});

document.getElementById('walkinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('/api/appointment/walkin', { method:'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('walkinForm').style.display = 'none';
            document.getElementById('tokenNum').textContent = data.token;
            document.getElementById('tokenName').textContent = fd.get('patient_name') || 'Patient';
            document.getElementById('tokenDisplay').style.display = 'block';
        } else {
            showMsg(data.message || 'Error', 'danger');
        }
    });
});

function showMsg(msg, type) {
    const el = document.getElementById('formMsg');
    el.className = 'alert alert-' + type;
    el.textContent = msg;
    el.style.display = 'block';
}

function resetForm() {
    document.getElementById('walkinForm').reset();
    document.getElementById('walkinForm').style.display = 'block';
    document.getElementById('tokenDisplay').style.display = 'none';
    document.getElementById('formMsg').style.display = 'none';
    clearPatient();
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
