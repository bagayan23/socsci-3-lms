<?php
include '../includes/db.php';
include '../includes/student_header.php';

$resources = $conn->query("SELECT r.*, u.first_name, u.last_name FROM resources r JOIN users u ON r.teacher_id = u.id ORDER BY created_at DESC");
?>

<h2>Resources</h2>

<table>
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
                <?php if($row['file_path']): ?>
                    <button onclick="previewFile('<?= $row['file_path'] ?>')" class="btn" style="width: auto; padding: 5px 10px; margin-right: 5px;" title="View"><i class="fas fa-eye"></i></button>
                    <a href="<?= $row['file_path'] ?>" download class="btn" style="width: auto; padding: 5px 10px; background-color: #4CAF50;" title="Download"><i class="fas fa-download"></i></a>
                <?php else: ?>
                    No File
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/student_footer.php'; ?>
