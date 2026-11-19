
<?php

session_start();

require_once "includes/db.php";

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Get profile information
$stmt = $pdo->query("SELECT * FROM profile ORDER BY id ASC LIMIT 1");
$profile = $stmt->fetch();

$fullName = $profile["full_name"] ?? "April Jean Bagio";
$displayName = $profile["display_name"] ?? "April Jean";

$email = $profile["email"] ?? "apriljeanbaguio@gmail.com";

function cleanOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact | <?php echo cleanOutput($displayName); ?></title>

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
                <li><a href="resume.php">Resume</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>

            </ul>

            <button class="menu-toggle" id="menuToggle">
                ☰
            </button>

        </nav>

    </header>

    <!-- Contact Page -->

    <main>

        <section class="page-header">

            <p>LET'S CONNECT</p>

            <h1>Contact Me</h1>

            <p>
                If you would like to get in touch with me,
                you can use the information below or send
                a message through the contact form.
            </p>

        </section>

        <!-- Contact Section -->

        <section class="contact-section">

            <div class="contact-container">

                <!-- Contact Information -->

                <div class="contact-info">

                    <p class="contact-label">
                        GET IN TOUCH
                    </p>

                    <h2>Let's Connect</h2>

                    <p>
                        I am always open to connecting with
                        classmates, friends, and people who
                        are interested in my projects and
                        learning journey.
                    </p>

                    <!-- Email -->

                    <div class="contact-item">

                        <div class="contact-icon">
                            @
                        </div>

                        <div>

                            <h3>Email</h3>

                            <a href="mailto:<?php echo cleanOutput($email); ?>">
                                <?php echo cleanOutput($email); ?>
                            </a>

                        </div>

                    </div>

                    <!-- Facebook -->

                    <div class="contact-item">

                        <div class="contact-icon">
                            f
                        </div>

                        <div>

                            <h3>Facebook</h3>

                            <a
                                href="https://www.facebook.com/profile.php?id=61578668607556"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Visit my Facebook
                            </a>

                        </div>

                    </div>

                    <!-- School -->

                    <div class="contact-item">

                        <div class="contact-icon"></div>

                        <div>

                            <h3>School</h3>

                            <p>
                                NORSU Bayawan-Santa Catalina Campus
                            </p>

                        </div>

                    </div>

                </div>

                <!-- Contact Form -->

                <div class="contact-form-container">

                    <h2>Send Me a Message</h2>

                    <p>Fill out the form below.</p>

                    <form
                        id="contactForm"
                        method="POST"
                        action="process_contact.php"
                        novalidate
                    >

                        <!-- CSRF Protection Token -->

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo cleanOutput($_SESSION["csrf_token"]); ?>"
                        >

                        <div class="form-group">

                            <label for="name">Your Name</label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Enter your name"
                                maxlength="100"
                                required
                            >

                            <small class="error-message" id="nameError"></small>

                        </div>

                        <div class="form-group">

                            <label for="email">Your Email</label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                maxlength="150"
                                required
                            >

                            <small class="error-message" id="emailError"></small>

                        </div>

                        <div class="form-group">

                            <label for="subject">Subject</label>

                            <input
                                type="text"
                                id="subject"
                                name="subject"
                                placeholder="Enter a subject"
                                maxlength="150"
                                required
                            >

                            <small class="error-message" id="subjectError"></small>

                        </div>

                        <div class="form-group">

                            <label for="message">Message</label>

                            <textarea
                                id="message"
                                name="message"
                                rows="6"
                                maxlength="5000"
                                placeholder="Write your message here..."
                                required
                            ></textarea>

                            <small class="error-message" id="messageError"></small>

                        </div>

                        <button
                            type="submit"
                            class="submit-btn"
                        >
                            Send Message
                        </button>

                        <p id="formStatus" class="form-status"></p>

                    </form>

                </div>

            </div>

        </section>

        <!-- Social Section -->

        <section class="social-section">

            <div class="section-title">

                <p>FIND ME ONLINE</p>

                <h2>Social Links</h2>

            </div>

            <div class="social-links">

                <a
                    href="https://www.facebook.com/profile.php?id=61578668607556"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Facebook
                </a>

                <a href="mailto:<?php echo cleanOutput($email); ?>">
                    Email Me
                </a>

            </div>

        </section>

    </main>

    <!-- Footer -->

    <footer>

        <p>
            © 2026 <?php echo cleanOutput($displayName); ?>. All Rights Reserved.
        </p>

    </footer>

    <script src="js/script.js?v=2"></script>

</body>

</html>
