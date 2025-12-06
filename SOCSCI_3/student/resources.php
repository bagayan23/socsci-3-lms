<?php
include '../includes/db.php';
include '../includes/student_header.php';

$resources = $conn->query("SELECT r.*, u.first_name, u.last_name FROM resources r JOIN users u ON r.teacher_id = u.id ORDER BY created_at DESC");
?>

<h2>Resources</h2>

<input type="text" id="search-student-resources" class="search-bar form-control" data-target="#table-student-resources" placeholder="Search Resources..." style="margin-bottom: 10px; max-width: 300px;">
<table id="table-student-resources">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Description</th>
            <th>Teacher</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $resources->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: flex-start;">
                    <?php if($row['file_path']): ?>
                        <button onclick="previewFile('<?= htmlspecialchars($row['file_path']) ?>', '<?= htmlspecialchars(basename($row['file_path'])) ?>')" class="btn" style="width: auto; padding: 0.5rem 1rem; display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.875rem;" title="View">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <a href="<?= $row['file_path'] ?>" download class="btn" style="width: auto; padding: 0.5rem 1rem; background-color: #10b981; display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.875rem; text-decoration: none;" title="Download">
                            <i class="fas fa-download"></i> Download
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

<?php include '../includes/student_footer.php'; ?>
