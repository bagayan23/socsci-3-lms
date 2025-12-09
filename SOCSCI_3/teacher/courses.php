<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

// Handle course actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $code = $_POST['code'];
            $name = $_POST['name'];
            
            $stmt = $conn->prepare("INSERT INTO courses (code, name) VALUES (?, ?)");
            $stmt->bind_param("ss", $code, $name);
            
            if ($stmt->execute()) {
                $success = "Course added successfully!";
            } else {
                $error = "Error adding course: " . $stmt->error;
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = $_POST['id'];
            $code = $_POST['code'];
            $name = $_POST['name'];
            
            $stmt = $conn->prepare("UPDATE courses SET code = ?, name = ? WHERE id = ?");
            $stmt->bind_param("ssi", $code, $name, $id);
            
            if ($stmt->execute()) {
                $success = "Course updated successfully!";
            } else {
                $error = "Error updating course: " . $stmt->error;
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];
            
            $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $success = "Course deleted successfully!";
            } else {
                $error = "Error deleting course: " . $stmt->error;
            }
        }
    }
}

// Get all courses
$courses = $conn->query("SELECT * FROM courses ORDER BY code ASC");
?>

<style>
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
    }
    
    .modal.show {
        display: flex;
    }
    
    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    @media (max-width: 480px) {
        .modal-content {
            padding: 1.5rem;
        }
    }
</style>

<div class="card">
    <h2><i class="fas fa-book"></i> Manage Courses/Programs</h2>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <button onclick="showAddModal()" class="btn" style="margin-bottom: 1.5rem;">
        <i class="fas fa-plus"></i> Add New Course
    </button>
    
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($courses->num_rows > 0): ?>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($course['code']) ?></td>
                            <td><?= htmlspecialchars($course['name']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="showEditModal(<?= $course['id'] ?>, '<?= addslashes($course['code']) ?>', '<?= addslashes($course['name']) ?>')" class="btn" style="width: auto; padding: 0.5rem 1rem; background: var(--primary-color); font-size: 0.875rem;">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="deleteCourse(<?= $course['id'] ?>, '<?= addslashes($course['code']) ?>')" class="btn" style="width: auto; padding: 0.5rem 1rem; background: var(--error-color); font-size: 0.875rem;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; color: #64748b; margin-bottom: 1rem;"></i>
                            <p>No courses available. Add your first course!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Course Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-top: 0;"><i class="fas fa-plus-circle"></i> Add New Course</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" name="code" class="form-control" placeholder="e.g., BSCS" required>
            </div>
            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., BS Computer Science" required>
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="submit" class="btn" style="flex: 1;">
                    <i class="fas fa-save"></i> Save
                </button>
                <button type="button" onclick="closeModal('addModal')" class="btn" style="flex: 1; background: #64748b;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Course Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-top: 0;"><i class="fas fa-edit"></i> Edit Course</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" name="code" id="edit_code" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="submit" class="btn" style="flex: 1;">
                    <i class="fas fa-save"></i> Update
                </button>
                <button type="button" onclick="closeModal('editModal')" class="btn" style="flex: 1; background: #64748b;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form (hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function showAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function showEditModal(id, code, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_code').value = code;
    document.getElementById('edit_name').value = name;
    document.getElementById('editModal').classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

function deleteCourse(id, code) {
    if (confirm(`Are you sure you want to delete the course "${code}"?\n\nWarning: This may affect students enrolled in this program.`)) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            closeModal(modal.id);
        });
    }
});
</script>

<?php include '../includes/teacher_footer.php'; ?>
