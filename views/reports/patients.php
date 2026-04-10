<?php
$page_title  = 'Patient Analytics';
$reportTitle = 'Patient Analytics';
$reportIcon  = 'fas fa-users';
$reportBase  = '/reports/patients';
ob_start();

$byDay        = $reportData['byDay']        ?? [];
$byMonth      = $reportData['byMonth']      ?? [];
$gender       = $reportData['gender']       ?? [];
$ageGroups    = $reportData['ageGroups']    ?? [];
$complaints   = $reportData['complaints']   ?? [];
$newReturning = $reportData['newReturning'] ?? [];
$year         = $reportData['year']         ?? date('Y');

require __DIR__ . '/_header.php';

$newPts  = (int)($newReturning['new_patients']       ?? 0);
$retPts  = (int)($newReturning['returning_patients'] ?? 0);
$totalPts = $newPts + $retPts;
?>

<!-- Summary cards -->
<div class="report-grid-4" style="margin-bottom:16px;">
    <div class="stat-box">
        <div class="sv" style="color:#2563eb;"><?php echo number_format($totalPts); ?></div>
        <div class="sl">Total Visits</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#16a34a;"><?php echo number_format($newPts); ?></div>
        <div class="sl">New Patients</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#d97706;"><?php echo number_format($retPts); ?></div>
        <div class="sl">Returning Patients</div>
    </div>
    <div class="stat-box">
        <div class="sv" style="color:#7c3aed;">
            <?php echo $totalPts > 0 ? round($newPts/$totalPts*100).'%' : '—'; ?>
        </div>
        <div class="sl">New Patient Rate</div>
    </div>
</div>

<div class="report-grid-2">

    <!-- New registrations by day chart -->
    <?php if (!empty($byDay)): ?>
    <div class="chart-card">
        <h6><i class="fas fa-chart-line"></i> New Registrations — Day by Day</h6>
        <canvas id="chartDay" height="120"></canvas>
    </div>
    <script>
    (function(){
        const labels = <?php echo json_encode(array_column($byDay,'day')); ?>;
        const counts = <?php echo json_encode(array_map(fn($r)=>(int)$r['count'], $byDay)); ?>;
        const ctx = document.getElementById('chartDay').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'New Patients',
                    data: counts,
                    borderColor: CHART_COLORS.primary,
                    backgroundColor: makeGradient(ctx, CHART_COLORS.primary),
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                }]
            },
            options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } } }
        });
    })();
    </script>
    <?php endif; ?>

    <!-- Gender + New vs Returning doughnut -->
    <div class="chart-card">
        <h6><i class="fas fa-venus-mars"></i> Gender Distribution</h6>
        <canvas id="chartGender" height="120"></canvas>
    </div>
    <script>
    (function(){
        const raw = <?php echo json_encode($gender); ?>;
        const labelMap = { 'M':'Male','F':'Female','':'Unknown' };
        const labels = raw.map(r => labelMap[r.gender] || r.gender || 'Unknown');
        const data   = raw.map(r => parseInt(r.count));
        new Chart(document.getElementById('chartGender'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor:[CHART_COLORS.primary,CHART_COLORS.red,CHART_COLORS.gray], borderWidth:2 }]
            },
            options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
        });
    })();
    </script>

</div>

<div class="report-grid-2">

    <!-- Age groups bar -->
    <?php if (!empty($ageGroups)): ?>
    <div class="chart-card">
        <h6><i class="fas fa-chart-bar"></i> Age Groups</h6>
        <canvas id="chartAge" height="120"></canvas>
    </div>
    <script>
    (function(){
        const raw = <?php echo json_encode($ageGroups); ?>;
        new Chart(document.getElementById('chartAge'), {
            type: 'bar',
            data: {
                labels: raw.map(r=>r.age_group),
                datasets: [{ label:'Patients', data: raw.map(r=>parseInt(r.count)),
                    backgroundColor: CHART_COLORS.purple+'bb', borderColor:CHART_COLORS.purple, borderWidth:1, borderRadius:4 }]
            },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
        });
    })();
    </script>
    <?php endif; ?>

    <!-- Top complaints -->
    <?php if (!empty($complaints)): ?>
    <div class="chart-card" style="overflow-x:auto;">
        <h6><i class="fas fa-notes-medical"></i> Top Chief Complaints</h6>
        <?php $maxC = (int)($complaints[0]['count'] ?? 1); ?>
        <?php foreach ($complaints as $i => $c): ?>
        <div style="margin-bottom:7px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:2px;">
                <span><?php echo htmlspecialchars(ucfirst($c['chief'])); ?></span>
                <span style="font-weight:700;"><?php echo (int)$c['count']; ?></span>
            </div>
            <div style="background:#f3f4f6;border-radius:4px;height:6px;">
                <div style="background:var(--primary);height:6px;border-radius:4px;width:<?php echo round($c['count']/$maxC*100); ?>%;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Monthly registrations -->
<?php if (!empty($byMonth)): ?>
<div class="chart-card">
    <h6><i class="fas fa-calendar-alt"></i> Monthly Registrations — <?php echo $year; ?></h6>
    <canvas id="chartMonth" height="70"></canvas>
</div>
<script>
(function(){
    const allMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const raw = <?php echo json_encode($byMonth); ?>;
    const counts = Array(12).fill(0);
    raw.forEach(r => { counts[parseInt(r.month.split('-')[1])-1] = parseInt(r.count); });
    const ctx = document.getElementById('chartMonth').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: allMonths,
            datasets: [{ label:'New Patients', data:counts,
                backgroundColor:CHART_COLORS.green+'bb', borderColor:CHART_COLORS.green, borderWidth:1, borderRadius:4 }]
        },
        options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
    });
})();
</script>
<?php endif; ?>

<!-- Year selector -->
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
