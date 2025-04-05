<main class="contact-page">
    <div class="hero">
        <div class="hero-content">
            <h1>Contactez-nous</h1>
            <p>Notre équipe est à votre disposition pour répondre à toutes vos questions</p>
        </div>
    </div>

    <div class="main-content">
        <div class="contact-content">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Nos Coordonnées</h2>
                    <div class="info-card">
                        <h3>Adresse</h3>
                        <p>123 Avenue des Voitures</p>
                        <p>75000 Paris, France</p>
                    </div>
                    <div class="info-card">
                        <h3>Téléphone</h3>
                        <p>+33 1 23 45 67 89</p>
                    </div>
                    <div class="info-card">
                        <h3>Email</h3>
                        <p>contact@thegenuis.com</p>
                    </div>
                    <div class="info-card">
                        <h3>Horaires d'ouverture</h3>
                        <p>Lundi - Vendredi : 8h00 - 19h00</p>
                        <p>Samedi : 9h00 - 17h00</p>
                        <p>Dimanche : Fermé</p>
                    </div>
                </div>

                <div class="contact-form">
                    <h2>Envoyez-nous un message</h2>
                    <form id="contactForm" action="api/send_contact.php" method="POST">
                        <div class="form-group">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="subject">Sujet</label>
                            <select id="subject" name="subject" required>
                                <option value="">Choisissez un sujet</option>
                                <option value="reservation">Question sur une réservation</option>
                                <option value="information">Demande d'information</option>
                                <option value="reclamation">Réclamation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer le message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
                this.reset();
            } else {
                alert('Une erreur est survenue. Veuillez réessayer.');
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        }
    });
});
</script>
