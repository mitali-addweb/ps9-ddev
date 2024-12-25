<!-- start::tshirtecommerce -->
{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}

{if (isset($error_warning) && !empty($error_warning))}
	<div class="bottstrap">
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {$error_warning}
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	</div>
{/if}

{if (isset($msg) && $msg == 'success')}
	<div class="bootstrap">
		<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> {l s='Setting up success' mod='tshirtecommerce'}
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	</div>
{/if}

{if (isset($msg) && $msg == 'failed')}
	<div class="bootstrap">
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {l s='Setting up failed' mod='tshirtecommerce'}
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	</div>
{/if}

<div class="bootstrap">
	<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> {l s='Click Save on this page to save currency for Designer Tool' mod='tshirtecommerce'}
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
</div>

<div class="bootstrap panel panel-default">
  	<div class="panel-body">
  		<div class="panel-heading">
  			{l s='Prestashop Custom Product Designer Settings' mod='tshirtecommerce'}
  			- <a class="btn btn-success btn-sm" target="_blank" href="{$tshirtecommerce_quicksetup_link}">Quick Setup</a>
  		</div>

  		<form id="tshirt-settings-form" method="POST" action="{$action}{$token}" class="form-horizontal" enctype="multipart/form-data">
  			<div class="form-group">
				<div class="alert alert-info">{l s='Click Save button to synchrony currency and decimal number on Designer tool with your prestashop system' mod='tshirtecommerce'}</div>
		
				<label class="control-label col-lg-3">{l s='Currency' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<label class="control-label" style="line-height: 22px;"><strong>{$pscurrency}</strong> - <strong>{$psformat}</strong></label>
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">
						<a href="{$presta_currency_page}">{l s='Change currency'}</a>
					</div>
				</div>
  			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Product default' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					{if (count($products) > 0)}
					<select class="form-control" name="tshirtecommerce_product_default">
						{foreach from=$products item=product}
					  	<option value="{$product.id}" {if ($product.id==$setting_product)} selected="true" {/if}>{$product.name}</option>
					  	{/foreach}
					</select>
					{else}
						<select class="form-control" name="tshirtecommerce_product_default"><option></option></select>
					{/if}
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">
						{l s='Choose product default. It is very important. This product will show in designer.' mod='tshirtecommerce'}
						<br />
						{l s='Default link' mod='tshirtecommerce'} : <a title="design-your-own" href="{$link_design_your_own}" target="_blank">{$link_design_your_own}</a>. {l s='You can use this link for creating Design-Your-Own menu on your site.' mod='tshirtecommerce'}
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='SEO Urls' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<a target="_blank" href="{$presta_seo_page}" style="line-height: 33px">{l s='Click here to change' mod='tshirtecommerce'}</a>
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">
						{l s='Here you can replace tshirtecommerce/designer on URL.' mod='tshirtecommerce'}
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Allow download design' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="tshirtecommerce_downloadbale" id="chktshirtdownloadable_on" value="1" {if isset($setting_downloadable) && $setting_downloadable == 1} checked="checked" {/if} />
						<label for="chktshirtdownloadable_on" class="radioCheck">{l s='Yes' mod='tshirtecommerce'}</label>
						<input type="radio" name="tshirtecommerce_downloadbale" id="chktshirtdownloadable_off" value="0" {if !isset($setting_downloadable) || $setting_downloadable == 0} checked="checked" {/if} />
						<label for="chktshirtdownloadable_off" class="radioCheck">{l s='No' mod='tshirtecommerce'}</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">{l s='Allow clients to download design on order history.' mod='tshirtecommerce'}</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Position Custom Design Button' mod='tshirtecommerce'}</label>
				<div class="col-lg-5">
					<div class="radio">
						<label for="tshirtecommerce_position_custom_design_btn_1">
							<input type="radio" name="tshirtecommerce_position_custom_design_btn" id="tshirtecommerce_position_custom_design_btn_1" value="1" {if $tshirtecommerce_position_custom_design_btn == 1}checked{/if} />
							{l s='Display on right column' mod='tshirtecommerce'}
						</label>
					</div>
					<div class="radio">
						<label for="tshirtecommerce_position_custom_design_btn_0">
							<input type="radio" name="tshirtecommerce_position_custom_design_btn" id="tshirtecommerce_position_custom_design_btn_0" value="0" {if $tshirtecommerce_position_custom_design_btn == 0}checked{/if} />
							{l s='Display on left column' mod='tshirtecommerce'}
						</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Disable mobile layout' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="tshirtecommerce_mobile_full" id="chktshirtfullmobile_on" value="1" {if !isset($tshirtecommerce_mobile_full) || $tshirtecommerce_mobile_full == 1} checked="checked" {/if} />
						<label for="chktshirtfullmobile_on" class="radioCheck">{l s='Yes' mod='tshirtecommerce'}</label>
						<input type="radio" name="tshirtecommerce_mobile_full" id="chktshirtfullmobile_off" value="0" {if isset($tshirtecommerce_mobile_full) && $tshirtecommerce_mobile_full == 0} checked="checked" {/if} />
						<label for="chktshirtfullmobile_off" class="radioCheck">{l s='No' mod='tshirtecommerce'}</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">{l s='Disable show full layout of designer tool on mobile' mod='tshirtecommerce'}</div>
				</div>
			</div>
			
			<div class="alert alert-info">{l s='Purchase code must be verify in updating Prestashop Custom Product Designer module.' mod='tshirtecommerce'}</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Purchase Code' mod='tshirtecommerce'}</label>
				<div class="col-lg-5">
					<input style="font-weight:bold!important;" type="text" name="tshirtecommerce_purchase_code" value="{$purchase_code}" />
				</div>
				<div class="col-lg-4">
					{if $verified == 0}
					<span class="label label-success">{l s='VERIFIED' mod='tshirtecommerce'}</span>
					{else}
					<span class="label label-danger">{l s='NOT VERIFIED' mod='tshirtecommerce'}</span>
					{/if}
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block"><a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">{l s='Where is my Purchase Code?' mod='tshirtecommerce'}</a></div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Custom Text Button' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<input type="text" class="form-control" name="tshirtecommerce_custom_text" value="{$tshirtecommerce_custom_text}" />
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">{l s='Allow change text of Custom Your Design Button' mod='tshirtecommerce'}</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Custom Style Button' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<input class="form-control" name="tshirtecommerce_custom_style" value="{$tshirtecommerce_custom_style}" />
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">
						{l s='Please enter CSS class name (e.g. "btn-group", "btn button-custom") or CSS style code (e.g. "padding:6px 8px", "padding 6px 8px;color: #fff;" )'}
						<br />
						{l s='Allow change style of Custom Your Design Button' mod='tshirtecommerce'}
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Hide Add To Cart' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="tshirtecommerce_hide_add_to_cart" id="chktshirthideaddtocart_on" value="1" {if isset($tshirtecommerce_hide_add_to_cart) && $tshirtecommerce_hide_add_to_cart == 1} checked="checked" {/if} />
						<label for="chktshirthideaddtocart_on" class="radioCheck">{l s='Yes' mod='tshirtecommerce'}</label>
						<input type="radio" name="tshirtecommerce_hide_add_to_cart" id="chktshirthideaddtocart_off" value="0" {if !isset($tshirtecommerce_hide_add_to_cart) || $tshirtecommerce_hide_add_to_cart == 0} checked="checked" {/if} />
						<label for="chktshirthideaddtocart_off" class="radioCheck">{l s='No' mod='tshirtecommerce'}</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">{l s='Allow hide Add To Cart button on front-office.' mod='tshirtecommerce'}</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Logo Loading' mod='tshirtecommerce'}</label>
				<div class="col-sm-6">
					<img width="60px" height="60px" src="{$tshirtecommerce_logo_loading}" class="logo-loading-thumb" />
					<br />
					<input type="file" name="file" id="file" accept="image/*" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Text Loading' mod='tshirtecommerce'}</label>
				<div class="col-lg-9">
					<input class="form-control" name="tshirtecommerce_text_loading" value="{$tshirtecommerce_text_loading}" />
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">
						{l s='Allow change text loading when load Designer Tool' mod='tshirtecommerce'}
					</div>
				</div>
			</div>
			<div class="form-group" style="display: none!important;">
				<label class="control-label col-lg-3">{l s="Allow Specific Prices" mod="tshirtecommerce"}</label>
				<div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input class="form-control" id="checktshirtspecificprices_on" type="radio" name="tshirtecommerce_specific_prices" value="1" {if isset($tshirtecommerce_specific_prices) && $tshirtecommerce_specific_prices == 1} checked="checked"{/if} />
						<label for="checktshirtspecificprices_on" class="radioCheck">{l s='Yes' mod='tshirtecommerce'}</label>
						<input class="form-control" id="checktshirtspecificprices_off" type="radio" name="tshirtecommerce_specific_prices" value="0" {if !isset($tshirtecommerce_specific_prices) || $tshirtecommerce_specific_prices == 0} checked="checked"{/if} />
						<label for="checktshirtspecificprices_off" class="radioCheck">{l s='No' mod='tshirtecommerce'}</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">{l s='Allow discount printing price by product specific prices.' mod='tshirtecommerce'}</div>
				</div>
			</div>
			<div class="panel-footer">
				<div class="form-group pull-right">
					<button type="submit" class="btn btn-default"><i class="process-icon-save"></i>{l s='Save' mod='tshirtecommerce'}</button>
				</div>
			</div>
		</form>
	</div>
</div>

{/block}
<!-- end::tshirtecommerce -->