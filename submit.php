<?php
header('Content-Type: application/json; charset=utf-8');

$config = [
    'club_name' => 'Aurelia Athletic Club',
    'club_email' => 'hello@aurelia.fit',
    'whatsapp' => '923000000000',
];

function clean_value($value, $limit = 800) {
    $value = trim(preg_replace('/[\r\n]+/', ' ', (string)($value ?? '')));
    return mb_substr($value, 0, $limit);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Honeypot: quiet success for basic automated spam.
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true, 'message' => 'Request received.']);
    exit;
}

$name = clean_value($_POST['name'] ?? '', 120);
$email = clean_value($_POST['email'] ?? '', 160);
$interest = clean_value($_POST['interest'] ?? 'General enquiry', 120);
$message = clean_value($_POST['message'] ?? 'None', 800);

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please provide your name and a valid email address.']);
    exit;
}

$received = date('c');
$folder = __DIR__ . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not create the leads folder.']);
    exit;
}

$file = $folder . DIRECTORY_SEPARATOR . 'leads.csv';
$is_new = !file_exists($file) || filesize($file) === 0;
$handle = fopen($file, 'ab');
if (!$handle) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save your enquiry.']);
    exit;
}

if (!flock($handle, LOCK_EX)) {
    fclose($handle);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save your enquiry.']);
    exit;
}

if ($is_new) {
    fputcsv($handle, ['received', 'name', 'email', 'interest', 'message']);
}
fputcsv($handle, [$received, $name, $email, $interest, $message]);
fflush($handle);
flock($handle, LOCK_UN);
fclose($handle);

$email_body = "New enquiry for {$config['club_name']}\n\nName: {$name}\nEmail: {$email}\nInterest: {$interest}\nMessage: {$message}\nReceived: {$received}";
$email_headers = 'From: website@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\nReply-To: {$email}\r\n";
$email_sent = @mail($config['club_email'], 'New club enquiry — ' . $name, $email_body, $email_headers);

$whatsapp_text = "Hello {$config['club_name']}, I would like to learn more about the club.\n\nName: {$name}\nEmail: {$email}\nInterest: {$interest}\nMessage: {$message}";
$whatsapp_url = 'https://wa.me/' . $config['whatsapp'] . '?text=' . rawurlencode($whatsapp_text);

echo json_encode([
    'success' => true,
    'email_sent' => $email_sent,
    'whatsapp_url' => $whatsapp_url,
    'message' => 'Your enquiry was saved. WhatsApp will open next.',
]);
