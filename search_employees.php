```php
<?php
require_once "db.php";

header("Content-Type: text/html; charset=UTF-8");

$search = trim($_GET["search"] ?? "");

$sql = "SELECT id, name, email, phone, department, designation,
               salary, joining_date
        FROM employees
        WHERE name LIKE ?
           OR email LIKE ?
           OR department LIKE ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);

$term = "%" . $search . "%";
$stmt->bind_param("sss", $term, $term, $term);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<tr><td colspan='9' class='text-center'>No employees found.</td></tr>";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";

        echo "<td>" . (int)$row["id"] . "</td>";
        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["phone"] ?? "") . "</td>";
        echo "<td>" . htmlspecialchars($row["department"] ?? "") . "</td>";
        echo "<td>" . htmlspecialchars($row["designation"] ?? "") . "</td>";
        echo "<td>₹" . number_format((float)$row["salary"], 2) . "</td>";
        echo "<td>" . htmlspecialchars($row["joining_date"] ?? "") . "</td>";

        echo "<td><div class='d-flex gap-2'>";

        echo "<a href='edit_employee.php?id=" . (int)$row["id"] . "' class='btn btn-warning btn-sm'>Edit</a>";

       echo "<button type='button' class='btn btn-danger btn-sm ajax-delete' data-id='" . (int)$row["id"] . "'>Delete</button>";
        echo "</div></td>";

        echo "</tr>";
    }
}

$stmt->close();
$conn->close();
?>
```