
<?php

ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../includes/db.php";

// Create a separate CSRF token
if (empty($_SESSION["achievements_token"])) {
    $_SESSION["achievements_token"] = bin2hex(random_bytes(32));
}

$message = "";
$editAchievement = null;

// Escape output
function escape($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

// Validate date format
function isValidDate($date) {
    if ($date === "") {
        return true;
    }

    $parsedDate = DateTime::createFromFormat("!Y-m-d", $date);

    return $parsedDate !== false &&
           $parsedDate->format("Y-m-d") === $date;
}

// Check character length
function characterLength($value) {
    return function_exists("mb_strlen")
        ? mb_strlen($value, "UTF-8")
        : strlen($value);
}

// Add or update achievement
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_achievement"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["achievements_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $title = trim($_POST["title"] ?? "");
    $organization = trim($_POST["organization"] ?? "");
    $achievementDate = trim($_POST["achievement_date"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $id = filter_var($_POST["achievement_id"] ?? 0, FILTER_VALIDATE_INT);

    if ($id === false || $id < 0) {
        $message = "Invalid achievement record.";
    } elseif ($title === "") {
        $message = "Please enter the achievement title.";
    } elseif (
        characterLength($title) > 255 ||
        characterLength($organization) > 255 ||
        characterLength($description) > 5000
    ) {
        $message = "Please check the length of your information.";
    } elseif (!isValidDate($achievementDate)) {
        $message = "Please enter a valid achievement date.";
    } else {

        $date = $achievementDate === "" ? null : $achievementDate;

        if ($id > 0) {

            // Check whether the record exists
            $checkStmt = $pdo->prepare(
                "SELECT id FROM achievements WHERE id = ?"
            );

            $checkStmt->execute([$id]);

            if (!$checkStmt->fetch()) {
                $message = "Achievement record not found.";
            } else {

                $stmt = $pdo->prepare(
                    "UPDATE achievements
                     SET title = ?, organization = ?, achievement_date = ?,
                         description = ?
                     WHERE id = ?"
                );

                $stmt->execute([
                    $title,
                    $organization,
                    $date,
                    $description,
                    $id
                ]);

                $message = "Achievement updated successfully.";
            }

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO achievements
                 (title, organization, achievement_date, description)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $title,
                $organization,
                $date,
                $description
            ]);

            $message = "Achievement added successfully.";
        }
    }
}

// Delete achievement
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_achievement"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["achievements_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $id = filter_var($_POST["achievement_id"] ?? 0, FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $message = "Invalid achievement record.";
    } else {

        $stmt = $pdo->prepare(
            "DELETE FROM achievements WHERE id = ?"
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $message = "Achievement deleted successfully.";
        } else {
            $message = "Achievement record not found.";
        }
    }
}

// Load achievement for editing
if (isset($_GET["edit"])) {

    $id = filter_var($_GET["edit"], FILTER_VALIDATE_INT);

    if ($id !== false && $id > 0) {

        $stmt = $pdo->prepare(
            "SELECT * FROM achievements WHERE id = ?"
        );

        $stmt->execute([$id]);
        $editAchievement = $stmt->fetch();
    }
}

// Get all achievements
$stmt = $pdo->query(
    "SELECT * FROM achievements ORDER BY achievement_date DESC, id DESC"
);

$achievementList = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Achievements</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f5f9ff;
            color: #18324a;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            color: #0b4f8a;
        }

        .panel {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #ccd8e5;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button, .back-link {
            display: inline-block;
            margin-top: 18px;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            background: #0b6db7;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .delete-button {
            background: #c0392b;
            margin-top: 0;
        }

        .cancel-link {
            margin-left: 10px;
            color: #0b4f8a;
        }

        .message {
            padding: 12px;
            background: #e6f4ea;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .achievement-item {
            border: 1px solid #dce6f0;
            padding: 18px;
            margin-top: 15px;
            border-radius: 7px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .actions a {
            color: #0b4f8a;
        }

        .actions form {
            margin: 0;
        }

        @media (max-width: 600px) {

            body {
                padding: 15px;
            }

            .panel {
                padding: 18px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h1>Manage Achievements</h1>

    <p>Add and manage your certificates, awards, and recognitions.</p>

    <a class="back-link" href="dashboard.php">Back to Dashboard</a>

    <div class="panel">

        <h2>
            <?php echo $editAchievement ? "Edit Achievement" : "Add Achievement"; ?>
        </h2>

        <?php if ($message !== ""): ?>

            <p class="message">
                <?php echo escape($message); ?>
            </p>

        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo escape($_SESSION["achievements_token"]); ?>"
            >

            <input
                type="hidden"
                name="achievement_id"
                value="<?php echo escape($editAchievement["id"] ?? ""); ?>"
            >

            <label>Achievement or Certificate Title</label>

            <input
                type="text"
                name="title"
                maxlength="255"
                required
                placeholder="Example: Certificate of Participation"
                value="<?php echo escape($editAchievement["title"] ?? ""); ?>"
            >

            <label>Organization or Provider</label>

            <input
                type="text"
                name="organization"
                maxlength="255"
                placeholder="Example: NORSU"
                value="<?php echo escape($editAchievement["organization"] ?? ""); ?>"
            >

            <label>Date Awarded</label>

            <input
                type="date"
                name="achievement_date"
                value="<?php echo escape($editAchievement["achievement_date"] ?? ""); ?>"
            >

            <label>Description</label>

            <textarea
                name="description"
                maxlength="5000"
            ><?php echo escape($editAchievement["description"] ?? ""); ?></textarea>

            <button type="submit" name="save_achievement">

                <?php echo $editAchievement ? "Update Achievement" : "Save Achievement"; ?>

            </button>

            <?php if ($editAchievement): ?>

                <a class="cancel-link" href="achievements.php">
                    Cancel Edit
                </a>

            <?php endif; ?>

        </form>

    </div>

    <div class="panel">

        <h2>Achievement Records</h2>

        <?php if (count($achievementList) > 0): ?>

            <?php foreach ($achievementList as $achievement): ?>

                <div class="achievement-item">

                    <h3>
                        <?php echo escape($achievement["title"]); ?>
                    </h3>

                    <p>
                        <strong>Organization:</strong>
                        <?php echo escape($achievement["organization"]); ?>
                    </p>

                    <p>
                        <strong>Date Awarded:</strong>
                        <?php echo escape($achievement["achievement_date"]); ?>
                    </p>

                    <p>
                        <?php echo nl2br(escape($achievement["description"])); ?>
                    </p>

                    <div class="actions">

                        <a href="achievements.php?edit=<?php echo (int)$achievement["id"]; ?>">
                            Edit
                        </a>

                        <form
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this achievement?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo escape($_SESSION["achievements_token"]); ?>"
                            >

                            <input
                                type="hidden"
                                name="achievement_id"
                                value="<?php echo (int)$achievement["id"]; ?>"
                            >

                            <button
                                class="delete-button"
                                type="submit"
                                name="delete_achievement"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No achievements added yet.</p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
