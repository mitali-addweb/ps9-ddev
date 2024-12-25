<!-- start::tshirtecommerce -->
{if $product != null}
	{capture name=path}{l s='Custom Your Own' mod='tshirtecommerce'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}

	{literal}
		<style type="text/css">
			#center_column {width: 100%!important;}
		</style>
	{/literal}

	<div class="bootstrap">
	{if (isset($error) && !empty($error))}
		<div class="alert alert-warning">
			{$error}
		</div>
	{else}
		<div class="container">
			{$tshirtecommerce_content}
		</div>
	{/if}
	<div id="tshirtecommerce-dg-mask" class="tshirtecommerce-loading" style="display: none;"></div>
	</div>

	<link href="{$url_mobile}" rel="stylesheet" />
	<link href="{$url_prestashop_css}" rel="stylesheet" />
	<script type="text/javascript">
		var tshirtecommerce_cart_ajax = '{$tshirtecommerce_cart_ajax}';
		var tshirtecommerce_cart_ajax_msg = '{$tshirtecommerce_cart_ajax_msg}';
		var tshirtecommerce_mobile_full = '{$tshirtecommerce_mobile_full}';
		var ps_product_id={$ps_product_id};
		var ps_language_id={$ps_language_id};
		var ps_shop_id={$ps_shop_id};
		var ps_link_register_account="{$link_register_account}";
		var ps_link_forgot_password="{$link_forgot_password}";
		var ps_link_shopping_cart="{$link_shopping_cart}";
		var ps_id_customer="{$ps_id_customer}";
		var ps_id_currency="{$ps_id_currency}";
		var ps_id_country="{$ps_id_country}";
		var ps_id_group="{$ps_id_group}";

		var urlBack 		= '{$url_back}';
		var urlDesign 		= '{$url_designer}';
		var urlDesignload 	= '{$urlDesignload}';
		var urlTshirtecommermceApi = '{$url_api}';
		var logo_loading 	= '{$logo_loading}';
		var text_loading 	= '{$text_loading}';

		var url_regiter_ps = '{$url_regiter_ps}';
	</script>
	<script src="{$url_appjs}" type="text/javascript"></script>
{/if}
<!-- end::tshirtecommerce -->
