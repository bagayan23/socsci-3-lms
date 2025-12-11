<?php
include '../includes/db.php';
include '../includes/student_header.php';

$user_id = $_SESSION['user_id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    
    // Optional: Password update
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $password_sql = ", password='$password'";
    }

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, contact_number=? $password_sql WHERE id=?");
    $stmt->bind_param("sssi", $first_name, $last_name, $contact_number, $user_id);
    
    if ($stmt->execute()) {
        $msg = "Profile updated successfully!";
        $_SESSION['name'] = $first_name . ' ' . $last_name;
        $_SESSION['initials'] = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    } else {
        $msg = "Error updating profile.";
    }
}

$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
?>

<h2>Edit Profile</h2>

<?php if($msg): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px;"><?= $msg ?></div>
<?php endif; ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <form method="POST">
        <div class="form-group" style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #6366f1, #818cf8); color: white; font-size: 3rem; font-weight: 600; border: 3px solid var(--primary-color); box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
        </div>
        
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($user['contact_number']) ?>" required>
        </div>
        <div class="form-group">
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>

<?php include '../includes/student_footer.php'; ?>
