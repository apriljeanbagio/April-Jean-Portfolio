
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
if (empty($_SESSION["skills_token"])) {
    $_SESSION["skills_token"] = bin2hex(random_bytes(32));
}

$message = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["skills_token"], $token)
    ) {
        $message = "Invalid request. Please refresh the page.";

    } else {

        $action = $_POST["action"] ?? "";

        if ($action === "add" || $action === "update") {

            $skill_name = trim($_POST["skill_name"] ?? "");
            $description = trim($_POST["description"] ?? "");

            if (!is_string($skill_name) || !is_string($description)) {
                $message = "Invalid input.";

            } elseif ($skill_name === "") {
                $message = "Please enter a skill name.";

            } elseif (strlen($skill_name) > 100) {
                $message = "Skill name must not exceed 100 characters.";

            } elseif (strlen($description) > 5000) {
                $message = "Description must not exceed 5000 characters.";

            } elseif ($action === "add") {

                $stmt = $pdo->prepare(
                    "INSERT INTO skills (skill_name, description)
                     VALUES (:skill_name, :description)"
                );

                $stmt->execute([
                    ":skill_name" => $skill_name,
                    ":description" => $description
                ]);

                $message = "Skill added successfully!";

            } else {

                $id = filter_var(
                    $_POST["id"] ?? "",
                    FILTER_VALIDATE_INT,
                    ["options" => ["min_range" => 1]]
                );

                if (!$id) {
                    $message = "Invalid skill ID.";

                } else {

                    $stmt = $pdo->prepare(
                        "UPDATE skills
                         SET skill_name = :skill_name,
                             description = :description
                         WHERE id = :id"
                    );

                    $stmt->execute([
                        ":skill_name" => $skill_name,
                        ":description" => $description,
                        ":id" => $id
                    ]);

                    $message = $stmt->rowCount() > 0
                        ? "Skill updated successfully!"
                        : "No changes were made, or the skill was not found.";
                }
            }

        } elseif ($action === "delete") {

            $id = filter_var(
                $_POST["id"] ?? "",
                FILTER_VALIDATE_INT,
                ["options" => ["min_range" => 1]]
            );

            if (!$id) {
                $message = "Invalid skill ID.";

            } else {

                $stmt = $pdo->prepare(
                    "DELETE FROM skills WHERE id = :id"
                );

                $stmt->execute([":id" => $id]);

                $message = $stmt->rowCount() > 0
                    ? "Skill deleted successfully!"
                    : "Skill not found.";
            }

        } else {
            $message = "Invalid action.";
        }
    }
}

// Get all skills
$stmt = $pdo->query("SELECT * FROM skills ORDER BY id DESC");
$skills = $stmt->fetchAll();

// Load skill for editing
$editSkill = null;

if (isset($_GET["edit"])) {

    $editId = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($editId) {

        $stmt = $pdo->prepare(
            "SELECT * FROM skills WHERE id = :id"
        );

        $stmt->execute([":id" => $editId]);
        $editSkill = $stmt->fetch();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Skills | April Jean Portfolio</title>

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

        .box {
            background: white;
            padding: 22px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        h1 {
            color: #0b4f8a;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
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

        .delete {
            background: #c0392b;
        }

        .skill {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        a {
            color: #0b4f8a;
            text-decoration: none;
            margin-right: 12px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Manage My Skills</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <p><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p>

    <div class="box">

        <h2><?php echo $editSkill ? "Edit Skill" : "Add New Skill"; ?></h2>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION["skills_token"], ENT_QUOTES, "UTF-8"); ?>"
            >

            <input
                type="hidden"
                name="action"
                value="<?php echo $editSkill ? "update" : "add"; ?>"
            >

            <?php if ($editSkill): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo (int)$editSkill["id"]; ?>"
                >

            <?php endif; ?>

            <label>Skill Name:</label>

            <input
                type="text"
                name="skill_name"
                maxlength="100"
                required
                value="<?php echo htmlspecialchars($editSkill["skill_name"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
            >

            <label>Description:</label>

            <textarea
                name="description"
                maxlength="5000"
            ><?php echo htmlspecialchars($editSkill["description"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>

            <button type="submit">
                <?php echo $editSkill ? "Update Skill" : "Add Skill"; ?>
            </button>

            <?php if ($editSkill): ?>

                <a href="skills.php">Cancel Edit</a>

            <?php endif; ?>

        </form>

    </div>

    <div class="box">

        <h2>My Skills List</h2>

        <?php if (count($skills) > 0): ?>

            <?php foreach ($skills as $skill): ?>

                <div class="skill">

                    <h3>
                        <?php echo htmlspecialchars($skill["skill_name"], ENT_QUOTES, "UTF-8"); ?>
                    </h3>

                    <p>
                        <?php echo nl2br(htmlspecialchars($skill["description"] ?? "", ENT_QUOTES, "UTF-8")); ?>
                    </p>

                    <a href="skills.php?edit=<?php echo (int)$skill["id"]; ?>">
                        Edit
                    </a>

                    <form method="POST" style="display:inline;">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION["skills_token"], ENT_QUOTES, "UTF-8"); ?>"
                        >

                        <input type="hidden" name="action" value="delete">

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo (int)$skill["id"]; ?>"
                        >

                        <button
                            type="submit"
                            class="delete"
                            onclick="return confirm('Are you sure you want to delete this skill?');"
                        >
                            Delete
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No skills added yet.</p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
