
<?php

require_once "includes/db.php";

// Get profile information from the database
$stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
$profile = $stmt->fetch();

// Default information if no profile is available
$displayName = $profile["display_name"] ?? "April Jean";
$fullName = $profile["full_name"] ?? "April Jean Bagio";
$title = $profile["title"] ?? "3rd-Year Information Technology Student";
$introduction = $profile["introduction"] ?? "I'm a friendly, hardworking, and creative student who enjoys exploring technology, creating projects, and learning new things.";
$aboutMe = $profile["about_me"] ?? "I am currently a Bachelor in Information Technology student at Negros Oriental State University, Bayawan-Santa Catalina Campus.";
$profileImage = $profile["profile_image"] ?? "images/profile.jpg";

// Securely display database information
function cleanOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo cleanOutput($displayName); ?> | Personal Portfolio</title>

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

            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="about.php">About</a></li>
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

<!-- Home Section -->
<main>

    <section class="hero">

        <div class="hero-text">

            <p class="welcome-text">
                Welcome to my portfolio
            </p>

            <h1>
                Hi, I'm <span><?php echo cleanOutput($displayName); ?></span>
            </h1>

            <h2>
                <?php echo cleanOutput($title); ?>
            </h2>

            <p>
                <?php echo cleanOutput($introduction); ?>
            </p>

            <p>
                I know that learning Information Technology can be
                challenging, but I'm willing to learn, improve,
                and discover what I am capable of doing.
            </p>

            <div class="hero-buttons">

                <a href="about.php" class="btn">
                    Get to Know Me
                </a>

                <a href="contact.php" class="btn btn-outline">
                    Contact Me
                </a>

            </div>

        </div>

        <!-- Profile Image -->
        <div class="hero-image">

            <img
                src="<?php echo cleanOutput($profileImage); ?>"
                alt="<?php echo cleanOutput($displayName); ?> - Profile Picture"
                class="profile-img"
            >

        </div>

    </section>

    <!-- Introduction Section -->
    <section class="intro">

        <div class="section-title">

            <p>ABOUT MY PORTFOLIO</p>

            <h2>
                A little about me
            </h2>

        </div>

        <div class="intro-content">

            <p>
                <?php echo cleanOutput($aboutMe); ?>
            </p>

            <p>
                I chose Information Technology because I wanted to
                explore something new. I knew that it would be
                difficult, but I also wanted to challenge myself,
                learn more about technology, and develop new skills.
            </p>

            <p>
                In the future, I would like to work abroad and use
                the opportunities I find to help my family.
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

<!-- JavaScript -->
<script src="js/script.js"></script>

</body>

</html>
