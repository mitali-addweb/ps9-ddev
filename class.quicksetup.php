<?php 

class TshirtecommerceQuicksetup
{
	public function tshirtecommerce_settings_purchase_code($values)
	{
		$values['verified_code'] = 0;
		if (isset($values['purchased_code'])) {
			$values['purchased_code'] = str_replace(' ', '', $values['purchased_code']);
			$url = 'http://updates.tshirtecommerce.com/verify_purchase.php?code='.$values['purchased_code'].'&platform=prestashop';
			$content = Tools::file_get_contents($url);
			if ($content != false) {
				$result = json_decode($content, true);
				if (isset($result['error']) && $result['error'] == 0) {
					$values['verified_code'] = 1;
				}
			}
		}
		
		return $values;
	}

	public function save_settings($params)
	{
		$file = _PS_ROOT_DIR_.'/tshirtecommerce/data/settings.json';

		$data = json_decode(Tools::file_get_contents($file), true);

		$data['verified_code'] = $params['verified_code'];
		$data['purchased_code'] = $params['purchased_code'];

		$settings = json_encode($data);

		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.'/tshirtecommerce/');
		include_once _PS_ROOT_DIR_.'/tshirtecommerce/includes/functions.php';
		$dg = new Dg();
		$dg->WriteFile($file, $settings);
	}

	public function getPurchaseCode()
	{
		if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
			$purchased_code = Configuration::get('TSHIRTECOMMERCE_PURCHASE_CODE');
		} else {
			$purchased_code = Configuration::get(_DB_PREFIX_.'TSHIRTECOMMERCE_PURCHASE_CODE');
		}

		return $purchased_code;
	}
}