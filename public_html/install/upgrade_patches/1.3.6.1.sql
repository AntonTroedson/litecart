ALTER TABLE `lc_tax_rates` ADD COLUMN `address_type` ENUM('payment','shipping') NOT NULL DEFAULT 'payment' AFTER `rate`;
-- --------------------------------------------------------
ALTER TABLE `lc_settings` CHANGE `value` `value` VARCHAR(8192);
