<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = intval($_GET['id'] ?? 0);

// Fetch student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { header('Location: students.php'); exit; }

$allowedBlocks = allowed_blocks();
$courses = [
    'INFORMATION TECHNOLOGY - 1st Year',
    'INFORMATION TECHNOLOGY - 2nd Year',
    'INFORMATION TECHNOLOGY - 3rd Year',
    'INFORMATION TECHNOLOGY - 4th Year'
];
$error = '';

// Ensure current block is selectable
if(!in_array($student['block'], $allowedBlocks)){
    $allowedBlocks[] = $student['block'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = trim($_POST['name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $block = strtoupper(trim($_POST['block'] ?? ''));
    $course = $_POST['course'] ?? '';
    $prelim = floatval($_POST['prelim'] ?? 0);
    $midterm = floatval($_POST['midterm'] ?? 0);
    $final = floatval($_POST['final'] ?? 0);

    if(!$name || !$age || !$gender || !$block || !$course){
        $error = 'Fill all required fields';
    } elseif(!in_array($block, $allowedBlocks)){
        $error = 'You cannot assign this block';
    } elseif(!in_array($course, $courses)){
        $error = 'Invalid Course/Level';
    } else {
        $photo = $student['photo'];
        if(!empty($_FILES['photo']['tmp_name'])){
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo = uniqid().'.'.$ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__.'/uploads/'.$photo);
        }

        $stmt = $pdo->prepare("UPDATE students SET name=?, age=?, gender=?, block=?, course=?, prelim=?, midterm=?, final=?, photo=?, average=? WHERE id=?");
        $average = calc_avg($prelim, $midterm, $final);
        $stmt->execute([$name,$age,$gender,$block,$course,$prelim,$midterm,$final,$photo,$average,$id]);
        audit($pdo,$_SESSION['user']['id'] ?? 0,'edit_student',"Edited student ID {$id}");
        header('Location: students.php?updated=1'); exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <div class="card p-3">
    <h4>Edit Student</h4>
    <?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label>Name</label>
            <input name="name" class="form-control" value="<?= e($student['name']) ?>" required>
        </div>
        <div class="col-md-2">
            <label>Age</label>
            <input name="age" type="number" class="form-control" value="<?= e($student['age']) ?>" required>
        </div>
        <div class="col-md-2">
            <label>Gender</label>
            <select name="gender" class="form-select" required>
                <option value="Male" <?= $student['gender']=='Male'?'selected':'' ?>>Male</option>
                <option value="Female" <?= $student['gender']=='Female'?'selected':'' ?>>Female</option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Block</label>
            <select name="block" class="form-select" required>
                <?php foreach($allowedBlocks as $b): ?>
                <option value="<?= e($b) ?>" <?= $student['block']==$b?'selected':'' ?>><?= e($b) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Course/Level</label>
            <select name="course" class="form-select" required>
                <?php foreach($courses as $c): ?>
                <option value="<?= e($c) ?>" <?= $student['course']==$c?'selected':'' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><label>Prelim</label><input name="prelim" type="number" step="0.01" class="form-control" value="<?= e($student['prelim']) ?>"></div>
        <div class="col-md-2"><label>Midterm</label><input name="midterm" type="number" step="0.01" class="form-control" value="<?= e($student['midterm']) ?>"></div>
        <div class="col-md-2"><label>Final</label><input name="final" type="number" step="0.01" class="form-control" value="<?= e($student['final']) ?>"></div>
        <div class="col-md-6"><label>Photo</label><input name="photo" type="file" accept="image/*" class="form-control"></div>
        <div class="col-12"><img src="uploads/<?= e($student['photo']) ?>" class="img-fluid rounded mb-2" style="max-width:150px;"></div>
        <div class="col-12"><button name="update" class="btn btn-warning">Update Student</button></div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
