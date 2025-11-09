SET FOREIGN_KEY_CHECKS = 0;
-- Current
DROP TABLE IF EXISTS copy, invoice, loan, media, user, sab_category, options;

-- Deprecated
DROP TABLE IF EXISTS category;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE sab_category (
    sab_code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE user (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    passwordhash CHAR(64) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT current_timestamp()
);

CREATE TABLE media (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) DEFAULT NULL,
    isan VARCHAR(30) DEFAULT NULL,
    barcode VARCHAR(100) NOT NULL,
    title VARCHAR(512) NOT NULL,
    author VARCHAR(255) DEFAULT NULL,
    media_type ENUM('bok', 'ljudbok', 'film') NOT NULL,
    image_url MEDIUMTEXT DEFAULT NULL,
    image_width INT(11) DEFAULT 1, -- Only used for portrait/landscape/square detection
    image_height INT(11) DEFAULT 2, -- Only used for portrait/landscape/square detection
    sab_code VARCHAR(150) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT current_timestamp(),
    updated_at DATETIME DEFAULT current_timestamp()
);

CREATE TABLE copy (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    media_id INT(11) NOT NULL,
    barcode VARCHAR(100) NOT NULL,
    status ENUM('available', 'on_loan', 'lost', 'written_off') DEFAULT 'available',
    created_at DATETIME DEFAULT current_timestamp(),

    FOREIGN KEY (media_id) REFERENCES media(id)
);

CREATE TABLE loan (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    copy_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('active', 'returned', 'overdue', 'written_off') DEFAULT 'active',

    FOREIGN KEY (copy_id) REFERENCES copy(id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE invoice (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    loan_id INT(11) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    issued_at DATETIME DEFAULT current_timestamp(),
    paid TINYINT(1) DEFAULT 0,
    description TEXT DEFAULT NULL,

    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (loan_id) REFERENCES loan(id)
);

CREATE TABLE options (
    name VARCHAR(25) PRIMARY KEY,
    value VARCHAR(255) DEFAULT NULL,
    type VARCHAR(50) DEFAULT NULL,
    label VARCHAR(255) DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL
);

INSERT INTO options (name, value, type, description) VALUES
('library_name', '', 'string', 'Name of the library, shown as title in the UI'),
('compact_card_details', 'false', 'bool', 'If enabled, details of media cards are in a dropdown');