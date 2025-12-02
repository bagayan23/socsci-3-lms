<?php
include '../includes/db.php';
include '../includes/student_header.php';

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_work'])) {
    $activity_id = $_POST['activity_id'];
    $student_id = $_SESSION['user_id'];
    $text_submission = $_POST['text_submission'];
    
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../uploads/';
        $original_filename = basename($_FILES['file']['name']);
        $file_path = $upload_dir . time() . '_' . $student_id . '_' . $original_filename;
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
    }

    $stmt = $conn->prepare("INSERT INTO submissions (activity_id, student_id, file_path, text_submission) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $activity_id, $student_id, $file_path, $text_submission);
    
    if ($stmt->execute()) {
        echo "<script>alert('Submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error submitting work.');</script>";
    }
}

// Fetch Activities
$activities = $conn->query("
    SELECT a.*, u.first_name, u.last_name, s.id as submission_id, s.submitted_at, g.grade, g.feedback
    FROM activities a 
    JOIN users u ON a.teacher_id = u.id 
    LEFT JOIN submissions s ON a.id = s.activity_id AND s.student_id = " . $_SESSION['user_id'] . "
    LEFT JOIN grades g ON s.id = g.submission_id
    ORDER BY a.created_at DESC
");
?>

<h2>Activities & Quizzes</h2>

<?php while($row = $activities->fetch_assoc()): ?>
    <div class="card" style="max-width: 100%; margin-bottom: 1rem;">
        <div style="display:flex; justify-content:space-between;">
            <h3><?= htmlspecialchars($row['title']) ?> <span style="font-size: 0.8em; color: #777;">(<?= ucfirst($row['type']) ?>)</span></h3>
            <span>Posted by: <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></span>
        </div>
        <p><?= htmlspecialchars($row['description']) ?></p>
        <?php if($row['file_path']): ?>
            <p>Attachment: <a href="<?= $row['file_path'] ?>" target="_blank">View File</a></p>
        <?php endif; ?>
        
        <hr>

        <?php if($row['submission_id']): ?>
            <div style="background-color: #e8f5e9; padding: 10px; border-radius: 5px;">
                <p><strong>Status:</strong> Submitted on <?= $row['submitted_at'] ?></p>
                <?php if($row['grade'] !== null): ?>
                    <p><strong>Grade:</strong> <?= $row['grade'] ?></p>
                    <p><strong>Feedback:</strong> <?= htmlspecialchars($row['feedback']) ?></p>
                <?php else: ?>
                    <p><em>Pending Grade</em></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="activity_id" value="<?= $row['id'] ?>">
                <div class="form-group">
                    <label>Your Answer / Submission Text</label>
                    <textarea name="text_submission" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Attach File (if required)</label>
                    <input type="file" name="file" class="form-control">
                </div>
                <button type="submit" name="submit_work" class="btn">Submit Work</button>
            </form>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

<?php include '../includes/student_footer.php'; ?>
