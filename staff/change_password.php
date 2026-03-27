<?php
session_start();

// Must be logged in to change password
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

include '../db.php';

$staff_id = $_SESSION['staff_id'];
$success  = "";
$error    = "";

// Handle the change password form
if (isset($_POST['change_password'])) {

    $current_password = trim($_POST['current_password']);
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Step 1: Check none are empty
    if ($current_password == "" || $new_password == "" || $confirm_password == "") {
        $error = "Please fill in all fields.";

    // Step 2: Check new password and confirm match
    } elseif ($new_password != $confirm_password) {
        $error = "New password and confirm password do not match.";

    // Step 3: Check new password is long enough
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";

    } else {
        // Step 4: Get current password hash from database
        $result = mysqli_query($conn, "SELECT Password FROM Staff WHERE StaffID = $staff_id");
        $staff  = mysqli_fetch_assoc($result);

        // Step 5: Verify the current password they entered is correct
        if (!password_verify($current_password, $staff['Password'])) {
            $error = "Your current password is incorrect.";
        } else {
            // Step 6: Hash the new password before saving
            // password_hash() scrambles the password so it's stored safely
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $new_hashed_safe = mysqli_real_escape_string($conn, $new_hashed);

            // Step 7: Save the new hashed password to the database
            mysqli_query($conn, "UPDATE Staff SET Password = '$new_hashed_safe' WHERE StaffID = $staff_id");

            $success = "Your password has been changed successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Staff Portal</title>
    <link rel="stylesheet" href="../css/staff.css">
</head>
<body>

<!-- Top bar -->
<div class="topbar">
    <h2>👨‍🏫 Staff Portal</h2>
    <div>
        <span style="color:#aaa; font-size:13px;">
            <?php echo htmlspecialchars($_SESSION['staff_name']); ?>
        </span>
        <a href="portal.php">My Portal</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2 class="page-title">🔑 Change Password</h2>

    <!-- Show success or error message -->
    <?php if ($success != ""): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST" action="change_password.php">

            <!-- Current password -->
            <label for="current_password">Current Password:</label>
            <input type="password"
                   id="current_password"
                   name="current_password"
                   placeholder="Enter your current password"
                   required>

            <!-- New password -->
            <label for="new_password">
                New Password:
                <span class="hint-text">(at least 6 characters)</span>
            </label>
            <input type="password"
                   id="new_password"
                   name="new_password"
                   placeholder="Enter your new password"
                   required>

            <!-- Confirm new password -->
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password"
                   id="confirm_password"
                   name="confirm_password"
                   placeholder="Type new password again"
                   required>

            <button type="submit" name="change_password">Update Password</button>
        </form>
    </div>

    <a href="portal.php" class="back-link">&larr; Back to Portal</a>
</div>

</body>
</html>
