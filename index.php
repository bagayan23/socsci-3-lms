<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOCSCI-3 CONTEMPORARY WORLD</title>
    <link rel="stylesheet" href="SOCSCI_3/css/style.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional page-specific styles */
        .flashcard-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }
<?php 
// Load available courses from database
include 'SOCSCI_3/includes/db.php';
$courses_query = $conn->query("SELECT * FROM courses ORDER BY code ASC");
?>
        
        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .indicator.active {
            background: var(--primary-color);
            transform: scale(1.2);
        }
    </style>
</head>
<body>

    <header>
        <h1>SOCSCI-3 CONTEMPORARY WORLD</h1>
    </header>

    <div class="landing-container">
        
        <!-- Flashcards -->
        <div class="flashcard-container">
            <div class="flashcard active" data-index="0">
                <img src="SOCSCI_3/images/globalization.png" alt="Globalization" onerror="this.src='https://via.placeholder.com/800x600/6366f1/ffffff?text=Globalization'">
                <div class="flashcard-caption">
                    <h3>Globalization</h3>
                    <p>Exploring the interconnectedness of modern societies.</p>
                </div>
            </div>
            <div class="flashcard" data-index="1">
                <img src="SOCSCI_3/images/sustainability.png" alt="Sustainability" onerror="this.src='https://via.placeholder.com/800x600/10b981/ffffff?text=Sustainability'">
                <div class="flashcard-caption">
                    <h3>Sustainability</h3>
                    <p>Addressing environmental challenges in the contemporary world.</p>
                </div>
            </div>
            <div class="flashcard" data-index="2">
                <img src="SOCSCI_3/images/digital_age.png" alt="Digital Age" onerror="this.src='https://via.placeholder.com/800x600/f59e0b/ffffff?text=Digital+Age'">
                <div class="flashcard-caption">
                    <h3>Digital Age</h3>
                    <p>The impact of technology on human interaction.</p>
                </div>
            </div>
            
            <!-- Flashcard Indicators -->
            <div class="flashcard-indicators">
                <div class="indicator active" data-slide="0"></div>
                <div class="indicator" data-slide="1"></div>
                <div class="indicator" data-slide="2"></div>
            </div>
        </div>

        <!-- Auth Section -->
        <div class="auth-section">
            
            <!-- Login Card -->
            <div id="login-card" class="card">
                <h2>Sign In</h2>
                <form action="SOCSCI_3/includes/auth.php" method="POST" novalidate>
                    <input type="hidden" name="action" value="login">
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="login_email">Email</label>
                        <input type="email" id="login_email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group password-wrapper">
                        <label for="login_password">Password</label>
                        <input type="password" id="login_password" name="password" class="form-control" placeholder="Enter password" required>
                        <svg class="eye-icon-login toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    <a id="show-signup" class="toggle-link">Don't have an account? Sign up</a>
                </form>
            </div>

            <!-- Signup Card -->
            <div id="signup-card" class="card hidden full-width-card">
                <h2>Sign Up</h2>
                <form action="SOCSCI_3/includes/auth.php" method="POST" novalidate>
                    <input type="hidden" name="action" value="register">
                    
                    <div class="signup-grid">
                        <div class="form-group">
                            <label for="role">Account Type</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="">Select Account Type</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" class="form-control" data-validate="text" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" class="form-control" data-validate="text" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name (Optional)</label>
                            <input type="text" name="middle_name" class="form-control" data-validate="text">
                        </div>
                        <div class="form-group">
                            <label>Extension Name (Optional)</label>
                            <input type="text" name="extension_name" class="form-control" data-validate="text">
                        </div>
                        <div class="form-group">
                            <label>Birthday</label>
                            <input type="date" name="birthday" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" data-validate="number" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group password-wrapper">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                            <svg class="eye-icon-signup toggle-password" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                        </div>

                        <!-- Address Section -->
                        <div class="form-group">
                            <label>Region</label>
                            <select name="region" id="region" class="form-control">
                                <option value="">Select Region</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Province</label>
                            <select name="province" id="province" class="form-control">
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>City/Municipality</label>
                            <select name="city" id="city" class="form-control" required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Barangay</label>
                            <select name="barangay" id="barangay" class="form-control" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Street/House No.</label>
                            <input type="text" name="street" class="form-control" required>
                        </div>
                    </div>

                    <!-- Student Specific Fields -->
                    <div id="student-fields" class="hidden">
                        <div class="signup-grid">
                            <div class="form-group">
                                <label>Student ID (00-0000)</label>
                                <input type="text" name="student_id" class="form-control" placeholder="00-0000" pattern="\d{2}-\d{4}">
                            </div>
                            <div class="form-group">
                                <label>Year Level</label>
                                <select name="year" class="form-control">
                                    <option value="">Select Year</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Program</label>
                                <select name="program" class="form-control">
                                    <option value="">Select Program</option>
                                    <?php 
                                    if ($courses_query && $courses_query->num_rows > 0):
                                        while ($course = $courses_query->fetch_assoc()): 
                                    ?>
                                        <option value="<?= htmlspecialchars($course['code']) ?>">
                                            <?= htmlspecialchars($course['name']) ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <option value="">No programs available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Section</label>
                                <select name="section" class="form-control">
                                    <option value="">Select Section</option>
                                    <?php foreach(range('A','Z') as $char): ?>
                                        <option value="<?= $char ?>"><?= $char ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="margin-top: 1rem;">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </button>
                    <a id="show-login" class="toggle-link">Already have an account? Sign in</a>
                </form>
            </div>

        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>About the Course</h3>
                <p>SOCSCI-3 Contemporary World explores the interconnectedness of modern societies, addressing globalization, sustainability, and the digital age.</p>
            </div>
            <div class="footer-section">
                <h3>Contact Information</h3>
                <p><i class="fas fa-envelope"></i> info@socsci3.edu</p>
                <p><i class="fas fa-phone"></i> +63 900 000 0000</p>
                <p><i class="fas fa-map-marker-alt"></i> Quezon City University</p>
            </div>
            <div class="footer-section">
                <h3>Developers</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="https://web.facebook.com/bagayan.231" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; color: #e2e8f0; text-decoration: none; transition: color 0.3s;">
                            <i class="fab fa-facebook" style="width: 20px;"></i> John Wilmer Bagayan
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="https://www.facebook.com/share/17G91Ujema/" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; color: #e2e8f0; text-decoration: none; transition: color 0.3s;">
                            <i class="fab fa-facebook" style="width: 20px;"></i> Juan Nathaniel Batallones
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="https://www.facebook.com/share/17Dnsk5BPk/" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; color: #e2e8f0; text-decoration: none; transition: color 0.3s;">
                            <i class="fab fa-facebook" style="width: 20px;"></i> Zydriff Bernardino
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="https://www.facebook.com/jorenarcel.buagas?mibextid=ZbWKwL" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; color: #e2e8f0; text-decoration: none; transition: color 0.3s;">
                            <i class="fab fa-facebook" style="width: 20px;"></i> Joren Arcel Buagas
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="https://www.facebook.com/share/1FqELDeGj1/" target="_blank" style="display: flex; align-items: center; gap: 0.5rem; color: #e2e8f0; text-decoration: none; transition: color 0.3s;">
                            <i class="fab fa-facebook" style="width: 20px;"></i> Matt Henry Benaventura
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>Published: <?= date('F d, Y') ?> | &copy; <?= date('Y') ?> SOCSCI-3</p>
        </div>
    </footer>

    <script src="SOCSCI_3/js/script.js"></script>
</body>
</html>
