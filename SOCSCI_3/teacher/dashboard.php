<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

// Get counts
$student_count_res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='student'");
$student_count = $student_count_res->fetch_assoc()['count'];

$activity_count_res = $conn->query("SELECT COUNT(*) as count FROM activities WHERE teacher_id=" . $_SESSION['user_id']);
$activity_count = $activity_count_res->fetch_assoc()['count'];

$resource_count_res = $conn->query("SELECT COUNT(*) as count FROM resources WHERE teacher_id=" . $_SESSION['user_id']);
$resource_count = $resource_count_res->fetch_assoc()['count'];

// Get recent submissions
$submissions_query = "
    SELECT
        s.id AS submission_id,
        s.file_path,
        s.submitted_at,
        a.title AS activity_title,
        u.first_name,
        u.last_name
    FROM submissions s
    JOIN activities a ON s.activity_id = a.id
    JOIN users u ON s.student_id = u.id
    WHERE a.teacher_id = " . $_SESSION['user_id'] . "
    ORDER BY s.submitted_at DESC
";
$submissions_res = $conn->query($submissions_query);

?>

<div class="dashboard-cards">
    <div class="card" onclick="location.href='students.php'" style="cursor:pointer; background: #fff3e0;">
        <h2>Total Students</h2>
        <p style="text-align:center; font-size: 2rem;"><?= $student_count ?></p>
    </div>
    
    <div class="card" onclick="location.href='activity.php'" style="cursor:pointer; background: #e0f2f1;">
        <h2>Activities</h2>
        <p style="text-align:center; font-size: 2rem;"><?= $activity_count ?></p>
    </div>

    <div class="card" onclick="location.href='resources.php'" style="cursor:pointer; background: #e8eaf6;">
        <h2>Resources</h2>
        <p style="text-align:center; font-size: 2rem;"><?= $resource_count ?></p>
    </div>
</div>

<div class="container" style="margin-top: 30px;">
    <h2>Recent Submissions</h2>

    <div style="margin: 20px 0;">
        <input type="text" id="search-submissions" class="search-bar" data-target="#submissions-table" placeholder="Search submissions..." style="width: 100%; padding: 10px;">
    </div>

    <table id="submissions-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background: #f2f2f2;">
                <th style="padding: 10px; border: 1px solid #ddd;">Student Name</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Activity Title</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Date Submitted</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">File</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($submissions_res->num_rows > 0): ?>
                <?php while($sub = $submissions_res->fetch_assoc()): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($sub['activity_title']) ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                            <?php if (!empty($sub['file_path'])): ?>
                                <button class="btn" onclick="previewFile('<?= htmlspecialchars($sub['file_path']) ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            <?php else: ?>
                                <span style="color: #666;">No File</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="padding: 20px; text-align: center; border: 1px solid #ddd;">No submissions found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/teacher_footer.php'; ?>
