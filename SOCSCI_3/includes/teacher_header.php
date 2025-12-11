<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - SOCSCI-3</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
</head>
<body>
    <header>
        <div style="display:flex; align-items:center;">
            <i class="fas fa-bars burger-menu"></i>
            <h1>SOCSCI-3 Teacher Portal</h1>
        </div>
        <div class="header-user-menu">
            <div class="user-avatar">
                <?= isset($_SESSION['initials']) ? $_SESSION['initials'] : strtoupper(substr($_SESSION['name'], 0, 1)) ?>
            </div>
            <div class="dropdown-menu">
                <a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Account</a>
                <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>
    
    <div class="dashboard-layout">
        <div class="sidebar">
            <nav>
                <ul>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="students.php">Students</a></li>
                    <li><a href="resources.php">Resources</a></li>
                    <li><a href="activity.php">Activity</a></li>
                    <li><a href="courses.php">Courses</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
