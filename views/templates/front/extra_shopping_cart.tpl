<!-- start:tshirtecommerce -->
<script type="text/javascript">
	$(document).ready(function() {
		{foreach from=$unit_price key=ukey item=uprice}
		$('#'+'{$ukey}').text('{$uprice}');
		{/foreach}
	});
</script>
{$elink}
<!-- end:tshirtecommerce -->