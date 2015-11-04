<?php
/*
  $Id: optional_related_products.php, ver 5.4 27 of July 2015 Exp $

  Copyright (c) 2015 Rainer Schmied @raiwa (info@sarplataygemas.com)
  based on: 
  cm_pi_reviews.php
  and 
  cm_pi_also_purchased_products by Matt Toste @mattjt83
  
  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  Copyright (c) 2004-2005 Daniel Bahna (daniel.bahna@gmail.com)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/

  class cm_pi_related_products {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_pi_related_products() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_TITLE;
      $this->description = MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_DESCRIPTION;

      if ( defined('MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_STATUS == 'True');
      require_once(DIR_WS_FUNCTIONS . 'related_products_funcs.php');
      }
    }

    function execute() {
      global $oscTemplate, $HTTP_GET_VARS, $languages_id, $currencies, $PHP_SELF;
      
// include the related products functions
      require_once(DIR_WS_FUNCTIONS . 'related_products_funcs.php');

      $content_width          = (int)MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_CONTENT_WIDTH;
      $optional_rel_prods_content = NULL;
      
  $orderBy = 'ORDER BY ';
  $orderBy .= (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_RANDOMIZE)?'rand()':'pop_order_id, pop_id';
  $orderBy .= (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_DISP)?' limit ' . MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_DISP:'';
  $attributes = "
         SELECT
         pop_products_id_slave,
         products_name,
         products_model,
         products_price,
         products_quantity,
         products_tax_class_id,
         products_image
         FROM 
         products_related_products, 
         products_description pa, 
         products pb
         WHERE pop_products_id_slave = pa.products_id
         AND pa.products_id = pb.products_id
         AND language_id = '" . (int)$languages_id . "'
         AND pop_products_id_master = '" . (int)$HTTP_GET_VARS['products_id'] . "'
         AND products_status='1' " . $orderBy;
  $attribute_query = tep_db_query($attributes);

  if (tep_db_num_rows($attribute_query)>0) {
  
  $optional_rel_prods_content = '<br /><h3>' . MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_TITLE . '</h3>';
  $optional_rel_prods_content .= '<div class="row">';

  while ($attributes_values = tep_db_fetch_array($attribute_query)) {

      $products_name_slave = ($attributes_values['products_name']);
      if (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_CHARACTERS > 0) {
        
        $products_name_slave_length = strlen ($products_name_slave);
        if ($products_name_slave_length > MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_CHARACTERS) {
          $products_name_slave = tep_limit_text_rel_prod ($products_name_slave, MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_CHARACTERS, MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_WORD_LENGTH);
          $products_name_slave .= '...';
        } // if ($name_length
      } // if (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_WORD_LENGTH

      $products_model_slave = ($attributes_values['products_model']);
      $products_qty_slave = ($attributes_values['products_quantity']);
      $products_id_slave = ($attributes_values['pop_products_id_slave']);
      if ($new_price = tep_get_products_special_price($products_id_slave)) {
        $products_price_slave = $currencies->display_price($new_price, tep_get_tax_rate($attributes_values['products_tax_class_id']));
      } else {
        $products_price_slave = $currencies->display_price($attributes_values['products_price'], tep_get_tax_rate($attributes_values['products_tax_class_id']));
      }
            
      $optional_rel_prods_content .= '  <div class="col-sm-6 col-md-12 lowMargin">';

      switch (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_HEIGHT_MODE) {
        case 'Equal Height':
        	$optional_rel_prods_content .= '    <div class="thumbnail equal-height">';
        break;
        case 'Fixed Height':
        	$optional_rel_prods_content .= '    <div class="thumbnail" style = "height:' . MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_HEIGHT_VALUE . 'em;">';
        break;
        case 'None':
        	$optional_rel_prods_content .= '    <div class="thumbnail">';
        break;
      }

      // show thumb image if Enabled
      if (MODULE_CONTENT_RELATED_PRODUCTS_SHOW_THUMBS == 'True') {
        $optional_rel_prods_content .= '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_id_slave) . '">' . tep_image(DIR_WS_IMAGES . $attributes_values['products_image'], $attributes_values['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>';
      }
      
      $optional_rel_prods_content .= '      <div class="caption">';

      if (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_NAME == 'True') {
        $optional_rel_prods_content .= '<p class="text-center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_id_slave) . '">' . $products_name_slave . '</a></p>';
       // $optional_rel_prods_content .= '       <div class="col-xs-6 text-right">' . tep_draw_button(IMAGE_BUTTON_BUY_NOW, 'cart', tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'sort', 'cPath')) . 'action=buy_now&products_id=' . $listing['products_id']), NULL, NULL, 'btn-success btn-sm') . '</div>';
		if (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_PRICE == 'True') {
        $optional_rel_prods_content .= '<p class="text-center">' . sprintf(MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_PRICE_TEXT, $products_price_slave) . '</p>';
      }
	   $optional_rel_prods_content .= '       <div class="text-center">' . tep_draw_button(IMAGE_BUTTON_BUY_NOW, 'cart', tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'sort', 'cPath')) . 'action=buy_now&products_id=' . $products_id_slave), NULL, NULL, 'btn-success btn-sm') . '</div>';
		if (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_MODEL == 'True') {
          $optional_rel_prods_content .= '<small>' . sprintf(MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MODEL_COMBO, $products_model_slave) . '</small></h5>';
		  
        } else {
          $optional_rel_prods_content .= '</h5>';
        }	

      } elseif (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_MODEL == 'True') {
        $optional_rel_prods_content .=  '<h5 class="text-center small">' . $products_model_slave . '</h5>';
      }
    //  if (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_PRICE == 'True') {
       // $optional_rel_prods_content .= '<h5 class="text-center">' . sprintf(MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_PRICE_TEXT, $products_price_slave) . '</h5>';
     // }
      if (MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_QUANTITY == 'True') {
        $optional_rel_prods_content .= '<h5 class="text-center">' . sprintf(MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_QUANTITY_TEXT, $products_qty_slave) . '</h5>';
      }
         

      $optional_rel_prods_content .= '      </div>'; // caption
      $optional_rel_prods_content .= '    </div>'; // thumbnail
      $optional_rel_prods_content .= '  </div>'; // col-sm-6 col-md-4
    }
    $optional_rel_prods_content .=  '</div>'; // row
  }

	ob_start();
        include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/related_products.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Current Version', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_VERSION_INSTALLED', '5.0 BS', 'This key is used by the SQL install to automatically update your database during upgrades. It is read only.?', '6', '1', 'tep_version_readonly(', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Related Products Module', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_STATUS', 'True', 'Should the optional_related_products block be shown on the product info page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_CONTENT_WIDTH', '12', 'What width container should the content be shown in?', '6', '1', 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SORT_ORDER', '1100', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Thumbnail Image', 'MODULE_CONTENT_RELATED_PRODUCTS_SHOW_THUMBS', 'True', 'Show Product Image', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Product Name', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_NAME', 'True', 'Show Product Name', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Product Model', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_MODEL', 'False', 'Show Product Model', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Product Price', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_PRICE', 'True', 'Show Product Price', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Quantity Available', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_QUANTITY', 'False', 'Show Product Quantity', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Define Number of Items to Display', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_DISP', '0', 'Maximum number of Related Products to display. 0 is unlimited.', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum word length', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_WORD_LENGTH', '40', 'Maximum number of characters in a single word in Product Name', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum of characters', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_CHARACTERS', '36', 'Maximum number of characters of the product name to display', '6', '1', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Height mode', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_HEIGHT_MODE', 'Equal Height', 'How should the height of each product box be adjusted. \'Equal Height\' uses the Equal Height jquery script. \Fixed Height\ uses the Height specified in the next field. \'None\' adjusts the height depending on the content', '6', '1', 'tep_cfg_select_option(array(\'Equal Height\', \'Fixed Height\', \'None\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Fixed height value', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_HEIGHT_VALUE', '11', 'Fixed height for the product box in em. Has no effect for \'Equal Height\' and \'None\' mode.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Use Random Display Order', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_RANDOMIZE', 'False', 'Adds random sort order to products displayed. Recommended if maximum number of products is set.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Admin Display: Maximum Rows', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_ROW_LISTS_OPTIONS', '10', 'Sets the maximum number of rows to display per page.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Admin Display: Drop-Down List Maximum Length', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH', '0', 'Sets the maximum length (in characters) of product name displayed in drop-down lists. Enter \'0\' to set this option to false.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Admin Display: Display List Maximum Length', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_DISPLAY_LENGTH', '0', 'Sets the maximum length (in characters) of product name displayed in list. Enter \'0\' to set this option to false.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Admin Display: Use Product Model', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL', 'False', 'Uses Product Model in lists. When Product Name is also selected, Product Model is displayed first.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Admin Display: Use Product Name', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_NAME', 'True', 'Uses Product Name in lists. When Product Model is also selected, Product Model is displayed first.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Admin Display: Combine Model and Name separator', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR', ': ', 'Enter the characters you would like to separate Model from Name, when using both. Leave empty if only using Model.', '6', '0', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Admin Function: Use Delete Confirmation', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_CONFIRM_DELETE', 'True', 'When set to True, a confirmation box will pop-up when deleting an association. Set to False to Delete without confirmation.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Admin Function: Combine Insert with Inherit', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_INSERT_AND_INHERIT', 'False', 'When set to True, clicking on Inherit will also Insert the product association. When False, Inherit works as before.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }
    
    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_VERSION_INSTALLED', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_STATUS', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SORT_ORDER', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_CONTENT_WIDTH', 'MODULE_CONTENT_RELATED_PRODUCTS_SHOW_THUMBS', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_NAME', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_MODEL', 
                   'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_PRICE', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_SHOW_QUANTITY', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_DISP', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_WORD_LENGTH', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_MAX_CHARACTERS', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_HEIGHT_MODE', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_HEIGHT_VALUE', 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_RANDOMIZE', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_ROW_LISTS_OPTIONS', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH', 
                   'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_DISPLAY_LENGTH','MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_NAME', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_CONFIRM_DELETE', 'MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_INSERT_AND_INHERIT' );
    }
  }

