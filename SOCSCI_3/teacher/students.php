<?php
include '../includes/db.php';
include '../includes/teacher_header.php';

$students = $conn->query("SELECT * FROM users WHERE role='student' ORDER BY last_name ASC");
?>

<h2>List of Students</h2>

<table>
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
                <a href="student_details.php?id=<?= $row['id'] ?>" target="_blank" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">View Details</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/teacher_footer.php'; ?>
