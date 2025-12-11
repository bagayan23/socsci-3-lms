// Form Validation
function validateForm() {
    const form = document.getElementById("myForm");
    const name = form.elements["name"].value;
    const email = form.elements["email"].value;

    if (!name || !email) {
        alert("Name and Email are required!");
        return false;
    }
    // Basic email validation
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!email.match(emailPattern)) {
        alert("Please enter a valid email address!");
        return false;
    }
    return true;
}

// Password Toggle
const passwordToggle = document.getElementById("passwordToggle");
const passwordInput = document.getElementById("password");

passwordToggle.addEventListener("click", function() {
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);
});

// Carousel Controls
let currentIndex = 0;
const slides = document.querySelectorAll(".carousel-slide");

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.style.display = (i === index) ? "block" : "none";
    });
}

function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    showSlide(currentIndex);
}

function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    showSlide(currentIndex);
}

// Mobile Menu Toggle
const mobileMenuToggle = document.getElementById("mobileMenuToggle");
const mobileMenu = document.getElementById("mobileMenu");

mobileMenuToggle.addEventListener("click", function() {
    mobileMenu.classList.toggle("active");
});

// Smooth Animations
document.querySelectorAll(".animate").forEach(element => {
    element.style.transition = "all 0.5s ease-in-out";
});
