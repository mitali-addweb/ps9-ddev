<?php

namespace PrestaShop\Module\Tshirtecommerce\Controller\Front;

use PrestaShop\PrestaShop\Core\Domain\Product\Query\GetProductForViewing;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShopBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\Product\ProductDataProvider;
use PrestaShop\PrestaShop\Core\Product\ProductPresenterFactory;

class DesignerController extends AbstractController
{
    private $module;
    private $context;
    private $configuration;
    private $productDataProvider;
    private $priceFormatter;

    public function __construct(
        \Tshirtecommerce $module,
        \Context $context,
        Configuration $configuration,
        ProductDataProvider $productDataProvider,
        PriceFormatter $priceFormatter
    ) {
        parent::__construct();
        $this->module = $module;
        $this->context = $context;
        $this->configuration = $configuration;
        $this->productDataProvider = $productDataProvider;
        $this->priceFormatter = $priceFormatter;
    }

    public function indexAction(Request $request): Response
    {
        $productId = $request->query->get('product_id');
        $parentId = $request->query->get('parent_id');
        $cartId = $request->query->get('cart_id');

        // Start session using Symfony session management
        $session = $request->getSession();

        // Handle customer login state
        if ($this->context->customer->isLogged()) {
            $session->set('is_logged', [
                'login' => true,
                'email' => $this->context->customer->email,
                'id' => $this->context->customer->id
            ]);
        } else {
            $session->remove('is_logged');
        }

        // Get base URL
        $shopUrl = $request->getSchemeAndHttpHost() . $request->getBasePath();

        // Check for design-your-own case
        if (!$productId && !$parentId) {
            $defaultProductId = $this->configuration->get('TSHIRTECOMMERCE_DOWNLOADABLE');
            if ($defaultProductId) {
                list($parentId) = explode('::', $defaultProductId);
                if ($parentId) {
                    $productId = $this->getDesignProductId($parentId);
                }
            }
            // Fallback to first available product
            if (!$productId) {
                $firstProduct = $this->getFirstAvailableProduct();
                if ($firstProduct) {
                    $productId = $firstProduct['design_product_id'];
                    $parentId = $firstProduct['id_product'];
                }
            }
        }

        // Initialize variables
        $error = '';
        $content = '';
        $urlDesignload = '';

        // Validate product
        if ($productId && $parentId) {
            $content = '<div class="row-designer"></div>';
            $designProductId = $this->getDesignProductId($parentId);
            
            // Handle design product logic
            list($error, $urlDesignload) = $this->processDesignProduct(
                $productId, 
                $parentId, 
                $designProductId, 
                $cartId, 
                $shopUrl
            );
        } else {
            $error = $this->trans('Product Not Found', [], 'Modules.Tshirtecommerce.Front');
        }

        // Get product data if parent ID exists
        $product = null;
        if ($parentId) {
            $product = $this->getProduct($parentId);
        }

        // Get template variables
        $templateVars = $this->getTemplateVars($product, $error, $content, $shopUrl, $urlDesignload);

        // Render the template
        return $this->render('@Modules/tshirtecommerce/views/templates/front/designer.html.twig', $templateVars);
    }

    private function getDesignProductId(int $parentId): ?string
    {
        return \Db::getInstance()->getValue(
            'SELECT `design_product_id` 
            FROM `' . _DB_PREFIX_ . 'product` 
            WHERE `id_product` = ' . (int)$parentId
        );
    }

    private function getFirstAvailableProduct(): ?array
    {
        return \Db::getInstance()->getRow(
            'SELECT `id_product`, `design_product_id` 
            FROM `' . _DB_PREFIX_ . 'product` 
            WHERE `design_product_id` <> "" 
            AND `design_product_id` IS NOT NULL 
            AND `design_product_id` NOT LIKE "%::%"
            LIMIT 1'
        );
    }

    private function processDesignProduct(
        string $productId, 
        int $parentId, 
        ?string $designProductId, 
        ?string $cartId, 
        string $shopUrl
    ): array {
        $error = '';
        $urlDesignload = '';

        $productParams = explode(':', $designProductId);
        if (count($productParams) > 1) {
            if ($productId != $productParams[2]) {
                $error = $this->trans('Design Not Found', [], 'Modules.Tshirtecommerce.Front');
            } else {
                $urlDesignload = $shopUrl . '/tshirtecommerce/index.php?' . http_build_query([
                    'product' => $productParams[2],
                    'color' => $productParams[3],
                    'user' => $productParams[0],
                    'id' => $productParams[1],
                    'parent' => $parentId,
                    'cart_id' => $cartId,
                ]);
            }
        } else {
            if ($productId != $designProductId) {
                $error = $this->trans('Design Not Found', [], 'Modules.Tshirtecommerce.Front');
            } else {
                $urlDesignload = $shopUrl . '/tshirtecommerce/index.php?' . http_build_query([
                    'product' => $productId,
                    'parent' => $parentId,
                    'cart_id' => $cartId,
                ]);
            }
        }

        return [$error, $urlDesignload];
    }

    private function getProduct(int $parentId): ?array
    {
        try {
            $product = $this->productDataProvider->getProduct($parentId);
            $presenterFactory = new ProductPresenterFactory($this->context, new \Link());
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = $presenterFactory->getPresenter();

            return $presenter->present(
                $presentationSettings,
                $product,
                $this->context->language
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getTemplateVars(?array $product, string $error, string $content, string $shopUrl, string $urlDesignload): array
    {
        $logoLoading = $this->configuration->get('TSHIRTECOMMERCE_LOGO_LOADING') 
            ?: $shopUrl . '/tshirtecommerce/assets/images/logo-loading.png';
        
        $textLoading = $this->configuration->get('TSHIRTECOMMERCE_TEXT_LOADING') 
            ?: $this->trans('Loading...', [], 'Modules.Tshirtecommerce.Front');

        $mobileFull = (int)$this->configuration->get('TSHIRTECOMMERCE_FULL_MOBILE_LAYOUT') ?: 0;
        $cartAjax = (int)$this->configuration->get('TSHIRTECOMMERCE_CART_AJAX') ?: 1;

        return [
            'product' => $product,
            'error' => $error,
            'tshirtecommerce_content' => $content,
            'url' => $shopUrl,
            'url_mobile' => $shopUrl . '/tshirtecommerce/assets/css/mobile.css',
            'url_mobile_css' => $shopUrl . '/tshirtecommerce/prestashop/css/mobile.css',
            'url_prestashop_css' => $shopUrl . '/tshirtecommerce/assets/css/prestashop.css',
            'url_back' => $product ? $this->context->link->getProductLink($product) : $shopUrl,
            'url_designer' => $this->context->link->getModuleLink('tshirtecommerce', 'designer'),
            'url_api' => $shopUrl . '/modules/tshirtecommerce/ajax.php',
            'url_appjs' => $shopUrl . '/tshirtecommerce/prestashop/js/app9.js',
            'urlDesignload' => $urlDesignload,
            'tshirtecommerce_mobile_full' => $mobileFull,
            'tshirtecommerce_cart_ajax' => $cartAjax,
            'tshirtecommerce_cart_ajax_msg' => $this->trans(
                'Product added to your shopping cart. Do you want to continue shopping?',
                [],
                'Modules.Tshirtecommerce.Front'
            ),
            'logo_loading' => $logoLoading,
            'text_loading' => $textLoading,
            'ps_data' => [
                'id_language' => $this->context->language->id,
                'id_shop' => $this->context->shop->id,
                'id_currency' => $this->context->currency->id,
                'id_customer' => $this->context->customer->id,
                'id_country' => $this->context->country->id,
                'id_group' => $this->getCustomerGroup(),
            ]
        ];
    }

    private function getCustomerGroup(): int
    {
        if ($this->context->customer->id) {
            return \Customer::getDefaultGroupId((int)$this->context->customer->id);
        }
        return (int)\Group::getCurrent()->id;
    }
}