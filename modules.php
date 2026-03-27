<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include '../db.php';

$msg = "";

// Delete module
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM ProgrammeModules WHERE ModuleID = $del_id");
    mysqli_query($conn, "DELETE FROM Modules WHERE ModuleID = $del_id");
    $msg = "Module deleted.";
}

// Add module
if (isset($_POST['add_module'])) {
    $name   = mysqli_real_escape_string($conn, trim($_POST['name']));
    $leader = intval($_POST['leader']);
    $desc   = mysqli_real_escape_string($conn, trim($_POST['description']));

    if ($name == "") {
        $msg = "ERROR: Module name is required.";
    } else {
        mysqli_query($conn, "INSERT INTO Modules (ModuleName, ModuleLeaderID, Description)
                              VALUES ('$name', $leader, '$desc')");
        $msg = "Module added.";
    }
}

// Edit module
if (isset($_POST['edit_module'])) {
    $eid    = intval($_POST['edit_id']);
    $name   = mysqli_real_escape_string($conn, trim($_POST['name']));
    $leader = intval($_POST['leader']);
    $desc   = mysqli_real_escape_string($conn, trim($_POST['description']));

    mysqli_query($conn, "UPDATE Modules SET ModuleName='$name', ModuleLeaderID=$leader,
                         Description='$desc' WHERE ModuleID=$eid");
    $msg = "Module updated.";
}

$modules = mysqli_query($conn,
    "SELECT m.*, s.Name AS LeaderName FROM Modules m
     LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
     ORDER BY m.ModuleName"
);

$staff = mysqli_query($conn, "SELECT * FROM Staff ORDER BY Name");

$edit_mod = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $edit_mod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Modules WHERE ModuleID = $eid"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Modules - Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Course<span>Hub</span> Admin</h2>
    <div class="section-label">Menu</div>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="programmes.php">🎓 Programmes</a>
    <a href="modules.php" class="active">📖 Modules</a>
    <a href="students.php">👥 Students</a>
    <div class="section-label">Other</div>
    <a href="../index.php" target="_blank">🌐 Student Site</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Manage Modules</h1>

    <?php if ($msg != ""): ?>
        <div class="<?php echo strpos($msg, 'ERROR') === false ? 'msg-success' : 'msg-error'; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <!-- Add / Edit Form -->
    <div class="form-box">
        <h3><?php echo $edit_mod ? "✏️ Edit Module" : "➕ Add New Module"; ?></h3>
        <form method="POST">
            <?php if ($edit_mod): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_mod['ModuleID']; ?>">
            <?php endif; ?>

            <label>Module Name:</label>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($edit_mod['ModuleName'] ?? ''); ?>"
                   placeholder="e.g. Introduction to AI">

            <label>Module Leader:</label>
            <select name="leader">
                <option value="0">-- None --</option>
                <?php
                while ($st = mysqli_fetch_assoc($staff)):
                ?>
                    <option value="<?php echo $st['StaffID']; ?>"
                        <?php echo (($edit_mod['ModuleLeaderID'] ?? 0) == $st['StaffID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($st['Name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Description:</label>
            <textarea name="description"><?php echo htmlspecialchars($edit_mod['Description'] ?? ''); ?></textarea>

            <?php if ($edit_mod): ?>
                <button type="submit" name="edit_module" class="btn">💾 Save Changes</button>
                <a href="modules.php" class="btn-cancel">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_module" class="btn">➕ Add Module</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Modules Table -->
    <div class="table-box">
        <h3>All Modules</h3>
        <table>
            <tr>
                <th>Module Name</th>
                <th>Leader</th>
                <th>Actions</th>
            </tr>
            <?php while ($m = mysqli_fetch_assoc($modules)): ?>
            <tr>
                <td><?php echo htmlspecialchars($m['ModuleName']); ?></td>
                <td><?php echo htmlspecialchars($m['LeaderName'] ?? 'Unassigned'); ?></td>
                <td>
                    <a href="modules.php?edit=<?php echo $m['ModuleID']; ?>" class="action-link">Edit</a>
                    <a href="modules.php?delete=<?php echo $m['ModuleID']; ?>"
                       class="action-link action-del"
                       onclick="return confirm('Delete this module?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
