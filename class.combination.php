<?php

require_once dirname(__FILE__).'/tshirtecommerce.php';
include_once dirname(__FILE__).'/class.customization.php';

class TshirtecommerceCombination
{
    protected $context;
    protected $product;
    protected $groups;
    protected $params;

    function __construct($id_product, $params = array(), $context = null)
    {
        $this->context = ($context != null) ? $context : Context::getContext();
        $this->product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);
        $this->params = $params;

        $this->groups = $this->getAttributesGroupsFull();

        $this->module = new Tshirtecommerce();
        $this->customization = new TshirtecommerceCustomizationsPresta($this->module);
    }

    public function genHtml($id_product)
    {
        $html = '';
        if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) define('_PS_PRICE_COMPUTE_PRECISION_', 2);

        $groups = $this->getAttributesGroups();

        $product = new Product($id_product);
        $customizations = $this->customization->getCustomizations($product);

        $html .= '<div class="content-y ps-attributes-combinations">';
        if (count($groups)) {
            $currency = new Currency($this->context->currency->id);
            $currency->prefix = trim($currency->prefix);
            $currency->suffix = trim($currency->suffix);
            $char_currency = (!empty($currency->prefix)) ? $currency->prefix : $currency->suffix;
            $pos_sym_currency = (!empty($currency->prefix)) ? 'left' : 'right';

            $html .= '<form method="POST" id="tool_cart_ps" name="tool_cart_ps" action="">';
            $html .= '<input type="hidden" name="token" value="'.Tools::getToken(false).'" />';
            $html .= '<input type="hidden" name="id_product" value="'.$this->product->id.'" id="product_page_product_id" />';
            $html .= '<input type="hidden" name="add" value="1" />';
            $html .= '<input type="hidden" name="id_product_attribute" id="idCombination" value="" />';
            $html .= '<input type="hidden" name="id_currency" value="'.$this->context->currency->id.'" />';
            $html .= '<input type="hidden" name="sym_currency" value="'.$char_currency.'" />';
            $html .= '<input type="hidden" name="pos_sym_currency" value="'.$pos_sym_currency.'" />';
            $html .= '<input type="hidden" name="compute_precision" value="'._PS_PRICE_COMPUTE_PRECISION_.'" />';

            $html .= '<div class="product-variants">';
            foreach ($groups as $id_attribute_group => $group) {
                $html .= '<div class="clearfix product-variants-item">';
                $html .= '<span class="control-label">'.$group['name'].'</span>';
                if ($group['group_type'] == 'select') {
                    $html .= '<select class="form-control form-control-select" onchange="prestashop.combination(this)" id="group_'.$id_attribute_group.'" data-product-attribute="'.$id_attribute_group.'" name="group['.$id_attribute_group.']">';
                    foreach ($group['attributes'] as $id_attribute => $group_attribute) {
                        $html .= '<option value="'.$id_attribute.'" title="'.$group_attribute['name'].'"'.($group_attribute['selected'] ? ' selected="selected"' : '').'>'.$group_attribute['name'].'</option>';
                    }
                    $html .= '</select>';
                } elseif ($group['group_type'] == 'color') {
                    $html .= '<ul id="group_'.$id_attribute_group.'">';
                    foreach ($group['attributes'] as $id_attribute => $group_attribute) {
                        $html .= '<li class="float-xs-left input-container"><label>';
                        $html .= '<input onclick="prestashop.combination(this)" class="input-color" type="radio" data-product-attribute="'.$id_attribute_group.'" name="group['.$id_attribute_group.']" value="'.$id_attribute.'"'.($group_attribute['selected'] ? ' checked="checked"' : '').'>';
                        $html .= '<span'.
                                  ($group_attribute['html_color_code'] ? ' class="color '.($group_attribute['selected'] ? 'active' : '').'" style="background-color: '.$group_attribute['html_color_code'].'"' : '').
                                  ($group_attribute['texture'] ? ' class="color texture '.($group_attribute['selected'] ? 'active' : '').'" style="background-image: url('.$group_attribute['texture'].')"' : '').
                                '><span class="sr-only">'.$group_attribute['name'].'</span></span>';
                        $html .= '</label></li>';
                    }
                    $html .= '</ul>';
                } elseif ($group['group_type'] == 'radio') {
                    $html .= '<ul id="group_'.$id_attribute_group.'">';
                    foreach ($group['attributes'] as $id_attribute => $group_attribute) {
                        $html .= '<li class="input-container float-xs-left"><label>';
                        $html .= '<input onclick="prestashop.combination(this)" class="input-radio" type="radio" data-product-attribute="'.$id_attribute_group.'" name="group['.$id_attribute_group.']" value="'.$id_attribute.'"'.($group_attribute['selected'] ? ' checked="checked"' : '').'>';
                        $html .= '<span class="radio-label">'.$group_attribute['name'].'</span>';
                        $html .= '</label></li>';
                    }
                    $html .= '</ul>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '</form>';
        }
        if (!empty($customizations)) {
            $html .= $customizations;
        }
        $html .= '</div>';

        return $html;
    }

    protected function getAttributesGroups()
    {
        $groups = array();

        // @todo (RM) should only get groups and not all declination ?
        $attributes_groups = $this->product->getAttributesGroups($this->context->language->id);
        if (is_array($attributes_groups) && $attributes_groups) {
            foreach ($attributes_groups as $k => $row) {
                if (!isset($groups[$row['id_attribute_group']])) {
                    $groups[$row['id_attribute_group']] = array(
                        'group_name' => $row['group_name'],
                        'name' => $row['public_group_name'],
                        'group_type' => $row['group_type'],
                        'default' => -1,
                    );
                }

                $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = array(
                    'name' => $row['attribute_name'],
                    'html_color_code' => $row['attribute_color'],
                    'texture' => (@filemtime(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg')) ? _THEME_COL_DIR_.$row['id_attribute'].'.jpg' : '',
                    'selected' => false,
                );

                //$product.attributes.$id_attribute_group.id_attribute eq $id_attribute
                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$row['id_attribute_group']]['default'] = (int) $row['id_attribute'];
                }
                if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                    $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
                }
                $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int) $row['quantity'];
            }

            if (count($this->params)) {
                foreach ($groups as &$group) {
                    if (count($group['attributes'])) {
                        $have_selected = false;
                        foreach ($group['attributes'] as $key => &$attribute) {
                            if (in_array($key, $this->params)) {
                                $attribute['selected'] = true;
                                $have_selected = true;
                            } else {
                                $attribute['selected'] = false;
                            }
                        }

                        if ($have_selected === false) {
                            $have_index = 0;
                            foreach ($group['attributes'] as $key => &$attribute) {
                                if ($have_index == 0) {
                                    $attribute['selected'] = true;
                                    $have_index++;
                                    break;
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($groups as &$group) {
                    if (count($group['attributes'])) {
                        foreach ($group['attributes'] as $key => &$attribute) {
                            $attribute['selected'] = ($key == $group['default']) ? true : false;
                        }
                    }
                }
            }

            // wash attributes list depending on available attributes depending on selected preceding attributes
            $current_selected_attributes = array();
            $count = 0;
            foreach ($groups as &$group) {
                $count++;
                if ($count > 1) {
                    //find attributes of current group, having a possible combination with current selected
                    $id_product_attributes = array(0);
                    $query = 'SELECT pac.`id_product_attribute`
                        FROM `'._DB_PREFIX_.'product_attribute_combination` pac
                        INNER JOIN `'._DB_PREFIX_.'product_attribute` pa ON pa.id_product_attribute = pac.id_product_attribute
                        WHERE id_product = '.$this->product->id.' AND id_attribute IN ('.implode(',', array_map('intval', $current_selected_attributes)).')
                        GROUP BY id_product_attribute
                        HAVING COUNT(id_product) = '.count($current_selected_attributes);
                    if ($results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query)) {
                        foreach ($results as $row) {
                            if (!in_array($row['id_product_attribute'], $id_product_attributes)) {
                                $id_product_attributes[] = $row['id_product_attribute'];
                            }
                        }
                    }
                    $id_attributes = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination` pac2
                        WHERE `id_product_attribute` IN ('.implode(',', array_map('intval', $id_product_attributes)).')
                        AND id_attribute NOT IN ('.implode(',', array_map('intval', $current_selected_attributes)).')');
                    $id_attributes_tmp = array();
                    foreach ($id_attributes as $row) {
                        if (!in_array($row['id_attribute'], $id_attributes_tmp)) {
                            $id_attributes_tmp[] = (int)$row['id_attribute'];
                        }
                    }
                    $id_attributes = $id_attributes_tmp;
                    foreach ($group['attributes'] as $key => $attribute) {
                        if (!in_array((int)$key, $id_attributes)) {
                            unset($group['attributes'][$key]);
                            unset($group['attributes_quantity'][$key]);
                        }
                    }
                }
                //find selected attribute or first of group
                $index = 0;
                $current_selected_attribute = 0;
                foreach ($group['attributes'] as $key => $attribute) {
                    if ($index === 0) {
                        $current_selected_attribute = $key;
                    }
                    if ($attribute['selected']) {
                        $current_selected_attribute = $key;
                        break;
                    }
                }
                if ($current_selected_attribute > 0 && !in_array($current_selected_attribute, $current_selected_attributes)) {
                    $current_selected_attributes[] = $current_selected_attribute;
                }
            }

            // re-update value groups -- @TODO
            if (count($this->groups)) {
                foreach ($groups as $key => &$group) {
                    if (count($group['attributes'])) {
                        foreach ($group['attributes'] as $k => &$attribute) {
                            $attribute['name'] = $this->groups[$key]['attributes'][$k]['name'];
                            $attribute['html_color_code'] = $this->groups[$key]['attributes'][$k]['html_color_code'];
                            $attribute['texture'] = $this->groups[$key]['attributes'][$k]['texture'];
                            $attribute['selected'] = $this->groups[$key]['attributes'][$k]['selected'];
                        }
                    }
                }
            }

            // wash attributes list (if some attributes are unavailables and if allowed to wash it)
            if (!Product::isAvailableWhenOutOfStock($this->product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
                foreach ($groups as &$group) {
                    foreach ($group['attributes_quantity'] as $key => &$quantity) {
                        if ($quantity < 1) {
                            unset($group['attributes'][$key]);
                        }
                    }
                }

                foreach ($colors as $key => $color) {
                    if ($color['attributes_quantity'] < 1) {
                        unset($colors[$key]);
                    }
                }
            }
        }

        if (count($groups)) {
            foreach ($groups as $key => &$group) {
                if (isset($group['attributes']) && count($group['attributes'])) {
                    $have_selected = false;
                    foreach ($group['attributes'] as $k => $group_attribute) {
                        if ($group_attribute['selected'] == true) {
                            $have_selected = true;
                            break;
                        }
                    }
                    if ($have_selected == false) {
                        $index = key($group['attributes']);
                        if ($index != null && $index > 0) {
                            $group['default'] = $index;
                            $group['attributes'][$index]['selected'] = true;
                        }
                    }
                }
            }
        }

        return $groups;
    }

    protected function getAttributesGroupsFull()
    {
        $groups = array();

        // @todo (RM) should only get groups and not all declination ?
        $attributes_groups = $this->product->getAttributesGroups($this->context->language->id);
        if (is_array($attributes_groups) && $attributes_groups) {
            foreach ($attributes_groups as $k => $row) {
                if (!isset($groups[$row['id_attribute_group']])) {
                    $groups[$row['id_attribute_group']] = array(
                        'group_name' => $row['group_name'],
                        'name' => $row['public_group_name'],
                        'group_type' => $row['group_type'],
                        'default' => -1,
                    );
                }

                $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = array(
                    'name' => $row['attribute_name'],
                    'html_color_code' => $row['attribute_color'],
                    'texture' => (@filemtime(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg')) ? _THEME_COL_DIR_.$row['id_attribute'].'.jpg' : '',
                    'selected' => false,
                );

                //$product.attributes.$id_attribute_group.id_attribute eq $id_attribute
                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$row['id_attribute_group']]['default'] = (int) $row['id_attribute'];
                }
                if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                    $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
                }
                $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int) $row['quantity'];
            }

            if (count($this->params)) {
                foreach ($groups as &$group) {
                    if (count($group['attributes'])) {
                        $have_selected = false;
                        foreach ($group['attributes'] as $key => &$attribute) {
                            if (in_array($key, $this->params)) {
                                $attribute['selected'] = true;
                                $have_selected = true;
                            } else {
                                $attribute['selected'] = false;
                            }
                        }

                        if ($have_selected === false) {
                            $have_index = 0;
                            foreach ($group['attributes'] as $key => &$attribute) {
                                if ($have_index == 0) {
                                    $attribute['selected'] = true;
                                    $have_index++;
                                    break;
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($groups as &$group) {
                    if (count($group['attributes'])) {
                        foreach ($group['attributes'] as $key => &$attribute) {
                            $attribute['selected'] = ($key == $group['default']) ? true : false;
                        }
                    }
                }
            }
        }

        return $groups;
    }
}
