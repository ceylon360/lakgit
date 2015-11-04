<div id="upCart" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo MODULE_CONTENT_FOOTER_MODAL_CART_HEADING_TITLE; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo MODULE_CONTENT_FOOTER_MODAL_CART_TEXT; ?>
        <?php echo $cart_contents_string; ?>
        <p style="text-align: right; padding: 1px;"><strong><?php echo MODULE_CONTENT_FOOTER_MODAL_CART_TOTAL . $currencies->format($cart->show_total()); ?></strong></p>
      <div class="modal-footer">
	  <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-plus"></i>Continue Shopping</span></button>
      <?php echo  tep_draw_button(HEADER_TITLE_CHECKOUT, 'fa fa-chevron-right', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'),null,null,'btn-success'); ?>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
