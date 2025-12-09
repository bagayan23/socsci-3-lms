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
    // Only allow teacher to delete their own resources
    $conn->query("DELETE FROM resources WHERE id=$id AND teacher_id=" . $_SESSION['user_id']);
    echo "<script>window.location.href='resources.php';</script>";
}

// Handle Update
if (isset($_POST['update_resource'])) {
    $id = intval($_POST['resource_id']);
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $teacher_id = $_SESSION['user_id'];
    
    // Check if new file is uploaded
    $file_sql = "";
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        // Get old file path to delete it
        $old_resource = $conn->query("SELECT file_path FROM resources WHERE id=$id AND teacher_id=$teacher_id")->fetch_assoc();
        if ($old_resource && $old_resource['file_path'] && file_exists($old_resource['file_path'])) {
            unlink($old_resource['file_path']);
        }
        
        // Upload new file
        $upload_dir = '../uploads/';
        $original_filename = basename($_FILES['file']['name']);
        $target_file = $upload_dir . time() . '_' . $original_filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $file_sql = ", file_path='$target_file', original_filename='$original_filename'";
        }
    }
    
    $conn->query("UPDATE resources SET subject='$subject', description='$description' $file_sql WHERE id=$id AND teacher_id=$teacher_id");
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
        
        <div class="form-group">
            <label><?= $edit_resource ? 'Replace File (optional)' : 'Attach File' ?></label>
            <?php if($edit_resource && $edit_resource['file_path']): ?>
                <div style="margin-bottom: 0.75rem; padding: 0.75rem; background: #f1f5f9; border-radius: 6px; border-left: 3px solid var(--primary-color);">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file" style="color: var(--primary-color);"></i>
                        <span style="color: var(--text-color); font-weight: 500;">Current file:</span>
                        <span style="color: #64748b;"><?= htmlspecialchars($edit_resource['original_filename']) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" name="file" class="form-control" style="padding: 0.75rem;">
            <?php if($edit_resource && $edit_resource['file_path']): ?>
                <small style="color: #64748b; font-size: 0.875rem; display: block; margin-top: 0.5rem;">
                    Upload a new file to replace the current one, or leave empty to keep the existing file.
                </small>
            <?php endif; ?>
        </div>
        
        <button type="submit" name="<?= $edit_resource ? 'update_resource' : 'upload_resource' ?>" class="btn"><?= $edit_resource ? 'Update' : 'Post Resource' ?></button>
        <?php if($edit_resource): ?>
            <a href="resources.php" class="btn" style="background-color: #777; width: auto; display: inline-block; margin-top: 10px;">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<h3>Uploaded Resources</h3>
<input type="text" id="search-resources" class="search-bar form-control" data-target="#table-resources" placeholder="Search Resources..." style="margin-bottom: 10px; max-width: 300px;">
<div class="table-wrapper">
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
                    <span class="file-name-display">
                        <i class="fas fa-file" style="color: var(--primary-color);"></i>
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($row['original_filename']) ?></span>
                    </span>
                <?php else: ?>
                    <span style="color: #94a3b8; font-size: 0.875rem;">No File</span>
                <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <div class="action-buttons-container">
                    <?php if($row['file_path']): ?>
                        <button onclick="previewFile('<?= htmlspecialchars($row['file_path']) ?>', '<?= htmlspecialchars($row['original_filename'] ?? basename($row['file_path'])) ?>')" class="btn file-preview-btn" title="View">
                            <i class="fas fa-eye"></i> <span>View</span>
                        </button>
                    <?php endif; ?>
                    <a href="resources.php?edit=<?= $row['id'] ?>" class="btn file-preview-btn" style="background-color: #2196F3; text-decoration: none;" title="Edit">
                        <i class="fas fa-edit"></i> <span>Edit</span>
                    </a>
                    <a href="resources.php?delete=<?= $row['id'] ?>" class="btn file-preview-btn" style="background-color: #ef4444; text-decoration: none;" onclick="return confirm('Are you sure you want to delete this resource?')" title="Delete">
                        <i class="fas fa-trash"></i> <span>Delete</span>
                    </a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php include '../includes/teacher_footer.php'; ?>
