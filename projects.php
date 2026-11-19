
<?php

require_once "includes/db.php";

// Get profile information
$stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
$profile = $stmt->fetch();

$fullName = $profile["full_name"] ?? "April Jean Bagio";
$displayName = $profile["display_name"] ?? "April Jean";

// Get projects from the database
$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll();

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

    <title>Projects | <?php echo cleanOutput($displayName); ?></title>

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
                <li><a href="skills.php">Skills</a></li>
                <li><a href="projects.php" class="active">Projects</a></li>
                <li><a href="resume.php">Resume</a></li>
                <li><a href="contact.php">Contact</a></li>

            </ul>

            <button class="menu-toggle" id="menuToggle">
                ☰
            </button>

        </nav>

    </header>

    <!-- Projects Page -->

    <main>

        <section class="page-header">

            <p>MY SCHOOL WORK</p>

            <h1>My Projects</h1>

            <p>
                Here are some of the projects I have worked on
                during my studies and learning activities.
            </p>

        </section>

        <!-- Projects -->

        <section class="projects-section">

            <div class="projects-grid">

                <?php if (!empty($projects)): ?>

                    <?php foreach ($projects as $project): ?>

                        <article class="project-card">

                            <?php if (!empty($project["image"])): ?>

                                <div class="project-image">

                                    <img
                                        src="<?php echo cleanOutput($project["image"]); ?>"
                                        alt="<?php echo cleanOutput($project["title"]); ?>"
                                    >

                                </div>

                            <?php endif; ?>

                            <div class="project-content">

                                <span class="project-category">
                                    Project
                                </span>

                                <h2>
                                    <?php echo cleanOutput($project["title"]); ?>
                                </h2>

                                <p>
                                    <?php echo cleanOutput($project["description"]); ?>
                                </p>

                                <?php if (!empty($project["project_link"])): ?>

                                    <div class="project-tools">

                                        <a
                                            href="<?php echo cleanOutput($project["project_link"]); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            View Project
                                        </a>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No projects have been added yet.</p>

                <?php endif; ?>

            </div>

        </section>

        <!-- Project Note -->

        <section class="project-note">

            <div>

                <p>
                    These projects represent some of the things I have
                    learned and practiced as an Information Technology
                    student.
                </p>

                <p>
                    I continue to improve my skills by trying new
                    activities and learning from every project I make.
                </p>

            </div>

        </section>

    </main>

    <!-- Footer -->

    <footer>

        <p>
            © 2026 <?php echo cleanOutput($displayName); ?>. All Rights Reserved.
        </p>

    </footer>

    <script src="js/script.js"></script>

</body>

</html>
