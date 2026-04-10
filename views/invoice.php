<?php
/**
 * Invoice view — standalone page (no layout.php wrapper)
 * Variables provided by index.php:
 *   $report   — progress_report row
 *   $patient  — patients row
 *   $s        — settings key=>value array
 */

// Guard
if (empty($report) || empty($patient)) {
    http_response_code(404);
    echo '<p style="font-family:sans-serif;padding:40px;">Invoice not found.</p>';
    exit;
}

// ── Helpers ─────────────────────────────────────────────────────────────────
function invFmt($v, $fb = '—') {
    $v = is_string($v) ? trim($v) : $v;
    return ($v === null || $v === '' || $v === '0000-00-00') ? $fb : $v;
}
function invFmtDate($v) {
    if (!$v || $v === '0000-00-00') return '—';
    $ts = strtotime($v);
    return $ts ? date('d M Y', $ts) : $v;
}
function invName($f, $l) {
    $n = trim(trim($f ?? '') . ' ' . trim($l ?? ''));
    return $n === '' ? 'Patient' : $n;
}

// ── Data ─────────────────────────────────────────────────────────────────────
$reportId   = (int)$report['id'];
$invoiceNo  = 'INV-' . str_pad($reportId, 5, '0', STR_PAD_LEFT);
$visitDate  = invFmtDate($report['date'] ?? '');
$medicines  = array_filter(array_map('trim', explode(',', $report['medicins'] ?? '')));
$baseAmt    = (float)($report['amt'] ?? 0);

// GST
$gstEnabled = ($s['inv_gst_enabled'] ?? '0') === '1';
$gstRate    = $gstEnabled ? (float)($s['inv_gst_rate'] ?? 18) : 0;
$gstAmt     = $gstEnabled ? round($baseAmt * $gstRate / 100, 2) : 0;
$totalAmt   = $baseAmt + $gstAmt;

// Clinic / Doctor info
$clinicName   = invFmt($s['inv_doctor_name'] ?? $s['clinic_name'] ?? '', 'Dr. Feelgood');
$qualification = invFmt($s['inv_qualification'] ?? '', '');
$clinicAddress = invFmt($s['inv_address'] ?? '', '');
$clinicPhone   = invFmt($s['inv_phone'] ?? $s['clinic_phone'] ?? '', '');
$clinicEmail   = invFmt($s['inv_email'] ?? '', '');
$showPan       = ($s['inv_show_pan'] ?? '0') === '1';
$pan           = invFmt($s['inv_pan'] ?? '', '');
$gstNumber     = invFmt($s['inv_gst_number'] ?? '', '');

// Patient
$patientName = invName($patient['fname'] ?? '', $patient['lname'] ?? '');
$patientId   = $patient['patient_id'] ?? $patient['id'];
$patientAge  = invFmt($patient['age'] ?? null, '');
$patientGender = match($patient['gender'] ?? '') { 'M' => 'Male', 'F' => 'Female', default => '' };
$patientContact = invFmt($patient['contact_no'] ?? null, '');
$patientAddress = invFmt($patient['address'] ?? null, '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($invoiceNo); ?> — Invoice</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 13px;
    color: #1f2937;
    background: #f3f4f6;
}

/* Print wrapper */
.inv-page {
    width: 794px;          /* A4 width at 96dpi */
    min-height: 1123px;    /* A4 height at 96dpi */
    margin: 20px auto;
    background: #fff;
    padding: 40px 44px;
    position: relative;
    box-shadow: 0 4px 24px rgba(0,0,0,.10);
}

/* ── Header ── */
.inv-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 20px;
    border-bottom: 2px solid #1d4ed8;
    margin-bottom: 22px;
}
.inv-clinic-name {
    font-size: 22px;
    font-weight: 800;
    color: #1d4ed8;
    line-height: 1.1;
}
.inv-qualification {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}
.inv-clinic-contact {
    font-size: 11px;
    color: #6b7280;
    margin-top: 6px;
    line-height: 1.7;
}
.inv-meta {
    text-align: right;
}
.inv-number {
    font-size: 18px;
    font-weight: 800;
    color: #1d4ed8;
}
.inv-meta-row {
    font-size: 11px;
    color: #6b7280;
    margin-top: 4px;
}
.inv-meta-row strong { color: #374151; }

/* ── Two-column info row ── */
.inv-info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 22px;
}
.inv-box {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 14px;
}
.inv-box-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #9ca3af;
    margin-bottom: 6px;
}
.inv-box-name {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 3px;
}
.inv-box-detail {
    font-size: 11px;
    color: #6b7280;
    line-height: 1.7;
}

/* ── Items table ── */
.inv-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 12px;
}
.inv-table thead th {
    background: #1d4ed8;
    color: #fff;
    padding: 8px 12px;
    font-weight: 600;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.inv-table thead th:last-child { text-align: right; }
.inv-table tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.inv-table tbody tr:last-child td { border-bottom: none; }
.inv-table tbody td:last-child { text-align: right; font-weight: 600; }
.inv-table tfoot td {
    padding: 7px 12px;
    font-size: 12px;
}
.inv-table tfoot tr.subtotal td { border-top: 1px solid #e5e7eb; }
.inv-table tfoot tr.total-row td {
    font-size: 14px;
    font-weight: 800;
    border-top: 2px solid #1d4ed8;
    color: #1d4ed8;
    padding-top: 10px;
}
.inv-table tfoot td:last-child { text-align: right; }

/* ── Medicines list in table ── */
.med-pill {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    border-radius: 12px;
    padding: 2px 9px;
    font-size: 11px;
    margin: 2px 3px 2px 0;
    font-weight: 500;
}

/* ── Footer ── */
.inv-footer {
    position: absolute;
    bottom: 32px;
    left: 44px;
    right: 44px;
    border-top: 1px solid #e5e7eb;
    padding-top: 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.inv-footer-note {
    font-size: 10px;
    color: #9ca3af;
}
.inv-signature {
    text-align: center;
}
.inv-signature-line {
    width: 140px;
    border-top: 1px solid #374151;
    margin-bottom: 4px;
}
.inv-signature-label {
    font-size: 10px;
    color: #6b7280;
}

/* ── Tax info row ── */
.inv-tax-row {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 18px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.inv-tax-row span strong { color: #374151; }

/* ── Print button ── */
.print-bar {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 12px 0 0;
    margin-bottom: 8px;
}
.print-btn {
    padding: 8px 28px;
    background: #1d4ed8;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.print-btn:hover { background: #1e40af; }
.close-btn {
    padding: 8px 18px;
    background: #fff;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

/* ── Print media ── */
@media print {
    body { background: #fff; }
    .print-bar { display: none !important; }
    .inv-page {
        margin: 0;
        box-shadow: none;
        padding: 24px 28px;
        min-height: unset;
    }
}
</style>
</head>
<body>

<!-- Print / Close bar -->
<div class="print-bar no-print">
    <button class="print-btn" onclick="window.print()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:5px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print / Save PDF
    </button>
    <button class="close-btn" onclick="window.close()">Close</button>
</div>

<!-- ── INVOICE PAGE ── -->
<div class="inv-page">

    <!-- Header -->
    <div class="inv-header">
        <div>
            <div class="inv-clinic-name"><?php echo htmlspecialchars($clinicName); ?></div>
            <?php if ($qualification !== '—'): ?>
            <div class="inv-qualification"><?php echo htmlspecialchars($qualification); ?></div>
            <?php endif; ?>
            <div class="inv-clinic-contact">
                <?php if ($clinicAddress !== '—'): ?>
                    <?php echo nl2br(htmlspecialchars($clinicAddress)); ?><br>
                <?php endif; ?>
                <?php if ($clinicPhone !== '—'): ?>
                    <span>&#9742; <?php echo htmlspecialchars($clinicPhone); ?></span>
                <?php endif; ?>
                <?php if ($clinicEmail !== '—'): ?>
                    <?php if ($clinicPhone !== '—'): ?> &nbsp;|&nbsp; <?php endif; ?>
                    <span>&#9993; <?php echo htmlspecialchars($clinicEmail); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="inv-meta">
            <div class="inv-number"><?php echo htmlspecialchars($invoiceNo); ?></div>
            <div class="inv-meta-row"><strong>Date:</strong> <?php echo $visitDate; ?></div>
            <div class="inv-meta-row" style="margin-top:2px;">
                <span style="background:#dcfce7;color:#166534;border-radius:20px;padding:2px 10px;font-size:10px;font-weight:700;">PAID</span>
            </div>
        </div>
    </div>

    <!-- Tax numbers row -->
    <?php if (($showPan && $pan !== '—') || ($gstEnabled && $gstNumber !== '—')): ?>
    <div class="inv-tax-row">
        <?php if ($showPan && $pan !== '—'): ?>
            <span><strong>PAN:</strong> <?php echo htmlspecialchars($pan); ?></span>
        <?php endif; ?>
        <?php if ($gstEnabled && $gstNumber !== '—'): ?>
            <span><strong>GSTIN:</strong> <?php echo htmlspecialchars($gstNumber); ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Billed To / Visit Info -->
    <div class="inv-info-row">
        <div class="inv-box">
            <div class="inv-box-title">Billed To</div>
            <div class="inv-box-name"><?php echo htmlspecialchars($patientName); ?></div>
            <div class="inv-box-detail">
                <strong>Patient ID:</strong> <?php echo htmlspecialchars($patientId); ?><br>
                <?php if ($patientAge !== ''): ?>
                    <strong>Age:</strong> <?php echo htmlspecialchars($patientAge); ?> yrs
                    <?php if ($patientGender !== ''): ?> &nbsp;|&nbsp; <strong>Gender:</strong> <?php echo htmlspecialchars($patientGender); ?><?php endif; ?><br>
                <?php elseif ($patientGender !== ''): ?>
                    <strong>Gender:</strong> <?php echo htmlspecialchars($patientGender); ?><br>
                <?php endif; ?>
                <?php if ($patientContact !== '—'): ?><strong>Contact:</strong> <?php echo htmlspecialchars($patientContact); ?><br><?php endif; ?>
                <?php if ($patientAddress !== '—'): ?><?php echo htmlspecialchars($patientAddress); ?><?php endif; ?>
            </div>
        </div>
        <div class="inv-box">
            <div class="inv-box-title">Visit Details</div>
            <div class="inv-box-detail" style="line-height:2;">
                <strong>Invoice No.:</strong> <?php echo htmlspecialchars($invoiceNo); ?><br>
                <strong>Visit Date:</strong> <?php echo $visitDate; ?><br>
                <strong>Report ID:</strong> #<?php echo $reportId; ?>
            </div>
        </div>
    </div>

    <!-- Line items table -->
    <table class="inv-table">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Description</th>
                <th style="width:120px;text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <!-- Consultation fee -->
            <tr>
                <td>1</td>
                <td>
                    <strong>Consultation Fee</strong>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                        Visit on <?php echo $visitDate; ?>
                    </div>
                </td>
                <td>&#8377;<?php echo number_format($baseAmt, 2); ?></td>
            </tr>
            <!-- Medicines row if any -->
            <?php if (!empty($medicines)): ?>
            <tr>
                <td>2</td>
                <td>
                    <strong>Medicines Prescribed</strong>
                    <div style="margin-top:4px;">
                        <?php foreach ($medicines as $med): ?>
                            <span class="med-pill"><?php echo htmlspecialchars($med); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div style="font-size:10px;color:#9ca3af;margin-top:4px;">For reference only — dispensed separately</div>
                </td>
                <td style="color:#9ca3af;font-weight:400;">—</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="subtotal">
                <td colspan="2" style="text-align:right;color:#6b7280;">Subtotal</td>
                <td>&#8377;<?php echo number_format($baseAmt, 2); ?></td>
            </tr>
            <?php if ($gstEnabled && $gstAmt > 0): ?>
            <tr>
                <td colspan="2" style="text-align:right;color:#6b7280;">GST (<?php echo $gstRate; ?>%)</td>
                <td>&#8377;<?php echo number_format($gstAmt, 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">Total</td>
                <td>&#8377;<?php echo number_format($totalAmt, 2); ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($report['notes'] ?? '')): ?>
    <div style="background:#f9fafb;border-radius:6px;padding:10px 14px;margin-bottom:20px;font-size:12px;color:#374151;">
        <strong>Notes:</strong> <?php echo htmlspecialchars($report['notes']); ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="inv-footer">
        <div class="inv-footer-note">
            Thank you for visiting <?php echo htmlspecialchars($clinicName); ?>.<br>
            This is a computer-generated invoice.
        </div>
        <div class="inv-signature">
            <div class="inv-signature-line"></div>
            <div class="inv-signature-label"><?php echo htmlspecialchars($clinicName); ?></div>
            <?php if ($qualification !== '—'): ?>
            <div class="inv-signature-label"><?php echo htmlspecialchars($qualification); ?></div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /inv-page -->

</body>
</html>
