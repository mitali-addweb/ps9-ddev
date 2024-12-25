<?php
/**
 * Admin Controller for Tshirtecommerce Import
 * PrestaShop 9 Compatible
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('ROOT')) {
    define('ROOT', _PS_ROOT_DIR_ . DS . 'tshirtecommerce');
}
if (file_exists(ROOT . DS . 'includes' . DS . 'config.php')) {
    include_once ROOT . DS . 'includes' . DS . 'config.php';
}

/**
 * Import controller for the T-shirt Designer module
 * Handles importing designs, clipart and products from external sources
 */
class AdminTshirtecommerceImportController extends ModuleAdminController
{
    protected $download_url = 'https://9file.net/store/download/index/';
    protected $import_url = 'https://9file.net/store/import/index';
    protected $product_img = 'https://9file.net/store/products_data/tshirtecommerce/';

    protected $tshirtecommerce_root;
    protected $user_id;

    protected $task;
    protected $api;
    protected $site_id;

    protected $error;

	public function __construct()
	{
		$this->lang       = false;
		$this->bootstrap  = true;
        $this->display    = 'view';
        $this->module     = 'tshirtecommerce';
        parent::__construct();

        $this->tshirtecommerce_root = _PS_ROOT_DIR_.'/tshirtecommerce/data/';

        $import_data = Configuration::get(_DB_PREFIX_.'LINK_BO_IMPORT_DATA_TSE');
        if (!Tools::getIsset('task') && !Tools::getIsset('site_id') && !Tools::getIsset('api') && !empty($import_data)) {
            Configuration::updateValue(_DB_PREFIX_.'LINK_BO_IMPORT_DATA_TSE', '');
            Tools::redirect($this->context->link->getAdminLink('AdminTshirtecommerceImport').'&'.base64_decode($import_data));
        }
        $this->user_id = md5((int)$this->context->employee->id);

        if (defined('MAIN_STORE_URL')) {
            $this->download_url = MAIN_STORE_URL.'download/index/';
            $this->import_url = MAIN_STORE_URL.'import/index';
            $this->product_img = MAIN_STORE_URL.'products_data/tshirtecommerce/';
        }
	}
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->trans('Prestashop Custom Product Designer', [], 'Modules.Tshirtecommerce.Admin');
        parent::initPageHeaderToolbar();
        if ($this->meta_title != null) array_pop($this->meta_title);
    }
    public function initContent()
    {
        $this->renderView();
        parent::initContent();
    }
    public function initProcess()
    {
        $task = Tools::getValue('task', '');
        $api = Tools::getValue('api', '');
        $site_id = (int)Tools::getValue('site_id', 0);
        $download = (int)Tools::getValue('download', 0);

        $this->task = $task;
        $this->api = $api;
        $this->site_id = $site_id;

        if (!empty($task) && !empty($api) && $site_id > 0) {
            if ($download === 1) {
                $response = $this->import('downloadzip', $api, $site_id);
                $response = str_replace('downloadzip', 'design', $response);
                Tools::redirect($response);
            }

            $response = $this->import($task, $api, $site_id);

            if (isset($response['message'])) {
                $this->error = $response['message'];
            }
        }
    }
	public function renderView()
	{
		global $cookie;
		include_once _PS_MODULE_DIR_.'/tshirtecommerce/class.quicksetup.php';
		$tsequicksetup = new TshirtecommerceQuicksetup();
		$site_name 	= Configuration::get('PS_SHOP_NAME');

        $base_site = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $base_site .= __PS_BASE_URI__;
        $base_site = rtrim($base_site, '/');
		$site_url	= Context::getContext()->link->getAdminLink('AdminTshirtecommerceImport');
        if (strpos($site_url, $base_site) !== false) {
            $site_url = str_replace($base_site, $base_site.'|', $site_url);
        } else {
            $site_url = $base_site.'|'.basename(_PS_ADMIN_DIR_).'/'.$site_url;
        }

        $param = array('download' => 1);
        $site_url_dowload = Context::getContext()->link->getAdminLink('AdminTshirtecommerceImport', true, array(), $param);
        if (strpos($site_url_dowload, $base_site) !== false) {
            $site_url_dowload = str_replace($base_site, $base_site.'|', $site_url_dowload);
        } else {
            $site_url_dowload = $base_site.'|'.basename(_PS_ADMIN_DIR_).'/'.$site_url_dowload;
        }

		$purchased 	= $tsequicksetup->getPurchaseCode();
		$url = $this->import_url.'?name='.base64_encode($site_name).'_'.$purchased.'&returnUrl='.base64_encode($site_url);
        $url_download = $this->import_url.'?name='.base64_encode($site_name).'_'.$purchased.'&returnUrl='.base64_encode($site_url_dowload);
		$values['verified_code'] = 0;
		$values['purchased_code'] = $purchased;
		$valid = $tsequicksetup->tshirtecommerce_settings_purchase_code($values);

		if (isset($valid['verified_code']) && $valid['verified_code'] == 1) {
            $link_import_product    = $url.'&task=product';
            $link_import_art_design = $url.'&task=design';
            $verified_code = 1;

            $link_convert_import_clipart = $url.'&task=toolsart';
            $link_convert_import_design = $url.'&task=toolsdesign';
            $link_import_art_design_downlnoad = $url_download.'&task=design';
        } else {
            $link_import_product    = '';
            $link_import_art_design = '';
            $verified_code = 0;

            $link_convert_import_clipart = '';
            $link_convert_import_design = '';
            $link_import_art_design_downlnoad = '';
        }
        $link_verified_code = Context::getContext()->link->getAdminLink('AdminTshirtecommerceSetting');
        $this->tpl_view_vars = array(
            'verified_code'             => $verified_code,
            'link_verified_code'        => $link_verified_code,
            'link_import_product'       => $link_import_product,
            'link_import_art_design'    => $link_import_art_design,

            'link_convert_import_clipart' => $link_convert_import_clipart,
            'link_convert_import_design' => $link_convert_import_design,
            'ifolder' => _PS_ROOT_DIR_.'/tshirtecommerce/data/store/',
            'link_import_art_design_downlnoad' => $link_import_art_design_downlnoad,

            'msg' => $this->error,
        );
		$this->base_tpl_view = 'AdminTshirtecommerceImport.tpl';
		return parent::renderView();
	}

    protected function import($task, $api, $site_id)
    {
        $json = array('error' => 0, 'message' => $this->trans('Action does not exists.', [], 'Modules.Tshirtecommerce.Admin'));

        $url = $this->download_url.$task.'/'.$api.'/'.$site_id;

        if ($task == 'designidea' || $task == 'design') {
            $data = Tools::file_get_contents($url);

            if ($data == false) {
                $data = $this->openUrl($url);
            }

            if ($task == 'designidea') {
                $json = $this->importDesignIdeas($data);
            } else {
                $json = $this->download_design($data);
            }
        } else {
            $store_data = Tools::file_get_contents($url);
            if ($store_data == false) {
                $store_data = $this->openUrl($url);
            }
            $data = json_decode($store_data, true);

            if (count($data)) {
                switch ($task) {
                    case 'products':
                        $json = $this->importProduct($data);
                        break;

                    case 'customIdea':      // designs
                        $json = $this->importCustomIdea($data);
                        break;

                    case 'customClipart':   // cliparts
                        $json = $this->importCustomClipart($data);
                        break;

                    case 'cliparts':
                        $json = $this->importCliparts($data);
                        break;

                    case 'downloadzip':
                        return $url;
                        break;

                    default:
                        break;
                }
            }
        }

        return $json;
    }

    protected function download_design($data)
    {
    $json = array('error' => 0, 'message' => $this->trans('Action does not exists.', [], 'Modules.Tshirtecommerce.Admin'));

        $path = $this->tshirtecommerce_root.'store'.DS.'temp';
        if (!is_dir($path)) {
            mkdir($path, 0755);
        }
        $file = $path.'store.zip';

        if (file_put_contents($file, $data)) {
            $unzipfile = Tools::ZipExtract($file, $path);
            if ($unzipfile) {
                $json['error'] = 0;
                $json['message'] = $this->trans('Imported successful.', [], 'Modules.Tshirtecommerce.Admin');

                $this->mergerImportedJson('art_categories');
                $this->mergerImportedJson('arts_info');
                $this->mergerImportedJson('arts');
                $this->mergerImportedJson('idea_categories');
                $this->mergerImportedJson('types');

                $this->removeDeleted();
            } else {
                $json['error'] = 1;
                $json['message'] = $this->trans('Imported failed.', [], 'Modules.Tshirtecommerce.Admin');
            }
        }
        unlink($file);

        return $json;
    }

    protected function addProperty($array = array(), $parent = 'id', $child = 'children')
    {
        $result = array();

        if (count($array) < 1) return $result;

        foreach ($array as $row) {
            if (!in_array($row[$parent], $result)) {

                $result[] = $row[$parent];
                if (!empty($child) && isset($row[$child]) && count($row[$child])) {

                    foreach ($row[$child] as $r) {
                        if (!in_array($r[$parent], $result)) {
                            $result[] = $r[$parent];
                        }
                    }
                }
            }
        }

        return $result;
    }

    protected function mergerRow($data, $new, $parent = 'id', $child = 'children', $have_key = false)
    {
        $is_child = false;
        foreach ($data as $key => &$row) {

            if ($row[$parent] == $new[$parent]) {
                if (!empty($child) && isset($new[$child]) && count($new[$child])) {
                    if (isset($row[$child]) && count($row[$child])) {

                        $ids = $this->addProperty($row[$child]);

                        foreach ($new[$child] as $n) {
                            if (!in_array($n[$parent], $ids)) {
                                $row[$child][] = $n;
                            }
                        }
                    } else {
                        $row[$child] = $new[$child];
                    }
                }

                $is_child = true;
                break;
            }
        }

        if ($is_child === false) {
            if ($have_key === false)
                $data[] = $new;
            else
                $data[$new[$parent]] = $new;
        }

        return $data;
    }

    protected function removeDeleted()
    {
        $path = $this->tshirtecommerce_root.'store'.DS.'deleted.json';
        if (file_exists($path)) {
            $deleteds = json_decode(file_get_contents($path), true);
            if ($deleteds == false || $deleteds == null) {
                $deleteds = array();
            }

            if (count($deleteds)) {
                if (isset($deleteds['art'])) {
                    $deleteds['art'] = array();
                }

                if (isset($deleteds['cate_art'])) {
                    $deleteds['cate_art'] = array();
                }

                if (isset($deleteds['idea'])) {
                    $deleteds['idea'] = array();
                }
            }

            $deleteds = json_encode($deleteds);
            file_put_contents($path, $deleteds);
        }
    }

    protected function mergerImportedJson($file_name)
    {
        $path = $this->tshirtecommerce_root.'store/';

        $json_old_file = $path.$file_name.'.json';
        $olds = json_decode(@file_get_contents($json_old_file), true);
        if ($olds == false || $olds == null) $olds = array();

        $json_new_file = $path.'temp/'.$file_name.'.json';
        $news = json_decode(@file_get_contents($json_new_file), true);
        if ($news == false || $news == null) $news = array();

        // fixed exception when download false
        // stop compile when $news is null
        if (count($news) < 1) return true;

        $data = $olds;
        if (count($data)) {
            switch ($file_name) {
                case 'art_categories':
                case 'idea_categories':
                case 'types':
                    $ids = $this->addProperty($olds);
                    foreach ($news as $new) {
                        $data = $this->mergerRow($data, $new);
                    }
                    break;

                case 'arts_info':
                    $ids = $this->addProperty($olds, 'id' , '');
                    foreach ($news as $new) {
                        $data = $this->mergerRow($data, $new, 'id', '', true);
                    }
                    break;

                case 'arts':
                    foreach ($news as $new) {
                        if (!in_array($new, $data)) {
                            $data[] = $new;
                        }
                    }
                    break;

                default:
                    break;
            }
        } else {
            // fixed import first time
            $data = $news;
        }

        $data = json_encode($data);
        file_put_contents($json_old_file, $data);
        unlink($json_new_file);

        return true;
    }

    protected function importDesignIdeas($data)
    {
        $json = array('error' => 0, 'message' => $this->trans('Your designs imported successful.', [], 'Modules.Tshirtecommerce.Admin'));

        $file_name = 'designs.txt';
        if ($data != false && strlen($data) > 100) {
            $path = $this->tshirtecommerce_root . 'import/';
            $file = $path.$file_name;

            if (file_put_contents($file, $data)) {
                $this->importStore();
            }
        }

        $this->download_your_design();

        return $json;
    }

    protected function openUrl($url)
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

    protected function download_your_design()
    {
        $link   = $this->download_url.'vectors/'.$this->api.'/'.$this->site_id;
        $data   = Tools::file_get_contents($link);

        if ($data != false && strlen($data) > 100) {
            $path = _PS_ROOT_DIR_.'/tshirtecommerce/cache/admin/';
            $file = $path.'designs.zip';
            if (file_put_contents($file, $data)) {
                $unzipfile = Tools::ZipExtract($file, $path);
                if ($unzipfile) {
                    $folders = glob($path . 'user*' , GLOB_ONLYDIR);
                    if (isset($folders[0])) {
                        $folder = $folders[0];
                        $files = glob($folder . '/*.txt');

                        for ($i = 0; $i < count($files); $i++) {
                            $file_name = str_replace($folder.'/', '', $files[$i]);
                            rename($files[$i], $path.$file_name);
                        }
                        rmdir($folder);
                    }
                }
            }
            unlink($file);
        }
    }

    protected function importStore()
    {
        if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.'/tshirtecommerce/');
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        include_once ROOT.DS.'includes'.DS.'functions.php';
        $dg = new Dg();

        if (!is_dir(ROOT .DS. 'data' .DS. 'import')) {
            mkdir(ROOT .DS. 'data' .DS. 'import', 0755);
        }

        $file       = ROOT .DS. 'data' .DS. 'import' .DS. 'designs.txt';
        $new_file   = ROOT .DS. 'cache' .DS. 'admin' .DS. 'designs.txt';

        if (file_exists($file)) {
            rename($file, $new_file);

            $cache      = $dg->cache('admin');
            $designs    = $cache->get('designs');

            if ($designs != null) {
                $user_id    = $this->user_id;
                $users      = $cache->get($user_id);

                if ($users == null) {
                    $users = array();
                }

                $rows = $this->getData('ideas', 'store');

                foreach ($designs as $design) {
                    $design['title']        = 'Export design';
                    $design['thumb']        = $design['image'];
                    $design['is_ideas']     = 1;

                    $users[$design['key']] = $design;
                    if (isset($rows[$design['key']])) continue;

                    $row = array(
                        'id'            => $design['key'],
                        'user_id'       => $user_id,
                        'type'          => 'shop',
                        'username'      => 'admin',
                        'slug'          => 'admin-design',
                        'image'         => $design['image'],
                        'thumb'         => $design['image'],
                        'title'         => 'Export design',
                        'description'   => 'Export design',
                        'featured'      => '0',
                        'fonts'         => '',
                        'color'         => '',
                        'tags'          => '',
                        'categories'    => '',
                        'types'         => '',
                    );
                    $rows[$design['key']] = $row;
                }

                $file = $this->tshirtecommerce_root. 'store/ideas.json';
                $this->WriteFile($file, json_encode($rows));
                $cache->set($user_id, $users);
            }
            unlink($new_file);
        }
    }

    protected function importCliparts($data)
    {
        $json = array('error' => 0, 'message' => $this->trans('Cliparts imported successful.', [], 'Modules.Tshirtecommerce.Admin'));
        $file = $this->tshirtecommerce_root.'arts.json';

        if (isset($data['arts']) && $data['arts'] > 0) {
            $rows = $data['arts'];
            if (count($rows) == 0) return $json;

            $data = array();
            foreach ($rows as $art) {
                $key        = md5($art['file_name']);
                $data[$key] = $art;
            }

            /* get index of clipart */
            $index = 0;
            if (file_exists($file)) {
                $content    = Tools::file_get_contents($file);
                $arts       = json_decode($content, true);
                $index      = 0;

                if (isset($arts['arts']) && count($arts['arts'])) {

                    foreach ($arts['arts'] as $row) {

                        if ($row['clipart_id'] > $index) {
                            $index = $row['clipart_id'];
                        }

                        $key = md5($row['file_name']);
                        if (isset($data[$key])) {
                            unset($data[$key]);
                        }
                    }
                }

                if (count($data) > 0) {
                    $index = $index + 1;
                    foreach ($data as $key => $art) {
                        $art['clipart_id'] = $index;
                        $arts['arts'][] = $art;

                        $index ++;
                    }
                }
                $arts['count'] = count($arts['arts']);
            } else {
                $arts = array(
                    'arts' => $rows,
                    'count' => count($rows),
                );
            }
            $this->WriteFile($file, json_encode($arts));

            $this->mergeClipart();
        }

        return $json;
    }

    protected function mergeClipart()
    {
        $file = $this->tshirtecommerce_root.'store/art_categories.json';
        $data = json_decode(Tools::file_get_contents($file), true);

        $ids = array();

        if (isset($data)) {
            $categories = $this->categoriestree();
            $categories = json_decode(json_encode($categories), true);

            if (count($categories)) {
                $shop_categories = array();
                foreach ($categories as $j => $cate) {
                    if ($cate['id'] == 0) continue;

                    $new_id = '11111'.$cate['id'];
                    $ids[$cate['id']] = $new_id;

                    if (isset($cate['is_added_store'])) continue;

                    if (isset($cate['children']) && count($cate['children'])) {
                        foreach ($cate['children'] as $i => $child) {
                            $id_child               = '11111'.$child['id'];
                            $child['parent_id']     = $new_id;
                            $child['is_shop']       = 1;
                            $ids[$child['id']]      = $id_child;
                            $child['id']            = $id_child;
                            $cate['children'][$i]   = $child;
                        }
                    }
                    $categories[$j]['is_added_store'] = 1;

                    $cate['is_shop'] = 1;
                    $cate['id']      = $new_id;

                    $shop_categories[] = $cate;
                }

                /* add categories */
                if (count($shop_categories)) {
                    foreach ($shop_categories as $cate) {
                        $data[] = $cate;
                    }

                    /* update store categories */
                    $this->WriteFile($file, json_encode($data));

                    /* update shop categories */
                    $file = $this->tshirtecommerce_root.'categories_art.json';
                    $this->WriteFile($file, json_encode($categories));
                }
            }
        }

        /* move art */
        $file = $this->tshirtecommerce_root.'arts.json';
        if (file_exists($file)) {
            $str  = Tools::file_get_contents($file);
            $arts = json_decode($str, true);

            /* get art of store */
            $file_store = $this->tshirtecommerce_root.'store/arts_info.json';
            if (!file_exists($file_store)) {
                $myfile = fopen($file_store, "w");
                fclose($myfile);
            }

            $file_store_id  = $this->tshirtecommerce_root.'store/arts.json';
            $arts_ids = json_decode(Tools::file_get_contents($file_store_id), true);
            if (file_exists($file_store) && count($arts['arts'])) {
                $str        = Tools::file_get_contents($file_store);
                $art_store  = json_decode($str, true);

                $deleted = array();
                foreach ($arts['arts'] as $key => $art) {
                    $id = '22222'.$art['clipart_id'];
                    $deleted[] = $id;

                    if (isset($art['is_added_store'])) continue;

                    if (empty($art['is_added_store'])) {
                        $arts_ids[] = $id;
                    }

                    $art['clipart_id']  = $id;
                    $art['id']          = $id;

                    if (isset($ids[$art['cate_id']])) {
                        $art['cate_id'] = $ids[$art['cate_id']];
                    }

                    $art['is_shop']         = 1;
                    $art['is_added_store']  = 1;

                    $art_store[$id] = $art;

                    $arts['arts'][$key]['is_added_store'] = 1;
                }

                if (count($deleted)) {
                    $file_deleted = $this->tshirtecommerce_root.'store/deleted.json';
                    $json_deleted = json_decode(Tools::file_get_contents($file_deleted), true);
                    if ($json_deleted != false && isset($json_deleted['art']) && count($json_deleted['art'])) {
                        foreach ($json_deleted['art'] as $akey => $artid) {
                            if (in_array($artid, $deleted)) {
                                unset($json_deleted['art'][$akey]);
                            }
                        }

                        $temp = array();
                        if (count($json_deleted['art'])) {
                            foreach ($json_deleted['art'] as $avalue) {
                                $temp[] = $avalue;
                            }
                        }
                        $json_deleted['art'] = $temp;
                        $this->WriteFile($file_deleted, json_encode($json_deleted));
                    }
                }

                $this->WriteFile($file_store, json_encode($art_store));
                $this->WriteFile($file, json_encode($arts));
                $this->WriteFile($file_store_id, json_encode($arts_ids));
            }
        }
    }

    protected function importProduct($data)
    {
        $json = array('error' => 0, 'message' => $this->trans('Products imported successful.', [], 'Modules.Tshirtecommerce.Admin'));

		$id_max = $this->getMaxIdTshirtecommerceProduct();
		$file = $this->tshirtecommerce_root.'products.json';

		$products = json_decode(Tools::file_get_contents($file), true);
		if (count($products) && isset($products['products'])) {
			$products = $products['products'];
		}

		foreach ($data as &$row) {
			$id_max++;

			$row['id'] = $id_max;
			$products[] = $row;

			$this->importProduct2Prestashop($row, $id_max);
		}

		if (count($products) && file_exists($file)) {
			$new_products['products'] = $products;
			file_put_contents($file, json_encode($new_products)); // commend for test
		}

		return $json;
	}
	protected function getMaxIdTshirtecommerceProduct()
	{
		$max = 1;
		$products['products'] = array();

		$file = $this->tshirtecommerce_root.'products.json';

		$products = json_decode(Tools::file_get_contents($file), true);
		if (count($products) && isset($products['products'])) {
			$products = $products['products'];
		}

		if (count($products)) {
			foreach ($products as $product) {
				if ($product['id'] > $max) $max = $product['id'];
			}
		}

		return $max;
	}
    protected function checkExitsSku($sku)
    {
        $return = true;

        $products = Db::getInstance()->executeS('SELECT `reference` FROM `'._DB_PREFIX_.'product`');
        if ($products != false && count($products) > 0) {
            foreach ($products as $product) {
                if ($product['reference'] == $sku) {
                    $return = false;
                    break;
                }
            }
        }

        return $return;
    }
	protected function importProduct2Prestashop($row, $new_id)
	{
        if (!isset($row['sku']) || !$this->checkExitsSku($row['sku'])) {
            return false;
        }

		$default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
		$id_lang = $default_language_id;
		$shop_is_feature_active = Shop::isFeatureActive();
		$shop_ids = Shop::getCompleteListOfShopsID();

		if (count($row)) {
            $id_product = null;

            $product = new Product($id_product);

            $product->tax_name = '';
            $product->tax_rate = '';
            $product->id_manufacturer = 1;
            $product->id_supplier = 0;
            $product->id_category_default = 1;
            $product->id_shop_default = Configuration::get('PS_SHOP_DEFAULT');
            $product->manufacturer_name = '';
            $product->supplier_name = '';
            $product->name = array($id_lang => $row['title']);
            $product->description = array($id_lang => strip_tags($row['description']));
            $product->description_short = array($id_lang => strip_tags($row['short_description']));
            $product->quantity = (isset($row['max_oder']) && !empty($row['max_oder']) && (int)$row['max_oder'] > 0) ? $row['max_oder'] : 1000;
            $product->minimal_quantity = (isset($row['min_order']) && !empty($row['min_order']) && (int)$row['min_order'] > 0) ? $row['min_order'] : 1;
            $product->low_stock_threshold = '';
            $product->available_now = array();
            $product->available_later = array();
            $product->price = $row['price'];
            $product->specificPrice = 0;
            $product->additional_shipping_cost = 0;
            $product->wholesale_price = (isset($row['sale_price']) && !empty($row['sale_price'])) ? $row['sale_price'] : 0;
            $product->on_sale = 0;
            $product->online_only = 0;
            $product->unity = '';
            $product->unit_price = '';
            $product->unit_price_ratio = 0;
            $product->ecotax = 0;
            $product->reference = $row['sku'];
            $product->supplier_reference = '';
            $product->location = '';
            $product->width = 0;
            $product->height = 0;
            $product->depth = 0;
            $product->weight = 0;
            $product->ean13 = '';
            $product->isbn = '';
            $product->upc = '';
            $product->link_rewrite = array($id_lang => Tools::strtolower(Tools::link_rewrite($row['title'])));
            $product->meta_description = array($id_lang => $row['title']);
            $product->meta_keywords = array($id_lang => $row['title']);
            $product->meta_title = array($id_lang => $row['title']);
            $product->quantity_discount = 0;
            $product->customizable = 0;
            $product->new = '';
            $product->uploadable_files = 0;
            $product->text_fields = 0;
            $product->active = 1;
            $product->redirect_type = '404';
            $product->id_type_redirected = 0;
            $product->available_for_order = 1;
            $product->available_date = date('Y-m-d');
            $product->show_condition = 0;
            $product->condition = 'new';
            $product->show_price = 1;
            $product->indexed = 1;
            $product->visibility = 'both';
            $product->date_add = date('Y-m-d');
            $product->date_upd = date('Y-m-d');
            $product->tags = '';
            $product->state = 1;
            $product->base_price = '';
            $product->id_tax_rules_group = 1;
            $product->id_color_default = 0;
            $product->advanced_stock_management = 0;
            $product->out_of_stock = 2;
            $product->depends_on_stock = '';
            $product->isFullyLoaded = '';
            $product->cache_is_pack = 0;
            $product->cache_has_attachments = 0;
            $product->is_virtual = 0;
            $product->id_pack_product_attribute = '';
            $product->cache_default_attribute = 0;
            $product->category = '';
            $product->pack_stock_type = 3;
            $product->additional_delivery_times = 1;
            $product->delivery_in_stock = array($id_lang => '');
            $product->delivery_out_stock = array($id_lang => '');

            $product->add();

            // copy image product
            $shops = Shop::getContextListShopID();
            $image = new Image();
			$image->id_product = $product->id;
			$image->position = Image::getHighestPosition($product->id) + 1;
			$image->cover = true;
			$url = $row['image'];
			//if (strpos($url, 'http://') === false || strpos($url, 'https://') === false) {
				//$url = $site_url. $row['image']; // todo
			//}
			if (($image->validateFields(false, true)) === true && ($image->validateFieldsLang(false, true)) === true && $image->add()) {
			    $image->associateTo($shops);
			    if (!$this->copyImg($product->id, $image->id, $url, 'products', false)) {
			        $image->delete();
			    }
			}


			// create thumb image (based on image type)
			$imgPath = _PS_ROOT_DIR_.'/img/p/';
			$idImage = $image->id;
			for ($i = 0; $i < strlen($idImage); $i++) { $imgPath .= $idImage[$i] . '/'; }
			$imgPath .= $idImage . '.'.$image->image_format;
			$new_path = $image->getPathForCreation();
			$imagesTypes = ImageType::getImagesTypes('products');
            foreach ($imagesTypes as $k => $image_type) {
                if (!ImageManager::resize($imgPath, $new_path.'-'.stripslashes($image_type['name']).'.'.$image->image_format, $image_type['width'], $image_type['height'], $image->image_format)) {
                    $this->errors[] = $this->trans('An error occurred while copying this image', [], 'Modules.Tshirtecommerce.Admin').': '.stripslashes($image_type['name']);
                }
            }

			// Update quantity
			$new_qty = (isset($row['max_oder']) && !empty($row['max_oder']) && (int)$row['max_oder'] > 0) ? $row['max_oder'] : 10;
			$count_sa = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'stock_available` WHERE `id_product`='.(int)$product->id);
			if ($count_sa > 0) {
				Db::getInstance()->update('stock_available', array('quantity' => (int)$new_qty), 'id_product='.(int)$product->id);
			}

            // link prestashop product with design of tshirtecommerce
           	Db::getInstance()->update('product', array(
           		'design_product_id' => $new_id,
           	), 'id_product='.(int)$product->id);
		}
	}
	protected static function copyImg($id_entity, $id_image = null, $url = '', $entity = 'products', $regenerate = true)
    {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

        switch ($entity) {
            default:
            case 'products':
                $image_obj = new Image($id_image);
                $path = $image_obj->getPathForCreation();
                break;
            case 'categories':
                $path = _PS_CAT_IMG_DIR_.(int)$id_entity;
                break;
            case 'manufacturers':
                $path = _PS_MANU_IMG_DIR_.(int)$id_entity;
                break;
            case 'suppliers':
                $path = _PS_SUPP_IMG_DIR_.(int)$id_entity;
                break;
            case 'stores':
                $path = _PS_STORE_IMG_DIR_.(int)$id_entity;
                break;
        }

        $url = urldecode(trim($url));
        $parced_url = parse_url($url);

        if (isset($parced_url['path'])) {
            $uri = ltrim($parced_url['path'], '/');
            $parts = explode('/', $uri);
            foreach ($parts as &$part) {
                $part = rawurlencode($part);
            }
            unset($part);
            $parced_url['path'] = '/'.implode('/', $parts);
        }

        if (isset($parced_url['query'])) {
            $query_parts = array();
            parse_str($parced_url['query'], $query_parts);
            $parced_url['query'] = http_build_query($query_parts);
        }

        if (!function_exists('http_build_url')) {
            require_once(_PS_TOOL_DIR_.'http_build_url/http_build_url.php');
        }

        $url = http_build_url('', $parced_url);

        $orig_tmpfile = $tmpfile;

        if (Tools::copy($url, $tmpfile)) {
            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
            if (!ImageManager::checkImageMemoryLimit($tmpfile)) {
                @unlink($tmpfile);
                return false;
            }

            $tgt_width = $tgt_height = 0;
            $src_width = $src_height = 0;
            $error = 0;
            ImageManager::resize($tmpfile, $path.'.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
            $images_types = ImageType::getImagesTypes($entity, true);

            if ($regenerate) {
                $previous_path = null;
                $path_infos = array();
                $path_infos[] = array($tgt_width, $tgt_height, $path.'.jpg');
                foreach ($images_types as $image_type) {
                    $tmpfile = self::get_best_path($image_type['width'], $image_type['height'], $path_infos);

                    if (ImageManager::resize(
                        $tmpfile,
                        $path.'-'.stripslashes($image_type['name']).'.jpg',
                        $image_type['width'],
                        $image_type['height'],
                        'jpg',
                        false,
                        $error,
                        $tgt_width,
                        $tgt_height,
                        5,
                        $src_width,
                        $src_height
                    )) {
                        // the last image should not be added in the candidate list if it's bigger than the original image
                        if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                            $path_infos[] = array($tgt_width, $tgt_height, $path.'-'.stripslashes($image_type['name']).'.jpg');
                        }
                        if ($entity == 'products') {
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'.jpg');
                            }
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'_'.(int)Context::getContext()->shop->id.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'_'.(int)Context::getContext()->shop->id.'.jpg');
                            }
                        }
                    }
                    if (in_array($image_type['id_image_type'], $watermark_types)) {
                        Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
                    }
                }
            }
        } else {
            @unlink($orig_tmpfile);
            return false;
        }
        unlink($orig_tmpfile);
        return true;
    }

    protected function importCustomIdea($data)
    {
        $json = array('error' => 0, 'message' => $this->trans('Designs imported successful.', [], 'Modules.Tshirtecommerce.Admin'));

		$rows = $this->getData('ideas');
		foreach ($data as $id => $design) {
			$index = (int) $id;
			if ($index > 0 && empty($rows[$index])) {
				$rows[$index] = $design;
			}
		}

		$store_file	= $this->tshirtecommerce_root.DS.'store'.DS.'ideas.json';
		$this->WriteFile($store_file, json_encode($rows));

		return $json;
	}

    protected function importCustomClipart($data)
    {
        $json = array('error' => 0, 'message' => $this->trans('Cliparts imported successful.', [], 'Modules.Tshirtecommerce.Admin'));

        $arts       = $this->getData('arts');
        $arts_info  = $this->getData('arts_info');

        if (count($data)) {
            foreach ($data as $id => $art) {
                $index  = (int) $id;
                if ($index > 0 && empty($arts_info[$index])) {
                    $arts_info[$index] = $art;
                    $arts[] = $index;
                }
            }
        }

        $store_file = $this->tshirtecommerce_root.DS.'store'.DS. 'arts.json';
        $this->WriteFile($store_file, json_encode($arts));

        $store_file = $this->tshirtecommerce_root.DS.'store'.DS.'arts_info.json';
        $this->WriteFile($store_file, json_encode($arts_info));

        return $json;
	}

	protected function WriteFile($path, $data)
	{
		if (!$fp = fopen($path, 'w')) return false;

		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);

		return true;
	}

	protected function getData($file = '', $folder = '')
	{
        if (!empty($folder)) {
            $file = $this->tshirtecommerce_root.'store/'.$file.'.json';
        } else {
            $file = $this->tshirtecommerce_root.$file.'.json';
        }

		if (file_exists($file)) {
			$temp = Tools::file_get_contents($file);
			if ($temp != false) {
				$content = json_decode($temp, true);
				return $content;
			}
            return array();
		}
		return array();
	}

    protected function categoriestree()
    {
        $path = $this->tshirtecommerce_root.'categories_art.json';
        $categories = array();

        if (file_exists($path)) {
            $str = Tools::file_get_contents($path);
            $categories = json_decode($str);
            if (count($categories) > 0) {
                $new = array();
                foreach ($categories as $a) {
                    if ($a->id == 0) continue;

                    $new[$a->parent_id][] = $a;
                }

                if (isset($new[0])) {
                    $tree = $this->createTree($new, $new[0]);
                } else {
                    $tree = $this->createTree($new, $new);
                }

                $categories = $tree;
            }
        }

        $all                = array();
        $all[0]             = new stdClass();
        $all[0]->id         = 0;
        $all[0]->title      = 'All Art';
        $all[0]->children   = array();
        $all[0]->parent_id  = 0;

        $categories = array_merge($all, $categories);
        return $categories;
    }

    protected function createTree(&$list, $parent){
        $tree = array();
        foreach ($parent as $k=>$l) {
            if (isset($list[$l->id])) {
                $l->children = $this->createTree($list, $list[$l->id]);

                if (count($l->children) > 0) $l->isFolder = true;
            }

            $tree[] = $l;
        }

        return $tree;
    }
}
