<?php
require_once "db.php";

$isAjax = (
    isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
    strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"
);

function sendResponse($success, $message, $status = 200)
{
    global $isAjax;

    if ($isAjax) {
        http_response_code($status);
        header("Content-Type: application/json; charset=UTF-8");

        echo json_encode([
            "success" => $success,
            "message" => $message
        ]);
        exit;
    }

    $parameter = $success ? "success" : "error";

    header(
        "Location: index.php?" .
        $parameter . "=" . urlencode($message)
    );
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Invalid request method.", 405);
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    sendResponse(false, "Invalid employee ID.", 400);
}

try {
    $stmt = $conn->prepare(
        "DELETE FROM employees WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        sendResponse(true, "Employee deleted successfully.");
    } else {
        $stmt->close();
        sendResponse(false, "Employee not found.", 404);
    }

} catch (Throwable $e) {
    error_log("Employee deletion failed: " . $e->getMessage());

    sendResponse(
        false,
        "Unable to delete employee. Please try again.",
        500
    );
}
?>