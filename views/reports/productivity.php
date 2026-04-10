<?php
$page_title  = 'Doctor Productivity';
$reportTitle = 'Doctor Productivity';
$reportIcon  = 'fas fa-stethoscope';
$reportBase  = '/reports/productivity';
ob_start();

$summary      = $reportData['summary']      ?? [];
$byDay        = $reportData['byDay']        ?? [];
$consultTrend = $reportData['consultTrend'] ?? [];
$busyDays     = $reportData['busyDays']     ?? [];

require __DIR__ . '/_header.php';
?>

<!-- Summary cards -->
<div class="report-grid-4" style="margin-bottom:16px;">
    <div class="stat-box">
        <div class="sv" style="color:#2563eb;"><?php echo number_format((int)($summary['total_seen'] ?? 0)); ?></div>
        <div class="sl">Patients Seen</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#16a34a;"><?php echo (int)($summary['working_days'] ?? 0); ?></div>
        <div class="sl">Working Days</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#d97706;"><?php echo $summary['avg_per_day'] ?? '—'; ?></div>
        <div class="sl">Avg Patients / Day</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#7c3aed;">
            <?php echo $summary['peak_count'] ? $summary['peak_count'].' on '.date('d M', strtotime($summary['peak_day'])) : '—'; ?>
        </div>
        <div class="sl">Peak Day</div>
    </div>
</div>

<!-- Patients seen per day -->
<?php if (!empty($byDay)): ?>
<div class="chart-card">
    <h6><i class="fas fa-chart-bar"></i> Patients Seen — Day by Day</h6>
    <canvas id="chartDay" height="80"></canvas>
</div>
<script>
(function(){
    const labels = <?php echo json_encode(array_column($byDay,'day')); ?>;
    const seen   = <?php echo json_encode(array_map(fn($r)=>(int)$r['seen'], $byDay)); ?>;
    const ctx    = document.getElementById('chartDay').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Patients Seen',
                data: seen,
                backgroundColor: CHART_COLORS.primary + 'bb',
                borderColor: CHART_COLORS.primary,
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend:{ display:false } },
            scales: { y:{ beginAtZero:true, ticks:{ precision:0 } } }
        }
    });
})();
</script>
<?php endif; ?>

<div class="report-grid-2">

    <!-- Consult time trend -->
    <?php if (!empty($consultTrend)): ?>
    <div class="chart-card">
        <h6><i class="fas fa-stopwatch"></i> Avg Consultation Time (min) — Trend</h6>
        <canvas id="chartConsult" height="120"></canvas>
    </div>
    <script>
    (function(){
        const labels = <?php echo json_encode(array_column($consultTrend,'day')); ?>;
        const mins   = <?php echo json_encode(array_map(fn($r)=>(float)$r['avg_minutes'], $consultTrend)); ?>;
        const ctx    = document.getElementById('chartConsult').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Avg Minutes',
                    data: mins,
                    borderColor: CHART_COLORS.yellow,
                    backgroundColor: makeGradient(ctx, CHART_COLORS.yellow),
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                }]
            },
            options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    })();
    </script>
    <?php endif; ?>

    <!-- Busiest days of week -->
    <?php if (!empty($busyDays)): ?>
    <div class="chart-card">
        <h6><i class="fas fa-calendar-week"></i> Load by Day of Week</h6>
        <canvas id="chartDow" height="120"></canvas>
    </div>
    <script>
    (function(){
        const raw = <?php echo json_encode($busyDays); ?>;
        new Chart(document.getElementById('chartDow'), {
            type: 'radar',
            data: {
                labels: raw.map(r=>r.day_name),
                datasets: [{
                    label: 'Appointments',
                    data: raw.map(r=>parseInt(r.total)),
                    backgroundColor: CHART_COLORS.cyan + '33',
                    borderColor: CHART_COLORS.cyan,
                    pointBackgroundColor: CHART_COLORS.cyan,
                    borderWidth: 2,
                }]
            },
            options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ r:{ beginAtZero:true, ticks:{precision:0,stepSize:1} } } }
        });
    })();
    </script>
    <?php endif; ?>

</div>

<?php if (empty($byDay)): ?>
<div style="text-align:center;padding:60px;color:#9ca3af;">
    <i class="fas fa-stethoscope" style="font-size:32px;display:block;margin-bottom:10px;"></i>
    No completed consultations found for this period.
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
