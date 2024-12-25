<?php

class TshirtecommerceCustomizationsPresta
{
    function __construct($module)
    {
        $this->module = $module;
        $this->translates = $this->module->translateTexts();
        $this->context = Context::getContext();
    }

    public function save($product, $data = array())
    {
        $json = array(
            'error' => 0,
            'message' => $this->translates['customization_save_success'],
            'html' => ''
        );

        $files = array();
        $post = array();

        if (count($data)) {
            $files = isset($data['customization_file']) ? $data['customization_file'] : array();
            $post = isset($data['customization_text']) ? $data['customization_text'] : array();
        }

        if (count($files)) {
            $res = $this->pictureUpload($product, $files);
            if ($res['error'] == 1) {
                $json['message'] = 1;
                $json['message'] = $res['message'];

                echo json_encode($json);
                return;
            }
        }
        if (count($post)) {
            $res = $this->textRecord($product, $post);
            if ($json['error'] == 1) {
                $json['error'] = 1;
                $json['message'] = $res['message'];

                echo json_encode($json);
                return;
            }
        }
        if ($json['error'] == 0) {
            $json['html'] = $this->getCustomizations($product, true);
        }

        echo json_encode($json);
        return;
    }

    // $index = id_customization_field
    public function remove($product, $index)
    {
        $json = array('error' => 0, 'message' => $this->translates['customization_remove_success']);

        $already_customized = $this->context->cart->getProductCustomization($product->id, null, true);
        if (count($already_customized)) {
            foreach ($already_customized as $customization) {
                $index_customization = $customization['index'];
                if ($index_customization == $index) {
                    if (!$this->context->cart->deleteCustomizationToProduct($product->id, $index)) {
                        $json['error'] = 1;
                        $json['message'] = $this->translates['customization_remove_error'];
                    }

                    break;
                }
            }
        }

        echo json_encode($json);
        return;
    }

    public function getCustomizations($product, $ajax = false)
    {
        $html = '';
        $htmls = array(
            'text' => array(),
            'file' => array()
        );

        if (!isset($product->id_product)) {
            $product->id_product = (int) $product->id;
        }
        $product_full = json_decode(json_encode($product), true);

        $customized_data = array();
        $already_customized = $this->context->cart->getProductCustomization(
            $product_full['id_product'],
            null,
            true
        );

        $id_customization = 0;
        if (count($already_customized)) {
            foreach ($already_customized as $customization) {
                $id_customization = $customization['id_customization'];
                $customized_data[$customization['index']] = $customization;
            }
        }

        $customizationData = array(
            'fields' => array(),
        );

        $customization_fields = $product->getCustomizationFields($this->context->language->id);
        if (is_array($customization_fields)) {
            foreach ($customization_fields as $customization_field) {
                // 'id_customization_field' maps to what is called 'index'
                // in what Product::getProductCustomization() returns
                $key = $customization_field['id_customization_field'];

                $field['label'] = $customization_field['name'];
                $field['id_customization_field'] = $customization_field['id_customization_field'];
                $field['required'] = $customization_field['required'];

                switch ($customization_field['type']) {
                    case Product::CUSTOMIZE_FILE:
                        $field['type'] = 'image';
                        $field['image'] = null;
                        $field['input_name'] = 'file' . $customization_field['id_customization_field'];
                        break;
                    case Product::CUSTOMIZE_TEXTFIELD:
                        $field['type'] = 'text';
                        $field['text'] = '';
                        $field['input_name'] = 'textField' . $customization_field['id_customization_field'];
                        break;
                    default:
                        $field['type'] = null;
                }

                if (array_key_exists($key, $customized_data)) {
                    $data = $customized_data[$key];
                    $field['is_customized'] = true;
                    switch ($customization_field['type']) {
                        case Product::CUSTOMIZE_FILE:
                            $field['image'] = $this->getCustomizationImage(
                                $data['value']
                            );
                            $field['remove_image_url'] = $this->context->link->getProductDeletePictureLink(
                                $product_full,
                                $customization_field['id_customization_field']
                            );
                            break;
                        case Product::CUSTOMIZE_TEXTFIELD:
                            $field['text'] = $data['value'];
                            break;
                    }
                } else {
                    $field['is_customized'] = false;
                }

                $customizationData['fields'][] = $field;
            }
        }

        if (count($customizationData['fields'])) {
            $html .= '<script>var ps_customization_require_msg="'.$this->translates['customization_require_text'].'"</script>';
            $html .= '<div class="design-ps-customizations">';

            $html .= '<h5>'.$this->translates['customization_text_title'].'</h5>';
            $html .= '<p>'.$this->translates['customization_help_text'].'</p>';
            $html .= '<form id="ps-customizations" enctype="multipart/form-data">';
            $html .= '<ul class="clearfix">';

            foreach ($customizationData['fields'] as $field) {
                if ($ajax === false) {
                    $html .= '<li class="form-group product-customization-item">';
                    $html .= '<label> '.$field['label'].($field['required'] ? ' <small style="color:#ff0000">(*)</small>' : '').'</label>';
                }
                if ($field['type'] == 'text') {
                    $html .= '<textarea placeholder="'.$this->translates['customization_place_holder'].'" class="form-control product-message" maxlength="250" '.($field['required'] ? 'required' : '').' name="customization_text['.$field['input_name'].']">'.$field['text'].'</textarea>';
                    $html .= '<small class="pull-right">'.$this->translates['customization_help_max'].'</small>';
                    $html .= '<div class="ps-text-customization-'.$field['id_customization_field'].'">';

                    if ($field['text'] !== '') {
                        $html .= '<h6 class="customization-message">'.$this->translates['customization_your_custom'];
                        $html .=    '<label>'.$field['text'].'</label>'.'<a class="rm-text-ps" href="javascript:void(0)" onclick="prestashop.customizaton.remove(this,'.$field['id_customization_field'].')">'.$this->translates['customization_remove_text'].'</a>';
                        $html .= '</h6>';
                    }

                    $html .= '</div>';

                    if ($ajax === true) {
                        if (!isset($htmls['text'][$field['id_customization_field']])) {
                            $htmls['text'][$field['id_customization_field']] = '';
                        }
                        $htmls['text'][$field['id_customization_field']] .= '<h6 class="customization-message">'.$this->translates['customization_your_custom'];
                        $htmls['text'][$field['id_customization_field']] .=    '<label>'.$field['text'].'</label>'.'<a class="rm-text-ps" href="javascript:void(0)" onclick="prestashop.customizaton.remove(this,'.$field['id_customization_field'].')">'.$this->translates['customization_remove_text'].'</a>';
                        $htmls['text'][$field['id_customization_field']] .= '</h6>';
                    }
                } elseif ($field['type'] == 'image') {
                    $html .= '<div class="ps-file-customization">';
                    if ($field['is_customized']) {
                        $html .= '<div class="form-group">';
                        $html .= '<div class="ps-customization-uploaded-'.$field['id_customization_field'].'">';
                        $html .= '<img src="'.$field['image']['small']['url'].'">';
                        $html .= '<a href="javascript:void(0)" onclick="prestashop.customizaton.remove(this,'.$field['id_customization_field'].')">'.$this->translates['customization_remove_image'].'</a>';
                        $html .= '</div>';
                        $html .= '</div>';

                        if ($ajax === true) {
                            if (!isset($htmls['file'][$field['id_customization_field']])) {
                                $htmls['file'][$field['id_customization_field']] = '';
                            }
                            $htmls['file'][$field['id_customization_field']] .= '<div class="form-group">';
                            $htmls['file'][$field['id_customization_field']] .= '<div class="ps-customization-uploaded">';
                            $htmls['file'][$field['id_customization_field']] .= '<img src="'.$field['image']['small']['url'].'">';
                            $htmls['file'][$field['id_customization_field']] .= '<a href="javascript:void(0)" onclick="prestashop.customizaton.remove(this,'.$field['id_customization_field'].')">'.$this->translates['customization_remove_image'].'</a>';
                            $htmls['file'][$field['id_customization_field']] .= '</div></div>';
                        }
                    }
                    $html .= '</div>';
                    $html .= '<span class="custom-file">';
                        //$html .= '<span class="js-file-name">No selected file</span>';
                        $html .= '<input '.($field['required'] ? 'required' : '').' type="file" name="customization_file['.$field['input_name'].']">';
                        //$html .= '<button class="btn btn-sm btn-default">Choose file</button>';
                    $html .= '</span>';
                    $html .= '<small>.png .jpg .gif</small>';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
            $html .= '<div class="form-group clearfix">';
            $html .= '<button class="btn btn-sm btn-default" type="buttom" onclick="prestashop.customizaton.save()">'.$this->translates['customization_save_button'].'</button>';
            $html .= '</div>';
            $html .= '</form>';

            $html .= '</div>';
        }

        if ($ajax === false) {
            return $html;
        } else {
            return $htmls;
        }
    }

    protected function getCustomizationImage($imageHash)
    {
        $large_image_url = rtrim($this->context->link->getBaseLink(), '/') . '/upload/' . $imageHash;
        $small_image_url = $large_image_url . '_small';

        $small = [
            'url' => $small_image_url,
        ];

        $large = [
            'url' => $large_image_url,
        ];

        $medium = $large;

        return [
            'bySize' => [
                'small' => $small,
                'medium' => $medium,
                'large' => $large,
            ],
            'small' => $small,
            'medium' => $medium,
            'large' => $large,
            'legend' => '',
        ];
    }

    protected function pictureUpload($product, $files)
    {
        $json = array('error' => 0, 'message' => $this->translates['customization_upload_success']);

        if (!$field_ids = $product->getCustomizationFieldIds()) {
            return $json;
        }

        $authorized_file_fields = array();
        foreach ($field_ids as $field_id) {
            if ($field_id['type'] == Product::CUSTOMIZE_FILE) {
                $authorized_file_fields[(int) $field_id['id_customization_field']] = 'file' . (int) $field_id['id_customization_field'];
            }
        }
        $indexes = array_flip($authorized_file_fields);

        foreach ($files as $field_name => $file) {
            if (in_array($field_name, $authorized_file_fields) && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
                $file_name = md5(uniqid(rand(), true));
                if ($error = ImageManager::validateUpload($file, (int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'))) {
                    $json['message'] = $error;
                }

                $product_picture_width = (int) Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
                $product_picture_height = (int) Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
                $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                if ($error || (!$tmp_name || !move_uploaded_file($file['tmp_name'], $tmp_name))) {
                    $json['error'] = 1;
                    return $json;
                }
                // Original file
                if (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name)) {
                    $json['error'] = 1;
                    $json['message'] = $this->translates['customization_upload_error'];
                } elseif (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name . '_small', $product_picture_width, $product_picture_height)) {
                    $json['error'] = 1;
                    $json['message'] = $this->translates['customization_upload_error'];
                } elseif (!chmod(_PS_UPLOAD_DIR_ . $file_name, 0777) || !chmod(_PS_UPLOAD_DIR_ . $file_name . '_small', 0777)) {
                    $json['error'] = 1;
                    $json['message'] = $this->translates['customization_upload_error'];
                } else {
                    $this->context->cart->addPictureToProduct($product->id, $indexes[$field_name], Product::CUSTOMIZE_FILE, $file_name);
                }
                unlink($tmp_name);
            }
        }

        return $json;
    }

    protected function textRecord($product, $post)
    {
        $json = array('error' => 0, 'message' => '');

        if (!$field_ids = $product->getCustomizationFieldIds()) {
            $json['error'] = 1;
            return $json;
        }

        $authorized_text_fields = array();
        foreach ($field_ids as $field_id) {
            if ($field_id['type'] == Product::CUSTOMIZE_TEXTFIELD) {
                $authorized_text_fields[(int) $field_id['id_customization_field']] = 'textField' . (int) $field_id['id_customization_field'];
            }
        }

        $indexes = array_flip($authorized_text_fields);
        foreach ($post as $field_name => $value) {
            if (in_array($field_name, $authorized_text_fields) && $value != '') {
                if (!Validate::isMessage($value)) {
                    $json['error'] = 1;
                    $json['message'] = $this->translates['customization_invalid_message'];
                } else {
                    $this->context->cart->addTextFieldToProduct($product->id, $indexes[$field_name], Product::CUSTOMIZE_TEXTFIELD, $value);
                }
            } elseif (in_array($field_name, $authorized_text_fields) && $value == '') {
                $this->context->cart->deleteCustomizationToProduct((int) $product->id, $indexes[$field_name]);
            }
        }

        return $json;
    }
}
