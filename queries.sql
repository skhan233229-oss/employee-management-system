```sql
-- Select the database
USE employee_management22;

-- 1. Display all employees
SELECT * FROM employees;

-- 2. Display employee names and email addresses
SELECT name, email FROM employees;

-- 3. Display employees in the HR department
SELECT * FROM employees
WHERE department = 'HR';

-- 4. Display employees with salary greater than 30000
SELECT * FROM employees
WHERE salary > 30000;

-- 5. Display employees in descending order of salary
SELECT * FROM employees
ORDER BY salary DESC;

-- 6. Count total employees
SELECT COUNT(*) AS total_employees
FROM employees;

-- 7. Find the average salary
SELECT AVG(salary) AS average_salary
FROM employees;

-- 8. Find the highest salary
SELECT MAX(salary) AS highest_salary
FROM employees;

-- 9. Find the lowest salary
SELECT MIN(salary) AS lowest_salary
FROM employees;

-- 10. Calculate total salary
SELECT SUM(salary) AS total_salary
FROM employees;

-- 11. Count employees in each department
SELECT department, COUNT(*) AS total_employees
FROM employees
GROUP BY department;

-- 12. Find average salary in each department
SELECT department, AVG(salary) AS average_salary
FROM employees
GROUP BY department;

-- 13. Show departments with more than one employee
SELECT department, COUNT(*) AS total_employees
FROM employees
GROUP BY department
HAVING COUNT(*) > 1;

-- 14. Search employees whose names contain 'a'
SELECT * FROM employees
WHERE name LIKE '%a%';

-- 15. Display employees who joined after 1 January 2025
SELECT * FROM employees
WHERE joining_date > '2025-01-01';

-- 16. Join employees with departments
SELECT
    e.name,
    e.email,
    d.department_name
FROM employees e
INNER JOIN departments d
ON e.department = d.department_name;

-- 17. Show all employees and their departments
SELECT
    e.name,
    e.email,
    d.department_name
FROM employees e
LEFT JOIN departments d
ON e.department = d.department_name;

-- 18. Find employees earning above the average salary
SELECT * FROM employees
WHERE salary > (SELECT AVG(salary) FROM employees);

-- 19. Count employees by designation
SELECT designation, COUNT(*) AS total_employees
FROM employees
GROUP BY designation;

-- 20. Find departments with total salary greater than 30000
SELECT department, SUM(salary) AS total_salary
FROM employees
GROUP BY department
HAVING SUM(salary) > 30000;
```