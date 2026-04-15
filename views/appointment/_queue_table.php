<?php
/**
 * Shared queue table partial — used by dashboard (compact) and queue page (full).
 * Requires: $queue array, $compact bool
 */

if (!function_exists('qFmt')) {
    function qFmt($v, $fallback = '—') {
        $v = trim((string)($v ?? ''));
        return ($v === '' || $v === '0000-00-00' || $v === '1970-01-01') ? $fallback : htmlspecialchars($v);
    }
    function qName($row) {
        $fn = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
        if ($fn !== '') return htmlspecialchars($fn);
        return htmlspecialchars($row['patient_name'] ?? 'Unknown');
    }
    function qTime($dt) {
        if (!$dt || $dt === '0000-00-00 00:00:00') return '<span style="color:#d1d5db;">—</span>';
        return '<span style="font-size:11px;">' . date('h:i A', strtotime($dt)) . '</span>';
    }
    function statusBadge($s, $isLate = false) {
        $map = [
            'waiting'         => ['warning',  'Waiting'],
            'arrived'         => ['info',     'Arrived'],
            'in_consultation' => ['primary',  'In Consult'],
            'completed'       => ['success',  'Completed'],
            'cancelled'       => ['secondary','Cancelled'],
            'no_show'         => ['danger',   'Not Arrived'],
        ];
        [$cls, $label] = $map[$s] ?? ['secondary', ucfirst($s)];
        $badge = "<span class=\"badge bg-{$cls}\">{$label}</span>";
        if ($isLate && in_array($s, ['arrived','waiting'])) {
            $badge .= ' <span class="badge-late"><i class="fas fa-clock"></i> Late</span>';
        }
        return $badge;
    }
}

$compact     = $compact ?? false;
$tableId     = $tableId ?? 'queueTable';
$qRole       = $_SESSION['role'] ?? 'doctor';
$qCanConsult = in_array($qRole, ['doctor', 'asst_doctor']);
$nowTime     = date('H:i');
$nowDate     = date('Y-m-d');
?>

<?php if (empty($queue)): ?>
    <div style="padding:32px;text-align:center;color:#9ca3af;">
        <i class="fas fa-calendar-day" style="font-size:28px;margin-bottom:8px;display:block;"></i>
        No appointments today
    </div>
<?php else: ?>

<div style="overflow-x:auto;">
<table class="table" style="margin:0;" id="<?php echo htmlspecialchars($tableId); ?>">
    <thead>
        <tr>
            <th style="width:42px;">#</th>
            <th>Patient</th>
            <th>Type</th>
            <th>Slot</th>
            <?php if (!$compact): ?>
            <th>Complaint</th>
            <?php endif; ?>
            <th>Status</th>
            <th style="width:<?php echo $compact ? '130px' : '180px'; ?>;">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($queue as $row):
        $s        = $row['status'];
        $id       = (int)$row['id'];
        $pid      = (int)($row['patient_id'] ?? 0);
        $isWalkin = ($row['type'] === 'walkin');
        $slotHHMM = $row['slot_time'] ? substr($row['slot_time'], 0, 5) : '';
        $rowDate  = $row['appt_date'] ?? $nowDate;
        $isLate   = ($row['type'] === 'prebooked')
                 && in_array($s, ['arrived','waiting'])
                 && $slotHHMM !== ''
                 && $rowDate === $nowDate
                 && $slotHHMM < $nowTime;
    ?>
        <tr class="queue-row <?php echo $isLate ? 'row-late' : ''; ?>"
            data-status="<?php echo htmlspecialchars($s); ?>" data-id="<?php echo $id; ?>">

            <td><span class="token-badge"><?php echo (int)$row['token_number']; ?></span></td>

            <td>
                <?php if ($pid): ?>
                    <a href="/patient/<?php echo $pid; ?>" style="font-weight:600;"><?php echo qName($row); ?></a>
                <?php else: ?>
                    <span style="font-weight:600;"><?php echo qName($row); ?></span>
                    <span class="badge bg-info" style="font-size:9px;">New</span>
                <?php endif; ?>
            </td>

            <td>
                <?php if ($isWalkin): ?>
                    <span class="badge bg-secondary"><i class="fas fa-walking"></i> Walk-in</span>
                <?php else: ?>
                    <span class="badge" style="background:#7c3aed;color:#fff;font-size:10px;"><i class="fas fa-calendar-check"></i> Booked</span>
                <?php endif; ?>
            </td>

            <td>
                <?php if ($slotHHMM): ?>
                    <?php echo date('h:i A', strtotime($slotHHMM)); ?>
                    <?php if ($isLate): ?><br><span class="badge-late"><i class="fas fa-clock"></i> Late</span><?php endif; ?>
                <?php else: ?>
                    <span style="color:#9ca3af;">—</span>
                <?php endif; ?>
            </td>

            <?php if (!$compact): ?>
            <td style="font-size:12px;color:#6b7280;"><?php echo qFmt($row['chief_complaint'] ?? ''); ?></td>
            <?php endif; ?>

            <td><?php echo statusBadge($s, $isLate); ?></td>

            <td class="status-btns">

                <?php if ($s === 'waiting'): ?>

                    <?php if ($isWalkin): ?>
                        <button class="btn btn-success btn-sm" onclick="setStatus(<?php echo $id; ?>,'arrived')">
                            <i class="fas fa-check-circle"></i><?php echo $compact ? '' : ' Arrived'; ?>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success btn-sm" onclick="setStatus(<?php echo $id; ?>,'arrived')">
                            <i class="fas fa-check-circle"></i><?php echo $compact ? '' : ' Arrived'; ?>
                        </button>
                        <button class="btn btn-warning btn-sm" style="color:#fff;" onclick="setStatus(<?php echo $id; ?>,'no_show')" title="Not Arrived">
                            <i class="fas fa-user-slash"></i><?php echo $compact ? '' : ' Not Arrived'; ?>
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary btn-sm" onclick="setStatus(<?php echo $id; ?>,'cancelled')" title="Cancel">
                        <i class="fas fa-times"></i>
                    </button>

                <?php elseif ($s === 'arrived'): ?>

                    <?php if ($qCanConsult): ?>
                    <button class="btn btn-primary btn-sm" onclick="callPatient(<?php echo $id; ?>,<?php echo $pid; ?>)">
                        <i class="fas fa-stethoscope"></i><?php echo $compact ? '' : ' Call'; ?>
                    </button>
                    <?php else: ?>
                    <span style="color:#16a34a;font-size:11px;font-weight:600;"><i class="fas fa-user-check"></i> In Clinic</span>
                    <?php endif; ?>
                    <button class="btn btn-secondary btn-sm" onclick="setStatus(<?php echo $id; ?>,'cancelled')" title="Cancel">
                        <i class="fas fa-times"></i>
                    </button>

                <?php elseif ($s === 'in_consultation'): ?>

                    <?php if ($qCanConsult): ?>
                    <button class="btn btn-success btn-sm" onclick="finishConsult(<?php echo $id; ?>)">
                        <i class="fas fa-check"></i><?php echo $compact ? '' : ' Finish'; ?>
                    </button>
                    <?php if ($pid): ?>
                    <a href="/patient/<?php echo $pid; ?>" class="btn btn-secondary btn-sm" title="View Patient">
                        <i class="fas fa-user"></i>
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <span style="color:#2563eb;font-size:11px;font-weight:600;"><i class="fas fa-stethoscope"></i> With Doctor</span>
                    <?php endif; ?>

                <?php elseif ($s === 'no_show'): ?>

                    <button class="btn btn-outline-primary btn-sm" onclick="setStatus(<?php echo $id; ?>,'arrived')" title="Patient came late">
                        <i class="fas fa-undo"></i><?php echo $compact ? '' : ' Arrived Late'; ?>
                    </button>

                <?php elseif ($s === 'completed'): ?>

                    <span style="color:#9ca3af;font-size:11px;"><i class="fas fa-check-double"></i> Done</span>
                    <?php if ($pid && $qCanConsult && !$compact): ?>
                    <a href="/patient/<?php echo $pid; ?>" class="btn btn-secondary btn-sm" title="View Patient">
                        <i class="fas fa-user"></i>
                    </a>
                    <?php endif; ?>

                <?php else: ?>
                    <span style="color:#9ca3af;font-size:11px;"><?php echo htmlspecialchars(ucfirst(str_replace('_',' ',$s))); ?></span>
                <?php endif; ?>

            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php endif; ?>

<?php
// JS output once per page
global $__queueJsLoaded;
if (empty($__queueJsLoaded)):
    $__queueJsLoaded = true;
?>
<style>
.queue-row[data-status="arrived"]         { background:#f0fdf4; }
.queue-row[data-status="in_consultation"] { background:#eff6ff; }
.queue-row[data-status="completed"]       { opacity:.75; }
.queue-row[data-status="no_show"]         { opacity:.6; }
.queue-row[data-status="cancelled"]       { opacity:.5; }
.queue-row.row-late                       { background:#fff7ed !important; border-left:3px solid #f97316; }
.badge-late {
    display:inline-block; font-size:9px; font-weight:700;
    background:#fff7ed; color:#c2410c; border:1px solid #fed7aa;
    border-radius:4px; padding:1px 5px; letter-spacing:.3px;
    text-transform:uppercase; white-space:nowrap;
}
.status-btns .btn { padding:3px 8px; font-size:11px; }
</style>
<script>
function callPatient(id, patientId) {
    doStatus(id, 'in_consultation', function(data) {
        location.href = data.redirect || location.href;
    });
}
function finishConsult(id) {
    doStatus(id, 'completed', function() { location.reload(); });
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
