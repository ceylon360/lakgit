<?php
/*
  $Id: optional_related_products.php, ver 5.4 22/06/2015 Exp $

  Copyright (c) 2015 Rainer Schmied @raiwa (info@sarplataygemas.com)

  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/
      //Optional Related Products
      if(!function_exists('tep_version_readonly')) {
        function tep_version_readonly($value){
          $version_text = '<br>Version ' . $value;
          return $version_text;
        }
      }

        function tep_get_products_model($product_id) {
          $product_query = tep_db_query("select products_model from products where products_id = '" . (int)$product_id . "'");
          $product = tep_db_fetch_array($product_query);

          return $product['products_model'];
        }
?>
