<?php
// Sessions, security helpers and shared settings.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const CURRENT_SESSION = '2026/2027';   // academic session used for course registration

// Escape output so user input can never run as HTML or JavaScript (prevents XSS)
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// CSRF protection for forms
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(400);
        die('Invalid request. Please go back and try again.');
    }
}

// Page guards: only logged-in users can open protected pages
function require_student(): void {
    if (empty($_SESSION['student_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function flash(?string $message = null, string $type = 'success'): ?array {
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
