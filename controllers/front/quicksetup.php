<?php
/**
 * Fixed Quicksetup Controller - Works without designer app
 * Copy to: controllers/front/quicksetup.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class TshirtecommerceQuicksetupModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;

    public function initContent()
    {
        parent::initContent();

        // Check if user is admin
        $is_admin = false;
        if ($this->context->employee && $this->context->employee->id) {
            $employee = new Employee($this->context->employee->id);
            if (Validate::isLoadedObject($employee) && $employee->active) {
                $is_admin = true;
            }
        }

        $site_url = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
        $site_url .= __PS_BASE_URI__;

        $designer_app_exists = file_exists(_PS_ROOT_DIR_.'/tshirtecommerce/index.php');

        if (!$is_admin) {
            // Not admin - redirect immediately to designer admin
            $redirect_url = $site_url.'tshirtecommerce/admin/';
            header('Location: ' . $redirect_url);
            exit;
        } elseif (!$designer_app_exists) {
            // Admin but designer app missing
            $this->context->smarty->assign(array(
                'error' => 'Designer application not installed',
                'designer_app_exists' => false,
                'admin_link' => $this->context->link->getAdminLink('AdminModules').'&configure=tshirtecommerce',
            ));
        } else {
            // Everything OK - load quicksetup
            $step = (int)Tools::getValue('step', 0);

            $this->context->smarty->assign(array(
                'is_admin' => true,
                'designer_app_exists' => true,
                'site_url' => $site_url,
                'step' => $step,
                'quicksetup_url' => $site_url.'tshirtecommerce/admin/index.php?/quicksetup',
            ));
        }

        $this->setTemplate('module:tshirtecommerce/views/templates/front/quicksetup.tpl');
    }
}
