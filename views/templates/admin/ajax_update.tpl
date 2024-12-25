<script type="text/javascript" src="{$site_url}/modules/tshirtecommerce/views/js/update.js"></script>
<script type="text/javascript">
  newupktse.url = '{$site_url}/modules/tshirtecommerce/ajax_sync.php';
  $(document).ready(function() {
    newupktse.init();
  });
</script>
<div class="bootstrap">
  <div id="updatermodaltse" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">{l s='Product designer data update' mod='tshirtecommerce'}</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute;right:10px;top:15px;><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body" id="updaterbodytse">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='tshirtecommerce'}</button>
          <button type="button" class="btn btn-primary" onclick="newupktse.update(this)">{l s='Run the updater' mod='tshirtecommerce'}</button>
        </div>
      </div>
    </div>
  </div>
</div>