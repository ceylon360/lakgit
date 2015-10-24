<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class bm_shopping_cart {
    var $code = 'bm_shopping_cart';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_shopping_cart() {
      $this->title = MODULE_BOXES_SHOPPING_CART_TITLE;
      $this->description = MODULE_BOXES_SHOPPING_CART_DESCRIPTION;

      if ( defined('MODULE_BOXES_SHOPPING_CART_STATUS') ) {
        $this->sort_order = MODULE_BOXES_SHOPPING_CART_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_SHOPPING_CART_STATUS == 'True');

        $this->group = ((MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $PHP_SELF,$cart, $new_products_id_in_cart, $currencies, $oscTemplate;
if (($PHP_SELF == 'login.php') 
     || ($PHP_SELF == 'account_pwa.php')
     || ($PHP_SELF == 'checkout_shipping_address.php')	
     || ($PHP_SELF == 'checkout_shipping.php')	
     || ($PHP_SELF == 'checkout_payment.php')
	 || ($PHP_SELF == 'checkout_payment_address.php')
     || ($PHP_SELF == 'checkout_confirmation.php')) {
      $cart_contents_string = '';

      if ($cart->count_contents() > 0) {
        $cart_contents_string = NULL;
        $products = $cart->get_products();
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {

		
          $cart_contents_string .= '<div class="mini-product"';
          if ((tep_session_is_registered('new_products_id_in_cart')) && ($new_products_id_in_cart == $products[$i]['id'])) {
            $cart_contents_string .= ' class="newItemInCart"';
          }
          $cart_contents_string .= '>';
		  
		  $cart_contents_string .= '<div class="cartbx_tmb">'. tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], $width = '50px', $height = '50px') . '</div>';

          
		  
			$cart_contents_string .='<div>';
          $cart_contents_string .= '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">';

          $cart_contents_string .= $products[$i]['name'];

          $cart_contents_string .= '</a></br><span class="cartbx_price">';
		  $cart_contents_string .= $products[$i]['quantity'];
		  $cart_contents_string .= ' x $'.$products[$i]['final_price'].'</span>';
			$cart_contents_string .='<span class="cartbx_qprice">';
			
			$products_tax = tep_get_tax_rate($products[$i]['tax_class_id']);
            $popup_price = $currencies->calculate_price($products[$i]['final_price'], $products_tax, $products[$i]['quantity']);
            $cart_contents_string .= $currencies->format($popup_price) . '</span></div>';
		 
		 $cart_contents_string .='</div><hr>';
		 
          if ((tep_session_is_registered('new_products_id_in_cart')) && ($new_products_id_in_cart == $products[$i]['id'])) {
            tep_session_unregister('new_products_id_in_cart');
          }
        }
$cart_contents_string .='<div>';
        $cart_contents_string .= '<p class="pull-left"><b>SUBTOTAL</b></p><div class="pull-right"><p class="cartbx_total">' . $currencies->format($cart->show_total()) . '</p></div>';
$cart_contents_string .='</div><br><hr>';
      } else {
        $cart_contents_string .= '<p>' . MODULE_BOXES_SHOPPING_CART_BOX_CART_EMPTY . '</p>';
      }
              
      ob_start();
      include(DIR_WS_MODULES . 'boxes/templates/shopping_cart.php');
      $data = ob_get_clean();

      $oscTemplate->addBlock($data, $this->group);
    }
	}
    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_SHOPPING_CART_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Shopping Cart Module', 'MODULE_BOXES_SHOPPING_CART_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_SHOPPING_CART_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_SHOPPING_CART_STATUS', 'MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT', 'MODULE_BOXES_SHOPPING_CART_SORT_ORDER');
    }
  }
?>
