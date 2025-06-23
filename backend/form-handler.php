<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");

// CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (
    !$data ||
    !isset($data['email']) ||
    !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
    !isset($data['sendTo']) ||
    !filter_var($data['sendTo'], FILTER_VALIDATE_EMAIL)
) {
    echo json_encode(['success' => false, 'message' => 'Некоректні дані']);
    exit;
}

$name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($data['email'] ?? '', ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($data['message'] ?? '', ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars($data['subject'] ?? 'Нове повідомлення з форми', ENT_QUOTES, 'UTF-8');

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = $_ENV['SMTP_PORT'];

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom($_ENV['SMTP_FROM'], 'Feedback Bot');
    $mail->addAddress($_ENV['SMTP_TO']);

    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $mail->Subject = $encoded_subject;

    $body = "Ім’я: {$name}\n";
    $body .= "Email: {$email}\n\n";
    $body .= "Повідомлення:\n{$message}";
    $mail->Body = $body;

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
}
