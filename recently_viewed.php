<?php
/*
  $Id: recently_viewed.php
  $Loc: catalog/

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  2015 Recently Viewed BS by @raiwa info@sarplataygemas.com
  based on 2.0 2008-10-28 Kymation $

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require_once ('includes/application_top.php');

  require_once (DIR_WS_LANGUAGES . $language . '/recently_viewed.php');

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link('recently_viewed.php'));

  require(DIR_WS_INCLUDES . 'template_top.php');

  $error = '';

// Check that the customer has viewed some products
  if (tep_session_is_registered ('recently_viewed') && strlen ($_SESSION['recently_viewed']) > 0) { 
    $recently_viewed_string = $_SESSION['recently_viewed'];

// Deal with sessions created by the previous version
    if (substr_count ($recently_viewed_string, ';') > 0) {
      $_SESSION['recently_viewed'] = '';
      $recently_viewed_string = '';
    }

    // Turn the string of product IDs into an array in the correct order
    $recently_viewed_string = strtr ($recently_viewed_string, ',,', ','); // Remove blank values
    $recently_viewed_array = explode (',', $recently_viewed_string); // Array is in order newest first
    
// create column list
  $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                       'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                       'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                       'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                       'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                       'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                       'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                       'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

  asort($define_list);

  $column_list = array();
  reset($define_list);
  while (list($key, $value) = each($define_list)) {
    if ($value > 0) $column_list[] = $key;
  }

  $column_list[] = 'PRODUCT_LIST_ID';

  $select_column_list = '';

  for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
    switch ($column_list[$i]) {
      case 'PRODUCT_LIST_MODEL':
        $select_column_list .= 'p.products_model, ';
        break;
      case 'PRODUCT_LIST_NAME':
        $select_column_list .= 'pd.products_name, ';
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $select_column_list .= 'm.manufacturers_name, ';
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $select_column_list .= 'p.products_quantity, ';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $select_column_list .= 'p.products_image, ';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $select_column_list .= 'p.products_weight, ';
        break;
    }
  }

  $listing_sql = "select " . $select_column_list . " p.products_id, SUBSTRING_INDEX(pd.products_description, ' ', 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id where p.products_id in (" . $recently_viewed_string . ") and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'";

  if ( (!isset($HTTP_GET_VARS['sort'])) || (!preg_match('/^[1-8][ad]$/', $HTTP_GET_VARS['sort'])) || (substr($HTTP_GET_VARS['sort'], 0, 1) > sizeof($column_list)) ) {
    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      if ($column_list[$i] == 'PRODUCT_LIST_ID') {
        $HTTP_GET_VARS['sort'] = $i+1 . 'd';
        $listing_sql .= " order by p.products_id DESC";
        break;
      }
    }
  } else {
    $sort_col = substr($HTTP_GET_VARS['sort'], 0 , 1);
    $sort_order = substr($HTTP_GET_VARS['sort'], 1);

    switch ($column_list[$sort_col-1]) {
      case 'PRODUCT_LIST_MODEL':
        $listing_sql .= " order by p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_NAME':
        $listing_sql .= " order by pd.products_name " . ($sort_order == 'd' ? 'desc' : '');
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $listing_sql .= " order by m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $listing_sql .= " order by p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_IMAGE':
        $listing_sql .= " order by pd.products_name";
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $listing_sql .= " order by p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_PRICE':
        $listing_sql .= " order by final_price " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_ID':
        $listing_sql .= " order by p.products_id " . ($sort_order == 'd' ? 'desc' : '');
        break;
    }
  }

      

    if (count ($listing_sql) == 0) { // Show message if we don't have any products in the array
      $error = ERROR_NO_PRODUCTS_VIEWED;
    }

  } else {  // Show message if we don't have a session or variable is empty
    $error = ERROR_NO_PRODUCTS_VIEWED;
  }
  
?>
<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>
<?php
// Show "no products" message if we have no products in the array or there are errors set
  if (count ($recently_viewed_array) == 0 || strlen ($error) > 0) { 
?>
            <?php echo TEXT_NO_PRODUCTS_VIEWED; ?>
<?php
  } else {

  include(DIR_WS_MODULES . 'product_listing.php');
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
 ?>
