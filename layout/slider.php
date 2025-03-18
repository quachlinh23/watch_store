<?php
include 'class/slider.php';
$slide = new Slider();
?>
<section class="slider-container">
    <div class="slider">
        <div class="slides">
            <?php
            $sdlist = $slide->show();
            $slideCount = 0; // Đếm số lượng slide
            if ($sdlist) {
                while ($result = $sdlist->fetch_assoc()) {
                    $slideCount++;
                    echo '<div class="slide"><img src="admin/' .$result['hinhAnh'].'" alt="Slide ' . $slideCount . '"></div>';
                }
            }
            ?>
        </div>

        <!-- Nút điều hướng -->
        <button class="prev">&#10094;</button>
        <button class="next">&#10095;</button>

        <!-- Dots -->
        <div class="dots">
            <?php for ($i = 0; $i < $slideCount; $i++) {
                echo '<span class="dot" data-slide="' . $i . '"></span>';
            } ?>
        </div>
    </div>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let slides = document.querySelectorAll(".slide");
        let dots = document.querySelectorAll(".dot");
        let prev = document.querySelector(".prev");
        let next = document.querySelector(".next");
        let currentIndex = 0;
        let interval;

        function showSlide(index) {
            let slider = document.querySelector(".slides");
            let width = document.querySelector(".slider").offsetWidth;
            slider.style.transform = `translateX(-${index * width}px)`;
            
            dots.forEach(dot => dot.classList.remove("active"));
            dots[index].classList.add("active");
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % slides.length;
            showSlide(currentIndex);
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            showSlide(currentIndex);
        }

        // Auto slide
        function startAutoSlide() {
            interval = setInterval(nextSlide, 3000);
        }

        function stopAutoSlide() {
            clearInterval(interval);
        }

        // Event listeners
        next.addEventListener("click", () => {
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
        });

        prev.addEventListener("click", () => {
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
        });

        dots.forEach((dot, index) => {
            dot.addEventListener("click", () => {
                stopAutoSlide();
                currentIndex = index;
                showSlide(currentIndex);
                startAutoSlide();
            });
        });

        // Khởi động slider
        showSlide(currentIndex);
        startAutoSlide();
    });
</script>