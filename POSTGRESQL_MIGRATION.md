# PostgreSQL Migration Guide for SOCSCI-3 LMS

## Database Connection Updated
Both database connection files have been updated to use PostgreSQL with PDO:
- `public/SOCSCI_3/includes/db.php`
- `SOCSCI_3/includes/db.php`

## Connection Details
- **Host:** dpg-d4sva8pr0fns73f3k6qg-a.oregon-postgres.render.com
- **Database:** socsci3_lms
- **Port:** 5432
- **SSL Mode:** Required (essential for Render.com)

## Key Changes from MySQL to PostgreSQL

### 1. Database Connection
**Before (MySQLi):**
```php
$conn = new mysqli($servername, $username, $password, $dbname);
```

**After (PDO):**
```php
$conn = new PDO($dsn, $user, $pass, $options);
```

### 2. Prepared Statements
**Before (MySQLi):**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
```

**After (PDO):**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

### 3. Insert with Auto-Increment ID
**Before (MySQLi):**
```php
$stmt->execute();
$id = $conn->insert_id;
```

**After (PostgreSQL/PDO):**
```php
$stmt->execute();
$id = $conn->lastInsertId();
```

### 4. Row Count Check
**Before (MySQLi):**
```php
if ($result->num_rows > 0) { ... }
```

**After (PDO):**
```php
if ($stmt->rowCount() > 0) { ... }
// OR
if ($stmt->fetch()) { ... }
```

### 5. Fetching Multiple Rows
**Before (MySQLi):**
```php
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
```

**After (PDO):**
```php
$data = $stmt->fetchAll();
// OR
while ($row = $stmt->fetch()) {
    $data[] = $row;
}
```

### 6. Column Names in PostgreSQL
- `student_school_id` → `student_id` (already updated in schema)
- ENUM types → VARCHAR with CHECK constraints
- `AUTO_INCREMENT` → `SERIAL`
- `CURRENT_TIMESTAMP` remains the same

### 7. SQL Syntax Differences
**LIMIT/OFFSET:**
- MySQL: `LIMIT 10 OFFSET 20`
- PostgreSQL: Same syntax works

**String Concatenation:**
- MySQL: `CONCAT(first_name, ' ', last_name)`
- PostgreSQL: `first_name || ' ' || last_name` OR `CONCAT(first_name, ' ', last_name)`

**REGEX:**
- MySQL: `column REGEXP '^[0-9]+$'`
- PostgreSQL: `column ~ '^[0-9]+$'`

## Files Already Converted to PDO
✅ `public/SOCSCI_3/includes/db.php`
✅ `SOCSCI_3/includes/db.php`
✅ `public/SOCSCI_3/api/auth/login.php`
✅ `public/SOCSCI_3/api/auth/register.php`

## Files Needing Conversion
The following files still use MySQLi and need to be converted to PDO:

1. `api/auth/session.php`
2. `api/auth/logout.php` (minimal changes)
3. `api/get_courses.php`
4. `api/student/dashboard.php`
5. `api/student/activities.php`
6. `api/student/resources.php`
7. `api/teacher/dashboard.php`
8. `api/teacher/activity.php`
9. `api/teacher/resources.php`
10. `api/teacher/students.php`
11. `api/teacher/courses.php`

## PostgreSQL Schema
Run the SQL script in `database_postgresql.sql` on your Render.com PostgreSQL database to create all tables with proper:
- SERIAL primary keys
- Proper indexes
- Foreign key constraints
- CHECK constraints for ENUM-like fields
- Automatic trigger for graded_at timestamp

## Testing Checklist
After migration, test:
- [ ] Login functionality
- [ ] Registration (student & teacher)
- [ ] Session validation
- [ ] Dashboard data loading
- [ ] Activity submissions
- [ ] Resource management
- [ ] Grading system
- [ ] File uploads
- [ ] Student/Teacher CRUD operations

## Common Errors and Fixes

### Error: "could not connect to server"
- Check SSL mode in connection string
- Verify Render.com database is active
- Check firewall settings

### Error: "relation does not exist"
- Table name is case-sensitive in PostgreSQL
- Run database_postgresql.sql to create tables

### Error: "column does not exist"
- Check column name spelling (case-sensitive)
- Verify schema matches (e.g., student_id vs student_school_id)

### Error: "invalid input syntax for type integer"
- PostgreSQL is stricter with types
- Cast strings to integers: `CAST(column AS INTEGER)` or `column::integer`
