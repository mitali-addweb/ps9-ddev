{* T-Shirt eCommerce Designer Template – PS9 Compatible *}
{if isset($product) && $product && (($product.id|default:0) > 0 || is_object($product))}
{capture name=path}
<a href="{$link->getProductLink($product)|escape:'html':'UTF-8'}">
{if is_object($product)}{$product->name}{else}{$product.name}{/if}
</a>
<span class="navigation-pipe">{$navigationPipe|default:'>'}</span>
<span class="navigation_page">{l s='Custom Your Own' mod='tshirtecommerce'}</span>
{/capture}

<div class="bootstrap">
{if (isset($error) && !empty($error))}
<div class="alert alert-warning">
{$error}
</div>
{else}
<div class="container">
{$tshirtecommerce_content nofilter}
</div>
{/if}
<div id="tshirtecommerce-dg-mask" class="tshirtecommerce-loading" style="display:none;"></div>
</div>

<link href="{$url_mobile}" rel="stylesheet" />
<link href="{$url_mobile_css}" rel="stylesheet" />
<link href="{$url_prestashop_css}" rel="stylesheet" />

{* PS9: Inline scripts instead of addJsDef *}
{if isset($combinations) && $combinations}
<script>
var colors = {$colors|json_encode nofilter};
var combinations = {$combinations|json_encode nofilter};
var combinationsFromController = {$combinations|json_encode nofilter};
</script>
{/if}

<script>
var ps_product_id = {$ps_product_id|intval};
var ps_language_id = {$ps_language_id|intval};
var ps_shop_id = {$ps_shop_id|intval};
var ps_link_register_account = {$link_register_account|json_encode nofilter};
var ps_link_forgot_password = {$link_forgot_password|json_encode nofilter};
var ps_link_shopping_cart = {$link_shopping_cart|json_encode nofilter};
var ps_id_customer = {$ps_id_customer|intval};
var ps_id_currency = {$ps_id_currency|intval};
var ps_id_country = {$ps_id_country|intval};
var ps_id_group = {$ps_id_group|intval};
</script>

<script type="text/javascript">
var tshirtecommerce_cart_ajax = '{$tshirtecommerce_cart_ajax}';
var tshirtecommerce_cart_ajax_msg = '{$tshirtecommerce_cart_ajax_msg|escape:'javascript'}';
var tshirtecommerce_mobile_full = '{$tshirtecommerce_mobile_full}';
var urlBack = '{$url_back|escape:'javascript'}';
var urlDesign = '{$url_designer|escape:'javascript'}';
var urlDesignload = '{$urlDesignload|escape:'javascript'}';
var urlTshirtecommermceApi = '{$url_api|escape:'javascript'}';
var logo_loading = '{$logo_loading|escape:'javascript'}';
var text_loading = `{$text_loading|escape:'javascript'}`;
var url_regiter_ps = '{$url_regiter_ps|escape:'javascript'}';
</script>
<script src="{$url_appjs}" type="text/javascript"></script>

{else}
<div class="bootstrap">
<div class="alert alert-danger">
<p>{l s='No designer product configured. Please:' mod='tshirtecommerce'}</p>
<ul>
<li>{l s='Go to module settings and configure a default product, OR' mod='tshirtecommerce'}</li>
<li>{l s='Create a product with a design template assigned, OR' mod='tshirtecommerce'}</li>
<li>{l s='Access the designer with proper product_id and parent_id parameters' mod='tshirtecommerce'}</li>
</ul>
</div>
</div>
{/if}
