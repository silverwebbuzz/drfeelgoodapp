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
            <?php if (!$compact): ?><th>Phone</th><?php endif; ?>
            <th>Type</th>
            <th>Slot</th>
            <?php if (!$compact): ?>
            <th style="min-width:60px;text-align:center;" title="Called in">In</th>
            <th style="min-width:60px;text-align:center;" title="Done">Out</th>
            <th>Complaint</th>
            <?php endif; ?>
            <th>Status</th>
            <?php if ($qRole === 'reception'): ?><th style="min-width:100px;text-align:center;">Payment</th><?php endif; ?>
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

            <?php if (!$compact): ?>
            <td><?php
                $ph = trim($row['patient_phone'] ?? $row['contact_no'] ?? '');
                if ($ph !== '') {
                    $telDigits = preg_replace('/[^0-9+]/', '', $ph);
                    echo '<a href="tel:' . htmlspecialchars($telDigits) . '" style="font-weight:600;white-space:nowrap;"><i class="fas fa-phone-alt" style="font-size:10px;margin-right:4px;color:#16a34a;"></i>' . htmlspecialchars($ph) . '</a>';
                } else {
                    echo '<span style="color:#d1d5db;">—</span>';
                }
            ?></td>
            <?php endif; ?>

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
            <td style="text-align:center;"><?php
                $ca = $row['called_at'] ?? null;
                echo ($ca && $ca !== '0000-00-00 00:00:00') ? '<span style="font-size:11px;">'.date('h:i A', strtotime($ca)).'</span>' : '<span style="color:#d1d5db;">—</span>';
            ?></td>
            <td style="text-align:center;"><?php
                $cp = $row['completed_at'] ?? null;
                echo ($cp && $cp !== '0000-00-00 00:00:00') ? '<span style="font-size:11px;">'.date('h:i A', strtotime($cp)).'</span>' : '<span style="color:#d1d5db;">—</span>';
            ?></td>
            <td style="font-size:12px;color:#6b7280;"><?php echo qFmt($row['chief_complaint'] ?? ''); ?></td>
            <?php endif; ?>

            <td><?php echo statusBadge($s, $isLate); ?></td>

            <?php if ($qRole === 'reception'): ?>
            <td style="text-align:center;">
                <?php if ($s === 'completed' && !empty($row['report_id'])): ?>
                    <?php
                        $payStatus = $row['payment_status'] ?? 'paid';
                        $payType   = $row['payment_type']   ?? 'cash';
                        $payAmt    = (int)($row['report_amt'] ?? 0);
                        $rptId     = (int)$row['report_id'];
                    ?>
                    <?php if ($payStatus === 'paid'): ?>
                        <span class="badge bg-success" style="font-size:11px;">
                            <i class="fas fa-check-circle"></i> Paid
                        </span>
                    <?php else: ?>
                        <div class="pay-pop-wrap" style="position:relative;display:inline-block;">
                            <button type="button" class="btn btn-warning btn-sm"
                                    onclick="togglePayPop(<?php echo $rptId; ?>)"
                                    title="Click to record payment">
                                <i class="fas fa-clock"></i> Remaining
                            </button>
                            <div class="pay-pop" id="payPop<?php echo $rptId; ?>" style="display:none;">
                                <div class="pay-pop-amt">Amount: &#8377;<?php echo number_format($payAmt); ?></div>
                                <label class="pay-pop-label">Payment method</label>
                                <select id="payType<?php echo $rptId; ?>" class="pay-pop-select">
                                    <option value="cash" <?php echo $payType === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                    <option value="online" <?php echo $payType === 'online' ? 'selected' : ''; ?>>Online</option>
                                </select>
                                <div class="pay-pop-actions">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="togglePayPop(<?php echo $rptId; ?>)">Cancel</button>
                                    <button type="button" class="btn btn-success btn-sm" onclick="savePayment(<?php echo $rptId; ?>)">
                                        <i class="fas fa-check"></i> Mark Paid
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#d1d5db;font-size:12px;">—</span>
                <?php endif; ?>
            </td>
            <?php endif; ?>

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
                    <?php if ($qRole === 'reception' && !empty($row['report_id'])): ?>
                    <a href="/invoice/<?php echo (int)$row['report_id']; ?>" target="_blank" class="btn btn-primary btn-sm" title="Print Invoice">
                        <i class="fas fa-print"></i> Invoice
                    </a>
                    <?php endif; ?>
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
.pay-pop {
    position:absolute; top:100%; right:0; margin-top:4px; z-index:50;
    background:#fff; border:1px solid #e5e7eb; border-radius:8px;
    box-shadow:0 8px 24px rgba(0,0,0,.12); padding:12px; width:180px; text-align:left;
}
.pay-pop-amt { font-weight:700; font-size:13px; color:#111827; margin-bottom:8px; }
.pay-pop-label { display:block; font-size:11px; color:#6b7280; margin-bottom:3px; }
.pay-pop-select { width:100%; padding:5px 6px; font-size:12px; border:1px solid #d1d5db; border-radius:6px; margin-bottom:10px; }
.pay-pop-actions { display:flex; gap:6px; justify-content:flex-end; }
.pay-pop-actions .btn { padding:3px 8px; font-size:11px; }
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
function togglePayPop(reportId) {
    const pop = document.getElementById('payPop' + reportId);
    if (!pop) return;
    const isOpen = pop.style.display === 'block';
    // Close any other open popovers first
    document.querySelectorAll('.pay-pop').forEach(p => p.style.display = 'none');
    pop.style.display = isOpen ? 'none' : 'block';
}
function savePayment(reportId) {
    const sel = document.getElementById('payType' + reportId);
    const payType = sel ? sel.value : 'cash';
    fetch('/api/report/' + reportId + '/payment', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'payment_status=paid&payment_type=' + encodeURIComponent(payType)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update payment status'));
        }
    })
    .catch(e => alert('Error: ' + e.message));
}
// Close popover when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.pay-pop-wrap')) {
        document.querySelectorAll('.pay-pop').forEach(p => p.style.display = 'none');
    }
});
</script>
<?php endif; ?>
