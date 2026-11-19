
<?php

// Secure session configuration
ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "domain" => "",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

// Check admin login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../includes/db.php";

// Generate CSRF token
if (empty($_SESSION["education_token"])) {
    $_SESSION["education_token"] = bin2hex(random_bytes(32));
}

$message = "";
$editEducation = null;

// Add or update education
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["education_token"], $token)
    ) {
        $message = "Invalid request. Please refresh the page.";

    } elseif (isset($_POST["save_education"])) {

        $schoolName = trim($_POST["school_name"] ?? "");
        $degree = trim($_POST["degree"] ?? "");
        $yearStarted = trim($_POST["year_started"] ?? "");
        $yearEnded = trim($_POST["year_ended"] ?? "");
        $description = trim($_POST["description"] ?? "");

        $id = filter_var(
            $_POST["education_id"] ?? "0",
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 0]]
        );

        if (
            !is_string($schoolName) ||
            !is_string($degree) ||
            !is_string($yearStarted) ||
            !is_string($yearEnded) ||
            !is_string($description)
        ) {
            $message = "Invalid input.";

        } elseif ($schoolName === "") {
            $message = "Please enter the school name.";

        } elseif (strlen($schoolName) > 255) {
            $message = "School name must not exceed 255 characters.";

        } elseif (strlen($degree) > 255) {
            $message = "Degree or level must not exceed 255 characters.";

        } elseif (strlen($description) > 5000) {
            $message = "Description must not exceed 5000 characters.";

        } elseif ($id === false) {
            $message = "Invalid education record ID.";

        } else {

            $start = null;
            $end = null;

            if (
                ($yearStarted !== "" && !preg_match('/^\d{4}$/', $yearStarted)) ||
                ($yearEnded !== "" && !preg_match('/^\d{4}$/', $yearEnded))
            ) {
                $message = "Please enter years using four digits.";

            } else {

                $start = $yearStarted === "" ? null : (int)$yearStarted;
                $end = $yearEnded === "" ? null : (int)$yearEnded;

                if (
                    ($start !== null && ($start < 1900 || $start > 2100)) ||
                    ($end !== null && ($end < 1900 || $end > 2100)) ||
                    ($start !== null && $end !== null && $end < $start)
                ) {
                    $message = "Please enter valid education years.";

                } elseif ($id > 0) {

                    $stmt = $pdo->prepare(
                        "UPDATE education
                         SET school_name = ?,
                             degree = ?,
                             year_started = ?,
                             year_ended = ?,
                             description = ?
                         WHERE id = ?"
                    );

                    $stmt->execute([
                        $schoolName,
                        $degree,
                        $start,
                        $end,
                        $description,
                        $id
                    ]);

                    $message = $stmt->rowCount() > 0
                        ? "Education updated successfully."
                        : "No changes were made, or the record was not found.";

                } else {

                    $stmt = $pdo->prepare(
                        "INSERT INTO education
                         (school_name, degree, year_started, year_ended, description)
                         VALUES (?, ?, ?, ?, ?)"
                    );

                    $stmt->execute([
                        $schoolName,
                        $degree,
                        $start,
                        $end,
                        $description
                    ]);

                    $message = "Education added successfully.";
                }
            }
        }

    } elseif (isset($_POST["delete_education"])) {

        $id = filter_var(
            $_POST["education_id"] ?? "",
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if (!$id) {
            $message = "Invalid education record ID.";

        } else {

            $stmt = $pdo->prepare(
                "DELETE FROM education WHERE id = ?"
            );

            $stmt->execute([$id]);

            $message = $stmt->rowCount() > 0
                ? "Education deleted successfully."
                : "Education record not found.";
        }

    } else {
        $message = "Invalid request.";
    }
}

// Load selected education for editing
if (isset($_GET["edit"])) {

    $id = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($id) {

        $stmt = $pdo->prepare(
            "SELECT * FROM education WHERE id = ?"
        );

        $stmt->execute([$id]);
        $editEducation = $stmt->fetch();
    }
}

// Get all education records
$stmt = $pdo->query(
    "SELECT * FROM education ORDER BY year_started DESC, id DESC"
);

$educationList = $stmt->fetchAll();

// Escape output
function escape($value) {
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Education</title>

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

        .education-item {
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

    <h1>Manage Education</h1>

    <p>Add and manage your educational background.</p>

    <a class="back-link" href="dashboard.php">
        Back to Dashboard
    </a>

    <div class="panel">

        <h2>
            <?php echo $editEducation ? "Edit Education" : "Add Education"; ?>
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
                value="<?php echo escape($_SESSION["education_token"]); ?>"
            >

            <input
                type="hidden"
                name="education_id"
                value="<?php echo escape($editEducation["id"] ?? ""); ?>"
            >

            <label>School Name</label>

            <input
                type="text"
                name="school_name"
                maxlength="255"
                required
                value="<?php echo escape($editEducation["school_name"] ?? ""); ?>"
            >

            <label>Degree or Level</label>

            <input
                type="text"
                name="degree"
                maxlength="255"
                placeholder="Example: Bachelor of Science in Information Technology"
                value="<?php echo escape($editEducation["degree"] ?? ""); ?>"
            >

            <label>Year Started</label>

            <input
                type="number"
                name="year_started"
                min="1900"
                max="2100"
                value="<?php echo escape($editEducation["year_started"] ?? ""); ?>"
            >

            <label>Year Ended</label>

            <input
                type="number"
                name="year_ended"
                min="1900"
                max="2100"
                value="<?php echo escape($editEducation["year_ended"] ?? ""); ?>"
            >

            <label>Description</label>

            <textarea
                name="description"
                maxlength="5000"
            ><?php echo escape($editEducation["description"] ?? ""); ?></textarea>

            <button type="submit" name="save_education">

                <?php echo $editEducation ? "Update Education" : "Save Education"; ?>

            </button>

            <?php if ($editEducation): ?>

                <a class="cancel-link" href="education.php">
                    Cancel Edit
                </a>

            <?php endif; ?>

        </form>

    </div>

    <div class="panel">

        <h2>Education Records</h2>

        <?php if (count($educationList) > 0): ?>

            <?php foreach ($educationList as $education): ?>

                <div class="education-item">

                    <h3>
                        <?php echo escape($education["school_name"]); ?>
                    </h3>

                    <p>
                        <strong>Degree/Level:</strong>
                        <?php echo escape($education["degree"]); ?>
                    </p>

                    <p>
                        <strong>Years:</strong>
                        <?php echo escape($education["year_started"]); ?>
                        -
                        <?php echo escape($education["year_ended"]); ?>
                    </p>

                    <p>
                        <?php echo nl2br(escape($education["description"])); ?>
                    </p>

                    <div class="actions">

                        <a href="education.php?edit=<?php echo (int)$education["id"]; ?>">
                            Edit
                        </a>

                        <form
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this education record?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo escape($_SESSION["education_token"]); ?>"
                            >

                            <input
                                type="hidden"
                                name="education_id"
                                value="<?php echo (int)$education["id"]; ?>"
                            >

                            <button
                                class="delete-button"
                                type="submit"
                                name="delete_education"
                            >
                                Delete
                            </button>

                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No education records added yet.</p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
