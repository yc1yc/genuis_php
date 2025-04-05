<?php
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
            <div class="vehicle-options">
                <h4>Options disponibles</h4>
                <div class="options-list">
                    <?php foreach ($options as $option): ?>
                        <label class="option-item">
                            <input type="checkbox" name="options[]" 
                                   value="<?php echo $option['id']; ?>">
                            <?php echo htmlspecialchars($option['name']); ?> 
                            (+<?php echo number_format($option['price_per_day'], 2); ?>€/jour)
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="btn btn-primary reserve-btn">Réserver</button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const vehicleResults = document.getElementById('vehicleResults');
    const template = document.getElementById('vehicleTemplate');

    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        try {
            const response = await fetch('api/search_vehicles.php', {
                method: 'POST',
                body: formData
            });
            const vehicles = await response.json();
            
            vehicleResults.innerHTML = '';
            vehicles.forEach(vehicle => {
                const clone = template.content.cloneNode(true);
                
                // Remplir les données du véhicule
                clone.querySelector('.vehicle-image').src = vehicle.image_url;
                clone.querySelector('.vehicle-title').textContent = `${vehicle.brand} ${vehicle.model}`;
                clone.querySelector('.vehicle-description').textContent = vehicle.description;
                clone.querySelector('.vehicle-price').textContent = `${vehicle.price_per_day}€/jour`;
                clone.querySelector('.vehicle-category').textContent = vehicle.category_name;
                
                // Ajouter au DOM
                vehicleResults.appendChild(clone);
            });
        } catch (error) {
            console.error('Erreur lors de la recherche:', error);
        }
    });
});
</script>
