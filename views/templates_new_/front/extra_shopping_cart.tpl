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

<!-- Tshirtecommerce -->
<script type="text/javascript">
	$(document).ready(function() {
		{foreach from=$unit_price key=ukey item=uprice}
		$('#'+'{$ukey}').text('{$uprice}');
		{/foreach}
	});
</script>
<!-- /Tshirtecommerce -->