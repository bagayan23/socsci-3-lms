# Backend Documentation for XAMPP Integration

## PHP and MySQL Backend Structure

This document outlines the necessary structure required for integrating PHP and MySQL with XAMPP. It will detail the database schemas and the API endpoint specifications needed for the application to function optimally.

### 1. Database Structure
#### 1.1 Database Name: `socsci_lms`

**Tables:**

- **users**  
  - `id` (INT, Primary Key, Auto Increment)  
  - `username` (VARCHAR(255), Unique)  
  - `password` (VARCHAR(255))  
  - `email` (VARCHAR(255), Unique)  
  - `created_at` (TIMESTAMP)

- **courses**  
  - `id` (INT, Primary Key, Auto Increment)  
  - `course_name` (VARCHAR(255))  
  - `created_at` (TIMESTAMP)  
  - `created_by` (INT, Foreign Key referencing `users.id`)

- **enrollments**  
  - `id` (INT, Primary Key, Auto Increment)  
  - `user_id` (INT, Foreign Key referencing `users.id`)  
  - `course_id` (INT, Foreign Key referencing `courses.id`)  
  - `enrollment_date` (TIMESTAMP)

### 2. API Endpoint Specifications
#### 2.1 Base URL
```
http://localhost/socsci_lms/api/
```
#### 2.2 Endpoints

- **User Registration**  
  - **Endpoint:** `/register`  
  - **Method:** POST  
  - **Request Body:**  
    ```json
    {
      "username": "string",
      "password": "string",
      "email": "string"
    }
    ```  
  - **Responses:**  
    - Success: `201 Created`  
    - Error: `400 Bad Request`

- **User Login**  
  - **Endpoint:** `/login`  
  - **Method:** POST  
  - **Request Body:**  
    ```json
    {
      "username": "string",
      "password": "string"
    }
    ```  
  - **Responses:**  
    - Success: `200 OK`  
    - Error: `401 Unauthorized`

- **Get Courses**  
  - **Endpoint:** `/courses`  
  - **Method:** GET  
  - **Responses:**  
    - Success: `200 OK`, returns a list of courses in JSON format.
    - Error: `500 Internal Server Error`

- **Enroll in Course**  
  - **Endpoint:** `/enroll`  
  - **Method:** POST  
  - **Request Body:**  
    ```json
    {
      "user_id": "int",
      "course_id": "int"
    }
    ```  
  - **Responses:**  
    - Success: `201 Created`  
    - Error: `400 Bad Request`

### 3. Conclusion
This documentation provides the basic structure required for the backend of the socsci-lms application. Ensure that you have the correct PHP and MySQL configurations in XAMPP for proper functionality.
