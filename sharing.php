<?php 

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

class TshirtecommerceSharing 
{
	public static function getShareUrl($data)
	{
		$sharing_url = Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', array(
						'product_id' => $data['design'],
						'parent_id' => (int)$data['parent_id']
					));;

		return $sharing_url;
	}
}