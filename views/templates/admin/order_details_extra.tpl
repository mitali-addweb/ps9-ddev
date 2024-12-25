<!-- start:tshirtecommerce -->
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

			jQuery(this).parent().append(html);
		});
	});
</script>
<!-- end:tshirtecommerce -->