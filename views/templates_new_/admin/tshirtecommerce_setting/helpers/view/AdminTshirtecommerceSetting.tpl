{*
* @module Prestashop Custom Product Designer
*
* @author 		tshirtecommerce - https://tshirtecommerce.com/
* @date 		May 2017
* 
* API 			1.0.4
* 
* @copyright  	Copyright (C) 2016 tshirtecommerce.com. All rights reserved.
* @license    	GNU General Public License version 2 or later; see LICENSE
*
* @since 		1.5
*
*}

{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}

{if (isset($error_warning) && !empty($error_warning))}
	{if !isset($psversion) || $psversion == 1}
	<div class="bottstrap">
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {$error_warning}
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	</div>
	{else}
	<div class="warn">
		<span style="float:right"><a id="hideWarn" href=""><img alt="X" src="../img/admin/close.png"></a></span>
		<ul style="margin-top: 3px"><li>{$error_warning}</li></ul>
	</div>
	{/if}
{/if}

{if (isset($msg) && $msg == 'success')}
	{if !isset($psversion) || $psversion == 1}
	<div class="bootstrap">
		<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> {l s='Setting up success' mod='tshirtecommerce'}
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	</div>
	{else}
	<div class="info">
		<span style="float:right"><a id="hideWarn" href=""><img alt="X" src="../img/admin/close.png"></a></span>
		<ul style="margin-top: 3px"><li> {l s='Setting up success' mod='tshirtecommerce'} </li></ul>
	</div>
	{/if}
{/if}

{if (isset($msg) && $msg == 'failed')}
	{if !isset($psversion) || $psversion == 1}
	<div class="bootstrap">
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {l s='Setting up failed' mod='tshirtecommerce'}
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	</div>
	{else}
	<div class="info">
		<span style="float:right"><a id="hideWarn" href=""><img alt="X" src="../img/admin/close.png"></a></span>
		<ul style="margin-top: 3px"><li> {l s='Setting up failed' mod='tshirtecommerce'} </li></ul>
	</div>
	{/if}
{/if}

<div class="bootstrap panel panel-default">
  	<div class="panel-body">
  		{if !isset($psversion) || $psversion == 1}
  		<div class="panel-heading">
  			{l s='T-Shirt eCommerce Settings' mod='tshirtecommerce'}
  			- <a class="btn btn-success btn-sm" target="_blank" href="{$tshirtecommerce_quicksetup_link}">Quick Setup</a>
  		</div>
  		<div class="alert alert-info">{l s='Purchase code must be verify in updating T-Shirt eCommerce module.' mod='tshirtecommerce'}</div>
  		{else}
  		<div class="hint" style="display:block;">{l s='Purchase code must be verify in updating T-Shirt eCommerce module.' mod='tshirtecommerce'}</div>
  		<br /><br />
  		{/if}
  		<form id="tshirt-settings-form" method="POST" action="{$action}{$token}" class="form-horizontal" enctype="multipart/form-data">
  			{if isset($psversion) && $psversion == 0}
  			<fieldset>
  			<legend>{l s='T-Shirt eCommerce Settings' mod='tshirtecommerce'}</legend>
  			<div class="form-group">
  				<div class="margin-form">
  					<a target="_blank" href="{$tshirtecommerce_quicksetup_link}" style="color:#5cb85c;text-decoration: underline; text-transform: uppercase;"><strong>Quick Setup</strong></a>
  				</div>
  			</div>
  			{/if}
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label{/if} col-lg-3">{l s='Product default' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-9{else}margin-form{/if}">
					{if (count($products) > 0)}
					<select {if !isset($psversion) || $psversion == 1}class="form-control"{/if} name="tshirtecommerce_product_default" {if isset($psversion) && $psversion == 0}style="min-width:30%"{/if}>
						{foreach from=$products item=product}
					  	<option value="{$product.id}" {if ($product.id==$setting_product)} selected="true" {/if}>{$product.name}</option>
					  	{/foreach}
					</select>
					{else}
						<select {if !isset($psversion) || $psversion == 1}class="form-control"{/if} name="tshirtecommerce_product_default" {if isset($psversion) && $psversion == 0}style="min-width:30%"{/if}><option></option></select>
					{/if}
					{if isset($psversion) && $psversion == 0}
						<p class="preference_description" style="width: 100%">
							{l s='Choose product default. It is very important. This product will show in designer.' mod='tshirtecommerce'}
							<br />
							{l s='Default link' mod='tshirtecommerce'} : <a title="design-your-own" href="{$link_design_your_own}" target="_blank">{$link_design_your_own}</a>. {l s='You can use this link for creating Design-Your-Own menu on your site.' mod='tshirtecommerce'}
						</p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block">
						{l s='Choose product default. It is very important. This product will show in designer.' mod='tshirtecommerce'}
						<br />
						{l s='Default link' mod='tshirtecommerce'} : <a title="design-your-own" href="{$link_design_your_own}" target="_blank">{$link_design_your_own}</a>. {l s='You can use this link for creating Design-Your-Own menu on your site.' mod='tshirtecommerce'}
					</div>
				</div>
				{/if}
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Allow download design' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-9{else}margin-form{/if}">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="tshirtecommerce_downloadbale" id="chktshirtdownloadable_on" value="1" {if isset($setting_downloadable) && $setting_downloadable == 1} checked="checked" {/if} />
						<label for="chktshirtdownloadable_on" class="radioCheck">{l s='Yes' mod='tshirtecommerce'}</label>
						<input type="radio" name="tshirtecommerce_downloadbale" id="chktshirtdownloadable_off" value="0" {if !isset($setting_downloadable) || $setting_downloadable == 0} checked="checked" {/if} />
						<label for="chktshirtdownloadable_off" class="radioCheck">{l s='No' mod='tshirtecommerce'}</label>
						<a class="slide-button btn"></a>
					</span>
					{if isset($psversion) && $psversion == 0}
						<p class="preference_description" style="width: 100%">{l s='Allow clients to download design on order history.' mod='tshirtecommerce'}</p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block">{l s='Allow clients to download design on order history.' mod='tshirtecommerce'}</div>
				</div>
				{/if}
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Position Custom Design Button' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-5{else}margin-form{/if}">
					<div class="radio">
						<label for="tshirtecommerce_position_custom_design_btn_1" {if isset($psversion) && $psversion == 0}style="width:auto;padding-left:0;"{/if}>
							<input type="radio" name="tshirtecommerce_position_custom_design_btn" id="tshirtecommerce_position_custom_design_btn_1" value="1" {if $tshirtecommerce_position_custom_design_btn == 1}checked{/if} />
							{l s='Display on right column' mod='tshirtecommerce'}
						</label>
					</div>
					<div class="radio">
						<label for="tshirtecommerce_position_custom_design_btn_0" {if isset($psversion) && $psversion == 0}style="width:auto;padding-left:0;"{/if}>
							<input type="radio" name="tshirtecommerce_position_custom_design_btn" id="tshirtecommerce_position_custom_design_btn_0" value="0" {if $tshirtecommerce_position_custom_design_btn == 0}checked{/if} />
							{l s='Display on left column' mod='tshirtecommerce'}
						</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Purchase Code' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-5{else}margin-form{/if}">
					<input style="font-weight:bold!important;{if isset($psversion) && $psversion == 0}min-width:60%{/if}" type="text" name="tshirtecommerce_purchase_code" value="{$purchase_code}" />
					{if isset($psversion) && $psversion == 0}
						{if $verified == 0}
						<span class="label label-success">{l s='VERIFIED' mod='tshirtecommerce'}</span>
						{else}
						<span class="label label-danger">{l s='NOT VERIFIED' mod='tshirtecommerce'}</span>
						{/if}
						<p class="preference_description" style="width: 100%"><a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">{l s='Where is my Purchase Code?' mod='tshirtecommerce'}</a></p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-4"{/if}>
					{if $verified == 0}
					<span class="label label-success">{l s='VERIFIED' mod='tshirtecommerce'}</span>
					{else}
					<span class="label label-danger">{l s='NOT VERIFIED' mod='tshirtecommerce'}</span>
					{/if}
				</div>
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block"><a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">{l s='Where is my Purchase Code?' mod='tshirtecommerce'}</a></div>
				</div>
				{/if}
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Custom Text Button' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-9{else}margin-form{/if}">
					<input type="text" {if !isset($psversion) || $psversion == 1}class="form-control"{/if} name="tshirtecommerce_custom_text" value="{$tshirtecommerce_custom_text}" {if isset($psversion) && $psversion == 0}style="min-width:80%"{/if} />
					{if isset($psversion) && $psversion == 0}
					<p class="preference_description" style="width: 100%">{l s='Allow change text of Custom Your Design Button' mod='tshirtecommerce'}</p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block">{l s='Allow change text of Custom Your Design Button' mod='tshirtecommerce'}</div>
				</div>
				{/if}
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Custom Style Button' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-9{else}margin-form{/if}">
					<input {if !isset($psversion) || $psversion == 1}class="form-control"{/if} name="tshirtecommerce_custom_style" value="{$tshirtecommerce_custom_style}" {if isset($psversion) && $psversion == 0}style="min-width:80%"{/if} />
					{if isset($psversion) && $psversion == 0}
					<p class="preference_description" style="width: 100%">
						{l s='Please enter CSS class name (e.g. "btn-group", "btn button-custom") or CSS style code (e.g. "padding:6px 8px", "padding 6px 8px;color: #fff;" )'}
						<br />
						{l s='Allow change style of Custom Your Design Button' mod='tshirtecommerce'}
					</p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block">
						{l s='Please enter CSS class name (e.g. "btn-group", "btn button-custom") or CSS style code (e.g. "padding:6px 8px", "padding 6px 8px;color: #fff;" )'}
						<br />
						{l s='Allow change style of Custom Your Design Button' mod='tshirtecommerce'}
					</div>
				</div>
				{/if}
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Hide Add To Cart' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-9{else}margin-form{/if}">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="tshirtecommerce_hide_add_to_cart" id="chktshirthideaddtocart_on" value="1" {if isset($tshirtecommerce_hide_add_to_cart) && $tshirtecommerce_hide_add_to_cart == 1} checked="checked" {/if} />
						<label for="chktshirthideaddtocart_on" class="radioCheck">{l s='Yes' mod='tshirtecommerce'}</label>
						<input type="radio" name="tshirtecommerce_hide_add_to_cart" id="chktshirthideaddtocart_off" value="0" {if !isset($tshirtecommerce_hide_add_to_cart) || $tshirtecommerce_hide_add_to_cart == 0} checked="checked" {/if} />
						<label for="chktshirthideaddtocart_off" class="radioCheck">{l s='No' mod='tshirtecommerce'}</label>
						<a class="slide-button btn"></a>
					</span>
					{if isset($psversion) && $psversion == 0}
					<p class="preference_description" style="width: 100%">{l s='Allow hide Add To Cart button on front-office.' mod='tshirtecommerce'}</p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block">{l s='Allow hide Add To Cart button on front-office.' mod='tshirtecommerce'}</div>
				</div>
				{/if}
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Logo Loading' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-sm-6{else}margin-form{/if}">
					<img width="60px" height="60px" src="{$tshirtecommerce_logo_loading}" class="logo-loading-thumb" />
					<br />
					<input type="file" name="file" id="file" accept="image/*" />
				</div>
			</div>
			<div class="form-group">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s='Text Loading' mod='tshirtecommerce'}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-9{else}margin-form{/if}">
					<input {if !isset($psversion) || $psversion == 1}class="form-control"{/if} name="tshirtecommerce_text_loading" value="{$tshirtecommerce_text_loading}" {if isset($psversion) && $psversion == 0}style="min-width:80%"{/if} />
					{if isset($psversion) && $psversion == 0}
					<p class="preference_description" style="width: 100%">
						{l s='Allow change text loading when load Designer Tool' mod='tshirtecommerce'}
					</p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block">
						{l s='Allow change text loading when load Designer Tool' mod='tshirtecommerce'}
					</div>
				</div>
				{/if}
			</div>
			<div class="form-group" style="display: none!important;">
				<label {if !isset($psversion) || $psversion == 1}class="control-label col-lg-3"{/if}>{l s="Allow Specific Prices" mod="tshirtecommerce"}</label>
				<div class="{if !isset($psversion) || $psversion == 1}col-lg-9{else}margin-form{/if}">
					<span class="switch prestashop-switch fixed-width-lg">
						<input {if !isset($psversion) || $psversion == 1}class="form-control"{/if} id="checktshirtspecificprices_on" type="radio" name="tshirtecommerce_specific_prices" value="1" {if isset($tshirtecommerce_specific_prices) && $tshirtecommerce_specific_prices == 1} checked="checked"{/if} />
						<label for="checktshirtspecificprices_on" class="radioCheck">{l s='Yes' mod='tshirtecommerce'}</label>
						<input {if !isset($psversion) || $psversion == 1}class="form-control"{/if} id="checktshirtspecificprices_off" type="radio" name="tshirtecommerce_specific_prices" value="0" {if !isset($tshirtecommerce_specific_prices) || $tshirtecommerce_specific_prices == 0} checked="checked"{/if} />
						<label for="checktshirtspecificprices_off" class="radioCheck">{l s='No' mod='tshirtecommerce'}</label>
						<a class="slide-button btn"></a>
					</span>
					{if isset($psversion) && $psversion == 0}
					<p class="preference_description" style="width: 100%">{l s='Allow discount printing price by product specific prices.' mod='tshirtecommerce'}</p>
					{/if}
				</div>
				{if !isset($psversion) || $psversion == 1}
				<div {if !isset($psversion) || $psversion == 1}class="col-lg-9 col-lg-offset-3"{/if}>
					<div class="help-block">{l s='Allow discount printing price by product specific prices.' mod='tshirtecommerce'}</div>
				</div>
				{/if}
			</div>
			{if isset($psversion) && $psversion == 0}
			<div class="separation"></div>
			{/if}
			<div class="{if !isset($psversion) || $psversion == 1}panel-footer{else}margin-form{/if}">
				<div class="{if !isset($psversion) || $psversion == 1}form-group pull-right{/if}">
					<button type="submit" class="btn btn-default {if isset($psversion) && $psversion == 0}button{/if}" {if isset($psversion) && $psversion == 0}style="padding: 6px 12px;"{/if}><i class="process-icon-save"></i>{l s='Save' mod='tshirtecommerce'}</button>
				</div>
			</div>
			{if isset($psversion) && $psversion == 0}
  			</fieldset>
  			{/if}
		</form>
	</div>
</div>

{/block}