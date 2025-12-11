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

// Get submission statistics
$total_submissions_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions s
    JOIN activities a ON s.activity_id = a.id
    WHERE a.teacher_id = " . $_SESSION['user_id']
);
$total_submissions = $total_submissions_res->fetch_assoc()['count'];

// Get pending grading count
$pending_grading_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions s
    JOIN activities a ON s.activity_id = a.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = " . $_SESSION['user_id'] . " AND g.id IS NULL"
);
$pending_grading = $pending_grading_res->fetch_assoc()['count'];

// Get graded count
$graded_count = $total_submissions - $pending_grading;

// Get average grade
$avg_grade_res = $conn->query("
    SELECT AVG(g.grade) as avg_grade FROM grades g
    JOIN submissions s ON g.submission_id = s.id
    JOIN activities a ON s.activity_id = a.id
    WHERE a.teacher_id = " . $_SESSION['user_id']
);
$avg_grade = $avg_grade_res->fetch_assoc()['avg_grade'] ?? 0;

// Get activity type distribution
$activity_types = $conn->query("
    SELECT type, COUNT(*) as count FROM activities 
    WHERE teacher_id = " . $_SESSION['user_id'] . "
    GROUP BY type
");

// Get recent submissions
$submissions_query = "
    SELECT
        s.id AS submission_id,
        s.file_path,
        s.submitted_at,
        a.title AS activity_title,
        u.first_name,
        u.last_name,
        g.grade,
        a.total_score
    FROM submissions s
    JOIN activities a ON s.activity_id = a.id
    JOIN users u ON s.student_id = u.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = " . $_SESSION['user_id'] . "
    ORDER BY s.submitted_at DESC
    LIMIT 10
";
$submissions_res = $conn->query($submissions_query);

// Calculate completion rate
$completion_rate = $total_submissions > 0 ? round(($graded_count / $total_submissions) * 100, 1) : 0;

?>

<div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem;">
    <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 3px solid var(--primary-color); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); flex-shrink: 0;">
        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--primary-color), #818cf8); color: white; font-size: 2rem; font-weight: 600;">
            <?= isset($_SESSION['initials']) ? $_SESSION['initials'] : strtoupper(substr($_SESSION['name'], 0, 1)) ?>
        </div>
    </div>
    <div>
        <h2 style="margin: 0 0 0.5rem 0; color: var(--text-color); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-chart-line" style="color: var(--primary-color);"></i>
            Teacher Dashboard
        </h2>
        <p style="margin: 0; color: #64748b; font-size: 0.95rem;">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>! Here's your teaching overview</p>
    </div>
</div>

<!-- Overview Section -->
<div style="margin-bottom: 2rem;">
    <h3 style="color: #64748b; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem;">
        <i class="fas fa-chart-bar"></i> Overview
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <div class="analytics-card" onclick="location.href='students.php'" style="cursor:pointer;">
            <div class="icon" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                <i class="fas fa-users" style="color: white;"></i>
            </div>
            <div class="label">Total Students</div>
            <div class="value"><?= $student_count ?></div>
            <div class="change positive">
                <i class="fas fa-arrow-up"></i> Active
            </div>
        </div>
        
        <div class="analytics-card" onclick="location.href='activity.php'" style="cursor:pointer;">
            <div class="icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-tasks" style="color: white;"></i>
            </div>
            <div class="label">Total Activities</div>
            <div class="value"><?= $activity_count ?></div>
            <div class="change positive">
                <i class="fas fa-check-circle"></i> Posted
            </div>
        </div>

        <div class="analytics-card" onclick="location.href='resources.php'" style="cursor:pointer;">
            <div class="icon" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                <i class="fas fa-book" style="color: white;"></i>
            </div>
            <div class="label">Resources</div>
            <div class="value"><?= $resource_count ?></div>
            <div class="change positive">
                <i class="fas fa-folder-open"></i> Available
            </div>
        </div>
    </div>
</div>

<!-- Grading Section -->
<div style="margin-bottom: 2rem;">
    <h3 style="color: #64748b; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem;">
        <i class="fas fa-clipboard-check"></i> Grading & Assessment
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <div class="analytics-card">
            <div class="icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                <i class="fas fa-paper-plane" style="color: white;"></i>
            </div>
            <div class="label">Submissions</div>
            <div class="value"><?= $total_submissions ?></div>
            <div class="change <?= $pending_grading > 0 ? 'negative' : 'positive' ?>">
                <i class="fas fa-<?= $pending_grading > 0 ? 'exclamation-circle' : 'check-circle' ?>"></i> 
                <?= $pending_grading ?> pending
            </div>
        </div>

        <div class="analytics-card">
            <div class="icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="fas fa-clipboard-check" style="color: white;"></i>
            </div>
            <div class="label">Graded</div>
            <div class="value"><?= $graded_count ?></div>
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: <?= $completion_rate ?>%"></div>
            </div>
            <small style="color: #64748b;"><?= $completion_rate ?>% completion rate</small>
        </div>

        <div class="analytics-card">
            <div class="icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="fas fa-star" style="color: white;"></i>
            </div>
            <div class="label">Average Grade</div>
            <div class="value"><?= number_format($avg_grade, 1) ?></div>
            <div class="change <?= $avg_grade >= 75 ? 'positive' : 'negative' ?>">
                <i class="fas fa-<?= $avg_grade >= 75 ? 'thumbs-up' : 'info-circle' ?>"></i> 
                <?= $avg_grade >= 75 ? 'Excellent' : 'Needs attention' ?>
            </div>
        </div>
    </div>
</div>

<!-- Activity Type Distribution -->
<?php if ($activity_types->num_rows > 0): ?>
<div class="card" style="margin-bottom: 2rem;">
    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
        <i class="fas fa-chart-pie" style="color: var(--primary-color);"></i>
        Activity Distribution
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
        <?php while($type = $activity_types->fetch_assoc()): ?>
            <div style="text-align: center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                    <?= $type['count'] ?>
                </div>
                <div style="font-size: 0.875rem; color: #64748b; text-transform: capitalize; margin-top: 0.5rem;">
                    <?= htmlspecialchars($type['type']) ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<!-- Recent Submissions Table -->
<div class="card" style="width: 100%; max-width: 100%;">
    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        <i class="fas fa-history" style="color: var(--primary-color);"></i>
        Recent Submissions
    </h3>

    <input type="text" id="search-submissions" class="search-bar form-control" data-target="#submissions-table" 
           placeholder="Search submissions..." style="margin-bottom: 1rem; max-width: 400px;">

    <div class="table-wrapper" style="width: 100%;">
        <table id="submissions-table" style="width: 100%; table-layout: auto;">
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> Student Name</th>
                    <th><i class="fas fa-tasks"></i> Activity Title</th>
                    <th><i class="fas fa-calendar"></i> Date Submitted</th>
                    <th><i class="fas fa-star"></i> Grade</th>
                    <th style="text-align: center;"><i class="fas fa-cog"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($submissions_res->num_rows > 0): ?>
                    <?php while($sub = $submissions_res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                                        <?= strtoupper(substr($sub['first_name'], 0, 1) . substr($sub['last_name'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($sub['activity_title']) ?></td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span><?= date('M d, Y', strtotime($sub['submitted_at'])) ?></span>
                                    <small style="color: #64748b;"><?= date('h:i A', strtotime($sub['submitted_at'])) ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($sub['grade'] !== null): ?>
                                    <span class="badge badge-success">
                                        <?= number_format($sub['grade'], 1) ?> / <?= $sub['total_score'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($sub['file_path'])): ?>
                                    <button class="btn file-preview-btn" onclick="previewFile('<?= htmlspecialchars($sub['file_path']) ?>', '<?= htmlspecialchars($sub['original_filename'] ?? basename($sub['file_path'])) ?>')">
                                        <i class="fas fa-eye"></i> <span>View</span>
                                    </button>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.875rem;">No File</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: #64748b;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                            No submissions found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Initialize table search
setupTableSearch('#submissions-table', '#search-submissions');
</script>

<?php include '../includes/teacher_footer.php'; ?>
