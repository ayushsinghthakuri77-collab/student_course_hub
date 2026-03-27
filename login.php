<?php
session_start();

// If already logged in, go to dashboard
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// Check login form submission
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple hardcoded credentials
    if ($username == "admin" && $password == "admin123") {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Wrong username or password. Try: admin / admin123";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="login-page">

<div class="login-box">
    <h2>🔐 Admin Login</h2>
    <p>Student Course Hub Administration</p>

    <?php if ($error != ""): ?>
        <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="admin">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password">

        <button type="submit" name="login">Login</button>
    </form>

    <div class="hint-box">Demo: username = <strong>admin</strong> | password = <strong>admin123</strong></div>
    <a href="../index.php">&larr; Back to Student Site</a>
</div>

</body>
</html>
