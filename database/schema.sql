-- Create the database
CREATE DATABASE IF NOT EXISTS genuis_rental;
USE genuis_rental;

-- Vehicle categories table
CREATE TABLE vehicle_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT
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
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT true,
    FOREIGN KEY (category_id) REFERENCES vehicle_categories(id)
);

-- Options table
CREATE TABLE options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    description TEXT
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reservations table
CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    vehicle_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- Reservation options table
CREATE TABLE reservation_options (
    reservation_id INT,
    option_id INT,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id),
    FOREIGN KEY (option_id) REFERENCES options(id),
    PRIMARY KEY (reservation_id, option_id)
);

-- Insert some sample data
INSERT INTO vehicle_categories (name, description) VALUES
('SUV', 'Véhicules spacieux et polyvalents'),
('Berline', 'Voitures confortables pour la ville'),
('Sport', 'Véhicules performants et élégants');

INSERT INTO options (name, price_per_day, description) VALUES
('GPS', 5.00, 'Système de navigation GPS'),
('Siège bébé', 10.00, 'Siège auto pour bébé'),
('Assurance complète', 15.00, 'Couverture complète des dommages');
