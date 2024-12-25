<?php

namespace PrestaShop\Module\Tshirtecommerce\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Context;

class AdminTshirtecommerceBuilderController extends FrameworkBundleAdminController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $context = Context::getContext();
        $session = $request->getSession();
        
        if ($context->employee && $context->employee->id) {
            $session->set('is_admin', [
                'login' => 1,
                'email' => $context->employee->email,
                'id' => $context->employee->id
            ]);
            $session->set('admin', $session->get('is_admin'));
        }

        $errorWarning = '';
        if (!ini_get('allow_url_fopen')) {
            $errorWarning = $this->trans(
                'Your server does not support <strong>allow_url_fopen</strong>. Please configure your hosting with <strong>allow_url_fopen = ON</strong>.',
                [],
                'Modules.Tshirtecommerce.Admin'
            );
        }

        $moduleUrl = $context->link->getBaseLink() . 'modules/tshirtecommerce/';
        $designerUrl = $moduleUrl . 'admin/index.php';
        
        $task = $request->query->get('task');
        if ($task) {
            $designerUrl .= '?' . http_build_query([
                'task' => $task,
                'session_id' => $session->getId()
            ]);
        } else {
            $designerUrl .= '?session_id=' . $session->getId();
        }

        return $this->render('@Modules/tshirtecommerce/views/templates/admin/tshirtecommerce_builder.html.twig', [
            'errorWarning' => $errorWarning,
            'designerUrl' => $designerUrl,
            'moduleUrl' => $moduleUrl,
            'enableSidebar' => true,
            'help_link' => false,
            'layoutTitle' => $this->trans('T-Shirt Designer', [], 'Modules.Tshirtecommerce.Admin'),
        ]);
    }
}