ALTER TABLE `lc_categories_info` ADD UNIQUE INDEX `category_info` (`category_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_delivery_statuses_info` ADD UNIQUE INDEX `delivery_status_info` (`delivery_status_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_languages` DROP INDEX `id`, ADD PRIMARY KEY (`id`);
-- --------------------------------------------------------
ALTER TABLE `lc_manufacturers_info` ADD UNIQUE INDEX `manufacturer_info` (`manufacturer_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_option_groups_info`	ADD UNIQUE INDEX `option_group_info` (`group_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_option_values_info` ADD UNIQUE INDEX `option_value_info` (`value_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_order_statuses_info` ADD UNIQUE INDEX `order_status_info` (`order_status_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_pages_info` ADD UNIQUE INDEX `page_info` (`page_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_info` ADD UNIQUE INDEX `product_info` (`product_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_options` ADD UNIQUE INDEX `product_option` (`product_id`, `group_id`, `value_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_options_stock` ADD UNIQUE INDEX `product_option_stock` (`product_id`, `combination`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_prices` ADD UNIQUE INDEX `product_price` (`product_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_stock` ADD UNIQUE INDEX `stock_item` (`product_id`, `warehouse_id`, `option_combination`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_to_categories` ADD UNIQUE INDEX `mapping` (`product_id`, `category_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_product_groups_info` ADD UNIQUE INDEX `product_group_info` (`product_group_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_product_groups_values_info` ADD UNIQUE INDEX `product_group_value_info` (`product_group_value_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_quantity_units_info` ADD UNIQUE INDEX `quantity_unit_info` (`quantity_unit_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_sold_out_statuses_info` ADD UNIQUE INDEX `sold_out_status_info` (`sold_out_status_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_zones_to_geo_zones` ADD UNIQUE INDEX `region` (`geo_zone_id`, `country_code`, `zone_code`);
-- --------------------------------------------------------
DELETE FROM `lc_settings` where `key` IN ('order_action_modules', 'order_success_modules');
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('', 'local', 'Installed Order Modules', '', 'order_modules', '', '', 0, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('listings', 'local', 'Product Modal Window', 'Open products in a modal window', 'product_modal_window', '1', 'toggle()', 21, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_customers` ADD `notes` TEXT NOT NULL AFTER `newsletter`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` varchar(64) NOT NULL,
  `type` varchar(16) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `settings` text NOT NULL,
  `last_log` text NOT NULL,
  `date_updated` varchar(32) NOT NULL,
  `date_created` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `module_id` (`module_id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT;
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('listings', 'global', 'Maintenance Mode', 'Setting the store in maintenance mode will prevent users from browsing your site.', 'maintenance_mode', '0', 'toggle()', 2, NOW(), NOW()),
('listings', 'local', 'Also Purchased Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_also_purchased_products_num_items', '4', 'int()', 20, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_geo_zones` ADD COLUMN `code` VARCHAR(32) NOT NULL AFTER `id`;