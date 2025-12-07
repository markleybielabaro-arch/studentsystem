<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$user_role   = $_SESSION['user']['role'] ?? 'teacher';
$user_blocks = $_SESSION['user']['blocks'] ?? [];
$is_admin    = is_admin();

// KPIs
if($is_admin){
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalModules  = $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
} else {
    if(!empty($user_blocks)){
        $placeholders = implode(',', array_fill(0, count($user_blocks), '?'));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE block IN ($placeholders)");
        $stmt->execute($user_blocks);
        $totalStudents = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE block IN ($placeholders)");
        $stmt->execute($user_blocks);
        $totalModules = $stmt->fetchColumn();
    } else {
        $totalStudents = 0;
        $totalModules = 0;
    }
}

// Fetch students grouped by block
$studentsByBlock = [];
if($is_admin){
    $stmt = $pdo->query("SELECT * FROM students ORDER BY block, name ASC");
    $students = $stmt->fetchAll();
    foreach($students as $s){
        $studentsByBlock[$s['block']][] = $s;
    }
} else {
    if(!empty($user_blocks)){
        $placeholders = implode(',', array_fill(0, count($user_blocks), '?'));
        $stmt = $pdo->prepare("SELECT * FROM students WHERE block IN ($placeholders) ORDER BY block, name ASC");
        $stmt->execute($user_blocks);
        $students = $stmt->fetchAll();
        foreach($students as $s){
            $studentsByBlock[$s['block']][] = $s;
        }
    }
}

// Fetch modules
if($is_admin){
    $modules = $pdo->query("SELECT * FROM modules ORDER BY id DESC")->fetchAll();
} else {
    if(!empty($user_blocks)){
        $placeholders = implode(',', array_fill(0, count($user_blocks), '?'));
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE block IN ($placeholders) ORDER BY id DESC");
        $stmt->execute($user_blocks);
        $modules = $stmt->fetchAll();
    } else {
        $modules = [];
    }
}

include 'includes/header.php';
?>

<style>
.dashboard-card{padding:20px; background:white; border-radius:10px; box-shadow:0 4px 14px rgba(24,39,75,0.05); margin-bottom:15px;}
.dashboard-card h5{margin-bottom:15px;}
.student-card{border:1px solid #ddd;border-radius:10px;padding:10px;text-align:center;transition:0.2s;cursor:pointer;}
.student-card:hover{transform:scale(1.03);box-shadow:0 4px 15px rgba(0,0,0,0.1);}
.student-photo{width:80px;height:80px;object-fit:cover;border-radius:50%;margin-bottom:10px;}
.module-card{border:1px solid #ddd;border-radius:10px;padding:10px;transition:0.2s;}
.module-card:hover{box-shadow:0 4px 15px rgba(0,0,0,0.1);}
</style>

<div class="container-fluid mt-4">
    <h4 class="mb-4 text-center">📊 Dashboard Overview</h4>

    <!-- KPIs -->
    <div class="row text-center mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="dashboard-card">
                <h6>Total Students</h6>
                <h2><?= $totalStudents ?></h2>
            </div>
        </div>
        <?php if($is_admin): ?>
        <div class="col-6 col-md-3 mb-3">
            <div class="dashboard-card">
                <h6>Total Teachers</h6>
                <h2><?= $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn(); ?></h2>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-6 col-md-3 mb-3">
            <div class="dashboard-card">
                <h6>Total Modules</h6>
                <h2><?= $totalModules ?></h2>
            </div>
        </div>
    </div>

    <!-- Students by Block -->
    <?php foreach($studentsByBlock as $block => $students): ?>
    <div class="dashboard-card mb-4">
        <h5>🧑‍🎓 Block: <?= e($block); ?></h5>
        <input type="text" class="form-control mb-3 studentSearch" placeholder="Search in <?= e($block); ?>">
        <div class="row g-3 studentCards">
            <?php foreach($students as $s): ?>
            <div class="col-6 col-md-3 col-lg-2 student-card-container">
                <div class="student-card" 
                    data-id="<?= e($s['id']); ?>"
                    data-name="<?= strtolower(e($s['name'])); ?>"
                    data-block="<?= strtolower(e($s['block'])); ?>"
                    data-course="<?= strtolower(e($s['course'])); ?>"
                    data-prelim="<?= e($s['prelim']); ?>"
                    data-midterm="<?= e($s['midterm']); ?>"
                    data-final="<?= e($s['final']); ?>"
                    data-average="<?= e($s['average']); ?>"
                    data-photo="<?= e($s['photo']); ?>"
                    onclick="showStudent(this)">
                    <img src="uploads/<?= e($s['photo']); ?>" class="student-photo">
                    <h6><?= e($s['name']); ?></h6>
                    <small><?= e($s['block']); ?> - <?= e($s['course']); ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Modules -->
    <div class="dashboard-card mb-4">
        <h5>📚 Modules</h5>
        <?php if($is_admin || !empty($user_blocks)): ?>
        <a href="upload_module.php" class="btn btn-success mb-3">Upload Module</a>
        <?php endif; ?>
        <?php if(empty($modules)): ?>
            <div class="alert alert-info">No modules available for your block.</div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach($modules as $m): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="module-card">
                    <h6><?= e($m['title'] ?? 'No Title'); ?></h6>
                    <small>Block: <?= e($m['block'] ?? ''); ?></small><br>
                    <a href="uploads/<?= e($m['file'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-primary mt-1">View Module</a>
                    <a href="uploads/<?= e($m['file'] ?? '#'); ?>" download class="btn btn-sm btn-secondary mt-1">Download Module</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <img id="modalPhoto" src="" class="img-fluid rounded mb-3" style="max-height:200px;">
      <h5 id="modalName"></h5>
      <p><strong>Block:</strong> <span id="modalBlock"></span></p>
      <p><strong>Course:</strong> <span id="modalCourse"></span></p>
      <p><strong>Prelim:</strong> <span id="modalPrelim"></span></p>
      <p><strong>Midterm:</strong> <span id="modalMidterm"></span></p>
      <p><strong>Final:</strong> <span id="modalFinal"></span></p>
      <p><strong>Average:</strong> <span id="modalAverage"></span></p>
      <div class="d-flex justify-content-between mt-3">
          <a href="#" class="btn btn-primary" id="editBtn">Edit</a>
          <a href="#" class="btn btn-danger" id="deleteBtn" onclick="return confirmDelete();">Delete</a>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show Student in Modal
function showStudent(el){
    document.getElementById('modalName').innerText = el.dataset.name;
    document.getElementById('modalBlock').innerText = el.dataset.block;
    document.getElementById('modalCourse').innerText = el.dataset.course;
    document.getElementById('modalPrelim').innerText = el.dataset.prelim;
    document.getElementById('modalMidterm').innerText = el.dataset.midterm;
    document.getElementById('modalFinal').innerText = el.dataset.final;
    document.getElementById('modalAverage').innerText = el.dataset.average;
    document.getElementById('modalPhoto').src = 'uploads/' + el.dataset.photo;

    document.getElementById('editBtn').href = 'edit_student.php?id=' + el.dataset.id;
    document.getElementById('deleteBtn').href = 'delete_student.php?id=' + el.dataset.id;

    new bootstrap.Modal(document.getElementById('studentModal')).show();
}

// Confirm Delete
function confirmDelete(){
    return confirm('Are you sure you want to delete this student?');
}

// Search per block
document.querySelectorAll('.dashboard-card').forEach(card => {
    const searchInput = card.querySelector('.studentSearch');
    const studentCards = card.querySelectorAll('.student-card-container');

    if(searchInput){
        searchInput.addEventListener('input', function(){
            const filter = this.value.toLowerCase();
            studentCards.forEach(sc => {
                const name = sc.querySelector('.student-card').dataset.name;
                const block = sc.querySelector('.student-card').dataset.block;
                const course = sc.querySelector('.student-card').dataset.course;
                sc.style.display = (name.includes(filter) || block.includes(filter) || course.includes(filter)) ? '' : 'none';
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
