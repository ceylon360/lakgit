<?php
/*
  $Id: fp_new_products.php v1.3 20130513 Kymation $
  Most of the execute() code is from the stock osCommerce New Products module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class fp_new_products {
    var $code = 'fp_new_products';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function fp_new_products() {
      $this->title = MODULE_FRONT_PAGE_NEW_PRODUCTS_TITLE;
      $this->description = MODULE_FRONT_PAGE_NEW_PRODUCTS_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_NEW_PRODUCTS_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $languages_id, $language, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the text to display on the front page
        $new_prods_content = '<!-- New Products BOF -->' . "\n";
        if( constant( 'MODULE_FRONT_PAGE_NEW_PRODUCTS_TITLE_' . strtoupper( $language ) ) != '') {
          $new_prods_content .= '  <h2>' . sprintf( constant( 'MODULE_FRONT_PAGE_NEW_PRODUCTS_TITLE_' . strtoupper( $language ) ), strftime('%B')) . '</h2>' . "\n";
        }
        $new_prods_content .= '  <div class="contentText">' . "\n";

        if ((!isset ($new_products_category_id)) || ($new_products_category_id == '0')) {
          $new_products_query = tep_db_query( "select p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' order by p.products_date_added desc limit " . MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY);
        } else {
          $new_products_query = tep_db_query( "select distinct p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . (int) $new_products_category_id . "' and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' order by p.products_date_added desc limit " . MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY);
        }

        $col = 0;

        $new_prods_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n";
        while ($new_products = tep_db_fetch_array($new_products_query)) {
      $new_prods_content .= '<div class="col-sm-6 col-md-4">';
      $new_prods_content .= '  <div class="thumbnail equal-height">';
      $new_prods_content .= '    <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $new_products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $new_products['products_image'], $new_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>';
      $new_prods_content .= '    <div class="caption">';
      $new_prods_content .= '      <p class="text-center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $new_products['products_id']) . '">' . $new_products['products_name'] . '</a></p>';
      $new_prods_content .= '      <hr>';
      $new_prods_content .= '      <p class="text-center">' . $currencies->display_price($new_products['products_price'], tep_get_tax_rate($new_products['products_tax_class_id'])) . '</p>';
      $new_prods_content .= '      <div class="text-center">';
      $new_prods_content .= '        <div class="btn-group">';
      $new_prods_content .= '          <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'products_id=' . $new_products['products_id']) . '" class="btn btn-default" role="button">' . SMALL_IMAGE_BUTTON_VIEW . '</a>';
      $new_prods_content .= '          <a href="' . tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $new_products['products_id']) . '" class="btn btn-success" role="button">' . SMALL_IMAGE_BUTTON_BUY . '</a>';
      $new_prods_content .= '        </div>';
      $new_prods_content .= '      </div>';
      $new_prods_content .= '    </div>';
      $new_prods_content .= '  </div>';
      $new_prods_content .= '</div>';
    }

        $new_prods_content .= '    </table>' . "\n";
        $new_prods_content .= '  </div>' . "\n";
        $new_prods_content .= '<!-- New Products EOF -->' . "\n"; 

        $oscTemplate->addBlock($new_prods_content, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS');
    }

    function install() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['directory'];
      }

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable New Products', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS', 'True', 'Do you want to show the New Products box on the front page?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_SORT_ORDER', '35', 'Sort order of display. Lowest is displayed first.', '6', '1', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max New Products', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY', '6', 'How many New Products do you want to show on the front page?', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Columns', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_COLUMNS', '3', 'Number of columns of products to show', '6', '3', now())");

      foreach( $this->languages_array as $language_name ) {
        tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ( '" . ucwords( $language_name ) . " Title', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_TITLE_" . strtoupper( $language_name ) . "', 'Title %s', 'Enter the title that you want on your box in " . $language_name . " (%s inserts the current month).', '6', '14', now())" );
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

      $keys[] = 'MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS';
      $keys[] = 'MODULE_FRONT_PAGE_NEW_PRODUCTS_SORT_ORDER';
      $keys[] = 'MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY';
      $keys[] = 'MODULE_FRONT_PAGE_NEW_PRODUCTS_COLUMNS';

      foreach( $this->languages_array as $language_name ) {
        $keys[] = 'MODULE_FRONT_PAGE_NEW_PRODUCTS_TITLE_' . strtoupper( $language_name );
      }

      return $keys;
    }
  }

?>
