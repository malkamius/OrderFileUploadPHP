CREATE SCHEMA ordersdb;
USE ordersdb;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `contact_name` varchar(200) NOT NULL,
  `phone_number` varchar(45) NOT NULL,
  `email_address` varchar(256) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `address1` text NOT NULL,
  `address2` text NOT NULL,
  `city` varchar(45) NOT NULL,
  `state` varchar(45) NOT NULL,
  `zipcode` varchar(45) NOT NULL,
  `duedate` date NOT NULL,
  `duetime` varchar(45) NOT NULL,
  `project_number` varchar(45) NOT NULL,
  `purchase_order_number` varchar(45) NOT NULL,
  `project_name` varchar(45) NOT NULL,
  `notes` text NOT NULL,
  `status` varchar(45) NOT NULL,
  `view_order_key` varchar(45) NOT NULL,
  `upload_file_key` varchar(45) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `files` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `filepath` varchar(256) NOT NULL,
  `content_type` varchar(256) NOT NULL,
  `length` bigint NOT NULL,
  `order_id` int NOT NULL,
  `written_bytes` bigint NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
