<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{l s="T-Shirt eCommerce Settings Wizard" mod="Tshirtecommerce"}</title>

	<link rel="stylesheet" href="{$site_url}/tshirtecommerce/prestashop/quicksetup/css/bootstrap.min.css" type="text/css" media="all">
	<link rel="stylesheet" href="{$site_url}/tshirtecommerce/prestashop/quicksetup/css/setup.css" type="text/css" media="all">
	<script type="text/javascript" src="{$site_url}/tshirtecommerce/prestashop/quicksetup/js/jquery.min.js"></script>
	<script type="text/javascript" src="{$site_url}/tshirtecommerce/prestashop/quicksetup/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="{$site_url}/tshirtecommerce/prestashop/quicksetup/js/quicksetup.js"></script>
	<script type="text/javascript">
		var quicksetup_url = '{$site_url}';
	</script>
</head>
<body>
	{if isset($error) && !empty($error)}
		<div class="alert alert-danger" role="alert">{$error}</div>
	{else}
		<h1 class="logo text-center">
			<a href="http://tshirtecommerce.com/" target="_blank">
				<img src="https://tshirtecommerce.com/wp-content/uploads/2015/09/small-logo1.png" alt="tshirtecommerce.com">
			</a>
		</h1>
		<div class="e-steps">
			<div class="e-steps-label">
				<div class="e-step-1 e-step e-step-active" id="quicksetup1">
					{l s="Design Tool" mod="Tshirtecommerce"} <span>1</span>
				</div>
				<div class="e-step-2 e-step" id="quicksetup2">
					{l s="Cliparts Store" mod="Tshirtecommerce"} <span>2</span>
				</div>
				<div class="e-step-3 e-step" id="quicksetup3">
					{l s="Import Products" mod="Tshirtecommerce"} <span>3</span>
				</div>
				<div class="e-step-4 e-step" id="quicksetup4">
					{l s="Finished!" mod="Tshirtecommerce"} <span>4</span>
				</div>
			</div>
			
			<div class="progress">
			  <div id="progress-bar-step" class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%">
					<span class="sr-only">25% {l s=" Complete (success)" mod="Tshirtecommerce"}</span>
			  </div>
			</div>
		</div>

		<div id="step-quicksetup1" class="page-content" style="display: block;">
			<h2 class="title">{l s="Setting Design Tool" mod="Tshirtecommerce"}</h2>
			<p>{l s="Thank you for choosing T-Shirt eCommerce. This quick setup wizard will help you configure the basic settings. It's completely optional and shouldn't take longer than five minutes." mod="Tshirtecommerce"}</p>
			<hr>
			<form class="form-horizontal" method="post" action="{$add_new_product_link}">
				<div class="form-group">
					<label class="col-xs-5">{l s="Layout of design tool" mod="Tshirtecommerce"}</label>
					<div class="col-xs-7">
						<select class="form-control input-sm" name="layout">
							{foreach from=$themes item=theme}
								{if $theme.theme == 'default'}
									<option value="{$theme.name}" selected="selected">{$theme.title}</option>
								{else}
									<option value="{$theme.name}">{$theme.title}</option>
								{/if}
							{/foreach}
						</select>		
					</div>
					<div class="col-xs-ofsset-5 col-xs-7">
						<div class="center-block text-muted">{l s="You can" mod="Tshirtecommerce"} <a style="color:#0049b0;text-decoration:underline;" href="http://docs.tshirtecommerce.com/knowledgebase/customize-layout-with-color-background-of-design-tool/" target="_blank">{l s="add new or customize layout" mod="Tshirtecommerce"}</a></div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-5">{l s="Your language" mod="Tshirtecommerce"}</label>
					<div class="col-xs-7">
						<select class="form-control input-sm" name="language">
							{foreach from=$tlanguages item=tlanguage}
								{if $tlanguage['code'] == 'en'}
									<option value="{$tlanguage['code']}" selected="selected">{$tlanguage['title']}</option>
								{else}
									<option value="{$tlanguage['code']}">{$tlanguage['title']}</option>
								{/if}
							{/foreach}
						</select>		
					</div>
					<div class="col-xs-ofsset-5 col-xs-7">
						<div class="center-block text-muted">{l s="Choose your language display in page design tool or" mod="Tshirtecommerce"} <a style="color:#0049b0;text-decoration:underline;" href="http://docs.tshirtecommerce.com/knowledgebase/languages-prestashop/" target="_blank">{l s="add new language" mod="Tshirtecommerce"}</a>.</div>
					</div>
				</div>
				
				<hr>
				<p class="text-right">
					<a class="btn btn-default pull-left" href="http://docs.tshirtecommerce.com/kb/prestashop-custom-product-designer/" target="_blank">{l s="Document Online" mod="Tshirtecommerce"}</a>
					<a href="javascript:void(0)" class="btn btn-success" onclick="quicksetup.submit('quicksetup2')">{l s="Save" d="Modules.Tshirtecommerce"} &amp; {l s="Continue" mod="Tshirtecommerce"}</a>
				</p>
			</form>
		</div>
		<div id="step-quicksetup2" class="page-content" style="display: none;">
			<h2 class="title">{l s="Cliparts and Design Template" mod="Tshirtecommerce"}</h2>
			<p>{l s="We give to you librarie clipart and design template. You can active" mod="Tshirtecommerce"} <a href="http://9file.net/">T-Shirt eCommerce Store</a> {l s="to use design from our community." mod="Tshirtecommerce"}</p>
			<hr>
			<form class="form-horizontal" method="post" action="">
				<p>
					1. {l s="Create account on website " mod="Tshirtecommerce"} <a style="color:#0049b0;text-decoration:underline;" href="http://store.9file.net/users/register" title="" target="_blank">http://store.9file.net</a>
				</p>
				<div class="form-group" style="margin-bottom: 0px;">
					<label class="col-xs-7">2. {l s="Enter your API" mod="Tshirtecommerce"}</label>
					<div class="col-xs-7">
						<input class="form-control input-sm" type="text" id="store-api" name="store_api" value="e1a9fa98e1b064bb78cfdbdd5866f531" />
					</div>
				</div>
				<p><small>{l s="Video create account and get your API" mod="Tshirtecommerce"} <a style="color:#0049b0;text-decoration:underline;" href="https://youtu.be/cPvRpXdVe0s?t=23s" target="_blank">https://youtu.be/cPvRpXdVe0s</a></small></p>
				<p>3. {l s="Click button" mod="Tshirtecommerce"} "<strong>Active &amp; Download Design</strong>"</p>
				<div id="download-store2" class="download-store">
					<p class="help-block">{l s="Status" mod="Tshirtecommerce"}: <strong id="text-status2" class="text-danger text-status">{l s="Processing..." mod="Tshirtecommerce"}</strong></p>
					<div class="progress">
					  	<div id="progress-bar2" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
							<span class="sr-only">1% {l s="Complete (success)" mod="Tshirtecommerce"}</span>
					  	</div>
					</div>
					<p id="text-danger2" class="text-danger">{l s="Please don't navigate away while importing" mod="Tshirtecommerce"}</p>
				</div>
				<p class="text-right">
					<a class="btn btn-default pull-left" href="javascript:void(0)" onclick="quicksetup.back('quicksetup1', 'quicksetup2', 25)">{l s="Back" mod="Tshirtecommerce"}</a>
					<a class="btn btn-default" href="javascript:void(0)" onclick="quicksetup.skip('quicksetup3', 'quicksetup2', 75)">{l s="Skip this step" mod="Tshirtecommerce"}</a>
					<a onclick="quicksetup.download(this)" href="javascript:void(0)" class="btn btn-success ">Active &amp; Download Design</a>
				</p>
			</form>
		</div>
		<div id="step-quicksetup3" class="page-content" style="display: none;">
			<h2 class="title">{l s="Import Product Demo" mod="Tshirtecommerce"}</h2>
			<p>{l s="Please check button \"Import Products\" to automatic import products demo." mod="Tshirtecommerce"}</p>

			<div id="download-store3" class="download-store" style="margin-top:2rem; margin-bottom:2rem;width: 100%;min-height: 50px;">
				<p class="help-block">{l s="Status" mod="Tshirtecommerce"}: <strong id="text-status3" class="text-danger text-status">{l s="Downloading data..." mod="Tshirtecommerce"}</strong></p>
				<div class="progress">
				  	<div id="progress-bar3" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
						<span class="sr-only">1% {l s="Complete (success)" mod="Tshirtecommerce"}</span>
				  	</div>
				</div>
				<p class="text-danger" id="text-danger3">{l s="Please don't navigate away while importing" mod="Tshirtecommerce"}</p>
			</div>

			<p class="text-right">
				<a class="btn btn-default pull-left" href="javascript:void(0)" onclick="quicksetup.back('quicksetup2', 'quicksetup3', 50)">{l s="Back" mod="Tshirtecommerce"}</a>
				<a class="btn btn-default" href="javascript:void(0)" onclick="quicksetup.skip('quicksetup4', 'quicksetup3', 100)">{l s="Skip this step" mod="Tshirtecommerce"}</a>
				<button type="button" onclick="quicksetup.import()" class="btn btn-success ">{l s="Import Products" mod="Tshirtecommerce"}</button>
			</p>
		</div>
		<div id="step-quicksetup4" class="page-content" style="display: none;">
			<div class="page-content">
				<h2 class="title">{l s="Finished!" mod="Tshirtecommerce"}</h2>
				<p class="des-addon">{l s="Congratulations! You have successfully installed plugin." d="Modules.Tshirtecommerce"}</p>
				
				<hr>
				
				<div class="alert alert-info">
					{l s="We provide some useful addon for this plugin that add many functions awesome on your site." mod="Tshirtecommerce"}
					<br>
					<br>
					<a class="btn btn-default btn-sm" target="_blank" href="https://tshirtecommerce.com/add-ons">{l s="View Addons" mod="Tshirtecommerce"}</a>
				</div>
				<div class="form-horizontal">
					<div class="form-group">
						<div class="col-xs-6">
							<p><strong>{l s="Help &amp; Support" mod="Tshirtecommerce"}</strong></p>
							<ul>
								<li>{l s="Please read" mod="Tshirtecommerce"} <a target="_blank" href="http://docs.tshirtecommerce.com/kb/prestashop-custom-product-designer/">{l s="documentation" d="Modules.Tshirtecommerce"}</a> {l s="of plugin" mod="Tshirtecommerce"}</li>
								<li>{l s="If you need help, please" mod="Tshirtecommerce"} <a target="_blank" href="https://tshirtecommerce.com/submit-ticket">{l s="submit a ticket" mod="Tshirtecommerce"}</a></li>
							</ul>
							<div class="fb-share-button fb_iframe_widget" data-href="https://tshirtecommerce.com" data-layout="button" data-size="small" data-mobile-iframe="true" fb-xfbml-state="rendered" fb-iframe-plugin-query="app_id=468877319808630&amp;container_width=319&amp;href=https%3A%2F%2Ftshirtecommerce.com%2F&amp;layout=button&amp;locale=en_US&amp;mobile_iframe=true&amp;sdk=joey&amp;size=small"><span style="vertical-align: bottom; width: 58px; height: 20px;"><iframe name="f12abf165972f38" width="1000px" height="1000px" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" title="fb:share_button Facebook Social Plugin" src="https://www.facebook.com/v2.8/plugins/share_button.php?app_id=468877319808630&amp;channel=http%3A%2F%2Fstaticxx.facebook.com%2Fconnect%2Fxd_arbiter%2Fr%2F87XNE1PC38r.js%3Fversion%3D42%23cb%3Df15dd7ce8f3109c%26domain%3Dlocalhost%26origin%3Dhttp%253A%252F%252Flocalhost%252Ff33943908873c%26relation%3Dparent.parent&amp;container_width=319&amp;href=https%3A%2F%2Ftshirtecommerce.com%2F&amp;layout=button&amp;locale=en_US&amp;mobile_iframe=true&amp;sdk=joey&amp;size=small" style="border: none; visibility: visible; width: 58px; height: 20px;" class=""></iframe></span></div>
							<iframe id="twitter-widget-0" scrolling="no" frameborder="0" allowtransparency="true" class="twitter-share-button twitter-share-button-rendered twitter-tweet-button" title="Twitter Tweet Button" src="http://platform.twitter.com/widgets/tweet_button.5b6375bb17bd9edb2f4e7f8f12971999.en.html#dnt=false&amp;id=twitter-widget-0&amp;lang=en&amp;original_referer=http%3A%2F%2Flocalhost%2Fwp2%2Fwp-admin%2Findex.php%3Fpage%3Dtshirtecommerce-setting%26step%3Dcomplete&amp;size=m&amp;text=complete%20business%20solution%20for%20selling%20custom%20printing%20products&amp;time=1493810038693&amp;type=share&amp;url=https%3A%2F%2Ftshirtecommerce.com%2F" style="position: static; visibility: visible; width: 60px; height: 20px;" data-url="https://tshirtecommerce.com/"></iframe><script async="" src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
						</div>
						
						<div class="col-xs-6">
							<p><strong>{l s="Popular articles" mod="Tshirtecommerce"}</strong></p>
							<ul>
								<li><a target="_blank" href="http://docs.tshirtecommerce.com/kb/prestashop-custom-product-designer/">{l s="Setting Module" mod="Tshirtecommerce"}</a></li>
								<li><a target="_blank" href="http://docs.tshirtecommerce.com/knowledgebase/add-new-printing-type-prestashop/">{l s="Prices and printing method" mod="Tshirtecommerce"}</a></li>
								<li><a target="_blank" href="http://docs.tshirtecommerce.com/knowledgebase/add-new-product-design-prestashop/">{l s="Add new product design" mod="Tshirtecommerce"}</a></li>
							</ul>
						</div>
					</div>
				</div>
				
				<hr>
				<p class="text-right">
					<a class="btn btn-default btn-success" href="{$designer_tool_link}" role="button">
						{l s="View Design Tool" mod="Tshirtecommerce"}</a>
				</p>
			</div>
		</div>
	{/if}
</body>
</html>