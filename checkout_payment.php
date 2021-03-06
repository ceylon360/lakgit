<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// Stock Check
  if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $products = $cart->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (tep_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        break;
      }
    }
  }

// if no billing destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('billto')) {
    tep_session_register('billto');
    $billto = $customer_default_address_id;
  } else {
// verify the selected billing address
    if ( (is_array($billto) && empty($billto)) || is_numeric($billto) ) {
      $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$billto . "'");
      $check_address = tep_db_fetch_array($check_address_query);

      if ($check_address['total'] != '1') {
        $billto = $customer_default_address_id;
        if (tep_session_is_registered('payment')) tep_session_unregister('payment');
      }
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  if (!tep_session_is_registered('comments')) tep_session_register('comments');
  if (isset($HTTP_POST_VARS['comments']) && tep_not_null($HTTP_POST_VARS['comments'])) {
    $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);
  }

// ship date
  if (!tep_session_is_registered('delivery_date')) tep_session_register('delivery_date');
  if (isset($HTTP_POST_VARS['delivery_date']) && tep_not_null($HTTP_POST_VARS['delivery_date'])) {
    $delivery_date = tep_db_prepare_input($HTTP_POST_VARS['delivery_date']);
  }
  // eof ship date
// suprise gift
if (!tep_session_is_registered('surprise')) tep_session_register('surprise');
if (isset($HTTP_POST_VARS['surprise']) && tep_not_null($HTTP_POST_VARS['surprise'])) {
    $surprise = tep_db_prepare_input($HTTP_POST_VARS['surprise']);
}
// eof suprise gift
// Sender Anonymous
if (!tep_session_is_registered('anonymous')) tep_session_register('anonymous');
if (isset($HTTP_POST_VARS['anonymous']) && tep_not_null($HTTP_POST_VARS['anonymous'])) {
    $anonymous = tep_db_prepare_input($HTTP_POST_VARS['anonymous']);
}
// eof Sender Anonymous

  $total_weight = $cart->show_weight();
  $total_count = $cart->count_contents();

// load all enabled payment modules
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PAYMENT);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<?php echo $payment_modules->javascript_validation(); ?>
<div class="card card-green animated fadeInRight" >
<!--  step -->
    <div class="row shop-tracking-status">
		<div class="order-status">
			
			<div class="order-status-timeline">
				<!-- class names: c0 c1 c2 c3 and c4 -->
				<div class="order-status-timeline-completion c2_2"></div>
			</div>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-new active img-circle">
                    <div class="icon fa fa-truck fa-flip-horizontal fa-2x"></div>
                </div>
			</a>
			
                <div class="image-order-status image-order-status-active active img-circle">
                     <div class="icon fa fa-calendar fa-2x"></div>
                </div>
			
			<div class="image-order-status image-order-status-completed img-circle">
				<div class="icon fa fa-thumbs-up fa-2x"></div>
			</div>
			
		</div>
	</div>
	<!-- end step -->
<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('checkout_payment', tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'class="form-horizontal" onsubmit="return check_form();"', true); ?>

<div class="contentContainer">

<?php
  if (isset($HTTP_GET_VARS['payment_error']) && is_object(${$HTTP_GET_VARS['payment_error']}) && ($error = ${$HTTP_GET_VARS['payment_error']}->get_error())) {
?>

  <div class="contentText">
    <?php echo '<strong>' . tep_output_string_protected($error['title']) . '</strong>'; ?>

    <p class="messageStackError"><?php echo tep_output_string_protected($error['error']); ?></p>
  </div>

<?php
  }
?>

  <h2><?php// echo TABLE_HEADING_BILLING_ADDRESS; ?></h2>

  <div class="contentText row hide">
    <div class="col-sm-12">
      <div class="notice notice-info">
        <?php echo TEXT_PAYMENT_RULES; ?>
        <div class="clearfix"></div>
        <div class="pull-right">
          <?php// echo tep_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'glyphicon glyphicon-home', tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL')); ?>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
 <!--   <div class="col-sm-4">
      <div class="panel panel-primary">
        <div class="panel-heading"><?php //echo TITLE_BILLING_ADDRESS; ?></div>
        <div class="panel-body">
          <?php// echo tep_address_label($customer_id, $billto, true, ' ', '<br />'); ?>
        </div>
      </div>
    </div> -->
  </div>

  <div class="clearfix"></div>

  <h4><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></h4>

<?php
  $selection = $payment_modules->selection();

  if (sizeof($selection) > 1) {
?>

  <div class="contentText">
    <div class="alert alert-warning">
      <div class="row">
        <div class="col-xs-8">
          <?php echo TEXT_SELECT_PAYMENT_METHOD; ?>
        </div>
        <div class="col-xs-4 text-right">
          <?php echo '<strong>' . TITLE_PLEASE_SELECT . '</strong>'; ?>
        </div>
      </div>
    </div>
  </div>


<?php
    } else {
?>

  <div class="contentText">
    <div class="alert alert-info"><?php echo TEXT_ENTER_PAYMENT_INFORMATION; ?></div>
  </div>

<?php
    }
?>

  <div class="contentText">

    <table class="table table-striped table-condensed table-hover">
      <tbody>
<?php
  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
?>
      <tr class="table-selection">
        <td><strong><?php echo $selection[$i]['module']; ?></strong></td>
        <td align="right">

<?php
    if (sizeof($selection) > 1) {
      echo tep_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['id'] == $payment), 'required aria-required="true"');
    } else {
      echo tep_draw_hidden_field('payment', $selection[$i]['id']);
    }
?>

        </td>
      </tr>

<?php
    if (isset($selection[$i]['error'])) {
?>

      <tr>
        <td colspan="2"><?php echo $selection[$i]['error']; ?></td>
      </tr>

<?php
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>

      <tr>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">

<?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>

          <tr>
            <td><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
            <td><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
          </tr>

<?php
      }
?>

        </table></td>
      </tr>

<?php
    }
?>



<?php
    $radio_buttons++;
  }
?>
      </tbody>
    </table>

  </div>

  <hr>

  <div class="contentText">
    <div class="form-group">
      <label for="inputComments" class="control-label col-sm-4"><?php echo TABLE_HEADING_COMMENTS; ?></label>
      <div class="col-sm-8">
        <?php
        echo tep_draw_textarea_field('comments', 'soft', 60, 5, $comments, 'id="inputComments" placeholder="' . TABLE_HEADING_COMMENTS . '"');
        ?>

      </div>
    </div>
  </div>
<!--coupon-->.
	<?php	/* kgt - discount coupons */
		if( MODULE_ORDER_TOTAL_DISCOUNT_COUPON_STATUS == 'true' ) {
		?>
		<div class="row">
			<div class="col-sm-12">
				<h4><?php echo TABLE_HEADING_COUPON; ?></h4>
			</div>
		<div class="col-sm-4">
		<label for="coupon"><?php echo ENTRY_DISCOUNT_COUPON; ?></label>
		</div>
		
        <div class="col-sm-4">
        <?php echo tep_draw_input_field('coupon', '', 'size="32"', $coupon); ?>
		</div>
		</div>
		<?php
		}
	/* end kgt - discount coupons */ ?>
<!--coupon-->
  <div class="buttonSet">
    <div class="text-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success'); ?></div>
  </div>

  <div class="clearfix"></div>


  <!--  step -->
    <div class="row shop-tracking-status">
		<div class="order-status">
			
			<div class="order-status-timeline">
				<!-- class names: c0 c1 c2 c3 and c4 -->
				<div class="order-status-timeline-completion c2_2"></div>
			</div>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-new active img-circle">
                    <div class="icon fa fa-truck fa-flip-horizontal fa-2x"></div>
                </div>
			</a>
			
                <div class="image-order-status image-order-status-active active img-circle">
                     <div class="icon fa fa-calendar fa-2x"></div>
                </div>
			
			<div class="image-order-status image-order-status-completed img-circle">
				<div class="icon fa fa-thumbs-up fa-2x"></div>
			</div>
			
		</div>
	</div>
	<!-- end step -->

</div>
</div>
</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
