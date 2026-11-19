
<?php

ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

// Protect dashboard from unauthorized visitors
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../includes/db.php";

$username = $_SESSION["admin_username"] ?? "Administrator";

// Ensure admin CSRF token exists
if (empty($_SESSION["admin_csrf_token"])) {
    $_SESSION["admin_csrf_token"] = bin2hex(random_bytes(32));
}

// Count portfolio records
$tables = [
    "skills",
    "projects",
    "education",
    "experience",
    "achievements",
    "messages"
];

$counts = [];

foreach ($tables as $table) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
    $counts[$table] = $stmt->fetchColumn();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | April Jean Portfolio</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f9ff;
            margin: 0;
            padding: 25px;
            color: #17324d;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        header {
            background: #0b4f8a;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h3 {
            margin-top: 0;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
            color: #0b6db7;
        }

        a {
            display: inline-block;
            color: #0b4f8a;
            margin: 8px 12px 8px 0;
            text-decoration: none;
            font-weight: bold;
        }

        .logout {
            color: white;
            background: #d9534f;
            padding: 10px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .logout:hover {
            background: #c9302c;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <header>

        <h1>April Jean Portfolio</h1>

        <h2>Admin Dashboard</h2>

        <p>
            Welcome,
            <?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?>!
        </p>

        <form method="POST" action="logout.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION["admin_csrf_token"], ENT_QUOTES, "UTF-8"); ?>"
            >

            <button type="submit" class="logout">
                Log Out
            </button>

        </form>

    </header>

    <h2>Portfolio Overview</h2>

    <div class="cards">

        <?php foreach ($counts as $name => $count): ?>

            <div class="card">

                <h3>
                    <?php echo htmlspecialchars(ucfirst($name), ENT_QUOTES, "UTF-8"); ?>
                </h3>

                <p><?php echo (int)$count; ?></p>

            </div>

        <?php endforeach; ?>

    </div>

    <h2>Manage Your Portfolio</h2>

    <nav>

        <a href="profile.php">Manage Profile</a>
        <a href="skills.php">Manage Skills</a>
        <a href="projects.php">Manage Projects</a>
        <a href="education.php">Manage Education</a>
        <a href="experience.php">Manage Experience</a>
        <a href="achievements.php">Manage Achievements</a>
        <a href="messages.php">View Messages</a>
        <a href="settings.php">Manage Settings</a>

    </nav>

</div>

</body>
</html>
