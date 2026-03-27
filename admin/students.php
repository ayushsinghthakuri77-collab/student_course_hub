<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include '../db.php';

$msg = "";

// Delete a student registration
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM InterestedStudents WHERE InterestID = $del_id");
    $msg = "Registration removed.";
}

// CSV Export
if (isset($_GET['export'])) {
    $export_sql = "SELECT i.StudentName, i.Email, p.ProgrammeName, l.LevelName, i.RegisteredAt
                   FROM InterestedStudents i
                   JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
                   JOIN Levels l ON p.LevelID = l.LevelID
                   ORDER BY p.ProgrammeName, i.StudentName";

    $export_result = mysqli_query($conn, $export_sql);

    // Set headers to download file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="mailing_list_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Student Name', 'Email', 'Programme', 'Level', 'Registered At']);

    while ($row = mysqli_fetch_assoc($export_result)) {
        fputcsv($out, [
            $row['StudentName'],
            $row['Email'],
            $row['ProgrammeName'],
            $row['LevelName'],
            $row['RegisteredAt']
        ]);
    }
    fclose($out);
    exit();
}

// Filter by programme
$filter_prog = 0;
if (isset($_GET['programme'])) {
    $filter_prog = intval($_GET['programme']);
}

// Build query
$sql = "SELECT i.InterestID, i.StudentName, i.Email, p.ProgrammeName, i.RegisteredAt
        FROM InterestedStudents i
        JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
        WHERE 1=1";

if ($filter_prog > 0) {
    $sql .= " AND i.ProgrammeID = $filter_prog";
}

$sql .= " ORDER BY i.RegisteredAt DESC";
$students = mysqli_query($conn, $sql);

$programmes = mysqli_query($conn, "SELECT ProgrammeID, ProgrammeName FROM Programmes ORDER BY ProgrammeName");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students - Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Course<span>Hub</span> Admin</h2>
    <div class="section-label">Menu</div>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="programmes.php">🎓 Programmes</a>
    <a href="modules.php">📖 Modules</a>
    <a href="students.php" class="active">👥 Students</a>
    <div class="section-label">Other</div>
    <a href="../index.php" target="_blank">🌐 Student Site</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Interested Students</h1>

    <?php if ($msg != ""): ?>
        <div class="msg-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <!-- Filter & Export controls -->
    <div class="controls">
        <!-- Filter by programme -->
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <select name="programme" onchange="this.form.submit()">
                <option value="0">All Programmes</option>
                <?php while ($p = mysqli_fetch_assoc($programmes)): ?>
                    <option value="<?php echo $p['ProgrammeID']; ?>"
                        <?php echo $filter_prog == $p['ProgrammeID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['ProgrammeName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if ($filter_prog > 0): ?>
                <a href="students.php">Clear Filter</a>
            <?php endif; ?>
        </form>

        <!-- Export button -->
        <a href="students.php?export=1" class="btn btn-green">📤 Export CSV</a>
    </div>

    <!-- Students Table -->
    <div class="table-box">
        <h3>Mailing List (<?php echo mysqli_num_rows($students); ?> students)</h3>
        <table>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Programme</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
            <?php
            $count = 1;
            if (mysqli_num_rows($students) == 0):
            ?>
                <tr><td colspan="6" style="text-align:center; color:#888; padding:20px;">No registrations found.</td></tr>
            <?php else: ?>
                <?php while ($s = mysqli_fetch_assoc($students)): ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($s['StudentName']); ?></td>
                    <td><?php echo htmlspecialchars($s['Email']); ?></td>
                    <td><?php echo htmlspecialchars($s['ProgrammeName']); ?></td>
                    <td><?php echo date('d M Y', strtotime($s['RegisteredAt'])); ?></td>
                    <td>
                        <a href="students.php?delete=<?php echo $s['InterestID']; ?>"
                           class="action-del"
                           onclick="return confirm('Remove this registration?')">Remove</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
