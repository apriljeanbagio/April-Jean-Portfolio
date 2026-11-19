
<?php

require_once "includes/db.php";

// Get profile information
$stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
$profile = $stmt->fetch();

// Default information
$fullName = $profile["full_name"] ?? "April Jean Bagio";
$displayName = $profile["display_name"] ?? "April Jean";
$title = $profile["title"] ?? "3rd-Year Information Technology Student";

// Get education records
$stmt = $pdo->query("SELECT * FROM education ORDER BY id ASC");
$educationRecords = $stmt->fetchAll();

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

    <title>About | <?php echo cleanOutput($displayName); ?></title>

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
                <li><a href="about.php" class="active">About</a></li>
                <li><a href="skills.php">Skills</a></li>
                <li><a href="projects.php">Projects</a></li>
                <li><a href="resume.php">Resume</a></li>
                <li><a href="contact.php">Contact</a></li>

            </ul>

            <button class="menu-toggle" id="menuToggle">
                ☰
            </button>

        </nav>

    </header>

    <!-- About Page -->
    <main>

        <section class="page-header">

            <p>GET TO KNOW ME</p>

            <h1>About Me</h1>

            <p>
                A little more about who I am, what I enjoy,
                and what I hope to achieve in the future.
            </p>

        </section>

        <!-- Personal Background -->
        <section class="about-section">

            <div class="about-content">

                <div class="about-text">

                    <h2>Who I Am</h2>

                    <p>
                        Hi! I'm <?php echo cleanOutput($fullName); ?>,
                        a <?php echo cleanOutput($title); ?> at
                        Negros Oriental State University,
                        Bayawan-Santa Catalina Campus.
                    </p>

                    <p>
                        I describe myself as friendly, hardworking,
                        and creative. I enjoy learning new things
                        and exploring activities that allow me to
                        express my creativity.
                    </p>

                    <p>
                        I know that learning Information Technology
                        can be challenging, but I am willing to learn,
                        improve, and discover what I am capable of doing.
                    </p>

                </div>

                <div class="about-card">

                    <h3>Personal Information</h3>

                    <p>
                        <strong>Name:</strong>
                        <?php echo cleanOutput($fullName); ?>
                    </p>

                    <p>
                        <strong>Program:</strong>
                        Bachelor in Information Technology
                    </p>

                    <p>
                        <strong>Year:</strong>
                        3rd Year
                    </p>

                    <p>
                        <strong>Section:</strong>
                        Section B
                    </p>

                    <p>
                        <strong>School:</strong>
                        Negros Oriental State University
                    </p>

                    <p>
                        <strong>Campus:</strong>
                        Bayawan-Santa Catalina Campus
                    </p>

                </div>

            </div>

        </section>

        <!-- Education -->
        <section class="education-section">

            <div class="section-title">

                <p>MY BACKGROUND</p>

                <h2>Education</h2>

            </div>

            <div class="education-grid">

                <?php if (!empty($educationRecords)): ?>

                    <?php foreach ($educationRecords as $education): ?>

                        <div class="education-card">

                            <h3>
                                <?php echo cleanOutput($education["degree"]); ?>
                            </h3>

                            <h4>
                                <?php echo cleanOutput($education["school_name"]); ?>
                            </h4>

                            <p>
                                <?php echo cleanOutput($education["description"]); ?>
                            </p>

                            <span>
                                <?php
                                $start = $education["year_started"] ?? "";
                                $end = $education["year_ended"] ?? "";

                                echo cleanOutput($start);

                                if (!empty($end)) {
                                    echo " - " . cleanOutput($end);
                                } elseif (!empty($start)) {
                                    echo " - Present";
                                }
                                ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No education records available yet.</p>

                <?php endif; ?>

            </div>

        </section>

        <!-- Interests -->
        <section class="interests-section">

            <div class="section-title">

                <p>WHAT I ENJOY</p>

                <h2>My Interests</h2>

            </div>

            <div class="interest-list">

                <div class="interest-item">
                    <h3>Watching Movies</h3>
                    <p>I enjoy watching movies during my free time.</p>
                </div>

                <div class="interest-item">
                    <h3>Listening to Music</h3>
                    <p>Music is one of the things I enjoy when relaxing.</p>
                </div>

                <div class="interest-item">
                    <h3>Gaming</h3>
                    <p>I enjoy playing games as a way to have fun and relax.</p>
                </div>

                <div class="interest-item">
                    <h3>Drawing</h3>
                    <p>Drawing allows me to express my creativity.</p>
                </div>

                <div class="interest-item">
                    <h3>Eating</h3>
                    <p>I also enjoy trying and enjoying different food.</p>
                </div>

            </div>

        </section>

        <!-- Future Goal -->
        <section class="goal-section">

            <div class="goal-content">

                <p class="goal-label">MY FUTURE GOAL</p>

                <h2>Building a Better Future</h2>

                <p>
                    My goal is to work abroad someday and use the
                    opportunities I find to help my family.
                </p>

                <p>
                    I want to continue learning and developing my
                    skills so that I can become more confident in
                    what I do and prepare myself for future
                    opportunities.
                </p>

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
