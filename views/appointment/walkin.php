<?php
$page_title = 'Walk-in Token';
ob_start();
?>
<style>
.walkin-wrap { max-width:640px; margin:0 auto; }
.search-result-item { padding:7px 12px; cursor:pointer; border-bottom:1px solid #f3f4f6; font-size:12px; }
.search-result-item:hover { background:#f0f4ff; }
#patientResults { border:1px solid #d1d5db; border-radius:6px; background:#fff; max-height:200px; overflow-y:auto; display:none; position:relative; z-index:10; }

/* Slot grid */
.slot-section { margin-top:6px; }
.slot-session-lbl { font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; margin:10px 0 5px; display:flex; align-items:center; gap:5px; }
.slot-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:6px; }
.slot-pill { padding:7px 4px; border:2px solid #e5e7eb; border-radius:7px; text-align:center; cursor:pointer; font-size:11px; font-weight:600; color:#374151; background:#fff; transition:.15s; }
.slot-pill:hover { border-color:#93c5fd; background:#eff6ff; }
.slot-pill.selected { border-color:var(--primary); background:var(--primary); color:#fff; }
.slot-pill.full { opacity:.4; cursor:not-allowed; text-decoration:line-through; }
#slotLoading { font-size:12px; color:#9ca3af; padding:10px 0; }
#slotArea { min-height:40px; }
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
        <label class="form-label fw-semibold">Patient Search</label>
        <div style="display:flex;gap:8px;margin-bottom:6px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by name or phone..." autocomplete="off">
            <button type="button" class="btn btn-secondary btn-sm" onclick="clearPatient()">Clear</button>
        </div>
        <div id="patientResults"></div>
        <input type="hidden" name="patient_id" id="patientId">
        <div id="selectedPatient" style="display:none;padding:7px 12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;font-size:12px;margin-top:4px;"></div>
        <div style="font-size:11px;color:#6b7280;margin-top:4px;">Leave empty for new / unregistered patient</div>
    </div>

    <!-- Name + Phone for unregistered -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;" class="mb-3">
        <div id="nameRow">
            <label class="form-label">Patient Name</label>
            <input type="text" name="patient_name" id="patientNameInput" class="form-control" placeholder="Full name">
        </div>
        <div id="phoneRow">
            <label class="form-label">Phone</label>
            <input type="text" name="patient_phone" id="patientPhoneInput" class="form-control" placeholder="Contact number">
        </div>
    </div>

    <!-- Date + Follow-up -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;" class="mb-3">
        <div>
            <label class="form-label">Date</label>
            <input type="date" name="appt_date" id="apptDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <label class="form-label">Follow-up?</label>
            <select name="is_followup" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>

    <!-- Slot picker -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Time Slot <span style="font-weight:400;color:#9ca3af;font-size:11px;">(optional — assign a slot to avoid overlap)</span></label>
        <input type="hidden" name="slot_time" id="slotTimeInput">
        <div id="slotArea"><div id="slotLoading"><i class="fas fa-spinner fa-spin"></i> Loading slots…</div></div>
        <div style="font-size:11px;color:#6b7280;margin-top:4px;">
            Walk-ins without a slot join the queue after pre-booked patients for that time.
        </div>
    </div>

    <!-- Chief Complaint -->
    <div class="mb-3">
        <label class="form-label">Chief Complaint</label>
        <input type="text" name="chief_complaint" class="form-control" placeholder="Reason for visit">
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
    <div id="tokenSlot" style="font-size:13px;color:#6b7280;margin-top:4px;"></div>
    <div id="tokenName" style="font-size:14px;color:#374151;margin-top:4px;"></div>
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:center;">
        <button class="btn btn-primary" onclick="resetForm()"><i class="fas fa-plus"></i> New Token</button>
        <a href="/queue" class="btn btn-secondary"><i class="fas fa-list"></i> View Queue</a>
    </div>
</div>

</div>
</div>
</div>

<script>
// ── Patient search ────────────────────────────────────────────────────────────
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

// ── Slot picker ───────────────────────────────────────────────────────────────
function pad(n) { return String(n).padStart(2,'0'); }
function to12(t) {
    const [h,m] = t.split(':').map(Number);
    return (h%12||12) + ':' + pad(m) + ' ' + (h<12?'AM':'PM');
}

// Load slots on page load for today
loadSlots(document.getElementById('apptDate').value);

// Reload slots when date changes
document.getElementById('apptDate').addEventListener('change', function() {
    document.getElementById('slotTimeInput').value = ''; // reset selection
    loadSlots(this.value);
});

function loadSlots(date) {
    const area = document.getElementById('slotArea');
    area.innerHTML = '<div id="slotLoading"><i class="fas fa-spinner fa-spin"></i> Loading slots…</div>';
    document.getElementById('slotTimeInput').value = '';

    fetch('/api/slots?date=' + encodeURIComponent(date))
    .then(r => r.json())
    .then(data => renderSlots(data, date))
    .catch(() => {
        area.innerHTML = '<div style="color:#9ca3af;font-size:12px;">Could not load slots.</div>';
    });
}

function renderSlots(data, date) {
    const area = document.getElementById('slotArea');

    if (data.closed) {
        area.innerHTML = '<div style="color:#ef4444;font-size:12px;"><i class="fas fa-ban"></i> Clinic is closed on this date.</div>';
        return;
    }

    // Admin sees ALL slots (including full ones — they can override)
    // but marks full ones visually
    const slots = (data.slots || []);

    if (!slots.length) {
        area.innerHTML = '<div style="color:#9ca3af;font-size:12px;">No slots configured for this date.</div>';
        return;
    }

    const morning = slots.filter(s => s.time < '13:00');
    const evening = slots.filter(s => s.time >= '13:00');
    let html = '<div class="slot-section">';

    if (morning.length) {
        html += `<div class="slot-session-lbl"><i class="fas fa-sun" style="color:#f59e0b;"></i> Morning</div><div class="slot-grid">`;
        morning.forEach(s => { html += slotPill(s); });
        html += '</div>';
    }
    if (evening.length) {
        html += `<div class="slot-session-lbl"><i class="fas fa-moon" style="color:#6366f1;"></i> Evening</div><div class="slot-grid">`;
        evening.forEach(s => { html += slotPill(s); });
        html += '</div>';
    }
    html += '</div>';
    area.innerHTML = html;
}

function slotPill(s) {
    const fullCls = s.available ? '' : ' full';
    const fullTip = s.available ? '' : ` title="Slot full (${s.booked} booked)"`;
    return `<div class="slot-pill${fullCls}" data-time="${s.time}"${fullTip} onclick="selectSlot(this,'${s.time}')">${to12(s.time)}</div>`;
}

function selectSlot(el, time) {
    document.querySelectorAll('.slot-pill').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('slotTimeInput').value = time;
}

// ── Form submit ───────────────────────────────────────────────────────────────
document.getElementById('walkinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('/api/appointment/walkin', { method:'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('walkinForm').style.display = 'none';
            document.getElementById('tokenNum').textContent  = data.token;
            document.getElementById('tokenName').textContent = fd.get('patient_name') || 'Patient';
            const slot = fd.get('slot_time');
            document.getElementById('tokenSlot').textContent = slot ? 'Slot: ' + to12(slot) : 'Walk-in (no slot)';
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
    loadSlots(document.getElementById('apptDate').value);
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
