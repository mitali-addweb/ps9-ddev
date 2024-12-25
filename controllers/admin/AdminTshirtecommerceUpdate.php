<?php
/**
 * Admin Controller for Tshirtecommerce Update
 * PrestaShop 9 Compatible
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminTshirtecommerceUpdateController extends ModuleAdminController
{
	public function __construct()
	{
		$this->lang       = false;
		$this->bootstrap  = true;
        $this->display    = 'view';
        $this->module     = 'tshirtecommerce';

        parent::__construct();

        if (Tools::isSubmit('profitability_conf') || Tools::isSubmit('submitOptionsconfiguration')) {
            $this->fields_options = $this->getOptionFields();
        }
	}

	public function setMedia($isNewTheme = false)
	{
		parent::setMedia($isNewTheme);
		// PrestaShop 9: Add CSS using addCSS method
		$this->addCSS(_MODULE_DIR_ . 'tshirtecommerce/views/css/tshirtecommerce.css');
	}

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->trans('T-Shirt eCommerce Update', [], 'Modules.Tshirtecommerce.Admin').' ('.$this->trans('Using version', [], 'Modules.Tshirtecommerce.Admin').' '.$this->module->version.')';
        parent::initPageHeaderToolbar();
        if ($this->meta_title != null) {
            array_pop($this->meta_title);
        }
    }

	public function initContent()
    {
        $this->renderView();
        parent::initContent();
    }    public function renderView()
    {
    	$error_warning  = '';
        $error_info     = '';
        $versions       = array();
        $file           = 'http://updates.tshirtecommerce.com/prestashop/updates.json';
        $content        = Tools::file_get_contents($file);

        if ($content !== false) {
            $ver_temp = json_decode($content);

            if (count($ver_temp) > 0) {
                foreach ($ver_temp as &$value) {
                    $_content = Tools::file_get_contents('http://updates.tshirtecommerce.com/prestashop/'.$value->info);
                    $value->content = $_content;
                }

                // just get 3 recent latest version
                $versions = array_slice($ver_temp, 0, 3);
            }
        } else {
            $error_info = $this->trans('No version of Prestashop Custom Product Designer was found to update.', [], 'Modules.Tshirtecommerce.Admin');
        }

    	if (!ini_get('allow_url_fopen')) {
			$error_warning = 'Your server not support <strong>allow_url_fopen</strong>. Please upload your hosting with <strong>allow_url_fopen = ON</strong>. Click <a href="http://tshirtecommerce.com/wp-content/uploads/2015/04/allow_url_fopen.png" target="_blank"><strong>HERE</strong></a> to see update!';
		}

        // Get purchase code
        $verify = 1;
        $purchase_code = Db::getInstance()->getValue("
            SELECT `value`
            FROM `"._DB_PREFIX_."configuration`
            WHERE `name`='TSHIRTECOMMERCE_PURCHASE_CODE'
        ");

        if ($purchase_code == false) {
            $purchase_code = '';
        }

        if (!empty($purchase_code)) {
            $json_str = Tools::file_get_contents('http://updates.tshirtecommerce.com/verify_purchase.php?code=' . $purchase_code . '&platform=prestashop');

            if($json_str !== false) {
                $json_verify = json_decode($json_str, true);
            }

            $verify = isset($json_verify['error']) ? $json_verify['error'] : 0;
        }

        foreach ($versions as &$v) {
            $v->d_version = str_replace('.', '', $v->version);
        }

        $ps_version = substr(str_replace('.', '', _PS_VERSION_), 0, 2);

        // Update version x.x.x (.zip)
        if (Tools::getIsset('update') && Tools::getValue('update') != '') {
            if (!empty($purchase_code)) {
                $ts_version = str_replace('.zip', '', Tools::getValue('update'));
                 $ts_version = str_replace('v', '' , $ts_version);
                $url_download = 'http://updates.tshirtecommerce.com/api.php?code='.$purchase_code.'&version='.$ts_version.'&platform=prestashop&prestashop='.$ps_version;
                $content = Tools::file_get_contents($url_download);

                if ($content === false) {
                    $content = $this->openURL($url_download);
                }

                if ($content !== false) {
                    $path = _PS_ROOT_DIR_.'/ps_tshirtecommerce_'. $ts_version.'.zip';

                    if(@file_put_contents($path, $content)) {
                        if (Tools::ZipExtract($path, _PS_ROOT_DIR_)) {
                            $error_info = $this->trans('Your version has been successfully updated.', [], 'Modules.Tshirtecommerce.Admin');
                        } else {
                            $error_warning = $this->trans('Can not open file downloaded on your server. Please try to download and upload to server by FTP account.', [], 'Modules.Tshirtecommerce.Admin');
                        }
                    } else {
                        $error_warning = $this->trans('Update not found.', [], 'Modules.Tshirtecommerce.Admin');
                    }
                }
            } else {
                $error_warning = $this->trans('Please verify Purchased Code before update module.', [], 'Modules.Tshirtecommerce.Admin');
            }
        }

		$this->tpl_view_vars = array(
            'error_info'    => $error_info,
            'error_warning' => $error_warning,
            'versions'      => $versions,
            'verify'        => $verify,
            'code'          => $purchase_code,
            'ps_version'    => $ps_version,
            'link_setting' => Context::getContext()->link->getAdminLink('AdminTshirtecommerceSetting', true)
        );

		$this->base_tpl_view = 'AdminTshirtecommerceUpdate.tpl';

		return parent::renderView();
    }

    private function openUrl(string $url): string|false
    {
        $data = false;
        if( function_exists('curl_exec') )
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
        }

        if( $data == false && function_exists('file_get_contents') )
        {
            $data = file_get_contents($url);
        }

        return $data;
    }
}

// Backward compatibility: expose namespaced controller under global class name for legacy loader
if (!class_exists('AdminTshirtecommerceUpdateController')) {
    class_alias('\Tshirtecommerce\\Controllers\\Admin\\AdminTshirtecommerceUpdateController', 'AdminTshirtecommerceUpdateController');
}
