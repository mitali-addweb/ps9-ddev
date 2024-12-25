<!-- start::tshirtecommerce -->
{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}

{if isset($error_warning) && !empty($error_warning)}
	<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> { $error_warning }
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
{/if}

<div class="panel panel-default" style="padding-left: 0 !important; padding-right: 0 !important;">
  	<div class="panel-body" style="padding-left: 0 !important; padding-right: 0 !important;">
      <div class="panel-heading" style="padding-left: 30px !important; padding-right: 30px; !important;">{l s='T-Shirt eCommerce Builder' mod='tshirtecommerce'}</div>
  		<div style="padding: 0; margin: 0; height: auto">
  			<iframe width="100%" style="border:0; min-height: 60vh;" id="tshirtecommerce-build" src="{$url}"></iframe>
  		</div>
	</div>
</div>
<script type="text/javascript">
  var design_platform = 'prestashop';
  function setHeightF(height){
    document.getElementById('tshirtecommerce-build').setAttribute('height', height + 'px');
  }
</script>
{/block}
<!-- end::tshirtecommerce -->