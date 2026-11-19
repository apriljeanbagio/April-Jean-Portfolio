
<?php

session_start();

require_once "includes/db.php";

// Accept POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.php");
    exit;
}

// Verify CSRF token
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    die("Invalid form submission. Please return to the contact page and try again.");
}

// Get and clean form inputs
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

// Validate required fields
if ($name === "" || $email === "" || $subject === "" || $message === "") {
    die("Please complete all fields. <a href='contact.php'>Go back</a>");
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address. <a href='contact.php'>Go back</a>");
}

// Validate field lengths
if (
    strlen($name) > 100 ||
    strlen($email) > 150 ||
    strlen($subject) > 150 ||
    strlen($message) > 5000
) {
    die("One or more fields are too long. <a href='contact.php'>Go back</a>");
}

// Save message to database
try {

    $sql = "INSERT INTO messages
            (sender_name, sender_email, subject, message, is_read, created_at)
            VALUES
            (:name, :email, :subject, :message, 0, NOW())";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":subject" => $subject,
        ":message" => $message
    ]);

    // Prevent reuse of the same token
    unset($_SESSION["csrf_token"]);

    header("Location: contact.php?sent=success");
    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    die("Sorry, your message could not be sent. Please try again later.");

}

?>
