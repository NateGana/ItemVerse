CREATE DATABASE IF NOT EXISTS itemverse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE itemverse;
DROP TABLE IF EXISTS admin_activity_logs,wishlist,review_votes,review_images,reviews,items,categories,users;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL,email VARCHAR(150) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,role ENUM('user','admin') NOT NULL DEFAULT 'user',status ENUM('active','banned') NOT NULL DEFAULT 'active',bio TEXT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE categories(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL UNIQUE);
CREATE TABLE items(id INT AUTO_INCREMENT PRIMARY KEY,category_id INT NOT NULL,name VARCHAR(150) NOT NULL,brand VARCHAR(80) NOT NULL,description TEXT,price DECIMAL(10,2) NULL,image VARCHAR(255) NULL,featured TINYINT(1) NOT NULL DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(category_id) REFERENCES categories(id));
CREATE TABLE reviews(id INT AUTO_INCREMENT PRIMARY KEY,item_id INT NOT NULL,user_id INT NOT NULL,rating TINYINT NOT NULL,quality TINYINT NOT NULL,durability TINYINT NOT NULL,design TINYINT NOT NULL,performance TINYINT NOT NULL,value_money TINYINT NOT NULL,title VARCHAR(150) NOT NULL,body TEXT NOT NULL,pros VARCHAR(300) NULL,cons VARCHAR(300) NULL,status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY one_review(user_id,item_id),FOREIGN KEY(item_id) REFERENCES items(id) ON DELETE CASCADE,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE review_images(id INT AUTO_INCREMENT PRIMARY KEY,review_id INT NOT NULL,filename VARCHAR(255) NOT NULL,FOREIGN KEY(review_id) REFERENCES reviews(id) ON DELETE CASCADE);
CREATE TABLE review_votes(review_id INT NOT NULL,user_id INT NOT NULL,PRIMARY KEY(review_id,user_id),FOREIGN KEY(review_id) REFERENCES reviews(id) ON DELETE CASCADE,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE wishlist(user_id INT NOT NULL,item_id INT NOT NULL,PRIMARY KEY(user_id,item_id),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(item_id) REFERENCES items(id) ON DELETE CASCADE);
CREATE TABLE admin_activity_logs(id INT AUTO_INCREMENT PRIMARY KEY,admin_id INT NOT NULL,action VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(admin_id) REFERENCES users(id) ON DELETE CASCADE);
INSERT INTO categories(name) VALUES('Electronics'),('Gadgets'),('Accessories'),('Home Appliances'),('Fashion'),('Gaming'),('Everyday Essentials');
INSERT INTO users(name,email,password_hash,role) VALUES('Administrator','admin@itemverse.local','$2b$10$jEpeSldHkARYuXyznxOIN.wQjCpBaM9OYHANmJ.R//m/ff8uo8WDe','admin');
INSERT INTO users(name,email,password_hash) VALUES('Reviewer 1','reviewer1@itemverse.local','$2b$10$jEpeSldHkARYuXyznxOIN.wQjCpBaM9OYHANmJ.R//m/ff8uo8WDe'),('Reviewer 2','reviewer2@itemverse.local','$2b$10$jEpeSldHkARYuXyznxOIN.wQjCpBaM9OYHANmJ.R//m/ff8uo8WDe'),('Reviewer 3','reviewer3@itemverse.local','$2b$10$jEpeSldHkARYuXyznxOIN.wQjCpBaM9OYHANmJ.R//m/ff8uo8WDe');
INSERT INTO items(category_id,name,brand,description,price,featured) VALUES
(1,'Nimbus X1 Wireless Headphones','Auralis','Over-ear headphones with active noise cancelling and 40-hour battery life.',199.00,1),
(1,'Lumen 27 4K Monitor','Vistora','27-inch 4K IPS display with wide color coverage for work and play.',429.00,1),
(2,'Pulse Smart Tracker','Kinetra','Slim fitness tracker with heart-rate, sleep and GPS tracking.',79.99,0),
(3,'Anchor Braided USB-C Cable','Volt&Co','Two-metre braided cable rated for 100W charging.',14.99,0),
(4,'AeroCrisp Air Fryer 5L','Hearthly','Five-litre air fryer with eight presets and a dishwasher-safe basket.',109.00,1),
(5,'Trailbound Everyday Backpack','Northfold','Water-resistant 24L backpack with padded laptop sleeve.',89.00,0),
(6,'Vector Pro Gaming Mouse','Razorleaf','Lightweight 59g mouse with a 26,000 DPI sensor.',69.00,1),
(7,'Brewmate Insulated Bottle','Sipwell','750ml stainless steel bottle that keeps drinks cold for 24 hours.',27.50,0);
INSERT INTO reviews(item_id,user_id,rating,quality,durability,design,performance,value_money,title,body,pros,cons) VALUES
(1,2,5,5,4,5,5,4,'Best noise cancelling I have owned','Comfortable for long flights and the battery really does last all week of commuting.','Quiet, comfortable','Case is bulky'),
(1,3,4,4,4,4,5,3,'Great sound, a little pricey','Sound quality is excellent but I wish it cost less than it does.','Sound','Price'),
(2,2,5,5,5,4,5,5,'Sharp and colour accurate','Text is crisp and colours look great out of the box for photo editing.','Sharp, accurate','Stand wobbles'),
(3,3,3,3,3,4,3,4,'Decent for the money','Tracking is accurate enough but the strap is average after a few months.','Battery','Strap quality'),
(5,2,5,5,4,4,5,5,'Replaced my oven for weeknights','Fast, easy to clean and food comes out properly crisp every time.','Easy cleaning','Takes counter space'),
(7,4,5,5,5,4,5,4,'Precise and light','Feels great in hand and the sensor never skips during fast flicks.','Light, precise','Software is basic'),
(8,4,4,5,5,4,4,4,'Keeps ice all day','Stays cold from morning to evening and the lid seals well.','Insulation','Heavy when full');
