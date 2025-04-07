<main class="home-page">
    <div class="hero">
        <div class="hero-content">
            <h1>Bienvenue chez The Genuis</h1>
            <p>Location de voitures de qualité à des prix compétitifs</p>
            <a href="index.php?page=reservation" class="btn btn-primary">Réservez maintenant</a>
        </div>
    </div>

    <div class="main-content">
        <section class="partnerships">
            <div class="section-header">
                <h2>Découvrez nos plus grands partenariats</h2>
                <p>une collaboration avec des marques de prestige pour vous offrir l'excellence.</p>
            </div>

            <!-- Swiper -->
            <div class="swiper-container categories-slider">
                <div class="swiper-wrapper">
                    <?php
                    $pdo = getPDO();
                    $stmt = $pdo->query("SELECT * FROM vehicle_categories");
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($categories as $category): ?>
                        <div class="swiper-slide">
                            <div class="category-card">
                                <div class="category-icon">
                                    <img src="<?php echo htmlspecialchars($category['image_url'] ?? 'assets/images/default-category.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($category['name']); ?>">
                                </div>
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                <p><?php echo htmlspecialchars($category['description']); ?></p>
                                <a href="index.php?page=reservation&category=<?php echo $category['id']; ?>" 
                                   class="btn btn-outline">Découvrir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Add Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
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
