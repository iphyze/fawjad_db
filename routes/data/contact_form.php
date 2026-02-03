<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable('./');
$dotenv->load();

header('Content-Type: application/json');

// === Input sanitization function ===
function sanitizeInput($data)
{
  if (is_array($data)) {
    return array_map('sanitizeInput', $data);
  }
  return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// === Rate limiting (session-based) ===
session_start();
$rateLimitSeconds = 60;
$lastSubmission = $_SESSION['last_contact_form_submission'] ?? 0;
if (time() - $lastSubmission < $rateLimitSeconds) {
  http_response_code(429);
  echo json_encode(["message" => "Please wait a bit before submitting again."]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Accept both JSON and form-encoded
  $data = $_POST;
  if (empty($data)) {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
  }

  $data = sanitizeInput($data);

  // Extract fields
  $fullName = $data['fullName'] ?? '';
  $email = $data['email'] ?? '';
  $phone = $data['phone'] ?? '';
  $message = $data['message'] ?? '';
  $botField = $data['botField'] ?? '';

  // Honeypot check
  if (!empty($botField)) {
    http_response_code(403);
    echo json_encode(["message" => "Spam detected."]);
    exit;
  }

  // Validation
  $errors = [];
  if (empty($fullName)) $errors['fullName'] = "Full Name is required.";
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid Email is required.";
  if (empty($phone)) $errors['phone'] = "Phone number is required.";
  if (empty($message)) $errors['message'] = "Message is required.";

  $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
  if (!empty($email) && !preg_match($emailRegex, $email)) {
    $errors['email'] = "Please provide a valid email address.";
  }

  if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["errors" => $errors]);
    exit;
  }

  // === Insert into DB first ===
  global $conn; // Provided by index.php include
  $stmt = mysqli_prepare($conn, "INSERT INTO contact_form (fullname, email, phone, message, submitted_at) VALUES (?, ?, ?, ?, NOW())");
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . mysqli_error($conn)]);
    exit;
  }
  mysqli_stmt_bind_param($stmt, 'ssss', $fullName, $email, $phone, $message);

  if (mysqli_stmt_execute($stmt)) {
    // Only send emails if DB insert succeeded
    $siteName = "Fawjad Global";

    try {
      $mail = new PHPMailer(true);

      // SMTP settings
      $mail->isSMTP();
      $mail->Host = $_ENV['SMTP_HOST'];
      $mail->SMTPAuth = true;
      $mail->Username = $_ENV['SMTP_USER'];
      $mail->Password = $_ENV['SMTP_PASS'];
      $mail->SMTPSecure = 'tls';
      $mail->Port = $_ENV['SMTP_PORT'];
      $mail->CharSet = 'UTF-8';

      // === Email to Admin ===
      $mail->setFrom($_ENV['SMTP_USER'], "$siteName Contact Form");
      $mail->addAddress($_ENV['SMTP_USER']);
      $mail->addBCC('iphyze@gmail.com');
      // $mail->addBCC('info@surefields.com');
      $mail->isHTML(true);
      $mail->Subject = "New Contact Form Submission - $siteName";

      ob_start();
      require __DIR__ . '/contact-admin-email.php';

      $mail->Body = ob_get_clean();

      $mail->send();

      $_SESSION['last_contact_form_submission'] = time();
      http_response_code(200);
      echo json_encode(["message" => "Your message has been sent successfully."]);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(["message" => "Mailer Error: {$mail->ErrorInfo}"]);
    }
  } else {
    http_response_code(500);
    echo json_encode(["message" => "Error saving your message. Please try again later."]);
  }
  mysqli_stmt_close($stmt);
  exit;
} else {
  http_response_code(404);
  echo json_encode(["message" => "Page not found."]);
  exit;
}
