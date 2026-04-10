<?php
/**
 * Shared report header partial.
 * Requires: $reportData (array with 'period','from','to'), $reportTitle, $reportIcon, $reportBase (URL slug e.g. '/reports/income')
 */
$period = $reportData['period'] ?? 'week';
$from   = $reportData['from']   ?? date('Y-m-d');
$to     = $reportData['to']     ?? date('Y-m-d');
?>
<style>
/* ── Report shared styles ──────────────────────────────────────── */
.report-period-bar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
.period-btn { padding:5px 14px; border:2px solid #e5e7eb; border-radius:20px; background:#fff; font-size:12px; font-weight:600; color:#6b7280; cursor:pointer; transition:.15s; text-decoration:none; }
.period-btn:hover { border-color:#93c5fd; color:var(--primary); }
.period-btn.active { border-color:var(--primary); background:var(--primary); color:#fff; }
.stat-box { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px 18px; }
.stat-box .sv { font-size:26px; font-weight:800; line-height:1; }
.stat-box .sl { font-size:11px; color:#6b7280; margin-top:3px; }
.chart-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:16px; margin-bottom:16px; }
.chart-card h6 { font-size:12px; font-weight:700; color:#374151; margin-bottom:12px; text-transform:uppercase; letter-spacing:.5px; }
.report-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
.report-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }
.report-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
@media(max-width:768px){
    .report-grid-4,.report-grid-3 { grid-template-columns:1fr 1fr; }
    .report-grid-2 { grid-template-columns:1fr; }
}
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h1 class="page-title" style="margin:0;">
        <i class="<?php echo $reportIcon; ?>"></i> <?php echo $reportTitle; ?>
    </h1>
</div>

<!-- Period picker -->
<div class="report-period-bar">
    <?php
    $periods = ['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'];
    foreach ($periods as $key => $label):
        $active = ($period === $key) ? 'active' : '';
    ?>
        <a href="<?php echo $reportBase; ?>?period=<?php echo $key; ?>" class="period-btn <?php echo $active; ?>"><?php echo $label; ?></a>
    <?php endforeach; ?>

    <!-- Custom range -->
    <form method="GET" action="<?php echo $reportBase; ?>" style="display:flex;align-items:center;gap:6px;margin-left:4px;">
        <input type="hidden" name="period" value="custom">
        <input type="date" name="from" value="<?php echo htmlspecialchars($period==='custom'?$from:date('Y-m-01')); ?>" class="form-control form-control-sm" style="width:130px;">
        <span style="color:#9ca3af;font-size:12px;">to</span>
        <input type="date" name="to" value="<?php echo htmlspecialchars($period==='custom'?$to:date('Y-m-d')); ?>" class="form-control form-control-sm" style="width:130px;">
        <button type="submit" class="btn btn-secondary btn-sm">Go</button>
    </form>

    <span style="margin-left:auto;font-size:11px;color:#9ca3af;">
        <?php echo date('d M Y', strtotime($from)); ?> – <?php echo date('d M Y', strtotime($to)); ?>
    </span>
</div>

<!-- Chart.js (loaded once via flag) -->
<?php if (empty($GLOBALS['__chartjsLoaded'])): $GLOBALS['__chartjsLoaded'] = true; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#6b7280';
// Shared color palette
const CHART_COLORS = {
    primary : '#3b82f6',
    green   : '#22c55e',
    yellow  : '#f59e0b',
    red     : '#ef4444',
    purple  : '#8b5cf6',
    cyan    : '#06b6d4',
    gray    : '#9ca3af',
};
function makeGradient(ctx, color) {
    const g = ctx.createLinearGradient(0,0,0,200);
    g.addColorStop(0, color + '33');
    g.addColorStop(1, color + '00');
    return g;
}
</script>
<?php endif; ?>
