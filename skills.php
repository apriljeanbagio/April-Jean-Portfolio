
<?php

require_once "includes/db.php";

// Get profile information
$stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
$profile = $stmt->fetch();

$fullName = $profile["full_name"] ?? "April Jean Bagio";
$displayName = $profile["display_name"] ?? "April Jean";

// Get skills from the database
$stmt = $pdo->query("SELECT * FROM skills ORDER BY id ASC");
$skills = $stmt->fetchAll();

// Secure output function
function cleanOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Skills | <?php echo cleanOutput($displayName); ?></title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <!-- Navigation -->
    <header>

        <nav class="navbar">

            <div class="logo">
                <?php echo cleanOutput($fullName); ?>
            </div>

            <ul class="nav-links">

                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="skills.php" class="active">Skills</a></li>
                <li><a href="projects.php">Projects</a></li>
                <li><a href="resume.php">Resume</a></li>
                <li><a href="contact.php">Contact</a></li>

            </ul>

            <button class="menu-toggle" id="menuToggle">
                ☰
            </button>

        </nav>

    </header>

    <!-- Skills Page -->
    <main>

        <!-- Page Header -->
        <section class="page-header">

            <p>WHAT I CAN DO</p>

            <h1>My Skills</h1>

            <p>
                These are some of the skills I have developed
                through school activities, projects, and personal
                learning.
            </p>

        </section>

        <!-- Skills -->
        <section class="skills-section">

            <div class="skills-grid">

                <?php if (!empty($skills)): ?>

                    <?php $number = 1; ?>

                    <?php foreach ($skills as $skill): ?>

                        <div class="skill-card">

                            <div class="skill-number">
                                <?php echo sprintf("%02d", $number); ?>
                            </div>

                            <h2>
                                <?php echo cleanOutput($skill["skill_name"]); ?>
                            </h2>

                            <p>
                                <?php echo cleanOutput($skill["description"]); ?>
                            </p>

                        </div>

                        <?php $number++; ?>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No skills have been added yet.</p>

                <?php endif; ?>

            </div>

        </section>

        <!-- Personal Strengths -->
        <section class="strength-section">

            <div class="section-title">

                <p>MY PERSONAL STRENGTHS</p>

                <h2>Skills I Continue to Improve</h2>

            </div>

            <div class="strength-content">

                <div class="strength-item">

                    <h3>Creativity</h3>

                    <p>
                        I enjoy exploring creative ideas through
                        drawing, graphic design, and
                        other projects.
                    </p>

                </div>

                <div class="strength-item">

                    <h3>Time Management</h3>

                    <p>
                        I am learning how to manage my time better
                        while balancing school activities and
                        personal responsibilities.
                    </p>

                </div>

                <div class="strength-item">

                    <h3>Willingness to Learn</h3>

                    <p>
                        I am willing to learn new skills even when
                        something is difficult or unfamiliar to me.
                    </p>

                </div>

            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer>

        <p>
            © 2026 <?php echo cleanOutput($displayName); ?>.
            All Rights Reserved.
        </p>

    </footer>

    <!-- JavaScript -->
    <script src="js/script.js"></script>

</body>

</html>
