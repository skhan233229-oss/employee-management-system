<?php
require_once "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php?error=" . urlencode("Invalid employee ID."));
    exit;
}

$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    header("Location: index.php?error=" . urlencode("Employee not found."));
    exit;
}

$error = "";

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
    } elseif ($salary !== "" &&
              (!is_numeric($salary) ||
               (float)$salary < 0 ||
               (float)$salary > 99999999.99)) {
        $error = "Please enter a valid salary.";
    } elseif ($joining_date !== "" &&
              !DateTime::createFromFormat("!Y-m-d", $joining_date)) {
        $error = "Please enter a valid joining date.";
    } elseif ($joining_date !== "" &&
              DateTime::createFromFormat("!Y-m-d", $joining_date)->format("Y-m-d") !== $joining_date) {
        $error = "Please enter a valid joining date.";
    } else {

        $salary_value = $salary === "" ? 0 : (float)$salary;
        $date_value = $joining_date === "" ? null : $joining_date;

        $stmt = $conn->prepare(
            "UPDATE employees
             SET name = ?, email = ?, phone = ?, department = ?,
                 designation = ?, salary = ?, joining_date = ?
             WHERE id = ?"
        );

       $stmt->bind_param(
    "sssssdsi",
    $name,
    $email,
    $phone,
    $department,
    $designation,
    $salary_value,
    $date_value,
    $id
);

        if ($stmt->execute()) {
            $stmt->close();

            header("Location: index.php?success=" . urlencode("Employee updated successfully."));
            exit;
        }

        if ($stmt->errno === 1062) {
            $error = "This email address is already registered.";
        } else {
            $error = "Unable to update employee. Please try again.";
        }

        $stmt->close();
    }

    $employee["name"] = $name;
    $employee["email"] = $email;
    $employee["phone"] = $phone;
    $employee["department"] = $department;
    $employee["designation"] = $designation;
    $employee["salary"] = $salary;
    $employee["joining_date"] = $joining_date;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Employee</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Employee Management System
        </a>
    </div>
</nav>

<div class="container mt-5">

    <div class="card p-4 mx-auto" style="max-width: 800px;">

        <h2 class="page-title mb-4">Edit Employee</h2>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Employee Name *</label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        maxlength="100"
                        required
                        value="<?= htmlspecialchars($employee["name"]) ?>"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        maxlength="100"
                        required
                        value="<?= htmlspecialchars($employee["email"]) ?>"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>

                    <input
                        type="tel"
                        name="phone"
                        class="form-control"
                        maxlength="15"
                        value="<?= htmlspecialchars($employee["phone"] ?? "") ?>"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>

                    <select name="department" class="form-select">
                        <option value="">Select Department</option>

                        <?php foreach (["IT", "HR", "Sales", "Finance", "Marketing", "Operations"] as $dept): ?>
                            <option
                                value="<?= $dept ?>"
                                <?= $employee["department"] === $dept ? "selected" : "" ?>
                            >
                                <?= $dept ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Designation</label>

                    <input
                        type="text"
                        name="designation"
                        class="form-control"
                        maxlength="100"
                        value="<?= htmlspecialchars($employee["designation"] ?? "") ?>"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Salary (₹)</label>

                    <input
                        type="number"
                        name="salary"
                        class="form-control"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        value="<?= htmlspecialchars((string)$employee["salary"]) ?>"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Joining Date</label>

                    <input
                        type="date"
                        name="joining_date"
                        class="form-control"
                        value="<?= htmlspecialchars($employee["joining_date"] ?? "") ?>"
                    >
                </div>

            </div>

            <button type="submit" class="btn btn-primary">
                Update Employee
            </button>

            <a href="index.php" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>
</html>