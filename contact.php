<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'message'=>'Method not allowed.']);
  exit;
}

$to = 'arzify06@gmail.com';
$name = trim($_POST['name'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'New Arzify enquiry');
$message = trim($_POST['message'] ?? '');
$source = trim($_POST['source'] ?? '');

if (!$name || !$lastName || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$message) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'message'=>'Please complete all required fields.']);
  exit;
}

$fullName = $name . ' ' . $lastName;
$subject = 'Arzify enquiry: ' . preg_replace('/[\r\n]+/', ' ', $subject);
$body = "Name: {$fullName}\nEmail: {$email}\nHow they found Arzify: {$source}\n\nMessage:\n{$message}\n";
$headers = "From: Arzify Website <noreply@" . ($_SERVER['SERVER_NAME'] ?? 'localhost') . ">\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = mail($to, $subject, $body, $headers);
if ($sent) {
  echo json_encode(['ok'=>true,'message'=>'Thanks — your enquiry has been sent.']);
} else {
  http_response_code(500);
  echo json_encode(['ok'=>false,'message'=>'Mail could not be sent by this server.']);
}
?>