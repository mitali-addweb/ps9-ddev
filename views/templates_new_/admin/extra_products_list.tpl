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

<script type="text/javascript">
	var tshirtecommerce_img = '{$site_url|htmlspecialchars}/tshirtecommerce/prestashop/img/tshirtecommerce.png';
	var tshirtecommerce_ids = [
		{foreach $eproducts item=tshirtecommerce_id}
			{$tshirtecommerce_id.id_product|intval},
		{/foreach}
	];
	$(function(){
		var tshirtecommerce_products_table = $('#product');
		if(!tshirtecommerce_products_table.length) tshirtecommerce_products_table = $('#table-product');
		if(!tshirtecommerce_products_table.length) return;

		// display alert infomation note
		if ($('#form-product').length) {
			{if $psversion > 15}
			$('#form-product').prepend('<div class="bootstrap"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><img src="'+tshirtecommerce_img+'" alt="tshirtecommerce" /> : '+"{l s='The product has already been actived Tshsirtecommerce'}"+'</div></div>');
			{else}
			$('#form-product').prepend('<div class="hint" style="display:block"><img src="'+tshirtecommerce_img+'" alt="tshirtecommerce" /> : '+"{l s='The product has already been actived Tshsirtecommerce'}"+'</div><br/>');
			{/if}
		} else {
			if ($('table.table_grid').length) {
				{if $psversion > 15}
				$('table.table_grid').prepend('<div class="bootstrap"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><img src="'+tshirtecommerce_img+'" alt="tshirtecommerce" /> : '+"{l s='The product has already been actived Tshsirtecommerce'}"+'</div></div>');
				{else}
				$('table.table_grid').prepend('<div class="hint" style="display:block"><img src="'+tshirtecommerce_img+'" alt="tshirtecommerce" /> : '+"{l s='The product has already been actived Tshsirtecommerce'}"+'</div><br/>');
				{/if}
			}
		}

		// display icon on products list
		$.each(tshirtecommerce_ids, function(i, tshirtecommerce_id) {
			var $checkbox = tshirtecommerce_products_table.find('input[type=checkbox][value='+ tshirtecommerce_id +']');

			if (!$checkbox.length) {
				$tr = tshirtecommerce_products_table.find('tr[id]');

				if ($tr.length == 1 && $tr.attr('id').indexOf('_' + tshirtecommerce_id + '_') > -1) {
					$checkbox = $tr.find('td:eq(0)');
				} else {
					return true;
				}
			};

			{if $psversion > 15}
			var $titleCell = $checkbox.closest('tr').find('td:eq(2)');
			{else}
			var $titleCell = $checkbox.closest('tr').find('td:eq(3)');
			{/if}
			var $img = $('<img>',{
				src 	: tshirtecommerce_img,
				style 	: 'display:inline-table;{if $psversion > 15}position:absolute{else}position:relative{/if};max-width:16px;max-height:16px;',
				title 	: '{l s='Tshirtecommerce is active for this product' mod='tshirtecommerce'}'
			}).prependTo($titleCell);
		});
	});
</script>