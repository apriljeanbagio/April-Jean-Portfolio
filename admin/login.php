
<?php

ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

require_once "../includes/db.php";

// Redirect logged-in administrators
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

// Generate login CSRF token
if (empty($_SESSION["login_token"])) {
    $_SESSION["login_token"] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    // Verify CSRF token
    if (
        !is_string($token) ||
        !hash_equals($_SESSION["login_token"], $token)
    ) {

        $message = "Invalid request. Please refresh the page.";

    } else {

        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($username === "" || $password === "") {

            $message = "Please enter your username and password.";

        } else {

            $stmt = $pdo->prepare(
                "SELECT * FROM admin_users WHERE username = :username LIMIT 1"
            );

            $stmt->execute([":username" => $username]);

            $admin = $stmt->fetch();

            if ($admin) {

                if (
                    $admin["lock_until"] !== null &&
                    strtotime($admin["lock_until"]) > time()
                ) {

                    $message = "Too many attempts. Please try again later.";

                } elseif (password_verify($password, $admin["password"])) {

                    // Reset failed login attempts
                    $stmt = $pdo->prepare(
                        "UPDATE admin_users
                         SET failed_attempts = 0, lock_until = NULL
                         WHERE id = :id"
                    );

                    $stmt->execute([":id" => $admin["id"]]);

                    // Regenerate session ID
                    session_regenerate_id(true);

                    // Set administrator session
                    $_SESSION["admin_id"] = $admin["id"];
                    $_SESSION["admin_username"] = $admin["username"];

                    // Remove login token after successful login
                    unset($_SESSION["login_token"]);

                    // Generate a separate token for authenticated session
                    $_SESSION["admin_csrf_token"] = bin2hex(random_bytes(32));

                    header("Location: dashboard.php");
                    exit;

                } else {

                    $attempts = (int)$admin["failed_attempts"] + 1;

                    if ($attempts >= 5) {

                        $stmt = $pdo->prepare(
                            "UPDATE admin_users
                             SET failed_attempts = 0,
                                 lock_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                             WHERE id = :id"
                        );

                        $stmt->execute([":id" => $admin["id"]]);

                        $message = "Too many attempts. Please try again in 15 minutes.";

                    } else {

                        $stmt = $pdo->prepare(
                            "UPDATE admin_users
                             SET failed_attempts = :attempts
                             WHERE id = :id"
                        );

                        $stmt->execute([
                            ":attempts" => $attempts,
                            ":id" => $admin["id"]
                        ]);

                        $message = "Incorrect username or password.";
                    }
                }

            } else {

                $message = "Incorrect username or password.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | April Jean Portfolio</title>

</head>

<body>

    <h2>Admin Login</h2>

    <p>Enter your administrator account details.</p>

    <?php if ($message !== ""): ?>

        <p>
            <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Username:</label><br>

        <input
            type="text"
            name="username"
            required
            maxlength="100"
        >

        <br><br>

        <label>Password:</label><br>

        <input
            type="password"
            name="password"
            required
        >

        <br><br>

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION["login_token"], ENT_QUOTES, "UTF-8"); ?>"
        >

        <button type="submit">Log In</button>

    </form>

</body>

</html>
