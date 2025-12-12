<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

if (!function_exists('set_flash_message')) {
    function set_flash_message(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('redirect_with_message')) {
    function redirect_with_message(string $type, string $message, string $location = '../../index.php'): void
    {
        set_flash_message($type, $message);
        header("Location: {$location}");
        exit();
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input(?string $value, int $maxLength = 255): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = strip_tags($value);
        return mb_substr($value, 0, $maxLength);
    }
}

if (!function_exists('validate_name_field')) {
    function validate_name_field(string $value, string $fieldLabel, bool $required = true): string
    {
        $value = sanitize_input($value, 100);

        if ($value === '') {
            if ($required) {
                redirect_with_message('error', "{$fieldLabel} is required.");
            }
            return '';
        }

        if (!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ\s\-'`\.]+$/u", $value)) {
            redirect_with_message('error', "{$fieldLabel} may only contain letters, spaces, apostrophes, hyphens, or periods.");
        }

        return $value;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['action'])) {
        redirect_with_message('error', 'Invalid request.');
    }

    $action = $_POST['action'];

    if ($action == 'register') {
        $role = sanitize_input($_POST['role'] ?? '');
        if (!in_array($role, ['student', 'teacher'], true)) {
            redirect_with_message('error', 'Please select a valid account type.');
        }

        $email_raw = $_POST['email'] ?? '';
        $email = filter_var(trim($email_raw), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            redirect_with_message('error', 'Please enter a valid email address.');
        }

        $password_plain = $_POST['password'] ?? '';
        if (strlen($password_plain) < 8) {
            redirect_with_message('error', 'Password must be at least 8 characters long.');
        }
        $password = password_hash($password_plain, PASSWORD_BCRYPT);

        $first_name = validate_name_field($_POST['first_name'] ?? '', 'First name');
        $last_name = validate_name_field($_POST['last_name'] ?? '', 'Last name');
        $middle_name = validate_name_field($_POST['middle_name'] ?? '', 'Middle name', false);
        $extension_name = validate_name_field($_POST['extension_name'] ?? '', 'Extension name', false);

        $birthday = sanitize_input($_POST['birthday'] ?? '', 10);
        $date = DateTime::createFromFormat('Y-m-d', $birthday);
        $dateErrors = DateTime::getLastErrors();
        if ($birthday === '' || !$date || $dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0) {
            redirect_with_message('error', 'Please provide a valid birthday.');
        }

        $contact_number_raw = isset($_POST['contact_number']) ? $_POST['contact_number'] : '';
        $contact_number = preg_replace('/[\s-]/', '', $contact_number_raw);

        if (!preg_match('/^(?:\+63|63|0)9\d{9}$/', $contact_number)) {
            redirect_with_message('error', 'Please enter a valid Philippine mobile number.');
        }

        // Normalize to 09xxxxxxxxx format for storage consistency
        if (strpos($contact_number, '+63') === 0) {
            $contact_number = '0' . substr($contact_number, 3);
        } elseif (strpos($contact_number, '63') === 0) {
            $contact_number = '0' . substr($contact_number, 2);
        }

        $region = sanitize_input($_POST['region'] ?? '', 100);
        $province = sanitize_input($_POST['province'] ?? '', 100);
        $city = sanitize_input($_POST['city'] ?? '', 100);
        $barangay = sanitize_input($_POST['barangay'] ?? '', 100);
        $street = sanitize_input($_POST['street'] ?? '', 255);

        if ($city === '' || $barangay === '' || $street === '') {
            redirect_with_message('error', 'Please complete your address information.');
        }

        $student_id = sanitize_input($_POST['student_id'] ?? '', 20);
        if ($student_id !== '' && !preg_match('/^\d{2}-\d{4}$/', $student_id)) {
            redirect_with_message('error', 'Student ID must follow the 00-0000 format.');
        }
        $student_id = $student_id !== '' ? $student_id : null;

        $allowed_years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        $year = sanitize_input($_POST['year'] ?? '', 20);
        if ($year !== '' && !in_array($year, $allowed_years, true)) {
            redirect_with_message('error', 'Please select a valid year level.');
        }
        $year = $year !== '' ? $year : null;

        $program = sanitize_input($_POST['program'] ?? '', 100);
        $program = $program !== '' ? $program : null;

        $section = sanitize_input($_POST['section'] ?? '', 5);
        if ($section !== '' && !preg_match('/^[A-Z]$/', $section)) {
            redirect_with_message('error', 'Section must be a single uppercase letter.');
        }
        $section = $section !== '' ? $section : null;

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            redirect_with_message('error', 'Email already registered.');
        }

        $stmt = $conn->prepare("INSERT INTO users (role, email, password, first_name, last_name, middle_name, extension_name, birthday, contact_number, region, province, city, barangay, street, student_school_id, year_level, program, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssssss", $role, $email, $password, $first_name, $last_name, $middle_name, $extension_name, $birthday, $contact_number, $region, $province, $city, $barangay, $street, $student_id, $year, $program, $section);

        if ($stmt->execute()) {
            redirect_with_message('success', 'Registration successful! Please login.');
        } else {
            redirect_with_message('error', 'Registration failed. Please try again.');
        }

    } elseif ($action == 'login') {
        $email_raw = $_POST['email'] ?? '';
        $email = filter_var(trim($email_raw), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            redirect_with_message('error', 'Please enter a valid email address.');
        }

        $password = $_POST['password'] ?? '';
        if ($password === '') {
            redirect_with_message('error', 'Password is required.');
        }

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

                unset($_SESSION['flash']);

                if ($user['role'] == 'teacher') {
                    header("Location: ../teacher/dashboard.php");
                    exit();
                } else {
                    header("Location: ../student/dashboard.php");
                    exit();
                }
            } else {
                redirect_with_message('error', 'Invalid password.');
            }
        } else {
            redirect_with_message('error', 'User not found.');
        }
    }
}
?>
