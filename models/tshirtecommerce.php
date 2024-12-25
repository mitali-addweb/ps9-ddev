<?php
/**
 * Model class for Tshirtecommerce
 * Handles database operations for installation/uninstallation
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class TshirtecommerceModel extends ObjectModel
{
	public static function install()
	{
		return (
			TshirtecommerceModel::create_table() 
			&& TshirtecommerceModel::alter_table() 
			&& TshirtecommerceModel::insert_setting() 
			&& TshirtecommerceModel::insert_menu() 
			&& TshirtecommerceModel::alter_orders_table()
		);
	}

	public static function uninstall()
	{
		// Uninstall in reverse order of installation
		// Most critical: remove menu first (can cause issues if left)
		$result = true;

		try {
			// Remove menu tabs first
			if (!TshirtecommerceModel::remove_menu()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to remove menu', 3);
				$result = false;
			}

			// Drop custom database columns from core tables
			if (!TshirtecommerceModel::drop_column()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to drop columns', 3);
				$result = false;
			}

			// Drop orders table columns
			if (!TshirtecommerceModel::drop_orders_table()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to drop orders table columns', 3);
				$result = false;
			}

			// Drop custom tables
			if (!TshirtecommerceModel::drop_table()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to drop tables', 3);
				$result = false;
			}

			// Remove configuration settings
			if (!TshirtecommerceModel::drop_setting()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to drop settings', 3);
				$result = false;
			}

			return $result;
		} catch (Exception $e) {
			PrestaShopLogger::addLog('Tshirtecommerce uninstall exception: ' . $e->getMessage(), 3);
			return false;
		}
	}

	private static function create_table()
	{
		$sql_order = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tshirtecommerce_order`(
				`id` int(10) NOT NULL AUTO_INCREMENT, 
				`order_product_id` int(10) NOT NULL, 
				`options` text, 
				PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1';
		$result = Db::getInstance()->execute($sql_order);
        if (!$result) return false;
        $sql_cart_product = '
        	CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tshirtecommerce_cart_product` (
        		`id_cart` int(10) NOT NULL,
        		`id_product` int(10) NOT NULL,
        		`id_address_delivery` int(10) NOT NULL DEFAULT 0,
        		`id_shop` int(10) NOT NULL DEFAULT 1,
        		`id_product_attribute` int(10) NOT NULL DEFAULT 0,
        		`quantity` int(10) NOT NULL DEFAULT 0,
        		`date_add` datetime NOT NULL,
        		`tshirtecommerce_design_cart_id` VARCHAR( 255 ),
        		`tshirtecommerce_design_cart_img` TEXT,
        		`tshirtecommerce_design_type` VARCHAR(100) DEFAULT ""
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1';
        $query = Db::getInstance()->execute($sql_cart_product);
        if (!$query) return false;

		return true;
	}

	private static function alter_table()
	{
		// Fixed for version 1.5.6.x - alter table configuration column `name`
		Db::getInstance()->execute('
			ALTER TABLE `'._DB_PREFIX_.'configuration` 
			MODIFY COLUMN `name` varchar(254) NOT NULL
		');

		$check_design_product_id = false;
		$check_design_product_title_img = false;
		$check_tshirtecommerce_design_type = false;
		$result = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."product`");
		if (!$result) return false;
		foreach ($result as $row) {
			if ($row['Field'] == 'design_product_id') $check_design_product_id = true;
			if ($row['Field'] == 'design_product_title_img') $check_design_product_title_img = true;
			if ($row['Field'] == 'tshirtecommerce_design_type') $check_tshirtecommerce_design_type = true;
		}
		if ($check_design_product_id === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."product` 
				ADD `design_product_id` VARCHAR( 255 ) DEFAULT ''
			")) return false;
		}
		if ($check_design_product_title_img === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."product` 
				ADD `design_product_title_img` TEXT
			")) return false;
		}
		if ($check_tshirtecommerce_design_type === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."product` 
				ADD `tshirtecommerce_design_type` VARCHAR( 100 ) DEFAULT ''
			")) return false;
		}
		// Alter for cart table
		$check_tshirtecommerce_design_cart_img = false;
		$check_tshirtecommerce_design_cart_id = false;
		$check_tshirtecommerce_design_type = false;
		$rel = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."cart_product`");
		foreach ($rel as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_cart_img') $check_tshirtecommerce_design_cart_img = true;
			if ($row['Field'] == 'tshirtecommerce_design_cart_id') $check_tshirtecommerce_design_cart_id = true;
			if ($row['Field'] == 'tshirtecommerce_design_type') $check_tshirtecommerce_design_type = true;
		}
		if($check_tshirtecommerce_design_cart_id === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."cart_product` 
				ADD `tshirtecommerce_design_cart_id` VARCHAR( 255 ) NOT NULL DEFAULT '0'
			")) return false;
		}
		if ($check_tshirtecommerce_design_cart_img === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."cart_product` 
				ADD `tshirtecommerce_design_cart_img` TEXT
			")) return false;
		}
		if ($check_tshirtecommerce_design_type === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."cart_product` 
				ADD `tshirtecommerce_design_type` TEXT
			")) return false;
		}
		// Alter for customization table
		$check_customization = false;
		$rel_customization = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."customization`");
		foreach ($rel_customization as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_cart_id') $check_customization = true;
		}
		if ($check_customization === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."customization` 
				ADD `tshirtecommerce_design_cart_id` VARCHAR( 255 ) NOT NULL DEFAULT '0'
			")) return false;
		}
		// Alter for order_detail table
		$check_order_detail_id 	= false;
		$check_order_detail_img = false;
		$check_order_detail_type = false;
		$rel_order_detail = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."order_detail`");
		foreach ($rel_order_detail as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_order_id') $check_order_detail_id = true;
			if ($row['Field'] == 'tshirtecommerce_design_order_img') $check_order_detail_img = true;
			if ($row['Field'] == 'tshirtecommerce_design_type') $check_order_detail_type = true;
		}
		if ($check_order_detail_id === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."order_detail` 
				ADD `tshirtecommerce_design_order_id` VARCHAR( 255 ) NOT NULL DEFAULT '0'
			")) return false;
		}
		if ($check_order_detail_img === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."order_detail` 
				ADD `tshirtecommerce_design_order_img` TEXT
			")) return false;
		}
		if ($check_order_detail_type === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."order_detail` 
				ADD `tshirtecommerce_design_type` VARCHAR( 100 ) DEFAULT ''
			")) return false;
		}
		return true;
	}

	private static function alter_orders_table()
	{
		$check_tshirtecommerce_design_order_img = false;
		$check_tshirtecommerce_design_order_id = false;
		$rel = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."orders`");
		foreach ($rel as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_order_img') $check_tshirtecommerce_design_order_img = true;
			if ($row['Field'] == 'tshirtecommerce_design_order_id') $check_tshirtecommerce_design_order_id = true;
		}
		if ($check_tshirtecommerce_design_order_id === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."orders` 
				ADD `tshirtecommerce_design_order_id` VARCHAR( 255 ) DEFAULT ''
			")) return false;
		}
		if ($check_tshirtecommerce_design_order_img === false) {
			if (!Db::getInstance()->execute("
				ALTER TABLE `"._DB_PREFIX_."orders` 
				ADD `tshirtecommerce_design_order_img` TEXT
			")) return false;
		}
		return true;
	}

	private static function insert_setting()
	{
		// setting downloadable
		$count = Db::getInstance()->getValue("
			SELECT COUNT(`name`) 
			FROM `"._DB_PREFIX_."configuration` 
			WHERE `name`='TSHIRTECOMMERCE_DOWNLOADABLE'
		");
		if ($count == 0) {
			$result = Db::getInstance()->insert('configuration', array(
				'name' => 'TSHIRTECOMMERCE_DOWNLOADABLE',
				'value' => '',
				'date_add' => date("Y-m-d H:i:s"),
				'date_upd' => date("Y-m-d H:i:s"),
			));
			if (!$result) return false;
		}
		// setting purchase code
		$count_purchase_code = Db::getInstance()->getValue("
			SELECT COUNT(`name`) 
			FROM `"._DB_PREFIX_."configuration` 
			WHERE `name`='TSHIRTECOMMERCE_PURCHASE_CODE'
		");
		if ($count_purchase_code == 0) {
			$result = Db::getInstance()->insert('configuration', array(
				'name' => 'TSHIRTECOMMERCE_PURCHASE_CODE',
				'value' => '',
				'date_add' => date("Y-m-d H:i:s"),
				'date_upd' => date("Y-m-d H:i:s"),
			));
			if (!$result) return false;
		}
		return true;
	}

	public static function insert_menu()
	{
		// PrestaShop 9 compatible tab creation using Tab class
		$languages = Language::getLanguages(false);

		// Create main parent tab
		$parentTab = new Tab();
		$parentTab->class_name = 'AdminTshirtecommerce';
		$parentTab->module = 'tshirtecommerce';
		$parentTab->id_parent = 0;
		$parentTab->icon = 'brush';
		$parentTab->active = 1;

		foreach ($languages as $language) {
			$parentTab->name[(int)$language['id_lang']] = 'Product Designer';
		}

		if (!$parentTab->add()) {
			return false;
		}

		// Create Dashboard tab
		$dashboardTab = new Tab();
		$dashboardTab->class_name = 'AdminTshirtecommerceBuilder';
		$dashboardTab->module = 'tshirtecommerce';
		$dashboardTab->id_parent = (int)$parentTab->id;
		$dashboardTab->active = 1;

		foreach ($languages as $language) {
			$dashboardTab->name[(int)$language['id_lang']] = 'Dashboard';
		}

		if (!$dashboardTab->add()) {
			return false;
		}

		// Create Settings tab
		$settingsTab = new Tab();
		$settingsTab->class_name = 'AdminTshirtecommerceSetting';
		$settingsTab->module = 'tshirtecommerce';
		$settingsTab->id_parent = (int)$parentTab->id;
		$settingsTab->active = 1;

		foreach ($languages as $language) {
			$settingsTab->name[(int)$language['id_lang']] = 'Prestashop Settings';
		}

		if (!$settingsTab->add()) {
			return false;
		}

		// Create Update tab
		$updateTab = new Tab();
		$updateTab->class_name = 'AdminTshirtecommerceUpdate';
		$updateTab->module = 'tshirtecommerce';
		$updateTab->id_parent = (int)$parentTab->id;
		$updateTab->active = 1;

		foreach ($languages as $language) {
			$updateTab->name[(int)$language['id_lang']] = 'Update';
		}

		if (!$updateTab->add()) {
			return false;
		}

		return true;
	}

	private static function drop_table()
	{
		return (
			Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'tshirtecommerce_order`') 
			&& Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'tshirtecommerce_cart_product`')
		);
	}

	private static function drop_setting()
	{
		// remove downloadable setting
		$sql_count = "
			SELECT COUNT(`name`) 
			FROM `"._DB_PREFIX_."configuration` 
			WHERE `name`='TSHIRTECOMMERCE_DOWNLOADABLE'
		";
		$count = Db::getInstance()->getValue($sql_count);
		if ($count > 0) {
			$result = Db::getInstance()->execute("
				DELETE FROM `"._DB_PREFIX_."configuration` 
				WHERE `name`='TSHIRTECOMMERCE_DOWNLOADABLE'
			");
			if (!$result) return false;
		}

		// remove purchase code setting
		$sql_purchase_code = "
			SELECT COUNT(`name`) 
			FROM `"._DB_PREFIX_."configuration` 
			WHERE `name`='TSHIRTECOMMERCE_PURCHASE_CODE'
		";
		$count_purchase_code = Db::getInstance()->getValue($sql_purchase_code);
		if ($count_purchase_code > 0) {
			$result = Db::getInstance()->execute("
				DELETE FROM `"._DB_PREFIX_."configuration` 
				WHERE `name`='TSHIRTECOMMERCE_PURCHASE_CODE'
			");
			if (!$result) return false;
		}

		return true;
	}

	private static function drop_column()
	{
		$check_design_product_id = false;
		$check_design_product_title_img = false;
		$check_tshirtecommerce_design_type = false;
		$result = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."product`");
		if (!$result) return false;
		foreach ($result as $row) {
			if ($row['Field'] == 'design_product_id') $check_design_product_id = true;
			if ($row['Field'] == 'design_product_title_img') $check_design_product_title_img = true;
			if ($row['Field'] == 'tshirtecommerce_design_type') $check_tshirtecommerce_design_type = true;
		}
		if ($check_design_product_id === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."product` DROP COLUMN `design_product_id`")) return false;
		}
		if ($check_design_product_title_img === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."product` DROP COLUMN `design_product_title_img`")) return false;
		}
		if ($check_tshirtecommerce_design_type === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."product` DROP COLUMN `tshirtecommerce_design_type`")) return false;
		}
		// Drop cart table
		$check_tshirtecommerce_design_cart_img = false;
		$check_tshirtecommerce_design_cart_id = false;
		$check_tshirtecommerce_design_type = false;
		$rel = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."cart_product`");
		if (!$rel) return false;
		foreach ($rel as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_cart_img') $check_tshirtecommerce_design_cart_img = true;
			if ($row['Field'] == 'tshirtecommerce_design_type') $check_tshirtecommerce_design_type = true;
			if ($row['Field'] == 'tshirtecommerce_design_cart_id') $check_tshirtecommerce_design_cart_id = true;
		}
		if ($check_tshirtecommerce_design_cart_img === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."cart_product` DROP COLUMN `tshirtecommerce_design_cart_img`")) return false;
		}
		if ($check_tshirtecommerce_design_type === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."cart_product` DROP COLUMN `tshirtecommerce_design_type`")) return false;
		}
		if ($check_tshirtecommerce_design_cart_id === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."cart_product` DROP COLUMN `tshirtecommerce_design_cart_id`")) return false;
		}
		$check_customization = false;
		$rel_customization = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."customization`");
		foreach ($rel_customization as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_cart_id') $check_customization = true;
		}
		if ($check_customization === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."customization` DROP COLUMN `tshirtecommerce_design_cart_id`")) return false;
		}
		// Drop order_detail table
		$check_tshirtecommerce_design_order_img = false;
		$check_tshirtecommerce_design_order_id  = false;
		$check_tshirtecommerce_design_type  = false;
		$rel_order_detail = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."order_detail`");
		if (!$rel_order_detail) return false;
		foreach ($rel_order_detail as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_order_img') $check_tshirtecommerce_design_order_img = true;
			if ($row['Field'] == 'tshirtecommerce_design_order_id') $check_tshirtecommerce_design_order_id = true;
			if ($row['Field'] == 'tshirtecommerce_design_type') $check_tshirtecommerce_design_type = true;
		}
		if ($check_tshirtecommerce_design_order_img === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."order_detail` DROP COLUMN `tshirtecommerce_design_order_img`")) return false;
		}
		if ($check_tshirtecommerce_design_order_id === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."order_detail` DROP COLUMN `tshirtecommerce_design_order_id`")) return false;
		}
		if ($check_tshirtecommerce_design_type === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."order_detail` DROP COLUMN `tshirtecommerce_design_type`")) return false;
		}

		// return result
		return true;
	}

	public static function remove_menu()
	{
		try {
			// PrestaShop 9 compatible tab removal using Tab class
			// Get all tabs for this module
			$tabs = Db::getInstance()->executeS('SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `module` = "tshirtecommerce"');

			if ($tabs && is_array($tabs)) {
				foreach ($tabs as $tab) {
					try {
						$tabObj = new Tab((int)$tab['id_tab']);
						if (Validate::isLoadedObject($tabObj)) {
							$tabObj->delete();
							// Continue even if individual tab delete fails
						}
					} catch (Exception $e) {
						PrestaShopLogger::addLog('Tshirtecommerce: Error deleting tab ' . $tab['id_tab'] . ': ' . $e->getMessage(), 2);
						// Continue with other tabs
					}
				}
			}

			// Fallback: Clean up any remaining entries directly
			// This ensures orphaned records are removed even if Tab::delete() fails
			try {
				Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'tab_lang` WHERE `id_tab` IN (SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `module` = "tshirtecommerce")');
				Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'tab` WHERE `module` = "tshirtecommerce"');
			} catch (Exception $e) {
				PrestaShopLogger::addLog('Tshirtecommerce: Error in fallback tab cleanup: ' . $e->getMessage(), 2);
			}

			return true; // Always return true to not block uninstall
		} catch (Exception $e) {
			PrestaShopLogger::addLog('Tshirtecommerce: Error in remove_menu: ' . $e->getMessage(), 3);
			return true; // Return true to not block uninstall
		}
	}

	private static function drop_orders_table()
	{
		$check_tshirtecommerce_design_order_id = false;
		$check_tshirtecommerce_design_order_img = false;
		$result = Db::getInstance()->executeS("DESCRIBE `"._DB_PREFIX_."orders`");
		if (!$result) return false;
		foreach ($result as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_order_id') $check_tshirtecommerce_design_order_id = true;
			if ($row['Field'] == 'tshirtecommerce_design_order_img') $check_tshirtecommerce_design_order_img = true;
		}
		if ($check_tshirtecommerce_design_order_id === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."orders` DROP COLUMN `tshirtecommerce_design_order_id`")) return false;
		}
		if ($check_tshirtecommerce_design_order_img === true) {
			if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."orders` DROP COLUMN `tshirtecommerce_design_order_img`")) return false;
		}

		return true;
	}
}