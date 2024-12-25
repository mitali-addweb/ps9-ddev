<?php 

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

include_once 'class.quicksetup.php';
$tsequicksetup = new TshirtecommerceQuicksetup();

$json = array('error' => 0, 'msg' => 'success');

$task = Tools::getValue('task', '');
$purchase_code = Tools::getValue('code', '');

switch ($task) {
	case 'verify':
		if (!empty($purchase_code)) {
			
			$purchased_code = str_replace(' ', '', $purchase_code);
			$options 		= array('purchased_code' => $purchased_code);
			$values 		= $tsequicksetup->tshirtecommerce_settings_purchase_code($options);

			if (isset($values['verified_code']) && $values['verified_code'] == 1) {
				/* save purchsed code */
				$settings['verified_code'] 	= 1;
				$settings['purchased_code'] = $purchased_code;

				$tsequicksetup->save_settings($settings);
			} else {
				$json['error'] = 1;
				$json['msg'] = 'Your purchased code incorrect. Please check your purchased code and try again.'; 
			}
		} else {
			$json['error'] = 1;
			$json['msg'] = 'Please add your purchased code.'; 
		}

		break;
	
	case 'import':
		break;

	default:
		break;
}

echo json_encode($json);
exit;