document.addEventListener('DOMContentLoaded', function() {
    const swiper = new Swiper('.categories-slider', {
        // Afficher 3 slides mais défiler un par un
        slidesPerView: 3,
        slidesPerGroup: 1, // Défiler une slide à la fois
        spaceBetween: 20,
        loop: false,
        autoplay: false,

        // Navigation arrows
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        // Responsive design
        breakpoints: {
            // Mobile : 1 slide
            320: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 10
            },
            // Tablette : 2 slides
            640: {
                slidesPerView: 2,
                slidesPerGroup: 1, // Toujours défiler un par un
                spaceBetween: 15
            },
            // Desktop : 3 slides
            1024: {
                slidesPerView: 3,
                slidesPerGroup: 1, // Toujours défiler un par un
                spaceBetween: 20
            }
        }
    });
});
