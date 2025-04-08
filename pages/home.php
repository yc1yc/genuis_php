<main class="home-page">
    <div class="hero-container">
        <div class="hero-slider">
            <div class="slider-item active" data-index="0">
                <img src="/genuis_php/assets/images/cars/IMG_8149.JPG" alt="Voiture de luxe">
                <div class="slider-overlay">
                    <div class="slider-content">
                        <h1>Location de Voitures de Luxe</h1>
                        <p>Vivez une expérience de conduite exceptionnelle</p>
                        <a href="?page=reservation" class="btn btn-primary">Réserver Maintenant</a>
                    </div>
                </div>
            </div>
            <div class="slider-item" data-index="1">
                <img src="/genuis_php/assets/images/cars/IMG_8241.JPG" alt="Voiture sportive">
                <div class="slider-overlay">
                    <div class="slider-content">
                        <h1>Performance et Élégance</h1>
                        <p>Choisissez parmi notre flotte de voitures d'exception</p>
                        <a href="?page=vehicles" class="btn btn-primary">Découvrir nos Véhicules</a>
                    </div>
                </div>
            </div>
            <div class="slider-item" data-index="2">
                <img src="/genuis_php/assets/images/cars/IMG_8213.JPG" alt="Voiture élégante">
                <div class="slider-overlay">
                    <div class="slider-content">
                        <h1>Votre Voyage Commence Ici</h1>
                        <p>Réservation simple et rapide</p>
                        <a href="?page=reservation" class="btn btn-primary">Commencer</a>
                    </div>
                </div>
            </div>
            <div class="slider-controls">
                <button class="slider-prev" aria-label="Slide précédente">&#10094;</button>
                <button class="slider-next" aria-label="Slide suivante">&#10095;</button>
            </div>
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
            <div class="video-container">
            <video id="myVideo" src="<?php echo $baseUrl; ?>/assets/videos/Videos voiture.mp4"></video>
            <div class="overlay">
                <button class="play-button" onclick="playVideo()">
                    <svg viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="26px">
                        <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" fill="currentColor"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="main-container">
            <div class="left-content">
                <div class="text-wrapper">
                    <h2>Un Service Sur-Mesure</h2>
                    <p>Notre priorité est de vous offrir une expérience de location sans souci. C'est pourquoi nous proposons un service entièrement personnalisé, adapté à vos besoins et à vos préférences. Que vous souhaitiez une livraison à domicile ou à votre hôtel, un chauffeur privé ou un véhicule avec des options spécifiques, nous sommes à votre disposition pour répondre à toutes vos demandes. La satisfaction de nos clients est notre moteur, et nous nous engageons à vous offrir un service haut de gamme à chaque étape de votre location.</p>
                    
                    <div class="icon-group">
                        <div class="icon-wrapper">
                            <div class="icon-bg">
                                <img src="<?php echo $baseUrl; ?>/assets/images/helping-hand.png" alt="">
                            </div>
                            <div class="icon-description">
                                <h3>Joignez vous à nous</h3>
                                <p>Découvrez une nouvelle façon<br>de vivre le luxe.</p>
                            </div>
                        </div>
                        
                        <div class="icon-wrapper">
                            <div class="icon-bg">
                                <img src="<?php echo $baseUrl; ?>/assets/images/2a986cfadabc8284e63c8877bff098e9.png" alt="">
                            </div>
                            <div class="icon-description">
                                <h3>Venez goûter au luxe, vivez l'excellence.</h3>
                                <p>la performance et le confort inégalé <br>de nos voitures de prestige.</p>
                            </div>
                        </div>
                    </div>
                    
                    <button class="donate-button">
                        Cliquez ici
                    </button>
                </div>
            </div>
            
            <div class="right-section">
                <div class="large-square">
                    <div class="small-square top-left-corner"></div>
                    <div class="small-square top-right-corner"></div>
                    <div class="small-square bottom-left-corner"></div>
                    <div class="small-square bottom-right-corner"></div>

                    <div class="overlay-card">
                        <div class="photo"><img src="<?php echo $baseUrl; ?>/assets/images/cars/design.jpg" alt="Photo 3"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="compartment">
            <div class="image-left">
                <div class="overlay">
                    <h1>NOTRE EQUIPE</h1>
                    <p class="large-text">Est à votre disposition pour répondre à toutes vos demandes et vous offrir un service personnalisé. </p>
                    <p class="small-text">Que vous ayez des questions sur nos véhicules, sur nos options ou pour toute autre information, nous sommes là pour vous accompagner à chaque étape de votre expérience de location. Votre satisfaction est notre priorité.</p>
                    
                    <button class="join">
                        Contacter nous
                    </button>
                </div>
            </div>
            <div class="image-right">
                <!-- Image ajoutée via CSS -->
            </div>
        </div>

        <div class="compartment1">
  <div class="image-container1">
      <img src="<?php echo $baseUrl; ?>/assets/images/location-symbol-with-building.jpg" alt="Image de fond">
      <div class="overlay1">
          <h2>Decouvrez<br>les différents endroits<br>où vous trouverez <br>Genius rent a car</h2>
          <p>Que ce soit dans les grandes villes, <br>près des aéroports ou dans des destinations<br> de luxe, notre service de location de voitures <br> de prestige est toujours à votre portée,<br>où que vous soyez.</p>
      </div>
      <!-- Carte superposée et deux cartes supplémentaires -->
      <div class="card1 card11">
          <img src="<?php echo $baseUrl; ?>/assets/images/beautiful-landscape-shot-metro-station-paris-cloudy-day_181624-28829.jpg" alt="Photo 1">
          <h3>Gare de Lyon - Paris</h3>
          <p><span class="highlight">Adresse : <br> </span> <br>207 Rue de Bercy, 75012 Paris, France</p>
      </div>
      <div class="card1 card21">
          <img src="<?php echo $baseUrl; ?>/assets/images/traveling-by-train-lifestyle_23-2150578039.jpg" alt="Photo 2">
          <h3>Gare Saint-Charles - Marseille</h3>
          <p><span class="highlight">Adresse :<br> </span> <br>Gare Saint-Charles, 13001 Marseille, France</p>
      </div>
      <div class="card1 card31">
          <img src="<?php echo $baseUrl; ?>/assets/images/building_1127-3357.jpg" alt="Photo 3">
          <h3>Gare de Lille Flandres - Lille</h3>
          <p><span class="highlight">Adresse :<br></span> <br>Place des Buisses, 59800 Lille, France</p>
      </div>
      
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

       

    </div>
</main>

<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/hero-slider.css">
<script src="<?php echo $baseUrl; ?>/assets/js/video.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/js/hero-slider.js"></script>
</body>
