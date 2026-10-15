# Employee Management System

## 1. Project Description

The Employee Management System is a web application developed
using PHP, MySQL, HTML, CSS, and Bootstrap.

It helps manage employee records, including employee details,
departments, designations, salary, and joining dates.

## 2. Features

- Admin login and logout
- Add new employees
- View employee records
- Edit employee details
- Delete employee records
- Search employees by name, email, or department
- Input validation
- Success and error messages
- Prepared statements for database operations

## 3. Technologies Used

- Frontend: HTML, CSS, Bootstrap
- Backend: PHP
- Database: MySQL/MariaDB
- Local Server: XAMPP

## 4. Project Setup

1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Copy the project folder into:

   C:\xampp\htdocs\employee_management22

4. Open phpMyAdmin:

   http://localhost/phpmyadmin

5. Create or select the database:

   employee_management22

6. Create the required database tables.
7. Configure the database connection in db.php.
8. Open the application in your browser:

   http://localhost/employee_management22/index.php

## 5. Database Configuration

The database connection is configured in db.php.

- Host: localhost
- Username: root
- Password: Empty (default local XAMPP setup)
- Database: employee_management22

Update these settings if your local MySQL configuration differs.

## 6. Database Schema

### Table: admins

Stores administrator login details.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| username | VARCHAR(50) | Admin username |
| password | VARCHAR(255) | Hashed admin password |

### Table: departments

Stores department information used by the application.

Note: Confirm the actual columns in phpMyAdmin.

### Table: employees

Stores employee information.

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto-increment |
| name | VARCHAR(100) | Employee name |
| email | VARCHAR(100) | Employee email, unique |
| phone | VARCHAR(15) | Phone number |
| department | VARCHAR(100) | Department name |
| designation | VARCHAR(100) | Job designation |
| salary | DECIMAL(10,2) | Employee salary |
| joining_date | DATE | Employee joining date |
| created_at | TIMESTAMP | Record creation time |

## 7. Security and Validation

- Prepared statements are used for database operations.
- Required fields are validated.
- Email addresses are checked for valid format.
- Duplicate email addresses are handled.
- Passwords are verified using password hashing.
- Output is escaped using htmlspecialchars() where appropriate.

## 8. How to Use

1. Open the application in your browser.
2. Log in with a valid administrator account if login protection is configured.
3. Add, view, edit, search, or delete employee records.
4. Check success and error messages after each operation.

## 9. Author

Employee Management System Project