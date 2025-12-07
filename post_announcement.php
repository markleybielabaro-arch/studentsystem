<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$user = $_SESSION['user']['username'];
$user_blocks = $_SESSION['user']['blocks'] ?? [];
$is_admin = is_admin();

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $title = $_POST['title'];
    $content = $_POST['content'];
    $block = $_POST['block'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO announcements (title,content,block,created_by) VALUES (?,?,?,?)");
    $stmt->execute([$title,$content,$block,$user]);

    header("Location: dashboard.php");
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h4>Post Announcement</h4>

    <form method="post">
        <div class="mb-3">
            <label>Announcement Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Message</label>
            <textarea name="content" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label>Visible to Block</label>
            <select name="block" class="form-control">
                <option value="">All Blocks</option>
                <?php if($is_admin): ?>
                    <?php 
                    $b = $pdo->query("SELECT DISTINCT block FROM students");
                    foreach($b as $row): ?>
                    <option><?= $row['block'] ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach($user_blocks as $b): ?>
                    <option><?= $b ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <button class="btn btn-success">Post</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
