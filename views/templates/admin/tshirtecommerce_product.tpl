<!-- start::tshirtecommerce -->
{if isset($tshirtecommerce_warning) && !empty($tshirtecommerce_warning)}
	{if isset($is_ps_old_version)}
		<div class="hint" style="display:block;min-height:0;">{l s='There is 1 warning.' mod='tshirtecommerce'} {l s=$tshirtecommerce_warning mod='tshirtecommerce'}</div>
	{else}
		<div class="alert alert-warning">
			<button type="button" class="close" data-dismiss="alert">×</button>
				{l s='There is 1 warning.' mod='tshirtecommerce'}
			<ul style="display:block;list-style: none;">
				<li>{l s=$tshirtecommerce_warning mod='tshirtecommerce'}</li>
			</ul>
		</div>
	{/if}
{else}
	{if !isset($is_ps_old_version)}
		<div class="alert alert-info">
			{l s='If you want to hide option design of product. Please click Clear button to remove all data design.' mod='tshirtecommerce'}
			</br>
			{l s='If this product using color combination please add design color hex same them. You don\'t need color combination too. We are not support for texture of color combination.' mod='tshirtecommerce'}
		</div>
	{/if}
	<div class="bootstrap panel panel-default">
	  	<div class="panel-body">
	  		{if isset($is_ps_old_version)}
	  			<h4>{l s='T-Shirt eCommerce'}</h4>
	  			<div class="hint" style="display:block;min-height:0;">
	  				{l s='If you want to hide option design of product. Please click Clear button to remove all data design.' mod='tshirtecommerce'}
	  				<br/>
	  				{l s='If this product using color combination please add design color hex same them. You don\'t need color combination too. We are not support for texture of color combination.' mod='tshirtecommerce'}
	  			</div>
	  		{else}
		  		<div class="panel-heading form-group" style="padding-left: 0;padding-right: 0;">{l s='T-Shirt eCommerce'}</div>
			{/if}
	  		<div id="tab-tshirtecommerce" style="padding: 15px 0 0 0; margin: 0; height: auto;">
				<input type="hidden" value="{$design_product_id}" id="_product_id" name="design_product_id">
		        <input type="hidden" value="{$design_product_title_img}" id="_product_title_img" name="design_product_title_img">
		        <input type="hidden" value="{$tshirtecommerce_design_type}" id="tshirtecommerce_design_type" name="tshirtecommerce_design_type"/>
		        <div class="col-sm-12 form-group">
		          	<div class="btn-group" style="padding-bottom:5px;">
		            	<button type="button" class="btn btn-primary" style="text-transform:initial;">{l s='Change Product Design' mod='tshirtecommerce'}</button>
		            	<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		              		<span class="caret" style="border-top-color: #fff!important;"></span>
		              		<span class="sr-only">{l s='Toggle Dropdown' mod='tshirtecommerce'}</span>
		            	</button>
		            	<ul class="dropdown-menu">
		              		<li><a href="javascript:void(0)" key="123" onclick="app.admin.product(this, 0)"><strong>{l s='View List' mod='tshirtecommerce'}</strong>{l s=' Product Design' mod='tshirtecommerce'}</a></li>
		              		<li class="divider" role="separator"></li>
		              		<li><a href="javascript:void(0)" key="123" onclick="app.admin.product(this, 4)"><strong>{l s='Create New' mod='tshirtecommerce'}</strong>{l s=' Product Design' mod='tshirtecommerce'}</a></li>
		            	</ul>
		          	</div>
		          	<div class="btn-group" style="padding-bottom:5px;">
			            <button type="button" class="btn btn-primary">{l s='Design Templates' mod='tshirtecommerce'}</button>
			            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			              	<span class="caret" style="border-top-color: #fff!important;"></span>
			              	<span class="sr-only">{l s='Toggle Dropdown' mod='tshirtecommerce'}</span>
			            </button>
			            <ul class="dropdown-menu">
			              	<li><a href="javascript:void(0)" key="123" onclick="app.admin.product(this, 1)"><strong>{l s='Admin' mod='tshirtecommerce'}</strong>{l s=' Design saved' mod='tshirtecommerce'}</a></li>
			              	<li><a href="javascript:void(0)" key="123" onclick="app.admin.product(this, 2)"><strong>{l s='Clients' mod='tshirtecommerce'}</strong>{l s=' Design saved' mod='tshirtecommerce'}</a></li>
			              	<li class="divider" role="separator"></li>
			              	<li><a href="javascript:void(0)" key="123" onclick="app.admin.product(this, 3)">{l s='Create New Design' mod='tshirtecommerce'}</a></li>
			            </ul>
			        </div>
			        <div class="btn-group" style="padding-bottom:5px;">
			            <a href="javascript:void(0)" class="btn btn-danger" key="123" onclick="app.admin.clear()">{l s='Clear' mod='tshirtecommerce'}</a>
			        </div>
			        <div id="tshirtecommerce-div-design-temp" style="display:none;">
			          	<div class="form-group">
			            	<label class="col-sm-2 control-label">{l s='Price of Printing' mod='tshirtecommerce'}</label>
			            	<div class="col-sm-3">
			              		<input type="text" onchange="setupPrice(this)" value="{$price_of_print}" id="price_of_print" name="price_of_print" class="form-control">
			            	</div>
				            <div class="col-sm-7">
				              	<small>{l s='This price is price of printing product when you use design template.' mod='tshirtecommerce'}</small>
				            </div>
			          	</div>
			        </div>
			        <br />
			        {if !isset($is_ps_old_version)}
			        <br />
			        <div style="clear:both;"></div>
			        {/if}
			        <div id="add_designer_product">
			        {if (isset($design_product_id) && !empty($design_product_id))}
			        	<iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" style="min-height: 800px!important;" src="{$tshirt_product_url}"></iframe>
			        {/if}
			        </div>
		        </div>
	  		</div>
		</div>
		<div class="panel-footer">
			<a href="index.php?controller=AdminProducts&token={$token}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='tshirtecommerce'}</a>
			<div class="pull-right">
				<button id="_submitAddproductAndStay" type="submit" class="btn btn-default hidden" name="submitAddproductAndStay"><i class="process-icon-save"></i> {l s='Save and stay' mod='tshirtecommerce'}</button>
				<a class="btn btn-default" href="javascript:void(0)" onclick="product_submit_link_click({if !isset($is_ps_old_version)}2{else}152{/if})"><i class="process-icon-save"></i> {l s='Save' mod='tshirtecommerce'}</a>
				<button id="_submitAddproduct" type="submit" class="btn btn-default hidden" name="submitAddproduct"><i class="process-icon-save"></i> {l s='Save' mod='tshirtecommerce'}</button>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		var url_product_design_blank 	= '{$url_product_design_blank}';
		var url_product_design 			= '{$url_product_design}';
		var tshirtecommerce_admin 		= 1;
		var ajaxurl 					= 'index.php?controller=AdminProducts&id_product={$product_id}&updateproduct&token={$token}';
		var tshirtURL 					= '{$url}' + '/tshirtecommerce/';
		var baseDir 					= '{$url}';
		var ps_colors 					= '{$ps_colors}';
		var language_id 				= '{$language_id}';
		var product_title 				= "{$product_title|escape:javascript|escape:'htmlall'}";
		var product_short_description 	= `{$product_short_description}`;
		var product_description 		= `{$product_description}`;
		var product_sku 				= '{$product_sku}';
		var product_price 				= '{$product_price}';
		var product_min_order 			= '{$product_min_order}';
		var product_thumb 				= '{$product_thumb}';
		var ps_id_product 				= '{$product_id}';
		var design_platform = 'prestashop';
	</script>
	<script src="{$url}/tshirtecommerce/prestashop/js/app.js"></script>
	<script src="{$url}/tshirtecommerce/prestashop/js/prestashop.js"></script>
	{if isset($is_ps_old_version)}
	<script type="text/javascript">
		$(document).ready(function() {
			$(".tab-page").click(function(e) {
				e.preventDefault();

				currentId = $(".productTabs a.selected").attr('id').substr(5);
				id = $(this).attr('id').substr(5);

				if (id == 'ModuleTshirtecommerce') {
					$('#desc-product-save').hide();
					$('#desc-product-save-and-stay').hide();
				} else {
					$('#desc-product-save').show();
					$('#desc-product-save-and-stay').show();
				}
			});
		});
	</script>
	{/if}
{/if}