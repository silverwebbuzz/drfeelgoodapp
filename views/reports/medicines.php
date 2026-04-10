<?php
$page_title  = 'Medicines Report';
$reportTitle = 'Medicines';
$reportIcon  = 'fas fa-pills';
$reportBase  = '/reports/medicines';
ob_start();

$topMeds = $reportData['topMeds'] ?? [];
$byDay   = $reportData['byDay']   ?? [];

require __DIR__ . '/_header.php';

$totalPrescriptions = array_sum(array_column($topMeds, 'count'));
$uniqueMeds = count($topMeds);
?>

<!-- Summary -->
<div class="report-grid-3" style="margin-bottom:16px;">
    <div class="stat-box">
        <div class="sv" style="color:#2563eb;"><?php echo number_format($uniqueMeds); ?></div>
        <div class="sl">Unique Medicines</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#16a34a;"><?php echo number_format($totalPrescriptions); ?></div>
        <div class="sl">Total Prescriptions (top <?php echo $uniqueMeds; ?>)</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#d97706;">
            <?php echo !empty($topMeds) ? htmlspecialchars(ucfirst($topMeds[0]['medicine'])) : '—'; ?>
        </div>
        <div class="sl">Most Prescribed</div>
    </div>
</div>

<div class="report-grid-2">

    <!-- Horizontal bar chart -->
    <?php if (!empty($topMeds)): ?>
    <div class="chart-card">
        <h6><i class="fas fa-chart-bar"></i> Top Medicines by Prescription Count</h6>
        <canvas id="chartMeds" height="<?php echo min(count($topMeds) * 22, 300); ?>"></canvas>
    </div>
    <script>
    (function(){
        const raw = <?php echo json_encode($topMeds); ?>;
        const colors = [
            CHART_COLORS.primary, CHART_COLORS.green, CHART_COLORS.yellow,
            CHART_COLORS.purple, CHART_COLORS.cyan, CHART_COLORS.red,
        ];
        new Chart(document.getElementById('chartMeds'), {
            type: 'bar',
            data: {
                labels: raw.map(r => r.medicine.charAt(0).toUpperCase() + r.medicine.slice(1)),
                datasets: [{
                    label: 'Prescriptions',
                    data: raw.map(r => parseInt(r.count)),
                    backgroundColor: raw.map((_,i) => colors[i % colors.length] + 'bb'),
                    borderColor:     raw.map((_,i) => colors[i % colors.length]),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend:{ display:false } },
                scales: { x:{ beginAtZero:true, ticks:{ precision:0 } } }
            }
        });
    })();
    </script>
    <?php endif; ?>

    <!-- Ranked table -->
    <?php if (!empty($topMeds)): ?>
    <div class="chart-card" style="overflow-x:auto;">
        <h6><i class="fas fa-table"></i> Ranked List</h6>
        <table class="table" style="margin:0;font-size:12px;">
            <thead><tr><th>#</th><th>Medicine</th><th style="text-align:right;">Count</th><th style="text-align:right;">Share</th></tr></thead>
            <tbody>
            <?php foreach ($topMeds as $i => $m): ?>
            <tr>
                <td style="color:#9ca3af;"><?php echo $i+1; ?></td>
                <td style="font-weight:600;"><?php echo htmlspecialchars(ucfirst($m['medicine'])); ?></td>
                <td style="text-align:right;"><?php echo number_format((int)$m['count']); ?></td>
                <td style="text-align:right;color:#9ca3af;">
                    <?php echo $totalPrescriptions > 0 ? round($m['count']/$totalPrescriptions*100, 1).'%' : '—'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<!-- Prescription activity over time -->
<?php if (!empty($byDay)): ?>
<div class="chart-card">
    <h6><i class="fas fa-chart-line"></i> Prescription Activity — Day by Day</h6>
    <canvas id="chartDay" height="70"></canvas>
</div>
<script>
(function(){
    const labels = <?php echo json_encode(array_column($byDay,'day')); ?>;
    const counts = <?php echo json_encode(array_map(fn($r)=>(int)$r['prescriptions'], $byDay)); ?>;
    const ctx = document.getElementById('chartDay').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets:[{
                label: 'Prescriptions',
                data: counts,
                borderColor: CHART_COLORS.purple,
                backgroundColor: makeGradient(ctx, CHART_COLORS.purple),
                fill: true,
                tension: 0.4,
                pointRadius: 3,
            }]
        },
        options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
    });
})();
</script>
<?php endif; ?>

<?php if (empty($topMeds)): ?>
<div style="text-align:center;padding:60px;color:#9ca3af;">
    <i class="fas fa-pills" style="font-size:32px;display:block;margin-bottom:10px;"></i>
    No medicine data found for this period.
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
