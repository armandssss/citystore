-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2024 at 11:13 AM
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
-- Database: `veikals`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'In cart'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `added_at`, `status`) VALUES
(1, 14, 2, 1, '2023-12-19 14:17:14', 'In cart'),
(2, 14, 1, 1, '2023-12-19 14:17:18', 'In cart'),
(67, 26, 3, 1, '2024-03-06 18:04:49', 'In cart'),
(68, 26, 3, 1, '2024-03-06 18:04:54', 'In cart'),
(81, 25, 2, 1, '2024-03-08 17:02:31', 'In cart'),
(136, 27, 1, 1, '2024-03-14 15:06:18', 'In cart'),
(137, 27, 1, 1, '2024-03-14 15:06:24', 'In cart'),
(192, 44, 2, 1, '2024-05-26 12:01:44', 'In cart');

-- --------------------------------------------------------

--
-- Table structure for table `cart_total`
--

CREATE TABLE `cart_total` (
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_total`
--

INSERT INTO `cart_total` (`user_id`, `total`) VALUES
(25, 1500.00),
(26, 400.00),
(27, 2000.00),
(28, 3400.00),
(29, 159500.00),
(30, 2000.00),
(34, 3500.00),
(41, 1500.00),
(42, 5200.00),
(44, 1200.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Electronics'),
(2, 'Wearables'),
(3, 'Computers'),
(4, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `account_sender_id` int(11) NOT NULL,
  `account_receiver_id` int(11) NOT NULL,
  `submit_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `msg` varchar(255) NOT NULL,
  `submit_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `status`, `created_at`) VALUES
(290, 42, '', '2024-05-20 13:10:19'),
(294, 42, '', '2024-05-20 13:33:27'),
(295, 28, '', '2024-05-23 08:14:11'),
(296, 44, '', '2024-05-23 22:27:23');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`) VALUES
(27, 290, 4, 1),
(28, 290, 1, 1),
(29, 290, 9, 1),
(30, 295, 1, 1),
(31, 295, 4, 1),
(32, 296, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `image_url_2` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `image_url`, `image_url_2`, `company`) VALUES
(1, 'MacBook Air', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 1000.00, 'uploads/macbook_air.png', 'uploads/macbook_air_2.png', 'Apple'),
(2, 'iPhone 15 Pro', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 1200.00, 'uploads/iphone_15_pro.png', 'uploads/iphone_15_pro_2.png', 'Apple'),
(3, 'AirPods Pro', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 200.00, 'uploads/airpods_pro.png', 'uploads/airpods_pro_2.png', 'Apple'),
(4, 'AirPods Max', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 500.00, 'uploads/airpods_max.png', 'uploads/airpods_max_2.png', 'Apple'),
(5, 'Apple Watch', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 1000.00, 'uploads/apple_watch.png', 'uploads/apple_watch_2.png', 'Apple'),
(9, 'MacBook Pro', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 2000.00, 'uploads/macbook_pro.png', 'uploads/macbook_pro.png', 'Apple'),
(10, 'iPhone SE', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 400.00, 'uploads/iphone_se.png', 'uploads/iphone_se_2.png', 'Apple'),
(11, 'iPad Air Pro', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 800.00, 'uploads/ipad_air_pro.png', 'uploads/ipad_air_pro_2.png', 'Apple'),
(12, 'Apple Watch SE', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 300.00, 'uploads/apple_watch_se.png', 'uploads/apple_watch_se_2.png', 'Apple'),
(13, 'AirPods 3', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 250.00, 'uploads/airpods_gen_3.png', 'uploads/airpods_gen_3_2.png', 'Apple'),
(14, 'Mac Mini', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 700.00, 'uploads/mac_mini.png', 'uploads/mac_mini_2.png', 'Apple'),
(15, 'Apple TV 4K', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis libero consequat, consectetur felis nec, pharetra eros.', 250.00, 'uploads/apple_4k_tv.png', 'uploads/apple_4k_tv_2.png', 'Apple');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES
(1, 1),
(1, 3),
(2, 1),
(3, 1),
(3, 2),
(3, 4),
(4, 2),
(5, 2),
(5, 4),
(9, 1),
(9, 3),
(10, 1),
(11, 1),
(11, 3),
(12, 2),
(13, 1),
(13, 4),
(14, 3),
(15, 1),
(15, 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `secret` varchar(255) NOT NULL DEFAULT '',
  `last_seen` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Occupied','Waiting','Idle') NOT NULL DEFAULT 'Idle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `profile_picture`, `created_at`, `secret`, `last_seen`, `status`) VALUES
(9, '12345', '12345@gmail.com', '$2y$10$kbw8hez45hH0DPIwEM.BteyQc5eXsD0sDDj.POnWHKTcZVnk5v2Gq', 'user', '', '2023-12-10 16:40:14', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(11, '123123', '123123@gmail.com', '$2y$10$Xh7tJY5NBK.146Ny7Mm21uhVrsr0G.9c9at348eHVf2DdIqElDyo.', 'user', '', '2023-12-10 20:28:39', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(12, 'armands', 'armands@armands.lv', '$2y$10$k/GwNfL6WU8E2Psr1nIMo.uF3JcLVT8pBnJ0QLdKVKinl6WljM9fO', 'user', '', '2023-12-15 13:15:23', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(14, 'armandsjegers3', 'armandsjegers3@gmail.com', '$2y$10$9Snb83rqXVKSQoFpOrElyuHYQNrjTbnJaVfM/mTC7CK9x2ntqE3sO', 'user', '', '2023-12-19 16:15:19', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(15, 'didzis', 'user@school.com', '$2y$10$7/PWt4YpcbKXfvMtj1faJur0nWr448skPwUF1GcSaytoi7d3mwQxe', 'user', '', '2023-12-19 17:19:44', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(16, 'haha', 'haha@gmail.com', '$2y$10$bZuC/STaAk7M8H980RPDGO6DKqCo/yqemIymbnR/fVY/yUnyhLEVK', 'user', '', '2023-12-19 17:20:24', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(18, 'armands1', 'armands1@gmail.com', '$2y$10$lJt/2SO8Abo8NI4naoR2KO9cpariCQ0GpwrvwpUJHTz7d.sunpAKO', 'user', '', '2023-12-27 16:22:19', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(19, 'anita max wynn', 'armands2@gmail.com', '$2y$10$O3M0m5Paso.rsCJsuTjOQuFbB.FBaOkLin3CcYQ/SgS5rAs1Q1Uw2', 'user', '', '2023-12-27 16:44:34', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(20, 'GamingKiller3000', 'armands3@gmail.com', '$2y$10$eZ135P6LzCvZGi1zNxSu..yL8CT.BIEVxq60VUtjg0KGM4BG6chDy', 'user', '', '2023-12-27 16:48:48', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(23, 'lmaoo', 'lmao@gmail.com', '$2y$10$K1PvpKybkqwZpCRUvrsFoe65Ox3ZEzPQ/Xq4cCiDsb8lOSaPXPxjC', 'user', '', '2023-12-27 16:58:03', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(24, 'nezinu', 'nezinu@gmail.com', '$2y$10$EkYe9r9hVsusnKUzFjb6Iu0ORBMnRCAu/s0KGo4lWJSYbYB5Hf13u', 'user', '', '2023-12-27 17:00:53', 'new_secret_value', '2024-01-16 21:26:17', 'Idle'),
(25, 'armands4', 'armands4@gmail.com', '$2y$10$iTXwf0I2Y73MdaVkeYi6oeZv8B/CaJ57ik0dp5Pa1DaPWiynM0ASW', 'user', '', '2023-12-28 15:46:55', 'new_secret_value', '2024-03-08 18:06:41', 'Idle'),
(26, 'armands04', 'armandsjegers04@gmail.com', '$2y$10$WGJspcAEYrgxvWsaAv6DBej9mkYrRikZDfkjAvHTaCW1BMC9WLiRm', 'user', '', '2024-01-08 15:53:38', 'new_secret_value', '2024-03-09 13:46:21', 'Idle'),
(27, 'admin', 'citystore.help@gmail.com', '$2y$10$5/KvMMkWG9Z6ZMthG/X4aOVxYYNzODyJfTZm3aU.nGCpvPvn2dDDa', 'admin', 'uploads/65f30a6a80118_support-chat.png', '2024-01-10 18:46:28', 'new_secret_value', '2024-05-22 20:08:47', 'Idle'),
(28, 'iFeelLikePablo', 'armandsjegers4@gmail.com', '$2y$10$WZ2bKS803FURToHls/6UL.G5RMtcgzBLzFXtTnboMCQVwA.Iaiwzq', 'user', '', '2024-01-16 20:14:38', '', '2024-05-26 19:09:31', 'Idle'),
(29, 'student', 'ip20.a.jegers@vtdt.edu.lv', '$2y$10$TN4nZJJjd0NTc.T6XN7fo..Z5mBmQg57wjUsSMBHH8DzP6P3t.QaW', 'user', '', '2024-01-17 19:47:36', '', '2024-05-26 14:39:20', 'Idle'),
(30, 'DDuke', 'duke.dennis@gmail.com', '$2y$10$2.J3e.ka8OAlltQmu4Fhk.FJM5PAQxGAk2K22jd7aX5XfgQLCa3IC', 'user', '', '2024-03-11 16:20:21', '', '2024-03-13 16:49:14', 'Idle'),
(31, 'test1', 'test@test.com', '$2y$10$3M1N1udKflbl48sW5Ny65uEzkaj.lSKzy0OL24hT77DY/UKNXrJOi', 'user', '', '2024-03-12 12:19:55', '', '2024-03-12 14:19:55', 'Idle'),
(32, 'test2', 'test2@test.com', '$2y$10$gzl2qpIyYrKFgjTFjXcTYuK9HB5kWx6CJQDUChnHqzN.VadeMzeVW', 'user', '', '2024-03-12 14:48:18', '', '2024-03-12 15:50:28', 'Idle'),
(33, 'afawf', 'awfawfawf@wfafawf.lv', '$2y$10$/YiYr/pPP7ucTvk9Bh29punx8QDH9WFZ7JOghPXYlRGD8tIkbuHz2', 'user', '', '2024-03-12 15:58:49', '', '2024-03-12 17:58:49', 'Idle'),
(34, 'armaaa', 'armisj2@inbox.lv', '$2y$10$SMIv852VX6vYEFLvH6N23OEbjx37Nv/qKUgsAVO7FE.Y5Pg.Lknfm', 'user', '', '2024-03-12 17:04:38', '', '2024-03-13 21:19:10', 'Idle'),
(35, 'armaaaa', 'armisj3@inbox.lv', '$2y$10$bGZDsdjFuqQBS.mS4ffBm.5/yti5QiXxhj59Cmb0/QdIGhvYzSoRO', 'user', '', '2024-03-12 17:05:27', '', '2024-03-12 19:05:27', 'Idle'),
(36, 'armaaaaa', 'armandsjegers044@gmail.com', '$2y$10$ZbYRIDhKGNnofFWP2ZYLV.mx7sRuwC9koXPkISk47mgnNEK7SOLOO', 'user', '', '2024-03-12 17:15:23', '', '2024-03-12 20:11:49', 'Idle'),
(37, 'armaaaaaaa', 'aaa@a.lc', '$2y$10$qRatY9ghj1lkswJ3EmHO7uWhL0zhJLO1509rBytJWJrAfol7kx5AG', 'user', '', '2024-03-12 17:16:07', '', '2024-03-12 19:16:07', 'Idle'),
(38, '358963589345', 'aaaaaaaa@a.lc', '$2y$10$cLq/eIBL7kq3j65.Hqy5UOS3fvO6.q5Lzn/qpWtvUX4n3EJ3t7hUK', 'user', '', '2024-03-12 17:19:35', '', '2024-03-12 19:19:35', 'Idle'),
(39, 'wagiohjawgoiuawba', 'waopingawi@waiognawoig.lv', '$2y$10$g5FHuzd2Ao.dhwDEX/9v9.gpmtdsv4wG2PRLcM/2HBNnRqabOy6/G', 'user', '', '2024-03-12 17:21:11', '', '2024-03-12 19:21:11', 'Idle'),
(40, 'wagwagwag', 'awignhwaign@wainga.lv', '$2y$10$wsRVsTt6nCgJgIhh/5UpJu93nBxwimABf20m6ncGr1fL5CsFq/y6W', 'user', '', '2024-03-12 17:26:11', '', '2024-03-12 19:26:11', 'Idle'),
(41, 'okayy', 'okayy@okay.okay', '$2y$10$PwQXuYtoBku8GBDfHoxsDekQOoV43ksNHwTgYPQT4FZe27SKNjJ.y', 'user', '', '2024-03-13 10:24:43', '', '2024-03-13 16:27:11', 'Idle'),
(42, 'user222', 'user222@gmail.com', '$2y$10$paCUok/udIdr0vUbrIxIleVzPMdIfFALvHUsxlHG5Z7/mJFbhUEIW', 'user', '', '2024-03-15 19:11:24', '', '2024-05-20 15:35:57', 'Idle'),
(43, 'user99', 'user99@gmail.com', '$2y$10$ogDwNIFTQn/71XrGf/Mjvu9NPhEox/5Dni7V8b.4hR/3U0lF4phXC', 'user', '', '2024-04-01 20:02:11', '', '2024-04-01 22:02:23', 'Idle'),
(44, 'user00', 'citystore@gmail.com', '$2y$10$rzFlM/44aIloD8Gp5RWEa.ptndEDdOMizYo5Wzvelkc7H9F9nq9qS', 'user', '', '2024-05-23 17:22:56', '', '2024-05-29 11:13:08', 'Idle');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `cart_total`
--
ALTER TABLE `cart_total`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=297;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_total`
--
ALTER TABLE `cart_total`
  ADD CONSTRAINT `fk_cart_total_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
