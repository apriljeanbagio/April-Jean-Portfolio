
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
if (empty($_SESSION["projects_token"])) {
    $_SESSION["projects_token"] = bin2hex(random_bytes(32));
}

$message = "";

// Process project actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["projects_token"], $token)
    ) {
        $message = "Invalid request. Please refresh the page.";

    } else {

        $action = $_POST["action"] ?? "";

        if ($action === "add" || $action === "update") {

            $title = trim($_POST["title"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $project_link = trim($_POST["project_link"] ?? "");

            if (
                !is_string($title) ||
                !is_string($description) ||
                !is_string($project_link)
            ) {
                $message = "Invalid input.";

            } elseif ($title === "") {
                $message = "Please enter a project title.";

            } elseif (strlen($title) > 150) {
                $message = "Project title must not exceed 150 characters.";

            } elseif (strlen($description) > 5000) {
                $message = "Description must not exceed 5000 characters.";

            } elseif (strlen($project_link) > 255) {
                $message = "Project link must not exceed 255 characters.";

            } elseif (
                $project_link !== "" &&
                (
                    !filter_var($project_link, FILTER_VALIDATE_URL) ||
                    !in_array(
                        strtolower(parse_url($project_link, PHP_URL_SCHEME) ?? ""),
                        ["http", "https"],
                        true
                    )
                )
            ) {
                $message = "Please enter a valid HTTP or HTTPS project URL.";

            } else {

                if ($action === "add") {

                    $stmt = $pdo->prepare(
                        "INSERT INTO projects
                        (title, description, project_link)
                        VALUES (:title, :description, :project_link)"
                    );

                    $stmt->execute([
                        ":title" => $title,
                        ":description" => $description,
                        ":project_link" => $project_link
                    ]);

                    $message = "Project added successfully!";

                } else {

                    $id = filter_var(
                        $_POST["id"] ?? "",
                        FILTER_VALIDATE_INT,
                        ["options" => ["min_range" => 1]]
                    );

                    if (!$id) {
                        $message = "Invalid project ID.";

                    } else {

                        $stmt = $pdo->prepare(
                            "UPDATE projects
                             SET title = :title,
                                 description = :description,
                                 project_link = :project_link
                             WHERE id = :id"
                        );

                        $stmt->execute([
                            ":title" => $title,
                            ":description" => $description,
                            ":project_link" => $project_link,
                            ":id" => $id
                        ]);

                        $message = $stmt->rowCount() > 0
                            ? "Project updated successfully!"
                            : "No changes were made, or the project was not found.";
                    }
                }
            }

        } elseif ($action === "delete") {

            $id = filter_var(
                $_POST["id"] ?? "",
                FILTER_VALIDATE_INT,
                ["options" => ["min_range" => 1]]
            );

            if (!$id) {
                $message = "Invalid project ID.";

            } else {

                $stmt = $pdo->prepare(
                    "DELETE FROM projects WHERE id = :id"
                );

                $stmt->execute([":id" => $id]);

                $message = $stmt->rowCount() > 0
                    ? "Project deleted successfully!"
                    : "Project not found.";
            }

        } else {
            $message = "Invalid action.";
        }
    }
}

// Get all projects
$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll();

// Get project for editing
$editProject = null;

if (isset($_GET["edit"])) {

    $editId = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($editId) {

        $stmt = $pdo->prepare(
            "SELECT * FROM projects WHERE id = :id"
        );

        $stmt->execute([":id" => $editId]);
        $editProject = $stmt->fetch();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Projects | April Jean Portfolio</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f9ff;
            color: #17324d;
            padding: 25px;
        }

        .container {
            max-width: 900px;
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
            margin-top: 6px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            min-height: 100px;
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

        .project {
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

    <h1>Manage My Projects</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <p><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p>

    <div class="box">

        <h2><?php echo $editProject ? "Edit Project" : "Add New Project"; ?></h2>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION["projects_token"], ENT_QUOTES, "UTF-8"); ?>"
            >

            <input
                type="hidden"
                name="action"
                value="<?php echo $editProject ? "update" : "add"; ?>"
            >

            <?php if ($editProject): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo (int)$editProject["id"]; ?>"
                >

            <?php endif; ?>

            <label>Project Title:</label>

            <input
                type="text"
                name="title"
                maxlength="150"
                required
                value="<?php echo htmlspecialchars($editProject["title"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
            >

            <label>Description:</label>

            <textarea
                name="description"
                maxlength="5000"
            ><?php echo htmlspecialchars($editProject["description"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>

            <label>Project Link (Optional):</label>

            <input
                type="url"
                name="project_link"
                maxlength="255"
                value="<?php echo htmlspecialchars($editProject["project_link"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
            >

            <button type="submit">
                <?php echo $editProject ? "Update Project" : "Add Project"; ?>
            </button>

            <?php if ($editProject): ?>

                <a href="projects.php">Cancel Edit</a>

            <?php endif; ?>

        </form>

    </div>

    <div class="box">

        <h2>My Projects List</h2>

        <?php if (count($projects) > 0): ?>

            <?php foreach ($projects as $project): ?>

                <div class="project">

                    <h3>
                        <?php echo htmlspecialchars($project["title"], ENT_QUOTES, "UTF-8"); ?>
                    </h3>

                    <p>
                        <?php echo nl2br(htmlspecialchars($project["description"] ?? "", ENT_QUOTES, "UTF-8")); ?>
                    </p>

                    <?php if (!empty($project["project_link"])): ?>

                        <p>
                            <a
                                href="<?php echo htmlspecialchars($project["project_link"], ENT_QUOTES, "UTF-8"); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View Project
                            </a>
                        </p>

                    <?php endif; ?>

                    <a href="projects.php?edit=<?php echo (int)$project["id"]; ?>">
                        Edit
                    </a>

                    <form method="POST" style="display:inline;">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION["projects_token"], ENT_QUOTES, "UTF-8"); ?>"
                        >

                        <input type="hidden" name="action" value="delete">

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo (int)$project["id"]; ?>"
                        >

                        <button
                            type="submit"
                            class="delete"
                            onclick="return confirm('Are you sure you want to delete this project?');"
                        >
                            Delete
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No projects added yet.</p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
