<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$user_role   = $_SESSION['user']['role'] ?? 'teacher';
$user_blocks = $_SESSION['user']['blocks'] ?? [];
$is_admin    = is_admin();

// Handle Module Upload
if(isset($_POST['upload_module'])){
    $title = trim($_POST['title'] ?? '');
    $block = $_POST['block'] ?? '';
    $file = $_FILES['file'] ?? null;

    if($title && $block && $file && $file['error'] == 0){
        $filename = time().'_'.basename($file['name']);
        move_uploaded_file($file['tmp_name'], 'modules/'.$filename);

        $stmt = $pdo->prepare("INSERT INTO modules(module_title, block, file, uploaded_by, uploaded_at) VALUES(?,?,?,?,NOW())");
        $stmt->execute([$title,$block,$filename,$_SESSION['user']['username'] ?? 'system']);
    }
}

// Fetch modules
if($is_admin){
    $modules = $pdo->query("SELECT * FROM modules ORDER BY block, uploaded_at DESC")->fetchAll();
}else{
    if(!empty($user_blocks)){
        $blocks_in = implode("','",$user_blocks);
        $modules = $pdo->query("SELECT * FROM modules WHERE block IN ('$blocks_in') ORDER BY block, uploaded_at DESC")->fetchAll();
    } else {
        $modules = [];
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
<h4 class="mb-4 text-center">📚 Modules</h4>

<!-- Upload Module Form (Teacher/Student) -->
<?php if($user_role=='teacher'): ?>
<div class="dashboard-card mb-4">
    <h5>Upload Module</h5>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <input type="text" name="title" placeholder="Module Title" class="form-control" required>
        </div>
        <div class="col-md-4">
            <select name="block" class="form-select" required>
                <?php foreach($user_blocks as $b): ?>
                    <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="file" name="file" class="form-control" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary btn-sm w-100" name="upload_module">Upload Module</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if(empty($modules)): ?>
    <div class="alert alert-info">No modules available for your block.</div>
<?php else: ?>
<div class="row">
    <?php foreach($modules as $m): ?>
    <div class="col-12 col-sm-6 col-md-4 mb-3">
        <div class="dashboard-card">
            <strong><?= htmlspecialchars($m['module_title']) ?></strong><br>
            <small>Block: <?= htmlspecialchars($m['block']) ?> | Uploaded by: <?= htmlspecialchars($m['uploaded_by']) ?></small><br>
            <a href="modules/<?= htmlspecialchars($m['file']) ?>" download class="btn btn-success btn-sm mt-2 w-100">Download</a>
            <?php if($is_admin): ?>
                <a href="delete_module.php?id=<?= $m['id'] ?>" class="btn btn-danger btn-sm mt-2 w-100">Delete</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</div>
<?php include 'includes/footer.php'; ?>
