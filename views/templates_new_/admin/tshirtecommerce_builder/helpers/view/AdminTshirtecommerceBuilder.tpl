{*
* @module Prestashop Custom Product Designer
*
* @author     tshirtecommerce - https://tshirtecommerce.com/
* @date       April 10, 2017
* 
* API         1.0.3
* 
* @copyright  Copyright (C) 2016 tshirtecommerce.com. All rights reserved.
* @license    GNU General Public License version 2 or later; see LICENSE
*
* @since      1.5
*
*}

{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}

{if isset($error_warning) && !empty($error_warning)}
	<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> { $error_warning }
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
{/if}

<div class="panel panel-default" style="padding-left: 0 !important; padding-right: 0 !important;">
  	<div class="panel-body" style="padding-left: 0 !important; padding-right: 0 !important;{if isset($psversion) && $psversion == 0}padding:0!important;{/if}">
  		{if !isset($psversion) || $psversion == 1}
        <div class="panel-heading" style="padding-left: 30px !important; padding-right: 30px; !important;">T-Shirt eCommerce Builder</div>
      {/if}
  		<div style="padding: 0; margin: 0; height: auto">
  			<iframe width="100%" style="border:0; min-height: 60vh;" id="tshirtecommerce-build" src="{$url}"></iframe>
  		</div>
	</div>
</div>
<script type="text/javascript">
  function setHeightF(height){
    document.getElementById('tshirtecommerce-build').setAttribute('height', height + 'px');
  }
</script>
{/block}