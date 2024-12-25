{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
		{l s='My account'}
	</a>
	<span class="navigation-pipe">{$navigationPipe}</span>
	<span class="navigation_page">{l s='My Design'}</span>
{/capture}
{include file="$tpl_dir./errors.tpl"}

<link rel="stylesheet" type="text/css" href="{$site_url}/tshirtecommerce/prestashop/css/mydesign.css">
<script type="text/javascript">
	var mydesign_ajax_link = '{$mydesign_ajax_link}';
	var tshirtecommerce_design_confirm_delete = '{l s="Do you want to delete this design?"}';
	var mydesign_page = 2;
</script>
<script type="text/javascript" src="{$site_url}/tshirtecommerce/prestashop/js/mydesign.js"></script>
<h1 class="page-heading bottom-indent">{l s='My Design'}</h1>
<p class="info-title">{l s='Here are the designs you\'ve created with custom product tool.'}</p>

<div class="tshirtecommerce-loading"></div>

<p><a target="_blank" href="{$design_default_link}" class="mbtn mbtn-primary">{l s="Create Design"}</a></p>
<div class="block-center" id="block-mydesign">
	<div id="tshirtecommercemydesign" class="tshirtecommercemydesign">
		{$designs.html|unescape:'allhtml'}
	</div>
	{if $designs.continue == 1}
	<p id="mydesign_continue" style="text-align: center;width: 100%">
		<a href="javascript:void(0)" class="mbtn tshirtecommerce-design-loadmore" onclick="fnmydesignmore(this)">{l s="Load More"}</a>
	</p>
	{/if}
	</div>
</div>