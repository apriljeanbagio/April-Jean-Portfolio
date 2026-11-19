
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
if (empty($_SESSION["messages_token"])) {
    $_SESSION["messages_token"] = bin2hex(random_bytes(32));
}

$messageNotice = "";

// Handle message actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["messages_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $id = filter_var(
        $_POST["message_id"] ?? 0,
        FILTER_VALIDATE_INT
    );

    if ($id === false || $id <= 0) {

        $messageNotice = "Invalid message record.";

    } else {

        // Check whether the message exists
        $checkStmt = $pdo->prepare(
            "SELECT id FROM messages WHERE id = ?"
        );

        $checkStmt->execute([$id]);
        $messageExists = $checkStmt->fetch();

        if (!$messageExists) {

            $messageNotice = "Message not found.";

        } elseif (isset($_POST["toggle_read"])) {

            $stmt = $pdo->prepare(
                "UPDATE messages
                 SET is_read = CASE
                    WHEN is_read = 1 THEN 0
                    ELSE 1
                 END
                 WHERE id = ?"
            );

            $stmt->execute([$id]);

            $messageNotice = "Message status updated successfully.";

        } elseif (isset($_POST["delete_message"])) {

            $stmt = $pdo->prepare(
                "DELETE FROM messages WHERE id = ?"
            );

            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $messageNotice = "Message deleted successfully.";
            } else {
                $messageNotice = "Message could not be deleted.";
            }

        } else {

            $messageNotice = "Invalid message action.";

        }
    }
}

// Get all messages
$stmt = $pdo->query(
    "SELECT * FROM messages ORDER BY created_at DESC"
);

$messageList = $stmt->fetchAll();

// Count messages
$totalMessages = count($messageList);
$unreadMessages = 0;

foreach ($messageList as $item) {

    if ((int)$item["is_read"] === 0) {
        $unreadMessages++;
    }

}

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

    <title>Manage Messages</title>

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
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 16px;
            background: #0b6db7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .message-notice {
            padding: 12px;
            background: #e6f4ea;
            border-radius: 5px;
            margin-top: 20px;
        }

        .message-card {
            border: 1px solid #dce6f0;
            border-radius: 7px;
            padding: 20px;
            margin-top: 18px;
            background: #ffffff;
        }

        .unread {
            border-left: 5px solid #0b6db7;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
            background: #e8f2fc;
            color: #0b4f8a;
        }

        .read-status {
            background: #eeeeee;
            color: #555555;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        button {
            padding: 9px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            background: #0b6db7;
        }

        .delete-button {
            background: #c0392b;
        }

        .actions form {
            margin: 0;
        }

        .message-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
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

    <h1>Manage Messages</h1>

    <p>View and manage messages sent through your portfolio contact form.</p>

    <a class="back-link" href="dashboard.php">
        Back to Dashboard
    </a>

    <?php if ($messageNotice !== ""): ?>

        <p class="message-notice">
            <?php echo escape($messageNotice); ?>
        </p>

    <?php endif; ?>

    <div class="panel">

        <h2>Message Summary</h2>

        <p>
            <strong>Total Messages:</strong>
            <?php echo $totalMessages; ?>
        </p>

        <p>
            <strong>Unread Messages:</strong>
            <?php echo $unreadMessages; ?>
        </p>

    </div>

    <div class="panel">

        <h2>Received Messages</h2>

        <?php if (count($messageList) > 0): ?>

            <?php foreach ($messageList as $item): ?>

                <div class="message-card <?php echo (int)$item["is_read"] === 0 ? "unread" : ""; ?>">

                    <h3>
                        <?php echo escape($item["subject"]); ?>
                    </h3>

                    <?php if ((int)$item["is_read"] === 1): ?>

                        <span class="status read-status">
                            Read
                        </span>

                    <?php else: ?>

                        <span class="status">
                            Unread
                        </span>

                    <?php endif; ?>

                    <p>
                        <strong>Sender:</strong>
                        <?php echo escape($item["sender_name"]); ?>
                    </p>

                    <p>
                        <strong>Email:</strong>
                        <?php echo escape($item["sender_email"]); ?>
                    </p>

                    <p>
                        <strong>Date Received:</strong>
                        <?php echo escape($item["created_at"]); ?>
                    </p>

                    <p>
                        <strong>Message:</strong>
                    </p>

                    <div class="message-content">
                        <?php echo escape($item["message"]); ?>
                    </div>

                    <div class="actions">

                        <form method="POST">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo escape($_SESSION["messages_token"]); ?>"
                            >

                            <input
                                type="hidden"
                                name="message_id"
                                value="<?php echo (int)$item["id"]; ?>"
                            >

                            <button type="submit" name="toggle_read">

                                <?php
                                echo (int)$item["is_read"] === 1
                                    ? "Mark as Unread"
                                    : "Mark as Read";
                                ?>

                            </button>

                        </form>

                        <form
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this message?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo escape($_SESSION["messages_token"]); ?>"
                            >

                            <input
                                type="hidden"
                                name="message_id"
                                value="<?php echo (int)$item["id"]; ?>"
                            >

                            <button
                                class="delete-button"
                                type="submit"
                                name="delete_message"
                            >
                                Delete Message
                            </button>

                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No messages received yet.</p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
