<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

// Define audit function if not already defined
if (!function_exists('audit')) {
    function audit($pdo, $user_id, $action, $description) {
        $stmt = $pdo->prepare("
            INSERT INTO audit_log (user_id, action, description, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $action, $description]);
    }
}

// Blocks and courses
$courses = ['1st Year','2nd Year','3rd Year','4th Year'];
$error = '';

// Admin check
$is_admin = is_admin();

// Allowed blocks
if ($is_admin) {
    $blocks = range('A','Z'); // Admin can assign any block
} else {
    $blocks = allowed_blocks(); // Teacher assigned blocks
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $block = strtoupper(trim($_POST['block'] ?? ''));
    $course = $_POST['course'] ?? '';
    $prelim = floatval($_POST['prelim'] ?? 0);
    $midterm = floatval($_POST['midterm'] ?? 0);
    $final = floatval($_POST['final'] ?? 0);

    // Validation
    if (!$name || !$age || !$gender || !$block || !$course) {
        $error = 'Fill all required fields';
    } elseif (!in_array($block, $blocks)) {
        $error = 'You cannot assign this block';
    } elseif (!in_array($course, $courses)) {
        $error = 'Invalid Course/Level';
    } else {
        // Photo upload
        $photo = 'default.png';
        if (!empty($_FILES['photo']['name'])) {
            $photo = time() . '_' . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo);
        }

        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO students
            (name, age, gender, block, course, prelim, midterm, final, photo, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $age, $gender, $block, $course, $prelim, $midterm, $final, $photo]);

        // Audit log
        audit($pdo, $_SESSION['user']['id'] ?? 0, 'add_student', "Added student: $name");

        header('Location: students.php?added=1');
        exit;
    }
}

include 'includes/header.php';
?>

<div class="card p-3 mb-4">
    <h5>Add Student</h5>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6 col-sm-12">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-2 col-sm-6">
            <label>Age</label>
            <input type="number" name="age" class="form-control" required>
        </div>
        <div class="col-md-2 col-sm-6">
            <label>Gender</label>
            <select name="gender" class="form-select" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-6">
            <label>Block</label>
            <select name="block" class="form-select" required>
                <?php foreach ($blocks as $b): ?>
                    <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 col-sm-6">
            <label>Course/Level</label>
            <select name="course" class="form-select" required>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 col-sm-6">
            <label>Prelim</label>
            <input type="number" step="0.01" name="prelim" class="form-control" value="0">
        </div>
        <div class="col-md-3 col-sm-6">
            <label>Midterm</label>
            <input type="number" step="0.01" name="midterm" class="form-control" value="0">
        </div>
        <div class="col-md-3 col-sm-6">
            <label>Final</label>
            <input type="number" step="0.01" name="final" class="form-control" value="0">
        </div>
        <div class="col-md-6 col-sm-12">
            <label>Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
        </div>
        <div class="col-12">
            <button class="btn btn-success w-100">Add Student</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
