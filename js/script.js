// =========================================
// APRIL JEAN PORTFOLIO
// Main JavaScript File
// =========================================


// =========================================
// 1. MOBILE NAVIGATION
// =========================================

const menuToggle = document.getElementById("menuToggle");
const navLinks = document.querySelector(".nav-links");


if (menuToggle && navLinks) {

    menuToggle.addEventListener("click", function () {

        navLinks.classList.toggle("show");

    });


    // Close mobile menu after clicking a link

    const navigationLinks = navLinks.querySelectorAll("a");

    navigationLinks.forEach(function (link) {

        link.addEventListener("click", function () {

            navLinks.classList.remove("show");

        });

    });

}


// =========================================
// 2. TYPING EFFECT
// =========================================

const typingText = document.getElementById("typingText");


if (typingText) {

    const text = "I'm April Jean.";

    let index = 0;


    function typeText() {

        if (index < text.length) {

            typingText.textContent += text.charAt(index);

            index++;

            setTimeout(typeText, 100);

        }

    }


    typeText();

}


// =========================================
// 3. PROJECT FILTERING
// =========================================

const filterButtons = document.querySelectorAll(".filter-btn");
const projectCards = document.querySelectorAll(".project-card");


if (filterButtons.length > 0 && projectCards.length > 0) {

    filterButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            // Remove active class from all buttons

            filterButtons.forEach(function (btn) {

                btn.classList.remove("active");

            });


            // Add active class to clicked button

            button.classList.add("active");


            const selectedCategory =
                button.getAttribute("data-filter");


            projectCards.forEach(function (card) {

                const cardCategory =
                    card.getAttribute("data-category");


                if (
                    selectedCategory === "all" ||
                    selectedCategory === cardCategory
                ) {

                    card.style.display = "block";

                } else {

                    card.style.display = "none";

                }

            });

        });

    });

}


// =========================================
// 4. SCROLL-TO-TOP BUTTON
// =========================================

// Create the button

const scrollButton = document.createElement("button");

scrollButton.innerHTML = "↑";

scrollButton.id = "scrollTopBtn";

scrollButton.title = "Go to top";

document.body.appendChild(scrollButton);


// Hide button initially

scrollButton.style.display = "none";


// Show button when scrolling

window.addEventListener("scroll", function () {

    if (window.scrollY > 300) {

        scrollButton.style.display = "block";

    } else {

        scrollButton.style.display = "none";

    }

});


// Scroll to top when clicked

scrollButton.addEventListener("click", function () {

    window.scrollTo({

        top: 0,

        behavior: "smooth"

    });

});


// =========================================
// 5. CONTACT FORM VALIDATION
// =========================================

const contactForm = document.getElementById("contactForm");


if (contactForm) {

    contactForm.addEventListener("submit", function (event) {

        event.preventDefault();


        // Get form fields

        const name =
            document.getElementById("name");

        const email =
            document.getElementById("email");

        const subject =
            document.getElementById("subject");

        const message =
            document.getElementById("message");


        // Get error messages

        const nameError =
            document.getElementById("nameError");

        const emailError =
            document.getElementById("emailError");

        const subjectError =
            document.getElementById("subjectError");

        const messageError =
            document.getElementById("messageError");


        const formStatus =
            document.getElementById("formStatus");


        // Clear previous errors

        nameError.textContent = "";
        emailError.textContent = "";
        subjectError.textContent = "";
        messageError.textContent = "";
        formStatus.textContent = "";


        let isValid = true;


        // Validate name

        if (name.value.trim() === "") {

            nameError.textContent =
                "Please enter your name.";

            isValid = false;

        }


        // Validate email

        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


        if (email.value.trim() === "") {

            emailError.textContent =
                "Please enter your email.";

            isValid = false;

        } else if (!emailPattern.test(email.value.trim())) {

            emailError.textContent =
                "Please enter a valid email address.";

            isValid = false;

        }


        // Validate subject

        if (subject.value.trim() === "") {

            subjectError.textContent =
                "Please enter a subject.";

            isValid = false;

        }


        // Validate message

        if (message.value.trim() === "") {

            messageError.textContent =
                "Please enter your message.";

            isValid = false;

        } else if (message.value.trim().length < 10) {

            messageError.textContent =
                "Your message should be at least 10 characters.";

            isValid = false;

        }


        // If everything is valid

        if (isValid) {

            formStatus.textContent =
                "Thank you! Your message has been checked successfully.";

            contactForm.reset();

        }

    });

}


// =========================================
// 6. ACTIVE NAVIGATION
// =========================================

const currentPage =
    window.location.pathname.split("/").pop();


const allNavigationLinks =
    document.querySelectorAll(".nav-links a");


allNavigationLinks.forEach(function (link) {

    const linkPage =
        link.getAttribute("href");


    if (
        linkPage === currentPage ||
        (currentPage === "" && linkPage === "index.html")
    ) {

        link.classList.add("active");

    }

});