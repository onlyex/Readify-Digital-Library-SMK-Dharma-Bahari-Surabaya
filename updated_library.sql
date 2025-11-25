-- Updated SQL for library_db
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS borrows;
DROP TABLE IF EXISTS book_category;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS users;

CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255),
  publisher VARCHAR(255),
  publication_year INT,
  isbn VARCHAR(50),
  stock INT DEFAULT 0,
  description TEXT,
  cover_path VARCHAR(255),
  pdf_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE book_category (
  book_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (book_id, category_id),
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE borrows (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  book_id INT,
  borrowed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  due_at DATE,
  returned_at TIMESTAMP NULL,
  fine DECIMAL(8,2) DEFAULT 0,
  status ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  book_id INT,
  rating TINYINT,
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- sample data
INSERT INTO categories (name) VALUES ('Fantasy'),('Horror'),('Science'),('History'),('Fiction');
INSERT INTO books (title,author,publisher,publication_year,isbn,stock,description) VALUES
('Learn PHP','Jane Doe','TechBooks',2021,'978-1-23456-789-7',5,'Beginner friendly guide.'),
('Modern CSS','John Smith','DesignHouse',2020,'978-1-98765-432-1',3,'Advanced CSS techniques.');

-- ADMIN ACCOUNT (username=admin, password=admin123, email=admin@local)
-- password is bcrypt hash for "admin123"
INSERT INTO users (email, username, password, role) VALUES
('admin@local','admin','$2b$12$e0NRp6kGH1N3iFaf4GZtY.d7sW7lPj6SaTMoUxRZjZBuD6CnqNwEe','admin');

-- example normal user (keep existing hash from original file)
INSERT INTO users (email, username, password, role) VALUES
('user@example.com','bob','$2b$12$Z/EmJnsnjj47tQ9UDAZaO.crP8E64OxQoR2gy5AG2F1MqwpTtBKDK','user');

INSERT INTO announcements (title,content) VALUES ('Welcome','Library launched with new features');
