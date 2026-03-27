<?php
session_start();
if (!isset($_SESSION['staff_id'])) { header("Location: login.php"); exit(); }
include '../db.php';

$staff_id   = $_SESSION['staff_id'];
$staff_name = $_SESSION['staff_name'];

// Get modules this staff member leads
$my_modules = mysqli_query($conn,
    "SELECT m.ModuleName, m.Description,
            COUNT(DISTINCT pm.ProgrammeID) AS UsedIn
     FROM Modules m
     LEFT JOIN ProgrammeModules pm ON m.ModuleID = pm.ModuleID
     WHERE m.ModuleLeaderID = $staff_id
     GROUP BY m.ModuleID
     ORDER BY m.ModuleName"
);

// Get programmes this staff member's modules appear in
$my_programmes = mysqli_query($conn,
    "SELECT DISTINCT p.ProgrammeID, p.ProgrammeName, l.LevelName, pm.Year
     FROM Programmes p
     JOIN ProgrammeModules pm ON p.ProgrammeID = pm.ProgrammeID
     JOIN Modules m ON pm.ModuleID = m.ModuleID
     JOIN Levels l ON p.LevelID = l.LevelID
     WHERE m.ModuleLeaderID = $staff_id
     ORDER BY p.ProgrammeName"
);

// Get programmes where this staff is the programme leader
$leading = mysqli_query($conn,
    "SELECT p.ProgrammeID, p.ProgrammeName, l.LevelName
     FROM Programmes p
     JOIN Levels l ON p.LevelID = l.LevelID
     WHERE p.ProgrammeLeaderID = $staff_id"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Portal - <?php echo htmlspecialchars($staff_name); ?></title>
    <link rel="stylesheet" href="../css/staff.css">
</head>
<body>

<!-- Top bar -->
<div class="topbar">
    <h2>👨‍🏫 Staff Portal</h2>
    <div>
        <span>Welcome, <?php echo htmlspecialchars($staff_name); ?></span>
        <a href="../index.php" target="_blank">View Site</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <!-- Welcome message -->
    <div class="welcome">
        <h2>Hello, <?php echo htmlspecialchars($staff_name); ?> 👋</h2>
        <p>Here is an overview of your modules and the programmes you are involved in.</p>
    </div>

    <!-- My Modules -->
    <h3 class="section-heading">📖 Modules You Lead</h3>
    <div class="table-box">
        <table>
            <tr>
                <th>Module Name</th>
                <th>Description</th>
                <th>Used in Programmes</th>
            </tr>
            <?php if (mysqli_num_rows($my_modules) == 0): ?>
                <tr><td colspan="3" class="no-data">You are not currently leading any modules.</td></tr>
            <?php else: ?>
                <?php while ($m = mysqli_fetch_assoc($my_modules)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($m['ModuleName']); ?></strong></td>
                    <td><?php echo htmlspecialchars($m['Description'] ?? 'No description.'); ?></td>
                    <td><?php echo $m['UsedIn']; ?> programme(s)</td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
    </div>

    <!-- Programmes I teach in -->
    <h3 class="section-heading">🎓 Programmes You Teach In</h3>
    <div class="table-box">
        <table>
            <tr>
                <th>Programme Name</th>
                <th>Level</th>
                <th>Year</th>
            </tr>
            <?php if (mysqli_num_rows($my_programmes) == 0): ?>
                <tr><td colspan="3" class="no-data">Your modules are not assigned to any programmes yet.</td></tr>
            <?php else: ?>
                <?php while ($p = mysqli_fetch_assoc($my_programmes)): ?>
                <tr>
                    <td>
                        <a href="../programme.php?id=<?php echo $p['ProgrammeID']; ?>" target="_blank">
                            <?php echo htmlspecialchars($p['ProgrammeName']); ?> ↗
                        </a>
                    </td>
                    <td>
                        <span class="tag <?php echo $p['LevelName'] == 'Undergraduate' ? 'tag-ug' : 'tag-pg'; ?>">
                            <?php echo htmlspecialchars($p['LevelName']); ?>
                        </span>
                    </td>
                    <td>Year <?php echo $p['Year']; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
    </div>

    <!-- Programmes I lead -->
    <?php if (mysqli_num_rows($leading) > 0): ?>
    <h3 class="section-heading">⭐ Programmes You Lead</h3>
    <div class="table-box">
        <table>
            <tr>
                <th>Programme Name</th>
                <th>Level</th>
            </tr>
            <?php while ($p = mysqli_fetch_assoc($leading)): ?>
            <tr>
                <td>
                    <a href="../programme.php?id=<?php echo $p['ProgrammeID']; ?>" target="_blank">
                        <?php echo htmlspecialchars($p['ProgrammeName']); ?> ↗
                    </a>
                </td>
                <td>
                    <span class="tag <?php echo $p['LevelName'] == 'Undergraduate' ? 'tag-ug' : 'tag-pg'; ?>">
                        <?php echo htmlspecialchars($p['LevelName']); ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php endif; ?>

</div>

</body>
</html>

