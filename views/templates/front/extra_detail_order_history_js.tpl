{*
* @module Prestashop Custom Product Designer
*
* @author 		tshirtecommerce - https://tshirtecommerce.com/
* @date 		January 11, 2017
* 
* API 			1.0.1
* 
* @copyright  	Copyright (C) 2016 tshirtecommerce.com. All rights reserved.
* @license    	GNU General Public License version 2 or later; see LICENSE
*
* @since 		1.5
*
*}
<style type="text/css">
	{literal}
	.tshirtecommerce-img-design {float: left;}
	{/literal}
</style>
<script type="text/javascript">
	jQuery(document).ready( function() {
		// Add design image to history
		jQuery('#order-detail-content table tbody tr').each( function(){
			var row_idx = jQuery(this).find('td:eq(1) label').attr('for');
			var html = '';
			//if (row_idx != -1) {
				{foreach from=$products item=product}
					if (row_idx == 'cb_'+'{$product.id_order_detail|intval}') {
						var print_type = '';
						{if isset($product.print_type)}
							print_type = '{$product.print_type}';
						{/if}
						html += '<p class="printing-type">'+print_type+'</p>';
						html += '<div class="zoom-gallery">';
						{foreach from=$product.tshirtecommerce_design_order_img|json_decode key=title item=img}
							html += "<div class='tshirtecommerce-img-thumb'>";
							html += '<img with="88px" height="88px" class="tshirtecommerce-img-thumb-i" alt="" title="" src="{$site_url}{$img}" />';
							{if (isset($allow_download_design) && $allow_download_design == 1)}
								{if $msg_store_empty == '' && count($store_temp)}
									{if check_return == 0 && $store_temp.prices > 0}
										html += '<br />';
									{/if}
								{else}
									{if $tshirtecommerce_store_status == 1}
										html += '<br /><center><a target="_blank" class="tshirtecommerce-link-download" href="{$site_url}{$product.download_link}{$title}">{l s="Download" mod="tshirtecommerce"}</a></center>';
									{/if}
								{/if}
							{/if}
							html += '</div>';
						{/foreach}
						html += '</div>';
						{if (isset($product.link))}
						html += '<a href="{$product.link}">{l s="View Design" mod="tshirtecommerce"}</a>';
						{/if}
					}
				{/foreach}
			//}
			jQuery(this).find('td:eq(1)').append(html);
		});
	});
</script>