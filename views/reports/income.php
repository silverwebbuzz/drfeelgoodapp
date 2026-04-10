<?php
$page_title  = 'Income Report';
$reportTitle = 'Income & Financial';
$reportIcon  = 'fas fa-rupee-sign';
$reportBase  = '/reports/income';
ob_start();

$summary = $reportData['summary'] ?? [];
$byDay   = $reportData['byDay']   ?? [];
$byMonth = $reportData['byMonth'] ?? [];
$byWeek  = $reportData['byWeek']  ?? [];
$period  = $reportData['period']  ?? 'week';
$year    = $reportData['year']    ?? date('Y');

require __DIR__ . '/_header.php';

function rFmt($n) { return '₹' . number_format((float)$n, 0); }
?>

<!-- Summary cards -->
<div class="report-grid-3" style="margin-bottom:16px;">
    <div class="stat-box">
        <div class="sv" style="color:#16a34a;"><?php echo rFmt($summary['total'] ?? 0); ?></div>
        <div class="sl">Total Revenue</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#2563eb;"><?php echo number_format((int)($summary['consultations'] ?? 0)); ?></div>
        <div class="sl">Consultations with Fee</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#d97706;"><?php echo rFmt($summary['avg_fee'] ?? 0); ?></div>
        <div class="sl">Avg Fee / Consultation</div>
    </div>
</div>

<!-- Daily / period chart -->
<?php if (!empty($byDay)): ?>
<div class="chart-card">
    <h6><i class="fas fa-chart-bar"></i> Revenue — Day by Day</h6>
    <canvas id="chartDay" height="80"></canvas>
</div>
<script>
(function(){
    const labels = <?php echo json_encode(array_column($byDay,'day')); ?>;
    const totals = <?php echo json_encode(array_map(fn($r)=>(int)$r['total'], $byDay)); ?>;
    const ctx = document.getElementById('chartDay').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue (₹)',
                data: totals,
                backgroundColor: CHART_COLORS.primary + 'bb',
                borderColor: CHART_COLORS.primary,
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero:true, ticks:{ callback: v => '₹'+v.toLocaleString() } }
            }
        }
    });
})();
</script>
<?php endif; ?>

<div class="report-grid-2">
    <!-- Monthly bar chart -->
    <?php if (!empty($byMonth)): ?>
    <div class="chart-card">
        <h6><i class="fas fa-calendar-alt"></i> Monthly Revenue — <?php echo $year; ?></h6>
        <canvas id="chartMonth" height="120"></canvas>
    </div>
    <script>
    (function(){
        const allMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const raw = <?php echo json_encode($byMonth); ?>;
        const totals = Array(12).fill(0);
        raw.forEach(r => { const m = parseInt(r.month.split('-')[1])-1; totals[m] = parseInt(r.total); });
        const ctx = document.getElementById('chartMonth').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: allMonths,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: totals,
                    backgroundColor: CHART_COLORS.green + 'bb',
                    borderColor: CHART_COLORS.green,
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display:false } },
                scales: { y: { beginAtZero:true, ticks:{ callback: v => '₹'+v.toLocaleString() } } }
            }
        });
    })();
    </script>
    <?php endif; ?>

    <!-- Weekly table -->
    <?php if (!empty($byWeek)): ?>
    <div class="chart-card" style="overflow-x:auto;">
        <h6><i class="fas fa-table"></i> Weekly Breakdown</h6>
        <table class="table" style="margin:0;font-size:12px;">
            <thead><tr><th>Week of</th><th style="text-align:right;">Revenue</th><th style="text-align:right;">Visits</th></tr></thead>
            <tbody>
            <?php foreach ($byWeek as $w): ?>
                <tr>
                    <td><?php echo date('d M', strtotime($w['week_start'])); ?></td>
                    <td style="text-align:right;font-weight:600;color:#16a34a;"><?php echo rFmt($w['total']); ?></td>
                    <td style="text-align:right;"><?php echo (int)$w['consultations']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Yearly year selector -->
<div style="font-size:12px;color:#9ca3af;margin-top:4px;">
    View by year:
    <?php for ($y = date('Y'); $y >= date('Y')-4; $y--): ?>
        <a href="<?php echo $reportBase; ?>?period=<?php echo $reportData['period']??'week'; ?>&year=<?php echo $y; ?>"
           style="margin:0 4px;<?php echo $y===$year?'font-weight:700;color:var(--primary)':'color:var(--gray-500)'; ?>">
            <?php echo $y; ?>
        </a>
    <?php endfor; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
