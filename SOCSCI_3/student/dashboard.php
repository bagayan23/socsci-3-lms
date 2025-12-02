<?php
include '../includes/db.php';
include '../includes/student_header.php';

// New Resources
$new_resources = $conn->query("SELECT * FROM resources ORDER BY created_at DESC LIMIT 5");

// New Activities (Not yet submitted)
$pending_activities = $conn->query("
    SELECT a.* 
    FROM activities a 
    LEFT JOIN submissions s ON a.id = s.activity_id AND s.student_id = " . $_SESSION['user_id'] . "
    WHERE s.id IS NULL 
    ORDER BY a.created_at DESC LIMIT 5
");

?>

<h2>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>

<div style="display: flex; flex-direction: column; gap: 2rem;">

    <div class="card" style="max-width: 100%;">
        <h3>New Resources</h3>
        <input type="text" id="search-new-resources" class="search-bar form-control" data-target="#table-new-resources" placeholder="Search Resources..." style="margin-bottom: 10px; max-width: 300px;">
        <table id="table-new-resources">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $new_resources->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td><a href="resources.php" class="btn" style="width: auto; padding: 5px 10px;">View</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="max-width: 100%;">
        <h3>Pending Activities</h3>
        <input type="text" id="search-pending-activities" class="search-bar form-control" data-target="#table-pending-activities" placeholder="Search Activities..." style="margin-bottom: 10px; max-width: 300px;">
        <table id="table-pending-activities">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Date Posted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $pending_activities->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($row['type'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td><a href="activity.php" class="btn" style="width: auto; padding: 5px 10px;">Answer</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../includes/student_footer.php'; ?>
