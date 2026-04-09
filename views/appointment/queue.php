<?php
$page_title = 'Today\'s Queue';

function qFmt($v, $fallback = 'N/A') {
    $v = trim((string)($v ?? ''));
    return ($v === '' || $v === '0000-00-00' || $v === '1970-01-01') ? $fallback : htmlspecialchars($v);
}
function qName($row) {
    $fn = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
    if ($fn !== '') return htmlspecialchars($fn);
    return qFmt($row['patient_name'] ?? '', 'Unknown');
}
function qTime($dt) {
    if (!$dt || $dt === '0000-00-00 00:00:00') return '<span style="color:#d1d5db;">—</span>';
    return '<span style="font-size:11px;">' . date('h:i A', strtotime($dt)) . '</span>';
}
function statusBadge($s) {
    $map = [
        'waiting'        => ['warning',  'Waiting'],
        'in_consultation'=> ['primary',  'In Consult'],
        'completed'      => ['success',  'Completed'],
        'cancelled'      => ['secondary','Cancelled'],
        'no_show'        => ['danger',   'No Show'],
    ];
    [$cls, $label] = $map[$s] ?? ['secondary', $s];
    return "<span class=\"badge bg-{$cls}\">{$label}</span>";
}

$queue = $queueData['queue'] ?? [];
$stats = $queueData['stats'] ?? [];
$date  = $queueData['date']  ?? date('Y-m-d');
$today = date('Y-m-d');
ob_start();
?>
<style>
.queue-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:10px; margin-bottom:14px; }
.q-stat { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:10px 14px; }
.q-stat .val { font-size:22px; font-weight:700; line-height:1; }
.q-stat .lbl { font-size:11px; color:#6b7280; margin-top:2px; }
.token-badge { display:inline-block; width:36px; height:36px; border-radius:50%; background:var(--primary); color:#fff; font-weight:700; font-size:13px; line-height:36px; text-align:center; }
.status-btns .btn { padding:3px 8px; font-size:11px; }
.date-nav { display:flex; align-items:center; gap:8px; }
#filterTabs .nav-link { padding:4px 12px; font-size:12px; }
.queue-row td { vertical-align:middle; }
.time-col { text-align:center; min-width:64px; }
.time-col .tlabel { font-size:10px; color:#9ca3af; display:block; }
/* Highlight in-consultation row */
.queue-row[data-status="in_consultation"] { background:#eff6ff; }
.queue-row[data-status="completed"] { opacity:.75; }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h1 class="page-title" style="margin:0;">Today's Queue</h1>
    <div style="display:flex;gap:8px;align-items:center;">
        <div class="date-nav">
            <a href="/queue?date=<?php echo date('Y-m-d', strtotime($date . ' -1 day')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
            <input type="date" id="dateJump" class="form-control form-control-sm" value="<?php echo $date; ?>" style="width:140px;" onchange="location='/queue?date='+this.value">
            <?php if ($date !== $today): ?>
                <a href="/queue" class="btn btn-outline-primary btn-sm">Today</a>
            <?php endif; ?>
            <a href="/queue?date=<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
        </div>
        <a href="/walkin" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Walk-in Token</a>
    </div>
</div>

<!-- Stats -->
<div class="queue-grid">
    <div class="q-stat">
        <div class="val"><?php echo (int)($stats['total'] ?? 0); ?></div>
        <div class="lbl">Total</div>
    </div>
    <div class="q-stat" style="border-color:#f59e0b;">
        <div class="val" style="color:#d97706;"><?php echo (int)($stats['waiting'] ?? 0); ?></div>
        <div class="lbl">Waiting</div>
    </div>
    <div class="q-stat" style="border-color:#3b82f6;">
        <div class="val" style="color:#2563eb;"><?php echo (int)($stats['in_consultation'] ?? 0); ?></div>
        <div class="lbl">In Consult</div>
    </div>
    <div class="q-stat" style="border-color:#22c55e;">
        <div class="val" style="color:#16a34a;"><?php echo (int)($stats['completed'] ?? 0); ?></div>
        <div class="lbl">Completed</div>
    </div>
</div>

<!-- Filter tabs -->
<ul class="nav nav-tabs" id="filterTabs" style="margin-bottom:10px;">
    <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="waiting">Waiting</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="in_consultation">In Consult</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="completed">Completed</a></li>
</ul>

<div class="card" style="margin-bottom:0;">
    <div class="card-body" style="padding:0;">
        <?php if (empty($queue)): ?>
            <div style="padding:40px;text-align:center;color:#9ca3af;">
                <i class="fas fa-calendar-day" style="font-size:32px;margin-bottom:8px;display:block;"></i>
                No appointments for <?php echo htmlspecialchars($date); ?>
            </div>
        <?php else: ?>
        <table class="table" style="margin:0;" id="queueTable">
            <thead>
                <tr>
                    <th style="width:46px;">#</th>
                    <th>Patient</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Slot</th>
                    <th class="time-col" title="Dr. called patient in">In (Called)</th>
                    <th class="time-col" title="Consultation finished">Out (Done)</th>
                    <th>Complaint</th>
                    <th>Status</th>
                    <th style="width:200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($queue as $row): ?>
                <?php $s = $row['status']; $id = (int)$row['id']; $pid = (int)($row['patient_id'] ?? 0); ?>
                <tr class="queue-row" data-status="<?php echo htmlspecialchars($s); ?>" data-id="<?php echo $id; ?>">
                    <td><span class="token-badge"><?php echo (int)$row['token_number']; ?></span></td>
                    <td>
                        <?php if ($pid): ?>
                            <a href="/patient/<?php echo $pid; ?>" style="font-weight:600;"><?php echo qName($row); ?></a>
                        <?php else: ?>
                            <span style="font-weight:600;"><?php echo qName($row); ?></span>
                            <span class="badge bg-info" style="font-size:10px;">New</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo qFmt($row['patient_phone'] ?? $row['contact_no'] ?? ''); ?></td>
                    <td>
                        <?php if ($row['type'] === 'walkin'): ?>
                            <span class="badge bg-secondary">Walk-in</span>
                        <?php else: ?>
                            <span class="badge bg-info">Pre-book</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row['slot_time'] ? date('h:i A', strtotime($row['slot_time'])) : '<span style="color:#9ca3af;">—</span>'; ?></td>
                    <td class="time-col"><?php echo qTime($row['called_at'] ?? null); ?></td>
                    <td class="time-col"><?php echo qTime($row['completed_at'] ?? null); ?></td>
                    <td><?php echo qFmt($row['chief_complaint'] ?? ''); ?></td>
                    <td class="status-cell"><?php echo statusBadge($s); ?></td>
                    <td class="status-btns">
                        <?php if ($s === 'waiting'): ?>
                            <button class="btn btn-primary btn-sm" onclick="callPatient(<?php echo $id; ?>, <?php echo $pid; ?>)">
                                <i class="fas fa-stethoscope"></i> Call
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="setStatus(<?php echo $id; ?>,'no_show')">No Show</button>
                        <?php elseif ($s === 'in_consultation'): ?>
                            <button class="btn btn-success btn-sm" onclick="finishConsult(<?php echo $id; ?>)">
                                <i class="fas fa-check"></i> Finish
                            </button>
                            <?php if ($pid): ?>
                            <a href="/patient/<?php echo $pid; ?>" class="btn btn-secondary btn-sm" title="View Patient">
                                <i class="fas fa-user"></i>
                            </a>
                            <?php endif; ?>
                        <?php elseif ($s === 'completed'): ?>
                            <span style="color:#9ca3af;font-size:11px;"><i class="fas fa-check-double"></i> Done</span>
                        <?php else: ?>
                            <span style="color:#9ca3af;font-size:11px;"><?php echo ucfirst(str_replace('_',' ',$s)); ?></span>
                        <?php endif; ?>
                        <?php if (!in_array($s, ['completed','cancelled','no_show'])): ?>
                            <button class="btn btn-secondary btn-sm" onclick="setStatus(<?php echo $id; ?>,'cancelled')" title="Cancel"><i class="fas fa-times"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
// Filter tabs
document.querySelectorAll('#filterTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('#filterTabs .nav-link').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const filter = tab.dataset.filter;
        document.querySelectorAll('#queueTable tbody tr').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});

// Call patient → set in_consultation, redirect to patient detail
function callPatient(id, patientId) {
    doStatus(id, 'in_consultation', function(data) {
        if (data.redirect) {
            location.href = data.redirect;
        } else {
            location.reload();
        }
    });
}

// Finish consultation → set completed, redirect back to queue
function finishConsult(id) {
    doStatus(id, 'completed', function(data) {
        location.href = '/queue';
    });
}

// Generic status update (no redirect)
function setStatus(id, status) {
    doStatus(id, status, function(data) {
        location.reload();
    });
}

function doStatus(id, status, cb) {
    fetch('/api/appointment/' + id + '/status', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'status=' + encodeURIComponent(status)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) cb(data);
        else alert('Error: ' + data.message);
    });
}

// Auto-refresh every 60s (only if page is visible)
let refreshTimer = setTimeout(() => location.reload(), 60000);
document.addEventListener('visibilitychange', () => {
    if (document.hidden) clearTimeout(refreshTimer);
    else refreshTimer = setTimeout(() => location.reload(), 60000);
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
