<?php 

/*
* Customization management
*/
class TCustomizations
{
	public $id;

	public function getAllCustomizedDatas($id_cart, $id_lang = null, $only_in_cart = true, $id_shop = null)
    {
        if (!Customization::isFeatureActive()) {
            return false;
        }

        // No need to query if there isn't any real cart!
        if (!$id_cart) {
            return false;
        }
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        if (Shop::isFeatureActive() && !$id_shop) {
            $id_shop = (int)Context::getContext()->shop->id;
        }


        if (!$result = Db::getInstance()->executeS('
			SELECT cd.`id_customization`, c.`id_address_delivery`, c.`id_product`, cfl.`id_customization_field`, c.`id_product_attribute`,
				cd.`type`, cd.`index`, cd.`value`, cfl.`name`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customized_data` cd
			NATURAL JOIN `'._DB_PREFIX_.'tshirtecommerce_customization` c
			LEFT JOIN `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang` cfl ON (cfl.id_customization_field = cd.`index` AND id_lang = '.(int)$id_lang.
                ($id_shop ? ' AND cfl.`id_shop` = '.$id_shop : '').')
			WHERE c.`id_cart` = '.(int)$id_cart.
            ($only_in_cart ? ' AND c.`in_cart` = 1' : '').'
			ORDER BY `id_product`, `id_product_attribute`, `type`, `index`')) {
            return false;
        }

        $customized_datas = array();

        foreach ($result as $row) {
            $customized_datas[(int)$row['id_product']][(int)$row['id_product_attribute']][(int)$row['id_address_delivery']][(int)$row['id_customization']]['datas'][(int)$row['type']][] = $row;
        }

        if (!$result = Db::getInstance()->executeS(
            'SELECT `id_product`, `id_product_attribute`, `id_customization`, `id_address_delivery`, `quantity`, `quantity_refunded`, `quantity_returned`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customization`
			WHERE `id_cart` = '.(int)$id_cart.($only_in_cart ? '
			AND `in_cart` = 1' : ''))) {
            return false;
        }

        foreach ($result as $row) {
            $customized_datas[(int)$row['id_product']][(int)$row['id_product_attribute']][(int)$row['id_address_delivery']][(int)$row['id_customization']]['quantity'] = (int)$row['quantity'];
            $customized_datas[(int)$row['id_product']][(int)$row['id_product_attribute']][(int)$row['id_address_delivery']][(int)$row['id_customization']]['quantity_refunded'] = (int)$row['quantity_refunded'];
            $customized_datas[(int)$row['id_product']][(int)$row['id_product_attribute']][(int)$row['id_address_delivery']][(int)$row['id_customization']]['quantity_returned'] = (int)$row['quantity_returned'];
        }

        return $customized_datas;
    }

    public function addCustomizationPrice(&$products, &$customized_datas)
    {
        if (!$customized_datas) {
            return;
        }

        foreach ($products as &$product_update) {
            if (!Customization::isFeatureActive()) {
                $product_update['customizationQuantityTotal'] = 0;
                $product_update['customizationQuantityRefunded'] = 0;
                $product_update['customizationQuantityReturned'] = 0;
            } else {
                $customization_quantity = 0;
                $customization_quantity_refunded = 0;
                $customization_quantity_returned = 0;

                /* Compatibility */
                $product_id = isset($product_update['id_product']) ? (int)$product_update['id_product'] : (int)$product_update['product_id'];
                $product_attribute_id = isset($product_update['id_product_attribute']) ? (int)$product_update['id_product_attribute'] : (int)$product_update['product_attribute_id'];
                $id_address_delivery = (int)$product_update['id_address_delivery'];
                $product_quantity = isset($product_update['cart_quantity']) ? (int)$product_update['cart_quantity'] : (int)$product_update['product_quantity'];
                $price = isset($product_update['price']) ? $product_update['price'] : $product_update['product_price'];
                if (isset($product_update['price_wt']) && $product_update['price_wt']) {
                    $price_wt = $product_update['price_wt'];
                } else {
                    $price_wt = $price * (1 + ((isset($product_update['tax_rate']) ? $product_update['tax_rate'] : $product_update['rate']) * 0.01));
                }

                if (!isset($customized_datas[$product_id][$product_attribute_id][$id_address_delivery])) {
                    $id_address_delivery = 0;
                }
                if (isset($customized_datas[$product_id][$product_attribute_id][$id_address_delivery])) {
                    foreach ($customized_datas[$product_id][$product_attribute_id][$id_address_delivery] as $customization) {
                        $customization_quantity += (int)$customization['quantity'];
                        $customization_quantity_refunded += (int)$customization['quantity_refunded'];
                        $customization_quantity_returned += (int)$customization['quantity_returned'];
                    }
                }

                $product_update['customizationQuantityTotal'] = $customization_quantity;
                $product_update['customizationQuantityRefunded'] = $customization_quantity_refunded;
                $product_update['customizationQuantityReturned'] = $customization_quantity_returned;

                if ($customization_quantity) {
                    $product_update['total_wt'] = $price_wt * ($product_quantity - $customization_quantity);
                    $product_update['total_customization_wt'] = $price_wt * $customization_quantity;
                    $product_update['total'] = $price * ($product_quantity - $customization_quantity);
                    $product_update['total_customization'] = $price * $customization_quantity;
                }
            }
        }
    }

    /*
    ** Customization fields' label management
    */

    protected function _checkLabelField($field, $value)
    {
        if (!Validate::isLabel($value)) {
            return false;
        }
        $tmp = explode('_', $field);
        if (count($tmp) < 4) {
            return false;
        }
        return $tmp;
    }

    protected function _deleteOldLabels()
    {
        $max = array(
            Product::CUSTOMIZE_FILE => (int)$this->uploadable_files,
            Product::CUSTOMIZE_TEXTFIELD => (int)$this->text_fields
        );

        /* Get customization field ids */
        if (($result = Db::getInstance()->executeS(
            'SELECT `id_customization_field`, `type`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customization_field`
			WHERE `id_product` = '.(int)$this->id.'
			ORDER BY `id_customization_field`')
        ) === false) {
            return false;
        }

        if (empty($result)) {
            return true;
        }

        $customization_fields = array(
            Product::CUSTOMIZE_FILE => array(),
            Product::CUSTOMIZE_TEXTFIELD => array()
        );

        foreach ($result as $row) {
            $customization_fields[(int)$row['type']][] = (int)$row['id_customization_field'];
        }

        $extra_file = count($customization_fields[Product::CUSTOMIZE_FILE]) - $max[Product::CUSTOMIZE_FILE];
        $extra_text = count($customization_fields[Product::CUSTOMIZE_TEXTFIELD]) - $max[Product::CUSTOMIZE_TEXTFIELD];

        /* If too much inside the database, deletion */
        if ($extra_file > 0 && count($customization_fields[Product::CUSTOMIZE_FILE]) - $extra_file >= 0 &&
        (!Db::getInstance()->execute(
            'DELETE `'._DB_PREFIX_.'tshirtecommerce_customization_field`,`'._DB_PREFIX_.'tshirtecommerce_customization_field_lang`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customization_field` JOIN `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang`
			WHERE `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`id_product` = '.(int)$this->id.'
			AND `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`type` = '.Product::CUSTOMIZE_FILE.'
			AND `'._DB_PREFIX_.'tshirtecommerce_tshirtecommerce_customization_field_lang`.`id_customization_field` = `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`id_customization_field`
			AND `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`id_customization_field` >= '.(int)$customization_fields[Product::CUSTOMIZE_FILE][count($customization_fields[Product::CUSTOMIZE_FILE]) - $extra_file]
        ))) {
            return false;
        }

        if ($extra_text > 0 && count($customization_fields[Product::CUSTOMIZE_TEXTFIELD]) - $extra_text >= 0 &&
        (!Db::getInstance()->execute(
            'DELETE `'._DB_PREFIX_.'tshirtecommerce_customization_field`,`'._DB_PREFIX_.'tshirtecommerce_customization_field_lang`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customization_field` JOIN `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang`
			WHERE `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`id_product` = '.(int)$this->id.'
			AND `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`type` = '.Product::CUSTOMIZE_TEXTFIELD.'
			AND `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang`.`id_customization_field` = `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`id_customization_field`
			AND `'._DB_PREFIX_.'tshirtecommerce_customization_field`.`id_customization_field` >= '.(int)$customization_fields[Product::CUSTOMIZE_TEXTFIELD][count($customization_fields[Product::CUSTOMIZE_TEXTFIELD]) - $extra_text]
        ))) {
            return false;
        }

        // Refresh cache of feature detachable
        Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', Customization::isCurrentlyUsed());

        return true;
    }

    protected function _createLabel($languages, $type)
    {
        // Label insertion
        if (!Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'tshirtecommerce_customization_field` (`id_product`, `type`, `required`)
			VALUES ('.(int)$this->id.', '.(int)$type.', 0)') ||
            !$id_customization_field = (int)Db::getInstance()->Insert_ID()) {
            return false;
        }

        // Multilingual label name creation
        $values = '';

        foreach ($languages as $language) {
            foreach (Shop::getContextListShopID() as $id_shop) {
                $values .= '('.(int)$id_customization_field.', '.(int)$language['id_lang'].', '.$id_shop .',\'\'), ';
            }
        }

        $values = rtrim($values, ', ');
        if (!Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang` (`id_customization_field`, `id_lang`, `id_shop`, `name`)
			VALUES '.$values)) {
            return false;
        }

        // Set cache of feature detachable to true
        Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', '1');

        return true;
    }

    public function createLabels($uploadable_files, $text_fields)
    {
        $languages = Language::getLanguages();
        if ((int)$uploadable_files > 0) {
            for ($i = 0; $i < (int)$uploadable_files; $i++) {
                if (!$this->_createLabel($languages, Product::CUSTOMIZE_FILE)) {
                    return false;
                }
            }
        }

        if ((int)$text_fields > 0) {
            for ($i = 0; $i < (int)$text_fields; $i++) {
                if (!$this->_createLabel($languages, Product::CUSTOMIZE_TEXTFIELD)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function updateLabels()
    {
        $has_required_fields = 0;
        foreach ($_POST as $field => $value) {
            /* Label update */
            if (strncmp($field, 'label_', 6) == 0) {
                if (!$tmp = $this->_checkLabelField($field, $value)) {
                    return false;
                }
                /* Multilingual label name update */
                if (Shop::isFeatureActive()) {
                    foreach (Shop::getContextListShopID() as $id_shop) {
                        if (!Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang`
						(`id_customization_field`, `id_lang`, `id_shop`, `name`) VALUES ('.(int)$tmp[2].', '.(int)$tmp[3].', '.$id_shop.', \''.pSQL($value).'\')
						ON DUPLICATE KEY UPDATE `name` = \''.pSQL($value).'\'')) {
                            return false;
                        }
                    }
                } elseif (!Db::getInstance()->execute('
					INSERT INTO `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang`
					(`id_customization_field`, `id_lang`, `name`) VALUES ('.(int)$tmp[2].', '.(int)$tmp[3].', \''.pSQL($value).'\')
					ON DUPLICATE KEY UPDATE `name` = \''.pSQL($value).'\'')) {
                    return false;
                }

                $is_required = isset($_POST['require_'.(int)$tmp[1].'_'.(int)$tmp[2]]) ? 1 : 0;
                $has_required_fields |= $is_required;
                /* Require option update */
                if (!Db::getInstance()->execute(
                    'UPDATE `'._DB_PREFIX_.'tshirtecommerce_customization_field`
					SET `required` = '.(int)$is_required.'
					WHERE `id_customization_field` = '.(int)$tmp[2])) {
                    return false;
                }
            }
        }

        if ($has_required_fields && !ObjectModel::updateMultishopTable('product', array('customizable' => 2), 'a.id_product = '.(int)$this->id)) {
            return false;
        }

        if (!$this->_deleteOldLabels()) {
            return false;
        }

        return true;
    }

    public function getCustomizationFields($id_lang = false, $id_shop = null)
    {
        if (!Customization::isFeatureActive()) {
            return false;
        }

        if (Shop::isFeatureActive() && !$id_shop) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        if (!$result = Db::getInstance()->executeS('
			SELECT cf.`id_customization_field`, cf.`type`, cf.`required`, cfl.`name`, cfl.`id_lang`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customization_field` cf
			NATURAL JOIN `'._DB_PREFIX_.'tshirtecommerce_customization_field_lang` cfl
			WHERE cf.`id_product` = '.(int)$this->id.($id_lang ? ' AND cfl.`id_lang` = '.(int)$id_lang : '').
                ($id_shop ? ' AND cfl.`id_shop` = '.$id_shop : '').'
			ORDER BY cf.`id_customization_field`')) {
            return false;
        }

        if ($id_lang) {
            return $result;
        }

        $customization_fields = array();
        foreach ($result as $row) {
            $customization_fields[(int)$row['type']][(int)$row['id_customization_field']][(int)$row['id_lang']] = $row;
        }

        return $customization_fields;
    }

    public function getCustomizationFieldIds()
    {
        if (!Customization::isFeatureActive()) {
            return array();
        }
        return Db::getInstance()->executeS('
			SELECT `id_customization_field`, `type`, `required`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customization_field`
			WHERE `id_product` = '.(int)$this->id);
    }

    public function getRequiredCustomizableFields()
    {
        if (!Customization::isFeatureActive()) {
            return array();
        }
        return $this->getRequiredCustomizableFieldsStatic($this->id);
    }

    public function getRequiredCustomizableFieldsStatic($id)
    {
        if (!$id || !Customization::isFeatureActive()) {
            return array();
        }
        return Db::getInstance()->executeS('
			SELECT `id_customization_field`, `type`
			FROM `'._DB_PREFIX_.'tshirtecommerce_customization_field`
			WHERE `id_product` = '.(int)$id.'
			AND `required` = 1'
        );
    }

    public function hasAllRequiredCustomizableFields(Context $context = null)
    {
        if (!Customization::isFeatureActive()) {
            return true;
        }
        if (!$context) {
            $context = Context::getContext();
        }

        $fields = $context->cart->getProductCustomization($this->id, null, true);
        if (($required_fields = $this->getRequiredCustomizableFields()) === false) {
            return false;
        }

        $fields_present = array();
        foreach ($fields as $field) {
            $fields_present[] = array('id_customization_field' => $field['index'], 'type' => $field['type']);
        }

        if (is_array($required_fields) && count($required_fields)) {
            foreach ($required_fields as $required_field) {
                if (!in_array($required_field, $fields_present)) {
                    return false;
                }
            }
        }
        return true;
    }
}

?>