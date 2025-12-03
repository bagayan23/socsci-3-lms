<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_resource'])) {
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $teacher_id = $_SESSION['user_id'];
    
    // File upload logic
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../uploads/';
        $original_filename = basename($_FILES['file']['name']);
        $target_file = $upload_dir . time() . '_' . $original_filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO resources (teacher_id, subject, description, file_path, original_filename) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $teacher_id, $subject, $description, $target_file, $original_filename);
            $stmt->execute();
        }
    } else {
        // Just text thread
        $stmt = $conn->prepare("INSERT INTO resources (teacher_id, subject, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $teacher_id, $subject, $description);
        $stmt->execute();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM resources WHERE id=$id AND teacher_id=" . $_SESSION['user_id']);
    echo "<script>window.location.href='resources.php';</script>";
}

// Handle Update
if (isset($_POST['update_resource'])) {
    $id = intval($_POST['resource_id']);
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    
    $conn->query("UPDATE resources SET subject='$subject', description='$description' WHERE id=$id AND teacher_id=" . $_SESSION['user_id']);
    echo "<script>window.location.href='resources.php';</script>";
}

$resources = $conn->query("SELECT * FROM resources WHERE teacher_id=" . $_SESSION['user_id'] . " ORDER BY created_at DESC");
$edit_resource = null;
if(isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_resource = $conn->query("SELECT * FROM resources WHERE id=$edit_id AND teacher_id=" . $_SESSION['user_id'])->fetch_assoc();
}
?>

<h2>Resources</h2>

<div class="card" style="margin-bottom: 2rem; margin-top: 0px; max-width: 100%;">
    <h3><?= $edit_resource ? 'Edit Resource' : 'Create Thread / Upload Resource' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <?php if($edit_resource): ?>
            <input type="hidden" name="resource_id" value="<?= $edit_resource['id'] ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" required value="<?= $edit_resource ? htmlspecialchars($edit_resource['subject']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"><?= $edit_resource ? htmlspecialchars($edit_resource['description']) : '' ?></textarea>
        </div>
        
        <?php if(!$edit_resource): ?>
        <div class="form-group">
            <label>Attach File</label>
            <input type="file" name="file" class="form-control">
        </div>
        <?php endif; ?>
        
        <button type="submit" name="<?= $edit_resource ? 'update_resource' : 'upload_resource' ?>" class="btn"><?= $edit_resource ? 'Update' : 'Post Resource' ?></button>
        <?php if($edit_resource): ?>
            <a href="resources.php" class="btn" style="background-color: #777; width: auto; display: inline-block; margin-top: 10px;">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<h3>Uploaded Resources</h3>
<input type="text" id="search-resources" class="search-bar form-control" data-target="#table-resources" placeholder="Search Resources..." style="margin-bottom: 10px; max-width: 300px;">
<table id="table-resources">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Description</th>
            <th>File</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $resources->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>
                <?php if($row['file_path']): ?>
                    <a href="#" onclick="previewFile('<?= $row['file_path'] ?>'); return false;"><?= htmlspecialchars($row['original_filename']) ?></a>
                <?php else: ?>
                    No File
                <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <?php if($row['file_path']): ?>
                    <button onclick="previewFile('<?= $row['file_path'] ?>')" class="btn" style="width: auto; padding: 5px 10px; margin-right: 5px;" title="View"><i class="fas fa-eye"></i></button>
                <?php endif; ?>
                <a href="resources.php?edit=<?= $row['id'] ?>" class="btn" style="background-color: #1976D2; width: auto; padding: 5px 10px; display: inline-block; margin-right: 5px;" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="resources.php?delete=<?= $row['id'] ?>" class="btn" style="background-color: #d32f2f; width: auto; padding: 5px 10px; display: inline-block;" onclick="return confirm('Are you sure?')" title="Delete"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/teacher_footer.php'; ?>
