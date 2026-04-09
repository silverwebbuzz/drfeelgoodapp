<?php
$page_title = 'Today\'s Queue';

// Helpers
function qFmt($v, $fallback = 'N/A') {
    $v = trim((string)($v ?? ''));
    return ($v === '' || $v === '0000-00-00' || $v === '1970-01-01') ? $fallback : htmlspecialchars($v);
}
function qName($row) {
    $fn = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
    if ($fn !== '') return htmlspecialchars($fn);
    return qFmt($row['patient_name'] ?? '', 'Unknown');
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
<ul class="nav nav-tabs mb-10" id="filterTabs" style="margin-bottom:10px;">
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
                    <th style="width:50px;">#</th>
                    <th>Patient</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Time</th>
                    <th>Complaint</th>
                    <th>Status</th>
                    <th style="width:220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($queue as $row): ?>
                <tr class="queue-row" data-status="<?php echo htmlspecialchars($row['status']); ?>" data-id="<?php echo (int)$row['id']; ?>">
                    <td><span class="token-badge"><?php echo (int)$row['token_number']; ?></span></td>
                    <td>
                        <?php if (!empty($row['patient_id'])): ?>
                            <a href="/patient/<?php echo (int)$row['patient_id']; ?>" style="font-weight:600;"><?php echo qName($row); ?></a>
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
                            <span class="badge bg-info">Pre-booked</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row['slot_time'] ? date('h:i A', strtotime($row['slot_time'])) : '<span style="color:#9ca3af;">—</span>'; ?></td>
                    <td><?php echo qFmt($row['chief_complaint'] ?? ''); ?></td>
                    <td class="status-cell"><?php echo statusBadge($row['status']); ?></td>
                    <td class="status-btns">
                        <?php $s = $row['status']; $id = (int)$row['id']; ?>
                        <?php if ($s === 'waiting'): ?>
                            <button class="btn btn-primary btn-sm" onclick="setStatus(<?php echo $id; ?>,'in_consultation')"><i class="fas fa-stethoscope"></i> Call</button>
                            <button class="btn btn-danger btn-sm" onclick="setStatus(<?php echo $id; ?>,'no_show')">No Show</button>
                        <?php elseif ($s === 'in_consultation'): ?>
                            <button class="btn btn-success btn-sm" onclick="setStatus(<?php echo $id; ?>,'completed')"><i class="fas fa-check"></i> Done</button>
                        <?php elseif ($s === 'completed'): ?>
                            <span style="color:#9ca3af;font-size:11px;"><i class="fas fa-check-double"></i> Done</span>
                        <?php else: ?>
                            <span style="color:#9ca3af;font-size:11px;"><?php echo ucfirst($s); ?></span>
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

// Status update
function setStatus(id, status) {
    const labels = {waiting:'Waiting',in_consultation:'In Consult',completed:'Completed',cancelled:'Cancelled',no_show:'No Show'};
    fetch('/api/appointment/' + id + '/status', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'status=' + encodeURIComponent(status)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Error: ' + data.message);
    });
}

// Auto-refresh every 60s
setTimeout(() => location.reload(), 60000);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
