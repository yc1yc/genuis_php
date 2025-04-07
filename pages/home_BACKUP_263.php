<main class="home-page">
    <div class="hero">
        <div class="hero-content">
            <h1>Bienvenue chez The Genuis</h1>
            <p>Location de voitures de qualité à des prix compétitifs</p>
            <a href="?page=vehicles" class="btn btn-primary">Réservez maintenant</a>
        </div>
    </div>

    <div class="main-content">
        <section class="partnerships">
            <div class="section-header">
                <h2>Découvrez nos plus grands<br>partenariats</h2>
                <p>une collaboration avec des marques de prestige pour vous offrir<br>l'excellence.</p>
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

        
            <div class="containerr">
                <div class="lef">
                    <div class="grand-carre">
                        <div class="petit-carre top-left"></div>
                        <div class="petit-carre top-right"></div>
                        <div class="petit-carre bottom-left"></div>
                        <div class="petit-carre bottom-right"></div>

                        <!-- Carte superposée contenant les photos -->
                        <div class="cardd">
                            <?php $baseUrl = '/genuis_php'; ?>
                            <div class="photo"><img src="<?php echo $baseUrl; ?>/assets/images/cars/IMG_8238.JPG" alt="Photo 1"></div>
                            <div class="photo"><img src="<?php echo $baseUrl; ?>/assets/images/cars/IMG_8241.JPG" alt="Photo 2"></div>
                            <div class="photo"><img src="<?php echo $baseUrl; ?>/assets/images/cars/IMG_8213.JPG" alt="Photo 3"></div>
                            <div class="photo"><img src="<?php echo $baseUrl; ?>/assets/images/cars/IMG_8214.JPG" alt="Photo 4"></div>
                        </div>
                    </div>
                </div>
                <div class="right">
                    <!-- Conteneur pour le texte -->
                    <div class="text-containerr">
                        <h2>Nos voiture de luxe<br>fonts des merveilles</h2>
                        <p>Explorez notre gamme de voitures de luxe, comprenant les modèles les plus prestigieux et les plus désirés. Chaque voiture que nous proposons allie performance, confort et design, pour transformer chaque trajet en un moment d'exception.</p>
                        <p>De la sportive italienne à la berline élégante, nos véhicules sont parfaitement entretenus et prêts à vous offrir une conduite sans égale. Parcourez nos offres et trouvez la voiture qui fera de votre prochain déplacement une expérience inoubliable.</p>
                    </div>
                    <button class="btn-donatee" onclick="window.location.href='?page=vehicles&category=luxury'">
                        Voir nos voitures
                    </button>
                </div>
            </div>
        

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

        <section class="features">
            <h2>Pourquoi choisir The Genuis ?</h2>
        </section>

        <div class="video-container">
            <video id="myVideo" src="videos/Videos voiture.mp4"></video>
            <div class="overlay">
                <button class="play-button" onclick="playVideo()">
                    <svg viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="26px">
                        <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" fill="currentColor"></path>
                    </svg>
                </button>
            </div>
        </div>

    </div>
</main>

<script src="<?php echo $baseUrl; ?>/assets/js/video.js"></script>
</body>
