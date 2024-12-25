{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}

{if isset($error_warning) && !empty($error_warning)}
	<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {$error_warning}
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
{/if}


{if isset($msg) && !empty($msg)}
  <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> {$msg}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
{/if}

<div class="panel panel-default" style="padding-left: 0 !important; padding-right: 0 !important;">
  	<div class="panel-body">
      <div class="panel-heading">{l s='T-Shirt eCommerce Import Data' mod='tshirtecommerce'}</div>
  		<div style="padding: 0; margin: 0; height: auto">
  			<p>{l s='This tool allows you to download theme, modules, products and design data to your site.' mod='tshirtecommerce'}</p>

        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
          <h4>
            {l s='Please backup your site before import.' mod='tshirtecommerce'}
            {if $verified_code == 0}
            <br/> Please <a href="{$link_verified_code}">verify purchase code</a> before import data
            {/if}
          </h4>
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>

        <div class="row">
          <div class="col-xs-6 col-sm-4">
            <label>{l s='Import Products' mod='tshirtecommerce'}</label><br/>
            {if $verified_code == 0}
            <a class="btn btn-danger" onclick="return alert('Please verifiy purchased code and continue import data!')" href="javascript:void(0)">{l s='View & Import' mod='tshirtecommerce'}</a>
            {else}
            <a class="btn btn-primary" href="{$link_import_product}">{l s='View & Import' mod='tshirtecommerce'}</a>
            {/if}
            <p class="help-block">{l s='Free import product design to your site.' mod='tshirtecommerce'}</p>
          </div>

          <div class="col-xs-6 col-sm-4">
            <label>{l s='Import Clipart & Design template' mod='tshirtecommerce'}</label><br/>
            {if $verified_code == 0}
              <a class="btn btn-danger" onclick="return alert('Please verifiy purchased code and continue import data!')" href="javascript:void(0)">{l s='View & Import' mod='tshirtecommerce'}</a>
            {else}
              <a class="btn btn-primary" id="btndo2" onclick="return fnimport2()" href="{$link_import_art_design}">{l s='View & Import' mod='tshirtecommerce'}</a>
              <a class="btn btn-primary disabled" id="btndoing2" href="javascript:void(0)" disabled style="display: none">{l s='Processing...' mod='tshirtecommerce'}</a>
            {/if}
            <p class="help-block">{l s='Free import our cliparts and design template to your site.' mod='tshirtecommerce'}</p>
            {if !empty($link_import_art_design_downlnoad)}
            <div class="htlp-btndoing2" style="width: 100%;">
              <div class="import-help">
                <h4>{l s='Step by step import via FTP' mod='tshirtecommerce'}:</h4>
                <ol>
                  <li>{l s='Download file' mod='tshirtecommerce'} <a href="{$link_import_art_design_downlnoad}" target="_blank">store.zip</a></li>
                  <li>{l s='Unzip file store.zip on your computer' mod='tshirtecommerce'}</li>
                  <li>{l s='Upload all files to folder' mod='tshirtecommerce'} <strong>{$ifolder}</strong></li>
                  <li>{l s='Reload this page again' mod='tshirtecommerce'}</li>
                </ol>
              </div>
            </div>
            <script type="text/javascript">
                function fnimport2() {
                  $('#btndo2').css('display', 'none');
                  $('#btndoing2').css('display', 'inline-block');
                }
            </script>
            {/if}
          </div>
        </div>
        </div>

        <div class="row" style="margin-top: 30px;">
          <div class="col-xs-6 col-sm-4">
            <label>{l s='Convert & Import Cliparts' mod='tshirtecommerce'}</label><br/>
            {if $verified_code == 0}
            <a class="btn btn-danger" onclick="return alert('Please verifiy purchased code and continue import data!')" href="javascript:void(0)">{l s='View & Import' mod='tshirtecommerce'}</a>
            {else}
            <a class="btn btn-primary" target="_blank" href="{$link_convert_import_clipart}">{l s='Upload Now' mod='tshirtecommerce'}</a>
            {/if}
            <p class="help-block">{l s='You have library clipart with SVG, PDF, PSD...This tool help you easy and fast convert all your file and automatic import to your site.' mod='tshirtecommerce'}</p>
          </div>

          <div class="col-xs-6 col-sm-4">
            <label>{l s='Convert & Import design template' mod='tshirtecommerce'}</label><br/>
            {if $verified_code == 0}
              <a class="btn btn-danger" onclick="return alert('Please verifiy purchased code and continue import data!')" href="javascript:void(0)">{l s='View & Import' mod='tshirtecommerce'}</a>
            {else}
              <a class="btn btn-primary" target="_blank" href="{$link_convert_import_design}">{l s='Convert Now' mod='tshirtecommerce'}</a>
            {/if}
            <p class="help-block">{l s='You have files design editable in SVG, AI, PSD, EPS. This tool automatic convert your file to design template design tools.' mod='tshirtecommerce'}</p>
          </div>
        </div>
  		</div>
	</div>
</div>
{/block}