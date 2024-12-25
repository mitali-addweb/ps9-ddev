<?php

class TshirtecommerceMydesignModuleFrontController extends ModuleFrontController
{
    public $auth = true;

    public $display_column_left = false;
	public $display_column_right = false;

	public function __construct()
    {
        parent::__construct();

        if (Configuration::get('PS_SSL_ENABLED')) {
            $this->ssl = true;
        }
    }

    public function initContent()
    {
        parent::initContent();

        $site_url = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $site_url .= __PS_BASE_URI__;

        $this->context->smarty->assign(array(
            'site_url' => $site_url,
            'designs' => $this->getDesigns(),
            'design_default_link' => Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', array()),
            'mydesign_ajax_link' => $site_url.'/modules/tshirtecommerce/mydesign.php'
        ));

        $this->setTemplate('module:tshirtecommerce/views/templates/front/mydesign.tpl');
    }

    protected function getDesigns()
    {
    	$segment = 16;
    	$json = array(
    		'continue' => 1,
    		'html' => ''
    	);
    	$html = '';

    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS);
		include_once _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.'includes'.DS.'functions.php';
		$dg = new dg();
		$cache = $dg->cache();
		$user_id = md5(Context::getContext()->customer->id);
		$designs = $cache->get($user_id);

		$site_url = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $site_url .= __PS_BASE_URI__;

		if (count((array)$designs)) {
			$count = 1;
			foreach ($designs as $key => $design) {
				if ($count <= $segment) {
					$params = array(
						'product_id' => sprintf('%s:%s:%s:%s', $user_id, $key, $design['product_id'], $design['product_options']),
						'parent_id' => $design['parent_id'],

					);
					$link_edit = Context::getContext()->link->getModuleLink('tshirtecommerce', 'designer', $params);

					$html .= '<div id="mydesign-item-'.$key.'" class="mydesign-item">';
					$html .= 	'<span class="iconclear" title="'.$this->module->l('Delete').'" onclick="removemydesign(\''.$key.'\')"><i class="mmaterial-icons">X</i></span>';
					$html .= 	'<a target="_blank" href="'.$link_edit.'" title="'.$design['title'].'">';
					$html .= 		'<img src="'.$site_url.'/tshirtecommerce/'.$design['image'].'" alt="'.$design['title'].'" />';
					$html .= 	'</a>';
					$html .= '</div>';
				}
				$count++;
			}
		} else {
			$html = '';
		}

		if (count((array)$designs) <= $segment) {
			$json['continue'] = 0;
		}

		$json['html'] = $html;

    	return $json;
    }
}
