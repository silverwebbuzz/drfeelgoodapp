<?php
$page_title = 'Help & User Guide';
ob_start();
?>
<style>
.help-toc {
    background:#fff; border:1px solid #e5e7eb; border-radius:10px;
    padding:16px 20px; margin-bottom:20px; position:sticky; top:16px;
}
.help-toc h3 { font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:#9ca3af; margin:0 0 10px; }
.help-toc a { display:block; font-size:12px; color:#374151; text-decoration:none;
    padding:4px 0; border-left:2px solid transparent; padding-left:8px; transition:.15s; }
.help-toc a:hover { color:var(--primary); border-left-color:var(--primary); }
.help-toc a.active { color:var(--primary); border-left-color:var(--primary); font-weight:600; }

.help-section { background:#fff; border:1px solid #e5e7eb; border-radius:10px;
    padding:24px 28px; margin-bottom:20px; scroll-margin-top:80px; }
.help-section h2 { font-size:17px; font-weight:700; color:#111827; margin:0 0 6px;
    display:flex; align-items:center; gap:10px; padding-bottom:12px;
    border-bottom:2px solid #f3f4f6; }
.help-section h2 i { width:32px; height:32px; border-radius:8px;
    display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
.help-section h3 { font-size:13px; font-weight:700; color:#374151; margin:18px 0 6px; }
.help-section p  { font-size:13px; color:#4b5563; line-height:1.7; margin:0 0 10px; }
.help-section ul { padding-left:18px; margin:0 0 10px; }
.help-section ul li { font-size:13px; color:#4b5563; line-height:1.8; }
.help-section ul li strong { color:#111827; }

.help-badge { display:inline-block; font-size:10px; font-weight:700;
    border-radius:4px; padding:2px 7px; vertical-align:middle; margin:0 2px; }
.hb-doctor   { background:#eff6ff; color:#2563eb; }
.hb-asst     { background:#f0fdf4; color:#16a34a; }
.hb-recep    { background:#fefce8; color:#ca8a04; }
.hb-all      { background:#f5f3ff; color:#7c3aed; }

.help-flow { display:flex; align-items:center; flex-wrap:wrap; gap:6px; margin:10px 0 14px; }
.help-flow-step { background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px;
    padding:5px 12px; font-size:12px; font-weight:600; color:#374151; }
.help-flow-arrow { color:#9ca3af; font-size:12px; }

.help-tip { background:#fffbeb; border:1px solid #fde68a; border-radius:7px;
    padding:10px 14px; font-size:12px; color:#92400e; margin:10px 0; }
.help-tip i { color:#d97706; margin-right:5px; }

.help-note { background:#eff6ff; border:1px solid #bfdbfe; border-radius:7px;
    padding:10px 14px; font-size:12px; color:#1e40af; margin:10px 0; }
.help-note i { color:#2563eb; margin-right:5px; }

.help-layout { display:grid; grid-template-columns:220px 1fr; gap:20px; align-items:start; }
@media(max-width:860px){ .help-layout { grid-template-columns:1fr; } }
@media(max-width:860px){ .help-toc { position:static; } }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title" style="margin:0;"><i class="fas fa-book-open"></i> Help &amp; User Guide</h1>
    <span style="font-size:12px;color:#9ca3af;">
        Role legend:
        <span class="help-badge hb-doctor">Doctor</span>
        <span class="help-badge hb-asst">Asst. Doctor</span>
        <span class="help-badge hb-recep">Reception</span>
        <span class="help-badge hb-all">All roles</span>
    </span>
</div>

<div class="help-layout">

<!-- ── TOC ── -->
<div>
<div class="help-toc">
    <h3>Contents</h3>
    <a href="#overview">System Overview</a>
    <a href="#roles">Roles &amp; Permissions</a>
    <a href="#dashboard">Dashboard</a>
    <a href="#appointments">Appointments / Queue</a>
    <a href="#walkin">Walk-in Token</a>
    <a href="#patients">Patients</a>
    <a href="#patient-detail">Patient Detail &amp; History</a>
    <a href="#invoice">Invoice / Billing</a>
    <a href="#reports">Reports</a>
    <a href="#users">User Management</a>
    <a href="#settings">Clinic Settings</a>
</div>
</div>

<!-- ── CONTENT ── -->
<div>

<!-- Overview -->
<div class="help-section" id="overview">
    <h2><i class="fas fa-info-circle" style="background:#eff6ff;color:#2563eb;"></i> System Overview</h2>
    <p>Dr. Feelgood is a clinic management portal for managing patients, daily appointments, billing, and reports. The system supports three user roles — Doctor, Assistant Doctor, and Reception — each with different access levels.</p>
    <p>All daily work flows through two main areas: the <strong>Appointments queue</strong> (managing who is in clinic today) and the <strong>Patient records</strong> (storing visit history, medicines, and invoices).</p>
    <div class="help-note">
        <i class="fas fa-lock"></i> This Help page is visible to Doctors only. Other roles see only the features available to them.
    </div>
</div>

<!-- Roles -->
<div class="help-section" id="roles">
    <h2><i class="fas fa-users-cog" style="background:#f0fdf4;color:#16a34a;"></i> Roles &amp; Permissions</h2>

    <h3><span class="help-badge hb-doctor">Doctor</span> Full access</h3>
    <ul>
        <li>All pages: Dashboard, Patients, Appointments, Reports, Users, Settings, Help</li>
        <li>Can call patients into consultation, finish consultations</li>
        <li>Can view and edit full patient history (medicines, amounts, notes)</li>
        <li>Can create, edit, and delete system users</li>
        <li>Can change clinic settings, slot timings, invoice details</li>
    </ul>

    <h3><span class="help-badge hb-asst">Asst. Doctor</span> Clinical access</h3>
    <ul>
        <li>Same as Doctor for patient care: queue, patients, reports, billing</li>
        <li>Can call patients and finish consultations</li>
        <li>Cannot access User Management or Clinic Settings</li>
    </ul>

    <h3><span class="help-badge hb-recep">Reception</span> Front-desk access</h3>
    <ul>
        <li>Dashboard and Appointments queue — can mark patients Arrived, Not Arrived, Cancel</li>
        <li>Can add Walk-in tokens</li>
        <li>Can view patient list; on patient detail page sees <strong>visit dates only</strong> (no medicines, amounts, or notes)</li>
        <li>Cannot access Reports, Users, Settings, or Help</li>
    </ul>
</div>

<!-- Dashboard -->
<div class="help-section" id="dashboard">
    <h2><i class="fas fa-th-large" style="background:#eff6ff;color:#2563eb;"></i> Dashboard</h2>
    <span class="help-badge hb-all">All roles</span>

    <h3>Stat Cards (top row)</h3>
    <ul>
        <li><strong>Total Patients</strong> — total registered patients in the system</li>
        <li><strong>Progress Reports</strong> — total visit records ever logged</li>
        <li><strong>New This Month</strong> — patients registered in the current calendar month</li>
        <li><strong>Seen Today</strong> — patients completed today; sub-text shows how many are still pending (waiting + in clinic + in consultation)</li>
    </ul>

    <h3>Today's Appointments</h3>
    <p>Shows all appointments for today in a table with the same columns as the full Appointments page. The status bar shows live counts: Waiting / In Clinic / In Consult / Done. Actions work exactly as they do on the Appointments page — see that section below.</p>

    <h3>Quick Actions</h3>
    <p>Shortcut buttons to the most common tasks: Walk-in Token, Search Patient, New Patient, Appointments, Reports, and Settings (Doctor only).</p>

    <h3>Recently Registered Patients</h3>
    <p>The last 10 patients added to the system, with a link to their detail page.</p>
</div>

<!-- Appointments -->
<div class="help-section" id="appointments">
    <h2><i class="fas fa-calendar-check" style="background:#f5f3ff;color:#7c3aed;"></i> Appointments / Queue</h2>
    <span class="help-badge hb-all">All roles</span>

    <h3>Views</h3>
    <ul>
        <li><strong>Today</strong> — live queue for the selected date with auto-refresh every 60 seconds. Use the &lt; &gt; arrows or date picker to navigate days.</li>
        <li><strong>This Week</strong> — all appointments in the current week</li>
        <li><strong>This Month</strong> — all appointments in the current month</li>
    </ul>

    <h3>Appointment Status Flow</h3>
    <p>Every appointment moves through a defined set of statuses:</p>

    <p><strong>Walk-in patient:</strong></p>
    <div class="help-flow">
        <span class="help-flow-step">Waiting</span>
        <span class="help-flow-arrow">→</span>
        <span class="help-flow-step">Arrived</span>
        <span class="help-flow-arrow">→</span>
        <span class="help-flow-step">In Consultation</span>
        <span class="help-flow-arrow">→</span>
        <span class="help-flow-step">Completed</span>
    </div>
    <p>Walk-ins are added by Reception and the patient is typically already in the clinic, so the first action is simply clicking <strong>Arrived</strong>.</p>

    <p><strong>Pre-booked patient:</strong></p>
    <div class="help-flow">
        <span class="help-flow-step">Waiting</span>
        <span class="help-flow-arrow">→</span>
        <span class="help-flow-step">Arrived <em>or</em> Not Arrived</span>
        <span class="help-flow-arrow">→</span>
        <span class="help-flow-step">In Consultation</span>
        <span class="help-flow-arrow">→</span>
        <span class="help-flow-step">Completed</span>
    </div>
    <p>Booked patients have a set slot time. If they haven't come by their slot time, they show an orange <strong>Late</strong> badge automatically.</p>

    <h3>Action Buttons by Status</h3>
    <ul>
        <li><strong>Waiting (Walk-in)</strong> — <em>Arrived</em>, <em>Cancel</em></li>
        <li><strong>Waiting (Booked)</strong> — <em>Arrived</em>, <em>Not Arrived</em>, <em>Cancel</em></li>
        <li><strong>Arrived</strong> — Doctor/Asst: <em>Call</em> (moves to In Consultation), <em>Cancel</em> &nbsp;|&nbsp; Reception: sees "In Clinic" label only</li>
        <li><strong>In Consultation</strong> — Doctor/Asst: <em>Finish</em> (marks Completed, opens patient record), <em>View Patient</em> &nbsp;|&nbsp; Reception: sees "With Doctor" label only</li>
        <li><strong>Not Arrived</strong> — <em>Arrived Late</em> button to recover the patient if they come late</li>
        <li><strong>Completed / Cancelled</strong> — no further actions</li>
    </ul>

    <h3>Late Badge</h3>
    <p>A row highlights in orange with a <strong>Late</strong> badge when a pre-booked patient's slot time has passed but they are still in <em>Waiting</em> or <em>Arrived</em> status. This is automatic — no action needed.</p>

    <h3>Filter Tabs</h3>
    <p>Click the tabs above the table (All / Waiting / Arrived / In Consult / Completed / Not Arrived / Cancelled) to filter rows without reloading the page.</p>

    <div class="help-tip">
        <i class="fas fa-lightbulb"></i> The Today view auto-refreshes every 60 seconds so all staff see the same live queue without manually reloading.
    </div>
</div>

<!-- Walk-in -->
<div class="help-section" id="walkin">
    <h2><i class="fas fa-ticket-alt" style="background:#fefce8;color:#d97706;"></i> Walk-in Token</h2>
    <span class="help-badge hb-all">All roles</span>

    <p>Use this form to add a patient to today's (or any date's) appointment queue instantly.</p>

    <h3>Steps</h3>
    <ul>
        <li><strong>Search for existing patient</strong> — type name or phone, select from the dropdown. The name and phone fill automatically.</li>
        <li><strong>New / unregistered patient</strong> — leave the search blank, type the name and phone manually. A new patient record will be created automatically on submission.</li>
        <li><strong>Date</strong> — defaults to today. Can be changed for advance bookings.</li>
        <li><strong>Follow-up</strong> — mark Yes if the patient is returning for a follow-up visit.</li>
        <li><strong>Time Slot</strong> — optional. Assign a slot to avoid overlapping. Full slots are shown greyed out. Walk-ins without a slot join the queue after pre-booked patients for that time.</li>
        <li><strong>Extended Hours</strong> — toggle to see slots beyond normal clinic hours (admin-only feature).</li>
        <li><strong>Chief Complaint</strong> — brief reason for visit (optional but recommended).</li>
    </ul>

    <p>After submitting, a <strong>Token Number</strong> is displayed. Use this to call the patient by token in the queue.</p>
</div>

<!-- Patients -->
<div class="help-section" id="patients">
    <h2><i class="fas fa-users" style="background:#f0fdf4;color:#16a34a;"></i> Patients</h2>
    <span class="help-badge hb-all">All roles</span>

    <h3>Patient List</h3>
    <ul>
        <li>Shows all registered patients, 25 per page by default</li>
        <li><strong>Search</strong> — filters by name, phone, or patient ID in real time (debounced 350ms)</li>
        <li><strong>Per page</strong> — change to 10 / 25 / 50 / 100 using the dropdown</li>
        <li>Pagination loads via AJAX — no full page reload</li>
        <li>Click <strong>View</strong> to open a patient's detail page</li>
    </ul>

    <h3>New Patient</h3>
    <p>Click <strong>New Patient</strong> to register a patient. Required: First Name, Gender. Recommended: Phone, Date of Birth, Chief Complaint.</p>

    <div class="help-note">
        <i class="fas fa-info-circle"></i> Patients can also be auto-created when a walk-in token is submitted for an unregistered person — no need to register them separately first.
    </div>
</div>

<!-- Patient Detail -->
<div class="help-section" id="patient-detail">
    <h2><i class="fas fa-user-circle" style="background:#fff7ed;color:#d97706;"></i> Patient Detail &amp; Visit History</h2>
    <span class="help-badge hb-doctor">Doctor</span>
    <span class="help-badge hb-asst">Asst. Doctor</span>
    <span class="help-badge hb-recep" style="margin-left:2px;">Reception (limited)</span>

    <h3>Patient Info Card</h3>
    <p>Shows name, age, gender, contact, date of registration, chief complaint, and blood group. Doctor/Asst Doctor can edit these details.</p>

    <h3>Visit History <span class="help-badge hb-doctor">Doctor</span> <span class="help-badge hb-asst">Asst.</span></h3>
    <p>Each visit (progress report) shows:</p>
    <ul>
        <li><strong>Visit Date</strong> — always visible to all roles</li>
        <li><strong>Medicines prescribed</strong> — visible to Doctor and Asst. Doctor only</li>
        <li><strong>Consultation notes / symptoms</strong> — Doctor and Asst. Doctor only</li>
        <li><strong>Amount charged</strong> — Doctor and Asst. Doctor only</li>
        <li><strong>Invoice</strong> — print/download button — Doctor and Asst. Doctor only</li>
    </ul>

    <div class="help-tip">
        <i class="fas fa-eye-slash"></i> Reception staff see only the visit date — no medicines, amounts, or clinical notes.
    </div>

    <h3>Adding a Visit Record</h3>
    <p>When a consultation is finished (status set to <em>Completed</em> from the queue), the system opens the patient page automatically. The doctor then fills in medicines, notes, and the amount for that visit.</p>
</div>

<!-- Invoice -->
<div class="help-section" id="invoice">
    <h2><i class="fas fa-file-invoice" style="background:#f0fdf4;color:#16a34a;"></i> Invoice / Billing</h2>
    <span class="help-badge hb-doctor">Doctor</span>
    <span class="help-badge hb-asst">Asst. Doctor</span>

    <p>Each visit record has a <strong>Print Invoice</strong> button on the patient detail page. The invoice shows:</p>
    <ul>
        <li>Clinic name, address, doctor name, qualification, phone</li>
        <li>Patient name, age, gender</li>
        <li>Visit date and invoice number</li>
        <li>One combined line item: Consultation + medicines</li>
        <li>Total amount, GST (if enabled), and grand total</li>
    </ul>
    <p>Click the <strong>Print</strong> button on the invoice page to open the browser print dialog. The layout is optimised for A4 paper with a clean professional format.</p>

    <h3>Invoice Settings</h3>
    <p>Doctor name, qualification, clinic address, phone, email, PAN, and GST settings are all configured in <strong>Settings → Invoice / Billing Settings</strong>.</p>
</div>

<!-- Reports -->
<div class="help-section" id="reports">
    <h2><i class="fas fa-chart-bar" style="background:#f5f3ff;color:#7c3aed;"></i> Reports</h2>
    <span class="help-badge hb-doctor">Doctor</span>
    <span class="help-badge hb-asst">Asst. Doctor</span>

    <h3>Patient Report</h3>
    <p>Filter and export patient data. Search by date range, gender, age group. Useful for understanding patient demographics and new registrations over time.</p>

    <div class="help-note">
        <i class="fas fa-info-circle"></i> Additional report types (Income, Queue/Ops, Medicines, Productivity) are planned and will be enabled in future updates.
    </div>
</div>

<!-- Users -->
<div class="help-section" id="users">
    <h2><i class="fas fa-users-cog" style="background:#eff6ff;color:#2563eb;"></i> User Management</h2>
    <span class="help-badge hb-doctor">Doctor only</span>

    <p>Manage all staff login accounts from here.</p>

    <h3>Add User</h3>
    <ul>
        <li>Click <strong>Add User</strong>, fill in First Name, Username, Email, Contact, Role, and Password</li>
        <li>Username cannot be changed after creation</li>
        <li>Role choices: <strong>Doctor</strong>, <strong>Asst. Doctor</strong>, <strong>Reception</strong></li>
    </ul>

    <h3>Edit User</h3>
    <ul>
        <li>Click the pencil icon on any user row</li>
        <li>Leave the password field blank to keep their existing password</li>
        <li>Set Status to <strong>Inactive</strong> to disable a user's login without deleting their account</li>
    </ul>

    <h3>Delete User</h3>
    <p>Click the trash icon. You cannot delete your own account (the row shows no delete button for the logged-in user).</p>

    <div class="help-tip">
        <i class="fas fa-shield-alt"></i> Only the Doctor role can access this page. Never share your Doctor login — create individual accounts for each staff member.
    </div>
</div>

<!-- Settings -->
<div class="help-section" id="settings">
    <h2><i class="fas fa-cog" style="background:#f1f5f9;color:#475569;"></i> Clinic Settings</h2>
    <span class="help-badge hb-doctor">Doctor only</span>

    <h3>Clinic Info</h3>
    <p>Clinic name, phone number, and default consultation fee. These appear on invoices and the booking page.</p>

    <h3>Slot Duration &amp; Max Per Slot</h3>
    <p>Set appointment slots to 15 or 30 minutes. <strong>Max per slot</strong> controls how many patients can book the same time slot online.</p>

    <h3>Monday – Saturday Sessions</h3>
    <p>Enable/disable Morning and Evening sessions independently. Set start and end times for each session. Slots are auto-generated from these times.</p>

    <h3>Sunday</h3>
    <p>Toggle whether the clinic is open on Sundays and set the session times.</p>

    <h3>Extended Hours (Walk-in Admin Only)</h3>
    <p>Separate end times beyond the normal session close. These extra slots are only visible in the admin Walk-in form, never on the public booking page. Use this when the doctor accepts last-minute patients after normal hours.</p>

    <h3>Online Booking Window</h3>
    <p>How many days ahead patients can book online — 7, 15, or 30 days.</p>

    <h3>Invoice / Billing Settings</h3>
    <p>Doctor name, qualification, clinic address, phone, email — all printed on invoices. Optionally enable PAN number and GST (with GST number and rate).</p>

    <h3>Closed / Holiday Dates</h3>
    <p>Add specific dates when the clinic is closed (holidays, etc.). No slots will be shown on the booking page for these dates. Add a reason (optional) for your own reference.</p>
</div>

</div><!-- /.help-content -->
</div><!-- /.help-layout -->

<script>
// Highlight active TOC link on scroll
const sections = document.querySelectorAll('.help-section');
const tocLinks = document.querySelectorAll('.help-toc a');

function setActiveToc() {
    let current = '';
    sections.forEach(s => {
        const top = s.getBoundingClientRect().top;
        if (top <= 100) current = s.id;
    });
    tocLinks.forEach(a => {
        a.classList.toggle('active', a.getAttribute('href') === '#' + current);
    });
}

window.addEventListener('scroll', setActiveToc);
setActiveToc();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
