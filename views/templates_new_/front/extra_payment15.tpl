{*
* @module Prestashop Custom Product Designer
*
* @author 		tshirtecommerce - https://tshirtecommerce.com/
* @date 		October 31, 2016
* 
* API 			1.0.1
* 
* @copyright  	Copyright (C) 2016 tshirtecommerce.com. All rights reserved.
* @license    	GNU General Public License version 2 or later; see LICENSE
*
* @since 		1.5
*
*}

<!-- Tshirteocmmerce -->
{function jsadd keypart=''}
    {foreach $data as $key => $item}
        {if not $item|@is_array}
            {if $keypart eq ''}
                design_images['{$key}'] = '{$item}'
             {else}
                design_images{$keypart}['{$key}'] = '{$item}'
            {/if}
        {else}
            design_images{$keypart}['{$key}'] = [];
            {jsadd data = $item keypart = "`$keypart`['`$key`']" }
        {/if}
    {/foreach}
{/function}
<script type="text/javascript">
	jQuery(document).ready( function() {
		var design_images=[];
		{jsadd data=$design_images}
		var url_path_img="{$url_path_img}";

		// Append image design to shopping cart
		jQuery('#cart_summary tr').each( function() {
			jQuery(this).find(".zoom-gallery").remove();
			jQuery(this).find(".link-edit-design").remove();

			var row_index = jQuery(this).index('tbody tr');
			if (row_index != -1) {
				var html = '<p class="printing-type">'+design_images[row_index-1]["printing_type"]+'</p><div class="zoom-gallery">';
				for (var d in design_images[row_index-1]['design']) {
					var title = d;
					html += "<a href='" + url_path_img + design_images[row_index-1]['design'][d] + "' data-source='" + url_path_img + design_images[row_index-1]['design'][d] + "' title='" + title + "' product-name='" + jQuery(this).find('.product-name a').text() + "'>";
					html += "<img alt='" + title + "' src='" + url_path_img+design_images[row_index-1]['design'][d] + "' title='" + title + "' class='tshirtecommerce-img-thumb' width='58px' height='58px' />";
					html += "</a>"; 
				}
				html += '</div>';
				if (design_images[row_index-1]["link"] != "") {
					html += '<a href="'+design_images[row_index-1]["link"]+'">{l s="View Design" mod="tshirtecommerce"}</a>';
				}
				jQuery(this).find('.cart_description').append(html);
			}
		});
	});

</script>
<!-- /Tshirtecommerce -->