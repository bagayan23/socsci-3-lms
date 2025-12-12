<?php
include '../includes/db.php';
include '../includes/student_header.php';

$student_id = $_SESSION['user_id'];

// Get total activities count
$total_activities_res = $conn->query("SELECT COUNT(*) as count FROM activities");
$total_activities = $total_activities_res->fetch_assoc()['count'];

// Get submitted count
$submitted_count_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions 
    WHERE student_id = $student_id
");
$submitted_count = $submitted_count_res->fetch_assoc()['count'];

// Get pending activities count
$pending_count = $total_activities - $submitted_count;

// Get graded submissions count
$graded_count_res = $conn->query("
    SELECT COUNT(*) as count FROM submissions s
    JOIN grades g ON s.id = g.submission_id
    WHERE s.student_id = $student_id
");
$graded_count = $graded_count_res->fetch_assoc()['count'];

// Get average grade
$avg_grade_res = $conn->query("
    SELECT AVG(g.grade) as avg_grade FROM grades g
    JOIN submissions s ON g.submission_id = s.id
    WHERE s.student_id = $student_id
");
$avg_grade = $avg_grade_res->fetch_assoc()['avg_grade'] ?? 0;

// Get total resources
$resources_count_res = $conn->query("SELECT COUNT(*) as count FROM resources");
$resources_count = $resources_count_res->fetch_assoc()['count'];

// Calculate completion rate
$completion_rate = $total_activities > 0 ? round(($submitted_count / $total_activities) * 100, 1) : 0;

// Get recent grades
$recent_grades_query = "
    SELECT a.title, a.total_score, g.grade, g.feedback, g.graded_at
    FROM grades g
    JOIN submissions s ON g.submission_id = s.id
    JOIN activities a ON s.activity_id = a.id
    WHERE s.student_id = $student_id
    ORDER BY g.graded_at DESC
    LIMIT 5
";
$recent_grades_res = $conn->query($recent_grades_query);

// New Resources
$new_resources = $conn->query("SELECT * FROM resources ORDER BY created_at DESC LIMIT 5");

// New Activities (Not yet submitted)
$pending_activities = $conn->query("
    SELECT a.* 
    FROM activities a 
    LEFT JOIN submissions s ON a.id = s.activity_id AND s.student_id = $student_id
    WHERE s.id IS NULL 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

?>

<div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem;">
    <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 3px solid var(--primary-color); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); flex-shrink: 0;">
        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--primary-color), #818cf8); color: white; font-size: 2rem; font-weight: 600;">
            <?= isset($_SESSION['initials']) ? $_SESSION['initials'] : strtoupper(substr($_SESSION['name'], 0, 1)) ?>
        </div>
    </div>
    <div>
        <h2 style="margin: 0 0 0.5rem 0; color: var(--text-color); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-graduation-cap" style="color: var(--primary-color);"></i>
            Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!
        </h2>
        <p style="margin: 0; color: #64748b; font-size: 0.95rem;">Here's your academic progress overview</p>
    </div>
</div>

<!-- Progress Section -->
<div style="margin-bottom: 2rem;">
    <h3 style="color: #64748b; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem;">
        <i class="fas fa-chart-line"></i> My Progress
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <div class="analytics-card">
            <div class="icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-check-circle" style="color: white;"></i>
            </div>
            <div class="label">Completed</div>
            <div class="value"><?= $submitted_count ?></div>
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: <?= $completion_rate ?>%"></div>
            </div>
            <small style="color: #64748b;"><?= $completion_rate ?>% completion rate</small>
        </div>

        <div class="analytics-card" onclick="location.href='activity.php'" style="cursor:pointer;">
            <div class="icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-clock" style="color: white;"></i>
            </div>
            <div class="label">Pending Activities</div>
            <div class="value"><?= $pending_count ?></div>
            <div class="change <?= $pending_count > 0 ? 'negative' : 'positive' ?>">
                <i class="fas fa-<?= $pending_count > 0 ? 'exclamation-circle' : 'check-circle' ?>"></i> 
                <?= $pending_count > 0 ? 'Action needed' : 'All done!' ?>
            </div>
        </div>

        <div class="analytics-card">
            <div class="icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="fas fa-clipboard-check" style="color: white;"></i>
            </div>
            <div class="label">Graded</div>
            <div class="value"><?= $graded_count ?></div>
            <div class="change positive">
                <i class="fas fa-check"></i> of <?= $submitted_count ?> submitted
            </div>
        </div>
    </div>
</div>

<!-- Performance Section -->
<div style="margin-bottom: 2rem;">
    <h3 style="color: #64748b; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem;">
        <i class="fas fa-trophy"></i> Performance & Resources
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <div class="analytics-card">
            <div class="icon" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                <i class="fas fa-star" style="color: white;"></i>
            </div>
            <div class="label">Average Grade</div>
            <div class="value"><?= number_format($avg_grade, 1) ?></div>
            <div class="change <?= $avg_grade >= 75 ? 'positive' : 'negative' ?>">
                <i class="fas fa-<?= $avg_grade >= 75 ? 'thumbs-up' : 'info-circle' ?>"></i> 
                <?= $avg_grade >= 90 ? 'Excellent!' : ($avg_grade >= 75 ? 'Good' : 'Keep going!') ?>
            </div>
        </div>

        <div class="analytics-card" onclick="location.href='resources.php'" style="cursor:pointer;">
            <div class="icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="fas fa-book-open" style="color: white;"></i>
            </div>
            <div class="label">Resources Available</div>
            <div class="value"><?= $resources_count ?></div>
            <div class="change positive">
                <i class="fas fa-folder-open"></i> Ready to view
            </div>
        </div>

        <div class="analytics-card" onclick="location.href='activity.php'" style="cursor:pointer;">
            <div class="icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                <i class="fas fa-tasks" style="color: white;"></i>
            </div>
            <div class="label">Total Activities</div>
        <div class="value"><?= $total_activities ?></div>
        <div class="change positive">
            <i class="fas fa-list"></i> Available
        </div>
    </div>
</div>

<!-- Recent Grades -->
<?php if ($recent_grades_res->num_rows > 0): ?>
<div class="card" style="margin-bottom: 2rem;">
    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        <i class="fas fa-trophy" style="color: var(--primary-color);"></i>
        Recent Grades
    </h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-tasks"></i> Activity</th>
                    <th><i class="fas fa-star"></i> Grade</th>
                    <th><i class="fas fa-percentage"></i> Score</th>
                    <th><i class="fas fa-calendar"></i> Graded On</th>
                </tr>
            </thead>
            <tbody>
                <?php while($grade = $recent_grades_res->fetch_assoc()): 
                    $percentage = ($grade['grade'] / $grade['total_score']) * 100;
                    $badge_class = $percentage >= 90 ? 'badge-success' : ($percentage >= 75 ? 'badge-info' : 'badge-warning');
                ?>
                    <tr>
                        <td><?= htmlspecialchars($grade['title']) ?></td>
                        <td>
                            <span class="badge <?= $badge_class ?>">
                                <?= number_format($grade['grade'], 1) ?> / <?= $grade['total_score'] ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div class="progress-bar" style="width: 100px;">
                                    <div class="progress-bar-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <span style="font-weight: 600;"><?= number_format($percentage, 1) ?>%</span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <span><?= date('M d, Y', strtotime($grade['graded_at'])) ?></span>
                                <small style="color: #64748b;"><?= date('h:i A', strtotime($grade['graded_at'])) ?></small>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Pending Activities -->
<div class="card" style="margin-bottom: 2rem;">
    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        <i class="fas fa-exclamation-circle" style="color: var(--warning-color);"></i>
        Pending Activities
    </h3>
    <input type="text" id="search-pending-activities" class="search-bar form-control" data-target="#table-pending-activities" 
           placeholder="Search activities..." style="margin-bottom: 1rem; max-width: 400px;">
    
    <div class="table-wrapper">
        <table id="table-pending-activities">
            <thead>
                <tr>
                    <th><i class="fas fa-heading"></i> Title</th>
                    <th><i class="fas fa-tag"></i> Type</th>
                    <th><i class="fas fa-calendar-plus"></i> Date Posted</th>
                    <th style="text-align: center;"><i class="fas fa-cog"></i> Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pending_activities->num_rows > 0): ?>
                    <?php while($row = $pending_activities->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td>
                            <span class="badge badge-info"><?= htmlspecialchars(ucfirst($row['type'])) ?></span>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <span><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                                <small style="color: #64748b;"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <a href="activity.php" class="btn" style="width: auto; padding: 0.5rem 1rem;">
                                <i class="fas fa-pencil-alt"></i> Answer
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="padding: 2rem; text-align: center; color: #64748b;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--success-color); opacity: 0.5;"></i>
                            Great job! No pending activities.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Resources -->
<div class="card">
    <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        <i class="fas fa-book" style="color: var(--primary-color);"></i>
        New Resources
    </h3>
    <input type="text" id="search-new-resources" class="search-bar form-control" data-target="#table-new-resources" 
           placeholder="Search resources..." style="margin-bottom: 1rem; max-width: 400px;">
    
    <div class="table-wrapper">
        <table id="table-new-resources">
            <thead>
                <tr>
                    <th><i class="fas fa-bookmark"></i> Subject</th>
                    <th style="text-align: center;"><i class="fas fa-cog"></i> Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($new_resources->num_rows > 0): ?>
                    <?php while($row = $new_resources->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-file-alt" style="color: var(--primary-color);"></i>
                                <?= htmlspecialchars($row['subject']) ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <a href="resources.php" class="btn" style="width: auto; padding: 0.5rem 1rem;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="padding: 2rem; text-align: center; color: #64748b;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                            No resources available yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Initialize table searches
setupTableSearch('#table-pending-activities', '#search-pending-activities');
setupTableSearch('#table-new-resources', '#search-new-resources');
</script>

<?php include '../includes/student_footer.php'; ?>
