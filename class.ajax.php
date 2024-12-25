<?php
// for translate text
require_once dirname(__FILE__).'/tshirtecommerce.php';

include_once dirname(__FILE__).'/class.customization.php';

class TshirtecommerceAjax
{
    function __construct()
    {
        $this->module = new Tshirtecommerce(); // for translate text
        $this->context = Context::getContext();

        $this->customization = new TshirtecommerceCustomizationsPresta($this->module);
    }

    public function updateCustomization($product, $post)
    {
        if (!isset($post['task']) || empty($post['task'])) {
            return;
        }

        switch ($post['task']) {
            case 'save':
                $this->customization->save($product, $post);
                break;
            case 'remove':
                if (isset($post['index']) && $post['index'] > 0) {
                    $this->customization->remove($product, $post['index']);
                }
                break;

            default:
                break;
        }

        //return;
    }

    public function getTshirtecommerceAttributes($psproduct, $tshirtecommerce_product_id, $attributes = array())
    {
        $html = '';

        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce');
        include_once ROOT.DS.'includes'.DS.'functions.php';
        include_once ROOT.DS.'includes'.DS.'functionsps.php';
        $dg = new dgps();

        $enable_taxes = Configuration::get('PS_TAX');
        $taxCalculationMethod = Product::getTaxCalculationMethod();
        $allow_taxes = ($enable_taxes && (!$taxCalculationMethod || $taxCalculationMethod = 2)) ? 1: 0;

        if ($allow_taxes == 0) {
            return '';
        }

        $ps_taxes = isset($psproduct->tax_rate) ? $psproduct->tax_rate : 0;
        if ($ps_taxes == 0) {
            return '';
        }

        $products = $dg->getProducts2();
        if ($products === false || $products == null) $products = array();

        $product = false;
        if (count($products)) {
            foreach ($products as $p) {
                if ($p->id == $tshirtecommerce_product_id) {
                    $product = $p;
                    break;
                }
            }
        }

        if ($product !== false && isset($product->attributes->name)) {
            $min_order = 1;
            if (isset($psproduct->minimal_quantity)) {
                $min_order = $psproduct->minimal_quantity;
            }
            $html = $dg->reGenderAttributes($product->attributes, $ps_taxes, $psproduct->id, $min_order, $attributes);
        } else {
            $html = '';
        }

        echo $html;
        return;
    }
}
