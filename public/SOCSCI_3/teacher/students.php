<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

$students = $conn->query("SELECT * FROM users WHERE role='student' ORDER BY last_name ASC");
?>

<h2>List of Students</h2>

<input type="text" id="search-students" class="search-bar form-control" data-target="#table-students" placeholder="Search Students..." style="margin-bottom: 10px; max-width: 300px;">
<div class="table-wrapper">
<table id="table-students">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Year</th>
            <th>Section</th>
            <th>Program</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $students->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['student_school_id']) ?></td>
            <td><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']) ?></td>
            <td><?= htmlspecialchars($row['year_level']) ?></td>
            <td><?= htmlspecialchars($row['section']) ?></td>
            <td><?= htmlspecialchars($row['program']) ?></td>
            <td>
                <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: flex-start;">
                    <a href="student_details.php?id=<?= $row['id'] ?>" class="btn" style="width: auto; padding: 0.5rem 1rem; display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.875rem; text-decoration: none;">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php include '../includes/teacher_footer.php'; ?>
