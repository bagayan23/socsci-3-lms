<?php
include '../includes/db.php';
include '../includes/student_header.php';

$resources = $conn->query("SELECT r.*, u.first_name, u.last_name FROM resources r JOIN users u ON r.teacher_id = u.id ORDER BY created_at DESC");
?>

<h2>Resources</h2>

<input type="text" id="search-student-resources" class="search-bar form-control" data-target="#table-student-resources" placeholder="Search Resources..." style="margin-bottom: 10px; max-width: 300px;">
<div class="table-wrapper">
<table id="table-student-resources">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Description</th>
            <th>Teacher</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $resources->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
            <td>
                <div class="action-buttons-container">
                    <?php if($row['file_path']): ?>
                        <button onclick="previewFile('<?= htmlspecialchars($row['file_path']) ?>', '<?= htmlspecialchars(basename($row['file_path'])) ?>')" class="btn file-preview-btn" title="View">
                            <i class="fas fa-eye"></i> <span>View</span>
                        </button>
                        <a href="<?= $row['file_path'] ?>" download class="btn file-preview-btn" style="background-color: #10b981; text-decoration: none;" title="Download">
                            <i class="fas fa-download"></i> <span>Download</span>
                        </a>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.875rem;">No File</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php include '../includes/student_footer.php'; ?>
