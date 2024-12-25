<?php

// for ajax
require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

$params = array();
$tmp = Tools::getValue('params', array());
if (count($tmp)) {
	foreach ($tmp as $key => $value) {
		if (!empty($value) && $value != null) {
			$params[] = $value;
		}
	}
}

$id_product = Tools::getValue('id_product', 0);

if ($id_product > 0) {
	include_once 'class.combination.php';
	$combinations = new TshirtecommerceCombination($id_product, $params, Context::getContext());

	$html = $combinations->genHtml($id_product);

	echo $html;
	return;
}
exit('');
