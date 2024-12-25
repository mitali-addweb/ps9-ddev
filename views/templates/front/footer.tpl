<!-- start:tshirtecommerce -->
<script type="text/javascript">
	$(document).ready(function(){
		{foreach from=$tproduct_hide item=product}
			$('.ajax_add_to_cart_button[data-id-product="'+{$product}+'"]').css('display', 'none');
		{/foreach}
	});
</script>
<!-- end:tshirtecommerce -->
