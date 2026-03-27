<?php
include 'db.php';

// Get level filter from URL (e.g. ?level=1)
$level = 0;
if (isset($_GET['level'])) {
    $level = intval($_GET['level']);
}

// Get search term from URL (e.g. ?search=cyber)
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// Build the SQL query
$sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description,
               l.LevelName, s.Name AS LeaderName
        FROM Programmes p
        JOIN Levels l ON p.LevelID = l.LevelID
        LEFT JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
        WHERE 1=1";

// Add level filter if selected
if ($level > 0) {
    $sql .= " AND p.LevelID = $level";
}

// Add search filter if entered
if ($search != "") {
    $sql .= " AND (p.ProgrammeName LIKE '%$search%' OR p.Description LIKE '%$search%')";
}

$sql .= " ORDER BY p.LevelID, p.ProgrammeName";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Course Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navigation Bar -->
<nav>
    <a href="index.php" class="logo">Course<span>Hub</span></a>
    <div>
        <a href="index.php">Home</a>
        <a href="admin/login.php">Admin</a>
        <a href="staff/login.php">Staff</a>
    </div>
</nav>

<!-- Hero Section -->
<div class="hero">
    <h1>Find Your <span>Perfect Degree</span></h1>
    <p>Browse undergraduate and postgraduate programmes. Register your interest today.</p>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <!-- Search form -->
    <form method="GET" action="index.php" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Search programmes..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

        <!-- Level filter buttons -->
        <a href="index.php" class="<?php echo $level == 0 ? 'active' : ''; ?>">All</a>
        <a href="index.php?level=1" class="<?php echo $level == 1 ? 'active' : ''; ?>">Undergraduate</a>
        <a href="index.php?level=2" class="<?php echo $level == 2 ? 'active' : ''; ?>">Postgraduate</a>

        <button type="submit">Search</button>
    </form>
</div>

<!-- Programme Cards -->
<div class="container">
    <h2>Available Programmes</h2>

    <div class="cards">
        <?php
        // Check if any programmes were found
        if (mysqli_num_rows($result) == 0) {
            echo '<p class="no-results">No programmes found. Try a different search.</p>';
        } else {
            // Loop through each programme and display a card
            while ($row = mysqli_fetch_assoc($result)) {
                $id   = $row['ProgrammeID'];
                $name = htmlspecialchars($row['ProgrammeName']);
                $desc = htmlspecialchars($row['Description']);
                $level_name = htmlspecialchars($row['LevelName']);
                $leader = htmlspecialchars($row['LeaderName'] ?? 'TBA');
                $tag_class = ($row['LevelName'] == 'Undergraduate') ? 'tag-ug' : 'tag-pg';
        ?>
            <div class="card">
                <div class="card-top"></div>
                <div class="card-body">
                    <span class="tag <?php echo $tag_class; ?>"><?php echo $level_name; ?></span>
                    <h3><?php echo $name; ?></h3>
                    <p>👨‍🏫 Leader: <?php echo $leader; ?></p>
                    <p><?php echo substr($desc, 0, 100); ?>...</p>
                    <a href="programme.php?id=<?php echo $id; ?>" class="btn">View Details</a>
                </div>
            </div>
        <?php
            } // end while
        } // end else
        ?>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>&copy; <?php echo date('Y'); ?> Student Course Hub |
       <a href="admin/login.php">Admin Panel</a> |
       <a href="staff/login.php">Staff Portal</a>
    </p>
</footer>

</body>
</html>
