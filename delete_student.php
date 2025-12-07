<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if($s){
  if(!empty($s['photo']) && $s['photo'] !== 'default.png' && file_exists('uploads/'.$s['photo'])) unlink('uploads/'.$s['photo']);
  $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
  audit($pdo, $_SESSION['user']['id'] ?? 0, 'delete_student', "Deleted student ID {$id}");
}
header('Location: students.php');
exit;
