<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$user_role   = $_SESSION['user']['role'] ?? 'teacher';
$user_blocks = $_SESSION['user']['blocks'] ?? [];
$is_admin    = is_admin();

$error = '';
$success = '';

// Allowed blocks
$allBlocks = $is_admin ? $pdo->query("SELECT DISTINCT block FROM students")->fetchAll(PDO::FETCH_COLUMN) : $user_blocks;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])){
    $title = trim($_POST['title'] ?? '');
    $block = $_POST['block'] ?? '';
    
    if(!$title || !$block){
        $error = "Please fill all required fields.";
    } elseif(!in_array($block, $allBlocks)){
        $error = "You cannot assign this block.";
    } elseif(empty($_FILES['file']['tmp_name'])){
        $error = "Please select a file to upload.";
    } else {
        $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid().'_module.'.$file_ext;
        $upload_path = 'uploads/'.$filename;

        if(move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)){
            $stmt = $pdo->prepare("INSERT INTO modules (title, block, file) VALUES (?, ?, ?)");
            $stmt->execute([$title, $block, $filename]);
            audit($pdo, $_SESSION['user']['id'] ?? 0, 'upload_module', "Uploaded module '$title' for block $block");
            $success = "Module uploaded successfully!";
        } else {
            $error = "Failed to upload the file.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="card p-4 mx-auto" style="max-width:600px;">
        <h4 class="mb-3 text-center">📤 Upload Module</h4>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= e($error); ?></div>
        <?php elseif($success): ?>
            <div class="alert alert-success"><?= e($success); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Module Title</label>
                <input type="text" name="title" class="form-control" value="<?= e($_POST['title'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label>Block</label>
                <select name="block" class="form-select" required>
                    <?php foreach($allBlocks as $b): ?>
                        <option value="<?= e($b); ?>" <?php if(($POST['block'] ?? '')==$b) echo 'selected'; ?>><?= e($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>File</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip" required>
            </div>
            <div class="text-center">
                <button type="submit" name="upload" class="btn btn-success w-50">Upload Module</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
