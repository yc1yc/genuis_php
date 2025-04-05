<div class="error-page">
    <div class="error-content">
        <h1>404</h1>
        <h2>Page non trouvée</h2>
        <p>Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>
        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>

<style>
.error-page {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
}

.error-content h1 {
    font-size: 6rem;
    color: var(--primary-color);
    margin: 0;
    line-height: 1;
}

.error-content h2 {
    font-size: 2rem;
    margin: 1rem 0;
}

.error-content p {
    color: var(--light-text);
    margin-bottom: 2rem;
}
</style>
