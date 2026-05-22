-- BETELITE Sports Prediction Platform Database Schema
-- Designed for PHP 8+ & MySQLi (cPanel Shared Hosting)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telegram_id` varchar(100) DEFAULT NULL UNIQUE,
  `username` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `avatar_url` text DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL UNIQUE,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('USER', 'PREDICTOR', 'ADMIN') NOT NULL DEFAULT 'USER',
  `is_vip` tinyint(1) NOT NULL DEFAULT 0,
  `vip_expires_at` datetime DEFAULT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `referral_code` varchar(50) NOT NULL UNIQUE,
  `referred_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `wallets`
--
CREATE TABLE IF NOT EXISTS `wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL UNIQUE,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `predictor_earnings` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `matches`
--
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sport` enum('Football', 'Basketball', 'Tennis', 'eSports') NOT NULL DEFAULT 'Football',
  `home_team` varchar(100) NOT NULL,
  `away_team` varchar(100) NOT NULL,
  `home_logo` varchar(255) DEFAULT NULL,
  `away_logo` varchar(255) DEFAULT NULL,
  `match_time` datetime NOT NULL,
  `status` enum('SCHEDULED', 'LIVE', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'SCHEDULED',
  `home_score` int(11) NOT NULL DEFAULT 0,
  `away_score` int(11) NOT NULL DEFAULT 0,
  `live_timer` varchar(10) DEFAULT NULL,
  `extra_stats` text DEFAULT NULL, -- JSON formatted stats: possession, cards, shots etc.
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `predictions` (marketplace prediction bundles/tickets)
--
CREATE TABLE IF NOT EXISTS `predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predictor_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tips_json` text NOT NULL, -- JSON array of 3-5 tips (prediction, option, odds, confidence)
  `total_odds` decimal(6,2) NOT NULL DEFAULT 1.00,
  `confidence` int(11) NOT NULL DEFAULT 50, -- overall average confidence
  `is_vip` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('PENDING', 'WON', 'LOST', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
  `sales_count` int(11) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`predictor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `cart`
--
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `prediction_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_cart_prediction` (`user_id`, `prediction_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prediction_id`) REFERENCES `predictions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `orders`
--
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  `prediction_id` int(11) NOT NULL,
  `price_paid` decimal(10,2) NOT NULL,
  `status` enum('COMPLETED', 'REFUNDED') NOT NULL DEFAULT 'COMPLETED',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prediction_id`) REFERENCES `predictions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `transactions`
--
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('DEPOSIT', 'WITHDRAWAL', 'PURCHASE', 'EARNING', 'REFERRAL_BONUS', 'VIP_UPGRADE') NOT NULL,
  `status` enum('PENDING', 'COMPLETED', 'FAILED') NOT NULL DEFAULT 'PENDING',
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(100) NOT NULL UNIQUE,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `subscriptions` (VIP Subscriptions)
--
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_type` enum('WEEKLY', 'MONTHLY', 'YEARLY') NOT NULL,
  `price_paid` decimal(10,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('ACTIVE', 'EXPIRED', 'PENDING') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `ads`
--
CREATE TABLE IF NOT EXISTS `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` enum('BANNER', 'BOOST', 'POPUP', 'PROMOTION') NOT NULL DEFAULT 'BANNER',
  `impressions` int(11) NOT NULL DEFAULT 0,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `status` enum('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `notifications`
--
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `referrals`
--
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL UNIQUE,
  `commission_earned` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('PENDING', 'PAID') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `support_tickets`
--
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `reply` text DEFAULT NULL,
  `status` enum('OPEN', 'CLOSED', 'ANSWERED') NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `withdrawals`
--
CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payout_details` text NOT NULL, -- Crypto wallet Address or Bank Account
  `status` enum('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `earnings`
--
CREATE TABLE IF NOT EXISTS `earnings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predictor_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(5,2) NOT NULL DEFAULT 80.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`predictor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- SEED INITIAL DATA FOR QUICK TESTING
--

INSERT INTO `users` (`id`, `telegram_id`, `username`, `first_name`, `last_name`, `role`, `is_vip`, `referral_code`) VALUES
(1, '123456789', 'admin_demo', 'BetElite', 'Admin', 'ADMIN', 1, 'BETADMIN'),
(2, '987654321', 'predictor_john', 'John', 'Predictor', 'PREDICTOR', 1, 'PREDJOHN'),
(3, '555555555', 'buyer_bob', 'Bob', 'Buyer', 'USER', 0, 'BOBBUYER');

INSERT INTO `wallets` (`user_id`, `balance`, `predictor_earnings`) VALUES
(1, 10000.00, 0.00),
(2, 250.00, 480.00),
(3, 100.00, 0.00);

INSERT INTO `matches` (`id`, `sport`, `home_team`, `away_team`, `home_logo`, `away_logo`, `match_time`, `status`, `home_score`, `away_score`, `live_timer`, `extra_stats`) VALUES
(1, 'Football', 'Manchester United', 'Chelsea', '🔴', '🔵', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'SCHEDULED', 0, 0, NULL, NULL),
(2, 'Football', 'Real Madrid', 'Barcelona', '⚪', '🔵🔴', DATE_ADD(NOW(), INTERVAL -1 HOUR), 'LIVE', 2, 1, '57\'', '{"possession":[52,48],"shots_on_target":[6,4],"yellow_cards":[1,2],"corners":[4,3]}'),
(3, 'Basketball', 'LA Lakers', 'Golden State Warriors', '🟡', '🔵🟡', DATE_ADD(NOW(), INTERVAL 4 HOUR), 'SCHEDULED', 0, 0, NULL, NULL),
(4, 'Tennis', 'Novak Djokovic', 'Carlos Alcaraz', '🎾', '🇪🇸', DATE_ADD(NOW(), INTERVAL -3 HOUR), 'COMPLETED', 3, 1, 'FT', '{"aces":[12,8],"double_faults":[2,4],"unforced_errors":[24,31]}'),
(5, 'eSports', 'Natus Vincere', 'FaZe Clan', '💛', '❤️', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'SCHEDULED', 0, 0, NULL, NULL);

-- Seed Predictor Predictions
INSERT INTO `predictions` (`id`, `predictor_id`, `match_id`, `title`, `description`, `price`, `tips_json`, `total_odds`, `confidence`, `is_vip`, `status`, `sales_count`, `views`) VALUES
(1, 2, 1, 'Man Utd vs Chelsea - Super Combo', 'Highly analysed bundle for the Manchester Derby-like rival event. Safe odds.', 15.00, '[{"prediction":"Match Winner","option":"Manchester United","odds":"2.10","confidence":"82"},{"prediction":"Both Teams to Score","option":"Yes","odds":"1.65","confidence":"78"},{"prediction":"Total Goals","option":"Over 2.5","odds":"1.75","confidence":"85"}]', 6.06, 81, 0, 'PENDING', 4, 32),
(2, 2, 2, 'El Clasico Live VIP Ticket', 'Premium live tips for the ongoing El Clasico. Extremely hot.', 25.00, '[{"prediction":"Next Goal (3rd Goal)","option":"Real Madrid","odds":"2.40","confidence":"90"},{"prediction":"Total Cards","option":"Over 4.5","odds":"1.80","confidence":"95"}]', 4.32, 92, 1, 'PENDING', 2, 15),
(3, 2, 4, 'Tennis Finals Masterpiece', 'The Novak Djokovic masterclass prediction bundle.', 10.00, '[{"prediction":"Match Winner","option":"Novak Djokovic","odds":"1.60","confidence":"95"},{"prediction":"Set Handicap","option":"Djokovic -1.5","odds":"1.90","confidence":"90"}]', 3.04, 92, 0, 'WON', 9, 84);

-- Seed VIP ads
INSERT INTO `ads` (`id`, `title`, `image_url`, `link`, `type`, `impressions`, `clicks`, `status`, `start_date`, `end_date`) VALUES
(1, '🔥 UPGRADE TO VIP - Get Fixed Combo tickets over 15+ Odds!', 'https://images.unsplash.com/photo-1518063319789-7217e6706b04?q=80&w=600', '#wallet', 'BANNER', 241, 19, 'ACTIVE', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, '⚡ Sponsor: Stake with 1XBET! Promo Code: BETELITE', 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?q=80&w=600', 'https://1xbet.com', 'BANNER', 510, 89, 'ACTIVE', NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY));

COMMIT;
