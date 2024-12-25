{*
* @module Prestashop Custom Product Designer
*
* @author 		tshirtecommerce - https://tshirtecommerce.com/
* @date 		January 11, 2017
* 
* API 			1.0.2
* 
* @copyright  	Copyright (C) 2016 tshirtecommerce.com. All rights reserved.
* @license    	GNU General Public License version 2 or later; see LICENSE
*
* @since 		1.5
*
*}

{if $psversion < 16102}
{literal}
	<style type="text/css">
		#center_column {width: 100%!important;}
	</style>
{/literal}
{/if}

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
</div>

<link href="{$url_mobile}" rel="stylesheet" />
<link href="{$url_prestashop_css}" rel="stylesheet" />
{if $product != null}
	<script type="text/javascript">
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
	</script>
	<script src="{$url_appjs}" type="text/javascript"></script>
{/if}
