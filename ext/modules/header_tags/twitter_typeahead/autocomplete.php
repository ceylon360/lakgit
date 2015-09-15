<?php
/*
  $Id$ Twitter Typeahead Autocomplete Search v1.2 for oscommerce 2.3.4BS

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

chdir('../../../../');
require('includes/application_top.php');

$return_array = array();

if (isset($_GET['term'])) {

  $search_term = tep_db_prepare_input($_GET['term']);

  if (tep_not_null($search_term)) {
    if (tep_parse_search_string($search_term, $term)) {

      if (isset($term) && (sizeof($term) > 0)) {

        $where_str = " and (";
        for ($i=0, $n=sizeof($term); $i<$n; $i++ ) {
          switch ($term[$i]) {
            case '(':
            case ')':
            case 'and':
            case 'or':
              $where_str .= " " . $term[$i] . " ";
              break;
            default:
              $keyword = tep_db_prepare_input($term[$i]);
              $where_str .= "(pd.products_name like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%'" . (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_INCLUDE_MANUFACTURER == 'true' ? " or m.manufacturers_name like '%" . tep_db_input($keyword) . "%'" : "") . ")";
              break;
          }
        }
        $where_str .= " )";

        $results_query = tep_db_query("select p.products_id, pd.products_name, p.products_image, p.products_tax_class_id, p.products_model, s.status, s.specials_new_products_price, p.products_price" . (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_INCLUDE_MANUFACTURER == 'true' ? ", m.manufacturers_name" : "") . " from products p" . (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_INCLUDE_MANUFACTURER == 'true' ? " left join manufacturers m using(manufacturers_id)" : "") . " left join specials s on p.products_id = s.products_id, products_description pd WHERE p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' $where_str order by pd.products_name asc");

        if (tep_db_num_rows($results_query)) {
          while ($results = tep_db_fetch_array($results_query)) {

            if($results['status'] == 1 ) {
              $results_array['price'] = '<span style="color: #575757; font-size: 13px;"><del>' . $currencies->display_price( $results['products_price'], tep_get_tax_rate( $results['products_tax_class_id'] ) ) . '</del>';
              $results_array['price'] .= '<span style="color: #DA2C0D; font-size: 13px;"> ' . $currencies->display_price( $results['specials_new_products_price'], tep_get_tax_rate( $results['products_tax_class_id'] ) ) . '</span></span>';
            } else {
              $results_array['price'] = '<span style="font-size: 13px;">' . $currencies->display_price( $results['products_price'], tep_get_tax_rate( $results['products_tax_class_id'] ) ) . '</span>';
            }

            if (tep_not_null($results['products_image'])) {
              $results_array['img'] = DIR_WS_IMAGES . $results['products_image'];
            } else {
              $results_array['img'] = (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_DISPLAY_NO_IMAGE_ICON == 'true' ? DIR_WS_IMAGES . 'no_img.png' : '');
            }

            if (tep_not_null($results['products_model'])) {
              $results_array['model'] = $results['products_model'];
            } else {
              $results_array['model'] = '';
            }

            if (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_INCLUDE_MANUFACTURER == 'true') {
              $results_array['manufacturer'] = (tep_not_null($results['manufacturers_name']) ? $results['manufacturers_name'] : '');
            } else {
              $results_array['manufacturer'] = '';
            }

            $results_array['name'] = $results['products_name'];
            $results_array['link'] = tep_href_link('product_info.php', 'products_id=' . $results['products_id'], $request_type);
            $results_array['pid'] = $results['products_id'];
            array_push($return_array, $results_array);

          }

          print json_encode($return_array);
        }

      }

    }
  }

}

require(DIR_WS_INCLUDES . 'application_bottom.php');

?>
