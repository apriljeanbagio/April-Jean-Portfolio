
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

// Create a separate CSRF token for experience management
if (empty($_SESSION["experience_token"])) {
    $_SESSION["experience_token"] = bin2hex(random_bytes(32));
}

$message = "";
$editExperience = null;

// Escape output
function escape($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

// Check whether a date is a valid YYYY-MM-DD date
function isValidDate($date) {
    if ($date === "") {
        return true;
    }

    $parsedDate = DateTime::createFromFormat("!Y-m-d", $date);

    return $parsedDate !== false &&
           $parsedDate->format("Y-m-d") === $date;
}

// Get character length safely
function characterLength($value) {
    return function_exists("mb_strlen")
        ? mb_strlen($value, "UTF-8")
        : strlen($value);
}

// Add or update experience
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_experience"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["experience_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $position = trim($_POST["position"] ?? "");
    $organization = trim($_POST["organization"] ?? "");
    $startDate = trim($_POST["start_date"] ?? "");
    $endDate = trim($_POST["end_date"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $id = filter_var($_POST["experience_id"] ?? 0, FILTER_VALIDATE_INT);

    if ($id === false || $id < 0) {
        $message = "Invalid experience record.";
    } elseif ($position === "" || $organization === "") {
        $message = "Please enter the position and organization.";
    } elseif (
        characterLength($position) > 255 ||
        characterLength($organization) > 255 ||
        characterLength($description) > 5000
    ) {
        $message = "Please check the length of your information.";
    } elseif (
        !isValidDate($startDate) ||
        !isValidDate($endDate) ||
        ($startDate !== "" && $endDate !== "" && $endDate < $startDate)
    ) {
        $message = "Please enter valid dates. The end date must not be earlier than the start date.";
    } else {

        $start = $startDate === "" ? null : $startDate;
        $end = $endDate === "" ? null : $endDate;

        if ($id > 0) {

            // Check whether the record exists
            $checkStmt = $pdo->prepare(
                "SELECT id FROM experience WHERE id = ?"
            );
            $checkStmt->execute([$id]);

            if (!$checkStmt->fetch()) {
                $message = "Experience record not found.";
            } else {

                $stmt = $pdo->prepare(
                    "UPDATE experience
                     SET position = ?, organization = ?, start_date = ?,
                         end_date = ?, description = ?
                     WHERE id = ?"
                );

                $stmt->execute([
                    $position,
                    $organization,
                    $start,
                    $end,
                    $description,
                    $id
                ]);

                $message = "Experience updated successfully.";
            }

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO experience
                 (position, organization, start_date, end_date, description)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $position,
                $organization,
                $start,
                $end,
                $description
            ]);

            $message = "Experience added successfully.";
        }
    }
}

// Delete experience
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_experience"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["experience_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $id = filter_var($_POST["experience_id"] ?? 0, FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $message = "Invalid experience record.";
    } else {

        $stmt = $pdo->prepare(
            "DELETE FROM experience WHERE id = ?"
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $message = "Experience deleted successfully.";
        } else {
            $message = "Experience record not found.";
        }
    }
}

// Load selected experience for editing
if (isset($_GET["edit"])) {

    $id = filter_var($_GET["edit"], FILTER_VALIDATE_INT);

    if ($id !== false && $id > 0) {

        $stmt = $pdo->prepare(
            "SELECT * FROM experience WHERE id = ?"
        );

        $stmt->execute([$id]);
        $editExperience = $stmt->fetch();
    }
}

// Get all experience records
$stmt = $pdo->query(
    "SELECT * FROM experience ORDER BY start_date DESC, id DESC"
);

$experienceList = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Experience</title>

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

        .experience-item {
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

    <h1>Manage Experience</h1>

    <p>Add and manage your work or other relevant experience.</p>

    <a class="back-link" href="dashboard.php">Back to Dashboard</a>

    <div class="panel">

        <h2>
            <?php echo $editExperience ? "Edit Experience" : "Add Experience"; ?>
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
                value="<?php echo escape($_SESSION["experience_token"]); ?>"
            >

            <input
                type="hidden"
                name="experience_id"
                value="<?php echo escape($editExperience["id"] ?? ""); ?>"
            >

            <label>Position or Role</label>

            <input
                type="text"
                name="position"
                maxlength="255"
                required
                placeholder="Example: Student"
                value="<?php echo escape($editExperience["position"] ?? ""); ?>"
            >

            <label>Organization</label>

            <input
                type="text"
                name="organization"
                maxlength="255"
                required
                placeholder="Example: School or Organization Name"
                value="<?php echo escape($editExperience["organization"] ?? ""); ?>"
            >

            <label>Start Date</label>

            <input
                type="date"
                name="start_date"
                value="<?php echo escape($editExperience["start_date"] ?? ""); ?>"
            >

            <label>End Date</label>

            <input
                type="date"
                name="end_date"
                value="<?php echo escape($editExperience["end_date"] ?? ""); ?>"
            >

            <label>Description</label>

            <textarea
                name="description"
                maxlength="5000"
            ><?php echo escape($editExperience["description"] ?? ""); ?></textarea>

            <button type="submit" name="save_experience">

                <?php echo $editExperience ? "Update Experience" : "Save Experience"; ?>

            </button>

            <?php if ($editExperience): ?>

                <a class="cancel-link" href="experience.php">
                    Cancel Edit
                </a>

            <?php endif; ?>

        </form>

    </div>

    <div class="panel">

        <h2>Experience Records</h2>

        <?php if (count($experienceList) > 0): ?>

            <?php foreach ($experienceList as $experience): ?>

                <div class="experience-item">

                    <h3>
                        <?php echo escape($experience["position"]); ?>
                    </h3>

                    <p>
                        <strong>Organization:</strong>
                        <?php echo escape($experience["organization"]); ?>
                    </p>

                    <p>
                        <strong>Period:</strong>
                        <?php echo escape($experience["start_date"]); ?>
                        -
                        <?php echo escape($experience["end_date"]); ?>
                    </p>

                    <p>
                        <?php echo nl2br(escape($experience["description"])); ?>
                    </p>

                    <div class="actions">

                        <a href="experience.php?edit=<?php echo (int)$experience["id"]; ?>">
                            Edit
                        </a>

                        <form
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this experience record?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo escape($_SESSION["experience_token"]); ?>"
                            >

                            <input
                                type="hidden"
                                name="experience_id"
                                value="<?php echo (int)$experience["id"]; ?>"
                            >

                            <button
                                class="delete-button"
                                type="submit"
                                name="delete_experience"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No experience records added yet.</p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
