<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if(!$student){ header('Location: students.php'); exit; }

$user_blocks = $_SESSION['user']['blocks'] ?? [];
if(!is_admin() && !in_array($student['block'], $user_blocks)){
    header('Location: students.php'); exit;
}

// Calculate average
$student['average'] = isset($student['average']) ? $student['average'] : round(($student['prelim']+$student['midterm']+$student['final'])/3,2);

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="card shadow-sm p-3 mx-auto" style="max-width:500px;">
        <div class="text-center">
            <img src="uploads/<?= e($student['photo']); ?>" class="img-fluid rounded-circle mb-3" style="width:150px;height:150px;object-fit:cover;">
            <h4><?= e($student['name']); ?></h4>
            <p class="text-muted mb-2"><?= e($student['course']) ?> • Block <?= e($student['block']); ?></p>
        </div>
        <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item"><strong>Age:</strong> <?= e($student['age']); ?></li>
            <li class="list-group-item"><strong>Gender:</strong> <?= e($student['gender']); ?></li>
            <li class="list-group-item"><strong>Prelim:</strong> <?= e($student['prelim']); ?></li>
            <li class="list-group-item"><strong>Midterm:</strong> <?= e($student['midterm']); ?></li>
            <li class="list-group-item"><strong>Final:</strong> <?= e($student['final']); ?></li>
            <li class="list-group-item"><strong>Average:</strong> <?= e($student['average']); ?></li>
            <li class="list-group-item"><strong>Created At:</strong> <?= e($student['created_at']); ?></li>
        </ul>
        <div class="text-center">
            <a href="edit_student.php?id=<?= e($student['id']); ?>" class="btn btn-primary me-2">Edit</a>
            <a href="students.php" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
