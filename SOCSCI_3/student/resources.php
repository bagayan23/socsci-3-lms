<?php
include '../includes/db.php';
include '../includes/student_header.php';

$resources = $conn->query("SELECT r.*, u.first_name, u.last_name FROM resources r JOIN users u ON r.teacher_id = u.id ORDER BY created_at DESC");
?>

<h2>Resources</h2>

<!-- Preview Modal (Simplified as a hidden div that shows up) -->
<div id="file-preview-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center;">
    <div style="background:white; padding:20px; width:80%; height:80%; position:relative; display:flex; flex-direction:column;">
        <button onclick="document.getElementById('file-preview-modal').style.display='none'" style="align-self:flex-end; cursor:pointer;">Close</button>
        <iframe id="preview-frame" style="width:100%; height:100%; border:none;"></iframe>
    </div>
</div>

<script>
function previewFile(url) {
    document.getElementById('preview-frame').src = url;
    document.getElementById('file-preview-modal').style.display = 'flex';
}
</script>

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
                    <button onclick="previewFile('<?= $row['file_path'] ?>')" class="btn" style="width: auto; padding: 5px 10px; margin-right: 5px;">View</button>
                    <a href="<?= $row['file_path'] ?>" download class="btn" style="width: auto; padding: 5px 10px; background-color: #4CAF50;">Download</a>
                <?php else: ?>
                    No File
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/student_footer.php'; ?>
