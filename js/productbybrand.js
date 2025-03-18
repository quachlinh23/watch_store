document.addEventListener("DOMContentLoaded", function () {
    // Slider functionality
    let slideIndex = 0;
    const slides = [
        "images/slide_1.png",
        "images/slide_2.png",
        "images/slide_3.png",
        "images/slide_4.png",
        "images/slide_2.png"
    ];
    const slideLeft = document.getElementById("slideLeft");
    const slideRight = document.getElementById("slideRight");
    const slideContainer = document.getElementById("slide-container");

    function updateSlides(next = true) {
        slideIndex = (slideIndex + (next ? 1 : -1) + slides.length) % slides.length;
        slideLeft.src = next ? slideRight.src : slides[(slideIndex - 1 + slides.length) % slides.length];
        slideRight.src = slides[(slideIndex + 1) % slides.length];
    }

    window.nextSlide = () => updateSlides(true);
    window.prevSlide = () => updateSlides(false);
    window.closeSlide = () => (slideContainer.style.display = "none");

    // Modal filter functionality
    const modal = document.getElementById("filterModal");
    const overlay = document.getElementById("overlay");
    const btnOpen = document.getElementById("btnOpenFilter");
    const btnClose = document.getElementById("btnCloseFilter");
    const close = document.getElementById("close");

    function closeModal() {
        modal.style.display = "none";
        overlay.style.display = "none";
    }

    btnOpen.addEventListener("click", () => {
        modal.style.display = "block";
        overlay.style.display = "block";
    });

    [btnClose, close, overlay].forEach(el => el.addEventListener("click", closeModal));

    // Expand/collapse brand info
    
});