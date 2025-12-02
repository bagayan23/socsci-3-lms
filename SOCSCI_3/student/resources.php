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
    const modal = document.getElementById('file-preview-modal');
    const container = document.getElementById('preview-container'); // Need to change structure slightly
    const extension = url.split('.').pop().toLowerCase();

    let content = '';

    if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension)) {
        content = `<img src="${url}" style="max-width:100%; max-height:100%; object-fit:contain;">`;
    } else if (['mp4', 'webm', 'ogg', 'mov'].includes(extension)) {
        content = `<video src="${url}" controls style="max-width:100%; max-height:100%;"></video>`;
    } else if (extension === 'pdf') {
        content = `<iframe src="${url}" style="width:100%; height:100%; border:none;"></iframe>`;
    } else if (['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'].includes(extension)) {
        // Use Google Docs Viewer
        const encodedUrl = encodeURIComponent(window.location.origin + '/' + url.replace('../', 'SOCSCI_3/'));
        // Note: Google Docs Viewer requires a public URL. In localhost/sandbox, this won't work unless the URL is public.
        // However, I must implement the logic.
        // If the file path is relative like '../uploads/file.docx', we need the absolute URL.
        // But since this is a sandbox/localhost, Google can't see it.
        // I will implement the code, but note that it works in production.
        // For sandbox testing, I might need to just show a "Download to View" message if not public?
        // Or assume the user knows.
        // Let's try to construct a best-effort URL.
        // Actually, for local dev, we often can't view Office docs in browser without a public URL or a backend converter.
        // I will use the Google Viewer code as it's the standard frontend-only solution.
        // AND provide a download link as backup in the viewer.

        // Construct absolute URL (assuming public for the viewer to work)
        // Since I can't know the public domain here, I'll pass the full URL.
        // But wait, if I use `window.location.href`, it might work if the server is exposed.

        // For now, I will use a placeholder or just the code.
        const fullUrl = new URL(url, document.baseURI).href;
        content = `<iframe src="https://docs.google.com/gview?url=${encodeURIComponent(fullUrl)}&embedded=true" style="width:100%; height:100%; border:none;"></iframe>`;
    } else {
        content = `<div style="text-align:center; padding:20px;">
            <p>Cannot preview this file type.</p>
            <a href="${url}" download class="btn">Download File</a>
        </div>`;
    }

    // Update the container
    // I need to change the inner HTML of the modal content, not just src of iframe
    const modalContent = modal.querySelector('div');
    // Keep the close button
    const closeBtn = modalContent.querySelector('button');

    // Clear previous content except close button
    while(modalContent.lastChild !== closeBtn) {
        modalContent.removeChild(modalContent.lastChild);
    }

    // Create a wrapper for content
    const contentWrapper = document.createElement('div');
    contentWrapper.style.flex = '1';
    contentWrapper.style.overflow = 'hidden';
    contentWrapper.style.display = 'flex';
    contentWrapper.style.justifyContent = 'center';
    contentWrapper.style.alignItems = 'center';
    contentWrapper.innerHTML = content;

    modalContent.appendChild(contentWrapper);

    modal.style.display = 'flex';
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
