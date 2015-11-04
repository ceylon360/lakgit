<?php
/*
  $Id: recently_viewed.php
  $Loc: catalog/includes/modules/content/product_info/

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  2015 Recently Viewed 3.3 BS by @raiwa info@sarplataygemas.com
  based on 2.0 2008-10-28 Kymation $

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_pi_recently_viewed {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_pi_recently_viewed() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_TITLE;
      $this->description = MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DESCRIPTION;

      require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'recently_viewed.php');

      if ( defined('MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_STATUS == 'True');
      }
      
      if ( !defined('MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS') || MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS != 'True' ) {
      	$this->description = '<div class="secWarning">' . MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_ERROR_ADMIN_HT . '</div>' . $this->description;

        $this->enabled = false;
      }
    }

    function execute() {
      global $oscTemplate, $languages_id, $currencies, $_SESSION, $recently_viewed_string;

      // include the tep_recently_limit_text function
      require_once(DIR_WS_FUNCTIONS . 'recently_viewed.php');
      
      $content_width           = (int)MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_CONTENT_WIDTH;
      $recently_viewed_content = NULL;

      // Display recently viewed box only if the customer has viewed some products
      if (tep_session_is_registered ('recently_viewed') && strlen ($_SESSION['recently_viewed']) > 0) { 
      	$recently_viewed_string = $_SESSION['recently_viewed'];
      
      	// Deal with sessions created by the previous version
      	if (substr_count ($recently_viewed_string, ';') > 0) {
      		$_SESSION['recently_viewed'] = '';
      		$recently_viewed_string = '';
      	}
    
        // Retrieve the data on the products in the recently viewed list and load into an array by products_id
        $products_data = array();
    
      	$products_query = "select ";
      	$specials_query = '';
      		
        if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_DESCRIPTION == 'True')
        	$products_query .= "     pd.products_description,";
        
        if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_IMAGE == 'True')
          $products_query .= "      p.products_image,";
                                       
        if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_PRICE == 'True') {
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
          if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_DESCRIPTION == 'True')
          	$products_data[$products_id]['description'] = $products['products_description'];
												
          if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_IMAGE == 'True')
          	$products_data[$products_id]['image'] = $products['products_image'];
																			   
          if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_PRICE == 'True') {
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
        if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_CURRENT == 'False' && $products_id != 0) {
        	$current_key = array_search ($products_id, $recently_viewed_array);
        	unset ($recently_viewed_array[$current_key]);
        }

        // Limit the number of products shown as set in Admin
        $recently_viewed_array = array_slice ($recently_viewed_array, 0, MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_CONTENT_LIMIT);
    
        if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DISPLAY_ORDER == 'Oldest') { // Reverse the order if set in Admin
        	$recently_viewed_array = array_reverse ($recently_viewed_array);
        }

        // Set up the product data string in order by $recently_viewed_array
        if (count ($recently_viewed_array) > 0) { // Show only if we still have products in the array
    	    
        	// Recently viewed module title
        	$recently_viewed_content = '<br /><h3>' . MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_TITLE . ((MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_MORE_BUTTON == 'True')? '  ' . tep_draw_button(MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_IMAGE_BUTTON_SEE_MORE, 'glyphicon glyphicon-triangle-right', tep_href_link('recently_viewed.php'), 'primary', null, 'btn-default btn-sm'):'') . '</h3>';
        	$recently_viewed_content .= '<div class="row">';

        	foreach ($recently_viewed_array as $products_id) {
        		$recently_viewed_content .= '  <div class="col-sm-6 col-md-4 lowMargin">';

        		switch (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_HEIGHT_MODE) {
        		case 'Equal Height':
        			$recently_viewed_content .= '    <div class="thumbnail equal-height">';
        			break;
        		case 'Fixed Height':
        			$recently_viewed_content .= '    <div class="thumbnail" style = "height:' . MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_HEIGHT_VALUE . 'em;">';
        			break;
        		case 'None':
        			$recently_viewed_content .= '    <div class="thumbnail">';
        			break;
        		}

        		// Show the products image if selected in Admin
        		if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_IMAGE == 'True') {
        		  $recently_viewed_content .= '<a href="' . tep_href_link ('product_info.php', 'products_id=' . $products_data[$products_id]['id']) . '">' . tep_image (DIR_WS_IMAGES . $products_data[$products_id]['image'], $products_data[$products_id]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>';
        		} //if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_IMAGE

        		$recently_viewed_content .= '        <div class="caption">';

        		// Show the products name if selected in Admin
        		if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_NAME == 'True') {
        			$productname = $products_data[$products_id]['name'];
        			if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_NAME_LENGTH > 0) {
        				$productname_length = strlen ($productname);
        				if ($productname_length > MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_NAME_LENGTH) {
        					$productname = tep_recently_limit_text ($productname, MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_NAME_LENGTH, MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_WORD_LENGTH) . '&nbsp;...';
        				} // if ($name_length
        			} // if (MODULE_BOXES_RECENTLY_VIEWED_NAME_LENGTH
        			$recently_viewed_content .= '<p class="text-center"><a href="' . tep_href_link ('product_info.php', 'products_id=' . $products_data[$products_id]['id']) . '">' . $productname . '</a></p>';
        		} //if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_NAME
        		
        		// Show the products description if selected in Admin
        		if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_DESCRIPTION == 'True') {
        			$description = $products_data[$products_id]['description'];
        			if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DESCRIPTION_LENGTH > 0) {
        				$description_length = strlen ($description);
        				if ($description_length > MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DESCRIPTION_LENGTH) {
        					$description = tep_recently_limit_text ($description, MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DESCRIPTION_LENGTH, MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_WORD_LENGTH);
        					$description .= '<a href="' . tep_href_link ('product_info.php', 'products_id=' . $products_data[$products_id]['id']) . '">';
        					$description .= MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_MORE;
        					$description .= '</a>';
        				} // if ($description_length
        			} // if (MAX_DISPLAY_RECENTLY_VIEWED_PAGE_DESCRIPTION_LENGTH
        			$recently_viewed_content .= '<p class="text-center">' . $description . '</p>';
        		} //if (MODULE_BOXES_RECENTLY_VIEWED_SHOW_DESCRIPTION

        		// Show the products price if selected in Admin
        		if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_PRICE == 'True') {
        			$recently_viewed_content .= '<p class="text-center">';
        			if (tep_not_null($products_data[$products_id]['specials_price'])) {
        				$recently_viewed_content .= '<del>' . $currencies->display_price($products_data[$products_id]['price'], tep_get_tax_rate($products_data[$products_id]['tax_class_id'])) . '</del>';
        				$recently_viewed_content .= '<span class="productSpecialPrice">' . $currencies->display_price($products_data[$products_id]['specials_price'], tep_get_tax_rate($products_data[$products_id]['tax_class_id'])) . '</span><br />';
        			} else {
        				$recently_viewed_content .= $currencies->display_price($products_data[$products_id]['price'], tep_get_tax_rate($products_data[$products_id]['tax_class_id'])) . '<br />';
        			}
        			$recently_viewed_content .= '</p>';
        		} //if (MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_PRICE
        		$recently_viewed_content .= '      <div class="text-center">';
        			$recently_viewed_content .= '        <div class="btn-group">';
        			$recently_viewed_content .= '          <a href="' . tep_href_link('product_info.php', tep_get_all_get_params(array('action')) . 'products_id=' . $products_data[$products_id]['id']) . '" class="btn btn-default" role="button">' . SMALL_IMAGE_BUTTON_VIEW . '</a>';
        		    
					$recently_viewed_content .= '        </div>';
        			$recently_viewed_content .= '      </div>';
        		$recently_viewed_content .= '      </div>'; // caption
        		$recently_viewed_content .= '    </div>'; // thumbnail
        		$recently_viewed_content .= '  </div>'; // col-sm-6 col-md-4
        	} //foreach ($recently_viewed_array
        	$recently_viewed_content .= '</div>'; // row

        	ob_start();
        	include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/recently_viewed.php');
        	$template = ob_get_clean();

        	$oscTemplate->addContent($template, $this->group);
        } //if (count ($recently_viewed_array)
      } //  if (tep_session_is_registered
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Current Version', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_VERSION_INSTALLED', 'Version 3.3 BS', 'Version info. It is read only.', '6', '1', 'tep_version_readonly(', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Recently Viewed Module', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_STATUS', 'True', 'Should the recently_viewed block be shown on the product info page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_CONTENT_WIDTH', '12', 'What width container should the content be shown in?', '6', '1', 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SORT_ORDER', '1200', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Products', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_CONTENT_LIMIT', '6', 'How many recently viewed products should be shown?', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show the product image', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_IMAGE', 'True', 'Show the product image in the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show the product name', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_NAME', 'True', 'Show the product name in the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum word length', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_WORD_LENGTH', '40', 'Maximum number of characters in a single word (Needed to prevent breaking box width)', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum of characters', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_NAME_LENGTH', '32', 'Maximum number of characters of the product name to display in the recently_viewed module (to the nearest word)?', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show the product description', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_DESCRIPTION', 'False', 'Show the product description in the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum description length', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DESCRIPTION_LENGTH', '250', 'The number of characters (to the nearest word) of the description to display in the recently viewed module', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show the product price', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_PRICE', 'True', 'Show the product price in the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show the product MORE button', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_MORE_BUTTON', 'True', 'Show the MORE button in the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show the current product', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_CURRENT', 'False', 'Show the current product in the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Product order', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DISPLAY_ORDER', 'Newest', 'Show the oldest or newest product at the beginning of the Recently Viewed module?', '6', '1', 'tep_cfg_select_option(array(\'Newest\', \'Oldest\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Height mode', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_HEIGHT_MODE', 'Equal Height', 'How should the height of each product box be adjusted. \'Equal Height\' uses the Equal Height jquery script. \Fixed Height\ uses the Height specified in the next field. \'None\' adjusts the height depending on the content', '6', '1', 'tep_cfg_select_option(array(\'Equal Height\', \'Fixed Height\', \'None\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Fixed height value', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_HEIGHT_VALUE', '11', 'Fixed height for the product box in em. Has no effect for \'Equal Height\' and \'None\' mode.', '6', '0', now())");
    }
    
    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_VERSION_INSTALLED', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_STATUS', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_CONTENT_WIDTH', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SORT_ORDER', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_CONTENT_LIMIT', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_IMAGE', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_NAME', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_WORD_LENGTH', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_NAME_LENGTH', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_DESCRIPTION', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DESCRIPTION_LENGTH', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_PRICE', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_MORE_BUTTON', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_SHOW_CURRENT', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_DISPLAY_ORDER', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_HEIGHT_MODE', 'MODULE_CONTENT_PRODUCT_INFO_RECENTLY_VIEWED_HEIGHT_VALUE');
    }
  }

