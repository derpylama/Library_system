-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 09:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `sab_code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `sab_code`, `name`) VALUES
(1, 'A', 'Allmänt'),
(2, 'B', 'Filosofi och psykologi'),
(3, 'C', 'Religion'),
(4, 'D', 'Historia'),
(5, 'E', 'Samhällsvetenskap'),
(6, 'F', 'Språkvetenskap'),
(7, 'H', 'Litteratur'),
(8, 'I', 'Musik, teater och film');

-- --------------------------------------------------------

--
-- Table structure for table `copy`
--

CREATE TABLE `copy` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `status` enum('available','on_loan','lost','written_off') DEFAULT 'available',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `copy`
--

INSERT INTO `copy` (`id`, `media_id`, `barcode`, `status`, `created_at`) VALUES
(1, 1, 'RING-001', 'available', '2025-10-30 17:53:11'),
(2, 1, 'RING-002', 'available', '2025-10-30 17:53:11'),
(3, 2, 'MILL-001', 'on_loan', '2025-10-30 17:53:11'),
(4, 3, 'PIPPI-001', 'available', '2025-10-30 17:53:11'),
(5, 4, 'HP-001', 'on_loan', '2025-10-30 17:53:11'),
(6, 5, 'MARTIAN-001', 'available', '2025-10-30 17:53:11'),
(7, 5, 'MARTIAN-002', 'available', '2025-10-30 17:53:11'),
(8, 6, 'SW-001', 'written_off', '2025-10-30 17:53:11'),
(9, 7, 'INTER-001', 'available', '2025-10-30 17:53:11'),
(11, 8, 'LWW-001', 'on_loan', '2025-11-02 22:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issued_at` datetime DEFAULT current_timestamp(),
  `paid` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`id`, `user_id`, `loan_id`, `amount`, `issued_at`, `paid`, `description`) VALUES
(1, 3, 2, 373.50, '2025-10-30 17:53:11', 0, 'Overdue fine for Star Wars');

-- --------------------------------------------------------

--
-- Table structure for table `loan`
--

CREATE TABLE `loan` (
  `id` int(11) NOT NULL,
  `copy_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('active','returned','overdue','written_off') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan`
--

INSERT INTO `loan` (`id`, `copy_id`, `user_id`, `loan_date`, `due_date`, `return_date`, `status`) VALUES
(1, 3, 2, '2025-10-30', '2025-11-20', NULL, 'active'),
(2, 8, 3, '2025-09-30', '2025-10-21', NULL, 'written_off'),
(3, 5, 2, '2025-10-30', '2025-11-20', NULL, 'active'),
(4, 1, 3, '2025-11-01', '2025-11-22', '2025-11-01', 'returned'),
(5, 11, 1, '2025-11-02', '2025-11-23', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `title` varchar(512) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `media_type` enum('bok','ljudbok','film') NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `isbn`, `title`, `author`, `media_type`, `category_id`, `description`, `price`, `created_at`, `updated_at`) VALUES
(1, '9780261103573', 'Sagan om ringen', 'J.R.R. Tolkien', 'bok', 7, 'En episk fantasyroman.', 299.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11'),
(2, '9789170018361', 'Män som hatar kvinnor', 'Stieg Larsson', 'bok', 7, 'Första delen i Millennium-trilogin.', 159.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11'),
(3, '9789129688310', 'Pippi Långstrump', 'Astrid Lindgren', 'ljudbok', 7, 'Klassisk barnbok som ljudbok.', 120.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11'),
(4, '9780747532743', 'Harry Potter och de vises sten', ' J.K. Rowling', 'bok', 7, 'Magisk fantasyroman för alla åldrar.', 199.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11'),
(5, '9780307887443', 'The Martian', 'Andy Weir', 'bok', 7, 'Sci-fi berättelse om en man strandad på Mars.', 179.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11'),
(6, '9780000000003', 'Star Wars: A New Hope', 'George Lucas', 'film', 8, 'Klassisk sci-fi film.', 249.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11'),
(7, '9780000000004', 'Interstellar', 'Christopher Nolan', 'film', 8, 'Ett mästerverk inom science fiction.', 269.00, '2025-10-30 17:53:11', '2025-10-30 17:53:11'),
(8, '9780001831803', 'The Lion, the Witch and the Wardrobe', 'C.S. Lewis', 'bok', 7, 'Adventures in Narnia', 150.00, '2025-11-02 22:32:51', '2025-11-02 22:32:51');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password_`, `is_admin`, `created_at`) VALUES
(1, 'admin', 'admin123', 1, '2025-10-30 17:53:11'),
(2, 'alice', 'password1', 0, '2025-10-30 17:53:11'),
(3, 'bob', 'password2', 0, '2025-10-30 17:53:11'),
(4, 'person2', 'person2', 0, '2025-10-30 17:55:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_index_0` (`sab_code`);

--
-- Indexes for table `copy`
--
ALTER TABLE `copy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `loan_id` (`loan_id`);

--
-- Indexes for table `loan`
--
ALTER TABLE `loan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `copy_id` (`copy_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `copy`
--
ALTER TABLE `copy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan`
--
ALTER TABLE `loan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `copy`
--
ALTER TABLE `copy`
  ADD CONSTRAINT `copy_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`loan_id`) REFERENCES `loan` (`id`);

--
-- Constraints for table `loan`
--
ALTER TABLE `loan`
  ADD CONSTRAINT `loan_ibfk_1` FOREIGN KEY (`copy_id`) REFERENCES `copy` (`id`),
  ADD CONSTRAINT `loan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
