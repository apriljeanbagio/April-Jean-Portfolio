
<?php

require_once "includes/db.php";

// Get profile information
$stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
$profile = $stmt->fetch();

$fullName = $profile["full_name"] ?? "April Jean Bagio";
$displayName = $profile["display_name"] ?? "April Jean";

// Get education records
$stmt = $pdo->query("SELECT * FROM education ORDER BY year_started DESC");
$educationRecords = $stmt->fetchAll();

// Get experience records
$stmt = $pdo->query("SELECT * FROM experience ORDER BY start_date DESC");
$experienceRecords = $stmt->fetchAll();

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

    <title>Resume | <?php echo cleanOutput($displayName); ?></title>

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
                <li><a href="projects.php">Projects</a></li>
                <li><a href="resume.php" class="active">Resume</a></li>
                <li><a href="contact.php">Contact</a></li>

            </ul>

            <button class="menu-toggle" id="menuToggle">
                ☰
            </button>

        </nav>

    </header>

    <!-- Resume Page -->

    <main>

        <!-- Page Header -->

        <section class="page-header">

            <p>MY BACKGROUND</p>

            <h1>Resume</h1>

            <p>
                My educational background, activities, projects,
                and skills as an Information Technology student.
            </p>

        </section>

        <!-- Education -->

        <section class="resume-section">

            <div class="section-title">

                <p>EDUCATION</p>

                <h2>Educational Background</h2>

            </div>

            <div class="timeline">

                <?php if (!empty($educationRecords)): ?>

                    <?php foreach ($educationRecords as $education): ?>

                        <div class="timeline-item">

                            <div class="timeline-dot"></div>

                            <div class="timeline-content">

                                <span>
                                    <?php
                                    echo !empty($education["year_ended"])
                                        ? "Education"
                                        : "Currently Studying";
                                    ?>
                                </span>

                                <h3>
                                    <?php echo cleanOutput($education["school_name"]); ?>
                                </h3>

                                <p>
                                    <?php echo cleanOutput($education["degree"]); ?>
                                </p>

                                <?php if (!empty($education["description"])): ?>

                                    <p>
                                        <?php echo cleanOutput($education["description"]); ?>
                                    </p>

                                <?php endif; ?>

                                <strong>

                                    <?php
                                    echo cleanOutput($education["year_started"]);

                                    if (!empty($education["year_ended"])) {
                                        echo " - " . cleanOutput($education["year_ended"]);
                                    } else {
                                        echo " - Present";
                                    }
                                    ?>

                                </strong>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No education records have been added yet.</p>

                <?php endif; ?>

            </div>

        </section>

        <!-- Academic Activities -->

        <section class="resume-section">

            <div class="section-title">

                <p>ACADEMIC ACTIVITIES</p>

                <h2>What I Have Practiced</h2>

            </div>

            <div class="resume-grid">

                <div class="resume-card">

                    <h3>Animation Projects</h3>

                    <p>
                        Created animation projects using Adobe Animate
                        as part of school activities and requirements.
                    </p>

                </div>

                <div class="resume-card">

                    <h3>Graphic Design Activities</h3>

                    <p>
                        Created visual materials and practiced graphic
                        design using tools such as Canva and Adobe
                        Illustrator.
                    </p>

                </div>

                <div class="resume-card">

                    <h3>Networking Activities</h3>

                    <p>
                        Practiced network design, device configuration,
                        topologies, and connections using Cisco Packet
                        Tracer.
                    </p>

                </div>

                <div class="resume-card">

                    <h3>Group Projects</h3>

                    <p>
                        Participated in school group projects where I
                        practiced teamwork, communication, and sharing
                        responsibilities.
                    </p>

                </div>

            </div>

        </section>

        <!-- Experience -->

        <section class="resume-section">

            <div class="section-title">

                <p>EXPERIENCE</p>

                <h2>Work and Learning Experience</h2>

            </div>

            <div class="timeline">

                <?php if (!empty($experienceRecords)): ?>

                    <?php foreach ($experienceRecords as $experience): ?>

                        <div class="timeline-item">

                            <div class="timeline-dot"></div>

                            <div class="timeline-content">

                                <h3>
                                    <?php echo cleanOutput($experience["position"]); ?>
                                </h3>

                                <p>
                                    <?php echo cleanOutput($experience["organization"]); ?>
                                </p>

                                <p>
                                    <?php echo cleanOutput($experience["description"]); ?>
                                </p>

                                <strong>

                                    <?php
                                    echo cleanOutput($experience["start_date"]);

                                    if (!empty($experience["end_date"])) {
                                        echo " - " . cleanOutput($experience["end_date"]);
                                    } else {
                                        echo " - Present";
                                    }
                                    ?>

                                </strong>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No experience records have been added yet.</p>

                <?php endif; ?>

            </div>

        </section>

        <!-- Training and Learning -->

        <section class="resume-section learning-section">

            <div class="section-title">

                <p>LEARNING EXPERIENCE</p>

                <h2>Training & Development</h2>

            </div>

            <div class="learning-content">

                <p>
                    As an Information Technology student, I continue
                    to develop my skills through school activities,
                    hands-on projects, and learning new software and
                    technologies.
                </p>

                <p>
                    Some of the tools and areas I have practiced
                    include graphic design, animation, networking,
                    presentations, documentation, and basic web
                    development.
                </p>

                <p>
                    I am still learning and improving, but I am
                    willing to explore new technologies and take on
                    challenges that can help me grow.
                </p>

            </div>

        </section>

        <!-- Skills Summary -->

        <section class="resume-section">

            <div class="section-title">

                <p>SKILLS SUMMARY</p>

                <h2>Skills & Abilities</h2>

            </div>

            <div class="resume-skills">

                <span>Canva,</span>
                <span>Adobe Illustrator,</span>
                <span>Adobe Animate,</span>
                <span>Microsoft Word,</span>
                <span>Microsoft PowerPoint,</span>
                <span>Graphic Design,</span>
                <span>Animation,</span>
                <span>Communication,</span>
                <span>Problem Solving,</span>
                <span>Teamwork,</span>
                <span>Creativity,</span>
                <span>Time Management.</span>

            </div>

        </section>

        <!-- Personal Statement -->

        <section class="resume-statement">

            <div>

                <p>PERSONAL STATEMENT</p>

                <h2>Learning, Improving, and Moving Forward</h2>

                <p>
                    I may still be developing my skills, but every
                    project gives me an opportunity to learn something
                    new. I want to continue improving myself and
                    prepare for future opportunities in the IT field.
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
