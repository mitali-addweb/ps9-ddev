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
<!-- Prestashop Custom Product Designer -->
<script type="text/javascript">
	jQuery(document).ready(function() {
		{if ($allow_download_design == 1)}
		jQuery('li > .tselink').each(function() {
			var rowid = jQuery(this).attr('rowid');
			var html = '<br /> {l s="Download" mod="tshirtecommerce"} : ';
			if (rowid !== '') {
				{foreach from=$downlnoads key=rid item=row}
				if (rowid == '{$rid}') {
					{if empty($msg_store_empty) && count($store_temp)}
						{if $store_temp.prices > 0}
							{if isset($shop_email) && !empty($shop_email)}
							html += '<strong>{l s="Please contact with" mod="tshirtecommerce"} <a href="mailto:{$shop_email}">{$shop_email}</a> {l s="to download" mod="tshirtecommerce"}.</strong>';
							{else}
							html += '<strong>{l s="Please contact with administrator to download." mod="tshirtecommerce"}</strong>';
							{/if}
						{/if}
					{else}
						{assign var="idx" value="1"}
						{foreach from=$row.images key=view item=image}
							{if !empty($image)}
								html += '<a href="{$row.link}{$view}" target="_blank">{$views[$view]}</a>';
								{if $idx < $row.images|count}
								html += ' - ';
								{/if}
								{$idx++}
							{/if}
						{/foreach}
					{/if}
				}
				{/foreach}
			}
			jQuery(this).parent().append(html);
		});
		{/if}
	});
</script>
<!-- \Prestashop Custom Product Designer -->