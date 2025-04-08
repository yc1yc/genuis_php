document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.hero-slider');
    const slides = slider.querySelectorAll('.slider-item');
    const prevBtn = slider.querySelector('.slider-prev');
    const nextBtn = slider.querySelector('.slider-next');

    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        // Désactiver tous les slides
        slides.forEach(slide => {
            slide.classList.remove('active');
        });

        // Activer le slide courant
        slides[index].classList.add('active');
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    // Navigation automatique
    function startSlideShow() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }

    function stopSlideShow() {
        clearInterval(slideInterval);
    }

    // Événements de navigation
    nextBtn.addEventListener('click', () => {
        stopSlideShow();
        nextSlide();
        startSlideShow();
    });

    prevBtn.addEventListener('click', () => {
        stopSlideShow();
        prevSlide();
        startSlideShow();
    });

    // Initialisation
    showSlide(currentSlide);
    startSlideShow();

    // Pause du diaporama au survol
    slider.addEventListener('mouseenter', stopSlideShow);
    slider.addEventListener('mouseleave', startSlideShow);
});
