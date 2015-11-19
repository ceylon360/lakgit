<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/classes/http_client.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link('account_pwa.php', '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// if no shipping destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('sendto')) {
		tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
    //tep_session_register('sendto');
    //$sendto = $customer_default_address_id;
  } else {
// verify the selected shipping address
    if ( (is_array($sendto) && empty($sendto)) || is_numeric($sendto) ) {
      $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
      $check_address = tep_db_fetch_array($check_address_query);

      if ($check_address['total'] != '1') {
       // $sendto = $customer_default_address_id;
        if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');
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

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  if (!tep_session_is_registered('cartID')) {
    tep_session_register('cartID');
  } elseif (($cartID != $cart->cartID) && tep_session_is_registered('shipping')) {
    tep_session_unregister('shipping');

  }
// ship date
  if (!tep_session_is_registered('delivery_date')) tep_session_register('delivery_date');
  if (tep_not_null($HTTP_POST_VARS['delivery_date'])) {
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




  $cartID = $cart->cartID = $cart->generate_cart_id();

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  if ($order->content_type == 'virtual') {
    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
    $shipping = false;
    $sendto = false;
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  $total_weight = $cart->show_weight();
  $total_count = $cart->count_contents();

// load all enabled shipping modules
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;

  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'both':
        $pass = true;
        break;
    }

    $free_shipping = false;

    if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
      $free_shipping = true;

      include(DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }

// process the selected shipping method
  if ( isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'process') && isset($HTTP_POST_VARS['formid']) && ($HTTP_POST_VARS['formid'] == $sessiontoken) ) {
    if (!tep_session_is_registered('comments')) tep_session_register('comments');
    if (tep_not_null($HTTP_POST_VARS['comments'])) {
      $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);
    }

    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');

    if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
      if ( (isset($HTTP_POST_VARS['shipping'])) && (strpos($HTTP_POST_VARS['shipping'], '_')) ) {
        $shipping = $HTTP_POST_VARS['shipping'];

        list($module, $method) = explode('_', $shipping);
        if ( is_object($$module) || ($shipping == 'free_free') ) {
          if ($shipping == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote['error'])) {
            tep_session_unregister('shipping');
          } else {
            if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
              $shipping = array('id' => $shipping,
                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                'cost' => $quote[0]['methods'][0]['cost']);

              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
          }
        } else {
          tep_session_unregister('shipping');
        }
      }
    } else {
      if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') ) {
        tep_session_unregister('shipping');
      } else {
        $shipping = false;
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      }
    }
  }

// get all available shipping quotes
  $quotes = $shipping_modules->quote();

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
  if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ) $shipping = $shipping_modules->cheapest();

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_SHIPPING);

  if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') && !    tep_session_is_registered('shipping') && ($shipping == false) ) {
  $messageStack->add_session('checkout_address', ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS);
  tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
}

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
<div class="card card-green animated fadeInRight" >
    <!--  step -->
    <div class="row shop-tracking-status">
		<div class="order-status">
			
			<div class="order-status-timeline">
				<!-- class names: c0 c1 c2 c3 and c4 -->
				<div class="order-status-timeline-completion c2"></div>
			</div>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-new active img-circle">
                    <div class="icon fa fa-truck fa-flip-horizontal fa-2x"></div>
                </div>
			</a>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-active active img-circle">
                     <div class="icon fa fa-calendar fa-2x"></div>
                </div>
			</a>
			<div class="image-order-status image-order-status-completed img-circle">
				<div class="icon fa fa-thumbs-up fa-2x"></div>
			</div>
			
		</div>
	</div>
	<!-- end step -->
<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('checkout_address', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), 'post', 'class="form-horizontal"', true) . tep_draw_hidden_field('action', 'process'); ?>

<div class="contentContainer">
  

  <div class="contentText">
         <div class='col-sm-4 bg-gray'>
		<h4><?php echo TABLE_HEADING_SHIPPING_DATE; ?></h4>
		<div id="datepicker" ></div> 
		<input type="hidden" name="my_hidden_input"id="my_hidden_input" />
		<link rel="stylesheet" href="ext/datepicker2/css/bootstrap-datepicker.css" />
           <script src="ext/datepicker2/js/bootstrap-datepicker.js"></script>
		   <script> var date = new Date();
		   date.setDate(date.getDate()+1);
		   $('#datepicker').datepicker({
    startDate: date,
	datesDisabled: ['09/06/2015', '09/21/2015'],
    
    }).on('changeDate', function(e){
      $('#delivery_date').val(e.format('yyyy-mm-dd'))
    });

</script>
  <div class="contentText">
    <?php echo TEXT_CHOOSE_SHIPPING_DATE . '<br /><br />' . tep_draw_input_field('delivery_date','', 'id="delivery_date" required aria-required="true" readonly="readonly"'); ?>
  </div>
        </div> 
      <div class="col-sm-8">
	  <h4 ><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></h4>

	  <?php echo tep_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'glyphicon glyphicon-home', tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs'); ?>
      <div class="bg-gray">

        <div class="panel-heading hide"><?php echo TITLE_SHIPPING_ADDRESS; ?></div>
        <div class="panel-body">
          <?php echo tep_address_label($customer_id, $sendto, true, ' ', ''); ?>
		  <div class="pull-right">
          <?php //echo tep_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'glyphicon glyphicon-home', tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs'); ?>
        </div>
        </div>
      </div>

	  	  <!--<div class="alert alert-warning">
        <?php /** echo TEXT_CHOOSE_SHIPPING_DESTINATION;**/ ?>
        <div class="clearfix"></div>
        
        <div class="clearfix"></div>
      </div> -->
    </div>
	 <!-- ship date -->
	  <div class="col-sm-8">
	  		  
					  
				
		  <h4 ><?php echo TABLE_HEADING_BILLING_ADDRESS; ?></h4>
<?php echo tep_draw_button(IMAGE_BUTTON_CHANGE_NAME, 'glyphicon glyphicon-user', tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'), NULL, NULL, 'pull-right btn-info btn-xs'); ?>
		  <div class="bg-gray">
			  
			  <div class="panel-body">
			 
				  <?php echo tep_address_label($customer_id, $billto, true, ' ', ''); ?>
				   <?php //echo '</br> Telephone : '. $order->customer['telephone']; ?>
				  
				  
			  </div>
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
<div class="col-sm-8">
  <hr>
	<?php
	$surprise_sely='';
	$surprise_seln='';
if($surprise=='yes'){
		$surprise_sely=true;
}
else{
		$surprise_seln=true;
	}
	
	$anonymous_sely='';
	$anonymous_seln='';
if($anonymous=='yes'){
		$anonymous_sely=true;
}
else{
		$anonymous_seln=true;
	}


	?>
	
	<!-- surprise -->

	<div class="form-group has-feedback">
      <label class="control-label col-sm-4 text-left"><?php echo ENTRY_SURPRISE; ?></label>
      <div class="col-sm-8">
	  <label class="radio-inline">
          <?php echo tep_draw_radio_field('surprise', 'no',$surprise_seln,'id="surprisen"') . ' ' . ENTRY_SURPRISEN; ?>
        </label>
        <label class="radio-inline">
          <?php echo tep_draw_radio_field('surprise', 'yes', $surprise_sely, 'required aria-required="true" id="surprisey"') . ' ' .ENTRY_SURPRISEY; ?>
        </label>
        
        <?php //echo FORM_REQUIRED_INPUT; ?>
        <?php //if (tep_not_null(ENTRY_GENDER_TEXT)) echo '<span class="help-block">' . ENTRY_SURPRISE_TEXT . '</span>'; ?>
      </div>
	  <div class="col-sm-12">
			<div class="animated fadeInUp notice notice-warning surprise_yes" style="display:none"><?php echo SURPRISEY_INFO?></div>
		    <div class="animated fadeInUp notice notice-success surprise_no" style="display:none" ><?php echo SURPRISEN_INFO?></div>
		</div>
    </div>
	<!-- eof surprise -->
	
	<!-- anonymous -->
	<div class="form-group has-feedback">
		<label class="control-label col-sm-4 text-left"><?php echo ENTRY_ANONYMOUS; ?></label>
		<div class="col-sm-8">
			<label class="radio-inline">
				<?php echo tep_draw_radio_field('anonymous', 'no',$anonymous_seln,'id="anonymousn"') . ' ' . 'No'; ?>
			</label>
			<label class="radio-inline">
				<?php echo tep_draw_radio_field('anonymous', 'yes', $anonymous_sely, 'required aria-required="true" id="anonymousy"') . ' ' .'Yes'; ?>
			</label>

					
			<?php// echo FORM_REQUIRED_INPUT; ?>
			<?php //if (tep_not_null(ENTRY_GENDER_TEXT)) echo '<span class="help-block">' . ENTRY_ANONYMOUS_TEXT . '</span>'; ?>
		</div>
		<div class="col-sm-12">
			<div class="animated fadeInUp notice notice-warning anonymous_yes" style="display:none"><?php echo ANONYMOUSY_INFO?></div>
		<div class="animated fadeInUp notice notice-success anonymous_no" style="display:none"><?php echo ANONYMOUSN_INFO?></div>
			</div>
    </div>
	
	<script type="text/javascript">

		
		$(document).ready(function(){
			
			$('input[name="surprise"]').click(function(){
				if($(this).attr("value")=="yes"){
					$(".surprise_no").hide('slow');
					$(".surprise_yes").show('slow');
				}
				if($(this).attr("value")=="no"){
					$(".surprise_yes").hide('slow');
					$(".surprise_no").show('slow');
				}
				
			});
		});
	
	function anno(){
		if (document.getElementById('surprisey').checked){
			$(".surprise_no").hide();
			$(".surprise_yes").show();
		}
		if(document.getElementById('surprisen').checked){
			$(".surprise_yes").hide();
			$(".surprise_no").show();
		}
				if (document.getElementById('anonymousy').checked){
					$(".anonymous_no").hide();
					$(".anonymous_yes").show();
				}
				if(document.getElementById('anonymousn').checked){
					$(".anonymous_yes").hide();
					$(".anonymous_no").show();
				}
				
			};
			window.onload = anno;
		$(document).ready(function(){
			
			$('input[name="anonymous"]').click(function(){
				if($(this).attr("value")=="yes"){
					$(".anonymous_no").hide('slow');
					$(".anonymous_yes").show('slow');
				}
				if($(this).attr("value")=="no"){
					$(".anonymous_yes").hide('slow');
					$(".anonymous_no").show('slow');
				}
				
			});
		});
	</script>
	<!-- eof anonymous -->

</div>



<!-- eof ship date -->
	<div class="col-sm-4 hide">
      <!--<label for="inputComments" class="control-label"><?php echo TABLE_HEADING_COMMENTS; ?></label>-->
	  <h4><?php echo TABLE_HEADING_COMMENTS; ?></h4>
      <div class="">
        <?php
        echo tep_draw_textarea_field('comments', 'soft', 60, 16, $comments, 'id="inputComments" placeholder="' . TABLE_HEADING_COMMENTS . '"');
        ?>
      </div>
	  </div>

  </div>

  <div class="clearfix"></div>

<?php
  if (tep_count_shipping_modules() > 0) {
?>

  <h2><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></h2>

<?php
    if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
?>

  <div class="contentText">
    <div class="alert alert-warning">
      <div class="row">
        <div class="col-xs-8">
          <?php echo TEXT_CHOOSE_SHIPPING_METHOD; ?>
        </div>
        <div class="col-xs-4 text-right">
          <?php echo '<strong>' . TITLE_PLEASE_SELECT . '</strong>'; ?>
        </div>
      </div>
    </div>
  </div>

<?php
    } elseif ($free_shipping == false) {
?>

  <div class="contentText">
    <div class="alert alert-info"><?php echo TEXT_ENTER_SHIPPING_INFORMATION; ?></div>
  </div>

<?php
    }
?>

  <div class="contentText">
    <table class="table table-striped table-condensed table-hover">
      <tbody>

<?php
    if ($free_shipping == true) {
?>

    <div class="contentText">
      <div class="panel panel-success">
        <div class="panel-heading"><strong><?php echo FREE_SHIPPING_TITLE; ?></strong>&nbsp;<?php echo $quotes[$i]['icon']; ?></div>
        <div class="panel-body">
          <?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . tep_draw_hidden_field('shipping', 'free_free'); ?>
        </div>
      </div>
    </div>

<?php
    } else {
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
        for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
// set the radio button to be checked if it is the method chosen
          $checked = (($quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'] == $shipping['id']) ? true : false);         

?>
      <tr class="table-selection">
        <td>
          <strong><?php echo $quotes[$i]['module']; ?></strong>
          <?php
          if (isset($quotes[$i]['icon']) && tep_not_null($quotes[$i]['icon'])) echo '&nbsp;' . $quotes[$i]['icon'];
          ?>

          <?php
          if (isset($quotes[$i]['error'])) {
            echo '<div class="help-block">' . $quotes[$i]['error'] . '</div>';
          }
          ?>

          <?php
          if (tep_not_null($quotes[$i]['methods'][$j]['title'])) echo '<div class="help-block">' . $quotes[$i]['methods'][$j]['title'] . '</div>';
          ?>
          </td>

<?php
            if ( ($n > 1) || ($n2 > 1) ) {
?>

        <td align="right">
          <?php
          if (isset($quotes[$i]['error'])) {
            // nothing
            echo '&nbsp;';
          }
          else {
            echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))); ?>&nbsp;&nbsp;<?php echo tep_draw_radio_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked, 'required aria-required="true"');
          }
          ?>
        </td>

<?php
            } else {
?>

        <td align="right"><?php echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))) . tep_draw_hidden_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']); ?></td>

<?php
            }
?>

      </tr>

<?php
        }
      }
    }
?>

      </tbody>
    </table>
  </div>

<?php
  }
?>


<!-- ////////////////////////////////////// -->
  <div class="buttonSet">
  
    <div class="text-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success'); ?></div>
  </div>
  
  <div class="clearfix"></div>

  <!--  step -->
    <div class="row shop-tracking-status">
		<div class="order-status">
			
			<div class="order-status-timeline">
				<!-- class names: c0 c1 c2 c3 and c4 -->
				<div class="order-status-timeline-completion c2"></div>
			</div>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-new active img-circle">
                    <div class="icon fa fa-truck fa-flip-horizontal fa-2x"></div>
                </div>
			</a>
			<a href="<?php echo tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'); ?>">
                <div class="image-order-status image-order-status-active active img-circle">
                     <div class="icon fa fa-calendar fa-2x"></div>
                </div>
			</a>
			<div class="image-order-status image-order-status-completed img-circle">
				<div class="icon fa fa-thumbs-up fa-2x"></div>
			</div>
			
		</div>
	</div>
	<!-- end step -->
  <div class="contentText">
<div class="row">
    <?php echo $oscTemplate->getContent('shipping_info'); ?>
  </div>
  <div class="clearfix"></div>
  </div>
</div>
</div>
</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
