<?php
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/functions.php';
require_login();

$user_role   = $_SESSION['user']['role'] ?? 'teacher';
$user_blocks = allowed_blocks();
$is_admin    = is_admin();

// Fetch students
if($is_admin){
    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
    $students = $stmt->fetchAll();
} else {
    if(!empty($user_blocks)){
        $placeholders = implode(',', array_fill(0, count($user_blocks), '?'));
        $stmt = $pdo->prepare("SELECT * FROM students WHERE block IN ($placeholders) ORDER BY id DESC");
        $stmt->execute($user_blocks);
        $students = $stmt->fetchAll();
    } else {
        $students = [];
    }
}

// Compute average if not set
foreach($students as &$s){
    if(!isset($s['average'])){
        $s['average'] = calc_avg($s['prelim'], $s['midterm'], $s['final']);
    }
}
unset($s);

include __DIR__.'/includes/header.php';
?>

<div class="container-fluid my-3">
    <div class="card p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
            <h5>Student Records</h5>
            <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                <input type="text" id="searchInput" class="form-control" placeholder="Search students">
                <a href="add_student.php" class="btn btn-success">Add Student</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="studentsTable">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Block</th>
                        <th>Course</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Average</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $i=>$s): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <img src="uploads/<?= e($s['photo']); ?>" width="50" height="50" style="object-fit:cover;border-radius:50%;" onclick="showPhoto('<?= e($s['photo']); ?>')">
                        </td>
                        <td><?= e($s['name']); ?></td>
                        <td><?= e($s['age']); ?></td>
                        <td><?= e($s['gender']); ?></td>
                        <td><?= e($s['block']); ?></td>
                        <td><?= e($s['course']); ?></td>
                        <td><?= e($s['prelim']); ?></td>
                        <td><?= e($s['midterm']); ?></td>
                        <td><?= e($s['final']); ?></td>
                        <td><?= e($s['average']); ?></td>
                        <td class="text-nowrap">
                            <a href="edit_student.php?id=<?= e($s['id']); ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                            <a href="delete_student.php?id=<?= e($s['id']); ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-2">
      <img id="photoPreview" src="" class="img-fluid rounded">
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Photo preview modal
function showPhoto(file){
    document.getElementById('photoPreview').src = 'uploads/' + file;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

// Live search filter
document.getElementById('searchInput').addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#studentsTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
