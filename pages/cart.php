<?php
require_once 'includes/cart.php';

$cart = getCart();
$total = getCartTotal();
?>

<main class="cart-page">
    <div class="hero">
        <div class="hero-content">
            <h1>Votre panier</h1>
            <p>Vérifiez et finalisez vos réservations</p>
        </div>
    </div>

    <div class="main-content">
        <?php if (empty($cart)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Votre panier est vide</h2>
                <p>Découvrez nos véhicules disponibles à la location</p>
                <a href="index.php?page=reservation" class="btn btn-primary">Voir les véhicules</a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <div class="cart-items">
                    <?php foreach ($cart as $index => $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['vehicle_name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['vehicle_name']); ?></h3>
                                <p class="dates">
                                    Du <?php echo formatDate($item['start_date']); ?>
                                    au <?php echo formatDate($item['end_date']); ?>
                                    (<?php echo $item['days']; ?> jours)
                                </p>
                                <?php if (!empty($item['options'])): ?>
                                    <div class="options">
                                        <h4>Options :</h4>
                                        <ul>
                                            <?php foreach ($item['options'] as $option): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($option['name']); ?>
                                                    (<?php echo formatPrice($option['price_per_day']); ?>/jour)
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <div class="price-details">
                                    <p>Prix de base : <?php echo formatPrice($item['base_price']); ?></p>
                                    <?php if ($item['options_price'] > 0): ?>
                                        <p>Options : <?php echo formatPrice($item['options_price']); ?></p>
                                    <?php endif; ?>
                                    <p class="total">Total : <?php echo formatPrice($item['total_price']); ?></p>
                                </div>
                            </div>
                            <button class="btn btn-danger remove-item" data-index="<?php echo $index; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Récapitulatif</h3>
                    <div class="summary-details">
                        <div class="summary-line total">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>

                    <form id="checkoutForm" class="checkout-form">
                        <?php echo csrfField(); ?>
                        
                        <div class="form-group">
                            <label for="first_name">Prénom *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Nom *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>

                        <div class="form-group">
                            <label for="address">Adresse</label>
                            <textarea id="address" name="address"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Payer <?php echo formatPrice($total); ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Inclusion de Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    const removeButtons = document.querySelectorAll('.remove-item');

    // Gestion de la suppression d'articles
    removeButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const index = this.dataset.index;
            
            try {
                const response = await fetch('api/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        csrf_token: document.querySelector('[name="csrf_token"]').value,
                        cart_index: index
                    })
                });

                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Une erreur est survenue');
                console.error(error);
            }
        });
    });

    // Gestion du paiement
    checkoutForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Traitement en cours...';

        try {
            // Création de la session de paiement
            const response = await fetch('api/create_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(new FormData(this))
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }

            // Redirection vers Stripe Checkout
            const stripe = Stripe(data.publicKey);
            const result = await stripe.redirectToCheckout({
                sessionId: data.sessionId
            });

            if (result.error) {
                throw new Error(result.error.message);
            }

        } catch (error) {
            alert(error.message);
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réessayer le paiement';
        }
    });
});</script>
