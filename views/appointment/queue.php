<?php
$page_title = 'Today\'s Queue';

$queue = $queueData['queue'] ?? [];
$stats = $queueData['stats'] ?? [];
$date  = $queueData['date']  ?? date('Y-m-d');
$today = date('Y-m-d');
$tableId = 'queueTable';
$compact = false;
ob_start();
?>
<style>
.queue-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:10px; margin-bottom:14px; }
.q-stat { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:10px 14px; }
.q-stat .val { font-size:22px; font-weight:700; line-height:1; }
.q-stat .lbl { font-size:11px; color:#6b7280; margin-top:2px; }
.token-badge { display:inline-block; width:36px; height:36px; border-radius:50%; background:var(--primary); color:#fff; font-weight:700; font-size:13px; line-height:36px; text-align:center; }
.status-btns .btn { padding:3px 8px; font-size:11px; }
.date-nav { display:flex; align-items:center; gap:8px; }
#filterTabs-queueTable .nav-link { padding:4px 12px; font-size:12px; }
.queue-row td { vertical-align:middle; }
.time-col { text-align:center; min-width:64px; }
.time-col .tlabel { font-size:10px; color:#9ca3af; display:block; }
.queue-row[data-status="in_consultation"] { background:#eff6ff; }
.queue-row[data-status="completed"] { opacity:.75; }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h1 class="page-title" style="margin:0;">Today's Queue</h1>
    <div style="display:flex;gap:8px;align-items:center;">
        <div class="date-nav">
            <a href="/queue?date=<?php echo date('Y-m-d', strtotime($date . ' -1 day')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
            <input type="date" id="dateJump" class="form-control form-control-sm" value="<?php echo $date; ?>" style="width:140px;" onchange="location='/queue?date='+this.value">
            <?php if ($date !== $today): ?>
                <a href="/queue" class="btn btn-outline-primary btn-sm">Today</a>
            <?php endif; ?>
            <a href="/queue?date=<?php echo date('Y-m-d', strtotime($date . ' +1 day')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
        </div>
        <a href="/walkin" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Walk-in Token</a>
    </div>
</div>

<!-- Stats -->
<div class="queue-grid">
    <div class="q-stat">
        <div class="val"><?php echo (int)($stats['total'] ?? 0); ?></div>
        <div class="lbl">Total</div>
    </div>
    <div class="q-stat" style="border-color:#f59e0b;">
        <div class="val" style="color:#d97706;"><?php echo (int)($stats['waiting'] ?? 0); ?></div>
        <div class="lbl">Waiting</div>
    </div>
    <div class="q-stat" style="border-color:#3b82f6;">
        <div class="val" style="color:#2563eb;"><?php echo (int)($stats['in_consultation'] ?? 0); ?></div>
        <div class="lbl">In Consult</div>
    </div>
    <div class="q-stat" style="border-color:#22c55e;">
        <div class="val" style="color:#16a34a;"><?php echo (int)($stats['completed'] ?? 0); ?></div>
        <div class="lbl">Completed</div>
    </div>
</div>

<div class="card" style="margin-bottom:0;">
    <div class="card-body" style="padding:0 0 0 0;">
        <?php require __DIR__ . '/_queue_table.php'; ?>
    </div>
</div>

<script>
// Auto-refresh every 60s (only if page is visible)
let refreshTimer = setTimeout(() => location.reload(), 60000);
document.addEventListener('visibilitychange', () => {
    if (document.hidden) clearTimeout(refreshTimer);
    else refreshTimer = setTimeout(() => location.reload(), 60000);
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
