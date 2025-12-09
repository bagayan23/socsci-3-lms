<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['action'])) {
        header("Location: ../../index.php?error=Invalid request");
        exit();
    }
    
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
            header("Location: ../../index.php?error=Email already registered");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO users (role, email, password, first_name, last_name, middle_name, extension_name, birthday, contact_number, region, province, city, barangay, street, student_school_id, year_level, program, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssssss", $role, $email, $password, $first_name, $last_name, $middle_name, $extension_name, $birthday, $contact_number, $region, $province, $city, $barangay, $street, $student_id, $year, $program, $section);

        if ($stmt->execute()) {
            header("Location: ../../index.php?success=Registration successful! Please login");
            exit();
        } else {
            header("Location: ../../index.php?error=Registration failed: " . urlencode($stmt->error));
            exit();
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
                $_SESSION['initials'] = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

                if ($user['role'] == 'teacher') {
                    header("Location: ../teacher/dashboard.php");
                    exit();
                } else {
                    header("Location: ../student/dashboard.php");
                    exit();
                }
            } else {
                header("Location: ../../index.php?error=Invalid password");
                exit();
            }
        } else {
            header("Location: ../../index.php?error=User not found");
            exit();
        }
    }
}
?>
