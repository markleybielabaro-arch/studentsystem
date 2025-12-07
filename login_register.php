<?php
require_once 'db.php';
require_once 'includes/functions.php'; // session_start() is already here

$errors = [];
$success = '';

// LOGIN
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if($user && $password === $user['password']){ // plain password for simplicity
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'blocks' => explode(',', $user['blocks'] ?? '')
        ];
        header('Location: dashboard.php');
        exit;
    } else {
        $errors[] = "Invalid username or password";
    }
}

// REGISTER (teacher only)
if(isset($_POST['register'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $role = 'teacher';
    $blocks = $_POST['blocks'] ?? [];
    $blocks_str = implode(',', $blocks);

    $stmt = $pdo->prepare("INSERT INTO users(username,password,full_name,role,blocks) VALUES(?,?,?,?,?)");
    $stmt->execute([$username,$password,$fullname,$role,$blocks_str]);
    $success = "Account created successfully!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login/Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: #f8f9fa;
    }
    .card {
        border-radius: 12px;
    }
    .form-control, .form-select {
        border-radius: 6px;
    }
    @media (max-width: 575px) {
        .btn { font-size: 0.9rem; padding: 0.5rem; }
    }
</style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        <div class="card shadow-sm p-4">
            <h3 class="mb-4 text-center">Student Management System</h3>

            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger"><?php echo implode('<br>',$errors); ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <ul class="nav nav-tabs mb-3" id="tabMenu" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login">Login</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register">Register</button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Login -->
                <div class="tab-pane fade show active" id="login">
                    <form method="POST" class="d-flex flex-column gap-3">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <button class="btn btn-primary w-100" name="login">Login</button>
                    </form>
                </div>

                <!-- Register -->
                <div class="tab-pane fade" id="register">
                    <form method="POST" class="d-flex flex-column gap-3">
                        <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <label>Blocks (Ctrl+Click for multiple)</label>
                        <select name="blocks[]" class="form-select" multiple required>
                            <?php foreach(range('A','Z') as $b): ?>
                                <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-success w-100" name="register">Register</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
