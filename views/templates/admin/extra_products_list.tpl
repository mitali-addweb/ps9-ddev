<!-- start:tshirtecommerce -->
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
			$('#form-product').prepend('<div class="bootstrap"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><img src="'+tshirtecommerce_img+'" alt="tshirtecommerce" /> : '+"{l s='The product has already been actived Tshsirtecommerce' mod='tshirtecommerce'}"+'</div></div>');
		} else {
			if ($('table.table_grid').length) {
				$('table.table_grid').prepend('<div class="bootstrap"><div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><img src="'+tshirtecommerce_img+'" alt="tshirtecommerce" /> : '+"{l s='The product has already been actived Tshsirtecommerce' mod='tshirtecommerce'}"+'</div></div>');
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

			var $titleCell = $checkbox.closest('tr').find('td:eq(2)');
			var $img = $('<img>',{
				src 	: tshirtecommerce_img,
				style 	: 'display:inline-table;position:absolute;max-width:16px;max-height:16px;',
				title 	: "{l s='Tshirtecommerce is active for this product' mod='tshirtecommerce'}"
			}).prependTo($titleCell);
		});
	});
</script>
<!-- end:tshirtecommerce -->