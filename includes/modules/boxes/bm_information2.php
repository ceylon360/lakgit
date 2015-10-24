<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class bm_information2 {
    var $code = 'bm_information2';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_information2() {
      $this->title = MODULE_BOXES_INFORMATION2_TITLE;
      $this->description = MODULE_BOXES_INFORMATION2_DESCRIPTION;
	  

      if ( defined('MODULE_BOXES_INFORMATION2_STATUS') ) {
        $this->sort_order = MODULE_BOXES_INFORMATION2_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_INFORMATION2_STATUS == 'True');

        $this->group = ((MODULE_BOXES_INFORMATION2_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $cPath;
	
     if (($PHP_SELF == 'login.php') 
	 || ($PHP_SELF == 'shopping_cart.php')
     || ($PHP_SELF == 'account_pwa.php')
     || ($PHP_SELF == 'checkout_shipping_address.php')	
     || ($PHP_SELF == 'checkout_shipping.php')	
     || ($PHP_SELF == 'checkout_payment.php')
	 || ($PHP_SELF == 'checkout_payment_address.php')
     || ($PHP_SELF == 'checkout_confirmation.php')
     || ($PHP_SELF == 'checkout_success_pwa.php')) {
		 
		 
		 
		 	//$gen_data .= '      <div class="col-sm-12 col-md-12">';
	$shopping_data .= MODULE_BOXES_INFORMATION2_CONTENT;
	//$gen_data .= '      </div>';
      ob_start();
      include(DIR_WS_MODULES . 'boxes/templates/information2.php');
      $template = ob_get_clean();

      $oscTemplate->addBlock($template, $this->group);
     }
	}

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_INFORMATION2_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Information Module', 'MODULE_BOXES_INFORMATION2_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_INFORMATION2_CONTENT_PLACEMENT', 'Left Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_INFORMATION2_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('content text', 'MODULE_BOXES_INFORMATION2_CONTENT', 'example', 'content?', '6', '1', now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_INFORMATION2_STATUS', 'MODULE_BOXES_INFORMATION2_CONTENT_PLACEMENT', 'MODULE_BOXES_INFORMATION2_CONTENT','MODULE_BOXES_INFORMATION2_SORT_ORDER');
    }
  }
