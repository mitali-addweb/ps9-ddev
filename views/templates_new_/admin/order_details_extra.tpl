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
* @since 		1.5
*
*}

<link rel="stylesheet" href="{$site_url}prestashop/css/store.css" type="text/css" media="all">
<div class="arts-store table-responsive" style="overflow: hidden;">
	<table cellpadding="0" cellspacing="0" width="100%" class="tshirtecommerce-store-arts-order">
	</table>
	<div class="arts-store-payment"></div>
</div>
<script type="text/javascript">
	var ajax_id_order = '{$ajax_id_order}';
	var tshirtecommerce_ajax_url_store = '{$ajax_url_store}';
	var check_return = 0;
	var check_remove_link = false;
</script>
{if $msg_store_empty == ''}
<script type="text/javascript" src="{$site_url}prestashop/js/store.js"></script>
{/if}

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('a.tselink').each(function() {
			var rowid = jQuery(this).attr('rowid');
			var html = '<br /> {l s="Download" mod="tshirtecommerce"} : ';
			if (rowid !== '') {
				{foreach from=$downlnoads key=rid item=row}
				if (rowid == '{$rid}') {
					{assign var="idx" value="1"}
					{foreach from=$row.images key=view item=image}
						html += '<a href="{$row.link}{$view}" target="_blank">{$views[$view]}</a>';
						{if $idx < $row.images|count}
						html += ' - ';
						{/if}
						{$idx++}
					{/foreach}
				}
				{/foreach}
			}
			//jQuery(this).parent().append(html);

			// Store Arts Table
			{if $msg_store_empty == ''}
				// Encrypt arts on store
				check = tshirtecommerce_payment.key(ajax_id_order);
			{/if}

			// check tshirtecommerce-store
			{if $tshirtecommerce_store_status == 1 && count($store_temp) > 0 && $store_temp.prices > 0}
				html = '<br /> {l s="Download" mod="tshirtecommerce"} : ';
				html += '{l s="Please payment before download." mod="tshirtecommerce"}';
				{if $msg_store_empty != ''}
					html += '{$msg_store_empty}';
				{else}
					{if count($store_temp) > 0}
						if (check_return == 0) {
							html += '<div class="tshirt-header-storearts"><span class="lbl">Arts of Store</span><div>';
							{if ($store_temp.prices > 0) }
								html += '<p class="tshirt-alertpayment">Your order using arts of store with cost <strong>'+'{$store_temp.prices}'+' Credits</strong>. Please payment before download file output. <a href="http://store.9file.net/" target="_blank">Read More</a></p>';
								html += '<p class="btnpaymentnow"><a href="javascript:void(0);" data-id="'+'{$store_temp.api_ids_rowid}'+'" onclick="tshirtecommerce_payment.load(this);">Payment Now!</a></p>';
							{else}
								tshirtecommerce_payment.key("{$store_temp.tshirtecommerce_design_order_id}");
							{/if}
							html += '<div class="arts-store table-responsive" style="overflow: hidden;"><table cellpadding="0" cellspacing="0" width="100%" class="tshirtecommerce-store-arts-order">';
							html += '<thead><tr><th>Position</th><th>Image</th><th>Price & Info</th></tr></thead><tbody>';
							{foreach from=$store_temp.arts item=art}
								{if $art['price'] == 0 }
									html_span = '<strong>FREE</strong>';
								{else}
									html_span = 'Price: <strong>'+'{$art.price * $store_temp.qty}'+' Credits</strong><br />';
									html_span += 'Quantity: <strong>'+'{$store_temp.qty}'+'</strong>';
								{/if}
								html += '<tr><td>'+'{$art.view}'+'</td><td><a href="http://store.9file.net/arts/detail/'+'{$art.id}'+'" target="_blank"><img width="30" src="'+'{$art.thumb}'+'" alt="'+'{$art.title}'+'" title="'+'{$art.title}'+'"></a></td><td> '+html_span+'<br />File type: <strong>'+'{$art.file}'+'</strong></td></tr>';
							{/foreach}
							html += '</tbody></table><div class="arts-store-payment"></div></div></div></div>';
						}
					{/if}
					html += '';
				{/if}
				html += '</br>'
			{/if}

			jQuery(this).parent().append(html);
		});
	});
</script>