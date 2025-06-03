<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$host = "localhost";
$db = "live_shop";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  echo json_encode(["message" => "Database connection failed: " . $conn->connect_error]);
  exit;
}

$items = $conn->real_escape_string(json_encode($data["items"]));
$total = (float)$data["total"];

$sql = "INSERT INTO orders (items, total) VALUES ('$items', $total)";

if ($conn->query($sql) === TRUE) {
  // Send Email using PHPMailer
  $mail = new PHPMailer(true);

  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Or your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com'; // 🔁 Your Gmail address
    $mail->Password = 'your-app-password'; // 🔁 Use an App Password (not your Gmail password)
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('your-email@gmail.com', 'IN Tech Shop');
    $mail->addAddress('tech.anu5656@gmail.com'); // 🔁 Receiver’s email

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Order Received';
    $mail->Body    = "<h3>New Order</h3><p>Total: ₹$total</p><pre>" . print_r($data["items"], true) . "</pre>";

    $mail->send();
    echo json_encode(["message" => "✅ Order placed and email sent!"]);
  } catch (Exception $e) {
    echo json_encode(["message" => "Order saved but email not sent. Error: {$mail->ErrorInfo}"]);
  }
} else {
  echo json_encode(["message" => "❌ Error saving order: " . $conn->error]);
}

$conn->close();
?>
