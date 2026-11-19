
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
if (empty($_SESSION["settings_token"])) {
    $_SESSION["settings_token"] = bin2hex(random_bytes(32));
}

$message = "";

// Escape output safely
function e($value) {
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

// Check character length
function characterLength($value) {
    return function_exists("mb_strlen")
        ? mb_strlen($value, "UTF-8")
        : strlen($value);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["settings_token"], $token)
    ) {

        die("Invalid request. Please refresh the page.");

    } else {

        $action = $_POST["action"] ?? "";

        // Add setting
        if ($action === "add") {

            $setting_name = trim($_POST["setting_name"] ?? "");
            $setting_value = trim($_POST["setting_value"] ?? "");

            if ($setting_name === "") {

                $message = "Please enter a setting name.";

            } elseif (
                characterLength($setting_name) > 100 ||
                characterLength($setting_value) > 5000
            ) {

                $message = "Please check the length of your setting information.";

            } else {

                try {

                    $stmt = $pdo->prepare(
                        "INSERT INTO settings
                         (setting_name, setting_value)
                         VALUES (:setting_name, :setting_value)"
                    );

                    $stmt->execute([
                        ":setting_name" => $setting_name,
                        ":setting_value" => $setting_value
                    ]);

                    $message = "Setting added successfully!";

                } catch (PDOException $e) {

                    if ($e->getCode() === "23000") {
                        $message = "This setting name already exists.";
                    } else {
                        error_log($e->getMessage());
                        $message = "Unable to add the setting.";
                    }

                }
            }

        // Update setting
        } elseif ($action === "update") {

            $id = filter_var(
                $_POST["id"] ?? "",
                FILTER_VALIDATE_INT
            );

            $setting_name = trim($_POST["setting_name"] ?? "");
            $setting_value = trim($_POST["setting_value"] ?? "");

            if ($id === false || $id <= 0) {

                $message = "Invalid setting selected.";

            } elseif ($setting_name === "") {

                $message = "Please enter a setting name.";

            } elseif (
                characterLength($setting_name) > 100 ||
                characterLength($setting_value) > 5000
            ) {

                $message = "Please check the length of your setting information.";

            } else {

                // Check whether the setting exists
                $checkStmt = $pdo->prepare(
                    "SELECT id FROM settings WHERE id = :id"
                );

                $checkStmt->execute([
                    ":id" => $id
                ]);

                if (!$checkStmt->fetch()) {

                    $message = "Setting not found.";

                } else {

                    try {

                        $stmt = $pdo->prepare(
                            "UPDATE settings
                             SET setting_name = :setting_name,
                                 setting_value = :setting_value
                             WHERE id = :id"
                        );

                        $stmt->execute([
                            ":setting_name" => $setting_name,
                            ":setting_value" => $setting_value,
                            ":id" => $id
                        ]);

                        $message = "Setting updated successfully!";

                    } catch (PDOException $e) {

                        if ($e->getCode() === "23000") {
                            $message = "This setting name already exists.";
                        } else {
                            error_log($e->getMessage());
                            $message = "Unable to update the setting.";
                        }

                    }
                }
            }

        // Delete setting
        } elseif ($action === "delete") {

            $id = filter_var(
                $_POST["id"] ?? "",
                FILTER_VALIDATE_INT
            );

            if ($id === false || $id <= 0) {

                $message = "Invalid setting selected.";

            } else {

                $stmt = $pdo->prepare(
                    "DELETE FROM settings WHERE id = :id"
                );

                $stmt->execute([
                    ":id" => $id
                ]);

                if ($stmt->rowCount() > 0) {
                    $message = "Setting deleted successfully!";
                } else {
                    $message = "Setting not found.";
                }

            }

        } else {

            $message = "Invalid action.";

        }
    }
}

// Get all settings
$stmt = $pdo->query(
    "SELECT * FROM settings ORDER BY id DESC"
);

$settings = $stmt->fetchAll();

// Get setting for editing
$editSetting = null;

if (isset($_GET["edit"])) {

    $editId = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT
    );

    if ($editId !== false && $editId > 0) {

        $stmt = $pdo->prepare(
            "SELECT * FROM settings WHERE id = :id"
        );

        $stmt->execute([
            ":id" => $editId
        ]);

        $editSetting = $stmt->fetch();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Settings | April Jean Portfolio</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f9ff;
            color: #17324d;
            padding: 25px;
        }

        .container {
            max-width: 850px;
            margin: auto;
        }

        h1 {
            color: #0b4f8a;
        }

        .box {
            background: white;
            padding: 22px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }

        textarea {
            resize: vertical;
        }

        button {
            background: #0b4f8a;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #0b6db7;
        }

        .delete {
            background: #c0392b;
        }

        .delete:hover {
            background: #a93226;
        }

        .setting {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        a {
            color: #0b4f8a;
            text-decoration: none;
            margin-right: 12px;
        }

        a:hover {
            text-decoration: underline;
        }

        .message {
            color: #0b6b35;
            font-weight: bold;
            margin: 15px 0;
        }

        .setting-value {
            overflow-wrap: anywhere;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Manage Website Settings</h1>

    <p>
        <a href="dashboard.php">Back to Dashboard</a>
    </p>

    <?php if ($message !== ""): ?>

        <p class="message">
            <?php echo e($message); ?>
        </p>

    <?php endif; ?>

    <!-- Add or Edit Setting -->

    <div class="box">

        <h2>
            <?php echo $editSetting ? "Edit Setting" : "Add New Setting"; ?>
        </h2>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo e($_SESSION["settings_token"]); ?>"
            >

            <input
                type="hidden"
                name="action"
                value="<?php echo $editSetting ? "update" : "add"; ?>"
            >

            <?php if ($editSetting): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo (int)$editSetting["id"]; ?>"
                >

            <?php endif; ?>

            <label for="setting_name">
                Setting Name:
            </label>

            <input
                type="text"
                id="setting_name"
                name="setting_name"
                maxlength="100"
                placeholder="Example: website_title"
                required
                value="<?php echo e($editSetting["setting_name"] ?? ""); ?>"
            >

            <label for="setting_value">
                Setting Value:
            </label>

            <textarea
                id="setting_value"
                name="setting_value"
                rows="4"
                maxlength="5000"
                placeholder="Enter the setting value"
            ><?php echo e($editSetting["setting_value"] ?? ""); ?></textarea>

            <button type="submit">
                <?php echo $editSetting ? "Update Setting" : "Add Setting"; ?>
            </button>

            <?php if ($editSetting): ?>

                <a href="settings.php">
                    Cancel Edit
                </a>

            <?php endif; ?>

        </form>

    </div>

    <!-- Settings List -->

    <div class="box">

        <h2>Website Settings List</h2>

        <?php if (count($settings) > 0): ?>

            <?php foreach ($settings as $setting): ?>

                <div class="setting">

                    <h3>
                        <?php echo e($setting["setting_name"]); ?>
                    </h3>

                    <p class="setting-value">
                        <?php echo nl2br(e($setting["setting_value"])); ?>
                    </p>

                    <a href="settings.php?edit=<?php echo (int)$setting["id"]; ?>">
                        Edit
                    </a>

                    <form method="POST" style="display:inline;">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo e($_SESSION["settings_token"]); ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="delete"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo (int)$setting["id"]; ?>"
                        >

                        <button
                            type="submit"
                            class="delete"
                            onclick="return confirm('Are you sure you want to delete this setting?');"
                        >
                            Delete
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No website settings added yet.</p>

        <?php endif; ?>

    </div>

</div>

</body>

</html>
