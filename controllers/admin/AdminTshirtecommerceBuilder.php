<?php

declare(strict_types=1);

namespace PrestaShop\Module\Tshirtecommerce\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Context;

class AdminTshirtecommerceBuilderController extends FrameworkBundleAdminController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     */
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->module = 'tshirtecommerce';
    }

    public function initPageHeaderToolbar(): void
    {
        $this->page_header_toolbar_title = $this->trans('T-Shirt eCommerce', [], 'Modules.Tshirtecommerce.Admin'); // Set header title
        parent::initPageHeaderToolbar();
        if ($this->meta_title != null) {
            array_pop($this->meta_title); // Remove the last element on this controller to match the title with the rule of the others
        }
    }

    public function initContent(): void
    {
        $this->renderView();
        parent::initContent();
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        // Get session from Symfony
        $session = $request->getSession();
        $employee = $this->getContext()->employee;
        
        if ($employee->id) {
            $session->set('is_admin', [
                'login' => 1,
                'email' => $employee->email,
                'id' => $employee->id
            ]);
            $session->set('admin', $session->get('is_admin'));
        }

        $errorWarning = '';
        if (!ini_get('allow_url_fopen')) {
            $errorWarning = $this->trans(
                'Your server does not support <strong>allow_url_fopen</strong>. Please configure your hosting with <strong>allow_url_fopen = ON</strong>.',
                'Modules.Tshirtecommerce.Admin'
            );
        }

        $router = $this->get('router');
        $context = $this->getContext();
        
        $url = $context->link->getBaseLink(true);
        $task = $request->query->get('task');
        
        if ($task) {
            $url .= 'tshirtecommerce/admin/index.php?/' . $task . '&session_id=' . $session->getId();
        } else {
            $url .= 'tshirtecommerce/admin/index.php?session_id=' . $session->getId();
        }

        return $this->render('@Modules/tshirtecommerce/views/templates/admin/tshirtecommerce_builder.html.twig', [
            'errorWarning' => $errorWarning,
            'url' => $url,
            'data' => $context
        ]);
    }

    protected function allowSession()
    {
        if (version_compare(phpversion(), '5.4.0', '<')) {
            if(session_id() == '') 
                session_start();
        } else {
            if (session_status() == PHP_SESSION_NONE) 
                session_start();
        }
    } 
}

// Backward compatibility: expose namespaced controller under global class name for legacy loader
if (!class_exists('AdminTshirtecommerceBuilderController')) {
    class_alias('\Tshirtecommerce\\Controllers\\Admin\\AdminTshirtecommerceBuilderController', 'AdminTshirtecommerceBuilderController');
}