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
    if ($value === null || $value === '' || $value === '0000-00-00' || strpos((string)$value, '0000') === 0 || $value === '1970-01-01') return 'N/A';
    $ts = strtotime($value);
    return $ts ? date('d M Y', $ts) : 'N/A';
}
function fmtGender($g) {
    if ($g === 'M') return 'Male'; if ($g === 'F') return 'Female'; return 'N/A';
}
function fmtMrg($s) {
    return ['S'=>'Single','M'=>'Married','D'=>'Divorced','W'=>'Widowed'][$s] ?? 'N/A';
}
function fmtVeg($v) {
    return ['V'=>'Vegetarian','NV'=>'Non-Vegetarian','EV'=>'Eggetarian'][$v] ?? 'N/A';
}
function fmtName($f, $l) {
    $full = trim(trim($f??'').' '.trim($l??''));
    return $full==='' ? 'N/A' : $full;
}
?>

<?php if (isset($response) && $response['success']):
    $p = $response['patient'];
    $reports = $response['progress_reports'] ?? [];
    $totalReports = $response['total_reports'] ?? count($reports);
    $pid = $p['id'];
    // Define role/permission here so it's available throughout the whole view
    $viewerRole = $_SESSION['role'] ?? 'doctor';
    $canVisit   = in_array($viewerRole, ['doctor', 'asst_doctor']);
?>

<style>
/* ── Header ── */
.pt-header {
    display:flex; align-items:center; gap:12px; flex-wrap:wrap;
    padding:12px 16px; background:white; border-radius:8px;
    box-shadow:var(--shadow-sm); margin-bottom:14px;
}
.pt-avatar {
    width:50px; height:50px; border-radius:50%;
    background:var(--primary); color:white;
    display:flex; align-items:center; justify-content:center;
    font-size:1.3rem; font-weight:700; flex-shrink:0;
}
.pt-header-info h2 { margin:0; font-size:1.25rem; font-weight:700; color:var(--gray-900); }
.pt-meta { display:flex; gap:14px; flex-wrap:wrap; margin-top:3px; }
.pt-meta span { font-size:0.83rem; color:var(--gray-600); }
.pt-meta span i { margin-right:3px; color:var(--gray-400); }
.pt-meta a { color:var(--primary); text-decoration:none; }
.pt-header-actions { margin-left:auto; display:flex; gap:8px; flex-shrink:0; }

/* ── Info panel ── */
.info-panel-header {
    display:flex; align-items:center; justify-content:space-between;
    cursor:pointer; user-select:none;
    transition: background 0.15s;
    border-radius: 4px;
    margin: -2px -4px;
    padding: 2px 4px;
}
.info-panel-header:hover { background: var(--gray-50); }
.toggle-chevron {
    display:inline-flex; align-items:center; gap:5px;
    font-size:0.75rem; color:var(--gray-500);
    background: var(--gray-100);
    border: 1px solid var(--gray-200);
    border-radius: 5px;
    padding: 3px 10px;
    font-weight: 500;
    transition: all 0.15s;
    white-space: nowrap;
    pointer-events: none;
}
.info-panel-header:hover .toggle-chevron {
    background: var(--primary-light);
    border-color: var(--primary);
    color: var(--primary);
}
.toggle-chevron .chev {
    display:inline-block;
    transition: transform 0.25s;
    font-style: normal;
}
.info-card-open .toggle-chevron .chev { transform: rotate(180deg); }
.info-grid {
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:0;
    border-top:1px solid var(--gray-100);
}
@media(max-width:1100px){ .info-grid { grid-template-columns:repeat(3,1fr); } }
@media(max-width:760px){  .info-grid { grid-template-columns:repeat(2,1fr); } }
@media(max-width:480px){  .info-grid { grid-template-columns:1fr; } }

/* Section divider headers inside the grid */
.info-section {
    grid-column:1/-1;
    display:flex; align-items:center; gap:8px;
    padding:9px 14px;
    background:var(--gray-50, #f9fafb);
    border-bottom:1px solid var(--gray-100);
    font-size:0.7rem; font-weight:700; text-transform:uppercase;
    letter-spacing:0.6px; color:var(--gray-500);
}
.info-section i { color:var(--primary); font-size:0.8rem; }
.info-section:not(:first-child) { border-top:1px solid var(--gray-100); }

.info-item {
    padding:9px 14px;
    border-right:1px solid var(--gray-100);
    border-bottom:1px solid var(--gray-100);
    min-width:0;
}
.info-label {
    font-size:0.7rem; text-transform:uppercase;
    letter-spacing:0.4px; color:var(--gray-400); margin-bottom:3px;
}
.info-value {
    font-size:0.9rem; font-weight:600; color:var(--gray-800);
    word-break:break-word;
}
.info-value.normal { font-weight:400; }
.info-full {
    grid-column:1/-1; padding:9px 14px;
    border-bottom:1px solid var(--gray-100);
}
/* Span helpers for cleaner row arrangement */
.info-item.span-2 { grid-column:span 2; }
@media(max-width:480px){ .info-item.span-2 { grid-column:span 1; } }

/* View / Edit toggle */
.edit-btn-sm {
    font-size:0.75rem; padding:3px 10px; border-radius:4px;
    border:1px solid var(--gray-300); background:white;
    color:var(--gray-600); cursor:pointer; transition:all 0.15s;
}
.edit-btn-sm:hover { border-color:var(--primary); color:var(--primary); }
.edit-btn-sm.active { background:var(--primary); color:white; border-color:var(--primary); }

/* Edit inputs inside info grid */
.field-edit-input {
    width:100%; font-size:0.9rem; font-weight:600; color:var(--gray-800);
    border:1px solid var(--primary); border-radius:4px;
    padding:3px 7px; font-family:inherit; background:#f0f7ff;
    box-sizing:border-box;
}
.field-edit-input:focus { outline:none; box-shadow:0 0 0 2px rgba(37,99,235,0.15); }
.field-edit-select { appearance:auto; cursor:pointer; }
textarea.field-edit-input { resize:vertical; min-height:70px; font-weight:400; }
.info-save-bar {
    display:none; padding:10px 14px; background:#eff6ff;
    border-top:1px solid var(--gray-200);
    gap:8px; align-items:center;
}
.info-save-bar.visible { display:flex; }

/* ── Workspace ── */
.workspace {
    display:grid; grid-template-columns:1fr 340px;
    gap:14px; align-items:start;
}
@media(max-width:900px){ .workspace{grid-template-columns:1fr;} }
/* On mobile, history panel loses sticky — just flows naturally */
@media(max-width:900px){ .history-panel { position:static; } }
/* Visit form: stack Notes/Reports-Notes and the payment row on narrow screens */
@media(max-width:560px){
    .visit-notes-grid { grid-template-columns:1fr !important; }
    .visit-pay-grid   { grid-template-columns:1fr !important; }
}

/* ── Add report form ── */
.report-form-card .card-header { background:var(--primary); color:white; }
.r-input {
    width:100%; padding:9px 12px;
    border:1px solid var(--gray-300); border-radius:6px;
    font-size:0.93rem; font-family:inherit;
    transition:border-color 0.2s; box-sizing:border-box;
}
.r-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
textarea.r-input { resize:vertical; }
.save-btn {
    width:100%; padding:11px; font-size:0.95rem; font-weight:600;
    background:var(--primary); color:white; border:none;
    border-radius:6px; cursor:pointer; transition:background 0.2s;
}
.save-btn:hover { background:#1d4ed8; }
.save-btn:disabled { opacity:0.6; cursor:not-allowed; }
.save-ok {
    display:none; background:#dcfce7; color:#166534;
    padding:8px 12px; border-radius:6px; font-size:0.88rem; margin-top:8px;
}

/* ── History ── */
.history-panel { position:sticky; top:14px; }
.history-list { max-height:calc(100vh - 270px); overflow-y:auto; }
.h-item {
    padding:11px 14px; border-bottom:1px solid var(--gray-100);
    transition:background 0.15s; position:relative;
}
.h-item:last-child { border-bottom:none; }
.h-item:hover { background:var(--gray-50); }
.h-item.new-entry { background:#eff6ff; border-left:3px solid var(--primary); }
.h-date { font-size:0.78rem; font-weight:700; color:var(--primary); margin-bottom:3px; }
.h-meds { font-size:0.88rem; color:var(--gray-800); font-weight:500; margin-bottom:2px; }
.h-notes { font-size:0.82rem; color:var(--gray-500); font-style:italic; margin-bottom:2px; }
.h-notes i { color:var(--warning); margin-right:3px; }
.h-amt { font-size:0.8rem; color:var(--gray-500); }
.pay-badge { display:inline-block; font-size:0.65rem; font-weight:700; padding:1px 7px; border-radius:10px; margin-left:4px; text-transform:uppercase; letter-spacing:.3px; vertical-align:middle; }
.pay-badge.pay-cash      { background:#ecfdf5; color:#047857; }
.pay-badge.pay-online    { background:#eff6ff; color:#1d4ed8; }
.pay-badge.pay-paid      { background:#ecfdf5; color:#047857; }
.pay-badge.pay-remaining { background:#fef2f2; color:#b91c1c; }
.h-num { float:right; font-size:0.72rem; color:var(--gray-400); }
.h-action-btns {
    display:none; position:absolute; right:10px; bottom:10px;
    gap:5px;
}
.h-item:hover .h-action-btns { display:flex; }
.h-edit-btn {
    font-size:0.72rem; padding:2px 8px; border-radius:3px;
    border:1px solid var(--gray-300); background:white;
    color:var(--gray-500); cursor:pointer;
}
.h-inv-btn {
    font-size:0.72rem; padding:2px 8px; border-radius:3px;
    border:1px solid #d1fae5; background:#f0fdf4;
    color:#16a34a; cursor:pointer; text-decoration:none;
    display:inline-flex; align-items:center; gap:3px;
}
.h-edit-form {
    display:none; margin-top:8px; padding-top:8px;
    border-top:1px solid var(--gray-200);
}
.h-edit-form.open { display:block; }
.h-edit-row { display:grid; grid-template-columns:1fr 90px; gap:8px; margin-bottom:6px; }
@media(max-width:480px){ .h-edit-row { grid-template-columns:1fr; } }
.h-edit-input {
    width:100%; padding:5px 8px; font-size:0.85rem;
    border:1px solid var(--primary); border-radius:4px;
    font-family:inherit; box-sizing:border-box;
}
.h-edit-actions { display:flex; gap:6px; }
.h-save-btn {
    padding:4px 12px; font-size:0.82rem; border:none;
    border-radius:4px; background:var(--primary); color:white; cursor:pointer;
}
.h-cancel-btn {
    padding:4px 10px; font-size:0.82rem; border:1px solid var(--gray-300);
    border-radius:4px; background:white; color:var(--gray-600); cursor:pointer;
}

/* ── Medicine Tag Picker ── */
.med-tag {
    display:inline-flex; align-items:center; gap:5px;
    background:var(--primary); color:white;
    padding:3px 9px; border-radius:20px;
    font-size:11px; font-weight:500;
    animation: tagPop 0.15s ease;
}
@keyframes tagPop {
    from { transform:scale(0.8); opacity:0; }
    to   { transform:scale(1);   opacity:1; }
}
.med-tag-x {
    cursor:pointer; font-size:13px; line-height:1;
    opacity:0.75; margin-left:2px;
}
.med-tag-x:hover { opacity:1; }
.med-drop-item {
    padding:7px 12px; cursor:pointer; font-size:12px;
    display:flex; justify-content:space-between; align-items:center;
    border-bottom:1px solid var(--gray-100);
    transition:background 0.1s;
}
.med-drop-item:last-child { border-bottom:none; }
.med-drop-item:hover { background:var(--primary-light); color:var(--primary); }
.med-drop-item.selected { opacity:0.4; cursor:default; }
.med-drop-item .med-count {
    font-size:10px; color:var(--gray-400); flex-shrink:0; margin-left:8px;
}
.med-drop-item:hover .med-count { color:var(--primary); }
.med-drop-add {
    padding:7px 12px; cursor:pointer; font-size:12px;
    color:var(--primary); font-weight:600;
    display:flex; align-items:center; gap:6px;
    border-top:1px solid var(--gray-200);
}
.med-drop-add:hover { background:var(--primary-light); }
.med-drop-empty {
    padding:12px; text-align:center; font-size:11px; color:var(--gray-400);
}
</style>

<?php
$fromQueue = ($_GET['from'] ?? '') === 'queue';
$apptId    = (int)($_GET['appt'] ?? 0);
?>

<?php if ($fromQueue && $apptId): ?>
<div style="background:#eff6ff;border:2px solid #2563eb;border-radius:8px;padding:10px 16px;margin-bottom:12px;">
    <div style="font-size:12px;color:#1d4ed8;">
        <i class="fas fa-stethoscope"></i> <strong>In Consultation</strong> — Add visit notes below, then Save Visit to finish.
    </div>
</div>
<?php endif; ?>

<!-- ── HEADER ── -->
<div class="pt-header">
    <div class="pt-avatar"><?php echo strtoupper(substr($p['fname']??'P',0,1)); ?></div>
    <div class="pt-header-info">
        <h2><?php echo htmlspecialchars(fmtName($p['fname']??'',$p['lname']??'')); ?></h2>
        <div class="pt-meta">
            <span><i class="fas fa-id-badge"></i> ID: <?php echo htmlspecialchars($p['patient_id']??$p['id']); ?></span>
            <?php if(!empty($p['age'])&&$p['age']>0): ?>
            <span><i class="fas fa-birthday-cake"></i> <?php echo $p['age']; ?> yrs</span>
            <?php endif; ?>
            <span><i class="fas fa-venus-mars"></i> <?php echo fmtGender($p['gender']??''); ?></span>
            <?php $ct=trim($p['contact_no']??''); if($ct!==''): ?>
            <span><i class="fas fa-phone"></i> <a href="tel:<?php echo htmlspecialchars($ct); ?>"><?php echo htmlspecialchars($ct); ?></a></span>
            <?php endif; ?>
            <span><i class="fas fa-calendar-check"></i> Reg: <?php echo fmtDate($p['dor']??''); ?></span>
            <span><i class="fas fa-file-medical"></i> <?php echo $totalReports; ?> visit<?php echo $totalReports!=1?'s':''; ?></span>
        </div>
    </div>
    <div class="pt-header-actions">
        <a href="/patients" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        <?php if ($viewerRole === 'doctor'): ?>
        <button class="btn btn-danger btn-sm" id="deletePatientBtn"
                onclick="deletePatient(<?php echo (int)$p['id']; ?>, '<?php echo htmlspecialchars(addslashes(fmtName($p['fname']??'',$p['lname']??'')), ENT_QUOTES); ?>')">
            <i class="fas fa-trash"></i> Delete Patient
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ── PATIENT INFORMATION CARD ── -->
<div class="card mb-16" id="infoCard">
    <div class="card-header" style="cursor:pointer;" onclick="toggleInfo()">
        <div class="info-panel-header">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="fas fa-id-card" style="color:var(--primary);"></i>
                <strong>Patient Information</strong>
            </span>
            <div style="display:flex;gap:8px;align-items:center;">
                <?php if ($canVisit): ?>
                <button class="edit-btn-sm" id="infoEditBtn" onclick="event.stopPropagation();toggleInfoEdit()">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <?php endif; ?>
                <span class="toggle-chevron" id="infoToggleHint">
                    <i class="chev fas fa-chevron-down"></i>
                    <span id="infoToggleLabel">Show</span>
                </span>
            </div>
        </div>
    </div>
    <div id="infoBody" style="display:none;">
        <div class="info-grid" id="infoGrid">

            <!-- ══ CONTACT & DEMOGRAPHICS ══ -->
            <div class="info-section"><i class="fas fa-user"></i> Contact &amp; Demographics</div>

            <div class="info-item">
                <div class="info-label">Contact No.</div>
                <div class="info-value" id="disp_contact_no">
                    <?php $ct=trim($p['contact_no']??''); echo $ct!=='' ? '<a href="tel:'.htmlspecialchars($ct).'">'.htmlspecialchars($ct).'</a>' : 'N/A'; ?>
                </div>
                <input type="text" class="field-edit-input edit-mode" name="contact_no"
                    value="<?php echo htmlspecialchars(trim($p['contact_no']??'')); ?>" style="display:none;">
            </div>
            <div class="info-item">
                <div class="info-label">Age</div>
                <div class="info-value" id="disp_age">
                    <?php echo (!empty($p['age'])&&$p['age']>0) ? htmlspecialchars($p['age']).' yrs' : 'N/A'; ?>
                </div>
                <input type="number" class="field-edit-input edit-mode" name="age"
                    value="<?php echo htmlspecialchars($p['age']??''); ?>" min="0" max="150" style="display:none;">
            </div>
            <div class="info-item">
                <div class="info-label">DOB</div>
                <div class="info-value" id="disp_dob"><?php echo fmtDate($p['dob']??''); ?></div>
                <input type="date" class="field-edit-input edit-mode" name="dob"
                    value="<?php
                        $dv = $p['dob']??'';
                        echo ($dv&&$dv!=='0000-00-00'&&$dv!=='1970-01-01') ? htmlspecialchars($dv) : '';
                    ?>" style="display:none;">
            </div>
            <div class="info-item">
                <div class="info-label">Gender</div>
                <div class="info-value" id="disp_gender"><?php echo fmtGender($p['gender']??''); ?></div>
                <select class="field-edit-input field-edit-select edit-mode" name="gender" style="display:none;">
                    <option value="">-- Select --</option>
                    <option value="M" <?php echo ($p['gender']??'')==='M'?'selected':''; ?>>Male</option>
                    <option value="F" <?php echo ($p['gender']??'')==='F'?'selected':''; ?>>Female</option>
                </select>
            </div>
            <div class="info-item">
                <div class="info-label">Marital Status</div>
                <div class="info-value" id="disp_mrg_status"><?php echo fmtMrg($p['mrg_status']??''); ?></div>
                <select class="field-edit-input field-edit-select edit-mode" name="mrg_status" style="display:none;">
                    <option value="">-- Select --</option>
                    <option value="S" <?php echo ($p['mrg_status']??'')==='S'?'selected':''; ?>>Single</option>
                    <option value="M" <?php echo ($p['mrg_status']??'')==='M'?'selected':''; ?>>Married</option>
                    <option value="D" <?php echo ($p['mrg_status']??'')==='D'?'selected':''; ?>>Divorced</option>
                    <option value="W" <?php echo ($p['mrg_status']??'')==='W'?'selected':''; ?>>Widowed</option>
                </select>
            </div>
            <div class="info-item">
                <div class="info-label">Diet</div>
                <div class="info-value" id="disp_veg"><?php echo fmtVeg($p['veg']??''); ?></div>
                <select class="field-edit-input field-edit-select edit-mode" name="veg" style="display:none;">
                    <option value="">-- Select --</option>
                    <option value="V" <?php echo ($p['veg']??'')==='V'?'selected':''; ?>>Vegetarian</option>
                    <option value="NV" <?php echo ($p['veg']??'')==='NV'?'selected':''; ?>>Non-Vegetarian</option>
                    <option value="EV" <?php echo ($p['veg']??'')==='EV'?'selected':''; ?>>Eggetarian</option>
                </select>
            </div>
            <div class="info-item">
                <div class="info-label">Religion</div>
                <div class="info-value" id="disp_religion"><?php echo htmlspecialchars(fmt($p['religion']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="religion"
                    value="<?php echo htmlspecialchars(trim($p['religion']??'')); ?>" style="display:none;">
            </div>
            <div class="info-item">
                <div class="info-label">Referred By</div>
                <div class="info-value" id="disp_refered_by"><?php echo htmlspecialchars(fmt($p['refered_by']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="refered_by"
                    value="<?php echo htmlspecialchars(trim($p['refered_by']??'')); ?>" style="display:none;">
            </div>

            <!-- ══ PROFESSION ══ -->
            <div class="info-section"><i class="fas fa-briefcase"></i> Profession</div>

            <div class="info-item span-2">
                <div class="info-label">Occupation</div>
                <div class="info-value" id="disp_occupation"><?php echo htmlspecialchars(fmt($p['occupation']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="occupation"
                    value="<?php echo htmlspecialchars(trim($p['occupation']??'')); ?>" style="display:none;">
            </div>
            <div class="info-item span-2">
                <div class="info-label">Education</div>
                <div class="info-value" id="disp_education"><?php echo htmlspecialchars(fmt($p['education']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="education"
                    value="<?php echo htmlspecialchars(trim($p['education']??'')); ?>" style="display:none;">
            </div>

            <!-- ══ ADDRESS ══ -->
            <div class="info-section"><i class="fas fa-map-marker-alt"></i> Address</div>

            <div class="info-full">
                <div class="info-label">Street / Area</div>
                <div class="info-value normal" id="disp_address"><?php echo htmlspecialchars(fmt($p['address']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="address"
                    value="<?php echo htmlspecialchars(trim($p['address']??'')); ?>" style="display:none;">
            </div>
            <div class="info-item">
                <div class="info-label">City</div>
                <div class="info-value normal" id="disp_city"><?php echo htmlspecialchars(fmt($p['city']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="city"
                    value="<?php echo htmlspecialchars(trim($p['city']??'')); ?>" style="display:none;">
            </div>
            <div class="info-item">
                <div class="info-label">State</div>
                <div class="info-value normal" id="disp_state"><?php echo htmlspecialchars(fmt($p['state']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="state"
                    value="<?php echo htmlspecialchars(trim($p['state']??'')); ?>" style="display:none;">
            </div>
            <div class="info-item">
                <div class="info-label">ZIP Code</div>
                <div class="info-value normal" id="disp_zip"><?php echo htmlspecialchars(fmt($p['zip']??null)); ?></div>
                <input type="text" class="field-edit-input edit-mode" name="zip"
                    value="<?php echo htmlspecialchars(trim($p['zip']??'')); ?>" style="display:none;">
            </div>

            <!-- ══ CLINICAL ══ -->
            <div class="info-section"><i class="fas fa-notes-medical"></i> Clinical</div>

            <div class="info-full">
                <div class="info-label">Chief Complaint / Case Notes</div>
                <div class="info-value normal" id="disp_chief" style="white-space:pre-line;"><?php echo htmlspecialchars(fmt($p['chief']??null)); ?></div>
                <textarea class="field-edit-input edit-mode" name="chief" rows="5" style="display:none;"><?php echo htmlspecialchars(trim($p['chief']??'')); ?></textarea>
            </div>

        </div><!-- /info-grid -->

        <!-- Save bar (visible only in edit mode) -->
        <div class="info-save-bar" id="infoSaveBar">
            <button class="save-btn" style="width:auto;padding:7px 24px;" onclick="saveInfo(<?php echo $pid; ?>)">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <button class="edit-btn-sm" onclick="cancelInfoEdit()">Cancel</button>
            <span id="infoSaveMsg" style="font-size:0.85rem;color:#166534;display:none;">
                <i class="fas fa-check-circle"></i> Saved!
            </span>
        </div>
    </div><!-- /infoBody -->
</div>

<!-- ── WORKSPACE ── -->
<div class="workspace" style="<?php echo $canVisit ? '' : 'grid-template-columns:1fr;'; ?>">

    <!-- LEFT: Add Report (doctor + asst_doctor only) -->
    <?php if ($canVisit): ?>
    <div class="card report-form-card">
        <div class="card-header">
            <i class="fas fa-plus-circle"></i> Add Today's Visit
        </div>
        <div class="card-body" style="padding:14px;">

            <!-- Date row -->
            <div style="margin-bottom:10px;">
                <label class="info-label" style="display:block;margin-bottom:4px;">Date</label>
                <input type="date" id="reportDate" class="r-input"
                    value="<?php echo date('Y-m-d'); ?>" style="height:34px;">
            </div>

            <!-- Medicine tag picker -->
            <div style="margin-bottom:12px;">
                <label class="info-label" style="display:block;margin-bottom:4px;">
                    Medicines
                    <span style="font-weight:400;color:var(--gray-400);margin-left:6px;">— search or type, press Enter to add</span>
                </label>

                <!-- Tag input area -->
                <div id="medPickerWrap" style="position:relative;">
                    <div id="tagInputArea" style="display:flex;align-items:center;gap:5px;border:1.5px solid var(--gray-300);border-radius:6px;padding:5px 8px;background:#fff;flex-wrap:wrap;cursor:text;min-height:38px;" onclick="document.getElementById('medSearch').focus()">
                        <input type="text" id="medSearch"
                            placeholder="Type medicine name..."
                            autocomplete="off"
                            style="border:none;outline:none;font-size:13px;min-width:140px;flex:1;padding:2px 0;background:transparent;">
                    </div>
                    <!-- Dropdown -->
                    <div id="medDropdown" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:500;background:#fff;border:1.5px solid var(--gray-300);border-radius:6px;max-height:200px;overflow-y:auto;box-shadow:0 4px 16px rgba(0,0,0,.12);"></div>
                </div>

                <!-- Selected tags below the input -->
                <div id="selectedTags" style="display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;min-height:0;"></div>
            </div>

            <!-- Notes + Reports Notes side by side (50 / 50) -->
            <div class="visit-notes-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                <div>
                    <label class="info-label" style="display:block;margin-bottom:4px;">
                        Notes
                        <span style="font-weight:400;color:var(--gray-400);">— follow-up, observations</span>
                    </label>
                    <textarea id="reportNotes" class="r-input" placeholder="e.g. Follow-up in 2 weeks, improvement noted..." rows="3"></textarea>
                </div>
                <div>
                    <label class="info-label" style="display:block;margin-bottom:4px;">
                        Reports - Notes
                        <span style="font-weight:400;color:var(--gray-400);">— lab / investigation findings</span>
                    </label>
                    <textarea id="reportReportsNotes" class="r-input" placeholder="e.g. CBC normal, Vit-D low, X-ray clear..." rows="3"></textarea>
                </div>
            </div>

            <!-- Amount + Payment (bottom, grouped) -->
            <div style="background:var(--gray-50);border:1px solid var(--gray-200);border-radius:8px;padding:12px;margin-bottom:14px;">
                <div class="info-label" style="margin-bottom:8px;color:var(--gray-500);font-weight:700;">
                    <i class="fas fa-rupee-sign" style="color:var(--primary);"></i> Payment
                </div>
                <div class="visit-pay-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                    <div>
                        <label class="info-label" style="display:block;margin-bottom:4px;">Amount (₹)</label>
                        <input type="number" id="reportAmt" class="r-input" placeholder="0" min="0" style="height:34px;">
                    </div>
                    <div>
                        <label class="info-label" style="display:block;margin-bottom:4px;">Payment Type</label>
                        <select id="reportPaymentType" class="r-input" style="height:34px;">
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div>
                        <label class="info-label" style="display:block;margin-bottom:4px;">Payment Status</label>
                        <select id="reportPaymentStatus" class="r-input" style="height:34px;">
                            <option value="paid">Paid</option>
                            <option value="remaining">Remaining</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Hidden textarea for medicines only (submitted separately) -->
            <textarea id="reportMedicins" style="display:none;"></textarea>

            <button class="save-btn" id="saveReportBtn" onclick="saveReport(<?php echo $pid; ?>)">
                <i class="fas fa-save"></i> Save Visit
            </button>
            <div class="save-ok" id="saveOk">
                <i class="fas fa-check-circle"></i> Visit saved!
            </div>
            <?php if ($fromQueue && $apptId): ?>
            <button class="btn btn-success" id="finishConsultBtn" style="display:none;width:100%;margin-top:10px;padding:11px;font-size:15px;font-weight:600;"
                    onclick="finishConsult(<?php echo $apptId; ?>)">
                <i class="fas fa-check"></i> Finish &amp; Back to Queue
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; // canVisit ?>

    <!-- RIGHT: History -->
    <div class="history-panel">
        <div class="card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
                <span><i class="fas fa-history"></i> Visit History</span>
                <span style="font-size:0.8rem;color:var(--gray-400);" id="visitBadge"><?php echo $totalReports; ?> total</span>
            </div>
            <div class="history-list" id="historyList">
                <?php if(!empty($reports)): ?>
                    <?php foreach($reports as $idx => $r): ?>
                    <div class="h-item <?php echo $idx===0?'new-entry':''; ?>" id="hitem-<?php echo $r['id']; ?>">
                        <div class="h-date"><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars(fmtDate($r['date']??'')); ?></div>
                        <?php if ($canVisit): ?>
                        <div class="h-num">#<?php echo htmlspecialchars($r['id']); ?></div>
                        <div class="h-meds"><?php echo htmlspecialchars(fmt($r['medicins']??null,'—')); ?></div>
                        <?php if(!empty(trim($r['notes']??''))): ?>
                        <div class="h-notes"><i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($r['notes']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty(trim($r['reports_notes']??''))): ?>
                        <div class="h-notes"><i class="fas fa-flask"></i> <?php echo htmlspecialchars($r['reports_notes']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($r['amt'])&&$r['amt']>0): ?>
                        <div class="h-amt">₹<?php echo htmlspecialchars($r['amt']); ?>
                            <?php
                                $pt = $r['payment_type']   ?? 'cash';
                                $ps = $r['payment_status'] ?? 'paid';
                            ?>
                            <span class="pay-badge pay-<?php echo htmlspecialchars($pt); ?>"><?php echo $pt==='online'?'Online':'Cash'; ?></span>
                            <span class="pay-badge pay-<?php echo htmlspecialchars($ps); ?>"><?php echo $ps==='remaining'?'Due':'Paid'; ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="h-action-btns">
                            <a href="/invoice/<?php echo $r['id']; ?>" target="_blank" class="h-inv-btn">
                                <i class="fas fa-file-invoice"></i> Invoice
                            </a>
                            <button class="h-edit-btn" onclick="toggleHistEdit(<?php echo $r['id']; ?>)">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                        </div>
                        <div class="h-edit-form" id="hedit-<?php echo $r['id']; ?>">
                            <div class="h-edit-row">
                                <input type="date" class="h-edit-input" id="he-date-<?php echo $r['id']; ?>"
                                    value="<?php
                                        $rd=$r['date']??'';
                                        echo ($rd&&$rd!=='0000-00-00')?htmlspecialchars($rd):'';
                                    ?>">
                                <input type="number" class="h-edit-input" id="he-amt-<?php echo $r['id']; ?>"
                                    placeholder="₹ Amount"
                                    value="<?php echo htmlspecialchars($r['amt']??0); ?>">
                            </div>
                            <textarea class="h-edit-input" id="he-meds-<?php echo $r['id']; ?>"
                                rows="2" placeholder="Medicines..." style="margin-bottom:5px;"><?php echo htmlspecialchars($r['medicins']??''); ?></textarea>
                            <textarea class="h-edit-input" id="he-notes-<?php echo $r['id']; ?>"
                                rows="2" placeholder="Notes (optional)..." style="margin-bottom:5px;"><?php echo htmlspecialchars($r['notes']??''); ?></textarea>
                            <textarea class="h-edit-input" id="he-repnotes-<?php echo $r['id']; ?>"
                                rows="2" placeholder="Reports - Notes (optional)..." style="margin-bottom:6px;"><?php echo htmlspecialchars($r['reports_notes']??''); ?></textarea>
                            <div class="h-edit-actions">
                                <button class="h-save-btn" onclick="saveHistEdit(<?php echo $r['id']; ?>)">
                                    <i class="fas fa-save"></i> Save
                                </button>
                                <button class="h-cancel-btn" onclick="toggleHistEdit(<?php echo $r['id']; ?>)">Cancel</button>
                            </div>
                        </div>
                        <?php endif; // canVisit ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center;padding:40px 20px;color:var(--gray-400);" id="noVisitsMsg">
                        <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                        No visits yet
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /workspace -->

<script>
const PID = <?php echo $pid; ?>;

// ════════════════════════════════════════
// DELETE PATIENT (doctor only) — requires typed confirmation
// ════════════════════════════════════════
function deletePatient(id, name) {
    const warning =
        'PERMANENTLY DELETE this patient?\n\n' +
        '"' + name + '"\n\n' +
        'This removes the patient AND all related records — every visit/progress report, ' +
        'additional info, and appointments. This CANNOT be undone.\n\n' +
        'Type DELETE (in capitals) to confirm:';
    const typed = prompt(warning);
    if (typed === null) return;            // cancelled
    if (typed.trim() !== 'DELETE') {
        alert('Deletion cancelled — confirmation text did not match.');
        return;
    }

    const btn = document.getElementById('deletePatientBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...'; }

    fetch('/api/patient/' + id + '/delete', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const d = data.deleted || {};
            alert('Patient deleted.\n\n' +
                  'Reports removed: ' + (d.reports ?? 0) + '\n' +
                  'Appointments removed: ' + (d.appointments ?? 0));
            window.location.href = '/patients';
        } else {
            alert('Error: ' + (data.message || 'Could not delete patient'));
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash"></i> Delete Patient'; }
        }
    })
    .catch(() => {
        alert('Network error while deleting patient');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash"></i> Delete Patient'; }
    });
}

// ════════════════════════════════════════
// MEDICINE TAG PICKER
// ════════════════════════════════════════
const MedPicker = {
    selected: [],
    debounceTimer: null,
    _dropdownOpen: false,

    init() {
        const input    = document.getElementById('medSearch');
        const dropdown = document.getElementById('medDropdown');
        const wrap     = document.getElementById('medPickerWrap');

        // Show top medicines on focus
        input.addEventListener('focus', () => {
            this.fetchMeds(input.value.trim());
        });

        // Search on type with debounce
        input.addEventListener('input', () => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.fetchMeds(input.value.trim());
            }, 180);
        });

        // Close dropdown when clicking outside — use mousedown so it fires
        // before the input loses focus, which prevents the dropdown from
        // disappearing before the item click registers
        document.addEventListener('mousedown', (e) => {
            if (!wrap.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Keyboard: Enter / comma to add; Escape to close
        input.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ',') && input.value.trim() !== '') {
                e.preventDefault();
                this.addTag(input.value.replace(/,/g, '').trim());
                input.value = '';
                dropdown.style.display = 'none';
            }
            if (e.key === 'Escape') {
                dropdown.style.display = 'none';
                input.blur();
            }
        });
    },

    fetchMeds(query) {
        const url = '/api/medicines' + (query ? '?q=' + encodeURIComponent(query) : '');
        fetch(url)
            .then(r => r.json())
            .then(data => { if (data.success) this.renderDropdown(data.data, query); })
            .catch(() => {});
    },

    renderDropdown(items, query) {
        const dropdown = document.getElementById('medDropdown');
        const selectedNames = this.selected.map(s => s.name.toLowerCase());
        const filtered = items.filter(i => !selectedNames.includes(i.name.toLowerCase()));
        let html = '';

        if (filtered.length === 0 && !query) {
            html = '<div class="med-drop-empty">Start typing to search medicines</div>';
        } else {
            filtered.forEach(item => {
                const count = item.usage_count > 0 ? `<span class="med-count">×${item.usage_count}</span>` : '';
                // Use mousedown so the click registers before blur closes dropdown
                html += `<div class="med-drop-item" onmousedown="event.preventDefault();MedPicker.addTag('${escHtml(item.name)}')">
                    <span>${escHtml(item.name)}</span>${count}
                </div>`;
            });
        }

        if (query && !items.find(i => i.name.toLowerCase() === query.toLowerCase())) {
            html += `<div class="med-drop-add" onmousedown="event.preventDefault();MedPicker.addTag('${escHtml(query)}');document.getElementById('medSearch').value='';">
                <i class="fas fa-plus-circle"></i> Add "<strong>${escHtml(query)}</strong>"
            </div>`;
        }

        dropdown.innerHTML = html;
        dropdown.style.display = (html && filtered.length > 0 || query) ? 'block' : 'none';
    },

    addTag(name) {
        name = name.trim();
        if (!name) return;
        // Prevent duplicate
        if (this.selected.find(s => s.name.toLowerCase() === name.toLowerCase())) return;

        this.selected.push({ name });
        this.renderTags();
        this.syncTextarea();
        document.getElementById('medDropdown').style.display = 'none';
        document.getElementById('medSearch').value = '';
        document.getElementById('medSearch').focus();
    },

    removeTag(name) {
        this.selected = this.selected.filter(s => s.name !== name);
        this.renderTags();
        this.syncTextarea();
    },

    renderTags() {
        const container = document.getElementById('selectedTags');
        container.innerHTML = this.selected.map(s =>
            `<span class="med-tag">
                ${escHtml(s.name)}
                <span class="med-tag-x" onclick="MedPicker.removeTag('${escHtml(s.name)}')">×</span>
            </span>`
        ).join('');
    },

    syncTextarea() {
        // Medicines only — notes are a completely separate field
        document.getElementById('reportMedicins').value = this.selected.map(s => s.name).join(', ');
    },

    clear() {
        this.selected = [];
        this.renderTags();
        document.getElementById('medSearch').value = '';
        document.getElementById('reportMedicins').value = '';
        document.getElementById('medDropdown').style.display = 'none';
    }
};

// Init on load
MedPicker.init();

// ── Info panel toggle ──
function toggleInfo() {
    const b     = document.getElementById('infoBody');
    const card  = document.getElementById('infoCard');
    const label = document.getElementById('infoToggleLabel');
    const open  = b.style.display === 'none';
    b.style.display = open ? 'block' : 'none';
    label.textContent = open ? 'Hide' : 'Show';
    card.classList.toggle('info-card-open', open);
}

// ── Info edit mode ──
let infoEditing = false;
function toggleInfoEdit() {
    if (infoEditing) { cancelInfoEdit(); return; }
    // Expand panel if collapsed
    const b = document.getElementById('infoBody');
    if (b.style.display === 'none') {
        b.style.display = 'block';
        document.getElementById('infoToggleLabel').textContent = 'Hide';
        document.getElementById('infoCard').classList.add('info-card-open');
    }
    infoEditing = true;
    document.getElementById('infoEditBtn').classList.add('active');
    document.getElementById('infoEditBtn').innerHTML = '<i class="fas fa-times"></i> Cancel';
    document.getElementById('infoSaveBar').classList.add('visible');
    // Show inputs, hide display values
    document.querySelectorAll('#infoGrid .edit-mode').forEach(el => el.style.display = '');
    document.querySelectorAll('#infoGrid [id^="disp_"]').forEach(el => el.style.display = 'none');
}
function cancelInfoEdit() {
    infoEditing = false;
    document.getElementById('infoEditBtn').classList.remove('active');
    document.getElementById('infoEditBtn').innerHTML = '<i class="fas fa-edit"></i> Edit';
    document.getElementById('infoSaveBar').classList.remove('visible');
    document.querySelectorAll('#infoGrid .edit-mode').forEach(el => el.style.display = 'none');
    document.querySelectorAll('#infoGrid [id^="disp_"]').forEach(el => el.style.display = '');
    document.getElementById('infoSaveMsg').style.display = 'none';
}

function saveInfo(patientId) {
    const inputs = document.querySelectorAll('#infoGrid .edit-mode');
    const fd = new FormData();
    inputs.forEach(el => { if (el.name) fd.append(el.name, el.value); });

    const saveBtn = document.querySelector('#infoSaveBar .save-btn');
    saveBtn.disabled = true; saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    fetch(`/api/patient/${patientId}/update`, { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update display values
            const displayMap = {
                'contact_no': v => v ? `<a href="tel:${escHtml(v)}">${escHtml(v)}</a>` : 'N/A',
                'age': v => v && v > 0 ? escHtml(v) + ' yrs' : 'N/A',
                'gender': v => ({M:'Male',F:'Female'})[v] || 'N/A',
                'mrg_status': v => ({S:'Single',M:'Married',D:'Divorced',W:'Widowed'})[v] || 'N/A',
                'veg': v => ({V:'Vegetarian',NV:'Non-Vegetarian',EV:'Eggetarian'})[v] || 'N/A',
                'dob': v => { if(!v) return 'N/A'; const d=new Date(v); return isNaN(d)?'N/A':d.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}); },
            };
            inputs.forEach(el => {
                const disp = document.getElementById('disp_' + el.name);
                if (!disp) return;
                const fn = displayMap[el.name];
                disp.innerHTML = fn ? fn(el.value) : (escHtml(el.value) || 'N/A');
            });
            document.getElementById('infoSaveMsg').style.display = 'inline';
            setTimeout(() => { cancelInfoEdit(); }, 1200);
        } else {
            alert('Save failed: ' + (data.message || ''));
        }
    })
    .catch(() => alert('Network error'))
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
    });
}

// ── Save new report ──
function saveReport(patientId) {
    MedPicker.syncTextarea();

    const date      = document.getElementById('reportDate').value;
    const medicins  = document.getElementById('reportMedicins').value.trim();
    const notes     = document.getElementById('reportNotes').value.trim();
    const repNotes  = document.getElementById('reportReportsNotes').value.trim();
    const amt       = document.getElementById('reportAmt').value || 0;
    const payType   = document.getElementById('reportPaymentType').value;
    const payStat   = document.getElementById('reportPaymentStatus').value;
    const btn       = document.getElementById('saveReportBtn');
    const ok        = document.getElementById('saveOk');

    // Require at least medicines OR notes OR reports-notes
    if (!medicins && !notes && !repNotes) {
        const wrap = document.getElementById('tagInputArea');
        wrap.style.borderColor = '#ef4444';
        document.getElementById('medSearch').focus();
        setTimeout(() => wrap.style.borderColor = '', 2000);
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const fd = new FormData();
    fd.append('date', date);
    fd.append('medicins', medicins);
    fd.append('notes', notes);
    fd.append('reports_notes', repNotes);
    fd.append('amt', amt);
    fd.append('payment_type', payType);
    fd.append('payment_status', payStat);

    fetch('/api/patient/' + patientId + '/report', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const list  = document.getElementById('historyList');
            const noMsg = document.getElementById('noVisitsMsg');
            if (noMsg) noMsg.remove();
            list.querySelectorAll('.new-entry').forEach(e => e.classList.remove('new-entry'));

            const rId    = data.report_id || '';
            const dateStr = new Date(date).toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'numeric'});
            const payBadges = `<span class="pay-badge pay-${payType}">${payType==='online'?'Online':'Cash'}</span>`
                            + `<span class="pay-badge pay-${payStat}">${payStat==='remaining'?'Due':'Paid'}</span>`;
            const amtHtml   = amt > 0   ? `<div class="h-amt">₹${escHtml(String(amt))} ${payBadges}</div>` : '';
            const notesHtml = notes     ? `<div class="h-notes"><i class="fas fa-sticky-note"></i> ${escHtml(notes)}</div>` : '';
            const repHtml   = repNotes  ? `<div class="h-notes"><i class="fas fa-flask"></i> ${escHtml(repNotes)}</div>` : '';
            const medsDisp  = medicins  ? escHtml(medicins) : '—';

            const el = document.createElement('div');
            el.className = 'h-item new-entry';
            el.id = 'hitem-' + rId;
            el.innerHTML = `
                <div class="h-num">#${rId}</div>
                <div class="h-date"><i class="fas fa-calendar-day"></i> ${dateStr}</div>
                <div class="h-meds">${medsDisp}</div>
                ${notesHtml}
                ${repHtml}
                ${amtHtml}
                <div class="h-action-btns">
                    <a href="/invoice/${rId}" target="_blank" class="h-inv-btn"><i class="fas fa-file-invoice"></i> Invoice</a>
                    <button class="h-edit-btn" onclick="toggleHistEdit(${rId})"><i class="fas fa-pen"></i> Edit</button>
                </div>
                <div class="h-edit-form" id="hedit-${rId}">
                    <div class="h-edit-row">
                        <input type="date" class="h-edit-input" id="he-date-${rId}" value="${escHtml(date)}">
                        <input type="number" class="h-edit-input" id="he-amt-${rId}" placeholder="₹ Amount" value="${escHtml(String(amt))}">
                    </div>
                    <textarea class="h-edit-input" id="he-meds-${rId}" rows="2" placeholder="Medicines..." style="margin-bottom:5px;">${escHtml(medicins)}</textarea>
                    <textarea class="h-edit-input" id="he-notes-${rId}" rows="2" placeholder="Notes (optional)..." style="margin-bottom:5px;">${escHtml(notes)}</textarea>
                    <textarea class="h-edit-input" id="he-repnotes-${rId}" rows="2" placeholder="Reports - Notes (optional)..." style="margin-bottom:6px;">${escHtml(repNotes)}</textarea>
                    <div class="h-edit-actions">
                        <button class="h-save-btn" onclick="saveHistEdit(${rId})"><i class="fas fa-save"></i> Save</button>
                        <button class="h-cancel-btn" onclick="toggleHistEdit(${rId})">Cancel</button>
                    </div>
                </div>`;
            list.prepend(el);

            const badge = document.getElementById('visitBadge');
            badge.textContent = ((parseInt(badge.textContent) || 0) + 1) + ' total';

            MedPicker.clear();
            document.getElementById('reportAmt').value  = '';
            document.getElementById('reportNotes').value = '';
            document.getElementById('reportReportsNotes').value = '';
            document.getElementById('reportPaymentType').value   = 'cash';
            document.getElementById('reportPaymentStatus').value = 'paid';
            document.getElementById('reportDate').value = new Date().toISOString().split('T')[0];
            ok.style.display = 'block';
            setTimeout(() => ok.style.display = 'none', 3000);
            // Reveal the finish button so the doctor can complete the visit
            const finishBtn = document.getElementById('finishConsultBtn');
            if (finishBtn) finishBtn.style.display = 'block';
        } else {
            alert('Error: ' + (data.message || ''));
        }
    })
    .catch(() => alert('Network error'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save Visit'; });
}

// ── History edit ──
function toggleHistEdit(id) {
    const form = document.getElementById('hedit-' + id);
    form.classList.toggle('open');
}
function saveHistEdit(id) {
    const date     = document.getElementById('he-date-'     + id).value;
    const medicins = document.getElementById('he-meds-'     + id).value.trim();
    const notes    = document.getElementById('he-notes-'    + id)?.value.trim() || '';
    const repNotes = document.getElementById('he-repnotes-' + id)?.value.trim() || '';
    const amt      = document.getElementById('he-amt-'      + id).value || 0;

    const fd = new FormData();
    fd.append('date', date);
    fd.append('medicins', medicins);
    fd.append('notes', notes);
    fd.append('reports_notes', repNotes);
    fd.append('amt', amt);

    fetch('/api/report/' + id + '/update', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Reload so both Notes + Reports-Notes and amount/badges re-render cleanly
            location.reload();
        } else { alert('Save failed: ' + (data.message || '')); }
    })
    .catch(() => alert('Network error'));
}

function fmtDateJS(v) {
    if (!v) return 'N/A';
    const d = new Date(v);
    return isNaN(d) ? v : d.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
}
function escHtml(str) {
    const d = document.createElement('div'); d.textContent = String(str); return d.innerHTML;
}

// Finish consultation — mark completed and go back to queue
function finishConsult(apptId) {
    fetch('/api/appointment/' + apptId + '/status', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'status=completed'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) window.location.href = '/queue';
        else alert('Error: ' + data.message);
    });
}
</script>

<?php else: ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($response['message']??'Patient not found'); ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
