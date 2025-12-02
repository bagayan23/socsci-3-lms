<?php
include '../includes/db.php';
// This page opens in a new tab, so we need a full structure, but maybe without the sidebar if it's just a report.
// However, reusing the header keeps auth check. I will reuse header but hide sidebar via CSS if needed, or just keep it standard.
// The prompt says "student details will open another tabs to the teacher browser".
include '../includes/teacher_header.php';

if (!isset($_GET['id'])) {
    echo "Student ID not provided.";
    exit;
}

$student_id = intval($_GET['id']);
$student_query = $conn->query("SELECT * FROM users WHERE id = $student_id AND role='student'");
$student = $student_query->fetch_assoc();

if (!$student) {
    echo "Student not found.";
    exit;
}

// Fetch grades
$grades_query = "
    SELECT a.title, s.submitted_at, g.grade, g.feedback 
    FROM submissions s 
    JOIN activities a ON s.activity_id = a.id 
    LEFT JOIN grades g ON s.id = g.submission_id 
    WHERE s.student_id = $student_id
";
$grades = $conn->query($grades_query);

?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h2>Student Details</h2>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
            <p><strong>Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
            <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_school_id']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
            <p><strong>Contact:</strong> <?= htmlspecialchars($student['contact_number']) ?></p>
        </div>
        <div>
            <p><strong>Program:</strong> <?= htmlspecialchars($student['program']) ?></p>
            <p><strong>Year & Section:</strong> <?= htmlspecialchars($student['year_level']) ?> - <?= htmlspecialchars($student['section']) ?></p>
            <p><strong>Address:</strong>
                <?= htmlspecialchars($student['street']) ?>,
                <span class="psgc-field" data-type="barangays" data-code="<?= $student['barangay'] ?>"><?= $student['barangay'] ?></span>,
                <span class="psgc-field" data-type="cities-municipalities" data-code="<?= $student['city'] ?>"><?= $student['city'] ?></span>,
                <span class="psgc-field" data-type="provinces" data-code="<?= $student['province'] ?>"><?= $student['province'] ?></span>
            </p>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fields = document.querySelectorAll('.psgc-field');
        fields.forEach(field => {
            const type = field.dataset.type;
            const code = field.dataset.code;
            if(code && type) {
                fetch(`https://psgc.gitlab.io/api/${type}/${code}/`)
                .then(res => res.json())
                .then(data => {
                    if(data && data.name) {
                        field.textContent = data.name;
                    }
                })
                .catch(err => console.error('Failed to load PSGC data', err));
            }
        });
    });
    </script>

    <h3>Grades & Activities</h3>
    <input type="text" id="search-student-grades" class="search-bar form-control" data-target="#table-student-grades" placeholder="Search Activity..." style="margin-bottom: 10px; max-width: 300px;">
    <table id="table-student-grades">
        <thead>
            <tr>
                <th>Activity</th>
                <th>Submitted At</th>
                <th>Grade</th>
                <th>Feedback</th>
            </tr>
        </thead>
        <tbody>
            <?php while($g = $grades->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($g['title']) ?></td>
                <td><?= htmlspecialchars($g['submitted_at']) ?></td>
                <td><?= $g['grade'] !== null ? $g['grade'] : 'Not Graded' ?></td>
                <td><?= htmlspecialchars($g['feedback']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/teacher_footer.php'; ?>
