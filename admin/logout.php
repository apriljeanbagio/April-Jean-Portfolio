
<?php

ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

// Check admin login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Verify CSRF token
$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    empty($_SESSION["admin_csrf_token"]) ||
    !hash_equals($_SESSION["admin_csrf_token"], $token)
) {
    http_response_code(403);
    exit("Invalid logout request. Please return to the dashboard.");
}

// Remove all session data
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => $params["samesite"] ?? "Lax"
        ]
    );
}

// Destroy the session
session_destroy();

// Return to login page
header("Location: login.php");
exit;

?>
