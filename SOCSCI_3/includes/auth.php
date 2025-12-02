<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == 'register') {
        $role = $_POST['role'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $middle_name = $_POST['middle_name'];
        $extension_name = $_POST['extension_name'];
        $birthday = $_POST['birthday'];
        $contact_number = $_POST['contact_number'];
        
        $region = $_POST['region']; // Storing code or name, ideally name but API sends code. 
        // For simplicity, we store what's sent. The JS sends code. 
        // To be perfect we might want to fetch name or just store code.
        // Assuming we store what is sent.
        $province = $_POST['province'];
        $city = $_POST['city'];
        $barangay = $_POST['barangay'];
        $street = $_POST['street'];

        $student_id = !empty($_POST['student_id']) ? $_POST['student_id'] : null;
        $year = !empty($_POST['year']) ? $_POST['year'] : null;
        $program = !empty($_POST['program']) ? $_POST['program'] : null;
        $section = !empty($_POST['section']) ? $_POST['section'] : null;

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            echo "<script>alert('Email already registered!'); window.location.href='../index.php';</script>";
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO users (role, email, password, first_name, last_name, middle_name, extension_name, birthday, contact_number, region, province, city, barangay, street, student_school_id, year_level, program, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssssss", $role, $email, $password, $first_name, $last_name, $middle_name, $extension_name, $birthday, $contact_number, $region, $province, $city, $barangay, $street, $student_id, $year, $program, $section);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='../index.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

    } elseif ($action == 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, role, password, first_name, last_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

                if ($user['role'] == 'teacher') {
                    header("Location: ../teacher/dashboard.php");
                } else {
                    header("Location: ../student/dashboard.php");
                }
            } else {
                echo "<script>alert('Invalid password!'); window.location.href='../index.php';</script>";
            }
        } else {
            echo "<script>alert('User not found!'); window.location.href='../index.php';</script>";
        }
    }
}
?>
