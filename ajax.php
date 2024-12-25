<?php

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

include_once dirname(__FILE__).'/class.ajax.php';
$tsajax = new TshirtecommerceAjax();

function calcPrices($product, $attributes = array(), $lang_id = 1, $id_customer = 0, $id_currency = 1, $id_country = 1, $id_group = 1, $ajax_quantity = 1, $id_shop = 1)
{
    $combinations           = $product->getAttributeCombinations($lang_id);
    $calc_temp              = array();
    $max_order              = 999999;
    $min_order              = $product->minimal_quantity;
    $id_product_attribute   = null;

    $new_combinations = array();
    foreach ($combinations as $row) {
        $temp = array();
        if (count($new_combinations) > 0) {
            foreach ($new_combinations as &$new_row) {
                if ($new_row['id_product_attribute'] == $row['id_product_attribute']) {
                    $new_row['group_'.$row['id_attribute_group']] = $row['id_attribute'];
                } else {
                    $temp['id_product_attribute'] = $row['id_product_attribute'];
                    $temp['group_'.$row['id_attribute_group']] = $row['id_attribute'];
                    $temp['minimal_quantity'] = $row['minimal_quantity'];
                    $temp['quantity'] = $row['quantity'];
                }
            }
        } else {
            $temp['id_product_attribute'] = $row['id_product_attribute'];
            $temp['group_'.$row['id_attribute_group']] = $row['id_attribute'];
            $temp['minimal_quantity'] = $row['minimal_quantity'];
            $temp['quantity'] = $row['quantity'];
        }

        if (count($temp)) {
            $new_combinations[] = $temp;
        }
    }

    $count = 0;
    $temp_combination = array();
    foreach ($attributes as $key => $value) {
        if (strpos($key, 'group_') !== false && !empty($value)) {
            $temp_combination[] = $key;
            $count++;
        }
    }

    $new_combination = array();
    if (count($temp_combination) && count($new_combinations)) {
        foreach ($new_combinations as $row) {
            $new_count = 0;

            foreach ($temp_combination as $value) {
                if (isset($row[$value]) && isset($attributes[$value]) && $row[$value] == $attributes[$value]) {
                    $new_count++;

                    if ($new_count == $count) $new_combination = $row;
                }
            }
        }
    }

    if (isset($new_combination['quantity']) && $new_combination['quantity'] > 0) {
        $max_order = $new_combination['quantity'];
    }

    $min_order = isset($new_combination['minimal_quantity']) ? $new_combination['minimal_quantity'] : 1;
    $id_product_attribute = isset($new_combination['id_product_attribute']) ? $new_combination['id_product_attribute'] : null;
    if (!defined('_PS_PRICE_DISPLAY_PRECISION_')) {
        define('_PS_PRICE_DISPLAY_PRECISION_', 2);
    }
    if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) {
        define('_PS_PRICE_COMPUTE_PRECISION_', _PS_PRICE_DISPLAY_PRECISION_);
    }
    $price_base = Tools::ps_round($product->base_price, _PS_PRICE_COMPUTE_PRECISION_);

    $enable_taxes = (int)Configuration::get('PS_TAX');
    $priceDisplay = Product::getTaxCalculationMethod((int)Context::getContext()->cookie->id_customer);
    if ($enable_taxes == 1 && ($priceDisplay == 0 || $priceDisplay == 2)) {
        $show_taxes = true;
    } else {
        $show_taxes = false;
    }

    $price = $product->getPrice($show_taxes);

    $taxes      = (isset($product->tax_rate) && $show_taxes == 1) ? $product->tax_rate : 0;
    $quantity   = $product->quantity;

    if ($quantity < 0) $quantity = $max_order;

    if (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
        $price_discounts = SpecificPrice::getQuantityDiscounts($product->id, $id_shop, $id_currency, $id_country, $id_group, null, true, (int)$id_customer);

        $price_discount = array();
        foreach ($price_discounts as $row) {
            if ($ajax_quantity >= $row['from_quantity']) {
                $price_discount = $row;
                break;
            }
        }
        if (count($price_discount)) {
            if ($price_discount['reduction_type'] == 'percentage') {
                if ($price_discount['price'] > -1) {
                    $price = $price_discount['price'] - $price_discount['price'] * $price_discount['reduction'];
                } else {
                    $price += $price * $price_discount['reduction'] * $price_discount['price'];
                }
            } else {
                if ($price_discount['price'] > -1) {
                    $price = $price_discount['price'] - $price_discount['reduction'];
                } else {
                    $price += $price_discount['reduction'] * $price_discount['price'];
                }
            }
        }
    }

    die (json_encode(array(
        'price_base'    => $price_base,
        'price'         => $price,
        'taxes'         => $taxes,
        'quantity'      => $quantity,
        'min_order'     => $min_order,
        'max_order'     => $max_order,
        'ipa'           => $id_product_attribute,
        'round_mode'    => (int)Configuration::get('PS_PRICE_ROUND_MODE'),
    )));
}

// @deprecated 1.3.0.b
function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
    $sort_col = array();
    foreach ($arr as $key => $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

// @deprecated 1.3.0.b
function formatQuantityDiscounts($specific_prices, $price, $tax_rate = 0, $ecotax_amount = 0)
{
    foreach ($specific_prices as $key => &$row) {
        $row['quantity'] = &$row['from_quantity'];
        if ($row['price'] >= 0) {
            // The price may be directly set

            $cur_price = (!$row['reduction_tax'] ? $row['price'] : $row['price'] * (1 + $tax_rate / 100)) + (float)$ecotax_amount;

            if ($row['reduction_type'] == 'amount') {
                $cur_price -= ($row['reduction_tax'] ? $row['reduction'] : $row['reduction'] / (1 + $tax_rate / 100));
                $row['reduction_with_tax'] = $row['reduction_tax'] ? $row['reduction'] : $row['reduction'] / (1 + $tax_rate / 100);
            } else {
                $cur_price *= 1 - $row['reduction'];
            }

            $row['real_value'] = $price > 0 ? $price - $cur_price : $cur_price;
        } else {
            if ($row['reduction_type'] == 'amount') {
                if (Product::$_taxCalculationMethod == PS_TAX_INC) {
                    $row['real_value'] = $row['reduction_tax'] == 1 ? $row['reduction'] : $row['reduction'] * (1 + $tax_rate / 100);
                } else {
                    $row['real_value'] = $row['reduction_tax'] == 0 ? $row['reduction'] : $row['reduction'] / (1 + $tax_rate / 100);
                }
                $row['reduction_with_tax'] = $row['reduction_tax'] ? $row['reduction'] : $row['reduction'] +  ($row['reduction'] *$tax_rate) / 100;
            } else {
                $row['real_value'] = $row['reduction'] * 100;
            }
        }
        $row['nextQuantity'] = (isset($specific_prices[$key + 1]) ? (int)$specific_prices[$key + 1]['from_quantity'] : - 1);
    }
    return $specific_prices;
}

function renderAttributesHtml($product, $product_id, $lang_id = 1, $id_currency = 1)
{
    $html   = '';
    $arr    = getAttributesPs($product, $product_id, $lang_id);
    $colors = $arr['colors'];
    $groups = $arr['groups'];

    include_once dirname(__FILE__).'/class.customization.php';
    include_once dirname(__FILE__).'/tshirtecommerce.php';
    $module = new Tshirtecommerce();
    $customization = new TshirtecommerceCustomizationsPresta($module);
    $customizations = $customization->getCustomizations($product);

    if (!defined('_PS_PRICE_DISPLAY_PRECISION_')) {
        define('_PS_PRICE_DISPLAY_PRECISION_', 2);
    }
    if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) {
        define('_PS_PRICE_COMPUTE_PRECISION_', _PS_PRICE_DISPLAY_PRECISION_);
    }

    $currency           = new Currency($id_currency);
    $currency->prefix   = trim($currency->prefix);
    $currency->suffix   = trim($currency->suffix);
    $char_currency      = (!empty($currency->prefix)) ? $currency->prefix : $currency->suffix;
    $pos_sym_currency   = (!empty($currency->prefix)) ? 'left' : 'right';

    $html .= '<div class="content-y">';
    $html .= '<form method="POST" id="tool_cart_ps" name="tool_cart_ps" action="">';
    $html .= '<p class="hidden" style="display:none!important;">';
    $html .= '<input type="hidden" name="token" value="'.Tools::getToken(false).'" />';
    $html .= '<input type="hidden" name="id_product" value="'.$product->id.'" id="product_page_product_id" />';
    $html .= '<input type="hidden" name="add" value="1" />';
    $html .= '<input type="hidden" name="id_product_attribute" id="idCombination" value="" />';
    $html .= '<input type="hidden" name="id_currency" value="'.$id_currency.'" />';
    $html .= '<input type="hidden" name="sym_currency" value="'.$char_currency.'" />';
    $html .= '<input type="hidden" name="pos_sym_currency" value="'.$pos_sym_currency.'" />';
    $html .= '<input type="hidden" name="compute_precision" value="'._PS_PRICE_COMPUTE_PRECISION_.'" />';
    $html .= '</p>';
    if (is_array($groups) && count($groups) > 0) {
        $html .= '<div id="psattributes" style="display:none;">';
        foreach ($groups as $id_attribute_group => $group) {
            if (count($group['attributes']) > 0) {
                if ($group['group_type'] != 'color') {
                    $html .= '<div class="form-group product-fields attribute_fieldset">';
                    $html .= '<label class="attribute_label"';
                    if ($group['group_type'] != 'color' && $group['group_type'] != 'radio') {
                        $html .= ' for="group_'.$id_attribute_group.'"';
                    }
                    $html .= '><strong>'.$group['name'].'&nbsp;</strong></label>';
                    //$html .= 'var="groupName" value="group_'.$id_attribute_group.'"';
                    $html .= '<div class="attribute_list">';
                }
                if ($group['group_type'] == 'select') {
                    $html .= '<div class="form-group">';
                    $html .= '<select onchange="design.ajax.getPrice()" name="group_'.$id_attribute_group.'" id="group_'.$id_attribute_group.'" class="form-control attribute_select no-print">';
                    foreach ($group['attributes'] as $id_attribute => $group_attribute) {
                        $html .= '<option value="'.$id_attribute.'"';
                        if($group['default'] == $id_attribute) {
                            $html .= ' selected="selected"';
                        }
                        $html .= ' title="'.$group_attribute.'">'.$group_attribute.'</option>';
                    }
                    $html .= '</select>';
                    $html .= '</div>';
                } elseif ($group['group_type'] == 'color') {
                    /* @deprecated for fix #26  */
                    $default_colorpicker = '';
                    $html .= '<div class="form-group" style="display:none">';
                    $html .= '<ul id="color_to_pick_list" class="clearfix">';
                    foreach ($group['attributes'] as $id_attribute => $group_attribute) {
                        $html .= '<li';
                        if($group['default'] == $id_attribute) {
                            $html .= ' class="selected"';
                        }
                        $html .= '>';
                        $html .= '<a href="javascript:void(0)" onclick="prestashop.selectColor(this)" data-id="'.$id_attribute.'" title="'.$group_attribute.'" class="color_pick';
                        if ($group['default'] == $id_attribute) {
                            $html .= ' selected';
                        }
                        $html .= '"';
                        if(!file_exists(_PS_COL_IMG_DIR_.$id_attribute.'.jpg') && isset($colors[$id_attribute]['value']) && !empty($colors[$id_attribute]['value'])) {
                            $html .= ' data-color="'.str_replace('#', '', $colors[$id_attribute]['value']).'" style="background:'.$colors[$id_attribute]['value'].'"';
                        }
                        $html .= ' title="'.$colors[$id_attribute]['value'].'">';
                        if(file_exists(_PS_COL_IMG_DIR_.$id_attribute.'.jpg')) {
                            $html .= '<img src="'._THEME_COL_DIR_.$id_attribute.'.jpg" alt="'.$colors[$id_attribute]['value'].'" title="'.$group_attribute.'" />';
                        }
                        $html .= '</a></li>';
                        $default_colorpicker = $id_attribute;
                    }
                    $html .= '</ul><input type="hidden" id="ps_color_pick_hidden" class="color_pick_hidden" name="group_'.$id_attribute_group.'" value="'.$default_colorpicker.'" />';
                    //$html .= '<input type="hidden" id="ps_color_pick_hidden" class="color_pick_hidden" name="group_'.$id_attribute_group.'" value="'.$default_colorpicker.'" />';
                    $html .= '</div>';
                } elseif ($group['group_type'] == 'radio') {
                    $html .= '<div class="form-group">';
                    $html .= '<ul>';
                    foreach ($group['attributes'] as $id_attribute => $group_attribute) {
                        $html .= '<li';
                        if ($group['default'] == $id_attribute) {
                            $html .= ' class="checked"';
                        }
                        $html .= '><input onclick="design.ajax.getPrice()" type="radio" class="attribute_radio" name="group_'.$id_attribute_group.'" value="'.$id_attribute.'"';
                        if ($group['default'] == $id_attribute) {
                            $html .= ' checked="checked"';
                        }
                        $html .= ' />';
                        $html .= '<span>'.$group_attribute.'</span></li>';
                    }
                    $html .= '</ul>';
                    $html .= '</div>';
                } elseif ($group['group_type'] == 'checkbox') {
                    // @todo - custom by tshirtecomerce (add checkbox to prestashop attributes)
                }

            }
        }
    }
    $html .= '</div>';
    $html .= '</form>';
    if (!empty($customizations)) {
        $html .= $customizations;
    }

    echo $html;
    return;
}

function getAttributesPs($product, $product_id, $lang_id = 1)
{
    $colors             = array();
    $groups             = array();
    $combinations       = array();
    $combination_images = array();
    $attributes_groups  = $product->getAttributesGroups($lang_id);

    if (is_array($attributes_groups) && $attributes_groups) {
        $combination_images     = $product->getCombinationImages($lang_id);
        $combination_prices_set = array();

        foreach ($attributes_groups as $k => $row) {
            // Color management
            if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) {
                $colors[$row['id_attribute']]['value'] = $row['attribute_color'];
                $colors[$row['id_attribute']]['name'] = $row['attribute_name'];
                if (!isset($colors[$row['id_attribute']]['attributes_quantity'])) {
                    $colors[$row['id_attribute']]['attributes_quantity'] = 0;
                }
                $colors[$row['id_attribute']]['attributes_quantity'] += (int)$row['quantity'];
            }
            if (!isset($groups[$row['id_attribute_group']])) {
                $groups[$row['id_attribute_group']] = array(
                    'group_name'    => $row['group_name'],
                    'name'          => $row['public_group_name'],
                    'group_type'    => $row['group_type'],
                    'default'       => -1,
                );
            }
            $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
            if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                $groups[$row['id_attribute_group']]['default'] = (int)$row['id_attribute'];
            }
            if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
            }
            $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)$row['quantity'];
            $combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
            $combinations[$row['id_product_attribute']]['attributes'][] = (int)$row['id_attribute'];
            $combinations[$row['id_product_attribute']]['price'] = (float)Tools::convertPriceFull($row['price'], null, Context::getContext()->currency);
            // Call getPriceStatic in order to set $combination_specific_price
            if (!isset($combination_prices_set[(int)$row['id_product_attribute']])) {
                Product::getPriceStatic((int)$product_id, false, $row['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
                $combination_prices_set[(int)$row['id_product_attribute']] = true;
                $combinations[$row['id_product_attribute']]['specific_price'] = $combination_specific_price;
            }
            $combinations[$row['id_product_attribute']]['ecotax'] = (float)$row['ecotax'];
            $combinations[$row['id_product_attribute']]['weight'] = (float)$row['weight'];
            $combinations[$row['id_product_attribute']]['quantity'] = (int)$row['quantity'];
            $combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
            $combinations[$row['id_product_attribute']]['unit_impact'] = Tools::convertPriceFull($row['unit_price_impact'], null, Context::getContext()->currency);
            $combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
            if ($row['available_date'] != '0000-00-00' && Validate::isDate($row['available_date'])) {
                $combinations[$row['id_product_attribute']]['available_date'] = $row['available_date'];
                $combinations[$row['id_product_attribute']]['date_formatted'] = Tools::displayDate($row['available_date'], Context::getContext()->language->id);
            } else {
                $combinations[$row['id_product_attribute']]['available_date'] = $combinations[$row['id_product_attribute']]['date_formatted'] = '';
            }
            // Do not need get images
        }
        // wash attributes list (if some attributes are unavailables and if allowed to wash it)
        if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
            foreach ($groups as &$group) {
                foreach ($group['attributes_quantity'] as $key => &$quantity) {
                    if ($quantity <= 0) {
                        unset($group['attributes'][$key]);
                    }
                }
            }
            foreach ($colors as $key => $color) {
                if ($color['attributes_quantity'] <= 0) {
                    unset($colors[$key]);
                }
            }
        }
        foreach ($combinations as $id_product_attribute => $comb) {
            $attribute_list = '';
            foreach ($comb['attributes'] as $id_attribute) {
                $attribute_list .= '\''.(int)$id_attribute.'\',';
            }
            $attribute_list = rtrim($attribute_list, ',');
            $combinations[$id_product_attribute]['list'] = $attribute_list;
        }
    }

    return array(
        'groups'            => $groups,
        'colors'            => (count($colors)) ? $colors : false,
        'combinations'      => $combinations,
        'combinationImages' => $combination_images
    );
}

function login()
{
    $ajax       = array('error' => true, 'message' => '', 'id_cus' => 0);
    $error      = 0;
    $message    = '';
    $id_cus     = 0;

    Hook::exec('actionBeforeAuthentication');

    $passwd             = trim(Tools::getValue('password'));
    $_POST['passwd']    = null;
    $email              = trim(Tools::getValue('username'));

    if (empty($email)) {
        $error = 1;
        $messages = Tools::displayError('An email address required.');
    } elseif (!Validate::isEmail($email)) {
        $error = 1;
        $message = Tools::displayError('Invalid email address.');
    } elseif (empty($passwd)) {
        $error = 1;
        $message = Tools::displayError('Password is required.');
    } elseif (!Validate::isPasswd($passwd)) {
        $error = 1;
        $message = Tools::displayError('Invalid password.');
    } else {
        $customer = new Customer();
        $authentication = $customer->getByEmail(trim($email), trim($passwd));
        if (isset($authentication->active) && !$authentication->active) {
            $error = 1;
            $message = Tools::displayError('Your account isn\'t available at this time, please contact us');
        } elseif (!$authentication || !$customer->id) {
            $error = 1;
            $message = Tools::displayError('Authentication failed.');
        } else {
            $id_cus = $customer->id;
            $error = 0;
            //$message = Tools::displayError('Authentication Successful.');
            $message = 'Authentication Successful.';
            $_SESSION['is_logged'] = array(
                'login' => 1,
                'email' => $customer->email,
                'id' => (int)($customer->id)
            );
            //Context::getContext()->cookie->__set('id_compare', isset(Context::getContext()->cookie->id_compare) ? Context::getContext()->cookie->id_compare : CompareProduct::getIdCompareByIdCustomer($customer->id));
            Context::getContext()->cookie->__set('id_customer', (int)($customer->id));
            Context::getContext()->cookie->__set('customer_lastname', $customer->lastname);
            Context::getContext()->cookie->__set('customer_firstname', $customer->firstname);
            Context::getContext()->cookie->__set('logged', 1);
            $customer->logged = 1;
            Context::getContext()->cookie->__set('is_guest', $customer->isGuest());
            Context::getContext()->cookie->__set('passwd', $customer->passwd);
            Context::getContext()->cookie->__set('email', $customer->email);
            // Add customer to the context
            Context::getContext()->customer = $customer;
            if (Configuration::get('PS_CART_FOLLOWING') && (empty(Context::getContext()->cookie->id_cart) || Cart::getNbProducts(Context::getContext()->cookie->id_cart) == 0) && $id_cart = (int)Cart::lastNoneOrderedCart(Context::getContext()->customer->id)) {
                Context::getContext()->cart = new Cart($id_cart);
            } else {
                $id_carrier = (int)Context::getContext()->cart->id_carrier;
                Context::getContext()->cart->id_carrier = 0;
                Context::getContext()->cart->setDeliveryOption(null);
                Context::getContext()->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)($customer->id));
                Context::getContext()->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)($customer->id));
            }
            Context::getContext()->cart->id_customer = (int)$customer->id;
            Context::getContext()->cart->secure_key = $customer->secure_key;
            Context::getContext()->cart->save();
            Context::getContext()->cart->id_cart = (int)Context::getContext()->cart->id;
            Context::getContext()->cookie->write();
            Context::getContext()->cart->autosetProductAddress();
            Hook::exec('actionAuthentication', array('customer' => Context::getContext()->customer));
            // Login information have changed, so we check if the cart rules still apply
            CartRule::autoRemoveFromCart(Context::getContext());
            CartRule::autoAddToCart(Context::getContext());
        }
    }
    $ajax['error']      = $error;
    $ajax['message']    = $message;
    $ajax['id_cus']     = $id_cus;

    echo json_encode($ajax);
    return;
}

function getProduct($id_product, $tse_id_category, $id_lang, $start, $limit, $order_by, $order_way, $id_category = false, $only_active = false, Context $context = null)
{
    $products = array('products' => array());
    if (!$context) {
        $context = Context::getContext();
    }

    $front = true;
    if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
        $front  = false;
    }

    if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
        die(Tools::displayError());
    }

    if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
        $order_by_prefix = 'p';
    } elseif ($order_by == 'name') {
        $order_by_prefix = 'pl';
    } elseif ($order_by == 'position') {
        $order_by_prefix = 'c';
    }

    if (strpos($order_by, '.') > 0) {
        $order_by = explode('.', $order_by);
        $order_by_prefix = $order_by[0];
        $order_by = $order_by[1];
    }

    $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
            FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p').'
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
            LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
            LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (s.`id_supplier` = p.`id_supplier`)'.
            ($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)' : '').'
            WHERE pl.`id_lang` = '.(int)$id_lang.
                ($id_category ? ' AND c.`id_category` = '.(int)$id_category : '').
                ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                //($only_active ? ' AND product_shop.`active` = 1' : '').' AND p.`id_product` <> '.(int)$id_product.' AND p.`design_product_id` != "" '.'
                ($only_active ? ' AND product_shop.`active` = 1' : '').' AND p.`design_product_id` != "" '.'
            ORDER BY '.(isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way).
            ($limit > 0 ? ' LIMIT '.(int)$start.', '.(int)$limit : '');

    $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    if ($order_by == 'price') {
        Tools::orderbyPrice($rq, $order_way);
    }

    $file_category      = _PS_ROOT_DIR_.'/tshirtecommerce/data/product_categories.json';
    $product_categories = array();
    $product_cates      = array();

    if (file_exists($file_category)) {
        $file_category_content = Tools::file_get_contents($file_category);

        if ($file_category_content !== false) {
            $file_category_json = json_decode($file_category_content);
            if (count($file_category_json)) {
                foreach ($file_category_json as $value) {
                    if ($value->cate_id == $tse_id_category) {
                        $product_categories[] = $value->product_id;
                    }
                }
            }
        }
    }

    foreach ($rq as &$row) {
        $row = Product::getTaxesInformations($row);
        if (count($product_categories)) {
            foreach ($product_categories as $value) {
                if ((int)$value == (int)$row['design_product_id']) {
                    $product_cates[] = $row;
                }
            }
        } else {
            $product_cates = ($tse_id_category == 0) ? $rq : array();
        }
    }
    if (count($product_cates)) {
        foreach ($product_cates as $product) {
            // Get product from products.json of tshirtecommerce system
            $design_product_id = isset($product['design_product_id']) ? $product['design_product_id'] : '';
            if (strpos($design_product_id, ":") === false) {
                $image = Image::getCover($product['id_product']);
                $link = new Link;
                $imagePath  = Tools::getShopProtocol().$link->getImageLink($product['link_rewrite'], $image['id_image']);
                // get design
                $print_type = '';
                $size = '';
                $design = new stdClass();
                $attribute = '';
                $file = _PS_ROOT_DIR_.'/tshirtecommerce/data/products.json';
                $array = array('products' => array());
                $min_order = isset($product['minimal_quantity']) ? $product['minimal_quantity'] : 1;
                $box_width = 500;
                $box_height = 500;

                $temp = array();

                if (file_exists($file)) {
                    $file_content = Tools::file_get_contents($file);

                    if ($file_content !== false) {
                        $file_json = json_decode($file_content);

                        if (isset($file_json->products) && count($file_json->products)) {
                            foreach ($file_json->products as $value) {
                                if ($design_product_id == $value->id) {
                                    $temp = json_decode(json_encode($value), true);

                                    if (isset($design)) {
                                        $design = $value->design;
                                    }

                                    $print_type = isset($value->print_type) ? $value->print_type : '';
                                    $size = isset($value->size) ? $value->size : '';
                                    $attribute = getAttributes($value->attributes);
                                    $attribute .= quantity($min_order);

                                    $box_width = isset($value->box_width) ? $value->box_width : 500;
                                    $box_height = isset($value->box_height) ? $value->box_height : 500;
                                }
                            }
                        }
                    }
                }

                $p = array(
                    'id' => $design_product_id,
                    'min_order' => $min_order,
                    'max_order' => 999999,
                    'design_product_id' => $design_product_id,
                    'design_product_title_img' => isset($product['design_product_title_img']) ? $product['design_product_title_img'] : '',
                    'description' => isset($product['description']) ? $product['description'] : '',
                    'short_description' => isset($product['description_short']) ? $product['description_short'] : '',
                    'title' => isset($product['name']) ? $product['name'] : '',
                    'size' => $size,
                    'published' => 1,
                    'sku' => isset($product['reference']) ? $product['reference'] : '',
                    'price' => isset($product['price']) ? $product['price'] : 0,
                    'print_type' => $print_type,
                    'tax' => '',
                    'design' => isset($design) ? $design : '',
                    'image' => $imagePath,
                    'attributes' => '',
                    'parent_id' => isset($product['id_product']) ? $product['id_product'] : 0,
                    'attribute' => $attribute,
                    'link_rewrite' => isset($product['link_rewrite']) ? $product['link_rewrite'] : '',
                    'category_id' => isset($product['id_category_default']) ? $product['id_category_default'] : '',
                    'box_width' => $box_width,
                    'box_height' => $box_height,
                );

                if (count($temp)) {
                    $p = array_merge($temp, $p);
                }

                $products['products'][] = $p;
            }
        }
    }

    echo json_encode($products);

    return;
}

function getAttributes($attribute)
{
    if (isset($attribute->name) && !empty($attribute->name)) {
        $attrs = new stdClass();

        if (is_string($attribute->name)) {
            $attrs->name = json_decode($attribute->name);
        } else {
            $attrs->name = $attribute->name;
        }

        if (is_string($attribute->titles)) {
            $attrs->titles = json_decode($attribute->titles);
        } else {
            $attrs->titles = $attribute->titles;
        }

        if (is_string($attribute->prices)) {
            $attrs->prices = json_decode($attribute->prices);
        } else {
            $attrs->prices = $attribute->prices;
        }

        if (is_string($attribute->type)) {
            $attrs->type = json_decode($attribute->type);
        } else {
            $attrs->type = $attribute->type;
        }

        $html = '';
        for ($i = 0; $i < count($attrs->name); $i++) {
            $html .= '<div class="form-group product-fields">';
            $html .= '<label for="fields">'.$attrs->name[$i].'</label>';
            $id = 'attribute['.$i.']';
            $html .= field($attrs->name[$i], $attrs->titles[$i], $attrs->prices[$i], $attrs->type[$i], $id);
            $html .= '</div>';
        }

        return $html;
    } else {
        return '';
    }
}

function field($name, $title, $price, $type, $id)
{
    $html = '<div class="dg-poduct-fields">';
    switch($type) {
        case 'checkbox':
            for ($i = 0; $i < count($title); $i++) {
                $html .= '<label class="checkbox-inline">';
                $html .=    '<input type="checkbox" name="'.$id.'['.$i.']" value="'.$i.'"> '.$title[$i];
                $html .= '</label><br />';
            }
            break;

        case 'selectbox':
            $html .= '<select class="form-control input-sm" name="'.$id.'">';
            for ($i = 0; $i < count($title); $i++) {
                $html .= '<option value="'.$i.'">'.$title[$i].'</option>';
            }
            $html .= '</select><br />';
            break;

        case 'radio':
            for ($i = 0; $i < count($title); $i++) {
                $html .= '<label class="radio-inline">';
                $html .=    '<input type="radio" name="'.$id.'" value="'.$i.'"> '.$title[$i];
                $html .= '</label><br />';
            }
            break;

        case 'textlist':
            $html .= '<style>.product-quantity{display:none;}</style><ul class="p-color-sizes list-number col-md-12">';
            for ($i = 0; $i < count($title); $i++) {
                $html .= '<li>';
                $html .=    '<label>'.$title[$i].'</label>';
                $html .=    '<input type="text" class="form-control input-sm size-number" name="'.$id.'['.$i.']">';
                $html .= '</li>';
            }
            $html .= '</ul>';
            break;
        default:
            break;
    }
    $html .= '</div>';

    return $html;
}

function quantity($min = 1, $name = 'Quantity', $name2 = 'minimum quantity: '){
    if ($min < 1) $min = 1;
    $html = '<div class="form-group product-fields product-quantity">';
    $html .=    '<label class="col-sm-4">'.$name.'</label>';
    $html .=    '<div class="col-sm-6">';
    $html .=        '<input type="text" class="form-control input-sm" value="0" data-count="'.$min.'" name="quantity" id="quantity">';
    $html .=    '</div>';
    //$html .=    '<span class="help-block"><small>'.$name2.$min.'</small></span>';
    $html .= '</div>';

    return $html;
}

function changeProduct($id_product, $id_lang = 1, $id_shop = 1)
{
    $json       = array();
    $product    = new Product($id_product, false, $id_lang, $id_shop);
    $sql        = "SELECT `design_product_id` FROM `"._DB_PREFIX_."product` WHERE `id_product` = ".(int)$product->id;
    $query      = Db::getInstance()->getValue($sql);
    $design_id  = trim($query);
    $json['parent_id']  = $product->id;
    $json['max_order']  = 99999;
    $json['price']      = $product->price;
    $json['min_order']  = $product->minimal_quantity;
    $json['design_id']  = $design_id;
    //$json['title']    = $product->name;
    //$json['ecotax']   = $product->ecotax;

    echo json_encode($json);
    return;
}

function decryptStore($order_id)
{
    include_once('store.php');
    $store      = new Store();
    $arts       = '';
    $api_key    = '';
    $file_settings = _PS_ROOT_DIR_.'/tshirtecommerce/data/settings.json';

    if (file_exists($file_settings)) {
        $content = Tools::file_get_contents($file_settings);

        if ($content != false) {
            $settings = json_decode($content, true);

            if (isset($settings['store']) && isset($settings['store']['api'])) {
                $api_key = $settings['store']['api'];
            }
        }
    }

    $order = new Order($order_id);
    $customizations = Customization::getOrderedCustomizations($order->id_cart);

    $details = array();
    foreach ($customizations as $key => $value) {
        $id_cusomization = $value['id_customization'];
        $query = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT `tshirtecommerce_design_cart_id`, `tshirtecommerce_design_type`
            FROM `'._DB_PREFIX_.'customization`
            WHERE `id_customization` = '.(int)$id_cusomization.'
                AND `id_address_delivery` = '.(int)$order->id_address_delivery.'
                AND `id_cart` = '.(int)$order->id_cart.'
                AND `in_cart` = 1
        ');
        if ($query != false && count($query) > 0) {
            $temp = array();
            foreach ($query as $row) {
                $temp['tshirtecommerce_design_order_id'] = $row['tshirtecommerce_design_cart_id'];
                $temp['tshirtecommerce_design_type'] = $row['tshirtecommerce_design_type'];
            }
            if (count($temp) > 0) $details[] = $temp;
        }
    }

    $path_order_design = _PS_ROOT_DIR_.'/tshirtecommerce/cache/cart/';
    require_once ROOT .DS. 'includes' .DS. 'functions.php';
    $dg = new dg();
    $cache = $dg->cache('cart');
    $ids = array();
    $arts = array();
    $qty = 1;
    $prices = 0;
    $tshirtecommerce_design_order_id = '';
    $ajax = array('error' => 0, 'msg' => '', 'reload' => 0);

    if ($details && count($details)) {
        foreach ($details as $detail) {
            $tshirtecommerce_design_order_id = $detail['tshirtecommerce_design_order_id'];
            //$tshirtecommerce_design_type = $detail['tshirtecommerce_design_type'];
            $design = $cache->get($tshirtecommerce_design_order_id);

            if (isset($design['item']) && isset($design['item']['qty'])) {
                $qty = (int)($design['item']['qty'] / 10);
            }

            if ($qty < 1) {
                $qty = 1;
            }

            if (isset($design['vector'])) {
                $vectors = json_decode($design['vector'], true);

                if (count($vectors)) {
                    foreach($vectors as $view => $items) {
                        if (count($items)) {
                            foreach($items as $item) {
                                if (isset($item['clipar_type']) && empty($item['clipar_paid'])) {
                                    if (empty($item['price'])) {
                                        $item['price'] = 0;
                                    }

                                    if (is_string($item['file'])) {
                                        $file = $item['file'];
                                    }

                                    if (isset($item['file']['type'])) {
                                        $file = $item['file']['type'];
                                    }

                                    $arts[$item['clipart_id']] = array(
                                        'view' => $view,
                                        'id' => $item['clipart_id'],
                                        'title' => $item['title'],
                                        'thumb' => $item['thumb'],
                                        'file' => $file,
                                        'price' => $item['price'],
                                    );

                                    if (!in_array($item['clipart_id'].'-'.$qty, $ids)) {
                                        $prices += $item['price'] * $qty;
                                        $ids[] = $item['clipart_id'].'-'.$qty;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $params_ids = '';
            $idx = 0;
            foreach ($ids as $str) {
                $params_ids .= ($idx < (count($ids) - 1)) ? $str.'_' : $str;
                $idx ++;
            }

            $ajax = $store->store_ajax_key($api_key, $params_ids, $tshirtecommerce_design_order_id);
        }
    }

    echo json_encode($ajax);
    return;
}

function quicksetupStore($params)
{
    $return = false;
    $output = array();

    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce'.DS);

    include_once _PS_ROOT_DIR_.'/tshirtecommerce/includes/functions.php';
    $dg = new dg();

    // Update layout default
    $file = _PS_ROOT_DIR_.'/tshirtecommerce/data/layouts.json';
    $layout = $dg->getLayouts();
    if (count($params) && count($layout) && isset($params['quicksetup_layout'])) {
        foreach ($layout as &$row) {
            if ($row['name'] == $params['quicksetup_layout']) {
                $row['default'] = 1;
            } else {
                $row['default'] = 0;
            }
        }
        $json = json_encode($layout);
        $return = $dg->WriteFile($file, $json);
    }
    if ($return === false) {
        $output['error'] = 1;
        $output['msg'] = sprintf('Can not write file %s. Please set permission and try again.', $file);
    }

    // Update language default
    $lang = _PS_ROOT_DIR_.'/tshirtecommerce/data/languages.json';
    $langs = json_decode(Tools::file_get_contents($lang), true);
    if (count($params) && count($langs) && isset($params['quicksetup_language'])) {
        foreach ($langs as &$row) {
            if ($row['code'] == $params['quicksetup_language']) {
                $row['default'] = 1;
            } else {
                $row['default'] = 0;
            }
        }
        $json = json_encode($langs);
        $return = $dg->WriteFile($lang, $json);
    }
    if ($return === false) {
        $output['error'] = 1;
        $output['msg'] = sprintf('Can not write file %s. Please set permission and try again.', $lang);
    }

    if ($return === true) {
        $output['error'] = 0;
        $output['msg'] = 'Update success.';
    }

    echo (count($output) ? json_encode($output) : '');
    return;
}

function quicksetupImport($params)
{
    $json = array();
    $key = isset($params['api']) ? trim($params['api']) : '';
    if (empty($key)) {
        $json['error'] = 1;
        $json['msg'] = 'Your Key is wrong. Please check again.';
    } else {
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce');
        if (file_exists(ROOT.DS.'includes'.DS.'functions.php')) {
            include_once (ROOT.DS.'includes'.DS.'functions.php');
            $dg = new dg();
            $settings = $dg->getSetting();

            $url = 'http://api.9file.net/api/key/api_key/'.$key;
            $info = json_decode(Tools::file_get_contents($url));
            $verified = isset($info->error) ? 0 : 1;

            // Save the data to json
            if (count($settings)) {
                if (isset($settings->store)) $settings->store = new stdClass();
                $settings->store->api = $key;
                $settings->store->verified = $verified;
                $settings->store->enable = 1;
            }
            $file = ROOT.DS.'/data/settings.json';
            $write = $dg->WriteFile($file, json_encode($settings));

            if ($write === true) {
                // Get data from tshirtecommerce server
                if( isset($settings->store)
                    && isset($settings->store->api)
                    && $settings->store->api != ''
                    && isset($settings->store->verified)
                    && $settings->store->verified == 1
                    && isset($settings->store->enable)
                    && $settings->store->enable == 1
                ) {
                    include_once(ROOT.DS.'api.php');
                    $api = new API($settings->store->api);
                    $api->updateArts();
                    $api->updateIdeas();
                }
                $json['error'] = 0;
                $json['msg'] = 'The data synchronization is successful.';
            } else {
                $json['error'] = 1;
                $json['msg'] = sprintf('Can not write file %s.', $file);
            }
        }
    }

    echo (count($json)) ? json_encode($json) : '';
    return;
}

function quicksetupProducts()
{
    $json = array();

    // download products demo package
    $file = 'http://updates.tshirtecommerce.com/products_import.zip';
    $packages = Tools::file_get_contents($file);
    $new = _PS_ROOT_DIR_.'/products_import'.time().'.zip';
    if (@file_put_contents($new, $packages)) {
        if (Tools::ZipExtract($new, _PS_ROOT_DIR_)) {
            unlink($new);

            $json['error'] = 0;
            $json['msg'] = 'Download and extract datas success.';
            $json['log'] = 'Download and extract datas success.';
        } else {
            $json['error'] = 1;
            $json['msg'] = 'Can not extract file on root. Please check permission again.';
            $json['log'] = 'Can not extract file on root. Please check permission again.';
        }
    } else {
        $json['error'] = 1;
        $json['msg'] = 'Can not download demo package datas.';
        $json['log'] = 'Can not download demo package datas.';
    }

    // import products demo package
    if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce');
    include_once (ROOT.DS.'includes'.DS.'functions.php');
    $dg = new dg();
    $demo_products = $dg->getProducts();

    /* Update colors attributes */
    $colors = array();
    if (count($demo_products)) {
        foreach ($demo_products as $demo) {
            if (isset($demo->design) && isset($demo->design->color_hex) && count($demo->design->color_hex)) {
                foreach ($demo->design->color_hex as $index_color => $color) {
                    if (!isset($colors[$index_color])) {
                        $colors['#'.strtoupper($color)] = $demo->design->color_title[$index_color];
                    }
                }
            }
        }
    }
    $attributes_groups = AttributeGroup::getAttributesGroups(Context::getContext()->language->id);
    $id_attribute_group = 1;
    $is_exit_attribute_color = false;
    if (count($attributes_groups)) {
        foreach ($attributes_groups as $attributes_group) {
            if ($attributes_group['is_color_group'] == 1) {
                $id_attribute_group = $attributes_group['id_attribute_group'];
                $is_exit_attribute_color = true;
                break;
            }
        }
    }
    if ($is_exit_attribute_color === false) {
        $query = Db::getInstance()->execute('
            INSERT INTO `'._DB_PREFIX_.'attribute_group`(`is_color_group`, `group_type`, `position`)
            VALUES(1, "color", 2)
        ');
        if ($query === false) {
            $json['error'] = 1;
            $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
            $json['log'] = 'Insert to attribute_group table failed';
            echo json_encode($json);
            return;
        }
        $id_attribute_group = Db::getInstance()->Insert_ID();
        $query = DB::getInstance()->execute('
            INSERT INTO `'._DB_PREFIX_.'id_attribute_group`(`id_attribute_group`,`id_lang`,`name`,`public_name`)
            VALUES('.(int)$id_attribute_group.','.(int)Context::getContext()->language->id.',"Color","Color")
        ');
        if ($query === false) {
            $json['error'] = 1;
            $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
            $json['log'] = 'Insert to id_attribute_group table failed :: 1081';
            echo json_encode($json);
            return;
        }
        $query = DB::getInstance()->execute('
            INSERT INTO `'._DB_PREFIX_.'attribute_group_shop`(`id_attribute_group`,`id_shop`)
            VALUES('.(int)$id_attribute_group.','.(int)Context::getContext()->shop->id.')
        ');
        if ($query === false) {
            $json['error'] = 1;
            $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
            $json['log'] = 'Insert to attribute_group_shop table failed :: 1092';
            echo json_encode($json);
            return;
        }
    }
    $ps_colors = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT *
        FROM `'._DB_PREFIX_.'attribute`
        WHERE `id_attribute_group` = '.(int)$id_attribute_group.'
    ');
    if ($ps_colors === false) {
            $json['error'] = 1;
            $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
            $json['log'] = 'Insert to attribute table failed :: 1105';
            echo json_encode($json);
            return;
        }
    $ps_colors_check = array();
    if ($ps_colors !== false && count($ps_colors)) {
        foreach ($ps_colors as $row) {
            $ps_colors_check[] = strtoupper($row['color']);
        }
    }
    if (count($colors)) {
        foreach ($colors as $key => $value) {
            if (!in_array($key, $ps_colors_check)) {
                $query = Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'attribute`(`id_attribute_group`,`color`,`position`)
                    VALUES('.(int)$id_attribute_group.',"'.$key.'",19)
                ');
                if ($query === false) {
                    $json['error'] = 1;
                    $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                     $json['log'] = 'Insert to attribute table failed :: 1125';
                    echo json_encode($json);
                    return;
                }
                $id_attribute_new = Db::getInstance()->Insert_ID();
                $query = Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'attribute_lang`(`id_attribute`,`id_lang`,`name`)
                    VALUES('.(int)$id_attribute_new.','.(int)Context::getContext()->language->id.',"'.$value.'")
                ');
                if ($query === false) {
                    $json['error'] = 1;
                    $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                    $json['log'] = 'Insert to attribute_lang table failed :: 1137';
                    echo json_encode($json);
                    return;
                }
                $query = Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'attribute_shop`(`id_attribute`,`id_shop`)
                    VALUES('.(int)$id_attribute_new.','.(int)Context::getContext()->shop->id.')
                ');
                if ($query === false) {
                    $json['error'] = 1;
                    $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                    $json['log'] = 'Insert to attribute_shop table failed :: 1148';
                    echo json_encode($json);
                    return;
                }
            }
        }
    }
    $id_supplier_default = 1;
    $suppliers = Supplier::getSuppliers();
    if (count($suppliers)) {
        foreach ($suppliers as $supplier) {
            if ($supplier['active'] == 1) {
                $id_supplier_default = $supplier['id_supplier'];
                break;
            }
        }
    }

    $design_views = array('front', 'back', 'left', 'right');
    $id_shop_list = Shop::getContextListShopID();
    $id_shop_default = (in_array(Configuration::get('PS_SHOP_DEFAULT'), $id_shop_list) == true) ? Configuration::get('PS_SHOP_DEFAULT') : min($id_shop_list);
    if (count($demo_products)) {
        $legends = Tools::getValue('legend');
        if (!is_array($legends)) $legends = (array)$legends;

        // Get category default
        $id_category_default = 0;
        $id_category_default = Db::getInstance()->getValue('SELECT DISTINCT `id_category` FROM `'._DB_PREFIX_.'category_product`');
        if ($id_category_default === false) $id_category_default = 2; // default is home

        // get id_manufacturer
        $id_manufacturer = 1;
        $id_manufacturer = Db::getInstance()->getValue('SELECT `id_manufacturer` FROM `'._DB_PREFIX_.'manufacturer` WHERE `active` = 1');
        if ($id_manufacturer === false) $id_manufacturer = 1;

        foreach ($demo_products as $demo) {
            if ($supplier['active'] == 1) {
                $cache_default_attribute = 0;
                $query = Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'product`(
                        `id_supplier`, `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`,
                        `ean13`, `upc`, `quantity`, `minimal_quantity`,
                        `price`, `wholesale_price`, `unity`,
                        `reference`, `supplier_reference`, `location`, `active`, `redirect_type`, `available_for_order`,
                        `available_date`, `condition`, `cache_default_attribute`, `date_add`, `date_upd`,
                        `design_product_id`, `design_product_title_img`, `tshirtecommerce_design_type`
                    ) VALUES (
                        '.(int)$id_supplier_default.', '.(int)$id_manufacturer.', '.(int)$id_category_default.', '.(int)$id_shop_default.', 1,
                        0, null, '.(int)$demo->max_oder.', '.(int)$demo->min_order.',
                        '.$demo->price.', '.((isset($demo->sale_price) && !empty($demo->sale_price)) ? $demo->sale_price : 0).', null,
                        "'.$demo->sku.'", null, null, 1, "404", 1,
                        "0000-00-00", "new", 0, "'.date("Y-m-d H:i:s").'", "'.date("Y-m-d H:i:s").'",
                        '.$demo->id.', "", ""
                )');
                if ($query === false) {
                    $json['error'] = 1;
                    $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                    $json['log'] = 'Insert to product table failed :: 1128';
                    echo json_encode($json);
                    return;
                }
                $id_product_new = Db::getInstance()->Insert_ID();
                Configuration::updateValue('TSHIRTECOMMERCE_DOWNLOADABLE', $id_product_new);
                $query = Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'product_shop`(
                        `id_product`,`id_shop`,`id_category_default`,`id_tax_rules_group`,
                        `minimal_quantity`,`price`,
                        `wholesale_price`,
                        `unity`,`active`,`redirect_type`,`available_date`,
                        `cache_default_attribute`,`date_add`,`date_upd`
                    ) VALUES(
                        '.(int)$id_product_new.','.(int)Context::getContext()->shop->id.',1,1,
                        '.(int)$demo->min_order.','.(float)$demo->price.',
                        '.((isset($demo->sale_price) && $demo->sale_price > 0) ? $demo->sale_price : 0).',
                        null,1,"404","'.date("Y-m-d H:i:s").'",
                        '.(int)$cache_default_attribute.',"'.date("Y-m-d H:i:s").'","'.date("Y-m-d H:i:s").'"
                )');
                if ($query === false) {
                    $json['error'] = 1;
                    $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                    $json['log'] = 'Insert to product_shop table failed :: 1306';
                    echo json_encode($json);
                    return;
                }
                $demo->description = str_replace('"', '\\"', $demo->description);
                $demo->short_description = str_replace('"', '\\"', $demo->short_description);
                $demo->title = str_replace('"', '\\"', $demo->title);
                $query = Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'product_lang`(
                        `id_product`,`id_shop`,`id_lang`,
                        `description`,`description_short`,
                        `link_rewrite`,
                        `meta_description`,`meta_keywords`,`meta_title`,
                        `name`,`available_now`,`available_later`
                    ) VALUES(
                        '.(int)$id_product_new.','.(int)Context::getContext()->shop->id.','.(int)Context::getContext()->language->id.',
                        "'.$demo->description.'","'.$demo->short_description.'",
                        "'.str_replace(' ', '-', $demo->title).'",
                        null,null,null,
                        "'.$demo->title.'","In stock",null
                )');
                if ($query === false) {
                    $json['error'] = 1;
                    $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                    $json['log'] = 'Insert to product_lang table failed :: 1330';
                    echo json_encode($json);
                    return;
                }
                $design_images = array();
                $images_type = ImageType::getImagesTypes();
                foreach ($design_views as $view) {
                    if (isset($demo->design->$view) && count($demo->design->$view)) {
                        foreach ($demo->design->$view as $view_row) {
                            if (!empty($view_row)) {
                                $view_row = str_replace('\'', '"', $view_row);
                                $json = json_decode($view_row, true);
                                if (isset($json[1]['img'])) {
                                    $design_images[] = _PS_ROOT_DIR_.'/tshirtecommerce/'.$json[1]['img'];
                                }
                            }
                        }
                    }
                }
                $id_images_new = array();
                if (count($design_images)) {
                    $is_cover = false;
                    foreach ($design_images as $d_img) {
                        $position = Image::getHighestPosition($id_product_new) + 1;
                        $cover = ($is_cover === false) ? 1 : 0;
                        if ($cover === null || empty($cover)) $cover = 0;
                        // PrestaShop 9 uses modern query with cover field
                        $count = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'image` WHERE `id_product` = '.(int)$id_product_new.' AND `cover`='.(int)$cover);
                        if ($count == 0) {
                            $query = Db::getInstance()->execute('
                                INSERT INTO `'._DB_PREFIX_.'image`(
                                    `id_product`,`position`,`cover`
                                ) VALUES (
                                '.(int)$id_product_new.','.(int)$position.','.$cover.')
                            ');
                        }
                        if ($query === false) {
                            $json['error'] = 1;
                            $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                            echo json_encode($json);
                            return;
                        }
                        $is_cover = true;
                        $id_image_new = Db::getInstance()->Insert_ID();

                        /* Fixed product image wrong */
                        $dst = _PS_ROOT_DIR_.'/img/p/';
                        $id_image_new_str_split = str_split((string)$id_image_new);
                        foreach ($id_image_new_str_split as $sfolder) {
                            if ($sfolder != null && !empty($sfolder)) {
                                $dst .= $sfolder.'/';
                            }
                            if (!file_exists($dst)) {
                                @mkdir($dst);
                                // PrestaShop 9 uses Tools::chmodr()
                                if (function_exists('chmodr')) {
                                    chmodr($dst, 0755);
                                } else {
                                    Tools::chmodr($dst, 0755);
                                }
                            }
                        }
                        if (!file_exists($dst)) {
                            @mkdir($dst);
                            // PrestaShop 9 uses Tools::chmodr()
                            if (function_exists('chmodr')) {
                                chmodr($dst, 0755);
                            } else {
                                Tools::chmodr($dst, 0755);
                            }
                        }
                        @copy(_PS_ROOT_DIR_.'/tshirtecommerce/prestashop/quicksetup/copy/fileType', $dst.'fileType');
                        @copy(_PS_ROOT_DIR_.'/tshirtecommerce/prestashop/quicksetup/copy/index.php', $dst.'index.php');
                        @copy($d_img, $dst.$id_image_new.'.jpg');
                        if (count($images_type)) {
                            foreach ($images_type as $itype) {
                                if ($itype['products'] == 1) {
                                    $new_img = $dst.$id_image_new.'-'.$itype['name'].'.jpg';
                                    @copy($d_img, $new_img);
                                    ImageManager::resize($d_img, $new_img, $itype['width'], $itype['height']);
                                }
                            }
                        }
                        /* \Fixed product image wrong */

                        $id_images_new[] = $id_image_new;
                        $count = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'image_lang` WHERE `id_image` = '.(int)$id_image_new.' AND `id_lang` = '.(int)Context::getContext()->language->id);
                        if ($count == 0) {
                            $query = Db::getInstance()->execute('
                                INSERT INTO `'._DB_PREFIX_.'image_lang`(
                                    `id_image`, `id_lang`, `legend`
                                ) VALUES(
                                    '.(int)$id_image_new.', '.(int)Context::getContext()->language->id.', null
                            )');
                        }
                        if ($query === false) {
                            $json['error'] = 1;
                            $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                            $json['log'] = 'Insert to image_lang table failed :: 1410';
                            echo json_encode($json);
                            return;
                        }
                        // PrestaShop 9 uses complete image_shop insert
                        $count = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'image_shop` WHERE `id_product` = '.(int)$id_product_new.' AND `id_shop` = '.(int)Context::getContext()->shop->id.' AND `cover` = '.(int)$cover);
                        if ($count == 0) {
                            $query = Db::getInstance()->execute('
                                INSERT INTO `'._DB_PREFIX_.'image_shop`(
                                    `id_product`,`id_image`,`id_shop`,`cover`
                                ) VALUES (
                                    '.(int)$id_product_new.','.(int)$id_image_new.',
                                    '.(int)Context::getContext()->shop->id.','.$cover.'
                            )');
                        }
                        if ($query === false) {
                            $json['error'] = 1;
                            $json['msg'] = 'Can not write data. Please try again or click Skip this Step to continue.';
                            $json['log'] = 'Insert to image_shop table failed :: 1425';
                            echo json_encode($json);
                            return;
                        }
                    }
                }
            }
        }
    }

    $json['error'] = 0;
    $json['msg'] = 'Import success.';
    $json['log'] = 'Import success.';

    echo (count($json)) ? json_encode($json) : '';
}

function chmodr($path, $filemode)
{
    if (!is_dir($path)) {
        return @chmod($path, $filemode);
    }
    $dh = opendir($path);
    while (($file = readdir($dh)) !== false) {
        if ($file != '.' && $file != '..') {
            $fullpath = $path.'/'.$file;
            if (is_link($fullpath)) {
                return false;
            } elseif (!is_dir($fullpath) && !@chmod($fullpath, $filemode)) {
                return false;
            } elseif (!chmodr($fullpath, $filemode)) {
                return false;
            }
        }
    }
    closedir($dh);
    if (@chmod($path, $filemode)) {
        return true;
    } else {
        return false;
    }
}

function getProductLinked()
{
    $return = array();

    $array = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT `design_product_id`
        FROM `'._DB_PREFIX_.'product`
        WHERE `design_product_id` IS NOT NULL AND `design_product_id` <> ""
    ');

    if ($array !== false && count($array)) {
        foreach ($array as $row) {
            if (isset($row['design_product_id']) && !empty($row['design_product_id']))
                $return[] = $row['design_product_id'];
        }
    }

    if (count($return) <= 0) {
        echo 'false';
    } else {
        echo json_encode($return);
    }
    return;
}

function tshirt_store_import_all($type = 'art')
{
    if ($type != 'art') $type   = 'idea';

    if ($type == 'art') tshirt_store_art_import();
    else tshirt_store_idea_import();
}

function tshirt_store_art_import()
{
    if (defined('ROOT') == false) define('ROOT', dirname(dirname(dirname(__FILE__))). '/tshirtecommerce');
    if (defined('DS') == false) define('DS', DIRECTORY_SEPARATOR);

    include_once (ROOT .DS. 'includes' .DS. 'functions.php');
    $dg = new dg();

    $settings = $dg->getSetting();
    if (isset($settings->store)
        && isset($settings->store->api)
        && $settings->store->api != ''
        && isset($settings->store->verified)
        && $settings->store->verified == 1
        && isset($settings->store->enable)
        && $settings->store->enable == 1
    ) {
        include_once(dirname(dirname(dirname(__FILE__))). '/tshirtecommerce/api.php');
        $api = new API($settings->store->api);

        // load clipart
        $api->updateArts();
    }
    exit;
}

function tshirt_store_idea_import()
{
    if (defined('ROOT') == false) define('ROOT', dirname(dirname(dirname(__FILE__))). '/tshirtecommerce');
    if (defined('DS') == false) define('DS', DIRECTORY_SEPARATOR);

    include_once (ROOT .DS. 'includes' .DS. 'functions.php');
    $dg = new dg();

    $settings = $dg->getSetting();
    if (isset($settings->store)
        && isset($settings->store->api)
        && $settings->store->api != ''
        && isset($settings->store->verified)
        && $settings->store->verified == 1
        && isset($settings->store->enable)
        && $settings->store->enable == 1
    ) {
        include_once(dirname(dirname(dirname(__FILE__))). '/tshirtecommerce/api.php');
        $api    = new API($settings->store->api);

        // load design template
        $api->updateIdeas();
    }
    exit;
}

$method         = Tools::getValue('method');
$product_id     = Tools::getValue('id');
$shop_id        = Tools::getValue('shop', Context::getContext()->shop->id);
$lang_id        = Tools::getValue('lang', Context::getContext()->language->id);
if (empty($lang_id)) {
    $lang_id = (int)Configuration::get('PS_LANG_DEFAULT');
}
$attributes     = Tools::getValue('attributes', array());
$product        = new Product($product_id, true, $lang_id, $shop_id);
$id_customer    = Tools::getValue('id_customer', Context::getContext()->customer->id);
if (empty($id_customer)) {
    $id_customer = 0;
}
$id_currency    = isset(Context::getContext()->currency) ? Context::getContext()->currency->id : Tools::getValue('id_currency', 1);
$id_country     = Tools::getValue('id_country', (int)Configuration::get('PS_COUNTRY_DEFAULT'));
$id_group       = Tools::getValue('id_group', 0);
$quantity       = Tools::getValue('quantity', 1);
$id_category    = Tools::getValue('category', array());
$order_id       = Tools::getValue('order_id', 0);

$quicksetup_layout   = Tools::getValue('layout', 'default');
$quicksetup_language = Tools::getValue('language', 'en');

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('ROOT')) define('ROOT', _PS_ROOT_DIR_.DS.'tshirtecommerce');

$params = array();

switch ($method) {

    case 'tattributes':
        $eattributes = Tools::getValue('eattributes', array());
        $tsajax->getTshirtecommerceAttributes($product, Tools::getValue('tshirtecommerce_product_id', 0), $eattributes);
        break;

    case 'prices':
        calcPrices($product, $attributes, $lang_id, $id_customer, $id_currency, $id_country, $id_group, $quantity, $shop_id);
        break;

    case 'attributes':
        renderAttributesHtml($product, $product_id, $lang_id, $id_currency);
        break;

    case 'products':
        getProduct($product_id, $id_category, $lang_id, 0, 999999999, 'date_upd', 'DESC');
        break;

    case 'changeproduct':
        changeProduct($product_id, $lang_id, $shop_id);
        break;

    case 'login':
        login();
        break;

    case 'store':
        decryptStore($order_id);
        break;

    case 'updatestore':
        tshirt_store_import_all(Tools::getValue('type', 'art'));
        break;

    case 'quicksetup2':
        $params['quicksetup_layout']    = $quicksetup_layout;
        $params['quicksetup_language']  = $quicksetup_language;
        quicksetupStore($params);
        break;

    case 'quicksetup3':
        $params['api'] = Tools::getValue('api_key', '');
        quicksetupImport($params);
        break;

    case 'quicksetup4':
        quicksetupProducts();
        break;

    case 'linked':
        getProductLinked();
        break;

    case 'customization':
        $params = Tools::getAllValues();
        $files = isset($_FILES['customization_file']) ? $_FILES['customization_file'] : array();

        $files_uploaded = array();
        if (count($files) && isset($files['tmp_name']) && count($files['tmp_name'])) {
            foreach ($files['tmp_name'] as $key => $file_tmp_name) {
                if ($files['error'][$key] == 0) {
                    $files_uploaded[$key] = array(
                        'name' => $files['name'][$key],
                        'type' => isset($files['type']) ? $files['type'][$key] : $files['mime'][$key],
                        'tmp_name' => $file_tmp_name,
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    );
                }
            }
        }

        $params['customization_file'] = $files_uploaded;
        if (!isset($params['task']) || empty($params['task'])) {
            $params['task'] = Tools::getValue('task', '');
        }
        if (empty($params['task'])) {
            break;
        }
        $tsajax->updateCustomization($product, $params);
        break;

    default:
        break;
}
exit;
