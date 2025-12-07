<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if(!$student){ header('Location: students.php'); exit; }

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="card shadow-sm p-3 mx-auto" style="max-width:600px;">
        <div class="text-center mb-3">
            <img src="uploads/<?= e($student['photo']); ?>" class="img-fluid rounded-circle" style="max-width:150px; cursor:pointer;" onclick="showPhoto('<?= e($student['photo']); ?>')">
        </div>
        <h4 class="text-center mb-3"><?= e($student['name']); ?></h4>
        <div class="row">
            <div class="col-6 mb-2"><strong>Age:</strong> <?= e($student['age']); ?></div>
            <div class="col-6 mb-2"><strong>Gender:</strong> <?= e($student['gender']); ?></div>
            <div class="col-6 mb-2"><strong>Block:</strong> <?= e($student['block']); ?></div>
            <div class="col-6 mb-2"><strong>Course:</strong> <?= e($student['course']); ?></div>
            <div class="col-6 mb-2"><strong>Prelim:</strong> <?= e($student['prelim']); ?></div>
            <div class="col-6 mb-2"><strong>Midterm:</strong> <?= e($student['midterm']); ?></div>
            <div class="col-6 mb-2"><strong>Final:</strong> <?= e($student['final']); ?></div>
            <div class="col-6 mb-2"><strong>Average:</strong> <?= e($student['average']); ?></div>
            <div class="col-12 mb-2"><strong>Created At:</strong> <?= e($student['created_at']); ?></div>
        </div>
        <div class="text-center mt-3">
            <a href="students.php" class="btn btn-secondary">Back to List</a>
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
function showPhoto(file){
    document.getElementById('photoPreview').src = 'uploads/' + file;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
