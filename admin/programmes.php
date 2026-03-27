<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include '../db.php';

$msg = "";

// ---- DELETE programme ----
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM InterestedStudents WHERE ProgrammeID = $del_id");
    mysqli_query($conn, "DELETE FROM ProgrammeModules WHERE ProgrammeID = $del_id");
    mysqli_query($conn, "DELETE FROM Programmes WHERE ProgrammeID = $del_id");
    $msg = "Programme deleted.";
}

// ---- ADD new programme ----
if (isset($_POST['add_programme'])) {
    $name   = mysqli_real_escape_string($conn, trim($_POST['name']));
    $level  = intval($_POST['level']);
    $leader = intval($_POST['leader']);
    $desc   = mysqli_real_escape_string($conn, trim($_POST['description']));

    if ($name == "") {
        $msg = "ERROR: Programme name is required.";
    } else {
        mysqli_query($conn, "INSERT INTO Programmes (ProgrammeName, LevelID, ProgrammeLeaderID, Description)
                              VALUES ('$name', $level, $leader, '$desc')");
        $msg = "Programme added successfully.";
    }
}

// ---- EDIT programme ----
if (isset($_POST['edit_programme'])) {
    $edit_id = intval($_POST['edit_id']);
    $name    = mysqli_real_escape_string($conn, trim($_POST['name']));
    $level   = intval($_POST['level']);
    $leader  = intval($_POST['leader']);
    $desc    = mysqli_real_escape_string($conn, trim($_POST['description']));

    mysqli_query($conn, "UPDATE Programmes SET ProgrammeName='$name', LevelID=$level,
                         ProgrammeLeaderID=$leader, Description='$desc'
                         WHERE ProgrammeID=$edit_id");
    $msg = "Programme updated.";
}

// Load data
$programmes = mysqli_query($conn,
    "SELECT p.*, l.LevelName, s.Name AS LeaderName
     FROM Programmes p
     JOIN Levels l ON p.LevelID = l.LevelID
     LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
     ORDER BY p.LevelID, p.ProgrammeName"
);

$levels = mysqli_query($conn, "SELECT * FROM Levels");
$staff  = mysqli_query($conn, "SELECT * FROM Staff ORDER BY Name");

// If editing, load that programme
$edit_prog = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $edit_prog = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Programmes WHERE ProgrammeID = $eid"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Programmes - Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Course<span>Hub</span> Admin</h2>
    <div class="section-label">Menu</div>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="programmes.php" class="active">🎓 Programmes</a>
    <a href="modules.php">📖 Modules</a>
    <a href="students.php">👥 Students</a>
    <div class="section-label">Other</div>
    <a href="../index.php" target="_blank">🌐 Student Site</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Manage Programmes</h1>

    <?php if ($msg != ""): ?>
        <div class="<?php echo strpos($msg, 'ERROR') === false ? 'msg-success' : 'msg-error'; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <!-- Add / Edit Form -->
    <div class="form-box">
        <h3><?php echo $edit_prog ? "✏️ Edit Programme" : "➕ Add New Programme"; ?></h3>
        <form method="POST">
            <?php if ($edit_prog): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_prog['ProgrammeID']; ?>">
            <?php endif; ?>

            <label>Programme Name:</label>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($edit_prog['ProgrammeName'] ?? ''); ?>"
                   placeholder="e.g. BSc Computer Science">

            <label>Level:</label>
            <select name="level">
                <?php
                // Reset levels result
                mysqli_data_seek($levels, 0);
                while ($lv = mysqli_fetch_assoc($levels)):
                ?>
                    <option value="<?php echo $lv['LevelID']; ?>"
                        <?php echo (($edit_prog['LevelID'] ?? 1) == $lv['LevelID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($lv['LevelName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Programme Leader:</label>
            <select name="leader">
                <option value="0">-- None --</option>
                <?php
                mysqli_data_seek($staff, 0);
                while ($st = mysqli_fetch_assoc($staff)):
                ?>
                    <option value="<?php echo $st['StaffID']; ?>"
                        <?php echo (($edit_prog['ProgrammeLeaderID'] ?? 0) == $st['StaffID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($st['Name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Description:</label>
            <textarea name="description" placeholder="Describe the programme..."><?php echo htmlspecialchars($edit_prog['Description'] ?? ''); ?></textarea>

            <?php if ($edit_prog): ?>
                <button type="submit" name="edit_programme" class="btn">💾 Save Changes</button>
                <a href="programmes.php" class="btn-cancel">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_programme" class="btn">➕ Add Programme</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Programmes Table -->
    <div class="table-box">
        <h3>All Programmes</h3>
        <table>
            <tr>
                <th>Name</th>
                <th>Level</th>
                <th>Leader</th>
                <th>Actions</th>
            </tr>
            <?php while ($p = mysqli_fetch_assoc($programmes)): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['ProgrammeName']); ?></td>
                <td>
                    <span class="tag <?php echo $p['LevelID'] == 1 ? 'tag-ug' : 'tag-pg'; ?>">
                        <?php echo htmlspecialchars($p['LevelName']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($p['LeaderName'] ?? 'TBA'); ?></td>
                <td>
                    <a href="programmes.php?edit=<?php echo $p['ProgrammeID']; ?>" class="action-link">Edit</a>
                    <a href="../programme.php?id=<?php echo $p['ProgrammeID']; ?>" target="_blank" class="action-link">View</a>
                    <a href="programmes.php?delete=<?php echo $p['ProgrammeID']; ?>"
                       class="action-link action-del"
                       onclick="return confirm('Are you sure you want to delete this programme?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
