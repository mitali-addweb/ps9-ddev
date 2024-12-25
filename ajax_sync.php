<?php 

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');


include_once 'class.update.php';
$tseupdater = new TshirtecommerceUpdater(); 

$type = Tools::getValue('type');

$version = $tseupdater->getData('version');

switch ($type) {
	case 'update':
		$json = array('error' => 0, 'msg' => 'You have updated to lastest version.');

		if (isset($version['update']) && $version['update'] == 1) {
			// update data
			$tseupdater->printing();

			// remove data from version.json
			unset($version['update']);
			unset($version['about']);
			unset($version['changelog']);
			$tseupdater->saveData('version', $version);

			// update message output
			$json['msg'] = 'Updater completed.';
		}

		// for sync+update other datas
		// need create file .php put to /modules/tshirtecommerce/hook/ folder
		// not is class - only php code for update
		// can call prestashop functions/library and TshirtecommerceUpdater class
		foreach (glob(dirname(__FILE__)."/hook/*.php") as $filename) {
			if (strpos($filename, '.php') !== false) { 
				include_once dirname(__FILE__).'/hook/'.$filename;
			}
		}

		echo json_encode($json);

		break;

	case 'init':
	default:
		$html = '';

		if (isset($version['update']) && $version['update'] == 1) {
			$html .= '<p><strong>'.$version['about'].'</strong></p>';
			$html .= '<a href="'.$version['changelog'].'" target="_bank" class="btn btn-default">ChangeLog</a><br/><br/>';
			$html .= '<div class="alert alert-warning" role="alert"><strong>We are strongly recommended that you backup your site before processing. Are you sure you wish to run the updater now?</strong></div>';
		}
		echo $html;

		break;
}
exit;