
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

            filterButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

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

const scrollButton = document.createElement("button");

scrollButton.innerHTML = "↑";
scrollButton.id = "scrollTopBtn";
scrollButton.title = "Go to top";

document.body.appendChild(scrollButton);

scrollButton.style.display = "none";

window.addEventListener("scroll", function () {

    if (window.scrollY > 300) {
        scrollButton.style.display = "block";
    } else {
        scrollButton.style.display = "none";
    }

});

scrollButton.addEventListener("click", function () {

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

});


// =========================================
// 5. CONTACT FORM VALIDATION AND SUBMISSION
// =========================================

const contactForm = document.getElementById("contactForm");

if (contactForm) {

    const name = document.getElementById("name");
    const email = document.getElementById("email");
    const subject = document.getElementById("subject");
    const message = document.getElementById("message");

    const nameError = document.getElementById("nameError");
    const emailError = document.getElementById("emailError");
    const subjectError = document.getElementById("subjectError");
    const messageError = document.getElementById("messageError");

    const formStatus = document.getElementById("formStatus");


    // Display message after successful PHP submission

    const urlParameters = new URLSearchParams(window.location.search);

    if (urlParameters.get("sent") === "success" && formStatus) {

        formStatus.textContent =
            "Thank you! Your message has been sent successfully.";

        formStatus.style.color = "green";

    }


    // Contact form submission

    contactForm.addEventListener("submit", function (event) {

        event.preventDefault();


        // Clear previous error messages

        nameError.textContent = "";
        emailError.textContent = "";
        subjectError.textContent = "";
        messageError.textContent = "";
        formStatus.textContent = "";


        let isValid = true;


        // Validate name

        if (name.value.trim() === "") {

            nameError.textContent = "Please enter your name.";
            isValid = false;

        }


        // Validate email

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email.value.trim() === "") {

            emailError.textContent = "Please enter your email.";
            isValid = false;

        } else if (!emailPattern.test(email.value.trim())) {

            emailError.textContent =
                "Please enter a valid email address.";

            isValid = false;

        }


        // Validate subject

        if (subject.value.trim() === "") {

            subjectError.textContent = "Please enter a subject.";
            isValid = false;

        }


        // Validate message

        if (message.value.trim() === "") {

            messageError.textContent = "Please enter your message.";
            isValid = false;

        } else if (message.value.trim().length < 10) {

            messageError.textContent =
                "Your message should be at least 10 characters.";

            isValid = false;

        }


        // Submit valid form to PHP

        if (isValid) {

            formStatus.textContent = "Sending your message...";
            formStatus.style.color = "green";

            // Submit the form to process_contact.php

            HTMLFormElement.prototype.submit.call(contactForm);

        }

    });

}


// =========================================
// 6. ACTIVE NAVIGATION
// =========================================

const currentPage = window.location.pathname.split("/").pop();

const allNavigationLinks =
    document.querySelectorAll(".nav-links a");

allNavigationLinks.forEach(function (link) {

    const linkPage = link.getAttribute("href");

    if (
        linkPage === currentPage ||
        (currentPage === "" && linkPage === "index.html")
    ) {

        link.classList.add("active");

    }

});
