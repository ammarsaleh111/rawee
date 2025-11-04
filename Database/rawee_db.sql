-- SQL Script for RAWEE Website Database
-- Database: rawee_db
-- Author: Gemini
-- Generation Time: Sep 19, 2025

-- Create the database if it doesn't already exist
CREATE DATABASE IF NOT EXISTS `rawee_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rawee_db`;

--
-- Table structure for `user_roles`
-- Description: Stores user roles (e.g., Administrator, Customer).
--
CREATE TABLE `user_roles` (
  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

--
-- Table structure for `users`
-- Description: Stores user account information for authentication and profile management.
--
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Store hashed passwords only (e.g., using bcrypt)',
  `role_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `user_roles`(`role_id`)
) ENGINE=InnoDB;

--
-- Table structure for `categories`
-- Description: Stores product categories to organize different types of solutions.
--
CREATE TABLE `categories` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL-friendly identifier for the category'
) ENGINE=InnoDB;

--
-- Table structure for `products`
-- Description: Stores all product details as seen in the product catalog.
--
CREATE TABLE `products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL,
  `category_id` INT,
  `image_url` VARCHAR(512),
  `in_stock` BOOLEAN DEFAULT TRUE,
  `rating` DECIMAL(2, 1) DEFAULT 0.0,
  `is_new` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category_id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

--
-- Table structure for `features`
-- Description: Stores unique product features to be linked to products.
--
CREATE TABLE `features` (
  `feature_id` INT AUTO_INCREMENT PRIMARY KEY,
  `feature_name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

--
-- Table structure for `product_features`
-- Description: A junction table to create a many-to-many relationship between products and features.
--
CREATE TABLE `product_features` (
  `product_id` INT NOT NULL,
  `feature_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `feature_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
  FOREIGN KEY (`feature_id`) REFERENCES `features`(`feature_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

--
-- Table structure for `orders`
-- Description: Stores customer order history.
--
CREATE TABLE `orders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Processing',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

--
-- Table structure for `order_items`
-- Description: Details the products contained within each order.
--
CREATE TABLE `order_items` (
  `order_item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price_at_purchase` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`)
) ENGINE=InnoDB;

--
-- Table structure for `carts`
-- Description: Represents a user's shopping cart. Each user has one cart.
--
CREATE TABLE `carts` (
  `cart_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

--
-- Table structure for `cart_items`
-- Description: Stores the products a user has added to their shopping cart.
--
CREATE TABLE `cart_items` (
  `cart_item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`cart_id`) REFERENCES `carts`(`cart_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_cart_product` (`cart_id`, `product_id`)
) ENGINE=InnoDB;

--
-- Table structure for `contact_messages`
-- Description: Stores submissions from the contact and custom quote forms.
--
CREATE TABLE `contact_messages` (
  `message_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255),
  `phone` VARCHAR(50),
  `subject` VARCHAR(255),
  `message` TEXT NOT NULL,
  `farm_size` VARCHAR(100),
  `solution_type` VARCHAR(100),
  `status` ENUM('New', 'Read', 'Archived') DEFAULT 'New',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ================================================================= --
-- =================== POPULATING TABLES WITH DATA ================= --
-- ================================================================= --

--
-- Inserting data for `user_roles`
--
INSERT INTO `user_roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Customer');

--
-- Inserting sample users
-- NOTE: Passwords are fake hashes. In a real application, use a library like bcrypt to generate these.
--
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role_id`) VALUES
('Admin User', 'admin@rawee.tech', '$2y$10$fA.5.kC3qF6zF8J.wE5w/eJ.Z.E5q.p9H.aK5sB1xI9oB7w', 1),
('Alex Johnson', 'alex.j@example.com', '$2y$10$gB.7.jD4rG7zG9K.xH6x/fK.A.F6r.q0I.bL6tC2yJ0pC8x', 2);

--
-- Inserting product categories
--
INSERT INTO `categories` (`category_name`, `slug`) VALUES
('Aquaculture', 'aquaculture'),
('Hydroponics', 'hydroponics'),
('Greenhouse', 'greenhouse'),
('Field Crops', 'field_crops');

--
-- Inserting all 16 products from product.js
--
INSERT INTO `products` (`product_id`, `name`, `price`, `description`, `category_id`, `image_url`, `in_stock`, `rating`, `is_new`) VALUES
(1, 'AquaSense Pro', 350, 'Advanced water quality monitoring system for aquaculture with real-time data and alerts.', 1, 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.8, 1),
(2, 'HydroGrow Smart', 480, 'Automated hydroponics nutrient delivery and climate control system for optimal plant growth.', 2, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.9, 0),
(3, 'GreenHouse Guardian', 620, 'Comprehensive greenhouse monitoring and automation for temperature, humidity, and light.', 3, 'https://images.unsplash.com/photo-1533635700202-535359149454?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 0, 4.7, 1),
(4, 'FieldSense Pro', 290, 'Wireless soil sensor network for real-time moisture, nutrient, and temperature data in field crops.', 4, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.6, 0),
(5, 'FishFarm Monitor', 250, 'Entry-level water quality monitor for small-scale aquaculture operations with basic alerts.', 1, 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.5, 0),
(6, 'NutriFlow Hydro', 550, 'Precision nutrient dosing system for advanced hydroponic setups, ensuring optimal plant nutrition.', 2, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.9, 1),
(7, 'ClimateMaster Pro', 780, 'Integrated climate control system for large greenhouses with automated shading and irrigation.', 3, 'https://images.unsplash.com/photo-1533635700202-535359149454?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 0, 4.8, 1),
(8, 'CropHealth Scout', 380, 'Portable sensor for quick assessment of crop health and disease detection in the field.', 4, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.4, 0),
(9, 'SmartPond Ecosystem', 890, 'Complete IoT ecosystem for aquaculture ponds, including feeding, aeration, and water quality.', 1, 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.9, 1),
(10, 'VerticalFarm Pro', 950, 'Integrated IoT solution for vertical farms, managing light, nutrients, and environmental factors.', 2, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.7, 0),
(11, 'EcoDome Smart', 700, 'Energy-efficient greenhouse automation with predictive analytics for optimal crop cycles.', 3, 'https://images.unsplash.com/photo-1533635700202-535359149454?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.8, 0),
(12, 'Irrigation IQ', 420, 'Smart irrigation system for field crops, optimizing water usage based on real-time soil data.', 4, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 0, 4.6, 1),
(13, 'MiniAqua Monitor', 180, 'Compact and affordable water quality monitor for small fish tanks and aquaponics systems.', 1, 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.2, 0),
(14, 'NutrientMix Basic', 300, 'Simple nutrient mixing and delivery system for beginner hydroponic growers.', 2, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.3, 0),
(15, 'GrowLight Smart', 450, 'Smart LED grow light system for greenhouses with adjustable spectrum and scheduling.', 3, 'https://images.unsplash.com/photo-1533635700202-535359149454?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.4, 0),
(16, 'PestDetect Sensor', 200, 'IoT sensor for early detection of pests and diseases in field crops, sending instant alerts.', 4, 'https://images.unsplash.com/photo-1518531932812-ce1266282635?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 1, 4.1, 0);

--
-- Inserting unique features
--
INSERT INTO `features` (`feature_name`) VALUES
('pH Sensor'), ('Temp Monitor'), ('Oxygen Levels'), ('Nutrient Dosing'), ('Automated pH'), ('Climate Control'), ('Temp/Humidity'), ('Light Control'), ('Ventilation'), ('Soil Moisture'), ('Nutrient Levels'), ('Temp Sensing'), ('Basic pH'), ('Water Temp'), ('Alerts'), ('Automated Dosing'), ('EC/TDS Control'), ('Remote Access'), ('Auto Shading'), ('Smart Irrigation'), ('Air Circulation'), ('Disease Detection'), ('Nutrient Scan'), ('Portable'), ('Auto Feeder'), ('Aeration Control'), ('Full Monitoring'), ('Light Spectrum'), ('Stackable'), ('High Yield'), ('Energy Saving'), ('Predictive AI'), ('Automated Vents'), ('Water Optimization'), ('Zone Control'), ('Weather Integration'), ('Compact'), ('Affordable'), ('Basic Monitoring'), ('Easy Setup'), ('Manual Control'), ('Small Scale'), ('LED Lighting'), ('Spectrum Control'), ('Timer'), ('Early Warning'), ('Pest ID'), ('Wireless');

--
-- Linking products to their features
--
INSERT INTO `product_features` (`product_id`, `feature_id`) VALUES
(1, 1), (1, 2), (1, 3), (2, 4), (2, 5), (2, 6), (3, 7), (3, 8), (3, 9), (4, 10), (4, 11), (4, 12), (5, 13), (5, 14), (5, 15), (6, 16), (6, 17), (6, 18), (7, 19), (7, 20), (7, 21), (8, 22), (8, 23), (8, 24), (9, 25), (9, 26), (9, 27), (10, 28), (10, 29), (10, 30), (11, 31), (11, 32), (11, 33), (12, 34), (12, 35), (12, 36), (13, 37), (13, 38), (13, 39), (14, 40), (14, 41), (14, 42), (15, 43), (15, 44), (15, 45), (16, 46), (16, 47), (16, 48);

--
-- Inserting a sample order for Alex Johnson
--
INSERT INTO `orders` (`user_id`, `total_amount`, `status`) VALUES
(2, 1250.00, 'Shipped');

INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(1, 9, 1, 890.00),  -- SmartPond Ecosystem
(1, 1, 1, 350.00); -- AquaSense Pro

--
-- Inserting a cart and cart items for Alex Johnson
--
INSERT INTO `carts` (`user_id`) VALUES (2);

INSERT INTO `cart_items` (`cart_id`, `product_id`, `quantity`) VALUES
(1, 4, 5),  -- FieldSense Pro (5 units)
(1, 2, 1);  -- HydroGrow Smart (1 unit)

--
-- Inserting a sample support message
--
INSERT INTO `contact_messages` (`full_name`, `email`, `subject`, `message`, `status`) VALUES
('Maria Garcia', 'maria.g@example.com', 'Question about my order #RWF-84199', 'Hello, I wanted to follow up on the status of my recent order. Can you provide an update? Thanks!', 'New');

-- --- End of Script ---



select * from rawee_db.products;

DELETE FROM rawee_db.cart_items WHERE product_id = 9;
DELETE FROM rawee_db.order_items WHERE product_id = 9;

DELETE FROM rawee_db.products WHERE product_id = 9;
