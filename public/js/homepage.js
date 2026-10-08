// Smooth scroll untuk navigasi
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute("href")).scrollIntoView({
            behavior: "smooth",
        });
    });
});

// Animasi fade-in pada scroll
window.addEventListener("scroll", function () {
    const elements = document.querySelectorAll(".card");
    elements.forEach((el) => {
        if (el.getBoundingClientRect().top < window.innerHeight) {
            el.style.opacity = "1";
            el.style.transform = "translateY(0)";
        }
    });
});

// Inisialisasi opacity awal untuk animasi
document.addEventListener("DOMContentLoaded", function () {
    const cards = document.querySelectorAll(".card");
    cards.forEach((card) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "opacity 0.5s, transform 0.5s";
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const notif = document.getElementById("notification");
    if (notif) {
        notif.classList.add("show");

        // otomatis hilang setelah 3 detik
        setTimeout(() => {
            notif.classList.remove("show");
            notif.classList.add("hide");
        }, 3000);
    }
});
