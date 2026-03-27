<?php
include 'db.php';

// Get programme ID from URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Get programme details
$sql = "SELECT p.*, l.LevelName, s.Name AS LeaderName
        FROM Programmes p
        JOIN Levels l ON p.LevelID = l.LevelID
        LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
        WHERE p.ProgrammeID = $id";

$result = mysqli_query($conn, $sql);
$prog = mysqli_fetch_assoc($result);

// If programme not found, go back
if (!$prog) {
    echo "Programme not found.";
    exit();
}

// Get modules for this programme, grouped by year
$mod_sql = "SELECT m.ModuleName, m.Description, pm.Year, s.Name AS ModLeader
            FROM ProgrammeModules pm
            JOIN Modules m ON pm.ModuleID = m.ModuleID
            LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
            WHERE pm.ProgrammeID = $id
            ORDER BY pm.Year, m.ModuleName";

$mod_result = mysqli_query($conn, $mod_sql);

// Store modules in an array grouped by year
$modules_by_year = array();
while ($mod = mysqli_fetch_assoc($mod_result)) {
    $year = $mod['Year'];
    $modules_by_year[$year][] = $mod;
}

// ---- Handle Register Interest Form ----
$success_msg = "";
$error_msg = "";

if (isset($_POST['register'])) {
    $name  = mysqli_real_escape_string($conn, trim($_POST['student_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Simple validation
    if ($name == "" || $email == "") {
        $error_msg = "Please fill in all fields.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        // Check if already registered
        $check = mysqli_query($conn, "SELECT InterestID FROM InterestedStudents
                                      WHERE ProgrammeID = $id AND Email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $error_msg = "You have already registered interest with this email.";
        } else {
            // Insert into database
            $insert = "INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email)
                       VALUES ($id, '$name', '$email')";
            mysqli_query($conn, $insert);
            $success_msg = "Thank you $name! Your interest has been registered.";
        }
    }
}

// ---- Handle Withdraw Interest Form ----
if (isset($_POST['withdraw'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['withdraw_email']));

    $delete = "DELETE FROM InterestedStudents WHERE ProgrammeID = $id AND Email = '$email'";
    mysqli_query($conn, $delete);

    if (mysqli_affected_rows($conn) > 0) {
        $success_msg = "Your interest has been withdrawn successfully.";
    } else {
        $error_msg = "No registration found with that email for this programme.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($prog['ProgrammeName']); ?> - CourseHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Nav -->
<nav>
    <a href="index.php" class="logo">Course<span>Hub</span></a>
    <div>
        <a href="index.php">Home</a>
        <a href="admin/login.php">Admin</a>
        <a href="staff/login.php">Staff</a>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <p class="breadcrumb"><a href="index.php">Home</a> &rsaquo; <?php echo htmlspecialchars($prog['ProgrammeName']); ?></p>
    <h1><?php echo htmlspecialchars($prog['ProgrammeName']); ?></h1>
    <p><?php echo htmlspecialchars($prog['Description']); ?></p>
    <p style="margin-top:10px;">
        🎓 Level: <?php echo htmlspecialchars($prog['LevelName']); ?> &nbsp;|&nbsp;
        👨‍🏫 Programme Leader: <?php echo htmlspecialchars($prog['LeaderName'] ?? 'TBA'); ?>
    </p>
</div>

<div class="container">

    <!-- Success / Error Messages -->
    <?php if ($success_msg != ""): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <?php if ($error_msg != ""): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <!-- Modules Section -->
    <h2 class="section-heading">📖 Modules</h2>

    <?php if (empty($modules_by_year)): ?>
        <p>No modules have been assigned to this programme yet.</p>
    <?php else: ?>
        <?php foreach ($modules_by_year as $year => $mods): ?>
            <div class="year-label">
                <?php echo ($prog['LevelID'] == 2) ? "Postgraduate Modules" : "Year $year"; ?>
            </div>

            <table>
                <tr>
                    <th>Module Name</th>
                    <th>Module Leader</th>
                    <th>Description</th>
                </tr>
                <?php foreach ($mods as $mod): ?>
                <tr>
                    <td><?php echo htmlspecialchars($mod['ModuleName']); ?></td>
                    <td><?php echo htmlspecialchars($mod['ModLeader'] ?? 'TBA'); ?></td>
                    <td><?php echo htmlspecialchars(substr($mod['Description'], 0, 80)); ?>...</td>
                </tr>
                <?php endforeach; ?>
            </table>

        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Register Interest Form -->
    <div class="form-box">
        <h2>✉️ Register Your Interest</h2>
        <p style="color:#aaa; font-size:13px;">Fill in the form below and we'll keep you updated about this programme.</p>

        <form method="POST" action="programme.php?id=<?php echo $id; ?>">
            <label for="student_name">Your Full Name:</label>
            <input type="text" id="student_name" name="student_name" placeholder="e.g. John Smith" required>

            <label for="email">Your Email Address:</label>
            <input type="email" id="email" name="email" placeholder="e.g. john@example.com" required>

            <button type="submit" name="register">Register Interest</button>
        </form>
    </div>

    <!-- Withdraw Interest -->
    <div class="withdraw-box">
        <h3>🚫 Withdraw Interest</h3>
        <p style="font-size:13px; color:#666;">Enter your email to remove your interest registration.</p>
        <form method="POST" action="programme.php?id=<?php echo $id; ?>" style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
            <input type="email" name="withdraw_email" placeholder="Your email address" required>
            <button type="submit" name="withdraw">Withdraw</button>
        </form>
    </div>

    <a href="index.php" class="back-link">&larr; Back to all programmes</a>
</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Student Course Hub</p>
</footer>

</body>
</html>
