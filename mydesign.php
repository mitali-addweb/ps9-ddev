<?php 

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

function loadmore($page = 2)
{
	$json = array(
		'continue' => 1,
		'html' => '',
	);

	if (!isset(Context::getContext()->customer->logged) || Context::getContext()->customer->logged != 1) {
		echo json_encode($json);
		return;
	}

	$segment = 16;
	$start = ($page - 1) * $segment;
	$end = $start + $segment;
	
	$html = '';

	if ($page < 2) {
		$json['html'] = '';
		return json_encode($json);
	}

	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
	if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS);
	include_once _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.'includes'.DS.'functions.php';
	$dg = new dg();
	$cache = $dg->cache();
	$user_id = md5(Context::getContext()->customer->id);
	$designs = $cache->get($user_id);

	$site_url = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
    $site_url .= __PS_BASE_URI__;

	if ($start >= count($designs)) {
		$json['continue'] = 0;
		$json['html'] = '';
		return json_encode($json);
	}

	if ($end >= count($designs)) {
		$json['continue'] = 0;
	}

	if (count($designs)) {
		$count = 1;
		foreach ($designs as $key => $design) {
			if ($count >= $start && $count < $end) {
				$params = array(
					'product_id' => sprintf('%s:%s:%s:%s', $user_id, $key, $design['product_id'], $design['product_options']),
					'parent_id' => $design['parent_id']
				);
				$link_edit = Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', $params);

				$html .= '<div id="mydesign-item-'.$key.'" class="mydesign-item">';
				$html .= 	'<span class="iconclear" title="'.Module::getInstanceByName('tshirtecommerce')->l('Delete').'" onclick="removemydesign(\''.$key.'\')"><i class="mmaterial-icons">X</i></span>';
				$html .= 	'<a target="_blank" href="'.$link_edit.'" title="'.$design['title'].'">';
				$html .= 		'<img src="'.$site_url.'/tshirtecommerce/'.$design['image'].'" alt="'.$design['title'].'" />';
				$html .= 	'</a>';
				$html .= '</div>';
			}
			$count++;
		}
	} else {
		$json['continue'] = 0;
		$html .= '<p>'.Module::getInstanceByName('tshirtecommerce')->l('Design not found.').'</p>';
	}

	$json['html'] = $html;

	echo json_encode($json);
	return;
}

function delete($key = '')
{
	$json = array(
		'error' => 0,
		'msg' => Module::getInstanceByName('tshirtecommerce')->l('Deleted successfully!'),
	);

	if (!isset(Context::getContext()->customer->logged) || Context::getContext()->customer->logged != 1) {
		echo json_encode($json);
		return;
	}

	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
	if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS);
	include_once _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.'includes'.DS.'functions.php';
	$dg = new dg();
	$cache = $dg->cache();
	$user_id = md5(Context::getContext()->customer->id);
	$designs = $cache->get($user_id);

	$is_changed = false;
	if (count($designs)) {
		foreach ($designs as $k => &$design) {
			if ($key == $k) {
				unset($designs[$key]);
				$is_changed = true;
				break;
			}
		}
	}
	if ($is_changed === true) {
		$cache->set($user_id, $designs);
	} else {
		$json['error'] = 1;
		$json['msg'] = Module::getInstanceByName('tshirtecommerce')->l('Design not found.');
	}

	echo json_encode($json);
	return;
}

$type = Tools::getValue('type', '');
$key = Tools::getValue('key', '');
$page = Tools::getValue('page', 2);

switch ($type) {
	case 'more':
		loadmore($page);
		break;

	case 'delete':
		delete($key);
		break;
	
	default:
		break;
}
exit;