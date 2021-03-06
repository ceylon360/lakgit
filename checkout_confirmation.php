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
    $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if (!tep_session_is_registered('payment')) tep_session_register('payment');
  if (isset($HTTP_POST_VARS['payment'])) $payment = $HTTP_POST_VARS['payment'];

  if (!tep_session_is_registered('comments')) tep_session_register('comments');
  if (isset($HTTP_POST_VARS['comments']) && tep_not_null($HTTP_POST_VARS['comments'])) {
    $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);
  }
  //kgt - discount coupons
  if (!tep_session_is_registered('coupon')) tep_session_register('coupon');
  //this needs to be set before the order object is created, but we must process it after
  $coupon = tep_db_prepare_input($HTTP_POST_VARS['coupon']);
  //end kgt - discount coupons
// ship date
  if (!tep_session_is_registered('datum')) tep_session_register('datum');
  if (tep_not_null($HTTP_POST_VARS['datum'])) {
    $delivery_date = tep_db_prepare_input($HTTP_POST_VARS['datum']);
  }
  // eof ship date
  

// load the selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($payment);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  $payment_modules->update_status();

  if ( ($payment_modules->selected_module != $payment) || ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($$payment) ) || (is_object($$payment) && ($$payment->enabled == false)) ) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
  }

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }
//kgt - discount coupons
  if( tep_not_null( $coupon ) && is_object( $order->coupon ) ) { //if they have entered something in the coupon field
    $order->coupon->verify_code();
    if( MODULE_ORDER_TOTAL_DISCOUNT_COUPON_DEBUG != 'true' ) {
		  if( !$order->coupon->is_errors() ) { //if we have passed all tests (no error message), make sure we still meet free shipping requirements, if any
			  if( $order->coupon->is_recalc_shipping() ) tep_redirect( tep_href_link( FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode( ENTRY_DISCOUNT_COUPON_SHIPPING_CALC_ERROR ), 'SSL' ) ); //redirect to the shipping page to reselect the shipping method
		  } else {
			  if( tep_session_is_registered('coupon') ) tep_session_unregister('coupon'); //remove the coupon from the session
			  tep_redirect( tep_href_link( FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode( implode( ' ', $order->coupon->get_messages() ) ), 'SSL' ) ); //redirect to the payment page
		  }
    }
	} else { //if the coupon field is empty, unregister the coupon from the session
		if( tep_session_is_registered('coupon') ) { //we had a coupon entered before, so we need to unregister it
      tep_session_unregister('coupon');
      //now check to see if we need to recalculate shipping:
      require_once( DIR_WS_CLASSES.'discount_coupon.php' );
      if( discount_coupon::is_recalc_shipping() ) tep_redirect( tep_href_link( FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode( ENTRY_DISCOUNT_COUPON_SHIPPING_CALC_ERROR ), 'SSL' ) ); //redirect to the shipping page to reselect the shipping method
    }
	}
	//end kgt - discount coupons
// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($shipping);

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;
  $order_total_modules->process();

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
        $any_out_of_stock = true;
      }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    }
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_CONFIRMATION);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
<div class="card card-green animated fadeInRight" >
<!--  step -->
    <div class="row shop-tracking-status">
		<div class="order-status">
			
			<div class="order-status-timeline">
				<!-- class names: c0 c1 c2 c3 and c4 -->
				<div class="order-status-timeline-completion c3"></div>
			</div>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-new active img-circle">
                    <div class="icon fa fa-truck fa-flip-horizontal fa-2x"></div>
                </div>
			</a>
			
                <div class="image-order-status image-order-status-active active img-circle">
                     <div class="icon fa fa-calendar fa-2x"></div>
                </div>
			
			<div class="image-order-status image-order-status-completed active img-circle">
				<div class="icon fa fa-thumbs-up fa-2x"></div>
			</div>
			
		</div>
	</div>
	<!-- end step -->
<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('checkout_confirmation') > 0) {
    echo $messageStack->output('checkout_confirmation');
  }

  if (isset($$payment->form_action_url)) {
    $form_action_url = $$payment->form_action_url;
  } else {
    $form_action_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
  }
  
  echo tep_draw_form('checkout_confirmation', $form_action_url, 'post');
?>

<div class="contentContainer">
  <div class="contentText">

    <div class="card card-gray">
      <div class="panel-heading"><?php echo '<strong>' . HEADING_PRODUCTS . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_SHOPPING_CART), NULL, NULL, 'pull-right btn-info btn-xs' ); ?></div>
      <div class="panel-body">
    <table width="100%" class="table-hover order_confirmation">
     <tbody>

<?php
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td align="right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x&nbsp;</td>' . "\n" .
         '            <td valign="top">' . $order->products[$i]['name'];

    if (STOCK_CHECK == 'true') {
      echo tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty']);
    }

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }
// denuz text attr

     $b = strpos($order->products[$i]['id'], '{');
     if ($b === false) {
       $pid = $order->products[$i]['id'];
     } else {
       $pid = substr($order->products[$i]['id'], 0, $b);
     }

     $attr_q = tep_db_query("select cbta.*, pta.products_text_attributes_name from customers_basket_text_attributes as cbta, products_text_attributes as pta where cbta.products_text_attributes_id = pta.products_text_attributes_id and cbta.products_id = " . $pid . " and cbta.session_id = '" . $osCsid . "'");
     while ($attr = tep_db_fetch_array($attr_q)) {
          echo '<br><small>&nbsp;<i> - ' . $attr['products_text_attributes_name'] . ': ' . stripslashes($attr['products_text_attributes_text'])  . '</i></small>';       
     }

// eof denuz text attr

    echo '</td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) echo '            <td valign="top" align="right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";

    echo '            <td align="right" valign="top">' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>


        </tbody>
      </table>
      <hr>
      <table width="100%" class="pull-right">

<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    echo $order_total_modules->output();
  }
?>

        </table>
            </div>
    </div>



  </div>

  <div class="clearfix"></div>

  <div class="row">
    <?php
    if ($sendto != false) {
      ?>
      <div class="col-sm-8">
        <div class="card2 card-blue">
          <div class="panel-heading"><?php echo '<strong>' . HEADING_DELIVERY_ADDRESS . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?></div>
          <div class="panel-body">
            <?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?>
          </div>
        </div>
      </div>
      <?php
    }
    ?>
	<?php
	//ship date
          if (tep_not_null($order->info['delivery_date'])) {
              ?>
			  <div class="col-sm-4">
      <div class="card2 card-yellow">
        <div class="panel-heading">
		
		<?php echo '<strong>' . HEADING_SHIPPING_DATE . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?>
		</div>
        <div class="panel-body">
		
		  <?php echo tep_date_long($order->info['delivery_date']); ?>
        </div>
      </div>
    </div>
	<?php
          }
		  //ship date
		  if ($order->info['surprise']='yes'){
				$surprise_text=ENTRY_SURPRISEY;
		  }
		  else{
				$surprise_text=ENTRY_SURPRISEN;
		  }
		//surprise
		if (tep_not_null($order->info['surprise'])) {
		?>
		<div class="col-sm-4">
			<div class="card2 card-yellow">
				<div class="panel-heading">
					
					<?php echo '<strong>' . SURPRISE_TEXT . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?>
				</div>
				<div class="panel-body">
					
					<?php echo $surprise_text ?>
				</div>
			</div>
		</div>
		<?php
		}
		//surprise
		//anonymous
		if ($order->info['anonymous']='yes'){
				$anonymous_text=ENTRY_ANONYMOUSY;
		  }
		  else{
				$anonymous_text=ENTRY_ANONYMOUSN;
		  }
		if (tep_not_null($order->info['anonymous'])) {
				
		?>
		<div class="col-sm-4">
			<div class="card2 card-yellow">
				<div class="panel-heading">
					
					<?php echo '<strong>' . ANONYMOUS_TEXT . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link('account_pwa.php', '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?>
				</div>
				<div class="panel-body">
					
					<?php echo $anonymous_text; ?>
				</div>
			</div>
		</div>
		<?php
		}
		//anonymous
          ?>
		  
  <!--  <div class="col-sm-4">
      <div class="panel panel-warning">
        <div class="panel-heading"><?php// echo '<strong>' . HEADING_BILLING_ADDRESS . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?></div>
        <div class="panel-body">
          <?php// echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?>
        </div>
      </div>
    </div> -->
    <div class="col-sm-4">
      <?php
      if ($order->info['shipping_method']) {
        ?>
        <div class="card2 card-blue">
          <div class="panel-heading"><?php echo '<strong>' . HEADING_SHIPPING_METHOD . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?></div>
          <div class="panel-body">
            <?php echo $order->info['shipping_method']; ?>
          </div>
        </div>
        <?php
      }
      ?>
	  </div>
	  <div class="col-sm-4">
      <div class="card2 card-yellow">
        <div class="panel-heading"><?php echo '<strong>' . HEADING_PAYMENT_METHOD . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?></div>
        <div class="panel-body">
          <?php echo $order->info['payment_method']; ?>
        </div>
      </div>
	  </div>
    


  </div>


<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
?>
  <hr>

  <h2><?php echo HEADING_PAYMENT_INFORMATION; ?></h2>

  <div class="contentText row">
<?php
    if (tep_not_null($confirmation['title'])) {
      echo '<div class="col-sm-6">';
      echo '  <div class="alert alert-danger">';
      echo $confirmation['title'];
      echo '  </div>';
      echo '</div>';
    }
?>
<?php
      if (isset($confirmation['fields'])) {
        echo '<div class="col-sm-6">';
        echo '  <div class="alert alert-info">';
        for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
          echo $confirmation['fields'][$i]['title'] . ' ' . $confirmation['fields'][$i]['field'];
        }
        echo '  </div>';
        echo '</div>';
      }
?>
  </div>
  <div class="clearfix"></div>

<?php
    }
  }

  if (tep_not_null($order->info['comments'])) {
?>
  <hr>

  <h2><?php echo '<strong>' . HEADING_ORDER_COMMENTS . '</strong>' . tep_draw_button(TEXT_EDIT, 'glyphicon glyphicon-edit', tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs' ); ?></h2>

  <blockquote>
    <?php echo nl2br(tep_output_string_protected($order->info['comments'])) . tep_draw_hidden_field('comments', $order->info['comments']); ?>
  </blockquote>

<?php
  }
?>

  <div class="buttonSet">
    <div class="text-right">
      <?php
      if (is_array($payment_modules->modules)) {
        echo $payment_modules->process_button();
      }
      echo tep_draw_button(IMAGE_BUTTON_CONFIRM_ORDER, 'glyphicon glyphicon-ok', null, 'primary', null, 'btn-success');
      ?>
    </div>
  </div>

  <div class="clearfix"></div>
<!--  step -->
    <div class="row shop-tracking-status">
		<div class="order-status">
			
			<div class="order-status-timeline">
				<!-- class names: c0 c1 c2 c3 and c4 -->
				<div class="order-status-timeline-completion c3"></div>
			</div>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-new active img-circle">
                    <div class="icon fa fa-truck fa-flip-horizontal fa-2x"></div>
                </div>
			</a>
			
                <div class="image-order-status image-order-status-active active img-circle">
                     <div class="icon fa fa-calendar fa-2x"></div>
                </div>
			
			<div class="image-order-status image-order-status-completed active img-circle">
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
