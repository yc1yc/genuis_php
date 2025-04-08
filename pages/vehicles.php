<?php
require_once __DIR__ . '/../includes/auth.php';

// Vérifier que l'utilisateur est connecté
requireAuth();

try {
    $pdo = getPDO();
    
    // Récupérer les dates si elles sont fournies
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    
    // Requête de base pour les véhicules
    if ($start_date && $end_date) {
        // Requête pour trouver les véhicules disponibles pour les dates sélectionnées
        $query = "
            SELECT v.*,
                   (SELECT COUNT(*) FROM reservations r 
                    WHERE r.vehicle_id = v.id 
                    AND r.status = 'confirmed'
                    AND NOT (r.end_date < :start_date OR r.start_date > :end_date)) as has_conflict
            FROM vehicles v
            ORDER BY v.brand, v.model
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        
        // Filtrer les véhicules disponibles
        $all_vehicles = $stmt->fetchAll();
        $vehicles = array_filter($all_vehicles, function($v) {
            return $v['has_conflict'] == 0;
        });
        
        if (empty($vehicles)) {
            $_SESSION['flash_warning'] = "Aucun véhicule n'est disponible pour les dates sélectionnées.";
            // Récupérer tous les véhicules pour affichage
            $query = "SELECT v.* FROM vehicles v ORDER BY v.brand, v.model";
            $stmt = $pdo->query($query);
            $vehicles = $stmt->fetchAll();
        }
    } else {
        // Sans dates, afficher tous les véhicules
        $query = "SELECT v.* FROM vehicles v ORDER BY v.brand, v.model";
        $stmt = $pdo->query($query);
        $vehicles = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Erreur lors de la récupération des véhicules.";
    $vehicles = [];
}
?>

<div class="vehicles-container">
    <div class="vehicles-header">
        <h1><i class="fas fa-car"></i> Nos véhicules</h1>
    </div>



    <div class="vehicles-filters">
        <div class="form-row">
            <div class="col-md-6">
                <input type="text" id="searchVehicles" class="form-control" placeholder="Rechercher un véhicule...">
            </div>
            <div class="col-md-6">
                <select id="filterBrand" class="form-control">
                    <option value="">Toutes les marques</option>
                    <?php
                    $brands = array_unique(array_column($vehicles, 'brand'));
                    foreach ($brands as $brand) {
                        echo '<option value="' . htmlspecialchars($brand) . '">' . htmlspecialchars($brand) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <?php echo displayFlashMessages(); ?>

    <div class="vehicles-grid">
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="vehicle-card" data-brand="<?php echo htmlspecialchars($vehicle['brand']); ?>">
                <div class="vehicle-image">
                    <?php if (!empty($vehicle['image_url'])): ?>
                        <img src="/<?php echo ltrim(htmlspecialchars($vehicle['image_url']), './'); ?>" alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>">
                    <?php else: ?>
                        <div class="vehicle-image-placeholder">
                            <i class="fas fa-car"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="vehicle-details">
                    <h3>
                        <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>
                        <span class="vehicle-year"><?php echo htmlspecialchars($vehicle['year']); ?></span>
                    </h3>
                    
                    <div class="vehicle-info">
                        <div class="info-row">
                            <span class="label"><i class="fas fa-tag"></i> Prix/jour</span>
                            <span class="value"><?php echo number_format($vehicle['price_per_day'], 2, ',', ' '); ?> €</span>
                        </div>
                    </div>
                    <div class="vehicle-availability">
                        <?php if (isset($_GET['start_date']) && isset($_GET['end_date'])): ?>
                            <?php if (!isset($vehicle['has_conflict']) || $vehicle['has_conflict'] == 0): ?>
                                <p class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    Disponible pour ces dates
                                </p>
                            <?php else: ?>
                                <p class="text-danger">
                                    <i class="fas fa-times-circle"></i>
                                    Non disponible pour ces dates
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-info">
                                <i class="fas fa-info-circle"></i>
                                Sélectionnez des dates pour vérifier la disponibilité
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="vehicle-actions" id="actions-<?php echo $vehicle['id']; ?>">
                        <button type="button" class="btn btn-outline-primary" onclick="openModal('vehicleDetails<?php echo $vehicle['id']; ?>')">
                            <i class="fas fa-info-circle"></i> Détails
                        </button>
                        <button type="button" class="btn btn-primary" onclick="openModal('reservationModal<?php echo $vehicle['id']; ?>')">
                            <i class="fas fa-calendar-plus"></i> Réserver
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal de réservation -->
            <div class="modal" id="reservationModal<?php echo $vehicle['id']; ?>">
                <div class="modal-wrapper">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Réserver <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h5>
                            <button type="button" class="modal-close" onclick="closeModal('reservationModal<?php echo $vehicle['id']; ?>')">×</button>
                        </div>
                        <div class="modal-body">
                            <?php if (isLoggedIn()): ?>
                                <form onsubmit="submitReservation(event, <?php echo $vehicle['id']; ?>)" class="reservation-form" id="reservationForm<?php echo $vehicle['id']; ?>">
                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                    <?php echo csrfField(); ?>
                                    
                                    <div class="form-group">
                                        <label for="startDate<?php echo $vehicle['id']; ?>">Date de début</label>
                                        <input type="date" id="startDate<?php echo $vehicle['id']; ?>" 
                                               name="start_date" class="form-control start-date" required 
                                               min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="endDate<?php echo $vehicle['id']; ?>">Date de fin</label>
                                        <input type="date" id="endDate<?php echo $vehicle['id']; ?>" 
                                               name="end_date" class="form-control end-date" required 
                                               min="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div id="availability<?php echo $vehicle['id']; ?>" class="alert" style="display: none;"></div>

                                    <div class="text-right">
                                        <button type="button" class="btn btn-secondary" onclick="closeModal('reservationModal<?php echo $vehicle['id']; ?>')">Annuler</button>
                                        <button type="submit" class="btn btn-primary">
                                            Confirmer la réservation
                                        </button>
                                        <p class="mt-2">
                                            <a href="?page=reservations">Voir mes réservations</a>
                                        </p>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <p>Vous devez être connecté pour effectuer une réservation.</p>
                                    <a href="?page=login" class="btn btn-primary">Se connecter</a>
                                    <a href="?page=register" class="btn btn-outline-primary">S'inscrire</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal des détails -->
            <div class="modal" id="vehicleDetails<?php echo $vehicle['id']; ?>">
                <div class="modal-wrapper">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']); ?>
                            </h5>
                            <button type="button" class="modal-close" onclick="closeModal('vehicleDetails<?php echo $vehicle['id']; ?>')">×</button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($vehicle['image_url'])): ?>
                                <img src="/<?php echo ltrim(htmlspecialchars($vehicle['image_url']), './'); ?>" 
                                     alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>"
                                     class="modal-image">
                            <?php endif; ?>
                            
                            <div class="details-info">
                                <div class="info-section">
                                    <h6>Caractéristiques techniques</h6>
                                    <ul>
                                    <li><strong>Marque :</strong> <?php echo htmlspecialchars($vehicle['brand']); ?></li>
                                    <li><strong>Modèle :</strong> <?php echo htmlspecialchars($vehicle['model']); ?></li>
                                    <li><strong>Année :</strong> <?php echo htmlspecialchars($vehicle['year']); ?></li>
                                    <li><strong>Transmission :</strong> <?php echo htmlspecialchars($vehicle['transmission']); ?></li>
                                    <li><strong>Carburant :</strong> <?php echo htmlspecialchars($vehicle['fuel_type']); ?></li>
                                    <li><strong>Places :</strong> <?php echo htmlspecialchars($vehicle['seats']); ?></li>
                                    <li><strong>Prix par jour :</strong> <?php echo number_format($vehicle['price_per_day'], 2, ',', ' '); ?> €</li>
                                    </ul>
                                </div>
                                
                                <?php if (!empty($vehicle['description'])): ?>
                                <div class="info-section">
                                    <h6>Description</h6>
                                    <p><?php echo nl2br(htmlspecialchars($vehicle['description'])); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('vehicleDetails<?php echo $vehicle['id']; ?>')">Fermer</button>
                            <button type="button" class="btn btn-primary" onclick="openReservationModal('reservationModal<?php echo $vehicle['id']; ?>')">Réserver</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchVehicles');
    const brandFilter = document.getElementById('filterBrand');
    const vehicleCards = document.querySelectorAll('.vehicle-card');

    // Gestionnaire pour tous les champs de date de début
    document.querySelectorAll('.start-date').forEach(startDate => {
        startDate.addEventListener('change', function() {
            const vehicleId = this.id.replace('startDate', '');
            const endDate = document.getElementById('endDate' + vehicleId);
            endDate.min = this.value;
            if (endDate.value && endDate.value < this.value) {
                endDate.value = this.value;
            }
        });
    });

    // Gestionnaire pour les boutons de vérification de disponibilité
    document.querySelectorAll('.check-availability').forEach(button => {
        button.addEventListener('click', function() {
            const vehicleId = this.dataset.vehicleId;
            const form = document.getElementById('reservationForm' + vehicleId);
            const startDate = form.querySelector('.start-date').value;
            const endDate = form.querySelector('.end-date').value;
            const availabilityDiv = document.getElementById('availability' + vehicleId);
            const confirmButton = form.querySelector('.confirm-reservation');

            if (!startDate || !endDate) {
                availabilityDiv.className = 'alert alert-warning';
                availabilityDiv.style.display = 'block';
                availabilityDiv.textContent = 'Veuillez sélectionner les dates de début et de fin.';
                confirmButton.style.display = 'none';
                return;
            }

            // Appel AJAX pour vérifier la disponibilité
            fetch(`check_availability.php?vehicle_id=${vehicleId}&start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    availabilityDiv.style.display = 'block';
                    if (data.available) {
                        availabilityDiv.className = 'alert alert-success';
                        availabilityDiv.innerHTML = '<i class="fas fa-check-circle"></i> Ce véhicule est disponible pour ces dates!';
                        confirmButton.style.display = 'inline-block';
                    } else {
                        availabilityDiv.className = 'alert alert-danger';
                        availabilityDiv.innerHTML = '<i class="fas fa-times-circle"></i> Désolé, ce véhicule n\'est pas disponible pour ces dates.';
                        confirmButton.style.display = 'none';
                    }
                })
                .catch(error => {
                    availabilityDiv.className = 'alert alert-danger';
                    availabilityDiv.style.display = 'block';
                    availabilityDiv.textContent = 'Une erreur est survenue lors de la vérification de la disponibilité.';
                    confirmButton.style.display = 'none';
                });
        });
    });

    // Filtrage des véhicules
    function filterVehicles() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedBrand = brandFilter.value.toLowerCase();

        vehicleCards.forEach(card => {
            const brand = card.dataset.brand.toLowerCase();
            const vehicleText = card.textContent.toLowerCase();
            const matchesSearch = vehicleText.includes(searchTerm);
            const matchesBrand = !selectedBrand || brand === selectedBrand;

            card.style.display = matchesSearch && matchesBrand ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('input', filterVehicles);
    brandFilter.addEventListener('change', filterVehicles);
});

function submitReservation(event, vehicleId) {
    event.preventDefault();
    const form = document.getElementById('reservationForm' + vehicleId);
    const formData = new FormData(form);

    // Afficher un message de chargement
    const alertDiv = document.getElementById('availability' + vehicleId);
    alertDiv.className = 'alert alert-info';
    alertDiv.textContent = 'Traitement de la réservation...';
    alertDiv.style.display = 'block';

    fetch('/genuis_php/api/reservation.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Réponse texte:', text);
                throw new Error('Erreur de parsing JSON: ' + text);
            }
        });
    })
    .then(data => {
        console.log('Réponse du serveur:', data);
        if (data.success) {
            // Réinitialiser le formulaire
            form.reset();
            // Fermer le modal
            closeModal('reservationModal' + vehicleId);
            // Afficher un message de succès
            const successDiv = document.createElement('div');
            successDiv.className = 'alert alert-success';
            successDiv.textContent = data.message || 'Réservation effectuée avec succès!';
            document.querySelector('.vehicles-container').insertBefore(successDiv, document.querySelector('.vehicles-grid'));
            setTimeout(() => successDiv.remove(), 5000);
        } else {
            // Afficher l'erreur dans le formulaire
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = data.error || 'Une erreur est survenue';
            alertDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Erreur complète:', error);
        // Afficher une erreur générique
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = error.message || 'Une erreur est survenue lors de la réservation';
        alertDiv.style.display = 'block';
    });
}
</script>
