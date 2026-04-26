CREATE TABLE `IT202-E25-Golf` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tourn_id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_api` tinyint(1) DEFAULT '1'
)