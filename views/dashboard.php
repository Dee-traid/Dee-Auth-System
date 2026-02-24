<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Dee-Triad</title>
  <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body class="dashboard-body">

    <div class="dashboard-wrapper">
        
        <header class="dashboard-header-section">
            <h1>User Dashboard</h1>
        </header>

        <div class="dashboard-card">
            <div class="dashboard-box">
                <h2 class="dashboard-header-text">Welcome, <?php echo htmlspecialchars($_SESSION['userName']); ?>!</h2>
                
                <div class="dashboard-detail-row">
                    <span class="dashboard-label">Account Status</span>
                    <div class="dashboard-value">
                        <span class="dashboard-status-dot"></span> Online
                    </div>
                </div>

                <div class="dashboard-detail-row">
                    <span class="dashboard-label">User ID</span>
                    <div class="dashboard-value">
                        <?php echo htmlspecialchars($_SESSION['userId']); ?>
                    </div>
                </div>

                <div class="dashboard-footer">
                    <a href="views/logout.php" class="dashboard-button">Logout</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>