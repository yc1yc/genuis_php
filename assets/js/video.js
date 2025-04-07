function playVideo() {
    const video = document.getElementById('myVideo');
    const overlay = document.querySelector('.overlay');
    
    if (video.paused) {
        video.play();
        overlay.style.display = 'none';
    } else {
        video.pause();
        overlay.style.display = 'flex';
    }
}

// Ajouter un écouteur d'événements pour réafficher l'overlay quand la vidéo se termine
document.getElementById('myVideo').addEventListener('ended', function() {
    const overlay = document.querySelector('.overlay');
    overlay.style.display = 'flex';
});
