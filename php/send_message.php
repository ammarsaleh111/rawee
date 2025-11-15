<?php
// /rawee/php/send_message.php
// Server-side relay to EmailJS for three different forms (product, home, contact).

// --- CONFIG: replace these with your EmailJS values ---
define('EMAILJS_SERVICE_ID', 'service_xic50xi');
define('EMAILJS_TEMPLATE_ID_PRODUCT', 'template_87dq4ew');
define('EMAILJS_TEMPLATE_ID_HOME', 'template_87dq4ew');
define('EMAILJS_TEMPLATE_ID_CONTACT', 'template_87dq4ew');
define('EMAILJS_USER_ID', 'kH2tZ4WjM4Czctrey'); // keep secret server-side
// log file (make sure PHP can write here; change path if needed)
define('LOG_FILE', __DIR__ . '/send_message.log');
// ------------------------------------------------------

/**
 * Simple logger
 */
function log_msg($text) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $text . PHP_EOL;
    @file_put_contents(LOG_FILE, $line, FILE_APPEND);
}

/**
 * Safe helper to grab a POST param
 */
function p($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * Append query params to redirect while preserving fragments
 */
function build_redirect_url($target, array $extraParams = []) {
    if ($target === '') {
        $target = '/';
    }
    $fragment = '';
    $hashPos = strpos($target, '#');
    if ($hashPos !== false) {
        $fragment = substr($target, $hashPos + 1);
        $target = substr($target, 0, $hashPos);
    }
    $separator = (strpos($target, '?') === false) ? '?' : '&';
    $query = http_build_query($extraParams);
    $target .= $separator . $query;
    if ($fragment !== '') {
        $target .= '#' . $fragment;
    }
    return $target;
}

// Allow GET requests to confirm endpoint availability (helps debug 404 confusions)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ready',
        'message' => 'send_message.php reachable. Submit via POST to send.'
    ]);
    exit;
}

/**
 * Build template params depending on form_source.
 * Adjust variable names to match what you put in EmailJS templates.
 */
$form_source = p('form_source', 'unknown');
$redirect = p('redirect', '/');

// normalize redirect to absolute path if needed
if (!preg_match('#^(https?://|/)#i', $redirect)) {
    $appFolder = basename(dirname(__DIR__)); // 'rawee'
    $redirect = '/' . $appFolder . '/' . ltrim($redirect, '/');
}

// Basic common fields
$name    = p('name');
$email   = p('email');
$phone   = p('phone');
$message = p('message');
$subject = p('subject') ?: ''; // some forms included their own subject

// Template payload base
$template_params = [
    'form_source' => $form_source,
    'name'        => $name,
    'email'       => $email,
    'phone'       => $phone,
    'message'     => $message,
    'subject'     => $subject,
    // you can add more fields as needed
];

// Additional product-specific fields (if present)
if ($form_source === 'product') {
    $template_params['farm_size']     = p('farm_size');
    $template_params['solution_type'] = p('solution_type');
    // include any other product-specific fields:
    // e.g. product id, product name, etc.
}

// Add any other POST keys so you don't lose data (optional)
foreach ($_POST as $k => $v) {
    if (!isset($template_params[$k]) && strpos($k, '_') !== 0) {
        $template_params[$k] = is_array($v) ? json_encode($v) : $v;
    }
}

// Choose template id by form source
$templates_map = [
    'product' => EMAILJS_TEMPLATE_ID_PRODUCT,
    'home'    => EMAILJS_TEMPLATE_ID_HOME,
    'contact' => EMAILJS_TEMPLATE_ID_CONTACT,
];

// fallback
$template_id = isset($templates_map[$form_source]) ? $templates_map[$form_source] : EMAILJS_TEMPLATE_ID_CONTACT;

// Build EmailJS payload
$payload = [
    'service_id'      => EMAILJS_SERVICE_ID,
    'template_id'     => $template_id,
    'user_id'         => EMAILJS_USER_ID,
    'template_params' => $template_params
];

// cURL request to EmailJS
$ch = curl_init('https://api.emailjs.com/api/v1.0/email/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// Handle result and redirect back
if ($response === false) {
    // cURL error
    $errorMsg = 'cURL error while sending to EmailJS: ' . $curlErr;
    log_msg($errorMsg);
    // redirect with error
    $redir = build_redirect_url($redirect, ['status' => 'error', 'msg' => 'Network error']);
    header('Location: ' . $redir);
    exit;
}

// Try decode EmailJS response (they return JSON in success & error cases)
$respData = @json_decode($response, true);

// success typically HTTP 200 or 202
if ($httpCode >= 200 && $httpCode < 300) {
    // optional: log success
    log_msg("EmailJS success for form '{$form_source}' (http {$httpCode}).");
    $redir = build_redirect_url($redirect, ['status' => 'success']);
    header('Location: ' . $redir);
    exit;
} else {
    // failure (TEMPORARY DEBUG)
$errDetail = is_array($respData) ? json_encode($respData) : $response;
log_msg("EmailJS returned HTTP {$httpCode} for form '{$form_source}'. Response: {$errDetail}");
header('Content-Type: text/plain');
http_response_code(500);
echo "EmailJS error (HTTP {$httpCode}):\n{$errDetail}";
exit;

}
