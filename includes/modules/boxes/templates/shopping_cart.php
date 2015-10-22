<div class="panel panel-default bg-gray">
<div class="panel-heading cartbox"><a href="<?php echo tep_href_link('shopping_cart.php'); ?>"><?php echo '<span style="font-size: 19px;"><i class="fa fa-shopping-cart"></i></span>   '.MODULE_BOXES_SHOPPING_CART_BOX_TITLE; ?></a></div>
  <div class="panel-body">
    <div class="shoppingCartList">
      <?php echo $cart_contents_string; ?>
    </div>
  </div>
</div>
