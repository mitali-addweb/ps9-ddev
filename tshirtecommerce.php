<?php
/**
 * PrestaShop 9 Module - Tshirt Ecommerce
 * Custom Product Designer for PrestaShop
 *
 * @author Tshirtecommerce Team
 * @version 2.1.4
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Load composer autoloader if exists
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load model class
require_once __DIR__ . '/models/tshirtecommerce.php';

class Tshirtecommerce extends Module
{
	protected $_site_url;
	protected $tcustomization;
	protected $print_type;

	private $product_customizable = 0;
	private $product_uploadable_files = 0;
	private $product_text_fields = 0;

	public function __construct()
	{
		$this->name 			= 'tshirtecommerce';
		$this->tab 				= 'front_office_features';
		$this->version 			= '2.1.4';
		$this->author 			= 'Tshirtecommerce Team';
		$this->need_instance 	= 0;
		$this->bootstrap 		= true;
		$this->ajax				= false;

		parent::__construct();

		// Translations updated to use the Symfony translation bridge (PrestaShop 9)
		// Backward compatibility: was $this->trans(...)
		$this->displayName = $this->trans('Prestashop Custom Product Designer', [], 'Modules.Tshirtecommerce.Admin');
		$this->description = $this->trans('Prestashop Custom Product Designer is a system help you create website powerful with custom product online and ecommerce. Website allow your client design before order. A comprehensive solution for printing company with sale T-shirt/apparel designer, Sign board designer, Stamp designer, Smartphone/Tablet/Laptop skin, bottle label designer, Bags, Stickers/Label Designer …', [], 'Modules.Tshirtecommerce.Admin');

		$this->secure_key = hash('sha256', $this->name);
		$this->ps_versions_compliancy = [
			'min' => '1.7.8.0',
			'max' => '9.99.99'
		];

		$this->_site_url = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
		$this->_site_url .= __PS_BASE_URI__;
		$this->tcustomization 	= false;
		$this->btn_position 	= Configuration::get('TSHIRTECOMMERCE_POSITION_BTN');

		// Only run update methods if module is properly installed
		// Skip during install/uninstall/disable to prevent errors
		if ($this->active) {
			try {
				// Check if tabs exist before running updates
				$tab_id = (int)Tab::getIdFromClassName('AdminTshirtecommerce');
				if ($tab_id > 0) {
					$this->updateManual();
					$this->updatePermission();
				}
			} catch (Exception $e) {
				// Silently fail to prevent blocking module operations
				PrestaShopLogger::addLog('Tshirtecommerce constructor error: ' . $e->getMessage(), 2);
			}
		}
	}

	public function translateTexts()
	{
		return array(
			// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
			'customization_text_title' => $this->trans('Product customization', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_save_success' => $this->trans('Saved successfully.', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_upload_success' => $this->trans('Uploaded successfully.', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_upload_error' => $this->trans('An error occurred during the image upload process.', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_remove_success' => $this->trans('Removed successfully.', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_remove_error' => $this->trans('An error occurred while deleting the selected picture.', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_require_text' => $this->trans('(*) is required.', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_help_text' => $this->trans('Do not forget to save your customization to be able to add to cart', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_place_holder' => $this->trans('Your message here.', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_help_max' => $this->trans('250 char. max', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_remove_text' => $this->trans('Remove Text', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_remove_image' => $this->trans('Remove Image', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_save_button' => $this->trans('Save Customization', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_your_custom' => $this->trans('Your customization', [], 'Modules.Tshirtecommerce.Admin'),
			'customization_invalid_message' => $this->trans('Invalid message', [], 'Modules.Tshirtecommerce.Admin'),
		);
	}

	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('leftColumn')
			|| !$this->registerHook('rightColumn')
			|| !$this->registerHook('header')
			|| !$this->registerHook('footer')

			|| !$this->registerHook('actionAdminControllerSetMedia')
			|| !$this->registerHook('actionProductAdd')
			|| !$this->registerHook('actionProductUpdate')
			|| !$this->registerHook('actionUpdateQuantity')
			|| !$this->registerHook('actionCartSave')
			|| !$this->registerHook('actionFrontControllerSetMedia')
			|| !$this->registerHook('actionOrderDetail')
			|| !$this->registerHook('actionBeforeCartUpdateQty')

			|| !$this->registerHook('displayOrderDetail')
			|| !$this->registerHook('displayAdminProductsExtra')
			|| !$this->registerHook('displayProductButtons')
			|| !$this->registerHook('displayLeftColumnProduct')
			|| !$this->registerHook('displayRightColumnProduct')
			|| !$this->registerHook('displayProductTab')
			|| !$this->registerHook('displayProductTabContent')
			|| !$this->registerHook('displayOrderConfirmation')
			|| !$this->registerHook('displayShoppingCart')
			|| !$this->registerHook('displayShoppingCartExtra')
			|| !$this->registerHook('displayAdminOrder')
			|| !$this->registerHook('displayAdminOrderTabOrder')
			|| !$this->registerHook('displayBackOfficeHeader')

			|| !$this->registerHook('displayTCustomDesignButton')

			|| !$this->registerHook('displayShoppingCartFooter')

			|| !$this->registerHook('actionCartSummary')

			|| !TshirtecommerceModel::install()
		) {
			return false;
		}

		return true;
	}

	protected function updateManual()
	{
		// Ensure required hooks are registered. Use Hook::getIdByName for compatibility
		// across PrestaShop versions (PS9 prefers explicit id lookups).
		$hooksToEnsure = [
			'displayCustomerAccount',
			'actionCartSummary',
			'displayShoppingCartFooter',
		];

		foreach ($hooksToEnsure as $hookName) {
			$hookId = Hook::getIdByName($hookName);
			$mod = Hook::getModulesFromHook($hookId, $this->id);
			if (empty($mod) || count($mod) < 1) {
				// keep original behavior of registering the hook if not present
				$this->registerHook($hookName);
			}
		}

		// add menu import
		$parentTabid = Tab::getIdFromClassName('AdminTshirtecommerce');
		$currentid = Tab::getIdFromClassName('AdminTshirtecommerceImport');
		if (!$currentid ) {
		  	$tab = new Tab();
		  	$tab->active = 1;
		  	$tab->class_name = "AdminTshirtecommerceImport";
		  	$tab->name = array();
		  	foreach (Language::getLanguages() as $lang){
		    	$tab->name[$lang['id_lang']] = "Import Data";
		  	}
		  	$tab->id_parent = $parentTabid;
		  	$tab->module = $this->name;
		  	$tab->add();

		  	// PrestaShop 9: Tab::initAccess() is deprecated, use Access class
		  	if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
		  		// PS 9+ uses authorization roles instead of access table
		  		$this->initTabAccessPS9($tab->id);
		  	} else {
		  		// PS < 9 uses Tab::initAccess
		  		Tab::initAccess($tab->id);
		  	}
		}
		// fixed new install
		// re-update parent id with tshirtecommerce module id
		$tab = Tab::getInstanceFromClassName('AdminTshirtecommerceImport');
		if ($tab->id_parent != $parentTabid) {
			$tab->id_parent = $parentTabid;
			$tab->update();
		}
		if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
			// fixed access denice import tab
			$access = new Access();

			$slug = $access->findSlugByIdTab($tab->id);
	        $roles = Db::getInstance()->executeS('
	            SELECT `id_authorization_role`
	            FROM `' . _DB_PREFIX_ . 'authorization_role` t
	            WHERE `slug` LIKE "'.$slug.'%"
	        ');
	        if (count($roles) == 0) {
	        	$roles_au = array('CREATE', 'DELETE', 'UPDATE', 'READ');
	        	foreach ($roles_au as $role) {
	        		$d_role = Db::getInstance()->getValue('SELECT `id_authorization_role` FROM `'._DB_PREFIX_.'authorization_role` WHERE `slug` = "'.$slug.$role.'"');

	        		if ((int)$d_role < 1) {
						Db::getInstance()->insert('authorization_role', array(
							'slug' => $slug.$role
						));
		        	}
	        	}
	        } else {
	        	foreach ($roles as $role) {
		            $access->addAccess(1, $role['id_authorization_role']);
		        }
	        }
	    }

		$check_customization_fields = Db::getInstance()->executeS('DESCRIBE `'._DB_PREFIX_.'customization_field`');
		$check_customization_field = false;
		foreach ($check_customization_fields as $row) {
			if ($row['Field'] == 'tshirtecommerce') {
				$check_customization_field = true;
			}
		}

		if ($check_customization_field === false) {
			Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'customization_field`
				ADD `tshirtecommerce` tinyint(1) DEFAULT 0
			');
		}

		// Extend value column of customization data
		$modified 	= true;
		$shows 		= Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.'customized_data`;');
		if (count($shows) > 0) {
			foreach ($shows as $row) {
				if ($row['Field'] == 'value' && $row['Type'] == 'varchar(255)') {
					$modified = false;
					break;
				}
			}
		}
		if ($modified === false) Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'customized_data` MODIFY `value` varchar(1024)');
	}

    protected function updatePermission()
    {
        // Check if already updated
        $is_updated = Configuration::get('TSHIRTE_UPDATED_ACCESS_FROM_102');
        if (!empty($is_updated)) {
            return;
        }

        // Get profiles
        $profiles = Db::getInstance()->executeS('SELECT `id_profile` FROM `' . _DB_PREFIX_ . 'profile`');
        $profile_ids = array_column($profiles, 'id_profile');

        // Get Tshirtecommerce tabs (only for < PS9)
        $tabs = [];
        if (version_compare(_PS_VERSION_, '9.0.0', '<')) {
            $tabs = Db::getInstance()->executeS('SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `module` = "tshirtecommerce"');
        }
        $tab_ids = array_column($tabs, 'id_tab');

        foreach ($profile_ids as $profile) {

            if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
                // PrestaShop 9.x — only roles, skip `access` table entirely
                Db::getInstance()->execute('
                    INSERT IGNORE INTO `' . _DB_PREFIX_ . 'authorization_role` (`slug`)
                    VALUES
                    ("ROLE_MOD_TSHIRTECOMMERCE_VIEW"),
                    ("ROLE_MOD_TSHIRTECOMMERCE_CONFIGURE")
                ');
            } else {
                // PrestaShop < 9 — use module_access and access tables
                $exists = Db::getInstance()->getValue('
                    SELECT COUNT(*) 
                    FROM `' . _DB_PREFIX_ . 'module_access`
                    WHERE `id_profile` = ' . (int)$profile . '
                ');

                if ($exists < 1) {
                    Db::getInstance()->execute('
                        INSERT INTO `' . _DB_PREFIX_ . 'module_access`
                        (`id_profile`, `id_module`, `perm_view`, `perm_configure`, `perm_uninstall`)
                        VALUES (' . (int)$profile . ', ' . (int)$this->id . ', 1, 0, 0)
                    ');
                }

                // Handle `access` table only for PrestaShop < 9
                foreach ($tab_ids as $tab) {
                    $exists = Db::getInstance()->getValue('
                        SELECT COUNT(*) 
                        FROM `' . _DB_PREFIX_ . 'access`
                        WHERE `id_profile` = ' . (int)$profile . ' 
                        AND `id_tab` = ' . (int)$tab
                    );

                    if (!$exists) {
                        Db::getInstance()->execute('
                            INSERT INTO `' . _DB_PREFIX_ . 'access`
                            (`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`)
                            VALUES (' . (int)$profile . ', ' . (int)$tab . ', 1, 1, 1, 1)
                        ');
                    }
                }
            }
        }

        // Mark as updated
        Configuration::updateValue('TSHIRTE_UPDATED_ACCESS_FROM_102', true);
    }

	public function reset()
	{
		// Reset method to fix tab registration and clear caches
		// Useful after migration from PS 1.6 to PS 9
		try {
			PrestaShopLogger::addLog('Tshirtecommerce: Starting reset...', 1);

			// Remove old tabs (non-blocking)
			TshirtecommerceModel::remove_menu();
			PrestaShopLogger::addLog('Tshirtecommerce: Old tabs removed', 1);

			// Wait a moment for database to settle
			usleep(500000); // 0.5 seconds

			// Recreate tabs with correct structure
			if (!TshirtecommerceModel::insert_menu()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to create new tabs', 3);
				return false;
			}
			PrestaShopLogger::addLog('Tshirtecommerce: New tabs created', 1);

			// Clear PrestaShop caches
			if (method_exists('Tools', 'clearCache')) {
				Tools::clearCache();
			}

			// Clear Symfony cache for PrestaShop 9
			if (file_exists(_PS_ROOT_DIR_ . '/var/cache')) {
				$this->clearSymfonyCache();
			}

			PrestaShopLogger::addLog('Tshirtecommerce: Reset completed successfully', 1);
			return true;
		} catch (Exception $e) {
			PrestaShopLogger::addLog('Tshirtecommerce reset error: ' . $e->getMessage(), 3);
			return false;
		}
	}

	private function clearSymfonyCache()
	{
		try {
			$cache_dirs = [
				_PS_ROOT_DIR_ . '/var/cache/dev',
				_PS_ROOT_DIR_ . '/var/cache/prod'
			];

			foreach ($cache_dirs as $cache_dir) {
				if (is_dir($cache_dir)) {
					$this->recursiveDelete($cache_dir);
				}
			}
		} catch (Exception $e) {
			PrestaShopLogger::addLog('Tshirtecommerce: Cache clear error - ' . $e->getMessage(), 2);
		}
	}

	private function recursiveDelete($dir)
	{
		if (!is_dir($dir)) {
			return;
		}

		$files = array_diff(scandir($dir), ['.', '..']);
		foreach ($files as $file) {
			$path = $dir . '/' . $file;
			is_dir($path) ? $this->recursiveDelete($path) : @unlink($path);
		}

		@rmdir($dir);
	}

	/**
	 * Initialize tab access for PrestaShop 9+
	 * PS9 uses authorization roles instead of the access table
	 *
	 * @param int $tabId
	 * @return bool
	 */
	private function initTabAccessPS9($tabId)
	{
		try {
			if (!$tabId) {
				return false;
			}

			// Get all profiles
			$profiles = Profile::getProfiles($this->context->language->id);
			if (empty($profiles)) {
				return true; // No profiles to set access for
			}

			// For each profile, grant access to the tab
			foreach ($profiles as $profile) {
				$profileId = (int)$profile['id_profile'];

				// PS9: Use authorization roles
				$access = new Access();
				if (method_exists($access, 'findSlugByIdTab')) {
					$slug = $access->findSlugByIdTab($tabId);

					if ($slug) {
						// Create authorization roles for this tab
						$roles = ['CREATE', 'READ', 'UPDATE', 'DELETE'];
						foreach ($roles as $role) {
							$roleSlug = $slug . $role;

							// Check if role exists
							$existingRole = Db::getInstance()->getValue(
								'SELECT `id_authorization_role`
								FROM `'._DB_PREFIX_.'authorization_role`
								WHERE `slug` = "'.pSQL($roleSlug).'"'
							);

							if (!$existingRole) {
								// Create the role
								Db::getInstance()->insert('authorization_role', ['slug' => $roleSlug]);
								$roleId = Db::getInstance()->Insert_ID();
							} else {
								$roleId = $existingRole;
							}

							// Grant role to profile (for superadmin only by default)
							if ($profileId == 1 && $roleId) {
								$access->addAccess($profileId, $roleId);
							}
						}
					}
				}
			}

			return true;
		} catch (Exception $e) {
			PrestaShopLogger::addLog('Tshirtecommerce: initTabAccessPS9 error - ' . $e->getMessage(), 2);
			return false;
		}
	}

	public function disable($force_all = false)
	{
		try {
			// Call parent disable method
			return parent::disable($force_all);
		} catch (Exception $e) {
			PrestaShopLogger::addLog('Tshirtecommerce disable error: ' . $e->getMessage(), 2);
			// Return true to allow disable even if there's an error
			return true;
		}
	}

	public function uninstall()
	{
		try {
			// First uninstall the module hooks
			if (!parent::uninstall()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to uninstall parent', 3);
				return false;
			}

			// Then cleanup database and tabs
			if (!TshirtecommerceModel::uninstall()) {
				PrestaShopLogger::addLog('Tshirtecommerce: Failed to uninstall model', 3);
				return false;
			}

			return true;
		} catch (Exception $e) {
			PrestaShopLogger::addLog('Tshirtecommerce uninstall error: ' . $e->getMessage(), 3);
			return false;
		}
	}

	protected function isSessionStarted()
	{
		if (PHP_SAPI !== 'cli') {
			return session_status() === PHP_SESSION_ACTIVE;
		}
		return false;
	}

	public function getContent()
	{
		if (!$this->active) {
			return '';
		}

		// PrestaShop 9: getAdminLink() already returns the full URL
		// No need to prepend site URL or admin folder
		$url = $this->context->link->getAdminLink('AdminTshirtecommerceSetting');

		Tools::redirectAdmin($url);
	}

	public function prepareNewTab()
	{
		if (!$this->active) {
			return false;
		}

		//global $cookie;

		$img 			= '';
		$price_of_print = 0;
		$product_id 	= (int)Tools::getValue('id_product');

		$product = Db::getInstance()->executeS("
			SELECT `design_product_id`, `design_product_title_img`, `tshirtecommerce_design_type`
			FROM `"._DB_PREFIX_."product`
			WHERE `id_product`=".(int)$product_id
		);

		if (!$product) {
			$product = array();
		}

		$design_product_id = (isset($product[0]) && isset($product[0]['design_product_id'])) ? $product[0]['design_product_id'] : '';
		$design_product_title_img = (isset($product[0]) && isset($product[0]['design_product_title_img'])) ? $product[0]['design_product_title_img'] : '';
		$tshirtecommerce_design_type = (isset($product[0]) && isset($product[0]['tshirtecommerce_design_type'])) ? $product[0]['tshirtecommerce_design_type'] : '';
		$arr = explode('::', $design_product_title_img);

		if (!empty($design_product_title_img)) {
			$temp = explode('::', $design_product_title_img);

			if (count($temp) > 0) {
				if (isset($temp[0]) && isset($temp[1])) {
					$img = "<img alt='".$temp[0]."' class='img-responsive img-thumbnail' src='".$temp[1]."' /><br /><center>".$temp[0]."</center>";
				}

				if (isset($arr[2])) {
					$price_of_print = $arr[2];
				}
			}
		}

		if (session_status() !== PHP_SESSION_ACTIVE) {
    		session_start();
		}

		if ($this->context->employee->id) {
			$_SESSION['is_admin'] = array('login' => 1, 'email' => $this->context->employee->email, 'id' => $this->context->employee->id);
			$_SESSION['admin'] = $_SESSION['is_admin'];
		}

        $ps_product 				= new Product($product_id, false, $this->context->language->id, $this->context->shop->id);
        $product_title 				= $ps_product->name;
        $product_sku 				= $ps_product->reference;
        $product_short_description 	= $ps_product->description_short;
        $product_description 		= $ps_product->description;
        $product_price 				= $ps_product->price;
        $product_min_order 			= $ps_product->minimal_quantity;
		$id_image 					= Image::getCover((int)$product_id);
		$product_thumb 				= '';

        $image_types = ImageType::getImagesTypes('products');
		$image_type = $image_types[0]['name'];

        if (sizeof($id_image) > 0) {
            $image = new Image($id_image['id_image']);
			$product_thumb = $this->context->link->getImageLink(
				$ps_product->link_rewrite,
				$image->getExistingImgPath(),
				$image_type			
			);
        }
        if (strpos($this->_site_url, 'https://') !== false) {
        	$product_thumb = str_replace('http://', 'https://', $product_thumb);
        }

        $chk_design_temp = explode(':', $design_product_id);
        if (count($chk_design_temp) <= 1) {
        	$tshirt_product_url = $this->_site_url.'/tshirtecommerce/admin/index.php?/product/viewmodal/'.$design_product_id;
        } else {
        	$tshirt_product_url = $this->_site_url.'/tshirtecommerce/admin-template.php?user='.$chk_design_temp[0]
        		.'&id='.$chk_design_temp[1].'&product='.$chk_design_temp[2].'&color='.$chk_design_temp[3].'&lightbox=1';
        }

		$this->context->smarty->assign(array(
			'url' 							=> $this->_site_url,
			'product_id' 					=> $product_id,
			'price_of_print' 				=> $price_of_print,
			'tshirtecommerce_design_type' 	=> $tshirtecommerce_design_type,
			'design_product_title_img' 		=> $design_product_title_img,
			'design_product_id' 			=> $design_product_id,
			'img' 							=> $img,
			'tshirt_product_url' 			=> $tshirt_product_url,
			'base_url' 						=> $this->_site_url,
			'language_id' 					=> $this->context->language->id,
			'product_title' 				=> $product_title,
			'product_sku' 					=> $product_sku,
			'product_short_description' 	=> $product_short_description,
			'product_description' 			=> $product_description,
			'product_price'					=> $product_price,
			'product_min_order'				=> $product_min_order,
			'product_thumb'					=> $product_thumb
		));
		return $this->display(__FILE__, 'views/templates/admin/prepare_new_tab.tpl');
	}

	public function hookDisplayBackOfficeHeader()
	{
		if (!$this->active) {
			return '';
		}

		// Set admin session for tshirtecommerce admin panel access
		if (session_status() !== PHP_SESSION_ACTIVE) {
    		session_start();
		}

		if ($this->context->employee->id) {
			$_SESSION['is_admin'] = array('login' => 1, 'email' => $this->context->employee->email, 'id' => $this->context->employee->id);
			$_SESSION['admin'] = $_SESSION['is_admin'];
		}

    	$controller_name = $this->context->controller->controller_name ?? Tools::getValue('controller');
		if (empty($controller_name)) {
			$controller_name = Tools::getValue('controller');
		}

		$extra_display = '';
		if ($controller_name == 'AdminProducts' && !Tools::getIsset('id_product')) {
			$eproducts = Db::getInstance()->executeS('
				SELECT `id_product`
				FROM `'._DB_PREFIX_.'product`
				WHERE `active` = 1 AND `design_product_id` <> ""
			');

			if (empty($eproducts)) {
				$eproducts = array();
			}

			if (count($eproducts) > 0) {
				$this->context->smarty->assign(array(
					'eproducts' => $eproducts,
					'site_url' 	=> $this->_site_url,
				));
				$extra_display .= $this->display(__FILE__, 'views/templates/admin/extra_products_list.tpl');
			}
		}

		$this->context->smarty->assign(array(
			'site_url' => $this->_site_url
		));

		$extra_display .= $this->display(__FILE__, 'views/templates/admin/ajax_update.tpl');

		return $extra_display;
	}

	public function hookActionAdminControllerSetMedia()
	{
		if (!$this->active) {
			return false;
		}

		// Register styles and scripts using compatibility helpers so module works across PS versions.
		$controller = $this->context->controller;
		$this->registerStylesheetCompat(
			$controller,
			'module-tshirtecommerce-prestashop',
			$this->_path . 'tshirtecommerce/prestashop/css/prestashop.css?t=' . time(),
			'all'
		);

		if (!method_exists($controller, 'registerStylesheet')) {
			// legacy fallbacks: older Prestashop versions may require additional v15 assets
			$this->registerStylesheetCompat($controller, 'module-tshirtecommerce-bootstrap', '/tshirtecommerce/prestashop/v15/bootstrap.min.css', 'all');
			$this->registerJavascriptCompat($controller, 'module-tshirtecommerce-bootstrap-js', '/tshirtecommerce/prestashop/v15/bootstrap.min.js');
			$this->registerStylesheetCompat($controller, 'module-tshirtecommerce-v15', '/tshirtecommerce/prestashop/v15/15.css?t='.time(), 'all');
		}
	}

	public function hookDisplayAdminProductsExtra()
	{
		if (!$this->active) {
			return false;
		}

		//global $cookie;

		if (Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product')))) {
    		return $this->prepareNewTab();
		} else {
			$this->context->smarty->assign(array(
				// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
				'tshirtecommerce_warning' => $this->trans('You must save this product before adding design with T-Shirt eCommerce.', [], 'Modules.Tshirtecommerce.Admin')
			));
		}

		if (session_status() !== PHP_SESSION_ACTIVE) {
    		session_start();
		}


		if ($this->context->employee->id) {
			$_SESSION['is_admin'] = array('login' => 1, 'email' => $this->context->employee->email, 'id' => $this->context->employee->id);
			$_SESSION['admin'] = $_SESSION['is_admin'];
		}

        $products[] = (int)Tools::getValue('id_product');
        // PrestaShop 9 uses modern Product method
        $temps = Product::getAttributesColorList($products);

        $colors = array();
        if ($temps != false && count($temps) > 0) {
        	foreach ($temps as $key => $values) {
        		if ((int)Tools::getValue('id_product') == (int)$key) {
        			foreach ($temps[$key] as $value) {
			        	if (isset($value['color'])) {
			        		$colors[] = array(
			        			'color_hex' 	=> str_replace('#', '', $value['color']),
			        			'color_title' 	=> trim($value['name']),
			        			'id_attribute' 	=> (int)$value['id_attribute']
			        		);
			        	}
			        }
        		}
        	}
        }

        $url_product_design_blank = $this->context->link->getAdminLink('AdminTshirtecommerceBuilder', true);
        $url_product_design = $url_product_design_blank.'&task=product/child';

        $this->context->smarty->assign('url_product_design_blank', $url_product_design_blank);
        $this->context->smarty->assign('url_product_design', $url_product_design);

        $this->context->smarty->assign('ps_colors', json_encode($colors, true));

		return $this->display(__FILE__, 'views/templates/admin/tshirtecommerce_product.tpl');
	}

	protected function getAttributesColorList(Array $products, $have_stock = true)
    {
        if (!count($products) > 0) {
        	return array();
        }

        $id_lang = Context::getContext()->language->id;
        $check_stock = !Configuration::get('PS_DISP_UNAVAILABLE_ATTR');
        if (!$res = Db::getInstance()->executeS('
			SELECT pa.`id_product`, a.`color`, pac.`id_product_attribute`, '.($check_stock ? 'SUM(IF(stock.`quantity` > 0, 1, 0))' : '0').' qty, a.`id_attribute`, al.`name`, IF(color = "", a.id_attribute, color) group_by
			FROM `'._DB_PREFIX_.'product_attribute` pa
			'.Shop::addSqlAssociation('product_attribute', 'pa').
            ($check_stock ? Product::sqlStock('pa', 'pa') : '').'
			JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.`id_product_attribute` = product_attribute_shop.`id_product_attribute`)
			JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
			JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
			JOIN `'._DB_PREFIX_.'attribute_group` ag ON (a.id_attribute_group = ag.`id_attribute_group`)
			WHERE pa.`id_product` IN ('.implode(array_map('intval', $products), ',').') AND ag.`is_color_group` = 1
			GROUP BY pa.`id_product`, a.`id_attribute`, `group_by`
			'.($check_stock ? 'HAVING qty > 0' : '').'
			ORDER BY a.`position` ASC;'
        )) {
            return false;
        }

        $colors = array();
        foreach ($res as $row) {
            if (Tools::isEmpty($row['color']) && !@filemtime(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg')) {
            	continue;
            }

            $colors[(int)$row['id_product']][] = array(
            	'id_product_attribute' 	=> (int)$row['id_product_attribute'],
            	'color' 				=> $row['color'],
            	'id_product' 			=> $row['id_product'],
            	'name' 					=> $row['name'],
            	'id_attribute' 			=> $row['id_attribute']
            );
        }

        return $colors;
    }

	public function hookActionProductAdd()
	{
		if (!$this->active) {
			return false;
		}

		if (!Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product')))) {
			// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
			$this->errors[] = $this->context->controller->errors[] = $this->trans('A product must be created before adding features.', [], 'Modules.Tshirtecommerce.Admin');
		}
	}

	public function hookActionProductUpdate($params)
	{
		if (!$this->active) {
			return false;
		}

		//if (isset($_POST['submitAddproduct']) || isset($_POST['submitAddproductAndStay'])) {
		if (Tools::isSubmit('submitAddproduct') || Tools::isSubmit('submitAddproductAndStay')) {
			$product_id 					= (int)Tools::getValue('id_product');
			$design_product_id 				= pSQL(Tools::getValue('design_product_id', ''));
			$design_product_title_img 		= pSQL(Tools::getValue('design_product_title_img', ''));
			$tshirtecommerce_design_type 	= pSQL(Tools::getValue('tshirtecommerce_design_type', ''));

			$result = Db::getInstance()->update('product', array(
				'design_product_id' 			=> $design_product_id,
				'design_product_title_img' 		=> $design_product_title_img,
				'tshirtecommerce_design_type' 	=> $tshirtecommerce_design_type
			), 'id_product='.$product_id);

			if (!$result) {
				return false;
			}
		}
	}

	public function hookDisplayAdminOrder($params)
	{
		if (!$this->active) {
			return false;
		}

		$designs = array();
		$id_order = isset($params['id_order']) ? (int)$params['id_order'] : 0;

		if ($id_order > 0) {
			$order 					= new Order((int)$id_order);
			$id_cart 				= $order->id_cart;
			$id_address_delivery 	= $order->id_address_delivery;
			$id_shop 				= $order->id_shop;
			$id_lang 				= $order->id_lang;
			//$id_customer = $order->id_customer;

			$this->alterCustomizationTable();

			$products = $this->getProducts($order);
			if (!Validate::isLoadedObject($order)) {
                $this->errors[] = $this->context->controller->errors[] = $this->trans('The order cannot be found within your database.', [], 'Modules.Tshirtecommerce.Admin');
				return;
            }

			if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
			if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS);
			include_once _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.'includes'.DS.'functions.php';
			$dg = new dg();

			$rows = Db::getInstance()->executeS('
				SELECT `tshirtecommerce_design_cart_id`, `tshirtecommerce_design_type`, `id_product`
				FROM `'._DB_PREFIX_.'customization`
				WHERE `id_cart` = '.(int)$id_cart
			);

			$downloads = array();
			if (count($rows) > 0) {
				foreach ($rows as $row) {
					$tshirtecommerce_design_order_id = $row['tshirtecommerce_design_cart_id'];
					$tshirtecommerce_design_type = $row['tshirtecommerce_design_type'];

					if (empty($tshirtecommerce_design_type)) {
						$tshirtecommerce_design_type = 'admin';
					}

					$download_link = $this->_site_url.'tshirtecommerce/design.php';
					$cache = $dg->cache($tshirtecommerce_design_type);

					if ($tshirtecommerce_design_type == 'cart') {
						$design = $cache->get($tshirtecommerce_design_order_id);
						$download_link .= '?key='.$tshirtecommerce_design_order_id.'&view=';
					} else {
						$temps = Db::getInstance()->getValue('SELECT `design_product_id` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = '.(int)$row['id_product']);
						$temp = explode(':', $temps);
						if (!empty($temps) && count($temp) > 1) {
							$user_id 		= $temp[0];
							$design_id 		= $temp[1];
							$designs 		= $cache->get($user_id);
							$design 		= isset($designs[$design_id]) ? $designs[$design_id] : array();

							$download_link .= '?idea=1&key='.$temps.'&view=';
						} else {
							$design['images'] = array();
						}
					}

					$downloads[$tshirtecommerce_design_order_id]['link'] = $download_link;
					$downloads[$tshirtecommerce_design_order_id]['images'] = $design['images'];
				}
			}

	        // defind view {Front - Back - Left - Right}
			$views = array(
				// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
				'front' => $this->trans('Front', [], 'Modules.Tshirtecommerce.Admin'),
				'back' 	=> $this->trans('Back', [], 'Modules.Tshirtecommerce.Admin'),
				'left' 	=> $this->trans('Left', [], 'Modules.Tshirtecommerce.Admin'),
				'right' => $this->trans('Right', [], 'Modules.Tshirtecommerce.Admin'),
			);

			// Check store clipart payment
			$api_key 		= '';
	        $msg_store_empty = '';
	        $check_return 	= false;
	        $settings 		= array();
	        $tshirtecommerce_store_status = 0;
	        $file_settings 	= _PS_ROOT_DIR_.'/tshirtecommerce/data/settings.json';
	        $store_rate 	= 0.2; // default is 0.2

	        if (file_exists($file_settings)) {
		        $content = Tools::file_get_contents($file_settings);
		        if ($content != false) {
		        	$settings = json_decode($content, true);
		        	if (isset($settings['store'])) {
		        		if (isset($settings['store']['api'])) {
		        			$api_key = $settings['store']['api'];
		        		}

		            	if (isset($settings['store']['enable']) && isset($settings['store']['verified'])
		            		&& $settings['store']['verified'] == 1  && isset($settings['store']['api'])  && !empty($settings['store']['api'])) {
		            		$tshirtecommerce_store_status = $settings['store']['enable'];
		            	}

		            	if (isset($settings['store']['exchange_rate'])) {
		            		$store_rate = $settings['store']['exchange_rate'];
		            	}
		        	}

		        	$store_temp = array();
				    $cache = $dg->cache('cart');
				    if (!isset($tshirtecommerce_design_order_id) || empty($tshirtecommerce_design_order_id)) {
				    	$tshirtecommerce_design_order_id = '';
				    }
					$design 	= $cache->get($tshirtecommerce_design_order_id);
					$order_item = $design['item'];
					$arts 		= array();
					$prices 	= 0;
					$qty 		= (int)($order_item['qty'] / 10);

					if ($qty < 1) {
						$qty = 1;
					}

					if (isset($design['vector'])) {
						$ids = array();
						$vectors = json_decode($design['vector'], true);

						if (count($vectors) > 0) {
							foreach ($vectors as $view => $items) {
								if (count($items) > 0) {
									foreach ($items as $item) {
										if (isset($item['clipar_type']) && empty($item['clipar_paid'])) {
											if (empty($item['price'])) {
												$item['price'] = 0;
											}

											if (is_string($item['file'])) {
												$file = $item['file'];
											}

											if (isset($item['file']['type'])) {
												$file = $item['file']['type'];
											}

											$arts[$item['clipart_id']] = array(
												'view' 	=> $view,
												'id' 	=> $item['clipart_id'],
												'title'	=> $item['title'],
												'thumb'	=> $item['thumb'],
												'file' 	=> $file,
												'price'	=> $item['price'],
											);

											if (!in_array($item['clipart_id'].'-'.$qty, $ids)) {
												$prices = $prices + ($item['price'] * $qty);
												$ids[] 	= $item['clipart_id'].'-'.$qty;
											}
										}
									}
									// update info after paid
									if (Tools::getIsset('e_order_id') && Tools::getIsset('params') && count($ids)
										&& $tshirtecommerce_design_order_id == Tools::getValue('e_order_id')) {
										$e_order_id = Tools::getValue('e_order_id');
										$params 	= Tools::getValue('params');

										require_once(_PS_MODULE_DIR_.'tshirtecommerce/store.php');
										$store = new Store();

										$store->store_art_update($e_order_id, $params, array(), $api_key);
										$check_return = true;
									}
								}
							}
						}
					}
					$prices = $prices * $store_rate;
					if (count($arts) > 0 && count($settings) > 0 && $check_return == false) {
						if (!isset($settings['store']) || empty($settings['store'])) {
							$msg_store_empty = '<p style="background-color: #fcf8e3;color:#8a6d3b;border: 1px solid #faebcc;padding:14px 12px;margin: 0px;">Your order using arts of <a href="http://store.9file.net/" target="_blank">Store</a>. You missing setup API Store. Please go to <strong>T-Shirt eCommerce > Settings > Configuration > Tab Config</strong> setup store to download file output.</p>';
						} elseif (!isset($settings['store']['api']) || (isset($settings['store']['api']) && empty($settings['store']['api']))) {
							$msg_store_empty = '<p style="background-color: #fcf8e3;color:#8a6d3b;border: 1px solid #faebcc;padding:14px 12px;margin: 0px;">Your order using arts of <a href="http://store.9file.net/" target="_blank">Store</a>. You missing setup API Store. Please go to <strong>T-Shirt eCommerce > Settings > Configuration > Tab Config</strong> setup store to download file output.</p>';
						} else {
							$store_temp['ids'] 		= $ids;
							$store_temp['prices'] 	= $prices;
							$store_temp['arts'] 	= $arts;
							$store_temp['qty'] 		= $qty;
							$store_temp['api_ids_rowid'] = $api_key.'/'.implinklode('_', $store_temp['ids']).'/'.$tshirtecommerce_design_order_id;
							$store_temp['tshirtecommerce_design_order_id'] = $tshirtecommerce_design_order_id;
							$msg_store_empty 		= '';
						}
					}
		        }
		    }

		    $ajax_url = $this->_site_url;

		    // Assign variables to template
			$this->context->smarty->assign(array(
				'downlnoads' 		=> $downloads,
				'views' 			=> $views,
				'store_temp' 		=> $store_temp,
                'msg_store_empty' 	=> $msg_store_empty,
                'ajax_url_store' 	=> $ajax_url.'/modules/tshirtecommerce/',
                'ajax_id_order' 	=> $id_order,
                'site_url' 			=> $ajax_url.'/tshirtecommerce/',
                'tshirtecommerce_store_status' => $tshirtecommerce_store_status,
				'elink' => $edit_link,
			));
			// Update link download by template
			if (count($downloads) > 0) {
				return $this->display(__FILE__, 'views/templates/admin/order_details_extra.tpl');
			}
		}
	}

    protected function getProducts($order)
    {
        $products = $order->getProducts();

        foreach ($products as &$product) {
            if ($product['image'] != null) {
                $name = 'product_mini_'.(int)$product['product_id'].(isset($product['product_attribute_id']) ? '_'.(int)$product['product_attribute_id'] : '').'.jpg';

                // generate image cache, only for back office
                $product['image_tag'] = ImageManager::thumbnail(_PS_IMG_DIR_.'p/'.$product['image']->getExistingImgPath().'.jpg', $name, 45, 'jpg');
                if (file_exists(_PS_TMP_IMG_DIR_.$name)) {
                	$product['image_size'] = getimagesize(_PS_TMP_IMG_DIR_.$name);
                } else {
                	$product['image_size'] = false;
                }
            }
        }
        // Sort an array by key
        ksort($products);

        return $products;
    }

    protected function addJsDef($js_def, $array = array())
    {
        if (is_array($js_def)) {
            foreach ($js_def as $key => $js) {
                $array[$key] = $js;
            }
        } elseif ($js_def) {
            $array[] = $js_def;
        }
    }

	/**
	 * Register stylesheet with compatibility across PrestaShop versions.
	 */
	protected function registerStylesheetCompat($controller, string $id, string $path, string $media = 'all')
	{
		if (method_exists($controller, 'registerStylesheet')) {
			// PS 1.7+ / PS 9 style
			$controller->registerStylesheet($id, $path, ['media' => $media]);
		} elseif (method_exists($controller, 'addCSS')) {
			$controller->addCSS($path, $media);
		} elseif (method_exists($controller, 'addCss')) {
			$controller->addCss($path, $media);
		}
	}

	/**
	 * Register javascript with compatibility across PrestaShop versions.
	 */
	protected function registerJavascriptCompat($controller, string $id, string $path, array $options = [])
	{
		if (method_exists($controller, 'registerJavascript')) {
			$controller->registerJavascript($id, $path, $options);
		} elseif (method_exists($controller, 'addJS')) {
			$controller->addJS($path);
		}
	}

	/**
	 * Get Symfony container in a compatible way across PrestaShop versions.
	 */
	protected function getContainerCompat()
	{
		// If Module base class offers getContainer (PS 1.7+), use it
		if (method_exists($this, 'getContainer')) {
			return $this->getContainer();
		}

		// Fallback: context may expose the container in newer PS versions
		if (isset($this->context) && isset($this->context->container)
			&& is_object($this->context->container)) {
			return $this->context->container;
		}

		// Last resort: PrestaShop adapter class (if available)
		if (class_exists('\\PrestaShop\\PrestaShop\\Adapter\\SymfonyContainer')) {
			return \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
		}

		return null;
	}

	public function hookActionFrontControllerSetMedia($params)
	{
		if (!$this->active) {
			return false;
		}

		$controller = $this->context->controller;
		$this->registerStylesheetCompat($controller, 'module-tshirtecommerce-prestashop-front', $this->_site_url.'/tshirtecommerce/prestashop/css/prestashop.css?t='.time(), 'all');
	}

	public function hookFooter($params)
	{
		if (!$this->active) {
			return false;
		}

		$setting_hide_addtocart = Configuration::get('TSHIRTECOMMERCE_HIDE_ADDTOCART');
		if ($setting_hide_addtocart && $setting_hide_addtocart == 1) {
			$products = Db::getInstance()->executeS('
				SELECT p.`id_product`
				FROM `'._DB_PREFIX_.'product` p
				WHERE p.`active` = 1 AND p.`design_product_id` <> "" AND p.`design_product_id` <> 0
			');
			$array = array();
			if (count($products) > 0) {
				foreach ($products as $product) {
					$array[] = $product['id_product'];
				}

				$this->context->smarty->assign(array('tproduct_hide' => $array));
				return $this->display(__FILE__, 'views/templates/front/footer.tpl');
			}
		}
	}

	public function hookHeader($params)
	{
		if (!$this->active) {
			return false;
		}

		$setting_hide_addtocart = Configuration::get('TSHIRTECOMMERCE_HIDE_ADDTOCART');
		if ($setting_hide_addtocart && $setting_hide_addtocart == 1) {
			// Get all products which using Tshirtecommerce
			$products = Db::getInstance()->executeS('
				SELECT p.`id_product`
				FROM `'._DB_PREFIX_.'product` p
				WHERE p.`active` = 1 AND p.`design_product_id` <> "" AND p.`design_product_id` <> 0
			');
			$array = array();
			if (count($products) > 0) {
				foreach ($products as $product) {
					$array[] = $product['id_product'];
				}

				$this->context->smarty->assign(array('tproduct_hide' => $array));
				return $this->display(__FILE__, 'views/templates/front/footer.tpl');
			}
		}
	}

	public function hookDisplayRightColumnProduct($params)
	{
		if (!$this->active) {
			return false;
		}

        if ($this->btn_position == 0) {
			return $this->displayButtonOnProduct($params);
		}
	}

	public function hookDisplayProductButtons($params)
	{
		if (!$this->active) {
			return false;
		}

        if ($this->btn_position == 1) {
        	return $this->displayButtonOnProduct($params);
        } else {
        	$id_product = isset($params['id_product']) ? (int)$params['id_product'] : (int)Tools::getValue('id_product');
        	$tshirtecommerce_product_id = 0;
	    	$design_product_id = Db::getInstance()->getValue('
				SELECT `design_product_id`
				FROM `'._DB_PREFIX_.'product`
				WHERE `id_product` = '.(int)$id_product
			);
			if (!$design_product_id) $design_product_id = '';

	        if (!empty($design_product_id)) {
	        	$_params = explode(':', $design_product_id);

	        	if (count($_params) > 1) {
	        		$url = Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', array(
						'user' 			=> $_params[0],
						'id' 			=> $_params[1],
						'product_id' 	=> $_params[2],
						'color' 		=> $_params[3],
						'parent_id' 	=> (int)$id_product
					));
					$tshirtecommerce_product_id = $_params[2];
	        	} else {
	        		$url = Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', array(
						'product_id' => $design_product_id,
						'parent_id' => (int)$id_product
					));
					$tshirtecommerce_product_id = $design_product_id;
	        	}
	        }
        	$product_json 		= _PS_ROOT_DIR_.'/tshirtecommerce/data/products.json';
			$products 			= json_decode(Tools::file_get_contents($product_json), true);
			$attributes 		= '';
			$show_attribute 	= 0;
			$quantity_wanted 	= (int)Tools::getValue('quantity_wanted', 1);

			if ($products && isset($products['products']) && count($products['products']) && $tshirtecommerce_product_id > 0) {
				foreach ($products['products'] as $product) {
					if ($product['id'] == $tshirtecommerce_product_id && isset($product['show_attribute']) && $product['show_attribute'] == 1) {
						$show_attribute = 1;
						$attributes = $this->generateHtmlAttributes($id_product, $product, $quantity_wanted, $design_product_id);
						break;
					}
				}
			}
        	$this->context->smarty->assign(array(
				'show_attribute' => 0,
				'attributes' => $attributes
			));
			return $this->display(__FILE__, 'views/templates/front/attributes.tpl');
        }
	}

	public function hookDisplayTCustomDesignButton($params)
	{
		if (!$this->active) {
			return false;
		}

		return $this->displayButtonOnProduct($params, true);
	}

	protected function _getCustomizationFieldsNLabels($product_id, $id_shop = null)
    {
        if (!Customization::isFeatureActive()) {
            return false;
        }

        if (Shop::isFeatureActive() && !$id_shop) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        $check_customization_fields = Db::getInstance()->executeS('DESCRIBE `'._DB_PREFIX_.'customization_field`');
		$check_customization_field = false;
		foreach ($check_customization_fields as $row) {
			if ($row['Field'] == 'tshirtecommerce') {
				$check_customization_field = true;
			}
		}

		if ($check_customization_field === false) {
			Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'customization_field`
				ADD `tshirtecommerce` tinyint(1) DEFAULT 0
			');
		}

        $customizations = array();
        if (($customizations = Db::getInstance()->executeS('
			SELECT `id_customization_field`, `type`, `tshirtecommerce`
			FROM `'._DB_PREFIX_.'customization_field`
			WHERE `id_product` = '.(int)$product_id.'
			ORDER BY `id_customization_field`')) === false) {
            return false;
        }

        if (empty($customizations)) {
            return array();
        }

        return $customizations;
    }

	public function hookActionCartSave($params)
	{
		if (!$this->active || !Validate::isLoadedObject($this->context->cart) || !Tools::getIsset('id_product')) {
			return false;
		}

		//$this->registerHook('actionBeforeCartUpdateQty'); //TODO

		$cart = $this->context->cart;
		if (Tools::getIsset('add')) {
			$mode = 'add';
		} elseif (Tools::getIsset('update')) {
			$mode = 'update';
		} elseif (Tools::getIsset('delete')) {
			$mode = 'delete';
		} else {
			$mode = '';
		}
		$id_cart 				= (int)$cart->id;
		$id_product 			= (int)Tools::getValue('id_product');
		$quantity 				= (int)Tools::getValue('qty', 1);
 		$id_shop 				= isset($cart->id_shop) ? (int)$cart->id_shop : 0;
 		$op						= Tools::getValue('op', 'up');
 		$id_product_attribute 	= (int)Tools::getValue('ipa', 0);
 		$id_address_delivery 	= (int)$cart->id_address_delivery;
 		$print_type 			= Tools::getValue('print_type', '');

 		$this->print_type = $print_type;

 		if ($id_product > 0) {
 			$ps_product = new Product($id_product);
 			$this->product_customizable = $ps_product->customizable;
 			$this->product_uploadable_files = $ps_product->uploadable_files;
 			$this->product_text_fields = $ps_product->text_fields;
 		}

 		$design_product_id = Db::getInstance()->getValue('SELECT `design_product_id` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = '.(int)$id_product);

 		$temp = explode(':', $design_product_id);
 		$tshirtecommerce_design_cart_id_default = (count($temp) > 1 && isset($temp[0])) ? $temp[0] : '';
 		$design_id = (count($temp) > 1 && isset($temp[1])) ? $temp[1] : '';

 		$tshirtecommerce_design_cart_img_default = Db::getInstance()->getValue('
 			SELECT `design_product_title_img`
 			FROM `'._DB_PREFIX_.'product`
 			WHERE `id_product` = '.(int)$id_product
 		);

 		$tshirtecommerce_design_type_default = Db::getInstance()->getValue('
 			SELECT `tshirtecommerce_design_type`
 			FROM `'._DB_PREFIX_.'product`
 			WHERE `id_product` = '.(int)$id_product
 		);

 		$tshirtecommerce_design_cart_id = Tools::getValue('tshirtecommerce_design_cart_id', $tshirtecommerce_design_cart_id_default);
 		$tshirtecommerce_design_cart_img = Tools::getValue('tshirtecommerce_design_cart_img', $tshirtecommerce_design_cart_img_default);
 		$tshirtecommerce_design_type = Tools::getIsset('tshirtecommerce_design_cart_id') ? 'cart' : $tshirtecommerce_design_type_default;

 		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
 		if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS);
		include_once _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.'includes'.DS.'functions.php';
		$dg = new dg();

		// Get printing type
		$printings 					= array();
        $printings_tmp 				= array();
        $printings_json 			= ROOT.DS.'data'.DS.'printings.json';

		if (file_exists($printings_json)) {
        	$printings_content = Tools::file_get_contents($printings_json);
        	if ($printings_content !== false && !empty($printings_content)) {
        		$printings_tmp = json_decode($printings_content, true);
        	}
        }

        if (count($printings_tmp) > 0) {
        	foreach ($printings_tmp as $printing) {
        		$printings[strtolower($printing['printing_code'])] = $printing['title'];
        	}
        }

        $link_view_design = '';
        $cache = $dg->cache($tshirtecommerce_design_type);

        if ($tshirtecommerce_design_type == 'cart') {
        	$design = $cache->get($tshirtecommerce_design_cart_id);
        } else {
        	$designs = $cache->get($tshirtecommerce_design_cart_id_default);
        	$design = $designs[$design_id];
        }
        $images = (isset($design['images']) && count($design['images']) > 0) ? $design['images'] : array();
        $files = array();

        if (count($images) > 0) {
        	foreach ($images as $image) {
        		$file 					= _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.$image;
        		if (!empty($image) && file_exists($file)) {
	        		$file_name 				= md5(uniqid(rand(), true));
	        		$file_normal 			= _PS_UPLOAD_DIR_.$file_name;
	        		$file_small 			= _PS_UPLOAD_DIR_.$file_name.'_small';
	                $product_picture_width 	= (int)Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
	                $product_picture_height = (int)Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
	                $tmp_name 				= tempnam(_PS_TMP_IMG_DIR_, 'PS');

	                @copy($file, $tmp_name);
	                @copy($file, $file_normal);
	                ImageManager::resize($tmp_name, $file_normal);

	                @copy($file, $file_small);
	               	ImageManager::resize($tmp_name, $file_small, $product_picture_width, $product_picture_height);

	                unlink($tmp_name);
	                $files[] = $file_name;
	            }
        	}
        }

        $color 					= isset($design['color']) ? $design['color'] : $design['product_options'];
        $id 					= (isset($design['item']) && isset($design['item']['product_id'])) ? (int)$design['item']['product_id'] : $design['product_id'];
        $options 				= (isset($design['item']) && isset($design['item']['options'])) ? $design['item']['options'] : array();
        $printing_type_value 	= 'screen';

        // Update for custom info on Customization modal
        $attributes 	= array();
	    $eattributes 	= array();
	    $custom_info 	= '';
	    $products_json 	= _PS_ROOT_DIR_.'/tshirtecommerce/data/products.json';
	    $tproducts_temp = json_decode(Tools::file_get_contents($products_json), true);

	    if (Tools::getIsset('tshirtecommerce_color') && Tools::getIsset('tshirtecommerce_product_id')) {
        	$array 	= explode(':', Tools::getValue('tshirtecommerce_product_id'));
        	$id 	= (int)((count($array) > 1 && isset($array[2])) ? $array[2] : Tools::getValue('tshirtecommerce_product_id'));
        	$color 	= Tools::getValue('tshirtecommerce_color');
        	foreach ($tproducts_temp['products'] as $tproduct) {
				if ($tproduct['id'] == $id) {
					if (isset($tproduct['print_type'])) $printing_type_value = $tproduct['print_type'];
					break;
				}
			}
        }

	    if (isset($tproducts_temp['products']) && count($tproducts_temp['products']) && $id > 0) {
	    	foreach ($tproducts_temp['products'] as $tproduct) {
	    		if ($id == $tproduct['id']) {
	    			if (isset($tproduct['design']) && isset($tproduct['design']['color_hex']) && count($tproduct['design']['color_hex']) && isset($tproduct['design']['color_title'])) {
	    				foreach ($tproduct['design']['color_hex'] as $key => $value) {
	    					if (strtoupper($value) == strtoupper($color) && isset($tproduct['design']['color_title'][$key])) {
	    						$custom_info .= sprintf(
	    							'%s: %s, ',
									// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
									$this->trans("Color", [], 'Modules.Tshirtecommerce.Admin'),
	    							(!empty($tproduct['design']['color_title'][$key]) ? $tproduct['design']['color_title'][$key] : $value)
	    						);
	    						break;
	    					}
	    				}
	    			}
	    			$eattributes = $tproduct['attributes'];
	    			break;
	    		}
	    	}
	    }
	    $found_tshirtecommerce_sizes = false; // fixed 56528
	    $array_keys = array('screen', 'dtg', 'sublimation', 'embroidery');
	    if (isset($design['item']) && isset($design['item']['options']) && count($design['item']['options'])) {
	    	foreach ($design['item']['options'] as $key => $value) {
	    		switch ($value['type']) {
	    			case 'textlist':
	    				if (isset($value['value']) && count($value['value'])) {
	    					$custom_info .= sprintf('%s: ', $value['name']);
	    					foreach ($value['value'] as $k => $v) {
	    						$custom_info .= sprintf(' %s-%s; ', $k, $v);
	    						if (isset($eattributes['titles'][$key]) && count($eattributes['titles'][$key]) > 0) {
									foreach ($eattributes['titles'][$key] as $k_ => $v_) {
										if ($v_ == $k && $v > 0) {
											$attributes[$key][$k_] = $v;
										}
									}
								}
	    					}
	    					$custom_info .= ', ';
	    				}
	    				$found_tshirtecommerce_sizes = true; // fixed 56528
	    				break;

	    			case 'checkbox':
	    				$ttemp = array();
	    				if (isset($value['value']) && count($value['value'])) {
	    					$custom_info .= sprintf('%s: ', $value['name']);
	    					foreach ($value['value'] as $k => $v) {
	    						$custom_info .= sprintf('%s; ', $v);
	    						$ttemp[$k] = $k;
	    					}
	    					$custom_info .= ', ';
	    				}
						$attributes[$key] = $ttemp;
	    				break;

	    			case 'selectbox':
	    			case 'radio':
	    				$custom_info .= sprintf('%s: %s, ', $value['name'], $value['value']);
	    				if (isset($eattributes['titles'][$key]) && count($eattributes['titles'][$key]) > 0) {
							foreach ($eattributes['titles'][$key] as $k => $v) {
								if ($v == $value['value']) {
									$attributes[$key] = $k;
								}
							}
						}
	    				break;

	    			default:
	    				break;
	    		}
	    	}
	    } elseif (Tools::getIsset('tattributes')) {
	    	$attributess = Tools::getValue('tattributes');
	    	if (isset($attributess['attribute'])) $attributes = $attributess['attribute'];
	    	if (Tools::getIsset('tshirtecommerce_color')) {
	    		$attributes['tshirtecommerce_color'] = Tools::getValue('tshirtecommerce_color');
	    	}

	    	if (Tools::getIsset('tshirtecommerce_product_id')) {
	    		$attributes['tshirtecommerce_product_id'] = Tools::getValue('tshirtecommerce_product_id');
	    	}
	    }

	    if (isset($printings[strtolower($print_type)])) {
			// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
			$custom_info .= sprintf('%s: %s<br/>', $this->trans('Printing Type', [], 'Modules.Tshirtecommerce.Admin'), $printings[strtolower($print_type)]);
		}

	    if ( isset($design['options']) && $design['options'] != '' && $design['options'] != '[]')
		{
			if (is_string($design['options']))
				$toptions = json_decode( str_replace('\\"', '"', $design['options']), true);
			else
				$toptions = $design['options'];

			if (count($toptions) > 0)
			{

				foreach($toptions as $ii => $option)
				{
					if( is_array($option) )
					{
						foreach ($option as $keys => $value)
						{
							if(is_string($value))
							{
								$custom_info .= $value.'<br />';
								continue;
							}

							if(is_array($value))
							{
								if( isset($value['title']) )
								{
									$custom_info .= $value['title'].': ';

									if( isset($value['value']) )
									{
										$custom_info .= $value['value'].'<br />';
									}
									elseif( isset($value['color']) )
									{
										$custom_info .= $value['color'].'<br />';
									}
								}
							}
						}
					}
					elseif( is_string($option) )
					{
						$custom_info .= $option.'<br />';
					}
				}
			}
		}

	    // update for team & number
	    if (isset($design['item']) && isset($design['item']['teams']) && count($design['item']['teams'])
	    	&& isset($design['item']['teams']['size']) && count($design['item']['teams']['size'])) {
			// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
			$custom_info .= sprintf('%s</br /><table class="tstable">', $this->trans('Team:', [], 'Modules.Tshirtecommerce.Admin'));
			$custom_info .= sprintf('<thead><th>%s</th><th>%s</th><th>%s</th></thead><tbody>', $this->trans('Name', [], 'Modules.Tshirtecommerce.Admin'), $this->trans('Number', [], 'Modules.Tshirtecommerce.Admin'), $this->trans('Size', [], 'Modules.Tshirtecommerce.Admin'));
    		foreach ($design['item']['teams']['size'] as $key => $row) {
    			$size_ = explode('::', $row);
    			$custom_info .= '<tr>';
	    		$custom_info .= sprintf('<td>%s</td><td>%s</td><td>%s</td>',
	    			(isset($design['item']['teams']['name']) && isset($design['item']['teams']['name'][$key])) ? $design['item']['teams']['name'][$key] : '',
	    			(isset($design['item']['teams']['number']) && isset($design['item']['teams']['number'][$key])) ? $design['item']['teams']['number'][$key] : '',
	    			isset($size_[0]) ? $size_[0] : $row
	    		);
	    		$custom_info .= '</tr>';
    		}
	    	$custom_info .= '</tbody></table><br />';
	    }

		$key_temp 	= false;
		$name_temp 	= '';
        if (count($options) > 0) {
	        foreach ($options as $option) {
	        	if ($option['type'] == 'printing') {
	        		$printing_type_value = strtolower($option['value']);
	        		$array_keys = array('screen', 'dtg', 'sublimation', 'embroidery');
	        		if (!in_array($printing_type_value, $array_keys)) {
	        			$name_temp 	= $option['value'];
	        			$key_temp 	= true;
	        		}
	        	}
	        }
	    }
	    $printing_type = isset($printings[$printing_type_value]) ? $printings[$printing_type_value] : '';
	    if ($key_temp === true) {
	    	$printing_type = $name_temp;
	    }

		// only for blank design and template design which added from product desital page (not from designer tool)
		/*
		if (Tools::getIsset('tshirtecommerce_color') && Tools::getIsset('tshirtecommerce_product_id') && Tools::getIsset('tattributes')) {
			$attributess = Tools::getValue('tattributes');
			$attributes = (isset($attributess['attribute'])) ? $attributess['attribute'] : array();
			if (count($attributes)) {
				foreach ($tproducts_temp['products'] as $tproduct) {
					if ($tproduct['id'] == $id && isset($tproduct['attributes']['type']) && count($tproduct['attributes']['type'])) {
						foreach ($tproduct['attributes']['type'] as $key => $row) {
							$custom_info .= sprintf('%s:', isset($tproduct['attributes']['name'][$key]) ? $tproduct['attributes']['name'][$key] : '');
							foreach ($attributes as $k => $v) {
								if ($k == $key) {
									if (is_array($v)) {
										foreach ($v as $idx => $elm) {
											if ($row == 'textlist' && $key == $k) {
												$custom_info .= sprintf(' %s-%s;', (isset($tproduct['attributes']['titles']) && isset($tproduct['attributes']['titles'][$key]) && isset($tproduct['attributes']['titles'][$key][$idx])) ? $tproduct['attributes']['titles'][$key][$idx] : 0, $elm);
											} else {
												$custom_info .= sprintf(' %s;', $tproduct['attributes']['titles'][$key][$idx][$elm]);
											}
										}
									} else {
										$custom_info .= sprintf(' %s;', (isset($tproduct['attributes']['titles']) && isset($tproduct['attributes']['titles'][$key]) && isset($tproduct['attributes']['titles'][$key][$v])) ? $tproduct['attributes']['titles'][$key][$v] : 0);
									}
									$custom_info .= '<br/>';
								}
							}
						}
						break;
					}
				}
			}
		}*/

		// insert to customization if does not exists (3 objects: design-images, printing-type, view-design-link)
		if ($mode == 'add' && $this->tcustomization == false && !Tools::getIsset('id_customization')) {
			if (!empty($tshirtecommerce_design_cart_id)) {
				// Check customization_field
				$customization_field = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'customization_field` WHERE `id_product` = '.(int)$id_product);

				$is_design_image_front 	= false;
				$is_design_image_back 	= false;
				$is_design_image_left 	= false;
				$is_design_image_right 	= false;
				$is_printing_type 		= false;
				$is_link 				= false;
				$is_price_of_design 	= false; // fixed #22543

				foreach ($customization_field as $row) {
					if ($row['tshirtecommerce'] == 1) $is_design_image_front 	= true;
					if ($row['tshirtecommerce'] == 4) $is_design_image_back 	= true;
					if ($row['tshirtecommerce'] == 5) $is_design_image_left 	= true;
					if ($row['tshirtecommerce'] == 6) $is_design_image_right 	= true;
					if ($row['tshirtecommerce'] == 2) $is_printing_type 		= true;
					if ($row['tshirtecommerce'] == 3) $is_link 					= true;
					if ($row['tshirtecommerce'] == 7) $is_price_of_design 		= true;
				}

				$languages = Language::getLanguages();
				if ($is_design_image_front === false) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`, `tshirtecommerce`)
						VALUES ('.(int)$id_product.', '.(int)Product::CUSTOMIZE_FILE.', 0, 1)
					');

					$id_customization_field = (int)Db::getInstance()->Insert_ID();
					$values = '';

					foreach ($languages as $language) {
			            foreach (Shop::getContextListShopID() as $id_shop) {
			                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
			            }
			        }
			        $values = rtrim($values, ', ');

			        Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
						VALUES '.$values
					);
				}

				if ($is_design_image_back === false) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`, `tshirtecommerce`)
						VALUES ('.(int)$id_product.', '.(int)Product::CUSTOMIZE_FILE.', 0, 4)
					');

					$id_customization_field = (int)Db::getInstance()->Insert_ID();
					$values = '';

					foreach ($languages as $language) {
			            foreach (Shop::getContextListShopID() as $id_shop) {
			                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
			            }
			        }
			        $values = rtrim($values, ', ');

			        Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
						VALUES '.$values
					);
				}

				if ($is_design_image_left === false) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`, `tshirtecommerce`)
						VALUES ('.(int)$id_product.', '.(int)Product::CUSTOMIZE_FILE.', 0, 5)
					');
					$id_customization_field = (int)Db::getInstance()->Insert_ID();
					$values = '';

					foreach ($languages as $language) {
			            foreach (Shop::getContextListShopID() as $id_shop) {
			                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
			            }
			        }
			        $values = rtrim($values, ', ');

			        Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
						VALUES '.$values
					);
				}

				if ($is_design_image_right === false) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`, `tshirtecommerce`)
						VALUES ('.(int)$id_product.', '.(int)Product::CUSTOMIZE_FILE.', 0, 6)
					');
					$id_customization_field = (int)Db::getInstance()->Insert_ID();
					$values = '';

					foreach ($languages as $language) {
			            foreach (Shop::getContextListShopID() as $id_shop) {
			                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
			            }
			        }
			        $values = rtrim($values, ', ');

			        Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
						VALUES '.$values
					);
				}

				if ($is_printing_type === false) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`, `tshirtecommerce`)
						VALUES ('.(int)$id_product.', '.(int)Product::CUSTOMIZE_TEXTFIELD.', 0, 2)
					');
					$id_customization_field = (int)Db::getInstance()->Insert_ID();
					$values = '';

					foreach ($languages as $language) {
			            foreach (Shop::getContextListShopID() as $id_shop) {
			                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
			            }
			        }
			        $values = rtrim($values, ', ');

			        Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
						VALUES '.$values
					);
				}

				if ($is_link === false) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`, `tshirtecommerce`)
						VALUES ('.(int)$id_product.', '.(int)Product::CUSTOMIZE_TEXTFIELD.', 0, 3)
					');
					$id_customization_field = (int)Db::getInstance()->Insert_ID();
					$values = '';

					foreach ($languages as $language) {
			            foreach (Shop::getContextListShopID() as $id_shop) {
			                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
			            }
			        }
			        $values = rtrim($values, ', ');

			        Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
						VALUES '.$values
					);
				}

				// fixed #22543
				if ($is_price_of_design === false) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field` (`id_product`, `type`, `required`, `tshirtecommerce`)
						VALUES ('.(int)$id_product.', '.(int)Product::CUSTOMIZE_TEXTFIELD.', 0, 7)
					');
					$id_customization_field = (int)Db::getInstance()->Insert_ID();
					$values = '';

					foreach ($languages as $language) {
			            foreach (Shop::getContextListShopID() as $id_shop) {
			                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
			            }
			        }
			        $values = rtrim($values, ', ');

			        Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
						VALUES '.$values
					);
				}

				// Update the name of customizations
				$cusfields = Db::getInstance()->executeS('
					SELECT *
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` <> 0
					LIMIT 7
				');

				if (count($cusfields) > 0) {
					foreach ($cusfields as $row) {
						switch ($row['tshirtecommerce']) {
							case 1:
								// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
								$name = $this->trans('Front', [], 'Modules.Tshirtecommerce.Admin');
								break;

							case 2:
								$name = $this->trans('Custom Info', [], 'Modules.Tshirtecommerce.Admin');
								break;

							case 3:
								$name = $this->trans('Link', [], 'Modules.Tshirtecommerce.Admin');
								break;

							case 4:
								$name = $this->trans('Back', [], 'Modules.Tshirtecommerce.Admin');
								break;

							case 5:
								$name = $this->trans('Left', [], 'Modules.Tshirtecommerce.Admin');
								break;

							case 6:
								$name = $this->trans('Right', [], 'Modules.Tshirtecommerce.Admin');
								break;

							case 7:
								$name = $this->trans('Custom Price', [], 'Modules.Tshirtecommerce.Admin');

							default:
								break;
						}

						Db::getInstance()->update('customization_field_lang', array(
							'name' => $name),
							'`id_customization_field` = '.(int)$row['id_customization_field'].
							' AND `id_lang` = '.(int)$this->context->language->id.
							' AND `id_shop` = '.(int)$id_shop
						);
					}
				}

				// add or update customization
				$index_design_image_front = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` = 1
				');

				$index_printing = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` = 2
				');

				$index_link = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` = 3
				');

				$index_design_image_back = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` = 4
				');

				$index_design_image_left = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` = 5
				');

				$index_design_image_right = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` = 6
				');

				$index_price_of_design = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$id_product.'
						AND `tshirtecommerce` = 7
				');

				$text_value_printing_type = $custom_info;

				// get view design link
				$link_cart = $this->context->link->getModuleLink('tshirtecommerce', 'designer', array(
					'color' 		=> $color,
					'product_id' 	=> $id,
					'parent_id' 	=> $id_product,
					'cart_id' 		=> $tshirtecommerce_design_cart_id
				));

				$link_user = $this->context->link->getModuleLink('tshirtecommerce', 'designer', array(
					'user' 		=> $tshirtecommerce_design_cart_id,
					'id' 		=> $design_id,
					'color' 	=> (count($temp) > 1 && isset($temp[3])) ? $temp[3] : '',
					'product_id' => (count($temp) > 1 && isset($temp[2])) ? $temp[2] : 0,
					'parent_id' => $id_product
				));

				if ($tshirtecommerce_design_type == 'cart') {
					// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
					$text_value_link = '<a class="tselink" rowid="'.$tshirtecommerce_design_cart_id.'" href="'.$link_cart.'" target="_blank">'.$this->trans('Edit Design', [], 'Modules.Tshirtecommerce.Admin').'</a>';
				} elseif (Tools::getIsset('tshirtecommerce_product_id') && Tools::getIsset('tshirtecommerce_color')) {
					$is_template = explode(':', Tools::getValue('tshirtecommerce_product_id'));
					if (count($is_template) > 1) {
						$link_user = $this->context->link->getModuleLink('tshirtecommerce', 'designer', array(
							'user' => $is_template[0],
							'id' => $is_template[3],
							'color' => Tools::getIsset('tshirtecommerce_color'),
							'product_id' => (count($temp) > 1 && isset($temp[2])) ? $temp[2] : 0,
							'parent_id' => $id_product
						));
						$text_value_link = '<a class="tselink" rowid="'.Tools::getValue('tshirtecommerce_product_id').'" href="'.$link_user.'">'.$this->trans('Edit Design', [], 'Modules.Tshirtecommerce.Admin').'</a>';
					} else {
						$text_value_link = '';
					}
				} else {
					$text_value_link = '<a class="tselink" rowid="'.$tshirtecommerce_design_cart_id.'" href="'.$link_user.'">'.$this->trans('Edit Design', [], 'Modules.Tshirtecommerce.Admin').'</a>';
				}

				// fixed 56528
				if ($found_tshirtecommerce_sizes) {
					$text_value_link .= '<span class="found_tshirtecommerce_sizes" style="display:none"></span>';
				}

				// update values to database
				Db::getInstance()->execute(
	                'INSERT INTO `'._DB_PREFIX_.'customization` (`id_cart`, `id_product`, `id_product_attribute`, `id_address_delivery`, `quantity`, `in_cart`)
					VALUES ('.(int)$id_cart.', '.(int)$id_product.', '.(int)$id_product_attribute.', '.(int)$id_address_delivery.', '.(int)$quantity.', 1)'
	            );

	            $id_customization = Db::getInstance()->Insert_ID();

	            $this->alterCustomizationTable();
	            $this->updateCustomizationTable($id_customization, $id_address_delivery, $id_cart, $id_product, $tshirtecommerce_design_cart_id, $tshirtecommerce_design_type);

	            if ($id_customization > 0 || Tools::getIsset('tshirtecommerce_color') || count($attributes)) {
		           	if (count($files) > 0) {
						foreach ($files as $idx => $file) {
							switch ($idx) {
								case 0:
									Db::getInstance()->execute('
						            	INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`)
										VALUES ('.(int)$id_customization.', '.(int)Product::CUSTOMIZE_FILE.', '.(int)$index_design_image_front.', \''.pSQL($file).'\')
						            ');
									break;

								case 1:
									Db::getInstance()->execute('
						            	INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`)
										VALUES ('.(int)$id_customization.', '.(int)Product::CUSTOMIZE_FILE.', '.(int)$index_design_image_back.', \''.pSQL($file).'\')
						            ');
									break;

								case 2:
									Db::getInstance()->execute('
						            	INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`)
										VALUES ('.(int)$id_customization.', '.(int)Product::CUSTOMIZE_FILE.', '.(int)$index_design_image_left.', \''.pSQL($file).'\')
						            ');
									break;

								case 3:
									Db::getInstance()->execute('
						            	INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`)
										VALUES ('.(int)$id_customization.', '.(int)Product::CUSTOMIZE_FILE.', '.(int)$index_design_image_right.', \''.pSQL($file).'\')
						            ');
									break;

								default:
									break;
							}
						}
					}

					// convert price with currency
					$price_of_design = $this->getPriceOfDesign($id_product, $id_customization, $tshirtecommerce_design_cart_id, $tshirtecommerce_design_cart_img, $tshirtecommerce_design_type, $quantity, $cart->id_currency, $attributes, $id_product_attribute, $id_address_delivery);

		        	/*if ($price_of_design != '') {
		        		$string = sprintf('%s_%s_%s_%s', $id_product, $id_product_attribute, $id_customization, $id_address_delivery);
		        		$text_value_link .= '<style>input[name="quantity_'.$string.'"]{pointer-events: none;}#cart_quantity_down_'.$string.',#cart_quantity_up_'.$string.'{display:none!important}</style>';
		        	}*/

		            Db::getInstance()->execute('
		            	INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`)
						VALUES ('.(int)$id_customization.', '.(int)Product::CUSTOMIZE_TEXTFIELD.', '.(int)$index_printing.', \''.$text_value_printing_type.'\')
		            ');

		            if (!empty($price_of_design)) {
			             Db::getInstance()->execute('
			            	INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`)
							VALUES ('.(int)$id_customization.', '.(int)Product::CUSTOMIZE_TEXTFIELD.', '.(int)$index_price_of_design.', \''.$price_of_design.'\')
			            ');
			        }

		            Db::getInstance()->execute('
		            	INSERT INTO `'._DB_PREFIX_.'customized_data` (`id_customization`, `type`, `index`, `value`)
						VALUES ('.(int)$id_customization.', '.(int)Product::CUSTOMIZE_TEXTFIELD.', '.(int)$index_link.', \''.$text_value_link.'\')
		            ');

		        }

		        // Set cache of feature detachable to true
		        // Fixed can not display image design and printing type in customization region after add to cart from designer tool
		        Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', '1');

		        // Update value for cart_product table
	            Db::getInstance()->update('cart_product', array(
	            	'tshirtecommerce_design_cart_id' 	=> $tshirtecommerce_design_cart_id,
	            	'tshirtecommerce_design_cart_img' 	=> $tshirtecommerce_design_cart_img,
	            	'tshirtecommerce_design_type' 		=> $tshirtecommerce_design_type
	            ), '`id_cart` 					= '.(int)$id_cart.'
	            	AND `id_product` 			= '.(int)$id_product.'
	            	AND `id_address_delivery` 	= '.(int)$id_address_delivery.'
	            	AND `id_product_attribute` 	= '.(int)$id_product_attribute
	            );
			}
		} elseif ($mode == 'delete' && Tools::getIsset('id_customization')) {
			$id_customization = Tools::getValue('id_customization');
			Db::getInstance()->delete('customized_data', 'id_customization='.(int)$id_customization);
			Db::getInstance()->delete('customization', 'id_customization='.(int)$id_customization);
		}

		$this->tcustomization = true;

		// remove customizations files for this product
		//Db::getInstance()->delete('customization_field', 'tshirtecommerce IN (1, 2, 3, 4, 5, 6, 7)');
		if ($id_product > 0 && $this->tcustomization === true) {
			Db::getInstance()->update('product', array(
				'customizable' => 1, //$this->product_customizable,
				//'uploadable_files' => $this->product_uploadable_files,
				//'text_fields' => $this->product_text_fields
			), 'id_product='.(int)$id_product);
			Db::getInstance()->update('product_shop', array(
            	'customizable' => 1, //$this->product_customizable,
            	//'uploadable_files' => $this->product_uploadable_files,
            	//'text_fields' => $this->product_text_fields
            ),'id_product='.(int)$id_product .' AND id_shop='.(int)$id_shop);
		}
	}

	protected function updateCustomizationTable($id_customization, $id_address_delivery, $id_cart, $id_product, $tdci, $tdt = 'cart')
	{
		Db::getInstance()->update('customization', array(
			'tshirtecommerce_design_cart_id' => $tdci,
			'tshirtecommerce_design_type' => $tdt),
			'`id_customization` = '.(int)$id_customization.' AND
			`id_address_delivery` = '.(int)$id_address_delivery.' AND
			`id_cart` = '.(int)$id_cart.' AND
			`id_product` = '.(int)$id_product
		);
	}

	protected function alterCustomizationTable()
	{
		$check_tshirtecommerce_design_cart_id = false;
		$check_tshirtecommerce_design_type = false;

		$rows = Db::getInstance()->executeS('DESCRIBE `'._DB_PREFIX_.'customization`');
		foreach ($rows as $row) {
			if ($row['Field'] == 'tshirtecommerce_design_cart_id') $check_tshirtecommerce_design_cart_id = true;
			if ($row['Field'] == 'tshirtecommerce_design_type') $check_tshirtecommerce_design_type = true;
		}

		if($check_tshirtecommerce_design_cart_id === false) {
			Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'customization`
				ADD `tshirtecommerce_design_cart_id` VARCHAR(255) NOT NULL DEFAULT "0"
			');
		}

		if($check_tshirtecommerce_design_type === false) {
			Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'customization`
				ADD `tshirtecommerce_design_type` VARCHAR(255) NOT NULL DEFAULT ""
			');
		}
	}

	protected function getPriceOfDesign($id_product, $id_customization, $tshirtecommerce_design_cart_id, $tshirtecommerce_design_cart_img, $tshirtecommerce_design_type, $quantity = 1, $id_currency = '', $attributes = array(), $id_product_attribute = 0, $id_address_delivery = 0)
	{
		$price_of_design = 0;
		//if (empty($tshirtecommerce_design_cart_id) || empty($tshirtecommerce_design_type)) return 0; // removed since 1.0.3
		$product = new Product((int)$id_product);

		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce');
        include_once ROOT.DS.'includes'.DS.'functions.php';
        $dg = new dg();

        $data = array();
        if ($tshirtecommerce_design_type == 'cart') {
        	$cache 				= $dg->cache('cart');
        	$design 			= $cache->get($tshirtecommerce_design_cart_id);
        	$data 				= $design;
            $data['colors'] 	= array($design['color']);
            $data['product_id'] = $design['item']['product_id'];
            $data['quantity'] 	= $quantity;
            $data['artStore'] 	= array();
            //$data['ps_taxes'] 	= $product->getTaxesRate(new Address($id_address_delivery, $this->context->language->id));
            $data['ps_taxes'] = 0;
            $evector 			= isset($design['vector']) ? $design['vector'] : '';

            if (!empty($evector)) {
                $json_evector = json_decode($evector, true);
                if (count($json_evector)) {
                    foreach ($json_evector as $view => $items) {
                        if (count($items)) {
                            foreach ($items as $item) {
                                if (isset($item['clipar_type']) && $item['clipar_type'] == 'store') {
                                    $data['artStore'][] = isset($item['clipart_id']) ? $item['clipart_id'] : $item['id'];
                                }
                            }
                        }
                    }
                }
            }
            $data['attribute'] = $attributes;
            if (!empty($this->print_type)) {
            	$data['print_type'] = $this->print_type;
            }

            $data['elements'] = (isset($design['print']) && isset($design['print']['elements'])) ? $design['print']['elements'] : array();

            $tshirtecommerce_prices = @$dg->prices($data, true);
            if ($tshirtecommerce_prices->item > 0) {
                $price_of_design += $tshirtecommerce_prices->item;
            } else {
                $price_of_design += $tshirtecommerce_prices->printing;
            }
        } elseif (count($attributes) && isset($attributes['tshirtecommerce_color']) && isset($attributes['tshirtecommerce_product_id'])) {
        	$array = explode(':', $attributes['tshirtecommerce_product_id']);
        	$data['colors'] = array($attributes['tshirtecommerce_color']);
        	$data['product_id'] = (int)(count($array) > 1 ? $array[2] : $attributes['tshirtecommerce_product_id']);
        	$data['quantity'] = $quantity;
        	$data['artStore'] = array();
        	//$data['ps_taxes'] = $product->getTaxesRate(new Address($id_address_delivery, $this->context->language->id));
        	$data['ps_taxes'] = 0;
        	$data['attribute'] = $attributes;
        	if (!empty($this->print_type)) {
            	$data['print_type'] = $this->print_type;
            }
        	$data['print'] = array();

        	$tshirtecommerce_prices = @$dg->prices($data, true, true);
        	if ($tshirtecommerce_prices->item > 0) {
                $price_of_design += $tshirtecommerce_prices->item;
            } else {
                $price_of_design += $tshirtecommerce_prices->printing;
            }
        } else {
            $temp = explode('::', $tshirtecommerce_design_cart_img);
            if (count($temp) > 1 && isset($temp[2])) {
                $price_of_design += (int)$temp[2];
            }
        }

        if (!defined('_PS_PRICE_DISPLAY_PRECISION_')) {
	        define('_PS_PRICE_DISPLAY_PRECISION_', 2);
	    }
	    if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) {
	        define('_PS_PRICE_COMPUTE_PRECISION_', _PS_PRICE_DISPLAY_PRECISION_);
	    }

        if (!empty($id_currency)) {
        	$price_of_design = Tools::ps_round($price_of_design, _PS_PRICE_COMPUTE_PRECISION_);
        	if (!empty($id_currency)) {
        		$currency = new Currency($id_currency);
        	} else {
        		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        	}
        	$currency->prefix = trim($currency->prefix);
        	$currency->suffix = trim($currency->suffix);

        	if (!empty($currency->prefix)) {
        		$price_of_design = '<span id="tshirtecommercedesignunitprice_'.$id_product.'_'.$id_customization.'">'.$currency->prefix.$price_of_design.'</span>';
        	} elseif (!empty($currency->suffix)) {
        		$price_of_design = '<span id="tshirtecommercedesignunitprice_'.$id_product.'_'.$id_customization.'">'.$price_of_design.$currency->suffix.'</span>';
        	}
        }

		return $price_of_design;
	}

	public function hookDisplayShoppingCart($params)
	{
		if (!$this->active || !Validate::isLoadedObject($this->context->cart)) {
			return false;
		}

		// Fixd change currecy. Refer ticket #24373 (v1.0.3)
		$unit_price = array();
		$str_span = 'tshirtecommercedesignunitprice_';
		$cart = $params['cart'];

		if (isset($cart->id_currency)) {
			$id_currency = $cart->id_currency;
			$currency = new Currency($id_currency);
		} else {
			$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		}
		$currency->prefix 	= trim($currency->prefix);
        $currency->suffix 	= trim($currency->suffix);
        $char_currency 		= (!empty($currency->prefix)) ? $currency->prefix : $currency->suffix;

        $currencys 			= Currency::getCurrencies();
		$char_currencys 	= array();
		$cconversion_rate 	= 1;
		foreach ($currencys as $crow) {
			if (!empty($crow['sign'])) {
				$char_currencys[] = $crow['sign'];

				if ($char_currency == $crow['sign']) {
					$cconversion_rate = $crow['conversion_rate'];
				}
			}
		}

		$type = (int)Product::CUSTOMIZE_TEXTFIELD;

		$products = $cart->getProducts();
		foreach ($products as $product) {
			if (isset($product['id_customization']) && $product['id_customization'] > 0) {
				$index = Db::getInstance()->getValue('
					SELECT `id_customization_field`
					FROM `'._DB_PREFIX_.'customization_field`
					WHERE `id_product` = '.(int)$product['id_product'].' AND `tshirtecommerce` = 7
				');

				$tshirtecommerce_design_price_str = Db::getInstance()->getValue('
					SELECT `value`
					FROM `'._DB_PREFIX_.'customized_data`
					WHERE `id_customization` = '.(int)$product['id_customization'].' AND `type` = '.$type.' AND `index` = '.(int)$index.'
				');

				$uprice = trim(pSQL($tshirtecommerce_design_price_str));
				if (count($currencys) <= 1) {
					$_uprice = trim(str_replace($char_currency, '', $uprice));
				} else {
					$_uprice 		= trim(str_replace($char_currencys, '', $uprice));
					$old_currency 	= str_replace($_uprice, '', $uprice);
					$ccurrent_rate 	= 1;
					foreach ($currencys as $crow) {
						if ($old_currency == $crow['sign']) {
							$ccurrent_rate = $crow['conversion_rate'];
							break;
						}
					}
					if (trim($char_currency) != trim($old_currency)) {
						$_uprice = (float)$_uprice * (float)$cconversion_rate / (float)$ccurrent_rate;
					}
				}

				if (!defined('_PS_PRICE_DISPLAY_PRECISION_')) {
			        define('_PS_PRICE_DISPLAY_PRECISION_', 2);
			    }
			    if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) {
			        define('_PS_PRICE_COMPUTE_PRECISION_', _PS_PRICE_DISPLAY_PRECISION_);
			    }
				$_uprice = Tools::ps_round($_uprice, _PS_PRICE_COMPUTE_PRECISION_);

				if (!empty($currency->prefix)) {
					$_uprice = $currency->prefix.$_uprice;
				} else {
					$_uprice = $_uprice.$currency->suffix;
				}

				$unit_price[$str_span.$product['id_product'].'_'.$product['id_customization']] = $_uprice;
			}
		}

		$edit_link = $this->context->link->getModuleLink('tshirtecommerce', 'designer', array(
			'color' => $designs['color'],
			'product_id' => $designs['item']['product_id'],
			'parent_id' => $row['id_product'],
			'cart_id' => $row['tshirtecommerce_design_cart_id']
		));
		$this->context->smarty->assign('elink', $edit_link);

		$this->context->smarty->assign('unit_price', $unit_price);
		return $this->display(__FILE__, 'views/templates/front/extra_shopping_cart.tpl');
	}

	public function hookDisplayOrderDetail($params)
	{
		if (!$this->active) {
			return false;
		}

		$order 		= $params['order'];

		$id_order 	= $order->id;
		$id_cart 	= $order->id_cart;
		$id_address_delivery = $order->id_address_delivery;
		$id_shop 	= $order->id_shop;
		$id_lang 	= $order->id_lang;
		$id_customer = $order->id_customer;

		if (!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}

 		if (!defined('ROOT')) {
 			define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS);
 		}

		include_once _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.'includes'.DS.'functions.php';
		$dg = new dg();

		$rows = Db::getInstance()->executeS('
			SELECT `tshirtecommerce_design_cart_id`, `tshirtecommerce_design_type`, `id_product`
			FROM `'._DB_PREFIX_.'customization`
			WHERE `id_cart` = '.(int)$id_cart
		);

		$downloads = array();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$tshirtecommerce_design_order_id = $row['tshirtecommerce_design_cart_id'];
				$tshirtecommerce_design_type = $row['tshirtecommerce_design_type'];
				$download_link = $this->_site_url.'tshirtecommerce/design.php';

				if (empty($tshirtecommerce_design_type)) {
					$tshirtecommerce_design_type = 'admin';
				}

				$cache = $dg->cache($tshirtecommerce_design_type);
				if ($tshirtecommerce_design_type == 'cart') {
					$design = $cache->get($tshirtecommerce_design_order_id);
					$download_link .= '?key='.$tshirtecommerce_design_order_id.'&view=';
				} else {
					$temps = Db::getInstance()->getValue('
						SELECT `design_product_id`
						FROM `'._DB_PREFIX_.'product`
						WHERE `id_product` = '.(int)$row['id_product']
					);

					$temp = explode(':', $temps);
					$design_id = $temp[1];
					$designs = $cache->get($tshirtecommerce_design_order_id);
					$design = isset($designs[$design_id]) ? $designs[$design_id] : array();

					$download_link .= '?idea=1&key='.$temps.'&view=';
				}

				$downloads[$tshirtecommerce_design_order_id]['images'] = isset($design['images']) ? $design['images'] : array();
				$downloads[$tshirtecommerce_design_order_id]['link'] = $download_link;
			}
		}

		// get store clipart payment
		$api_key 						= '';
        $msg_store_empty 				= '';
        $check_return 					= false;
        $settings 						= array();
        $tshirtecommerce_store_status 	= 0;
        $file_settings 					= _PS_ROOT_DIR_.'/tshirtecommerce/data/settings.json';

	    if (file_exists($file_settings)) {
	        $content = Tools::file_get_contents($file_settings);
	        if ($content != false) {
	            $settings = json_decode($content, true);
	            if (isset($settings['store'])) {
	            	if (isset($settings['store']['api'])) {
	            		$api_key = $settings['store']['api'];
	            	}

	            	if (isset($settings['store']['enable']) && isset($settings['store']['verified'])
	            		&& $settings['store']['verified'] == 1 && isset($settings['store']['api']) && !empty($settings['store']['api'])) {
	            		$tshirtecommerce_store_status = $settings['store']['enable'];
	            	}
	        	}
	        }
	    }

	    $store_temp = array();
	    $cache 		= $dg->cache('cart');
	    if (isset($tshirtecommerce_design_order_id))
			$design = $cache->get($tshirtecommerce_design_order_id);
		else
			$design = array();
		$order_item = $design['item'];
		$arts 		= array();
		$prices 	= 0;
		$qty 		= (int) ($order_item['qty'] / 10);

		if ($qty < 1) {
			$qty = 1;
		}

		if (isset($design['vector'])) {
			$ids = array();
			$vectors = json_decode($design['vector'], true);

			if (count($vectors) > 0) {
				foreach ($vectors as $view => $items) {
					if (count($items) > 0) {
						foreach ($items as $item) {
							if (isset($item['clipar_type']) && empty($item['clipar_paid'])) {
								if (empty($item['price'])) {
									$item['price'] = 0;
								}

								if (is_string($item['file'])) {
									$file = $item['file'];
								}

								if (isset($item['file']['type'])) {
									$file = $item['file']['type'];
								}

								$arts[$item['clipart_id']] = array(
									'view' 	=> $view,
									'id' 	=> $item['clipart_id'],
									'title'	=> $item['title'],
									'thumb'	=> $item['thumb'],
									'file' 	=> $file,
									'price'	=> $item['price'],
								);

								if (!in_array($item['clipart_id'].'-'.$qty, $ids)) {
									$prices = $prices + ($item['price'] * $qty);
									$ids[] = $item['clipart_id'].'-'.$qty;
								}
							}
						}

						// update info after paid
						if (Tools::getIsset('e_order_id') && Tools::getIsset('params') && count($ids) > 0
							&& $tshirtecommerce_design_order_id == Tools::getValue('e_order_id')) {
							$e_order_id = Tools::getValue('e_order_id');
							$params = Tools::getValue('params');

							require_once(_PS_MODULE_DIR_.'tshirtecommerce/store.php');
							$store = new Store();

							$store->store_art_update($e_order_id, $params, array(), $api_key);
							$check_return = true;
						}
					}
				}
			}
		}

		if (count($arts) > 0 && count($settings) > 0 && $check_return == false) {
			if (!isset($settings['store']) || empty($settings['store'])) {
				$msg_store_empty = '<p style="background-color: #fcf8e3;color:#8a6d3b;border: 1px solid #faebcc;padding:14px 12px;margin: 0px;">Your order using arts of <a href="http://store.9file.net/" target="_blank">Store</a>. You missing setup API Store. Please go to <strong>T-Shirt eCommerce > Settings > Configuration > Tab Config</strong> setup store to download file output.</p>';
			} elseif (!isset($settings['store']['api']) || (isset($settings['store']['api']) && empty($settings['store']['api']))) {
				$msg_store_empty = '<p style="background-color: #fcf8e3;color:#8a6d3b;border: 1px solid #faebcc;padding:14px 12px;margin: 0px;">Your order using arts of <a href="http://store.9file.net/" target="_blank">Store</a>. You missing setup API Store. Please go to <strong>T-Shirt eCommerce > Settings > Configuration > Tab Config</strong> setup store to download file output.</p>';
			} else {
				$store_temp['ids'] 		= $ids;
				$store_temp['prices'] 	= $prices;
				$store_temp['arts'] 	= $arts;
				$store_temp['qty'] 		= $qty;
				//'$store_temp['api'].'/'.implode('_', $store_temp['ids']).'/'.$design->rowid
				$store_temp['api_ids_rowid'] = $api_key.'/'.implode('_', $store_temp['ids']).'/'.$tshirtecommerce_design_order_id;
				$store_temp['tshirtecommerce_design_order_id'] = $tshirtecommerce_design_order_id;
				$msg_store_empty 		= '';
			}
		}

		// get status allow download design
		$str_allow_download_design = Configuration::get('TSHIRTECOMMERCE_DOWNLOADABLE');
        if (empty($str_allow_download_design)) {
        	$allow_download_design = 0;
        } else {
        	$tmp = explode('::', $str_allow_download_design);
        	$allow_download_design = isset($tmp[1]) ? $tmp[1] : 0;
        }

        // defind view {Front - Back - Left - Right}
		$views = array(
			// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
			'front' => $this->trans('Front', [], 'Modules.Tshirtecommerce.Admin'),
			'back' 	=> $this->trans('Back', [], 'Modules.Tshirtecommerce.Admin'),
			'left' 	=> $this->trans('Left', [], 'Modules.Tshirtecommerce.Admin'),
			'right' => $this->trans('Right', [], 'Modules.Tshirtecommerce.Admin'),
		);

		$shop_email = isset($params['email']) ? $params['email'] : strval(Configuration::get('PS_SHOP_EMAIL'));

		$this->context->smarty->assign(array(
			'downlnoads' 					=> $downloads,
			'views' 						=> $views,
			'allow_download_design' 		=> $allow_download_design,
			'tshirtecommerce_store_status' 	=> $tshirtecommerce_store_status,
            'store_temp'					=> $store_temp,
            'msg_store_empty' 				=> $msg_store_empty,
            'shop_email' 					=> $shop_email,
		));

		if (count($downloads) > 0) {
			return $this->display(__FILE__, 'views/templates/front/extra_detail_order_history.tpl');
		}
	}

	protected function displayButtonOnProduct($params , $custom_hook = false)
	{
		// phinq - fixed enable module for customer group
    	$group_enable_module = false;
		$id_group = Group::getCurrent()->id;
		$authorized_modules = Module::getAuthorizedModules($id_group);
		if (count($authorized_modules)) {
			$has_active_module = 0;
			foreach ($authorized_modules as $row) {
				if ($row['id_module'] == $this->id) {
					$has_active_module++;
					break;
				}
			}
			if ($has_active_module > 0) {
				$group_enable_module = true;
			}
		}
		if ($group_enable_module === false) {
			return false;
		} // - end fixed

		$tshirtecommerce_product_id = 0;
    	$id_product = isset($params['id_product']) ? (int)$params['id_product'] : (int)Tools::getValue('id_product');
    	$design_product_id = Db::getInstance()->getValue('
			SELECT `design_product_id`
			FROM `'._DB_PREFIX_.'product`
			WHERE `id_product` = '.(int)$id_product
		);
		if ($design_product_id == false) $design_product_id = '';

        if (!empty($design_product_id)) {
        	$_params = explode(':', $design_product_id);

        	if (count($_params) > 1) {
        		$url = Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', array(
					'user' 			=> $_params[0],
					'id' 			=> $_params[1],
					'product_id' 	=> $_params[2],
					'color' 		=> $_params[3],
					'parent_id' 	=> (int)$id_product
				));
				$tshirtecommerce_product_id = $_params[2];
        	} else {
        		$url = Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', array(
					'product_id' 	=> $design_product_id,
					'parent_id' 	=> (int)$id_product
				));
				$tshirtecommerce_product_id = $design_product_id;
        	}

        	if (Configuration::get('PS_SSL_ENABLED')) {
        		$url = str_replace('http://', 'https://', $url);
        	}
        }

        $tshirtecommerce_custom_text = Configuration::get('TSHIRTECOMMERCE_CUSTOM_TEXT');
        if (empty($tshirtecommerce_custom_text)) {
		// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
		$tshirtecommerce_custom_text = $this->trans('Custom Your Design', [], 'Modules.Tshirtecommerce.Admin');
        }

        $tshirtecommerce_custom_style 	= Configuration::get('TSHIRTECOMMERCE_CUSTOM_STYLE');
        $is_class 						= 1;
        $is_class_temp 					= explode(':', $tshirtecommerce_custom_style);
        if (count($is_class_temp) > 1) {
        	$is_class = 0;
        }

        // Fixed #23973
		$setting_hide_addtocart = Configuration::get('TSHIRTECOMMERCE_HIDE_ADDTOCART');
		$allow_hide_addtocart = 0;
		if ($setting_hide_addtocart && $setting_hide_addtocart == 1) {
			$allow_hide_addtocart = 1;
		}

		// Hide customization of Tshirtecommerce
		$is_hide_all 			= true;
		$customizations_hide 	= array();
		$customizations 		= $this->_getCustomizationFieldsNLabels($id_product);
		$customization_array 	= array(1, 2, 3, 4, 5, 6, 7);
		if (count($customizations) > 0 && is_array($customizations)) {

			foreach ($customizations as $row) {
				if (in_array($row['tshirtecommerce'], $customization_array)) {
					if ($row['type'] == (int)Product::CUSTOMIZE_TEXTFIELD) {
				 		$customizations_hide[] = sprintf('textField%s', $row['id_customization_field']);
				 	} elseif ($row['type'] == (int)Product::CUSTOMIZE_FILE) {
				 		$customizations_hide[] = sprintf('file%s', $row['id_customization_field']);
				 	}
				} else {
					$is_hide_all = false;
				}
			 }
		}

		/* Update for tshirtecommerce attributes */
		$product_json 		= _PS_ROOT_DIR_.'/tshirtecommerce/data/products.json';
		$products 			= json_decode(Tools::file_get_contents($product_json), true);
		$attributes 		= '';
		$show_attribute 	= 0;
		$quantity_wanted 	= (int)Tools::getValue('quantity_wanted', 1);
		if ($this->btn_position == 1 && $products && isset($products['products']) && count($products['products']) && $tshirtecommerce_product_id > 0) {
			foreach ($products['products'] as $product) {
				if ($product['id'] == $tshirtecommerce_product_id && isset($product['show_attribute']) && $product['show_attribute'] == 1) {
					$show_attribute = 1;
					$attributes = $this->generateHtmlAttributes($id_product, $product, $quantity_wanted, (isset($design_product_id) ? $design_product_id : ''));
					break;
				}
			}
		}

		$this->context->smarty->assign(array(
			'site_url' 						=> $this->_site_url,
			'url' 							=> isset($url) ? $url : '',
			'design_product_id' 			=> isset($design_product_id) ? $design_product_id : '',
			'is_class' 						=> $is_class,
			'tshirtecommerce_custom_text' 	=> $tshirtecommerce_custom_text,
			'tshirtecommerce_custom_style' 	=> $tshirtecommerce_custom_style,
			'allow_hide_addtocart' 			=> $allow_hide_addtocart,
			'show_attribute' 				=> 0,
			'attributes' 					=> $attributes,
			'is_hide_all_customization' 	=> $is_hide_all,
			'customizations_hide' 			=> $customizations_hide,
			'custom_hook' 					=> $custom_hook,
		));
		$this->id_display_btton = true;
		return $this->display(__FILE__, 'views/templates/front/button_custom_design.tpl');
	}

	protected function generateHtmlAttributes($id_product, $product = array(), $quantity_wanted = 0, $design_product_id = '')
    {
    	$html 					= '';
    	$address 				= new Address();
    	$tax_manager 			= TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, Context::getContext()));
        $price_tax_calculator 	= $tax_manager->getTaxCalculator();
        $use_tax = Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC');
        if (Tax::excludeTaxeOption()) {
            $use_tax = false;
        }

    	if (count($product)) {
    		$html .= '<form id="tshirtecommerce-attributes-form">';
    		if (isset($product['id'])) {
    			$html .= '<input type="hidden" name="tshirtecommerce_product_id" value="'.((strpos($design_product_id, ':') === false) ? $product['id'] : $design_product_id).'" />';
    		}
    		if (isset($product['design']) && count($product['design']) && isset($product['design']['color_hex']) && count($product['design']['color_hex'])) {
    			$html .= '<div class="clearfix tshirtecommerce-attribute-group">';
				// Backward compatibility: replaced $this->trans(...) with $this->trans(...)
				$html .= '<label class="control-label tshirtecommerce-color-label"><strong>'.$this->trans('Color', [], 'Modules.Tshirtecommerce.Admin').'</strong></label>';
    			foreach ($product['design']['color_hex'] as $key => $hex) {
    				$html .= '<a onclick="tshirtecommerceSelectColor(this)" color="'.$hex.'" href="javascript:void(0)" class="tshirtecommerce-color '.($key == 0 ? 'tactive' : '').'" style="background-color:#'.$hex.'!important;" title="'.((isset($product['design']) && isset($product['design']['color_title']) && isset($product['design']['color_title'][$key])) ? $product['design']['color_title'][$key] : '').'"></a>';
    				$html .= '<input style="display:none!important;" name="tshirtecommerce_color" class="tshirtecommerce-radio" type="radio" value="'.$hex.'" '.($key == 0 ? 'checked' : '').' />';
    			}
    			$html .= '</div>';
    		} /* @todo: echo print for each color */

    		if (isset($product['attributes']) && count($product['attributes']) && $product['attributes']['type']) {
    			foreach ($product['attributes']['type'] as $key => $type) {
    				$html .= '<div class="clearfix tshirtecommerce-attribute-group">';
    				$html .= '<label class="control-label tshirtecommerce-color-label"><strong>'.((isset($product['attributes']['name']) && isset($product['attributes']['name'][$key])) ? $product['attributes']['name'][$key] : '').'</strong></label>';
    				switch ($type) {
    					case 'textlist':
    						if (isset($product['attributes']['titles']) && count($product['attributes']['titles'])
    							&& isset($product['attributes']['titles'][$key]) && count($product['attributes']['titles'][$key])) {
    							$html .= '<link rel="stylesheet" href="'.$this->_site_url.'/tshirtecommerce/prestashop/css/product.css?t='.time().'" type="text/css" media="all">';
    							$is_update_qty = false;
    							foreach ($product['attributes']['titles'][$key] as $k => $v) {
    								$html .= '<div class="tshirtecommerce-sizes">';
    								$html .= '<span>'.$v.'</span>';
    								if (isset($product['attributes']['prices']) && count($product['attributes']['prices'])
										&& isset($product['attributes']['prices'][$key]) && count($product['attributes']['prices'][$key])
										&& !empty($product['attributes']['prices'][$key][$k]) && $product['attributes']['prices'][$key][$k] != 0) {
										$currency = Context::getContext()->currency;
										if ($use_tax) {
											$price_ = $price_tax_calculator->addTaxes($product['attributes']['prices'][$key][$k]);
										} else {
											$price_ = $product['attributes']['prices'][$key][$k];
										}
										$price = Tools::displayPrice(Tools::convertPrice($price_, $currency), $currency);
										$html .= sprintf(' <span class="tshirtecommerce-attr-price">(%s%s)</span>', ($product['attributes']['prices'][$key][$k] > 0) ? '+' : '', $price);
									}
    								$html .= '<br/>';
    								if ($is_update_qty === false) {
    									$html .= '<input onchange="tshirtecommerceQtyChange()"  class="tshirtecommerce-size-control" type="text" name="tattributes['.$key.']['.$k.']" value="'.$quantity_wanted.'" />';
    									$is_update_qty = true;
    								} else {
    									$html .= '<input onchange="tshirtecommerceQtyChange()"  class="tshirtecommerce-size-control" type="text" name="tattributes['.$key.']['.$k.']" value="0" />';
    								}
    								$html .= '</div>';
    							}
    						}
    						break;

    					case 'selectbox':
    						if (isset($product['attributes']['titles']) && count($product['attributes']['titles'])
    							&& isset($product['attributes']['titles'][$key]) && count($product['attributes']['titles'][$key])) {
    							$html .= '<select name="tattributes['.$key.']" class="tshirtecommerce-form-control">';
    							foreach ($product['attributes']['titles'][$key] as $k => $v) {
    								$html .= '<option value="'.$k.'">'.$v;
    								if (isset($product['attributes']['prices']) && count($product['attributes']['prices'])
										&& isset($product['attributes']['prices'][$key]) && count($product['attributes']['prices'][$key])
										&& !empty($product['attributes']['prices'][$key][$k]) && $product['attributes']['prices'][$key][$k] != 0) {
										$currency = Context::getContext()->currency;
										if ($use_tax) {
											$price_ = $price_tax_calculator->addTaxes($product['attributes']['prices'][$key][$k]);
										} else {
											$price_ = $product['attributes']['prices'][$key][$k];
										}
										$price = Tools::displayPrice(Tools::convertPrice($price_, $currency), $currency);
										$html .= sprintf(' (%s%s)', ($product['attributes']['prices'][$key][$k] > 0) ? '+' : '', $price);
									}
    								$html .= '</option>';
    							}
    							$html .= '</select>';
    						}
    						break;

    					case 'radio':
    					case 'checkbox':
    						if (isset($product['attributes']['titles']) && count($product['attributes']['titles'])
    							&& isset($product['attributes']['titles'][$key]) && count($product['attributes']['titles'][$key])) {
    							foreach ($product['attributes']['titles'][$key] as $k => $v) {
    								$html .= '<label class="control-label tshirtecommerce-color-label">';
    								if ($type == 'checkbox') {
    									$html .= '<input class="tshirtecommerce-form-control" type="'.$type.'" name="tattributes['.$key.']['.$k.']" value="'.$k.'" /> '.$v;
    								} else {
    									$html .= '<input class="tshirtecommerce-form-control" type="'.$type.'" name="tattributes['.$key.']"  value="'.$k.'" /> '.$v;
    								}
    								if (isset($product['attributes']['prices']) && count($product['attributes']['prices'])
										&& isset($product['attributes']['prices'][$key]) && count($product['attributes']['prices'][$key])
										&& !empty($product['attributes']['prices'][$key][$k]) && $product['attributes']['prices'][$key][$k] != 0) {
										$currency = Context::getContext()->currency;
										if ($use_tax) {
											$price_ = $price_tax_calculator->addTaxes($product['attributes']['prices'][$key][$k]);
										} else {
											$price_ = $product['attributes']['prices'][$key][$k];
										}
										$price = Tools::displayPrice(Tools::convertPrice($price_, $currency), $currency);
										$html .= sprintf(' <span class="tshirtecommerce-attr-price">(%s%s)</span>', ($product['attributes']['prices'][$key][$k] > 0) ? '+' : '', $price);
									}
    								$html .= '</label>';
    							}
    						}
    						break;

    					default:
    						break;
    				}
    				$html .= '</div>';
    			}
    		}
    		$html .= '</form>';
    	}

    	return $html;
    }

    public function hookActionBeforeCartUpdateQty($params)
    {
    	// waiting for update
    }

    public function hookDisplayCustomerAccount($params)
    {
    	if (!$this->active) {
			return false;
		}

		$mydesignlink = Context::getContext()->link->getModuleLink('tshirtecommerce', 'mydesign', array());

		$this->context->smarty->assign(array(
			'site_url' => $this->_site_url,
			'mydesignlink' => $mydesignlink,
		));
		return $this->display(__FILE__, 'views/templates/front/btnmydesign.tpl');
    }

    public function hookActionCartSummary($params)
    {
    	$currency = new Currency($this->context->cart->id_currency);
		$currency->prefix = trim($currency->prefix);
    	$currency->suffix = trim($currency->suffix);
		$char_currency = (!empty($currency->prefix)) ? $currency->prefix : $currency->suffix;

		$currencys = Currency::getCurrencies();
		$char_currencys = array();
		$cconversion_rate = 1;

		foreach ($currencys as $crow) {
			if (!empty($crow['sign'])) {
				$char_currencys[] = $crow['sign'];
				if ($char_currency == $crow['sign']) $cconversion_rate = $crow['conversion_rate'];
			}
		}

    	$base_total_tax_inc = $this->context->cart->getOrderTotal(true);
        $base_total_tax_exc = $this->context->cart->getOrderTotal(false);

        $total_tax = $base_total_tax_inc - $base_total_tax_exc;

        if ($total_tax < 0) {
            $total_tax = 0;

            $summary = array(
	    		'total_tax' => $total_tax,
	    	);

	    	return $summary;
        }

        $prices = Db::getInstance()->executeS('
			SELECT cd.`value`, c.`quantity`, c.`id_product`
			FROM `'._DB_PREFIX_.'customized_data` cd
			INNER JOIN `'._DB_PREFIX_.'customization` c ON cd.id_customization = c.id_customization
			INNER JOIN `'._DB_PREFIX_.'customization_field` cf ON cf.id_customization_field = cd.index
			WHERE c.id_cart = '.(int)$this->context->cart->id.' AND c.in_cart = 1 AND cf.tshirtecommerce = 7
		');
		if ($prices === false || $prices == null) $prices = array();

		if (count($prices)) {
			$price_added = 0;

			foreach ($prices as $value) {
				if (count($currencys) <= 1) {
					$price_of_design = trim(str_replace($char_currency, '', pSql($value['value'])));
				} else {
					$price_of_design = trim(str_replace($char_currencys, '', pSql($value['value'])));
					$old_currency = str_replace($price_of_design, '', pSql($value['value']));
					$ccurrent_rate = 1;
					foreach ($currencys as $crow) {
						if ($old_currency == $crow['sign']) {
							$ccurrent_rate = $crow['conversion_rate'];
							break;
						}
					}
					if (trim($char_currency) != trim($old_currency)) {
						$price_of_design = $price_of_design * $cconversion_rate / $ccurrent_rate;
					}
				}

				$product = new Product($value['id_product'], true, $this->context->language->id, $this->context->shop->id);
				$taxes = isset($product->tax_rate) ? $product->tax_rate : 0;

				$price_of_design = $price_of_design - ($price_of_design * 100 / ($taxes + 100));

				$price_added += (float)$price_of_design * (int)$value['quantity'];
			}

			// update tax
			$total_tax += $price_added;
		}

    	$summary = array(
    		'total_tax' => $total_tax,
    	);

    	if ($total_tax < 0) $total_tax = 0;

    	return $summary;
    }

    public function hookDisplayShoppingCartFooter()
    {
    	$this->context->smarty->assign(array(
			'site_url' => $this->_site_url,
		));
		return $this->display(__FILE__, 'views/templates/front/image.tpl');
    }

	// Missing hook methods for PrestaShop 9 compatibility

	public function hookLeftColumn($params)
	{
		// Left column hook - can be used to display module content in left column
		return '';
	}

	public function hookRightColumn($params)
	{
		// Right column hook - can be used to display module content in right column
		return '';
	}

	public function hookActionUpdateQuantity($params)
	{
		// Hook called when product quantity is updated
		// Can be used to handle custom logic when stock changes
		return true;
	}

	public function hookActionOrderDetail($params)
	{
		// Hook called on order detail page
		// Can be used to add custom information to order details
		return true;
	}

	public function hookDisplayLeftColumnProduct($params)
	{
		// Display hook for left column on product page
		return '';
	}

	public function hookDisplayProductTab($params)
	{
		// Hook to add custom tab on product page
		return '';
	}

	public function hookDisplayProductTabContent($params)
	{
		// Hook to display content in custom product tab
		return '';
	}

	public function hookDisplayOrderConfirmation($params)
	{
		// Hook displayed on order confirmation page
		// Can show customization details after order is placed
		return '';
	}

	public function hookDisplayShoppingCartExtra($params)
	{
		// Extra content in shopping cart
		return '';
	}

	public function hookDisplayAdminOrderTabOrder($params)
	{
		// Hook to add tab in admin order detail page
		return '';
	}
}
