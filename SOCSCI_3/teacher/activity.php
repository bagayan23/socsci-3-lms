<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM activities WHERE id=$id AND teacher_id=" . $_SESSION['user_id']);
    echo "<script>window.location.href='activity.php';</script>";
}

// Handle Activity Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_activity'])) {
    $id = intval($_POST['activity_id']);
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $total_score = intval($_POST['total_score']);

    // Check if file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../uploads/';
        $original_filename = basename($_FILES['file']['name']);
        $file_path = $upload_dir . time() . '_' . $original_filename;
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);

        $stmt = $conn->prepare("UPDATE activities SET title=?, description=?, type=?, total_score=?, file_path=?, original_filename=? WHERE id=? AND teacher_id=?");
        $stmt->bind_param("sssisii", $title, $description, $type, $total_score, $file_path, $original_filename, $id, $_SESSION['user_id']);
    } else {
        $stmt = $conn->prepare("UPDATE activities SET title=?, description=?, type=?, total_score=? WHERE id=? AND teacher_id=?");
        $stmt->bind_param("sssiii", $title, $description, $type, $total_score, $id, $_SESSION['user_id']);
    }
    $stmt->execute();
    echo "<script>window.location.href='activity.php';</script>";
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

$edit_activity = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_activity = $conn->query("SELECT * FROM activities WHERE id=$edit_id AND teacher_id=" . $_SESSION['user_id'])->fetch_assoc();
}
?>

<h2>Activities</h2>

<div class="card" style="margin-bottom: 2rem; max-width: 100%; box-sizing: border-box; overflow-x: hidden;">
    <h3><?= $edit_activity ? 'Edit Activity / Quiz' : 'Create Activity / Quiz' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <?php if($edit_activity): ?>
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
            <input type="file" name="file" class="form-control">
            <?php if($edit_activity && $edit_activity['file_path']): ?>
                <p><small>Current file: <?= htmlspecialchars($edit_activity['original_filename']) ?></small></p>
            <?php endif; ?>
        </div>
        <button type="submit" name="<?= $edit_activity ? 'update_activity' : 'create_activity' ?>" class="btn"><?= $edit_activity ? 'Update' : 'Create' ?></button>
        <?php if($edit_activity): ?>
            <a href="activity.php" class="btn" style="background-color: #777; width: auto; display: inline-block; margin-top: 10px;">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<h3>Posted Activities</h3>
<input type="text" id="search-activities" class="search-bar form-control" data-target="#table-activities" placeholder="Search Activities..." style="margin-bottom: 10px; max-width: 300px;">
<div style="overflow-x: auto; margin-bottom: 2rem;">
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
                        <?php if($act['file_path']): ?>
                            <button onclick="previewFile('<?= $act['file_path'] ?>')" class="btn" style="padding: 5px 10px; width: auto; font-size: 0.8rem;">View</button>
                        <?php else: ?>
                            None
                        <?php endif; ?>
                    </td>
                    <td><?= $act['created_at'] ?></td>
                    <td>
                         <!-- Actions as Buttons -->
                         <a href="activity.php?edit=<?= $act['id'] ?>" class="btn" style="padding: 5px 10px; width: auto; background-color: #2196F3; display: inline-block;">Edit</a>
                         <a href="activity.php?delete=<?= $act['id'] ?>" class="btn" style="padding: 5px 10px; width: auto; background-color: #F44336; display: inline-block;" onclick="return confirm('Are you sure you want to delete this activity?');">Delete</a>
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
<input type="text" id="search-grading" class="search-bar form-control" data-target="#table-grading" placeholder="Search Grading Queue..." style="margin-bottom: 10px; max-width: 300px;">
<?php
// Get submissions that need grading
$submissions = $conn->query("
    SELECT s.id as sub_id, s.submitted_at, s.file_path, s.text_submission, 
           u.first_name, u.last_name, a.title, a.total_score, g.grade
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    JOIN activities a ON s.activity_id = a.id
    LEFT JOIN grades g ON s.id = g.submission_id
    WHERE a.teacher_id = " . $_SESSION['user_id'] . "
    ORDER BY s.submitted_at DESC
");
?>
<div style="overflow-x: auto;">
    <table id="table-grading">
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
                <td><?= $sub['grade'] !== null ? $sub['grade'] . ' / ' . $sub['total_score'] : 'N/A' ?></td>
                <td>
                    <form method="POST" style="display:flex; gap:5px;" onsubmit="return validateGrade(this, <?= $sub['total_score'] ?>)">
                        <input type="hidden" name="submission_id" value="<?= $sub['sub_id'] ?>">
                        <input type="number" name="grade" placeholder="Max: <?= $sub['total_score'] ?>" step="0.01" style="width: 80px;" required value="<?= $sub['grade'] ?>" min="0" max="<?= $sub['total_score'] ?>">
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
