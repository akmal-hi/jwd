-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2026 at 05:41 AM
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
-- Database: `jwd`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Desain Grafis', '2026-08-03 17:04:49'),
(2, 'Illustrasi', '2026-08-03 17:04:49'),
(3, 'Fotografi', '2026-08-03 17:04:49'),
(4, 'Videografi', '2026-08-03 17:04:49');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `client_address` text DEFAULT NULL,
  `service_name` varchar(200) NOT NULL,
  `service_description` text DEFAULT NULL,
  `guide_content` longtext DEFAULT NULL,
  `schedule` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','paid','overdue') NOT NULL DEFAULT 'draft',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `unique_link` varchar(50) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `client_name`, `client_email`, `client_phone`, `client_address`, `service_name`, `service_description`, `guide_content`, `schedule`, `amount`, `tax`, `discount`, `total`, `status`, `issue_date`, `due_date`, `notes`, `unique_link`, `product_id`, `created_at`, `updated_at`) VALUES
(1, 'INV-LSP-20260808-FCCC', 'Akmal', 'akmlh8@gmail.com', '081574016719', 'pangkalan jati', 'Ilustrasi comic', 'adada', '', 'Pelaksanaan Program: Sabtu, 22 Agustus 2026 - Selasa, 25 Agustus 2026\r\nWaktu: 08.00 - 16.00 WIB\r\nLokasi: LSP COACHPRO INDONESIA (Online / Offline)', 200000.00, 0.00, 0.00, 200000.00, 'draft', '2026-08-08', '2026-09-07', '', 'WQkj93pA5bd7', 10, '2026-08-08 08:44:47', '2026-08-08 08:44:47');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `demo_link` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `wa_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `description`, `price`, `demo_link`, `image_url`, `wa_number`, `created_at`) VALUES
(1, 'CV ATS', 1, 'JASA PEMBUATAN CV ATS FRIENDLY yang profesional, rapi, dan mudah dibaca oleh sistem Applicant Tracking System (ATS). Buat CV lebih profesional, mudah dibaca ATS, dan siap untuk melamar kerja!', 'Rp 200.000', '', 'uploads/1786161016_9799.png', '081574016719', '2026-08-03 17:04:49'),
(7, 'Fotografi Wedding', 3, 'Abadikan momen pernikahan Anda dalam visual yang natural, elegan, dan penuh cerita. DKV ROOM siap menangkap setiap momen berharga, dari persiapan hingga resepsi, untuk menjadi kenangan yang dapat dikenang selamanya. Capture the moment. Keep the story.', 'Rp. 2.500.000', '', 'uploads/1786170367_2456.jpg', '081574016719', '2026-08-08 06:26:07'),
(8, 'Videografer Musik', 4, 'Mengubah musik menjadi visual yang punya cerita. DKV ROOM menyediakan jasa pembuatan: Music Video, Live Session, Performance Video, dan Music Content dengan konsep visual yang disesuaikan dengan karakter musik dan identitas musisi.. Your sound. Our vision.', 'Rp. 10.000.000', '', 'uploads/1786170468_7179.jpg', '081574016719', '2026-08-08 06:27:48'),
(9, 'Illustrasi Digital', 2, 'Mengubah ide menjadi visual yang unik dan berkarakter. Melayani character illustration, artwork, poster, merchandise, custom illustration untuk kebutuhan personal maupun komersial.. Your idea, illustrated.', 'Rp 200.000', '', 'uploads/1786170586_8076.webp', '081574016719', '2026-08-08 06:29:46'),
(10, 'Ilustrasi comic', 2, 'adada', 'Rp 200.000', '', 'uploads/1786175700_4302.jpg', '081574016719', '2026-08-08 07:55:00'),
(11, 'CV Kreatif', 1, '', 'Rp', '', 'uploads/1786262374_1976.jpeg', '081574016719', '2026-08-09 07:59:35');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_logo', '', '2026-08-03 17:04:50'),
(2, 'site_name', 'DKV ROOM', '2026-08-08 04:53:55'),
(3, 'wa_number', '6281574016719', '2026-08-08 04:53:55'),
(4, 'footer_copyright', 'DKV ROOM — Your Creative Space.', '2026-08-08 04:53:55'),
(5, 'footer_credit_left', ' Mobile Friendly', '2026-08-08 04:53:55'),
(6, 'footer_credit_right', ' Kinerja Cepat', '2026-08-08 04:53:55'),
(7, 'cta_title', '💡 Konsultasi Gratis!', '2026-08-03 17:28:39'),
(8, 'cta_message', 'A creative space where ideas become visuals. Let’s create something meaningful!', '2026-08-08 04:53:55'),
(9, 'cta_emoji', '🎁', '2026-08-03 17:28:39'),
(10, 'cta_btn_text', 'Hubungi Sekarang →', '2026-08-03 17:28:39'),
(11, 'cta_wa_text', 'Halo, saya butuh konsultasi mengenai jasa di DKV ROOM.', '2026-08-08 04:53:55'),
(12, 'cta_footer_text', '⭐ 500+ klien puas | ⚡ Respon cepat | 🎯 Garansi 30 hari', '2026-08-03 17:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2a$12$WYVrEWoNyVIFbYvUqLZiweVcyEBpV.KaBpIQ/tYhCn/jm16B3lgU6', '2026-08-03 17:04:49'),
(2, 'admin akmal', '$2a$12$l6GUFYrhw6qyyUQNAWN0Q.lZHlMn0AFSd0juLBnN7e9oK1eS7wNnW', '2026-08-08 08:00:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD UNIQUE KEY `unique_link` (`unique_link`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
