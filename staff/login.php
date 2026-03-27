<?php
session_start();

// If already logged in, go to portal
if (isset($_SESSION['staff_id'])) {
    header("Location: portal.php");
    exit();
}

include '../db.php';

$error = "";

// Handle login form submission
if (isset($_POST['login'])) {

    // Get the email and password entered by user
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic check - make sure fields are not empty
    if ($email == "" || $password == "") {
        $error = "Please enter your email and password.";
    } else {
        // Look up the staff member by email (SAFE VERSION)
$stmt = $conn->prepare("SELECT * FROM Staff WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
        // Check if staff found AND password matches
        // password_verify() checks the entered password against the stored hashed password
        if ($staff && password_verify($password, $staff['Password'])) {
            // Login successful - save to session
            $_SESSION['staff_id']   = $staff['StaffID'];
            $_SESSION['staff_name'] = $staff['Name'];
            header("Location: portal.php");
            exit();
        } else {
            // Wrong email or password
            $error = "Incorrect email or password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - CourseHub</title>
    <link rel="stylesheet" href="../css/staff.css">
</head>
<body class="login-page">

<div class="login-box">
    <h2>👨‍🏫 Staff Login</h2>
    <p>Enter your staff email and password to access the portal.</p>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <label for="email">Staff Email:</label>
        <input type="email"
               id="email"
               name="email"
               placeholder="e.g. alice.johnson@university.ac.uk"
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
               required>

        <label for="password">Password:</label>
        <input type="password"
               id="password"
               name="password"
               placeholder="Enter your password"
               required>

        <button type="submit" name="login">Login to Portal</button>
    </form>

    <div class="hint">
        <strong>📌 Demo Credentials:</strong>
        Email: <code>alice.johnson@university.ac.uk</code><br>
        Password: <code>staff123</code><br>
        <em>(All staff share the default password: staff123)</em>
    </div>

    <a href="../index.php" class="back-link">&larr; Back to Student Site</a>
</div>

</body>
</html>
