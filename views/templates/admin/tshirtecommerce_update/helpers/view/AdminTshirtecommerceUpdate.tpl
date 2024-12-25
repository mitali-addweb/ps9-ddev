<!-- start::tshirtecommerce -->
{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}

{if isset($error_info) && !empty($error_info)}
	<div class="bootstrap alert alert-info"><i class="fa fa-exclamation-circle"></i> {$error_info}
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
{/if}
{if isset($error_warning) && !empty($error_warning)}
	<div class="bootstrap alert alert-danger"><i class="fa fa-exclamation-circle"></i> {$error_warning}
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
{/if}

{if (!isset($verify) || $verify == 1)}
	<div class="bootstrap alert alert-danger"><i class="fa fa-exclamation-circle"></i> {l s='Please' mod='tshirtecommerce'} <a href="{$link_setting}">{l s='verify purchased code' mod='tshirtecommerce'}</a> {l s='before update module.' mod='tshirtecommerce'}
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
{/if}

{if (count($versions) > 0)}
	{foreach from=$versions item=value}
		<div class="bootstrap panel panel-default">
			<div class="panel-body">		
				<div class="panel-heading">
					<h3 style="border-bottom:none!important"><span>Version {$value->version} <small>{$value->date}</small></span>
						{if (isset($verify) && $verify == 0)}
						<span class="pull-right" style="line-height:1.1!important; padding-right:15px;">
							<form action="index.php?controller=AdminTshirtecommerceUpdate&token={$token}" method="post" id="form-attribute">
								<button type="submit" class="btn btn-success btn-sm">{l s='Update' mod='tshirtecommerce'}</button>
								 <a target="_blank" href="http://updates.tshirtecommerce.com/api.php?code={$code}&version={$value->d_version}&platform=prestashop&prestashop={$ps_version}" class="btn btn-default btn-sm" style="top:0!important;">{l s='Download' mod='tshirtecommerce'}</a>
								<input type="hidden" name="update" value="{$value->file}" />
							</form>
						</span>
						{/if}
					</h3>
					
				</div>
				<div class="panel-body">
					 {$value->content}
				</div>
			</div>
		</div>
	{/foreach}
{else}
	<center>{l s='No new release found.' mod='tshirtecommerce'}</center>
{/if}

{/block}
<!-- end::tshirtecommerce -->