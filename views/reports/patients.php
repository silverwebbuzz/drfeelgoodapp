<?php
$page_title  = 'Patient Analytics';
$reportTitle = 'Patient Analytics';
$reportIcon  = 'fas fa-users';
$reportBase  = '/reports/patients';
ob_start();

$byDay        = $reportData['byDay']        ?? [];
$byWeek       = $reportData['byWeek']       ?? [];
$byMonth      = $reportData['byMonth']      ?? [];
$gender       = $reportData['gender']       ?? [];
$ageGroups    = $reportData['ageGroups']    ?? [];
$complaints   = $reportData['complaints']   ?? [];
$newReturning = $reportData['newReturning'] ?? [];
$period       = $reportData['period']       ?? 'week';
$year         = $reportData['year']         ?? date('Y');

require __DIR__ . '/_header.php';

$newPts   = (int)($newReturning['new_patients']       ?? 0);
$retPts   = (int)($newReturning['returning_patients'] ?? 0);
$totalPts = $newPts + $retPts;
$defaultG = in_array($period, ['today','week']) ? 'day' : ($period === 'month' ? 'week' : 'month');

// JS dataset prep
$dayLabels  = json_encode(array_column($byDay,  'day'));
$dayVals    = json_encode(array_map(fn($r)=>(int)$r['count'], $byDay));

$weekLabels = json_encode(array_map(fn($r)=>date('d M', strtotime($r['week_start'])), $byWeek));
$weekVals   = json_encode(array_map(fn($r)=>(int)$r['count'], $byWeek));

$allMonths  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$mCounts    = array_fill(0, 12, 0);
foreach ($byMonth as $r) { $mCounts[(int)explode('-',$r['month'])[1]-1] = (int)$r['count']; }
$monthLabels = json_encode($allMonths);
$monthVals   = json_encode($mCounts);
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

<!-- New registrations trend with toggle -->
<div class="chart-card">
    <h6><i class="fas fa-chart-line"></i> New Patient Registrations <span id="regPills"></span></h6>
    <canvas id="chartReg" height="80"></canvas>
</div>
<script>
(function(){
    const ctx = document.getElementById('chartReg').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: { labels:[], datasets:[{
            label:'New Patients', data:[],
            borderColor:CHART_COLORS.primary,
            backgroundColor: makeGradient(ctx, CHART_COLORS.primary),
            fill:true, tension:0.4, pointRadius:3,
        }]},
        options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
    });

    const datasets = {
        day:   { labels: <?php echo $dayLabels; ?>,   values: <?php echo $dayVals; ?> },
        week:  { labels: <?php echo $weekLabels; ?>,  values: <?php echo $weekVals; ?> },
        month: { labels: <?php echo $monthLabels; ?>, values: <?php echo $monthVals; ?> },
    };
    document.getElementById('regPills').outerHTML = buildTogglePills(['day','week','month'], '<?php echo $defaultG; ?>');
    chartToggle('chartReg', datasets, '<?php echo $defaultG; ?>');
})();
</script>

<div class="report-grid-2">

    <!-- Gender doughnut — no toggle needed, all-time data -->
    <div class="chart-card">
        <h6><i class="fas fa-venus-mars"></i> Gender Distribution (All Time)</h6>
        <canvas id="chartGender" height="120"></canvas>
    </div>
    <script>
    (function(){
        const raw = <?php echo json_encode($gender); ?>;
        const labelMap = { 'M':'Male','F':'Female','':'Unknown' };
        new Chart(document.getElementById('chartGender'), {
            type: 'doughnut',
            data: {
                labels: raw.map(r => labelMap[r.gender] || r.gender || 'Unknown'),
                datasets: [{ data: raw.map(r=>parseInt(r.count)),
                    backgroundColor:[CHART_COLORS.primary,CHART_COLORS.red,CHART_COLORS.gray], borderWidth:2 }]
            },
            options:{ responsive:true, plugins:{legend:{position:'bottom'}} }
        });
    })();
    </script>

    <!-- Age groups — no toggle, all-time -->
    <?php if (!empty($ageGroups)): ?>
    <div class="chart-card">
        <h6><i class="fas fa-chart-bar"></i> Age Groups (All Time)</h6>
        <canvas id="chartAge" height="120"></canvas>
    </div>
    <script>
    (function(){
        const raw = <?php echo json_encode($ageGroups); ?>;
        new Chart(document.getElementById('chartAge'), {
            type: 'bar',
            data: {
                labels: raw.map(r=>r.age_group),
                datasets:[{ label:'Patients', data:raw.map(r=>parseInt(r.count)),
                    backgroundColor:CHART_COLORS.purple+'bb', borderColor:CHART_COLORS.purple, borderWidth:1, borderRadius:4 }]
            },
            options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
        });
    })();
    </script>
    <?php endif; ?>

</div>

<!-- Top complaints -->
<?php if (!empty($complaints)): ?>
<div class="chart-card" style="overflow-x:auto;">
    <h6><i class="fas fa-notes-medical"></i> Top Chief Complaints (All Time)</h6>
    <?php $maxC = (int)($complaints[0]['count'] ?? 1); ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 24px;">
    <?php foreach ($complaints as $c): ?>
    <div style="margin-bottom:5px;">
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
</div>
<?php endif; ?>

<!-- Year selector -->
<div style="font-size:12px;color:#9ca3af;margin-top:8px;">
    View monthly by year:
    <?php for ($y = date('Y'); $y >= date('Y')-4; $y--): ?>
        <a href="<?php echo $reportBase; ?>?period=<?php echo $period; ?>&year=<?php echo $y; ?>"
           style="margin:0 4px;<?php echo $y===$year?'font-weight:700;color:var(--primary)':'color:var(--gray-500)'; ?>">
            <?php echo $y; ?>
        </a>
    <?php endfor; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
