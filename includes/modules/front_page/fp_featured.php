<?php
/*
  $Id: fp_featured.php v1.3 20130513 Kymation $
  Most of the execute() code is from the stock osCommerce New Products module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class fp_featured {
    var $code = 'fp_featured';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    var $count;
    // Set the number of featured products in case the constant is not defined
    var $featured_products = 10;

    function fp_featured() {
      global $PHP_SELF;

      $this->title = MODULE_FRONT_PAGE_FEATURED_TITLE;
      $this->description = MODULE_FRONT_PAGE_FEATURED_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_FEATURED_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_FEATURED_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_FEATURED_STATUS == 'True');
        $this->count = MODULE_FRONT_PAGE_FEATURED_MAX_DISPLAY + 1;
      }

      // Include the function that is used to add products in the Admin
      if ($PHP_SELF == 'modules.php') {
        include_once (DIR_WS_FUNCTIONS . 'modules/front_page/featured.php');
      }

    	if( defined( 'MAX_DISPLAY_FEATURED_PRODUCTS' ) ) {
        $this->featured_products = MAX_DISPLAY_FEATURED_PRODUCTS;
    	}
    }

    function execute() {
      global $oscTemplate, $languages_id, $language, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the text to display on the front page
        $featured__content = '<!-- Featured Products BOF -->' . PHP_EOL;
        
        if( constant( 'MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE_' . strtoupper( $language ) ) != '') {
          $featured__content .= '  <h2>' . constant( 'MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE_' . strtoupper( $language ) ) . '</h2>' . PHP_EOL;
        }
        
        $featured__content .= '  <div class="contentText">' . PHP_EOL;
        $featured__content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . PHP_EOL;

        $col = 0;
        for ($id = 1; $id < $this->count; $id++) {
          $products_id = @ constant('MODULE_FRONT_PAGE_FEATURED_PRODUCT_' . $id);
          if ($products_id > 0) {
            $featured_products_query_raw = "
                        select
                          p.products_id,
                          pd.products_name,
                          p.products_price,
                          p.products_tax_class_id,
                          p.products_image,
                          s.specials_new_products_price,
                          s.status
                        from
                          " . TABLE_PRODUCTS . " p
                          join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            on pd.products_id = p.products_id
                          left join " . TABLE_SPECIALS . " s
                            on s.products_id = p.products_id
                        where
                          p.products_id = '" . $products_id . "'
                          and pd.language_id = '" . ( int ) $languages_id . "'
                      ";
            // print 'Featured Query: ' . $featured_products_query_raw . '<br />';
            $featured_products_query = tep_db_query($featured_products_query_raw);
            $featured_products = tep_db_fetch_array($featured_products_query);

            // Format the price for the correct currency
            if ($featured_products['status'] == 1) {
              $products_price = '<del>' . $currencies->display_price($featured_products['products_price'], tep_get_tax_rate($featured_products['products_tax_class_id'])) . '</del><br />';
              $products_price .= '<span class="productSpecialPrice">' . $currencies->display_price($featured_products['specials_new_products_price'], tep_get_tax_rate($featured_products['products_tax_class_id'])) . '</span>';
            } else {
              $products_price = $currencies->display_price($featured_products['products_price'], tep_get_tax_rate($featured_products['products_tax_class_id']));
              
			}

            if ($col == 0) {
              $featured__content .= '    <tr>' . PHP_EOL;
            }

            $width = (floor(100 / MODULE_FRONT_PAGE_FEATURED_COLUMNS));

            $featured__content .= '<div class="col-sm-6 col-md-3 lowMargin animated fadeInLeft">';
			$featured__content .= '  <div class="thumbnail equal-height">';
            $featured__content .= '          <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $featured_products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $featured_products['products_image'], $featured_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>' . PHP_EOL;
			$featured__content .= '    <div class="caption">';
            $featured__content .= '          <p class="text-center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $featured_products['products_id']) . '">' . $featured_products['products_name'] . '</a></p>' . PHP_EOL;
			$featured__content .= '      	<hr>';
            $featured__content .= '      	<p class="text-center">' . $currencies->display_price($featured_products['products_price'], tep_get_tax_rate($featured_products['products_tax_class_id'])) . '</p>';
			
			$featured__content .= '      	<p class="text-center">' . $currencies->display_price_lk($featured_products['products_price'], tep_get_tax_rate($featured_products['products_tax_class_id'])) . '</p>';
			
			$featured__content .= '      	<div class="text-center">';
			$featured__content .= '        	<div class="btn-group">';
			$featured__content .= '          <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'products_id=' . $featured_products['products_id']) . '" class="btn btn-default" role="button">' . SMALL_IMAGE_BUTTON_VIEW . '</a>';
			$featured__content .= '          <a href="' . tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $featured_products['products_id']) . '" class="btn btn-success" role="button">' . SMALL_IMAGE_BUTTON_BUY . '</a>';
			$featured__content .= '       	</div>' . PHP_EOL;
			$featured__content .= '      	</div>' . PHP_EOL;
            $featured__content .= '        </div>' . PHP_EOL;
			$featured__content .= '        </div>' . PHP_EOL;
			$featured__content .= '        </div>' . PHP_EOL;

            $col++;

            if ($col > (MODULE_FRONT_PAGE_FEATURED_COLUMNS - 1)) {
              $featured__content .= '    </tr>' . PHP_EOL;
              $col = 0;
            }
          }
        } // for( $id=1;

        $featured__content .= '    </table>' . PHP_EOL;
        $featured__content .= '  </div>' . PHP_EOL;
        $featured__content .= '<!-- Featured Products EOF -->' . PHP_EOL;

        $oscTemplate->addBlock($featured__content, $this->group);
      }
    } // function execute

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_FEATURED_STATUS');
    }

    function install() {
    	if( !defined( 'MAX_DISPLAY_FEATURED_PRODUCTS' ) ) {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max Featured Products', 'MAX_DISPLAY_FEATURED_PRODUCTS', '10', 'Set the maximum number of featured products to allow.', '6', '222', now())");
    	}

      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['directory'];
      }

    	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_FEATURED_SORT_ORDER', '30', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Featured Products', 'MODULE_FRONT_PAGE_FEATURED_STATUS', 'True', 'Do you want to show the Featured box on the front page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max Featured Products', 'MODULE_FRONT_PAGE_FEATURED_MAX_DISPLAY', '6', 'How many featured products do you want to show?', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Columns', 'MODULE_FRONT_PAGE_FEATURED_COLUMNS', '3', 'Number of columns of products to show', '6', '4', now())");

      foreach( $this->languages_array as $language_name ) {
        tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ( '" . ucwords( $language_name ) . " Title', 'MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE_" . strtoupper( $language_name ) . "', 'Title', 'Enter the title that you want on your box in " . $language_name . "', '6', '14', now())" );
      }
      
      for ($id = 1; $id <= $this->featured_products; $id++) {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Featured Product #" . $id . "', 'MODULE_FRONT_PAGE_FEATURED_PRODUCT_" . $id . "', '', 'Select featured product #" . $id . " to show', '6', '99', 'tep_cfg_pull_down_products(', now())");
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['directory'];
      }

    	$keys = array ();

    	$keys[] = 'MODULE_FRONT_PAGE_FEATURED_STATUS';
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_SORT_ORDER';
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_MAX_DISPLAY';
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_COLUMNS';

      foreach( $this->languages_array as $language_name ) {
        $keys[] = 'MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE_' . strtoupper( $language_name );
      }
      
      for ($id = 1; $id <= $this->featured_products; $id++) {
        $keys[] = 'MODULE_FRONT_PAGE_FEATURED_PRODUCT_' . $id;
      }

      return $keys;
    }
  }

?>