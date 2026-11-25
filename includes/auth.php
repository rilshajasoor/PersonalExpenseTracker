<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function is_logged_in(): bool { return isset($_SESSION['user_id']); }
function require_login(): void {
    if (!is_logged_in()) { header('Location: login.php');
                          exit;
                         }
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); 
                           }
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(400); 
            exit('Invalid request token. Please go back and try again.');
        }
    }
}
