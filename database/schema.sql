-- Create the database
CREATE DATABASE IF NOT EXISTS genuis_rental;
USE genuis_rental;

-- Vehicle categories table
CREATE TABLE vehicle_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Vehicles table
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    description TEXT,
    specifications TEXT,
    mileage INT,
    fuel_type ENUM('essence', 'diesel', 'électrique', 'hybride'),
    transmission ENUM('manuelle', 'automatique'),
    seats INT,
    doors INT,
    air_conditioning BOOLEAN DEFAULT true,
    image_url VARCHAR(255),
    gallery_images TEXT,
    is_available BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES vehicle_categories(id)
);

-- Options table
CREATE TABLE options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(10),
    country VARCHAR(50),
    driving_license VARCHAR(50),
    role ENUM('client', 'admin') DEFAULT 'client',
    is_active BOOLEAN DEFAULT true,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reservations table
CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    vehicle_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    return_time TIME NOT NULL,
    total_days INT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    options_price DECIMAL(10,2) DEFAULT 0,
    insurance_price DECIMAL(10,2) DEFAULT 0,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- Reservation options table
CREATE TABLE reservation_options (
    reservation_id INT,
    option_id INT,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES options(id),
    PRIMARY KEY (reservation_id, option_id)
);

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('card', 'paypal', 'bank_transfer'),
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    vehicle_id INT,
    reservation_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO vehicle_categories (name, description) VALUES
('SUV', 'Véhicules spacieux et polyvalents, parfaits pour les familles et les longs trajets'),
('Berline', 'Voitures élégantes et confortables, idéales pour les déplacements professionnels'),
('Sport', 'Véhicules performants et design, pour une expérience de conduite unique'),
('Citadine', 'Petites voitures agiles, parfaites pour la ville'),
('Utilitaire', 'Véhicules pratiques pour vos déménagements et transports');

INSERT INTO options (name, price_per_day, description, icon) VALUES
('GPS', 5.00, 'Système de navigation GPS intégré', 'fa-location-dot'),
('Siège bébé', 10.00, 'Siège auto homologué pour enfant', 'fa-baby'),
('Assurance complète', 15.00, 'Couverture tous risques avec assistance 24/7', 'fa-shield'),
('Chaînes neige', 8.00, 'Kit de chaînes pour conditions hivernales', 'fa-snowflake'),
('Wifi portable', 7.00, 'Connexion internet 4G dans votre véhicule', 'fa-wifi'),
('Second conducteur', 12.00, 'Ajout d\'un conducteur supplémentaire', 'fa-user-plus');

-- Create admin user
INSERT INTO users (first_name, last_name, email, password, role) VALUES
('Admin', 'System', 'admin@thegenuis.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
