```php
<?php
require_once "db.php";

$search = trim($_GET["search"] ?? "");

// Pagination settings
$limit = 5;
$page = max(1, (int)($_GET["page"] ?? 1));
$offset = ($page - 1) * $limit;

// Count employees matching the search
if ($search !== "") {
    $countStmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM employees
         WHERE name LIKE ? OR email LIKE ? OR department LIKE ?"
    );

    $term = "%" . $search . "%";
    $countStmt->bind_param("sss", $term, $term, $term);
} else {
    $countStmt = $conn->prepare(
        "SELECT COUNT(*) AS total FROM employees"
    );
}

$countStmt->execute();
$totalEmployees = (int)$countStmt->get_result()->fetch_assoc()["total"];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalEmployees / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Fetch employees for the current page
if ($search !== "") {
    $stmt = $conn->prepare(
        "SELECT * FROM employees
         WHERE name LIKE ?
            OR email LIKE ?
            OR department LIKE ?
         ORDER BY id DESC
         LIMIT ? OFFSET ?"
    );

    $stmt->bind_param("sssii", $term, $term, $term, $limit, $offset);
} else {
    $stmt = $conn->prepare(
        "SELECT * FROM employees
         ORDER BY id DESC
         LIMIT ? OFFSET ?"
    );

    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
?>
```


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Employee Management System</title>

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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title">Employee List</h2>

        <a href="add_employee.php" class="btn btn-primary">
            + Add Employee
        </a>
    </div>

    <?php if (isset($_GET["success"])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_GET["success"]) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_GET["error"]) ?>
        </div>
    <?php endif; ?>

    <div class="card p-4">

        <form method="GET" class="row g-2 mb-4">

            <div class="col-md-9">
<input
    type="text"
    id="ajaxSearch"
    name="search"
    class="form-control"
    placeholder="Search by name, email or department"
    value="<?= htmlspecialchars($search) ?>"
    autocomplete="off"
>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    Search
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Reset
                </a>
            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Salary</th>
                        <th>Joining Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

<tbody id="employeeResults">
                <?php if ($result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>
                            <td><?= (int)$row["id"] ?></td>

                            <td>
                                <?= htmlspecialchars($row["name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["email"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["phone"] ?? "") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["department"] ?? "") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["designation"] ?? "") ?>
                            </td>

                            <td>
                                ₹<?= number_format((float)$row["salary"], 2) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["joining_date"] ?? "") ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2">

                                    <a
                                        href="edit_employee.php?id=<?= (int)$row["id"] ?>"
                                        class="btn btn-warning btn-sm"
                                    >
                                        Edit
                                    </a>

                                <button
    type="button"
    class="btn btn-danger btn-sm ajax-delete"
    data-id="<?= (int)$row['id'] ?>"
>
    Delete
</button>

                                </div>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="text-center">
                            No employees found.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </div>
    <?php if ($totalEmployees > 0 && $totalPages > 1): ?>
        <nav class="mt-4" aria-label="Employee pagination">
            <ul class="pagination justify-content-center">

                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                        Previous
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                        Next
                    </a>
                </li>

            </ul>
        </nav>
    <?php endif; ?>
    <div class="footer">
        Employee Management System | PHP and MySQL
    </div>

</div>
<script>
document.addEventListener("click", async function (event) {
    const button = event.target.closest(".ajax-delete");

    if (!button) {
        return;
    }

    const employeeId = button.dataset.id;

    if (!confirm("Are you sure you want to delete this employee?")) {
        return;
    }

    button.disabled = true;
    button.textContent = "Deleting...";

    try {
        const formData = new FormData();
        formData.append("id", employeeId);

        const response = await fetch("delete_employee.php", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Unable to delete employee.");
        }

        const row = button.closest("tr");

        if (row) {
            row.remove();
        }

        alert(data.message);

    } catch (error) {
        alert(error.message || "Something went wrong. Please try again.");

        button.disabled = false;
        button.textContent = "Delete";
    }
});
</script>
</body>
</html>