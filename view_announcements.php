<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$user_role   = $_SESSION['user']['role'] ?? 'teacher';
$user_blocks = $_SESSION['user']['blocks'] ?? [];
$is_admin    = is_admin();

// Handle Announcement Upload (Teacher Only)
if(isset($_POST['post_announcement']) && $user_role=='teacher'){
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $block   = $_POST['block'] ?? '';

    if($title && $content && $block && in_array($block, $user_blocks)){
        $stmt = $pdo->prepare("INSERT INTO announcements(title, content, block, created_at) VALUES(?,?,?,NOW())");
        $stmt->execute([$title, $content, $block]);
        // Optional: audit log
        audit($pdo, $_SESSION['user']['id'], 'post_announcement', "Posted announcement: $title for block $block");
    }
}

// Fetch Announcements (by block)
if($is_admin){
    $announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
}else{
    if(!empty($user_blocks)){
        $blocks_in = implode("','", $user_blocks);
        $announcements = $pdo->query("SELECT * FROM announcements WHERE block IN ('$blocks_in') ORDER BY created_at DESC")->fetchAll();
    } else {
        $announcements = [];
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
<h4 class="mb-4 text-center">📢 Announcements</h4>

<?php if($user_role=='teacher' && !empty($user_blocks)): ?>
<div class="dashboard-card mb-4">
    <h5>Post New Announcement</h5>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <input type="text" name="title" class="form-control" placeholder="Title" required>
        </div>
        <div class="col-md-4">
            <select name="block" class="form-select" required>
                <?php foreach($user_blocks as $b): ?>
                    <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning w-100" name="post_announcement">Post</button>
        </div>
        <div class="col-12">
            <textarea name="content" class="form-control mt-2" rows="3" placeholder="Announcement content" required></textarea>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Announcements List -->
<?php if(empty($announcements)): ?>
    <div class="alert alert-info">No announcements available for your block.</div>
<?php else: ?>
<div class="row">
    <?php foreach($announcements as $a): ?>
    <div class="col-12 col-md-6 mb-3">
        <div class="dashboard-card p-3">
            <strong><?= htmlspecialchars($a['title']) ?></strong><br>
            <small>Block: <?= htmlspecialchars($a['block']) ?></small><br>
            <?= htmlspecialchars($a['content']) ?><br>
            <small><?= date("M d, Y H:i", strtotime($a['created_at'])) ?></small><br>
            <?php if($is_admin): ?>
                <a href="delete_announcement.php?id=<?= $a['id'] ?>" class="btn btn-danger btn-sm mt-2 w-100">Delete</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
