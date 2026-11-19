
<?php

ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

// Protect page from unauthorized visitors
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../includes/db.php";

$message = "";

// Create a CSRF token
if (empty($_SESSION["profile_token"])) {
    $_SESSION["profile_token"] = bin2hex(random_bytes(32));
}

// Load existing profile
$stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
$profile = $stmt->fetch();

// Create an empty profile if none exists
if (!$profile) {

    $stmt = $pdo->prepare(
        "INSERT INTO profile
        (full_name, display_name, title, introduction, about_me, email, phone, address)
        VALUES ('', '', '', '', '', '', '', '')"
    );

    $stmt->execute();

    $stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
    $profile = $stmt->fetch();
}

// Save profile information
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["profile_token"], $token)
    ) {

        $message = "Invalid request. Please refresh the page.";

    } else {

        $full_name = trim($_POST["full_name"] ?? "");
        $display_name = trim($_POST["display_name"] ?? "");
        $title = trim($_POST["title"] ?? "");
        $introduction = trim($_POST["introduction"] ?? "");
        $about_me = trim($_POST["about_me"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $address = trim($_POST["address"] ?? "");

        if ($full_name === "" || $display_name === "") {

            $message = "Full name and display name are required.";

        } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $message = "Please enter a valid email address.";

        } else {

            $stmt = $pdo->prepare(
                "UPDATE profile SET
                    full_name = :full_name,
                    display_name = :display_name,
                    title = :title,
                    introduction = :introduction,
                    about_me = :about_me,
                    email = :email,
                    phone = :phone,
                    address = :address
                 WHERE id = :id"
            );

            $stmt->execute([
                ":full_name" => $full_name,
                ":display_name" => $display_name,
                ":title" => $title,
                ":introduction" => $introduction,
                ":about_me" => $about_me,
                ":email" => $email,
                ":phone" => $phone,
                ":address" => $address,
                ":id" => $profile["id"]
            ]);

            $message = "Profile updated successfully!";

            // Reload updated profile
            $stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
            $profile = $stmt->fetch();

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Profile | April Jean Portfolio</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f9ff;
            padding: 25px;
            color: #17324d;
        }

        .container {
            max-width: 750px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1 {
            color: #0b4f8a;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            min-height: 90px;
        }

        button {
            background: #0b4f8a;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }

        a {
            color: #0b4f8a;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Manage Personal Information</h1>

    <p>
        <a href="dashboard.php">Back to Dashboard</a>
    </p>

    <p>
        <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
    </p>

    <form method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION["profile_token"], ENT_QUOTES, "UTF-8"); ?>"
        >

        <label>Full Name:</label>

        <input
            type="text"
            name="full_name"
            maxlength="150"
            required
            value="<?php echo htmlspecialchars($profile["full_name"], ENT_QUOTES, "UTF-8"); ?>"
        >

        <label>Display Name:</label>

        <input
            type="text"
            name="display_name"
            maxlength="100"
            required
            value="<?php echo htmlspecialchars($profile["display_name"], ENT_QUOTES, "UTF-8"); ?>"
        >

        <label>Course or Professional Title:</label>

        <input
            type="text"
            name="title"
            maxlength="150"
            value="<?php echo htmlspecialchars($profile["title"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
        >

        <label>Introduction:</label>

        <textarea name="introduction"><?php echo htmlspecialchars($profile["introduction"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>

        <label>About Me:</label>

        <textarea name="about_me"><?php echo htmlspecialchars($profile["about_me"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>

        <label>Email:</label>

        <input
            type="email"
            name="email"
            maxlength="150"
            value="<?php echo htmlspecialchars($profile["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
        >

        <label>Phone Number:</label>

        <input
            type="text"
            name="phone"
            maxlength="30"
            value="<?php echo htmlspecialchars($profile["phone"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
        >

        <label>Address:</label>

        <input
            type="text"
            name="address"
            maxlength="255"
            value="<?php echo htmlspecialchars($profile["address"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
        >

        <button type="submit">Save Profile</button>

    </form>

</div>

</body>

</html>
