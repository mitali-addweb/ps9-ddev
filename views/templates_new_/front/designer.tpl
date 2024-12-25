{*
* @module Prestashop Custom Product Designer
*
* @author 		tshirtecommerce - https://tshirtecommerce.com/
* @date 		April 10, 2017
* 
* API 			1.0.3
* 
* @copyright  	Copyright (C) 2016 tshirtecommerce.com. All rights reserved.
* @license    	GNU General Public License version 2 or later; see LICENSE
*
* @since 		1.6
*
*}

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
<link href="{$url_mobile_css}" rel="stylesheet" />
<link href="{$url_prestashop_css}" rel="stylesheet" />
{if $product != null}
	{strip}
	{if isset($combinations) && $combinations}
		{addJsDef colors=$colors}
		{addJsDef combinations=$combinations}
		{addJsDef combinationsFromController=$combinations}
	{/if}

	{addJsDef ps_product_id=$ps_product_id}
	{addJsDef ps_language_id=$ps_language_id}
	{addJsDef ps_shop_id=$ps_shop_id}

	{addJsDef ps_link_register_account=$link_register_account}
	{addJsDef ps_link_forgot_password=$link_forgot_password}

	{addJsDef ps_link_shopping_cart=$link_shopping_cart}

	{addJsDef ps_id_customer=$ps_id_customer}
	{addJsDef ps_id_currency=$ps_id_currency}
	{addJsDef ps_id_country=$ps_id_country}
	{addJsDef ps_id_group=$ps_id_group}
	{/strip}
	<script type="text/javascript">
		var urlBack 		= '{$url_back}';	
		var urlDesign 		= '{$url_designer}';
		var urlDesignload 	= '{$urlDesignload}';
		var urlTshirtecommermceApi = '{$url_api}';
		var logo_loading 	= '{$logo_loading}';
		var text_loading 	= '{$text_loading}';
	</script>
	<script src="{$url_appjs}" type="text/javascript"></script>
{/if}