<?php
/**
 * Admin Controller for Tshirtecommerce Settings
 * PrestaShop 9 Compatible
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminTshirtecommerceSettingController extends ModuleAdminController
{
    private $_msg_setting = '';
    private $site_url;
    private $options_format = array(
        '1' => 'X0,000.00',
        '2' => '0 000,00X',
        '3' => 'X0.000,00',
        '4' => '0,000.00X',
        '5' => '0\'000.00X'
    );

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

        if (Tools::getIsset('action') && Tools::getValue('action') == 'setting') {
            $this->tshirtecommerceSaveSetting();
        }

        $this->site_url = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $this->site_url .= __PS_BASE_URI__;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        // PrestaShop 9: Add CSS using addCSS method
        $this->addCSS(_MODULE_DIR_ . 'tshirtecommerce/views/css/tshirtecommerce.css');
    }

    public function initPageHeaderToolbar()
    {
        // Backward compatibility: use trans() domain for PS9
        $this->page_header_toolbar_title = $this->trans('Prestashop Custom Product Designer', [], 'Modules.Tshirtecommerce.Admin');

        parent::initPageHeaderToolbar();

        if ($this->meta_title != null) array_pop($this->meta_title);
    }

    public function initContent()
    {
        $this->renderView();

        return parent::initContent();
    }

    public function renderView()
    {
        if ($this->isSessionStarted() === false) {
            @session_start();
        }

        if ($this->context->employee->id) {
            $_SESSION['is_admin'] = array('login' => 1, 'email' => $this->context->employee->email, 'id' => $this->context->employee->id);
            $_SESSION['admin'] = $_SESSION['is_admin'];
        }

        // PrestaShop 9: getAdminLink() returns full URLs, no need to prepend base URL
        $_SESSION['tse_link_import'] = Context::getContext()->link->getAdminLink('AdminTshirtecommerceImport');
        $_SESSION['tse_link_dashboard'] = Context::getContext()->link->getAdminLink('AdminDashboard');
        $_SESSION['tse_link_addproduct'] = Context::getContext()->link->getAdminLink('AdminProducts');

        $error_warning          = '';
        $setting                = '';
        $setting_product        = 0;
        $setting_downloadable   = 0;
        $purchase_code          = '';
        $arr                    = array();
        $products               = array();

        if (!ini_get('allow_url_fopen')) {
            $error_warning = 'Your server not support <strong>allow_url_fopen</strong>. Please upload your hosting with <strong>allow_url_fopen = ON</strong>. Click <a href="http://tshirtecommerce.com/wp-content/uploads/2015/04/allow_url_fopen.png" target="_blank"><strong>HERE</strong></a> to see update!';
        }

        $url = $this->site_url.'/tshirtecommerce/admin/index.php?session_id=' . session_id();

        $settings = Configuration::get('TSHIRTECOMMERCE_DOWNLOADABLE');
        if ($settings != false) $arr = explode('::', $settings);

        if (count($arr) > 0) {
            if (isset($arr[0])) $setting_product = $arr[0];
            if (isset($arr[1])) $setting_downloadable = $arr[1];
        }

        $purchase_code = Configuration::get('TSHIRTECOMMERCE_PURCHASE_CODE');
        if ($purchase_code === false) $setting_purchase_code = '';

        $tshirtecommerce_custom_text = '';
        $count_custom_text = Configuration::get('TSHIRTECOMMERCE_CUSTOM_TEXT');

        if ($count_custom_text === false) {
            Configuration::updateValue('TSHIRTECOMMERCE_CUSTOM_TEXT', '');
        } else {
            $tshirtecommerce_custom_text = Configuration::get('TSHIRTECOMMERCE_CUSTOM_TEXT');
            if (!$tshirtecommerce_custom_text) $tshirtecommerce_custom_text = '';
        }

        $tshirtecommerce_custom_style = '';
        $count_custom_style = Configuration::get('TSHIRTECOMMERCE_CUSTOM_STYLE');

        if ($count_custom_style === false) {
            Configuration::updateValue('TSHIRTECOMMERCE_CUSTOM_STYLE', '');
        } else {
            $tshirtecommerce_custom_style = Configuration::get('TSHIRTECOMMERCE_CUSTOM_STYLE');
            if (!$tshirtecommerce_custom_style) $tshirtecommerce_custom_style = '';
        }

        // Update Allow hide add-to-cart button
        $setting_hide_add_to_cart = 0;
        $hide_add_to_cart_count = Configuration::get('TSHIRTECOMMERCE_HIDE_ADDTOCART');

        if ($hide_add_to_cart_count === false) {
            Configuration::updateValue('TSHIRTECOMMERCE_HIDE_ADDTOCART', '0');
        } else {
            $setting_hide_add_to_cart = Configuration::get('TSHIRTECOMMERCE_HIDE_ADDTOCART');
            if (!$setting_hide_add_to_cart) $setting_hide_add_to_cart = 0;
        }
        // Get list products have blank design
        $id_lang        = isset($this->context->language->id) ? $this->context->language->id : 1;

        $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("
            SELECT P.`id_product` as id, S.`name` as name
            FROM `"._DB_PREFIX_."product` P
            INNER JOIN (SELECT L.`id_product`, L.`name`
                        FROM `"._DB_PREFIX_."product_lang` L
                        WHERE `id_lang`=".$id_lang.") S ON P.`id_product` = S.`id_product`
            WHERE P.`active` = 1 AND P.`design_product_id`<>'' AND P.`design_product_id` NOT LIKE '%::%'
        ");
        if (!$products) $products = array();

        // Verify purchase code
        // Backward compatibility: use trans() for translations
        $json_verify    = array('error' => 1, 'msg' => $this->trans('Data not found', [], 'Modules.Tshirtecommerce.Admin'));
        $json_str       = Tools::file_get_contents('http://updates.tshirtecommerce.com/verify_purchase.php?code=' . $purchase_code . '&platform=prestashop');

        if ($json_str !== false) $json_verify = json_decode($json_str, true);

        $link_design_your_own = $this->context->link->getModuleLink('tshirtecommerce', 'designer', array());

        //tshirtecommerce_position_custom_design_btn
        $count = Configuration::get('TSHIRTECOMMERCE_POSITION_BTN');
        if ($count === false) {
            Configuration::updateValue('TSHIRTECOMMERCE_POSITION_BTN', '0');
        }

        $tshirtecommerce_position_custom_design_btn = 0;
        $tshirtecommerce_position_custom_design_btns = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("
            SELECT S.value
            FROM "._DB_PREFIX_."configuration S
            WHERE S.name='TSHIRTECOMMERCE_POSITION_BTN'
        ");
        if (!$tshirtecommerce_position_custom_design_btns) {
            $tshirtecommerce_position_custom_design_btn = 0;
        } else {
            $tshirtecommerce_position_custom_design_btn = $tshirtecommerce_position_custom_design_btns[0]['value'];
        }

        $tshirtecommerce_text_loading = $this->trans('Loading...', [], 'Modules.Tshirtecommerce.Admin');
        $tshirtecommerce_logo_loading = $this->site_url.'/tshirtecommerce/assets/images/logo-loading.png';

        if (Configuration::get('TSHIRTECOMMERCE_TEXT_LOADING')) {
            $tshirtecommerce_text_loading = Configuration::get('TSHIRTECOMMERCE_TEXT_LOADING');
        }

        if (Configuration::get('TSHIRTECOMMERCE_LOGO_LOADING')) {
            $tshirtecommerce_logo_loading = $this->site_url.Configuration::get('TSHIRTECOMMERCE_LOGO_LOADING');
        }

        $tshirtecommerce_quicksetup_link = Context::getContext()->link->getModuleLink('tshirtecommerce', 'quicksetup', array(
            'step' => 0
        ));

//         // Update permission access #31738
//         $query = Db::getInstance()->getValue('
//             SELECT COUNT(a.`id_profile`)
//             FROM `'._DB_PREFIX_.'access` a
//             WHERE a.`id_tab` IN (SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `module` = "tshirtecommerce")
//         ');
//         if ($query == 0) {
//             $tshirtecommerce_tabs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `module` = "tshirtecommerce"');
//             $profiles = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_profile` FROM `'._DB_PREFIX_.'profile`');
//             foreach ($profiles as $profile) {
//                 if ($profile['id_profile'] == 1) {
//                     foreach ($tshirtecommerce_tabs as $row) {
//                         Db::getInstance()->execute('
//                             INSERT INTO `'._DB_PREFIX_.'access`(`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`)
//                             VALUES ('.(int)$profile['id_profile'].', '.(int)$row['id_tab'].', 1, 1, 1, 1)
//                         ');
//                     }
//                 } else {
//                     foreach ($tshirtecommerce_tabs as $row) {
//                         Db::getInstance()->execute('
//                             INSERT INTO `'._DB_PREFIX_.'access`(`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`)
//                             VALUES ('.(int)$profile['id_profile'].', '.(int)$row['id_tab'].', 1, 0, 0, 0)
//                         ');
//                     }
//                 }
//             }
//         }

        $default_currency = Currency::getDefaultCurrency();
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $default_currency_format = $this->options_format[$default_currency->format];
        } else {
            $default_currency_format = $default_currency->format;
        }

        $presta_seo_page = Context::getContext()->link->getAdminLink('AdminMeta', true);
        $presta_currency_page = $this->context->link->getAdminLink('AdminLocalization', true);

        // Set your tpl vars
        $this->tpl_view_vars = array(
            'presta_seo_page' => $presta_seo_page,
            'presta_currency_page' => $presta_currency_page,
            'pscurrency' => $default_currency->sign.' ('.$default_currency->iso_code.')',
            'psformat' => $default_currency_format,
            'error_warning' => $error_warning,
            'msg' => $this->_msg_setting,
            'url' => $url,
            'products' => $products,
            'setting_product' => $setting_product,
            'setting_downloadable' => $setting_downloadable,
            'tshirtecommerce_custom_text' => $tshirtecommerce_custom_text,
            'tshirtecommerce_custom_style' => $tshirtecommerce_custom_style,
            'tshirtecommerce_hide_add_to_cart' => $setting_hide_add_to_cart,
            'purchase_code' => $purchase_code,
            'verified' => isset($json_verify['error']) ? $json_verify['error'] : 0,
            'action' => 'index.php?controller=AdminTshirtecommerceSetting&action=setting&token=',
            'link_design_your_own' => $link_design_your_own,
            'tshirtecommerce_position_custom_design_btn' => $tshirtecommerce_position_custom_design_btn,
            'tshirtecommerce_logo_loading' => $tshirtecommerce_logo_loading,
            'tshirtecommerce_text_loading' => $tshirtecommerce_text_loading,
            'tshirtecommerce_quicksetup_link' => $tshirtecommerce_quicksetup_link,
            'tshirtecommerce_specific_prices' => Configuration::get('TSHIRTECOMMERCE_SPECIFIC_PRICES')
        );

        $this->base_tpl_view = 'AdminTshirtecommerceSetting.tpl';

        return parent::renderView();
    }

    private function tshirtecommerceSaveSetting()
    {
        $_product_id        = 0;
        $_downloadable      = 0;
        $_purchase_code     = '';
        $_custom_text       = '';
        $_custom_style      = '';
        $_position_button   = 0;
        $_logo_loading      = '/tshirtecommerce/assets/images/logo-loading.png';;
        $_text_loading      = $this->trans('Loading...', [], 'Modules.Tshirtecommerce.Admin');
        $_specific_prices   = 0;

        $_specific_prices = Tools::getValue('tshirtecommerce_specific_prices', 0);

        if (Tools::getIsset('tshirtecommerce_text_loading')) {
            $_text_loading = Tools::getValue('tshirtecommerce_text_loading');

            Configuration::updateValue('TSHIRTECOMMERCE_TEXT_LOADING', $_text_loading);
        }

        if (isset($_FILES['file'])) {
            $_file          = $_FILES['file'];
            $temp           = explode(".", $_file["name"]);
            $tmpFileName    = 'logo'.time().'.'.end($temp);
            $_logo_loading  = '/tshirtecommerce/uploaded/'.$tmpFileName;
            $tmpName        = _PS_ROOT_DIR_.'/tshirtecommerce/uploaded/'.$tmpFileName;

            if (@move_uploaded_file($_file['tmp_name'], $tmpName)) {
                Configuration::updateValue('TSHIRTECOMMERCE_LOGO_LOADING', $_logo_loading);
            }
        }

        if (Tools::getIsset('tshirtecommerce_product_default')) {
            $_product_id = Tools::getValue('tshirtecommerce_product_default');
        }

        if (Tools::getIsset('tshirtecommerce_downloadbale')) {
            $_downloadable  = Tools::getValue('tshirtecommerce_downloadbale');
        }

        if (Tools::getIsset('tshirtecommerce_purchase_code')) {
            $_purchase_code = trim(Tools::getValue('tshirtecommerce_purchase_code'));
        }

        if (Tools::getIsset('tshirtecommerce_custom_text')) {
            $_custom_text = Tools::getValue('tshirtecommerce_custom_text');
        }

        if (Tools::getIsset('tshirtecommerce_custom_style')) {
            $_custom_style = Tools::getValue('tshirtecommerce_custom_style');
        }

        if (Tools::getIsset('tshirtecommerce_position_custom_design_btn')) {
            $_position_button = Tools::getValue('tshirtecommerce_position_custom_design_btn');
        }

        // Update allow hide add-to-cart button on front-office
        $_hide_add_to_cart = 0;
        if (Tools::getIsset('tshirtecommerce_hide_add_to_cart')) {
            $_hide_add_to_cart = Tools::getValue('tshirtecommerce_hide_add_to_cart');
        }

        $_value = $_product_id.'::'.$_downloadable;

        // update value to config table
        Configuration::updateValue('TSHIRTECOMMERCE_DOWNLOADABLE', $_value);
        Configuration::updateValue('TSHIRTECOMMERCE_PURCHASE_CODE', $_purchase_code);
        Configuration::updateValue('TSHIRTECOMMERCE_CUSTOM_TEXT', $_custom_text);
        Configuration::updateValue('TSHIRTECOMMERCE_CUSTOM_STYLE', $_custom_style);
        Configuration::updateValue('TSHIRTECOMMERCE_HIDE_ADDTOCART', $_hide_add_to_cart);
        Configuration::updateValue('TSHIRTECOMMERCE_POSITION_BTN', $_position_button);
        Configuration::updateValue('TSHIRTECOMMERCE_SPECIFIC_PRICES', $_specific_prices);

        $this->saveCurrentcy();

        $this->renderView();

        return parent::renderView();
    }

    protected function saveCurrentcy()
    {
        $default_currency = Currency::getDefaultCurrency();
        if (!$default_currency) {
            return false;
        }

        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        $file = _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS.'data'.DS.'settings.json';
        $settings = json_decode(Tools::file_get_contents($file), true);

        if ($settings === false) $settings = array();

        // tshirtecommerce { currency_id, currency_symbol, currency_code, price_number, price_thousand, price_decimal, currency_postion}
        // prestashop { currency_rate , currency_prefix, currency_suffix, currency_format, currency_code_num}
        $settings['currency_id'] = $default_currency->id;
        $settings['currency_symbol'] = $default_currency->sign;
        $settings['currency_code'] = $default_currency->iso_code;
        $settings['currency_rate'] = $default_currency->conversion_rate;
        $settings['currency_format'] = $default_currency->format;
        $settings['currency_code_num'] = $default_currency->iso_code_num;
        if (!defined('_PS_PRICE_DISPLAY_PRECISION_')) {
            define('_PS_PRICE_DISPLAY_PRECISION_', 2);
        }
        if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) {
            define('_PS_PRICE_COMPUTE_PRECISION_', _PS_PRICE_DISPLAY_PRECISION_);
        }
        $settings['price_number'] = defined('_PS_PRICE_COMPUTE_PRECISION_') ? _PS_PRICE_COMPUTE_PRECISION_ : 2;
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $settings['currency_postion'] = (isset($default_currency->prefix) && !empty($default_currency->prefix)) ? 'left' : 'right';
            //$settings['price_number'] = (int)$default_currency->decimals * $settings['price_number'];

            $str_format = $this->options_format[$default_currency->format];
        } else {
            $settings['currency_postion'] = ($default_currency->format % 2 != 0) ? 'left' : 'right';

            $str_format = $default_currency->format;
        }

        $settings['price_thousand'] = ',';
        $settings['price_decimal'] = '.';
        if (isset($str_format) && !empty($str_format)) {
            $first = strpos($str_format, ',');
            $zero = strpos($str_format, "'");
            $space = strpos($str_format, " ");
            $second = strpos($str_format, '.');
            if ($space !== false) {
                $settings['price_thousand'] = " ";
                $settings['price_decimal'] = ',';
            }
            elseif ($zero !== false) {
                $settings['price_thousand'] = "'";
                $settings['price_decimal'] = '.';
            } else {
                if ($first < $second) {
                    $settings['price_thousand'] = ',';
                    $settings['price_decimal'] = '.';
                } else {
                    $settings['price_thousand'] = '.';
                    $settings['price_decimal'] = ',';
                }
            }
        }

        file_put_contents($file, json_encode($settings));

    }

    protected function isSessionStarted()
    {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>='))
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            else
                return session_id() === '' ? false : true;
        }
        return false;
    }
}

// Backward compatibility: expose namespaced controller under global class name for legacy loader
if (!class_exists('AdminTshirtecommerceSettingController')) {
    class_alias('\Tshirtecommerce\\Controllers\\Admin\\AdminTshirtecommerceSettingController', 'AdminTshirtecommerceSettingController');
}
