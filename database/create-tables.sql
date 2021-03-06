CREATE TABLE `prefix_paymentmodule` ( 
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(11) DEFAULT NULL,
	`api_status` ENUM('CREATED','SAVED','APPROVED','VOIDED','COMPLETED') DEFAULT NULL,
	`amount` DECIMAL(10,2) NOT NULL,
	`currency` CHAR(3) NOT NULL,
	`sandbox` TINYINT(1) UNSIGNED NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
);
