<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include '../db.php';

// Count totals for the dashboard
$total_programmes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Programmes"))['total'];
$total_modules    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Modules"))['total'];
$total_students   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM InterestedStudents"))['total'];
$total_staff      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Staff"))['total'];

// Get the 5 most recent student registrations
$recent = mysqli_query($conn,
    "SELECT i.StudentName, i.Email, p.ProgrammeName, i.RegisteredAt
     FROM InterestedStudents i
     JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
     ORDER BY i.RegisteredAt DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Course<span>Hub</span> Admin</h2>

    <div class="section-label">Menu</div>
    <a href="dashboard.php" class="active">📊 Dashboard</a>
    <a href="programmes.php">🎓 Programmes</a>
    <a href="modules.php">📖 Modules</a>
    <a href="students.php">👥 Students</a>

    <div class="section-label">Other</div>
    <a href="../index.php" target="_blank">🌐 Student Site</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <h1>Dashboard</h1>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-box">
            <div class="number"><?php echo $total_programmes; ?></div>
            <div class="label">Programmes</div>
        </div>
        <div class="stat-box">
            <div class="number"><?php echo $total_modules; ?></div>
            <div class="label">Modules</div>
        </div>
        <div class="stat-box">
            <div class="number"><?php echo $total_students; ?></div>
            <div class="label">Interested Students</div>
        </div>
        <div class="stat-box">
            <div class="number"><?php echo $total_staff; ?></div>
            <div class="label">Staff Members</div>
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="table-box">
        <h3>Recent Student Registrations</h3>
        <table>
            <tr>
                <th>Student Name</th>
                <th>Email</th>
                <th>Programme</th>
                <th>Date</th>
            </tr>
            <?php if (mysqli_num_rows($recent) == 0): ?>
                <tr><td colspan="4" style="text-align:center; color:#888; padding:20px;">No registrations yet.</td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['StudentName']); ?></td>
                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                    <td><?php echo htmlspecialchars($row['ProgrammeName']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['RegisteredAt'])); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
