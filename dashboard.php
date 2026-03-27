<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

$stats = getDashboardStats($user['id'], $user['role']);

// Recent incidents
$recentSql = $user['role'] === 'admin'
    ? "SELECT i.*, u.name AS reporter_name FROM incidents i LEFT JOIN users u ON i.reported_by=u.id ORDER BY i.created_at DESC LIMIT 8"
    : "SELECT i.*, u.name AS reporter_name FROM incidents i LEFT JOIN users u ON i.reported_by=u.id WHERE i.reported_by=? OR i.assigned_to=? ORDER BY i.created_at DESC LIMIT 8";
$stmt = db()->prepare($recentSql);
$user['role'] === 'admin' ? $stmt->execute() : $stmt->execute([$user['id'], $user['id']]);
$recentIncidents = $stmt->fetchAll();

// Chart data (last 7 days)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = db()->prepare("SELECT COUNT(*) FROM incidents WHERE DATE(created_at)=?");
    $stmt->execute([$date]);
    $chartData[] = ['date' => date('M j', strtotime($date)), 'count' => (int)$stmt->fetchColumn()];
}
?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item active">Overview</li>
        </ol></nav>
    </div>
    <a href="incidents/create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Incident
    </a>
</div>

<?= renderFlash() ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-<?= Auth::isAdmin() ? '2' : '3' ?>">
        <div class="stat-card bg-total">
            <div class="stat-number"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Incidents</div>
            <i class="bi bi-collection stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-<?= Auth::isAdmin() ? '2' : '3' ?>">
        <div class="stat-card bg-open">
            <div class="stat-number"><?= $stats['open'] ?></div>
            <div class="stat-label">Open</div>
            <i class="bi bi-exclamation-octagon stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-<?= Auth::isAdmin() ? '2' : '3' ?>">
        <div class="stat-card bg-progress">
            <div class="stat-number"><?= $stats['in_progress'] ?></div>
            <div class="stat-label">In Progress</div>
            <i class="bi bi-arrow-repeat stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-<?= Auth::isAdmin() ? '2' : '3' ?>">
        <div class="stat-card bg-resolved">
            <div class="stat-number"><?= $stats['resolved'] ?></div>
            <div class="stat-label">Resolved</div>
            <i class="bi bi-check-circle stat-icon"></i>
        </div>
    </div>
    <?php if (Auth::isAdmin()): ?>
    <div class="col-6 col-lg-2">
        <div class="stat-card bg-users">
            <div class="stat-number"><?= $stats['users'] ?></div>
            <div class="stat-label">Active Users</div>
            <i class="bi bi-people stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="stat-card bg-critical">
            <div class="stat-number"><?= $stats['critical'] ?></div>
            <div class="stat-label">Critical</div>
            <i class="bi bi-fire stat-icon"></i>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row g-4">
    <!-- Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart me-2 text-primary"></i>Incidents — Last 7 Days</span>
            </div>
            <div class="card-body">
                <canvas id="incidentChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <!-- Status breakdown -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-2 text-primary"></i>Status Breakdown</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="statusChart" style="max-height:200px"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Incidents -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Incidents</span>
        <a href="incidents/list.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-container">
        <?php if (empty($recentIncidents)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p class="mb-2 fw-semibold">No incidents yet</p>
            <a href="incidents/create.php" class="btn btn-primary btn-sm">Report First Incident</a>
        </div>
        <?php else: ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th><th>Title</th><th>Priority</th><th>Status</th>
                    <th>Reported By</th><th>Date</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentIncidents as $i): ?>
            <tr>
                <td class="text-muted small">#<?= $i['id'] ?></td>
                <td><a href="incidents/view.php?id=<?= $i['id'] ?>" class="incident-link"><?= Security::e(substr($i['title'],0,50)) ?><?= strlen($i['title'])>50?'…':'' ?></a></td>
                <td><?= getPriorityBadge($i['priority']) ?></td>
                <td><?= getStatusBadge($i['status']) ?></td>
                <td class="small"><?= Security::e($i['reporter_name'] ?? '—') ?></td>
                <td class="small text-muted" data-bs-toggle="tooltip" title="<?= Security::e($i['created_at']) ?>"><?= timeAgo($i['created_at']) ?></td>
                <td><a href="incidents/view.php?id=<?= $i['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
// Bar chart
const ctx = document.getElementById('incidentChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($chartData, 'date')) ?>,
        datasets: [{
            label: 'Incidents',
            data: <?= json_encode(array_column($chartData, 'count')) ?>,
            backgroundColor: 'rgba(37,99,235,0.15)',
            borderColor: '#2563EB',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{stepSize:1}}} }
});

// Donut chart
const ctx2 = document.getElementById('statusChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Open','In Progress','Resolved','Closed'],
        datasets: [{
            data: [<?= $stats['open']?>, <?= $stats['in_progress']?>, <?= $stats['resolved']?>, <?= ($stats['total'] - $stats['open'] - $stats['in_progress'] - $stats['resolved']) ?>],
            backgroundColor: ['#EF4444','#F59E0B','#10B981','#6B7280'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: { responsive:true, cutout:'70%', plugins:{ legend:{ position:'bottom', labels:{boxWidth:12, font:{size:12}} } } }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
