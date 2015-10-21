<?php
/*
  $Id: recently_viewed.php
  $Loc: catalog/includes/modules/boxes/

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  2015 Recently Viewed 3.3 BS by @raiwa info@sarplataygemas.com
  based on 2.0 2008-10-28 Kymation $

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/


  class bm_recently_viewed {
    var $code = 'bm_recently_viewed';
    var $group = 'boxes';                                                             
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_recently_viewed() {
      $this->title = MODULE_BOXES_RECENTLY_VIEWED_TITLE;
      $this->description = MODULE_BOXES_RECENTLY_VIEWED_DESCRIPTION;

      require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'recently_viewed.php');

      if ( defined('MODULE_BOXES_RECENTLY_VIEWED_STATUS') ) {
        $this->sort_order = MODULE_BOXES_RECENTLY_VIEWED_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_RECENTLY_VIEWED_STATUS == 'True');
        
        $this->group = ((MODULE_BOXES_RECENTLY_VIEWED_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
      if ( !defined('MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS') || MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS != 'True' ) {
      	$this->description = '<div class="secWarning">' . MODULE_BOXES_RECENTLY_VIEWED_ERROR_ADMIN_HT . '</div>' . $this->description;

        $this->enabled = false;
      }
    }

    function execute() {
      global $oscTemplate, $languages_id, $currencies, $_SESSION, $recently_viewed_string;

      // include the tep_recently_limit_text function
      require_once(DIR_WS_FUNCTIONS . 'recently_viewed.php');

      // Display recently viewed box only if the customer has viewed some products and box is set in Admin
      if (tep_session_is_registered ('recently_viewed') && strlen ($_SESSION['recently_viewed']) > 0) { 
      	$recently_viewed_string = $_SESSION['recently_viewed'];

      	// Deal with sessions created by the previous version
      	if (substr_count ($recently_viewed_string, ';') > 0) {
      		$_SESSION['recently_viewed'] = '';
      		$recently_viewed_string = '';
      	}

        $recently_box_text = null;
    
        // Retrieve the data on the products in the recently viewed list and load into an array by products_id
        $products_data = array();
    
      	$products_query = "select ";
      	$specials_query = '';
      		
        if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_DESCRIPTION == 'True')
        	$products_query .= "     pd.products_description,";
        
        if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_IMAGE == 'True')
          $products_query .= "      p.products_image,";
                                       
        if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_PRICE == 'True') {
        	$products_query .= "p.products_tax_class_id,p.products_price,IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price,";
        	$specials_query = " left join specials s on s.products_id = p.products_id ";
      	}
      	
      	$products_query .= "pd.products_name,p.products_id";
        
        $products_query .= " from products p
                              left join products_description pd on pd.products_id = p.products_id"
                              . $specials_query . "
                              where p.products_id in (" . $recently_viewed_string . ") 
                              and p.products_status = '1' 
                              and pd.language_id = '" . (int) $languages_id . "' 
                            ";
        $products_query = tep_db_query($products_query);
        
        while ($products = tep_db_fetch_array ($products_query) ) {
        	$products_id = $products['products_id'];
        	$products_data[$products_id] = array ('id' => $products_id,
                                              	'name' => $products['products_name']
                                           			);
          if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_DESCRIPTION == 'True')
          	$products_data[$products_id]['description'] = $products['products_description'];
												
          if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_IMAGE == 'True')
          	$products_data[$products_id]['image'] = $products['products_image'];
																			   
          if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_PRICE == 'True') {
          	$products_data[$products_id]['tax_class_id'] = $products['products_tax_class_id'];
            $products_data[$products_id]['price'] = $products['products_price'];
            $products_data[$products_id]['specials_price'] = $products['specials_new_products_price'];
          }
 
        } //while ($products
      
        // Turn the string of product IDs into an array in the correct order
        $recently_viewed_string = strtr ($recently_viewed_string, ',,', ','); // Remove blank values
        $recently_viewed_array = explode (',', $recently_viewed_string); // Array is in order newest first
    
        // Get rid of the current product if set in admin
        $products_id = (int) $_GET['products_id'];
        if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_CURRENT == 'False' && $products_id != 0) {
        	$current_key = array_search ($products_id, $recently_viewed_array);
        	unset ($recently_viewed_array[$current_key]);
        }

        // Limit the number of products shown as set in Admin
        $recently_viewed_array = array_slice ($recently_viewed_array, 0, MODULE_BOXES_RECENTLY_VIEWED_MAX_DISPLAY_PRODUCTS);
    
        if (MODULE_BOXES_RECENTLY_VIEWED_DISPLAY_ORDER == 'Oldest') { // Reverse the order if set in Admin
        	$recently_viewed_array = array_reverse ($recently_viewed_array);
        }

        // Set up the product data string in order by $recently_viewed_array
        if (count ($recently_viewed_array) > 0) { // Show only if we still have products in the array
        	$product_number = 0;
        	foreach ($recently_viewed_array as $products_id) {

        		// Show the products image if selected in Admin
        		if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_IMAGE == 'True') {
        			$recently_box_text .= '<a href="' . tep_href_link ('product_info.php', 'products_id=' . $products_data[$products_id]['id']) . '">' . tep_image (DIR_WS_IMAGES . $products_data[$products_id]['image'], $products_data[$products_id]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br />';
        		} //if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_IMAGE
      
        		// Show the products name if selected in Admin
        		if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_NAME == 'True') {
        			$productname = $products_data[$products_id]['name'];
        			if (MODULE_BOXES_RECENTLY_VIEWED_NAME_LENGTH > 0) {
        				$productname_length = strlen ($productname);
        				if ($productname_length > MODULE_BOXES_RECENTLY_VIEWED_NAME_LENGTH) {
        					$productname = tep_recently_limit_text ($productname, MODULE_BOXES_RECENTLY_VIEWED_NAME_LENGTH, MODULE_BOXES_RECENTLY_VIEWED_WORD_LENGTH) . '&nbsp;...';
        				} // if ($name_length
        			} // if (MODULE_BOXES_RECENTLY_VIEWED_NAME_LENGTH
        			$recently_box_text .= '<a href="' . tep_href_link ('product_info.php', 'products_id=' . $products_data[$products_id]['id']) . '">' . $productname . '</a><br />';
        		} //if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_NAME

        		// Show the products description if selected in Admin
        		if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_DESCRIPTION == 'True') {
        			$description = $products_data[$products_id]['description'];
        			if (MODULE_BOXES_RECENTLY_VIEWED_DESCRIPTION_LENGTH > 0) {
        				$description_length = strlen ($description);
        				if ($description_length > MODULE_BOXES_RECENTLY_VIEWED_DESCRIPTION_LENGTH) {
        					$description = tep_recently_limit_text ($description, MODULE_BOXES_RECENTLY_VIEWED_DESCRIPTION_LENGTH, MODULE_BOXES_RECENTLY_VIEWED_WORD_LENGTH);
        					$description .= '<a href="' . tep_href_link ('product_info.php', 'products_id=' . $products_data[$products_id]['id']) . '">';
        					$description .= MODULE_BOXES_RECENTLY_VIEWED_SHOW_MORE;
        					$description .= '</a>';
        				} // if ($description_length
        			} // if (MAX_DISPLAY_RECENTLY_VIEWED_PAGE_DESCRIPTION_LENGTH
        			$recently_box_text .= '<small>' . $description . '</small><br />';
        		} //if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_DESCRIPTION

        		// Show the products price if selected in Admin
        		if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_PRICE == 'True') {
        			if (tep_not_null($products_data[$products_id]['specials_price'])) {
        				$recently_box_text .= '<del>' . $currencies->display_price($products_data[$products_id]['price'], tep_get_tax_rate($products_data[$products_id]['tax_class_id'])) . '</del>&nbsp;';
        				$recently_box_text .= '<span class="productSpecialPrice">' . $currencies->display_price($products_data[$products_id]['specials_price'], tep_get_tax_rate($products_data[$products_id]['tax_class_id'])) . '<br />';
        			} else {
        				$recently_box_text .= $currencies->display_price($products_data[$products_id]['price'], tep_get_tax_rate($products_data[$products_id]['tax_class_id'])) . '<br />';
        			}
          	} //if (RECENTLY_VIEWED_BOX_SHOW_PRICE

          	if ( MODULE_BOXES_RECENTLY_VIEWED_MAX_DISPLAY_PRODUCTS > 1 )
          		$recently_box_text .= '<hr>';

          	$product_number++;
          	if ( $product_number == MODULE_BOXES_RECENTLY_VIEWED_MAX_DISPLAY_PRODUCTS ) break;
          } //foreach ($recently_viewed_array

          // Show the "More" button if set in Admin
        	if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_BUTTON == 'True') {
        		$recently_box_text .= tep_draw_button(MODULE_BOXES_RECENTLY_VIEWED_IMAGE_BUTTON_SEE_MORE, 'glyphicon glyphicon-triangle-right', tep_href_link('recently_viewed.php'), 'primary', null, 'btn-default btn-sm');
          } //if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_BUTTON)

      
          ob_start();
          	include(DIR_WS_MODULES . 'boxes/templates/recently_viewed.php');
          	$recently_box_text = ob_get_clean();

          	$oscTemplate->addBlock($recently_box_text, $this->group);
        } //if (count ($recently_viewed_array)
      } //  if (tep_session_is_registered
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_RECENTLY_VIEWED_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Current Version', 'MODULE_BOXES_RECENTLY_VIEWED_VERSION_INSTALLED', '3.3 BS', 'Version info. It is read only.', '6', '1', 'tep_version_readonly(', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Recently Viewed Box', 'MODULE_BOXES_RECENTLY_VIEWED_STATUS', 'True', 'Do you want to add the box to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_RECENTLY_VIEWED_CONTENT_PLACEMENT', 'Right Column', 'Should the box be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_RECENTLY_VIEWED_SORT_ORDER', '5100', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of products', 'MODULE_BOXES_RECENTLY_VIEWED_MAX_DISPLAY_PRODUCTS', '5', 'Maximum number of products to display in the recently viewed box.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show images', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_IMAGE', 'True', 'Show the product image in the Recently Viewed box?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show name', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_NAME', 'True', 'Show the product name in the Recently Viewed box?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Name length', 'MODULE_BOXES_RECENTLY_VIEWED_NAME_LENGTH', '23', 'Maximum number of characters of the product name to display in the recently viewed box (to the nearest word).', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show description in box', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_DESCRIPTION', 'False', 'Show the product description in the Recently Viewed box?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum word length', 'MODULE_BOXES_RECENTLY_VIEWED_WORD_LENGTH', '40', 'Maximum number of characters in a single word (Needed to prevent breaking box width)', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Description length', 'MODULE_BOXES_RECENTLY_VIEWED_DESCRIPTION_LENGTH', '250', 'The number of characters (to the nearest word) of the description to display in the recently viewed box (0 for all).', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show price', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_PRICE', 'True', 'Show the product price in the Recently Viewed box?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show button', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_BUTTON', 'True', 'Show the MORE button in the Recently Viewed box?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show the current product', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_CURRENT', 'False', 'Show the current product in the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
   }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_RECENTLY_VIEWED_VERSION_INSTALLED', 'MODULE_BOXES_RECENTLY_VIEWED_STATUS', 'MODULE_BOXES_RECENTLY_VIEWED_CONTENT_PLACEMENT', 'MODULE_BOXES_RECENTLY_VIEWED_SORT_ORDER', 'MODULE_BOXES_RECENTLY_VIEWED_MAX_DISPLAY_PRODUCTS', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_IMAGE', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_NAME', 'MODULE_BOXES_RECENTLY_VIEWED_NAME_LENGTH', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_DESCRIPTION', 'MODULE_BOXES_RECENTLY_VIEWED_WORD_LENGTH', 'MODULE_BOXES_RECENTLY_VIEWED_DESCRIPTION_LENGTH', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_PRICE', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_BUTTON', 'MODULE_BOXES_RECENTLY_VIEWED_SHOW_CURRENT');
    }
  }
