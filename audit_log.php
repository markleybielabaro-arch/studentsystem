<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();
if(!is_admin()){ header('Location: dashboard.php'); exit; }

// Pagination settings
$logsPerPage = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $logsPerPage;

// Count total logs
$totalLogs = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$totalPages = ceil($totalLogs / $logsPerPage);

// Fetch logs for current page
$stmt = $pdo->prepare("SELECT a.*, u.username FROM audit_logs a LEFT JOIN users u ON a.user_id=u.id ORDER BY a.timestamp DESC LIMIT :offset, :limit");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $logsPerPage, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

// Fetch data for chart (last 1000 logs)
$chartStmt = $pdo->query("SELECT action, COUNT(*) as count FROM audit_logs GROUP BY action");
$chartData = $chartStmt->fetchAll(PDO::FETCH_KEY_PAIR);

include 'includes/header.php';
?>

<style>
.card { border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 20px; }
.table-responsive { overflow-x:auto; }
.sticky-table th { position: sticky; top:0; background: linear-gradient(90deg,#0b67b2,#2b9fff); color:#fff; z-index:1; }
@media(max-width:767px) {
    .flex-wrap-mobile { flex-wrap: wrap !important; }
    .list-group-item { font-size: 14px; }
}
</style>

<div class="container-fluid mt-4">
  <h4 class="mb-4 text-center">📝 Audit Dashboard</h4>

  <!-- Charts & Recent Logs -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
      <div class="card">
        <h5 class="mb-3">Actions Summary</h5>
        <canvas id="actionChart" height="250"></canvas>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div class="card">
        <h5 class="mb-3">Recent Logs</h5>
        <?php if(empty($logs)): ?>
            <div class="alert alert-info">No logs available.</div>
        <?php else: ?>
            <ul class="list-group list-group-flush" style="max-height:300px; overflow-y:auto;">
                <?php foreach(array_slice($logs, 0, 10) as $l): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap flex-wrap-mobile">
                    <div>
                        <strong><?= htmlspecialchars($l['username'] ?? 'system') ?></strong> - <?= htmlspecialchars($l['action']) ?>
                    </div>
                    <small class="text-muted"><?= date('H:i', strtotime($l['timestamp'])) ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Logs Table -->
  <div class="card">
    <h5 class="mb-3">Audit Logs (Page <?= $page ?> / <?= $totalPages ?>)</h5>
    <div class="table-responsive">
      <table class="table table-striped table-hover sticky-table mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Action</th>
            <th>Details</th>
            <th>When</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($logs as $i=>$l): 
            $rowClass = '';
            if(stripos($l['action'], 'delete') !== false) $rowClass = 'table-danger';
            elseif(stripos($l['action'], 'edit') !== false) $rowClass = 'table-warning';
            elseif(stripos($l['action'], 'add') !== false) $rowClass = 'table-success';
        ?>
          <tr class="<?= $rowClass ?>">
            <td><?= ($offset + $i + 1) ?></td>
            <td><?= htmlspecialchars($l['username'] ?? 'system') ?></td>
            <td><?= htmlspecialchars($l['action']) ?></td>
            <td><?= htmlspecialchars($l['details']) ?></td>
            <td><?= date('Y-m-d H:i:s', strtotime($l['timestamp'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center mt-3 flex-wrap">
        <?php for($p=1; $p<=$totalPages; $p++): ?>
          <li class="page-item <?php if($p==$page) echo 'active'; ?>">
            <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('actionChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($chartData)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($chartData)) ?>,
            backgroundColor: ['#28a745','#ffc107','#dc3545','#007bff','#6c757d'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
