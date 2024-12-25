{*
* @module Prestashop Custom Product Designer
*
* @author 		tshirtecommerce - https://tshirtecommerce.com/
* @date 		April 10, 2017
* 
* API			1.0.3
* 
* @copyright  	Copyright (C) 2016 tshirtecommerce.com. All rights reserved.
* @license    	GNU General Public License version 2 or later; see LICENSE
*
* @since 		1.5
*
*}

<!-- Tshirtecommerce module -->

{if isset($design_product_id) && !empty($design_product_id)}
	{if (isset($allow_hide_addtocart) && $allow_hide_addtocart == 1)}
		{literal}
		<style type="text/css">
			#add_to_cart { display: none !important; }
		</style>
		{/literal}
	{/if}
	<div id="tshirtecommerce-product-detail" class="bootstrap">
		<div class="tshirtecommerce-hide">
			<input type="hidden" name="design[rowid]" value="" />
			<input type="hidden" name="design[design_product_id]" value="" />
			<input type="hidden" name="design[color_hex]" value="" />
			<input type="hidden" name="design[color_title]" value="" />
			<input type="hidden" name="design[images]" value="" />
			<input type="hidden" name="design[idea]" value="" />
		</div>
		{if $show_attribute == 1}
		<link rel="stylesheet" href="{$site_url}/modules/tshirtecommerce/views/css/product.css" type="text/css" media="all">
		<script type="text/javascript" src="{$site_url}/tshirtecommerce/prestashop/js/product.js"></script>
		<div class="tshirtecommerce-attributes">
			{$attributes nofilter}
		</div>
		{/if}
		<p class="tshirtecommerce-custom-block">
			{if $is_class == 0}
				<a target="_parent" href="{$url}" class="" style="{$tshirtecommerce_custom_style}">{$tshirtecommerce_custom_text}</a>
			{else}
				<a target="_parent" href="{$url}" class="{$tshirtecommerce_custom_style}">{$tshirtecommerce_custom_text}</a>
			{/if}
		</p>
	</div>
{/if}

{if isset($is_hide_all_customization) && $is_hide_all_customization === true}
	<script type="text/javascript">
		$(document).ready(function() {
			$('#customizationForm').css('display', 'none');
			$('#customizationForm').parent().css('display', 'none');
		});
	</script>
{else}
	<script type="text/javascript">
		$(document).ready(function() {
			{if count($customizations_hide) > 0}
				{foreach from=$customizations_hide item=cushide}
					$('input[name="{$cushide}"]').parent().css('display', 'none');
					$('input[name="{$cushide}"]').parent().parent().css('display', 'none');
					$('input[name="{$cushide}"]').parent().parent().parent().css('display', 'none');

					$('textarea[name="{$cushide}"]').parent().css('display', 'none');
					$('textarea[name="{$cushide}"]').parent().parent().css('display', 'none');
					$('textarea[name="{$cushide}"]').parent().parent().parent().css('display', 'none');
				{/foreach}
			{/if}
		});
	</script>
{/if}

<!-- /Tshirtecommerce module -->
