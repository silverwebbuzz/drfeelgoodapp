<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment – Dr. Feelgood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        body { background:#f0f4f8; font-family:'Segoe UI',system-ui,sans-serif; font-size:13px; }
        .booking-card { max-width:560px; margin:32px auto; background:#fff; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.08); overflow:hidden; }
        .booking-header { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; padding:24px 28px; }
        .booking-header h1 { font-size:20px; font-weight:700; margin:0 0 4px; }
        .booking-header p { margin:0; opacity:.85; font-size:13px; }
        .booking-body { padding:24px 28px; }
        .step { display:none; }
        .step.active { display:block; }
        .step-indicator { display:flex; gap:6px; margin-bottom:20px; }
        .step-dot { flex:1; height:4px; border-radius:2px; background:#e5e7eb; }
        .step-dot.done { background:#2563eb; }
        .slot-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:10px; }
        .slot-btn { padding:8px; border:1.5px solid #e5e7eb; border-radius:6px; text-align:center; cursor:pointer; font-size:12px; transition:.15s; background:#fff; }
        .slot-btn:hover:not(.full) { border-color:#2563eb; background:#eff6ff; }
        .slot-btn.selected { border-color:#2563eb; background:#eff6ff; font-weight:600; }
        .slot-btn.full { opacity:.4; cursor:not-allowed; }
        .slot-btn .time { font-weight:600; color:#111; }
        .slot-btn.full .time { text-decoration:line-through; }
        .form-label { font-size:12px; font-weight:600; color:#374151; margin-bottom:4px; }
        .form-control { font-size:12px; padding:7px 10px; border:1.5px solid #e5e7eb; border-radius:6px; }
        .form-control:focus { border-color:#2563eb; box-shadow:none; outline:none; }
        .btn-primary { background:#2563eb; border:none; border-radius:6px; padding:9px 20px; font-size:13px; font-weight:600; color:#fff; cursor:pointer; width:100%; }
        .btn-primary:hover { background:#1d4ed8; }
        .btn-secondary { background:#f3f4f6; border:none; border-radius:6px; padding:9px 20px; font-size:13px; color:#374151; cursor:pointer; }
        .found-box { background:#f0f9ff; border:1.5px solid #bae6fd; border-radius:8px; padding:12px; margin-bottom:14px; font-size:12px; }
        .success-icon { font-size:48px; color:#16a34a; text-align:center; margin-bottom:12px; }
        .token-big { font-size:64px; font-weight:800; color:#2563eb; text-align:center; line-height:1; }
        .info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6; font-size:12px; }
        .info-row:last-child { border:none; }
        #loadingSlots { text-align:center; padding:20px; color:#6b7280; font-size:12px; display:none; }
        .session-label { font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; margin:12px 0 6px; }
    </style>
</head>
<body>
<div class="booking-card">
    <div class="booking-header">
        <h1><i class="fas fa-calendar-plus"></i> Book Appointment</h1>
        <p>Dr. Feelgood Clinic &mdash; Online Booking</p>
    </div>
    <div class="booking-body">

        <!-- Step indicators -->
        <div class="step-indicator">
            <div class="step-dot done" id="dot1"></div>
            <div class="step-dot" id="dot2"></div>
            <div class="step-dot" id="dot3"></div>
            <div class="step-dot" id="dot4"></div>
        </div>

        <!-- Step 1: Date & Phone -->
        <div class="step active" id="step1">
            <h5 style="font-size:14px;margin:0 0 16px;font-weight:700;">Select Date & Enter Phone</h5>
            <div class="mb-3">
                <label class="form-label">Appointment Date</label>
                <input type="date" id="apptDate" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Your Phone Number</label>
                <input type="tel" id="phoneInput" class="form-control" placeholder="10-digit mobile number" maxlength="15">
            </div>
            <div id="step1Err" style="color:#dc2626;font-size:12px;margin-bottom:8px;display:none;"></div>
            <button class="btn-primary" onclick="step1Next()">Continue &rarr;</button>
        </div>

        <!-- Step 2: Patient info -->
        <div class="step" id="step2">
            <h5 style="font-size:14px;margin:0 0 16px;font-weight:700;">Patient Details</h5>
            <div id="foundBox" class="found-box" style="display:none;">
                <i class="fas fa-user-check" style="color:#0284c7;"></i>
                <strong id="foundName"></strong> &mdash; <span id="foundPhone"></span>
                <div style="margin-top:4px;color:#0284c7;font-size:11px;">Existing patient found. Using your saved details.</div>
            </div>
            <input type="hidden" id="hiddenPatientId">
            <div id="newPatientFields">
                <div class="mb-3">
                    <label class="form-label">Full Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="patientName" class="form-control" placeholder="First Last">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Chief Complaint</label>
                <input type="text" id="chiefComplaint" class="form-control" placeholder="Reason for visit (optional)">
            </div>
            <div class="mb-3">
                <label class="form-label">Is this a follow-up visit?</label>
                <select id="isFollowup" class="form-control">
                    <option value="0">No – First / Regular Visit</option>
                    <option value="1">Yes – Follow-up</option>
                </select>
            </div>
            <div id="step2Err" style="color:#dc2626;font-size:12px;margin-bottom:8px;display:none;"></div>
            <div style="display:flex;gap:8px;">
                <button class="btn-secondary" onclick="goStep(1)">← Back</button>
                <button class="btn-primary" onclick="step2Next()" style="flex:1;">Choose Slot &rarr;</button>
            </div>
        </div>

        <!-- Step 3: Slot picker -->
        <div class="step" id="step3">
            <h5 style="font-size:14px;margin:0 0 4px;font-weight:700;">Choose a Time Slot</h5>
            <div style="font-size:12px;color:#6b7280;margin-bottom:12px;">Date: <strong id="displayDate"></strong></div>
            <div id="loadingSlots"><i class="fas fa-spinner fa-spin"></i> Loading slots...</div>
            <div id="slotsContainer"></div>
            <div id="step3Err" style="color:#dc2626;font-size:12px;margin:8px 0;display:none;"></div>
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button class="btn-secondary" onclick="goStep(2)">← Back</button>
                <button class="btn-primary" onclick="step3Next()" style="flex:1;">Confirm Booking &rarr;</button>
            </div>
        </div>

        <!-- Step 4: Confirmation -->
        <div class="step" id="step4">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <div class="token-big" id="confirmToken"></div>
            <div style="text-align:center;font-size:11px;color:#6b7280;margin:4px 0 16px;">Your Queue Token</div>
            <div style="background:#f9fafb;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                <div class="info-row"><span>Patient</span><strong id="confName"></strong></div>
                <div class="info-row"><span>Date</span><strong id="confDate"></strong></div>
                <div class="info-row"><span>Time</span><strong id="confTime"></strong></div>
                <div class="info-row"><span>Appointment ID</span><strong id="confId"></strong></div>
            </div>
            <div style="background:#fef3c7;border-radius:6px;padding:10px 12px;font-size:11px;color:#92400e;margin-bottom:16px;">
                <i class="fas fa-info-circle"></i> Please arrive 10 minutes before your slot. Show this token number at reception.
            </div>
            <button class="btn-primary" onclick="location.reload()">Book Another Appointment</button>
        </div>

    </div>
</div>

<script>
let state = { date:'', phone:'', patientId:'', patientName:'', chiefComplaint:'', isFollowup:0, slotTime:'' };

function goStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step'+n).classList.add('active');
    for (let i=1;i<=4;i++) {
        document.getElementById('dot'+i).classList.toggle('done', i<=n);
    }
}

function step1Next() {
    const date = document.getElementById('apptDate').value;
    const phone = document.getElementById('phoneInput').value.trim();
    const err = document.getElementById('step1Err');
    if (!date) { err.textContent='Please select a date'; err.style.display='block'; return; }
    if (phone.length < 8) { err.textContent='Please enter a valid phone number'; err.style.display='block'; return; }
    err.style.display='none';
    state.date = date;
    state.phone = phone;

    // Lookup patient by phone
    fetch('/api/patient/lookup?phone=' + encodeURIComponent(phone))
    .then(r => r.json())
    .then(data => {
        if (data.success && data.found) {
            const p = data.patient;
            state.patientId = p.id;
            state.patientName = ((p.fname||'') + ' ' + (p.lname||'')).trim();
            document.getElementById('foundName').textContent = state.patientName;
            document.getElementById('foundPhone').textContent = p.contact_no;
            document.getElementById('foundBox').style.display = 'block';
            document.getElementById('newPatientFields').style.display = 'none';
            document.getElementById('hiddenPatientId').value = p.id;
            document.getElementById('patientName').value = state.patientName;
        } else {
            state.patientId = '';
            document.getElementById('foundBox').style.display = 'none';
            document.getElementById('newPatientFields').style.display = 'block';
            document.getElementById('hiddenPatientId').value = '';
        }
        goStep(2);
    });
}

function step2Next() {
    const name = document.getElementById('patientName').value.trim();
    const err = document.getElementById('step2Err');
    if (!name) { err.textContent='Please enter patient name'; err.style.display='block'; return; }
    err.style.display='none';
    state.patientName = name;
    state.chiefComplaint = document.getElementById('chiefComplaint').value.trim();
    state.isFollowup = document.getElementById('isFollowup').value;
    state.patientId = document.getElementById('hiddenPatientId').value;

    document.getElementById('displayDate').textContent = state.date;
    loadSlots(state.date);
    goStep(3);
}

function loadSlots(date) {
    const el = document.getElementById('slotsContainer');
    document.getElementById('loadingSlots').style.display='block';
    el.innerHTML='';
    fetch('/api/slots?date=' + encodeURIComponent(date))
    .then(r => r.json())
    .then(data => {
        document.getElementById('loadingSlots').style.display='none';
        if (!data.success || !data.slots.length) {
            el.innerHTML='<div style="color:#6b7280;font-size:12px;padding:20px;text-align:center;">No slots available for this date.</div>';
            return;
        }
        // Split morning (before 13:00) and evening
        const morning = data.slots.filter(s => s.time < '13:00');
        const evening = data.slots.filter(s => s.time >= '13:00');
        let html = '';
        if (morning.length) {
            html += '<div class="session-label">Morning</div><div class="slot-grid">';
            morning.forEach(s => html += slotBtn(s));
            html += '</div>';
        }
        if (evening.length) {
            html += '<div class="session-label">Evening</div><div class="slot-grid">';
            evening.forEach(s => html += slotBtn(s));
            html += '</div>';
        }
        el.innerHTML = html;
    });
}

function slotBtn(s) {
    const full = !s.available;
    const t12 = to12(s.time);
    return `<div class="slot-btn ${full?'full':''}" data-time="${s.time}" onclick="selectSlot(this,'${s.time}')">
        <div class="time">${t12}</div>
        ${full ? '<div style="font-size:10px;color:#9ca3af;">Full</div>' : ''}
    </div>`;
}

function to12(t) {
    const [h,m] = t.split(':').map(Number);
    const ampm = h<12 ? 'AM' : 'PM';
    const h12 = h%12||12;
    return h12+':'+(m<10?'0':'')+m+' '+ampm;
}

function selectSlot(el, time) {
    if (el.classList.contains('full')) return;
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    state.slotTime = time;
}

function step3Next() {
    const err = document.getElementById('step3Err');
    if (!state.slotTime) { err.textContent='Please select a time slot'; err.style.display='block'; return; }
    err.style.display='none';

    const body = new URLSearchParams({
        appt_date: state.date,
        slot_time: state.slotTime,
        patient_id: state.patientId,
        patient_name: state.patientName,
        patient_phone: state.phone,
        chief_complaint: state.chiefComplaint,
        is_followup: state.isFollowup,
    });
    fetch('/api/booking', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('confirmToken').textContent = data.token;
            document.getElementById('confName').textContent = state.patientName;
            document.getElementById('confDate').textContent = data.appt_date;
            document.getElementById('confTime').textContent = to12(data.slot_time);
            document.getElementById('confId').textContent = '#' + data.id;
            goStep(4);
        } else {
            err.textContent = data.message || 'Booking failed. Please try again.';
            err.style.display='block';
        }
    });
}
</script>
</body>
</html>
