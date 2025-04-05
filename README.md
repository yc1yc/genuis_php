# The Genuis - Location de voitures en ligne

> Développé dans le cadre du cours de **Web Dynamique** à l'Université Roi Henry Christophe  
> Encadré par le professeur **Jole Wilmore**  
> **Date de livraison : 6 avril 2025 à 16h**

---

## Présentation

The Genuis est une application web dynamique de location de voitures qui offre une expérience utilisateur moderne et intuitive. Le site permet aux clients de réserver facilement des véhicules en ligne, avec un système de réservation en temps réel et une interface d'administration complète.

---

## Fonctionnalités principales

### Interface Client (Front-end)

#### Pages principales
- **Accueil** : Présentation de l'entreprise et des catégories de véhicules
- **Réservation** : Système de recherche et réservation de véhicules
- **À propos** : Histoire et valeurs de l'entreprise
- **Contact** : Formulaire de contact et informations pratiques

#### Système de réservation
- Calendrier dynamique pour la sélection des dates
- Filtres de recherche avancés (catégorie, prix, disponibilité)
- Sélection d'options supplémentaires (GPS, siège bébé, etc.)
- Calcul automatique des tarifs
- Système de panier
- Paiement sécurisé
- Confirmation par email

### Interface Administration (Back-end)
- Tableau de bord avec statistiques
- Gestion des réservations
- Gestion des véhicules et catégories
- Gestion des clients
- Rapports et analyses

---

## Technologies utilisées

### Front-end
- HTML5 / CSS3
- JavaScript (Vanilla)
- Responsive design
- Animations fluides
- Interface mobile-first

### Back-end
- PHP 8.x
- MySQL 8.x
- Architecture MVC
- API RESTful pour les interactions dynamiques

### Librairies et dépendances
- Font Awesome pour les icônes
- Inter (Google Fonts) pour la typographie
- FullCalendar pour le calendrier de réservation
- PHPMailer pour l'envoi d'emails

---

## Installation

1. Cloner le repository :
```bash
git clone https://github.com/yc1yc/genuis_php.git
```


## Structure de la base de données

- `users` : Informations des utilisateurs
- `vehicles` : Catalogue des véhicules
- `categories` : Catégories de véhicules
- `reservations` : Réservations des clients
- `options` : Options supplémentaires
- `payments` : Historique des paiements

---

## Sécurité

- Protection contre les injections SQL
- Validation des données
- Authentification sécurisée
- Sessions chiffrées
- Protection CSRF
- Sanitization des entrées utilisateur

---

## Responsive Design

Le site est entièrement responsive avec une approche mobile-first :
- Smartphones (< 768px)
- Tablettes (768px - 1024px)
- Desktop (> 1024px)

---

## Performances

- Optimisation des images
- Minification des assets
- Mise en cache
- Chargement différé (lazy loading)
- Temps de chargement < 3s

---

### ***À faire*** :
### ***Système de réservation*** :
- Finaliser le processus de réservation
- Ajouter le panier
- Intégrer le système de paiement
- Ajouter les emails de confirmation
### ***Authentification*** :
- Système de connexion/inscription
- Espace client
- Interface d'administration
### ***Gestion des véhicules*** :
- Upload d'images
- Galerie de photos
- Filtres avancés
### ***Pages à compléter*** :
- À propos
- Contact avec formulaire
- Conditions générales
- Politique de confidentialité
### ***Améliorations UI/UX*** :
- Animations
- Responsive design
- Messages d'erreur/succès
- Chargements asynchrones
### ***Sécurité*** :
- Validation des formulaires
- Protection CSRF
- Sanitization des données
- Gestion des sessions
### ***Performance*** :
- Optimisation des images
- Mise en cache
- Minification des assets
### ***Tests*** :
- Tests unitaires
- Tests d'intégration
- Tests de sécurité
