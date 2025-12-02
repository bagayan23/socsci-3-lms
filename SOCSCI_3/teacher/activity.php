<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

// Handle Activity Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_activity'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $teacher_id = $_SESSION['user_id'];
    
    $file_path = null;
    $original_filename = null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../uploads/';
        $original_filename = basename($_FILES['file']['name']);
        $file_path = $upload_dir . time() . '_' . $original_filename;
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
    }

    $stmt = $conn->prepare("INSERT INTO activities (teacher_id, title, description, type, file_path, original_filename) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $teacher_id, $title, $description, $type, $file_path, $original_filename);
    $stmt->execute();
}

// Handle Grading
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_grade'])) {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];
    $teacher_id = $_SESSION['user_id'];

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
}

$activities = $conn->query("SELECT * FROM activities WHERE teacher_id=" . $_SESSION['user_id'] . " ORDER BY created_at DESC");
?>

<h2>Activities</h2>

<div class="card" style="margin-bottom: 2rem; max-width: 100%;">
    <h3>Create Activity / Quiz</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control">
                <option value="activity">Activity</option>
                <option value="quiz">Quiz</option>
            </select>
        </div>
        <div class="form-group">
            <label>Attach File (Optional)</label>
            <input type="file" name="file" class="form-control">
        </div>
        <button type="submit" name="create_activity" class="btn">Create</button>
    </form>
</div>

<h3>Posted Activities</h3>
<div style="overflow-x: auto; margin-bottom: 2rem;">
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
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
                    <td>
                        <?php if($act['file_path']): ?>
                            <button onclick="previewFile('<?= $act['file_path'] ?>')" class="btn" style="padding: 5px 10px; width: auto; font-size: 0.8rem;">View</button>
                        <?php else: ?>
                            None
                        <?php endif; ?>
                    </td>
                    <td><?= $act['created_at'] ?></td>
                    <td>
                         <!-- Actions as Buttons -->
                         <button class="btn" style="padding: 5px 10px; width: auto; background-color: #2196F3;">Edit</button>
                         <button class="btn" style="padding: 5px 10px; width: auto; background-color: #F44336;">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No activities posted yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h3>Grading Queue</h3>
<?php
// Get submissions that need grading
$submissions = $conn->query("
    SELECT s.id as sub_id, s.submitted_at, s.file_path, s.text_submission, 
           u.first_name, u.last_name, a.title, g.grade
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    JOIN activities a ON s.activity_id = a.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = " . $_SESSION['user_id'] . "
    ORDER BY s.submitted_at DESC
");
?>
<div style="overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Activity</th>
                <th>Submission</th>
                <th>Date</th>
                <th>Grade</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($sub = $submissions->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?></td>
                <td><?= htmlspecialchars($sub['title']) ?></td>
                <td>
                    <?php if($sub['file_path']): ?>
                        <button onclick="previewFile('<?= $sub['file_path'] ?>')" class="btn" style="padding: 5px 10px; width: auto; font-size: 0.8rem;">View File</button>
                    <?php endif; ?>
                    <?php if($sub['text_submission']): ?>
                        <p><?= htmlspecialchars($sub['text_submission']) ?></p>
                    <?php endif; ?>
                </td>
                <td><?= $sub['submitted_at'] ?></td>
                <td><?= $sub['grade'] !== null ? $sub['grade'] : 'N/A' ?></td>
                <td>
                    <form method="POST" style="display:flex; gap:5px;">
                        <input type="hidden" name="submission_id" value="<?= $sub['sub_id'] ?>">
                        <input type="number" name="grade" placeholder="Grade" step="0.01" style="width: 60px;" required value="<?= $sub['grade'] ?>">
                        <input type="text" name="feedback" placeholder="Feedback" style="width: 100px;">
                        <button type="submit" name="submit_grade" class="btn" style="width: auto; padding: 5px;">Save</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/teacher_footer.php'; ?>
