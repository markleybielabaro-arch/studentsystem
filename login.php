<?php
// Include database and functions
require_once 'db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if(isset($_SESSION['user'])) header('Location: dashboard.php');

// Initialize error variable
$err='';

// Handle form submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username=trim($_POST['username']??'');
    $password=$_POST['password']??'';

    if($username===''||$password===''){
        $err='Enter username & password';
    } else {
        $stmt=$pdo->prepare("SELECT * FROM users WHERE username=:username LIMIT 1");
        $stmt->execute(['username'=>$username]);
        $user=$stmt->fetch();

        if($user && password_verify($password,$user['password_hash'])){
            // Save user session
            $_SESSION['user']=[
                'id'=>$user['id'],
                'username'=>$user['username'],
                'full_name'=>$user['full_name']
            ];
            header('Location: dashboard.php'); 
            exit;
        } else {
            $err='Invalid username or password';
        }
    }
}

// Include header
require_once 'includes/header.php';
?>

<div class="row justify-content-center" style="margin-top:6rem;">
    <div class="col-md-5">
        <div class="card shadow-sm p-3">
            <h4 class="mb-3">Sign in</h4>

            <!-- ERROR MESSAGE -->
            <?php if($err): ?>
                <div class="alert alert-danger">
                    <?php echo e($err); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <input class="form-control mb-2" name="username" placeholder="Username" required>
                <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
                <button class="btn btn-primary w-100">Login</button>
            </form>

            <p class="text-muted mt-2 small">
                Default: username <code>admin</code> password <code>admin123</code>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
