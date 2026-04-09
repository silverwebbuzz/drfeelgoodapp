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
        body { background:#f0f4f8; font-family:'Segoe UI',system-ui,sans-serif; font-size:13px; margin:0; padding:16px 0 40px; }
        .booking-card { max-width:540px; margin:0 auto; background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(0,0,0,.10); overflow:hidden; }
        .booking-header { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; padding:20px 24px; }
        .booking-header h1 { font-size:18px; font-weight:700; margin:0 0 2px; }
        .booking-header p  { margin:0; opacity:.85; font-size:12px; }
        .booking-body { padding:20px 24px; }

        /* Progress bar */
        .progress-wrap { display:flex; align-items:center; gap:0; margin-bottom:20px; }
        .progress-step { display:flex; flex-direction:column; align-items:center; flex:1; position:relative; }
        .progress-step:not(:last-child)::after { content:''; position:absolute; top:12px; left:50%; width:100%; height:2px; background:#e5e7eb; z-index:0; }
        .progress-step.done::after { background:#2563eb; }
        .ps-circle { width:24px; height:24px; border-radius:50%; border:2px solid #e5e7eb; background:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:#9ca3af; z-index:1; position:relative; }
        .progress-step.done .ps-circle { background:#2563eb; border-color:#2563eb; color:#fff; }
        .progress-step.active .ps-circle { border-color:#2563eb; color:#2563eb; }
        .ps-label { font-size:10px; color:#9ca3af; margin-top:4px; }
        .progress-step.done .ps-label,
        .progress-step.active .ps-label { color:#2563eb; font-weight:600; }

        /* Steps */
        .step { display:none; }
        .step.active { display:block; }
        .step-title { font-size:15px; font-weight:700; margin:0 0 14px; color:#111; }

        /* Date selector */
        .date-scroll { display:flex; gap:8px; overflow-x:auto; padding-bottom:6px; scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch; }
        .date-scroll::-webkit-scrollbar { height:4px; }
        .date-scroll::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:2px; }
        .date-card { min-width:62px; border:2px solid #e5e7eb; border-radius:10px; padding:8px 4px; text-align:center; cursor:pointer; scroll-snap-align:start; transition:.15s; flex-shrink:0; }
        .date-card:hover { border-color:#93c5fd; background:#eff6ff; }
        .date-card.selected { border-color:#2563eb; background:#eff6ff; }
        .date-card .dc-day  { font-size:10px; color:#6b7280; text-transform:uppercase; font-weight:600; }
        .date-card .dc-num  { font-size:20px; font-weight:800; color:#111; line-height:1.1; }
        .date-card .dc-mon  { font-size:10px; color:#6b7280; }
        .date-card.selected .dc-day,
        .date-card.selected .dc-num,
        .date-card.selected .dc-mon { color:#2563eb; }

        /* Slot grid */
        .slot-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
        .slot-btn { padding:9px 4px; border:2px solid #e5e7eb; border-radius:8px; text-align:center; cursor:pointer; font-size:12px; font-weight:600; transition:.15s; background:#fff; color:#111; }
        .slot-btn:hover { border-color:#93c5fd; background:#eff6ff; }
        .slot-btn.selected { border-color:#2563eb; background:#eff6ff; color:#2563eb; }
        .session-label { font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; margin:14px 0 6px; }

        /* Forms */
        .form-label { font-size:12px; font-weight:600; color:#374151; margin-bottom:4px; display:block; }
        .form-control { font-size:13px; padding:8px 11px; border:2px solid #e5e7eb; border-radius:8px; width:100%; outline:none; transition:.15s; }
        .form-control:focus { border-color:#2563eb; }
        .mb-3 { margin-bottom:12px; }

        /* Buttons */
        .btn-main { background:#2563eb; border:none; border-radius:8px; padding:11px 20px; font-size:13px; font-weight:700; color:#fff; cursor:pointer; width:100%; transition:.15s; }
        .btn-main:hover { background:#1d4ed8; }
        .btn-main:disabled { background:#93c5fd; cursor:not-allowed; }
        .btn-back { background:#f3f4f6; border:none; border-radius:8px; padding:11px 18px; font-size:13px; color:#374151; cursor:pointer; }
        .btn-row { display:flex; gap:8px; margin-top:16px; }
        .btn-row .btn-main { flex:1; }

        /* Phone lookup */
        .found-box { background:#f0fdf4; border:2px solid #bbf7d0; border-radius:8px; padding:10px 14px; font-size:12px; margin-bottom:12px; }
        .found-box strong { color:#15803d; }

        /* Confirmation */
        .confirm-icon { text-align:center; font-size:52px; color:#16a34a; margin-bottom:8px; }
        .token-display { text-align:center; font-size:68px; font-weight:900; color:#2563eb; line-height:1; margin-bottom:4px; }
        .token-sub { text-align:center; font-size:11px; color:#6b7280; margin-bottom:16px; }
        .confirm-table { background:#f9fafb; border-radius:8px; overflow:hidden; margin-bottom:14px; }
        .confirm-row { display:flex; justify-content:space-between; padding:8px 14px; border-bottom:1px solid #f3f4f6; font-size:12px; }
        .confirm-row:last-child { border:none; }
        .confirm-row span { color:#6b7280; }
        .confirm-row strong { color:#111; }
        .notice { background:#fef3c7; border-radius:6px; padding:10px 12px; font-size:11px; color:#92400e; margin-bottom:14px; }

        #slotsLoading { text-align:center; padding:24px; color:#6b7280; font-size:12px; }
        #noSlots { text-align:center; padding:24px; color:#9ca3af; font-size:12px; display:none; }
    </style>
</head>
<body>
<div class="booking-card">
    <div class="booking-header">
        <h1><i class="fas fa-calendar-plus"></i> Book Appointment</h1>
        <p>Dr. Feelgood Clinic &mdash; Online Booking</p>
    </div>
    <div class="booking-body">

        <!-- Progress -->
        <div class="progress-wrap">
            <div class="progress-step done active" id="ps1">
                <div class="ps-circle">1</div>
                <div class="ps-label">Date</div>
            </div>
            <div class="progress-step" id="ps2">
                <div class="ps-circle">2</div>
                <div class="ps-label">Slot</div>
            </div>
            <div class="progress-step" id="ps3">
                <div class="ps-circle">3</div>
                <div class="ps-label">Details</div>
            </div>
            <div class="progress-step" id="ps4">
                <div class="ps-circle">4</div>
                <div class="ps-label">Done</div>
            </div>
        </div>

        <!-- STEP 1: Date -->
        <div class="step active" id="step1">
            <div class="step-title">Select Appointment Date</div>
            <div class="date-scroll" id="dateScroll"></div>
            <div id="step1Err" style="color:#dc2626;font-size:12px;margin-top:10px;display:none;"></div>
            <div class="btn-row" style="margin-top:16px;">
                <button class="btn-main" onclick="step1Next()">Next &rarr;</button>
            </div>
        </div>

        <!-- STEP 2: Slot -->
        <div class="step" id="step2">
            <div class="step-title">Choose a Time Slot</div>
            <div style="font-size:12px;color:#6b7280;margin-bottom:12px;">
                <i class="fas fa-calendar-day"></i> <span id="displayDate2"></span>
            </div>
            <div id="slotsLoading"><i class="fas fa-spinner fa-spin"></i> Loading slots...</div>
            <div id="noSlots">No available slots for this date.</div>
            <div id="slotsContainer"></div>
            <div id="step2Err" style="color:#dc2626;font-size:12px;margin-top:8px;display:none;"></div>
            <div class="btn-row">
                <button class="btn-back" onclick="goStep(1)">← Back</button>
                <button class="btn-main" onclick="step2Next()">Next &rarr;</button>
            </div>
        </div>

        <!-- STEP 3: Phone + Patient details -->
        <div class="step" id="step3">
            <div class="step-title">Your Details</div>

            <!-- Phone lookup -->
            <div class="mb-3">
                <label class="form-label">Mobile Number <span style="color:#dc2626;">*</span></label>
                <div style="display:flex;gap:8px;">
                    <input type="tel" id="phoneInput" class="form-control" placeholder="10-digit mobile" maxlength="15" style="flex:1;">
                    <button onclick="lookupPhone()" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:0 14px;font-size:12px;cursor:pointer;white-space:nowrap;">
                        <i class="fas fa-search"></i> Find
                    </button>
                </div>
                <div style="font-size:11px;color:#9ca3af;margin-top:4px;">We'll check if you're already registered.</div>
            </div>

            <div id="foundBox" class="found-box" style="display:none;">
                <i class="fas fa-user-check"></i> Found: <strong id="foundName"></strong>
                <div style="font-size:11px;color:#16a34a;margin-top:2px;">Your details are pre-filled below.</div>
            </div>

            <input type="hidden" id="hiddenPatientId">

            <div id="nameField" class="mb-3">
                <label class="form-label">Full Name <span style="color:#dc2626;">*</span></label>
                <input type="text" id="patientName" class="form-control" placeholder="First Last">
            </div>

            <div class="mb-3">
                <label class="form-label">Reason for Visit</label>
                <input type="text" id="chiefComplaint" class="form-control" placeholder="e.g. Fever, Back pain (optional)">
            </div>

            <div class="mb-3">
                <label class="form-label">Visit Type</label>
                <select id="isFollowup" class="form-control">
                    <option value="0">First / Regular Visit</option>
                    <option value="1">Follow-up</option>
                </select>
            </div>

            <div id="step3Err" style="color:#dc2626;font-size:12px;margin-bottom:8px;display:none;"></div>
            <div class="btn-row">
                <button class="btn-back" onclick="goStep(2)">← Back</button>
                <button class="btn-main" onclick="step3Next()">Confirm Booking &rarr;</button>
            </div>
        </div>

        <!-- STEP 4: Confirmation -->
        <div class="step" id="step4">
            <div class="confirm-icon"><i class="fas fa-check-circle"></i></div>
            <div class="token-display" id="confirmToken"></div>
            <div class="token-sub">Your Queue Token Number</div>
            <div class="confirm-table">
                <div class="confirm-row"><span>Patient</span><strong id="confName"></strong></div>
                <div class="confirm-row"><span>Date</span><strong id="confDate"></strong></div>
                <div class="confirm-row"><span>Time Slot</span><strong id="confTime"></strong></div>
                <div class="confirm-row"><span>Appointment ID</span><strong id="confId"></strong></div>
            </div>
            <div class="notice">
                <i class="fas fa-info-circle"></i> Please arrive 10 minutes before your slot and show this token number at reception.
            </div>
            <button class="btn-main" onclick="location.reload()">Book Another Appointment</button>
        </div>

    </div>
</div>

<script>
// ── IST helpers ──────────────────────────────────────────────────────────────
function getIST() {
    const now = new Date();
    return new Date(now.getTime() + now.getTimezoneOffset() * 60000 + 5.5 * 3600000);
}
function todayIST() {
    const d = getIST();
    return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
}
function nowTimeIST() {
    const d = getIST();
    return pad(d.getHours()) + ':' + pad(d.getMinutes());
}
function pad(n) { return String(n).padStart(2,'0'); }
function to12(t) {
    const [h,m] = t.split(':').map(Number);
    return (h%12||12) + ':' + pad(m) + ' ' + (h<12?'AM':'PM');
}
function fmtDateLong(ymd) {
    const [y,mo,d] = ymd.split('-');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const days   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const dt = new Date(y, mo-1, d);
    return days[dt.getDay()] + ', ' + d + ' ' + months[mo-1] + ' ' + y;
}

// ── State ────────────────────────────────────────────────────────────────────
let state = { date:'', slotTime:'', phone:'', patientId:'', patientName:'', chiefComplaint:'', isFollowup:'0' };

// ── Progress ─────────────────────────────────────────────────────────────────
function goStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step'+n).classList.add('active');
    for (let i=1; i<=4; i++) {
        const ps = document.getElementById('ps'+i);
        ps.classList.toggle('done',   i < n);
        ps.classList.toggle('active', i === n);
    }
    window.scrollTo(0,0);
}

// ── STEP 1: Build date cards (today + 13 days = 14 total) ────────────────────
(function buildDates() {
    const scroll = document.getElementById('dateScroll');
    const today  = todayIST();
    const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const monNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    for (let i = 0; i < 14; i++) {
        const ist = getIST();
        ist.setDate(ist.getDate() + i);
        const ymd  = ist.getFullYear()+'-'+pad(ist.getMonth()+1)+'-'+pad(ist.getDate());
        const card = document.createElement('div');
        card.className = 'date-card' + (i===0?' selected':'');
        card.dataset.date = ymd;
        card.innerHTML = `<div class="dc-day">${dayNames[ist.getDay()]}</div>
                          <div class="dc-num">${ist.getDate()}</div>
                          <div class="dc-mon">${monNames[ist.getMonth()]}</div>`;
        card.addEventListener('click', () => {
            document.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            state.date = ymd;
        });
        scroll.appendChild(card);
    }
    // Pre-select today
    state.date = today;
})();

function step1Next() {
    const err = document.getElementById('step1Err');
    if (!state.date) { err.textContent='Please select a date.'; err.style.display='block'; return; }
    err.style.display='none';
    document.getElementById('displayDate2').textContent = fmtDateLong(state.date);
    loadSlots(state.date);
    goStep(2);
}

// ── STEP 2: Slot picker ───────────────────────────────────────────────────────
function loadSlots(date) {
    const container = document.getElementById('slotsContainer');
    const loading   = document.getElementById('slotsLoading');
    const noSlots   = document.getElementById('noSlots');
    container.innerHTML = '';
    loading.style.display = 'block';
    noSlots.style.display = 'none';
    state.slotTime = '';

    fetch('/api/slots?date=' + encodeURIComponent(date))
    .then(r => r.json())
    .then(data => {
        loading.style.display = 'none';
        if (!data.success) { noSlots.style.display='block'; return; }

        const isToday  = (date === todayIST());
        const nowTime  = nowTimeIST();

        // Only show available (not full) and future slots
        const slots = data.slots.filter(s => {
            if (!s.available) return false;
            if (isToday && s.time <= nowTime) return false;
            return true;
        });

        if (!slots.length) { noSlots.style.display='block'; return; }

        const morning = slots.filter(s => s.time < '13:00');
        const evening = slots.filter(s => s.time >= '13:00');
        let html = '';
        if (morning.length) {
            html += '<div class="session-label"><i class="fas fa-sun"></i> Morning</div><div class="slot-grid">';
            morning.forEach(s => { html += `<div class="slot-btn" data-time="${s.time}" onclick="selectSlot(this,'${s.time}')">${to12(s.time)}</div>`; });
            html += '</div>';
        }
        if (evening.length) {
            html += '<div class="session-label"><i class="fas fa-moon"></i> Evening</div><div class="slot-grid">';
            evening.forEach(s => { html += `<div class="slot-btn" data-time="${s.time}" onclick="selectSlot(this,'${s.time}')">${to12(s.time)}</div>`; });
            html += '</div>';
        }
        container.innerHTML = html;
    })
    .catch(() => { loading.style.display='none'; noSlots.style.display='block'; });
}

function selectSlot(el, time) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    state.slotTime = time;
}

function step2Next() {
    const err = document.getElementById('step2Err');
    if (!state.slotTime) { err.textContent='Please select a time slot.'; err.style.display='block'; return; }
    err.style.display='none';
    goStep(3);
}

// ── STEP 3: Phone lookup + patient details ────────────────────────────────────
function lookupPhone() {
    const phone = document.getElementById('phoneInput').value.trim();
    const err   = document.getElementById('step3Err');
    if (phone.length < 8) { err.textContent='Enter a valid phone number first.'; err.style.display='block'; return; }
    err.style.display='none';
    state.phone = phone;

    fetch('/api/patient/lookup?phone=' + encodeURIComponent(phone))
    .then(r => r.json())
    .then(data => {
        if (data.success && data.found) {
            const p = data.patient;
            const name = ((p.fname||'') + ' ' + (p.lname||'')).trim();
            state.patientId   = p.id;
            state.patientName = name;
            document.getElementById('foundName').textContent = name;
            document.getElementById('foundBox').style.display = 'block';
            document.getElementById('hiddenPatientId').value = p.id;
            document.getElementById('patientName').value     = name;
            document.getElementById('nameField').style.opacity = '0.6';
        } else {
            state.patientId = '';
            document.getElementById('foundBox').style.display = 'none';
            document.getElementById('hiddenPatientId').value  = '';
            document.getElementById('nameField').style.opacity = '1';
        }
    });
}

// Also lookup on Enter key in phone field
document.getElementById('phoneInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); lookupPhone(); }
});

function step3Next() {
    const phone = document.getElementById('phoneInput').value.trim();
    const name  = document.getElementById('patientName').value.trim();
    const err   = document.getElementById('step3Err');
    if (phone.length < 8) { err.textContent='Please enter a valid mobile number.'; err.style.display='block'; return; }
    if (!name)             { err.textContent='Please enter patient name.'; err.style.display='block'; return; }
    err.style.display='none';

    state.phone          = phone;
    state.patientName    = name;
    state.patientId      = document.getElementById('hiddenPatientId').value;
    state.chiefComplaint = document.getElementById('chiefComplaint').value.trim();
    state.isFollowup     = document.getElementById('isFollowup').value;

    const body = new URLSearchParams({
        appt_date:        state.date,
        slot_time:        state.slotTime,
        patient_id:       state.patientId,
        patient_name:     state.patientName,
        patient_phone:    state.phone,
        chief_complaint:  state.chiefComplaint,
        is_followup:      state.isFollowup,
    });

    fetch('/api/booking', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('confirmToken').textContent = data.token;
            document.getElementById('confName').textContent     = state.patientName;
            document.getElementById('confDate').textContent     = fmtDateLong(data.appt_date);
            document.getElementById('confTime').textContent     = to12(data.slot_time);
            document.getElementById('confId').textContent       = '#' + data.id;
            goStep(4);
        } else {
            err.textContent = data.message || 'Booking failed. Please try again.';
            err.style.display = 'block';
        }
    })
    .catch(() => { err.textContent='Network error. Please try again.'; err.style.display='block'; });
}
</script>
</body>
</html>
