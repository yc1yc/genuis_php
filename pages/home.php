<main class="home-page">
    <div class="hero">
        <div class="hero-content">
            <h1>Bienvenue chez The Genuis</h1>
            <p>Location de voitures de qualité à des prix compétitifs</p>
            <a href="index.php?page=reservation" class="btn btn-primary">Réservez maintenant</a>
        </div>
    </div>

    <div class="main-content">
        <section class="categories">
            <h2>Nos catégories de véhicules</h2>
            <?php
            $stmt = $pdo->query("SELECT * FROM vehicle_categories");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div class="vehicle-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="vehicle-card">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="index.php?page=reservation&category=<?php echo $category['id']; ?>" 
                           class="btn btn-primary">Voir les véhicules</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="features">
            <h2>Pourquoi choisir The Genuis ?</h2>
            <div class="features-grid">
                <div class="feature">
                    <h3>Large gamme de véhicules</h3>
                    <p>Du SUV familial à la berline élégante, nous avons le véhicule parfait pour vous.</p>
                </div>
                <div class="feature">
                    <h3>Prix compétitifs</h3>
                    <p>Des tarifs transparents et avantageux pour tous vos besoins de location.</p>
                </div>
                <div class="feature">
                    <h3>Service client 24/7</h3>
                    <p>Notre équipe est disponible à tout moment pour vous assister.</p>
                </div>
            </div>
        </section>
    </div>
</main>
