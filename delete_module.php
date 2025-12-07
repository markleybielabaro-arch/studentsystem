<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();
if(!is_admin()) die("Not allowed");

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT file FROM modules WHERE id=?");
$stmt->execute([$id]);
$mod = $stmt->fetch();

if($mod){
    unlink("modules/" . $mod['file']);
    $pdo->prepare("DELETE FROM modules WHERE id=?")->execute([$id]);
}

header("Location: dashboard.php");
exit;
?>
