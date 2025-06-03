<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

// Decode input
$data = json_decode(file_get_contents("php://input"), true);

// Validate incoming data
if (!isset($data["items"]) || !isset($data["total"])) {
    echo json_encode(["message" => "❌ Invalid input data."]);
    exit;
}

// DB credentials
$host = "localhost";
$db = "live_shop";
$user = "root";
$pass = "";

// Connect to DB
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Sanitize and prepare data
$items_json = json_encode($data["items"], JSON_UNESCAPED_UNICODE);
$total = floatval($data["total"]);

// Use prepared statement for safety
$stmt = $conn->prepare("INSERT INTO orders (items, total) VALUES (?, ?)");
$stmt->bind_param("sd", $items_json, $total);

if ($stmt->execute()) {
    // Send email
    $to = "techdealer368@example.com"; // ✅ Update to your actual email
    $subject = "New Order Received";
    $message = "🛒 Order Details:\nTotal: ₹" . number_format($total, 2) . "\n\nItems:\n" . print_r($data["items"], true);
    $headers = "From: noreply@intechshop.com";

    // Only send if mail() is supported
    @mail($to, $subject, $message, $headers);

    echo json_encode(["message" => "✅ Order placed successfully and saved to database."]);
} else {
    echo json_encode(["message" => "❌ Error saving order: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
