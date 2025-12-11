<?php
session_start();
include '../includes/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

// Handle Activity Deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_activity'])) {
    $activity_id = intval($_POST['activity_id']);
    $teacher_id = $_SESSION['user_id'];
    
    // Get file path before deleting
    $stmt = $conn->prepare("SELECT file_path FROM activities WHERE id=? AND teacher_id=?");
    $stmt->bind_param("ii", $activity_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $activity = $result->fetch_assoc();
        
        // Delete associated grades first
        $conn->query("DELETE g FROM grades g INNER JOIN submissions s ON g.submission_id = s.id WHERE s.activity_id = $activity_id");
        
        // Delete submissions
        $conn->query("DELETE FROM submissions WHERE activity_id = $activity_id");
        
        // Delete activity
        $stmt = $conn->prepare("DELETE FROM activities WHERE id=? AND teacher_id=?");
        $stmt->bind_param("ii", $activity_id, $teacher_id);
        $stmt->execute();
        
        // Delete file if exists
        if ($activity['file_path'] && file_exists($activity['file_path'])) {
            unlink($activity['file_path']);
        }
        
        header("Location: activity.php?success=" . urlencode("Activity deleted successfully"));
        exit();
    }
}

// Handle Activity Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_activity'])) {
    $activity_id = intval($_POST['activity_id']);
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $total_score = intval($_POST['total_score']);
    $teacher_id = $_SESSION['user_id'];
    
    // Get current file path
    $stmt = $conn->prepare("SELECT file_path FROM activities WHERE id=? AND teacher_id=?");
    $stmt->bind_param("ii", $activity_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $current = $result->fetch_assoc();
        $file_path = $current['file_path'];
        $original_filename = null;
        
        // Handle new file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            // Delete old file
            if ($file_path && file_exists($file_path)) {
                unlink($file_path);
            }
            
            $upload_dir = '../uploads/';
            $original_filename = basename($_FILES['file']['name']);
            $file_path = $upload_dir . time() . '_' . $original_filename;
            move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
            
            $stmt = $conn->prepare("UPDATE activities SET title=?, description=?, type=?, total_score=?, file_path=?, original_filename=? WHERE id=? AND teacher_id=?");
            $stmt->bind_param("sssissii", $title, $description, $type, $total_score, $file_path, $original_filename, $activity_id, $teacher_id);
        } else {
            $stmt = $conn->prepare("UPDATE activities SET title=?, description=?, type=?, total_score=? WHERE id=? AND teacher_id=?");
            $stmt->bind_param("sssiii", $title, $description, $type, $total_score, $activity_id, $teacher_id);
        }
        
        $stmt->execute();
        header("Location: activity.php?success=" . urlencode("Activity updated successfully"));
        exit();
    }
}

// Handle Activity Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_activity'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $total_score = intval($_POST['total_score']);
    $teacher_id = $_SESSION['user_id'];
    
    $file_path = null;
    $original_filename = null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../uploads/';
        $original_filename = basename($_FILES['file']['name']);
        $file_path = $upload_dir . time() . '_' . $original_filename;
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
    }

    $stmt = $conn->prepare("INSERT INTO activities (teacher_id, title, description, type, total_score, file_path, original_filename) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssiss", $teacher_id, $title, $description, $type, $total_score, $file_path, $original_filename);
    $stmt->execute();
    
    header("Location: activity.php?success=" . urlencode("Activity created successfully"));
    exit();
}

// Handle Grading
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_grade'])) {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];
    $teacher_id = $_SESSION['user_id'];

    // Verify that the teacher owns the activity for this submission
    $verify_query = $conn->query("
        SELECT a.teacher_id 
        FROM submissions s 
        JOIN activities a ON s.activity_id = a.id 
        WHERE s.id = $submission_id AND a.teacher_id = $teacher_id
    ");
    
    if ($verify_query && $verify_query->num_rows > 0) {
        // Check if grade exists
        $check = $conn->query("SELECT id FROM grades WHERE submission_id=$submission_id");
        if ($check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE grades SET grade=?, feedback=?, graded_at=NOW() WHERE submission_id=?");
            $stmt->bind_param("dsi", $grade, $feedback, $submission_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO grades (submission_id, teacher_id, grade, feedback) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iids", $submission_id, $teacher_id, $grade, $feedback);
        }
        $stmt->execute();
    } else {
        // Not authorized to grade this submission
        header("Location: activity.php?error=" . urlencode("You can only grade submissions for your own activities"));
        exit();
    }
}

// Include header after all POST processing is complete
include '../includes/teacher_header.php';

$activities = $conn->query("SELECT * FROM activities WHERE teacher_id=" . $_SESSION['user_id'] . " ORDER BY created_at DESC");

// Get activity for editing if edit_id is set
$edit_activity = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM activities WHERE id=? AND teacher_id=?");
    $stmt->bind_param("ii", $edit_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_activity = $result->fetch_assoc();
    }
}
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<h2>Activities</h2>

<div class="card" style="margin-bottom: 2rem; margin-top:0px; max-width: 100%; box-sizing: border-box; overflow-x: hidden;">
    <h3><?= $edit_activity ? 'Edit Activity / Quiz' : 'Create Activity / Quiz' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <?php if ($edit_activity): ?>
            <input type="hidden" name="activity_id" value="<?= $edit_activity['id'] ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required value="<?= $edit_activity ? htmlspecialchars($edit_activity['title']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"><?= $edit_activity ? htmlspecialchars($edit_activity['description']) : '' ?></textarea>
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control">
                <option value="activity" <?= ($edit_activity && $edit_activity['type'] == 'activity') ? 'selected' : '' ?>>Activity</option>
                <option value="quiz" <?= ($edit_activity && $edit_activity['type'] == 'quiz') ? 'selected' : '' ?>>Quiz</option>
            </select>
        </div>
        <div class="form-group">
            <label>Total Score</label>
            <input type="number" name="total_score" class="form-control" required value="<?= $edit_activity ? $edit_activity['total_score'] : '100' ?>" min="1">
        </div>
        <div class="form-group">
            <label>Attach File (Optional)</label>
            <?php if ($edit_activity && $edit_activity['file_path']): ?>
                <p style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <span>Current file:</span>
                    <span class="file-name-display">
                        <i class="fas fa-file" style="color: var(--primary-color);"></i>
                        <strong style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($edit_activity['original_filename'] ?? basename($edit_activity['file_path'])) ?></strong>
                    </span>
                    <button type="button" onclick="previewFile('<?= $edit_activity['file_path'] ?>', '<?= htmlspecialchars($edit_activity['original_filename'] ?? basename($edit_activity['file_path'])) ?>')" class="btn file-preview-btn">
                        <i class="fas fa-eye"></i> <span>View</span>
                    </button>
                </p>
            <?php endif; ?>
            <input type="file" name="file" class="form-control">
            <?php if ($edit_activity && $edit_activity['file_path']): ?>
                <small style="color: #64748b;">Upload a new file to replace the existing one</small>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" name="<?= $edit_activity ? 'update_activity' : 'create_activity' ?>" class="btn">
                <i class="fas fa-<?= $edit_activity ? 'save' : 'plus' ?>"></i> <?= $edit_activity ? 'Update' : 'Create' ?>
            </button>
            <?php if ($edit_activity): ?>
                <a href="activity.php" class="btn" style="background: #6c757d; text-decoration: none;">
                    <i class="fas fa-times"></i> Cancel
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<h3>Posted Activities</h3>
<input type="text" id="search-activities" class="search-bar form-control" data-target="#table-activities" placeholder="Search Activities..." style="margin-bottom: 10px; max-width: 300px;">
<div class="table-wrapper">
    <table id="table-activities">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
                <th>Total Score</th>
                <th>File</th>
                <th>Date Posted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($activities->num_rows > 0): ?>
                <?php while($act = $activities->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($act['title']) ?></td>
                    <td><?= htmlspecialchars($act['description']) ?></td>
                    <td><?= ucfirst($act['type']) ?></td>
                    <td><?= $act['total_score'] ?></td>
                    <td>
                        <div class="file-preview-container">
                            <?php if($act['file_path']): ?>
                                <button onclick="previewFile('<?= $act['file_path'] ?>', '<?= htmlspecialchars($act['original_filename'] ?? basename($act['file_path'])) ?>')" class="btn file-preview-btn">
                                    <i class="fas fa-eye"></i> <span>View</span>
                                </button>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 0.875rem;">None</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= date('M d, Y h:i A', strtotime($act['created_at'])) ?></td>
                    <td>
                        <div class="action-buttons-container">
                            <a href="activity.php?edit_id=<?= $act['id'] ?>" class="btn file-preview-btn" style="background-color: #2196F3; text-decoration: none;">
                                <i class="fas fa-edit"></i> <span>Edit</span>
                            </a>
                            <button onclick="confirmDelete(<?= $act['id'] ?>, '<?= htmlspecialchars($act['title']) ?>')" class="btn file-preview-btn" style="background-color: #F44336;">
                                <i class="fas fa-trash"></i> <span>Delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No activities posted yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Form (Hidden) -->
<form id="delete-form" method="POST" style="display: none;">
    <input type="hidden" name="activity_id" id="delete-activity-id">
    <input type="hidden" name="delete_activity" value="1">
</form>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:3000; justify-content:center; align-items:center;">
    <div style="background:white; padding:30px; border-radius:12px; max-width:500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <h3 style="margin-top:0; color: var(--error-color);">
            <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
        </h3>
        <p style="margin: 1.5rem 0;">Are you sure you want to delete <strong id="delete-activity-title"></strong>?</p>
        <p style="color: #64748b; font-size: 0.875rem; margin-bottom: 1.5rem;">
            <i class="fas fa-info-circle"></i> This will also delete all student submissions and grades for this activity. This action cannot be undone.
        </p>
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button onclick="closeDeleteModal()" class="btn" style="background: #6c757d; width: auto; padding: 0.75rem 1.5rem;">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button onclick="submitDelete()" class="btn" style="background: var(--error-color); width: auto; padding: 0.75rem 1.5rem;">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>
function confirmDelete(activityId, activityTitle) {
    document.getElementById('delete-activity-id').value = activityId;
    document.getElementById('delete-activity-title').textContent = activityTitle;
    document.getElementById('delete-modal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
}

function submitDelete() {
    document.getElementById('delete-form').submit();
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

// Auto-dismiss success/error messages
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(function() {
            alert.remove();
        }, 300);
    });
}, 5000);
</script>

<h3 style="margin-top: 2rem; display: flex; align-items: center; gap: 0.5rem;">
    <i class="fas fa-clipboard-check" style="color: var(--primary-color);"></i> Grading Queue
</h3>

<?php
// Get submissions that need grading
$submissions = $conn->query("
    SELECT s.id as sub_id, s.submitted_at, s.file_path, s.text_submission,
           u.first_name, u.last_name, a.title, a.total_score, g.grade, g.feedback
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    JOIN activities a ON s.activity_id = a.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = " . $_SESSION['user_id'] . "
    ORDER BY 
        CASE WHEN g.grade IS NULL THEN 0 ELSE 1 END,
        s.submitted_at DESC
");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
    <input type="text" id="search-grading" class="search-bar form-control" data-target="#table-grading" placeholder="Search by student name or activity..." style="max-width: 400px; flex: 1; min-width: 250px;">
    
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span style="color: #64748b; font-size: 0.875rem;">
            <i class="fas fa-info-circle"></i> 
            <strong><?= $submissions->num_rows ?></strong> submission(s) total
        </span>
    </div>
</div>

<div class="table-wrapper">
    <table id="table-grading">
        <thead>
            <tr>
                <th style="min-width: 150px;"><i class="fas fa-user"></i> Student</th>
                <th style="min-width: 180px;"><i class="fas fa-tasks"></i> Activity</th>
                <th style="min-width: 200px;"><i class="fas fa-file-alt"></i> Submission</th>
                <th style="min-width: 150px;"><i class="fas fa-calendar"></i> Date Submitted</th>
                <th style="min-width: 120px; text-align: center;"><i class="fas fa-star"></i> Grade</th>
                <th style="min-width: 220px;"><i class="fas fa-edit"></i> Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($submissions->num_rows > 0): ?>
                <?php while($sub = $submissions->fetch_assoc()): 
                    $isGraded = $sub['grade'] !== null;
                    $rowStyle = '';
                ?>
                <tr style="<?= $rowStyle ?>">
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.875rem;">
                                <?= strtoupper(substr($sub['first_name'], 0, 1)) ?>
                            </div>
                            <strong><?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?></strong>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-color);">
                            <?= htmlspecialchars($sub['title']) ?>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                            Max Score: <?= $sub['total_score'] ?> pts
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <?php if($sub['file_path']): ?>
                                <button onclick="previewFile('<?= $sub['file_path'] ?>', '<?= htmlspecialchars($sub['original_filename'] ?? basename($sub['file_path'])) ?>')" class="btn file-preview-btn" style="background: var(--primary-color);">
                                    <i class="fas fa-eye"></i> <span>View File</span>
                                </button>
                                <small class="file-name-display" style="color: #64748b;">
                                    <i class="fas fa-paperclip"></i> 
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($sub['original_filename'] ?? basename($sub['file_path'])) ?></span>
                                </small>
                            <?php endif; ?>
                            <?php if($sub['text_submission']): ?>
                                <div style="padding: 0.75rem; background: #f8fafc; border-left: 3px solid var(--primary-color); border-radius: 4px; max-width: 300px;">
                                    <small style="color: #64748b; display: block; margin-bottom: 0.25rem;">
                                        <i class="fas fa-quote-left"></i> Text Response:
                                    </small>
                                    <div style="color: var(--text-color); font-size: 0.875rem; line-height: 1.4; max-height: 100px; overflow-y: auto;">
                                        <?= nl2br(htmlspecialchars(strlen($sub['text_submission']) > 150 ? substr($sub['text_submission'], 0, 150) . '...' : $sub['text_submission'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if(!$sub['file_path'] && !$sub['text_submission']): ?>
                                <span style="color: #94a3b8; font-style: italic;">
                                    <i class="fas fa-minus-circle"></i> No submission
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.875rem;">
                            <?= date('M d, Y', strtotime($sub['submitted_at'])) ?>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b;">
                            <?= date('h:i A', strtotime($sub['submitted_at'])) ?>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <?php if($isGraded): ?>
                            <div style="display: inline-block; padding: 0.5rem 1rem; background: linear-gradient(135deg, var(--success-color), #059669); color: white; border-radius: 20px; font-weight: 700; font-size: 1rem; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);">
                                <?= intval($sub['grade']) ?> / <?= $sub['total_score'] ?>
                            </div>
                            <?php if($sub['feedback']): ?>
                                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #64748b; text-align: left;">
                                    <i class="fas fa-comment"></i> <?= htmlspecialchars($sub['feedback']) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="display: inline-block; padding: 0.5rem 1rem; background: #fef3c7; color: #92400e; border-radius: 20px; font-weight: 600; font-size: 0.875rem;">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display: block; width: 100%;" onsubmit="return validateGrade(this, <?= $sub['total_score'] ?>)">
                            <input type="hidden" name="submission_id" value="<?= $sub['sub_id'] ?>">
                            
                            <div style="width: 100%; min-width: 160px;">
                                <input type="number" name="grade" placeholder="Grade" class="form-control" style="padding: 0.5rem; font-size: 0.875rem; width: 100%; box-sizing: border-box; margin-bottom: 0.25rem;" required value="<?= $sub['grade'] ?>" min="0" max="<?= $sub['total_score'] ?>">
                                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">
                                    Max: <?= $sub['total_score'] ?> pts
                                </small>
                                <input type="text" name="feedback" placeholder="Add feedback (optional)" class="form-control" value="<?= htmlspecialchars($sub['feedback'] ?? '') ?>" style="padding: 0.5rem; font-size: 0.875rem; width: 100%; box-sizing: border-box; margin-bottom: 0.5rem;">
                                <button type="submit" name="submit_grade" class="btn" style="width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; background: var(--primary-color); box-sizing: border-box; white-space: nowrap;">
                                    <i class="fas fa-<?= $isGraded ? 'sync' : 'check' ?>"></i> <?= $isGraded ? 'Update Grade' : 'Submit Grade' ?>
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        <i class="fas fa-inbox fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                        <div style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">No submissions yet</div>
                        <div style="font-size: 0.875rem;">Students haven't submitted any work for grading.</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/teacher_footer.php'; ?>
