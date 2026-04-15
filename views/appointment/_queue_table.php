<?php
/**
 * Shared queue table partial.
 * Requires: $queue (array of appointment rows), $compact (bool, optional — hides date nav & filter tabs)
 * Includes: qFmt, qName, qTime, statusBadge helpers (defined once; guard against re-declaration).
 * JS functions callPatient(), finishConsult(), setStatus(), doStatus() are output once via $__queueJsLoaded.
 */

if (!function_exists('qFmt')) {
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
}

$compact      = $compact ?? false;
$tableId      = $tableId ?? 'queueTable';
$qRole        = $_SESSION['role'] ?? 'doctor';
$qCanConsult  = in_array($qRole, ['doctor', 'asst_doctor']); // can call/finish patients
?>

<?php if (empty($queue)): ?>
    <div style="padding:32px;text-align:center;color:#9ca3af;">
        <i class="fas fa-calendar-day" style="font-size:28px;margin-bottom:8px;display:block;"></i>
        No appointments today
    </div>
<?php else: ?>

<?php if (!$compact): ?>
<!-- Filter tabs (full queue page only) -->
<ul class="nav nav-tabs" id="filterTabs-<?php echo $tableId; ?>" style="margin-bottom:10px;">
    <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="waiting">Waiting</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="in_consultation">In Consult</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="completed">Completed</a></li>
</ul>
<?php endif; ?>

<div style="overflow-x:auto;">
<table class="table" style="margin:0;" id="<?php echo htmlspecialchars($tableId); ?>">
    <thead>
        <tr>
            <th style="width:46px;">#</th>
            <th>Patient</th>
            <th>Phone</th>
            <th>Type</th>
            <th>Slot</th>
            <?php if (!$compact): ?>
            <th class="time-col" title="Dr. called patient in">In (Called)</th>
            <th class="time-col" title="Consultation finished">Out (Done)</th>
            <?php endif; ?>
            <th>Complaint</th>
            <th>Status</th>
            <th style="width:<?php echo $compact ? '120px' : '200px'; ?>;">Actions</th>
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
            <?php if (!$compact): ?>
            <td class="time-col"><?php echo qTime($row['called_at'] ?? null); ?></td>
            <td class="time-col"><?php echo qTime($row['completed_at'] ?? null); ?></td>
            <?php endif; ?>
            <td><?php echo qFmt($row['chief_complaint'] ?? ''); ?></td>
            <td class="status-cell"><?php echo statusBadge($s); ?></td>
            <td class="status-btns">
                <?php if ($s === 'waiting'): ?>
                    <?php if ($qCanConsult): ?>
                    <button class="btn btn-primary btn-sm" onclick="callPatient(<?php echo $id; ?>, <?php echo $pid; ?>)">
                        <i class="fas fa-stethoscope"></i> <?php echo $compact ? '' : 'Call'; ?>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="setStatus(<?php echo $id; ?>,'no_show')" title="No Show">
                        <?php echo $compact ? '<i class="fas fa-user-slash"></i>' : 'No Show'; ?>
                    </button>
                    <?php endif; ?>
                <?php elseif ($s === 'in_consultation'): ?>
                    <?php if ($qCanConsult): ?>
                    <button class="btn btn-success btn-sm" onclick="finishConsult(<?php echo $id; ?>)">
                        <i class="fas fa-check"></i> <?php echo $compact ? '' : 'Finish'; ?>
                    </button>
                    <?php endif; ?>
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
</div>

<?php if (!$compact): ?>
<script>
// Filter tabs
document.querySelectorAll('#filterTabs-<?php echo $tableId; ?> .nav-link').forEach(tab => {
    tab.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('#filterTabs-<?php echo $tableId; ?> .nav-link').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const filter = tab.dataset.filter;
        document.querySelectorAll('#<?php echo $tableId; ?> tbody tr').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});
</script>
<?php endif; ?>

<?php endif; // end empty check ?>

<?php
// Output shared JS exactly once per page (even if partial included twice)
global $__queueJsLoaded;
if (empty($__queueJsLoaded)):
    $__queueJsLoaded = true;
?>
<script>
function callPatient(id, patientId) {
    doStatus(id, 'in_consultation', function(data) {
        location.href = data.redirect || '/queue';
    });
}
function finishConsult(id) {
    doStatus(id, 'completed', function() { location.href = '/queue'; });
}
function setStatus(id, status) {
    doStatus(id, status, function() { location.reload(); });
}
function doStatus(id, status, cb) {
    fetch('/api/appointment/' + id + '/status', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'status=' + encodeURIComponent(status)
    })
    .then(r => r.json())
    .then(data => { if (data.success) cb(data); else alert('Error: ' + data.message); });
}
</script>
<?php endif; ?>
