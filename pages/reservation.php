<?php
require_once 'includes/cart.php';

// Récupération des catégories de véhicules
$stmt = $pdo->query("SELECT * FROM vehicle_categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des options disponibles
$stmt = $pdo->query("SELECT * FROM options");
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="reservation-page">
    <div class="hero">
        <div class="hero-content">
            <h1>Réservation de véhicule</h1>
            <p>Choisissez les dates et trouvez le véhicule parfait pour vos besoins</p>
        </div>
    </div>

    <div class="main-content">
        <div class="reservation-form">
            <form id="searchForm" class="search-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="startDate">Date de début</label>
                        <input type="date" id="startDate" name="startDate" required 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="endDate">Date de fin</label>
                        <input type="date" id="endDate" name="endDate" required 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </div>
                </div>
            </form>
        </div>

        <div id="vehicleResults" class="vehicle-grid">
            <!-- Les véhicules seront chargés ici dynamiquement -->
        </div>
    </div>
</main>

<template id="vehicleTemplate">
    <div class="vehicle-card">
        <img src="" alt="Photo du véhicule" class="vehicle-image">
        <div class="vehicle-info">
            <h3 class="vehicle-title"></h3>
            <p class="vehicle-description"></p>
            <div class="vehicle-details">
                <span class="vehicle-price"></span>
                <span class="vehicle-category"></span>
            </div>
            <div class="vehicle-specs">
                <span class="spec fuel-type"></span>
                <span class="spec transmission"></span>
                <span class="spec seats"></span>
                <span class="spec doors"></span>
            </div>
            <form class="reservation-options-form">
                <input type="hidden" name="vehicleId" class="vehicle-id">
                <div class="options-list">
                    <?php foreach ($options as $option): ?>
                        <label class="option-item">
                            <input type="checkbox" name="options[]" 
                                   value="<?php echo $option['id']; ?>">
                            <i class="fas <?php echo $option['icon']; ?>"></i>
                            <?php echo htmlspecialchars($option['name']); ?> 
                            <span class="option-price">
                                (+<?php echo number_format($option['price_per_day'], 2); ?>€/jour)
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter au panier</button>
            </form>
        </div>
    </div>
</template>

<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>Véhicule ajouté au panier</h3>
        <p>Que souhaitez-vous faire ?</p>
        <div class="modal-buttons">
            <a href="index.php?page=cart" class="btn btn-primary">Voir le panier</a>
            <button class="btn btn-secondary close-modal">Continuer mes recherches</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const vehicleResults = document.getElementById('vehicleResults');
    const template = document.getElementById('vehicleTemplate');
    const successModal = document.getElementById('successModal');
    let startDate, endDate;

    // Validation des dates
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });

    // Recherche de véhicules
    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        startDate = startDateInput.value;
        endDate = endDateInput.value;
        
        const formData = new FormData(this);
        
        try {
            vehicleResults.innerHTML = '<div class="loading">Recherche des véhicules disponibles...</div>';
            
            const response = await fetch('api/search_vehicles.php', {
                method: 'POST',
                body: formData
            });
            
            const vehicles = await response.json();
            
            if (!vehicles.length) {
                vehicleResults.innerHTML = '<div class="no-results">Aucun véhicule disponible pour ces dates</div>';
                return;
            }

            vehicleResults.innerHTML = '';
            
            vehicles.forEach(vehicle => {
                const card = template.content.cloneNode(true);
                
                // Remplir les informations du véhicule
                card.querySelector('.vehicle-image').src = vehicle.image_url;
                card.querySelector('.vehicle-title').textContent = `${vehicle.brand} ${vehicle.model}`;
                card.querySelector('.vehicle-description').textContent = vehicle.description;
                card.querySelector('.vehicle-price').textContent = `${vehicle.price_per_day}€/jour`;
                card.querySelector('.vehicle-category').textContent = vehicle.category_name;
                
                // Spécifications
                card.querySelector('.fuel-type').textContent = vehicle.fuel_type;
                card.querySelector('.transmission').textContent = vehicle.transmission;
                card.querySelector('.seats').textContent = `${vehicle.seats} places`;
                card.querySelector('.doors').textContent = `${vehicle.doors} portes`;
                
                // ID du véhicule pour la réservation
                card.querySelector('.vehicle-id').value = vehicle.id;
                
                vehicleResults.appendChild(card);
            });

            // Gestionnaire pour les formulaires d'options
            document.querySelectorAll('.reservation-options-form').forEach(form => {
                form.addEventListener('submit', handleReservation);
            });

        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
            vehicleResults.innerHTML = '<div class="error">Une erreur est survenue lors de la recherche</div>';
        }
    });

    // Gestion des réservations
    async function handleReservation(e) {
        e.preventDefault();
        
        const form = e.target;
        const vehicleId = form.querySelector('.vehicle-id').value;
        const selectedOptions = Array.from(form.querySelectorAll('input[name="options[]"]:checked'))
            .map(input => parseInt(input.value));

        try {
            const response = await fetch('api/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    vehicleId: parseInt(vehicleId),
                    startDate,
                    endDate,
                    options: selectedOptions
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Mettre à jour le compteur du panier dans la navigation
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cartCount;
                }

                // Afficher le modal de succès
                successModal.style.display = 'block';
            } else {
                alert(data.message || 'Une erreur est survenue lors de l\'ajout au panier');
            }

        } catch (error) {
            console.error('Erreur lors de l\'ajout au panier :', error);
            alert('Une erreur est survenue lors de l\'ajout au panier');
        }
    }

    // Gestion du modal
    document.querySelector('.close-modal').addEventListener('click', () => {
        successModal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === successModal) {
            successModal.style.display = 'none';
        }
    });
});</script>
