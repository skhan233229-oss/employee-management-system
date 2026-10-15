<?php
require_once "db.php";

// Get employees for the dependent dropdown.
$employeesByDepartment = [];

$stmt = $conn->prepare(
    "SELECT id, name, email, phone, department, designation, salary, joining_date
     FROM employees
     WHERE department IS NOT NULL AND department != ''
     ORDER BY name ASC"
);
$stmt->execute();
$employeeResult = $stmt->get_result();

while ($employee = $employeeResult->fetch_assoc()) {
    $dept = $employee["department"];
    $employeesByDepartment[$dept][] = [
        "id" => (int)$employee["id"],
        "name" => $employee["name"],
        "email" => $employee["email"],
        "phone" => $employee["phone"],
        "designation" => $employee["designation"],
        "salary" => $employee["salary"],
        "joining_date" => $employee["joining_date"]
    ];
}
$stmt->close();

$name = "";
$email = "";
$phone = "";
$department = "";
$designation = "";
$salary = "";
$joining_date = "";
$error = "";

$isAjax = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
    strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $designation = trim($_POST["designation"] ?? "");
    $salary = trim($_POST["salary"] ?? "");
    $joining_date = trim($_POST["joining_date"] ?? "");

    if ($name === "" || $email === "") {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (
        $salary !== "" &&
        (!is_numeric($salary) ||
         (float)$salary < 0 ||
         (float)$salary > 99999999.99)
    ) {
        $error = "Please enter a valid salary.";
    } elseif ($joining_date !== "") {
        $dateObject = DateTime::createFromFormat("!Y-m-d", $joining_date);
        if (!$dateObject || $dateObject->format("Y-m-d") !== $joining_date) {
            $error = "Please enter a valid joining date.";
        }
    }

    if ($error !== "") {
        if ($isAjax) {
            http_response_code(422);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode([
                "success" => false,
                "errors" => ["form" => $error]
            ]);
            exit;
        }
    } else {
        $salaryValue = ($salary === "") ? 0 : (float)$salary;
        $dateValue = ($joining_date === "") ? null : $joining_date;

        $stmt = $conn->prepare(
            "INSERT INTO employees
             (name, email, phone, department, designation, salary, joining_date)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sssssds",
            $name,
            $email,
            $phone,
            $department,
            $designation,
            $salaryValue,
            $dateValue
        );

        if ($stmt->execute()) {
            $stmt->close();

            if ($isAjax) {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode([
                    "success" => true,
                    "message" => "Employee added successfully."
                ]);
                exit;
            }

            header("Location: index.php?success=" . urlencode("Employee added successfully."));
            exit;
        }

        if ($stmt->errno === 1062) {
            $error = "This email address is already registered.";
        } else {
            $error = "Unable to add employee. Please try again.";
        }
        $stmt->close();

        if ($isAjax) {
            http_response_code(422);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode([
                "success" => false,
                "errors" => ["form" => $error]
            ]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Employee Management System</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="card p-4 mx-auto" style="max-width: 800px;">
        <h2 class="page-title mb-4">Add New Employee</h2>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
<form id="employeeForm" method="POST" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employee Name *</label>
                    <input type="text" name="name" class="form-control" maxlength="100" required value="<?= htmlspecialchars($name) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" maxlength="100" required value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" maxlength="15" value="<?= htmlspecialchars($phone) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach (["IT", "HR", "Sales", "Finance", "Marketing", "Operations"] as $dept): ?>
                            <option value="<?= htmlspecialchars($dept) ?>" <?= $department === $dept ? "selected" : "" ?>>
                                <?= htmlspecialchars($dept) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Select Existing Employee</label>
                    <select id="employeeDropdown" class="form-select" disabled>
                        <option value="">First select a department</option>
                    </select>
                    <small class="text-muted">Select a department to view its employees.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control" maxlength="100" value="<?= htmlspecialchars($designation) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Salary (₹)</label>
                    <input type="number" name="salary" class="form-control" min="0" max="99999999.99" step="0.01" value="<?= htmlspecialchars($salary) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" value="<?= htmlspecialchars($joining_date) ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Employee</button>
            <a href="index.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

<script>
const employeesByDepartment = <?= json_encode(
    $employeesByDepartment,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const departmentDropdown = document.querySelector('select[name="department"]');
const employeeDropdown = document.getElementById("employeeDropdown");

function updateEmployeeDropdown() {
    const department = departmentDropdown.value;
    const employees = employeesByDepartment[department] || [];
    employeeDropdown.innerHTML = "";

    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.textContent = employees.length ? "Select an employee" : "No employees in this department";
    employeeDropdown.appendChild(defaultOption);
    employeeDropdown.disabled = employees.length === 0;

    employees.forEach(function(employee) {
        const option = document.createElement("option");
        option.value = employee.id;
        option.textContent = employee.name;
        option.dataset.name = employee.name || "";
        option.dataset.email = employee.email || "";
        option.dataset.phone = employee.phone || "";
        option.dataset.designation = employee.designation || "";
        option.dataset.salary = employee.salary || "";
        option.dataset.joiningDate = employee.joining_date || "";
        employeeDropdown.appendChild(option);
    });
}

departmentDropdown.addEventListener("change", updateEmployeeDropdown);
updateEmployeeDropdown();

employeeDropdown.addEventListener("change", function() {
    const option = this.options[this.selectedIndex];
    if (!this.value) return;

    document.querySelector('[name="name"]').value = option.dataset.name || "";
    document.querySelector('[name="email"]').value = option.dataset.email || "";
    document.querySelector('[name="phone"]').value = option.dataset.phone || "";
    document.querySelector('[name="designation"]').value = option.dataset.designation || "";
    document.querySelector('[name="salary"]').value = option.dataset.salary || "";
    document.querySelector('[name="joining_date"]').value = option.dataset.joiningDate || "";
});

const employeeForm = document.getElementById("employeeForm");

employeeForm.addEventListener("submit", async function(event) {
    event.preventDefault();

    const submitButton = employeeForm.querySelector('button[type="submit"]');
    let errorBox = document.getElementById("serverValidationErrors");
    if (errorBox) errorBox.remove();

    submitButton.disabled = true;
    submitButton.textContent = "Saving...";

    try {
        const response = await fetch(employeeForm.action || window.location.href, {
            method: "POST",
            headers: {"X-Requested-With": "XMLHttpRequest"},
            body: new FormData(employeeForm)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            const errors = data.errors || {};
            const message = Object.values(errors).join("\n") || "Unable to save employee.";

            errorBox = document.createElement("div");
            errorBox.id = "serverValidationErrors";
            errorBox.className = "alert alert-danger";
            errorBox.textContent = message;
            employeeForm.parentNode.insertBefore(errorBox, employeeForm);
            return;
        }

        window.location.href = "index.php?success=" + encodeURIComponent(data.message);
    } catch (error) {
        alert("Something went wrong. Please check the server response and try again.");
        console.error(error);
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = "Save Employee";
    }
});
</script>
</body>
</html>
