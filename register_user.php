<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();

$err='';
$success='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username=trim($_POST['username']);
    $full_name=trim($_POST['full_name']);
    $password=$_POST['password'];
    $confirm_password=$_POST['confirm_password'];
    $role=$_POST['role']??'admin';

    if(!$username || !$full_name || !$password || !$confirm_password){
        $err='All fields are required';
    } elseif($password!==$confirm_password){
        $err='Passwords do not match';
    } else {
        // Check if username exists
        $stmt=$pdo->prepare("SELECT COUNT(*) FROM users WHERE username=:username");
        $stmt->execute(['username'=>$username]);
        if($stmt->fetchColumn()>0){
            $err='Username already exists';
        } else {
            // Insert user
            $password_hash=password_hash($password,PASSWORD_DEFAULT);
            $stmt=$pdo->prepare("INSERT INTO users (username,password_hash,full_name,role) VALUES (:username,:password_hash,:full_name,:role)");
            $stmt->execute([
                'username'=>$username,
                'password_hash'=>$password_hash,
                'full_name'=>$full_name,
                'role'=>$role
            ]);
            $success='User registered successfully';
        }
    }
}

require_once 'includes/header.php';
?>

<h3>Register New User</h3>

<?php if($err):?><div class="alert alert-danger"><?php echo e($err);?></div><?php endif;?>
<?php if($success):?><div class="alert alert-success"><?php echo e($success);?></div><?php endif;?>

<form method="post">
    <input class="form-control mb-2" name="username" placeholder="Username" required>
    <input class="form-control mb-2" name="full_name" placeholder="Full Name" required>
    <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
    <input class="form-control mb-2" type="password" name="confirm_password" placeholder="Confirm Password" required>
    <select class="form-control mb-2" name="role">
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>
    <button class="btn btn-success">Register User</button>
</form>

<?php require_once 'includes/footer.php'; ?>
