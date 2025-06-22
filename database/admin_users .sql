-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- 主機： mysql
-- 產生時間： 2025 年 06 月 18 日 05:57
-- 伺服器版本： 9.3.0
-- PHP 版本： 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `attendance_web_project`
--

-- --------------------------------------------------------

--
-- 資料表結構 `admin_users`
--

CREATE TABLE `admin_users` (
  `no` int NOT NULL,
  `acc` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `role` enum('admin','adv-user','normal-user') NOT NULL,
  `group_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `admin_users`
--

INSERT INTO `admin_users` (`no`, `acc`, `pwd`, `role`, `group_name`) VALUES
(1, 'admin@demo.com', '16d7a4fca7442dda3ad93c9a726597e4', 'admin', 'FS101'),
(2, 'adv-user@test.com',  '5f4dcc3b5aa765d61d8327deb882cf99', 'adv-user', 'FS101'),
(3, 'user@test.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'normal-user', 'FS101'),
(4, 'Aaron',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(5, 'Alex',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(6, 'Bun',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(7, 'Catherine',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(8, 'Cody',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(9, 'Elmo',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(10, 'Emily',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(11, 'Grace',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(12, 'Jason',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(13, 'Joan',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(14, 'Kasumi',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(15, 'Kevin',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(16, 'KevinChuang',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(17, 'KevinWang',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(18, 'Lihe',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(19, 'Melody',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(20, 'Ray',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(21, 'Shen',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(22, 'Simon',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(23, 'Stanley',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(24, 'Ted',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101'),
(25, 'Tina',  '$2y$10$D2AN7I/Tu4bvMsdHf.w.lu55dWQPVuJctmQ/WGYfIYSmrIbRI7VYe', 'normal-user', 'FS101');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`no`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
