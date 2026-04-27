CREATE TABLE `IT202-E25-Golf` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tournament_id` varchar(50) NOT NULL,
  `tournament_name` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_api` tinyint(1) DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

