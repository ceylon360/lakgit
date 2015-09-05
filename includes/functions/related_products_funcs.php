<?php
/*
  $Id: optional_related_products.php, ver 5.0 08/03/2015 Exp $

  Copyright (c) 2015 Rainer Schmied @raiwa (info@sarplataygemas.com)

  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  Based on: recently_viewed.php 2.0 2008-10-28 Kymation $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/
// begin related products
// Limit the size of text blocks to the nearest full word
//    $text is string to be truncated, 
//    $maxchar is number of characters to limit to,
//    $wordlength is maximum length of a single word, to avoid long words breaking linewrap
  function tep_limit_text_rel_prod ($text, $maxchar, $wordlength = 40) {
    $text = str_replace ("\n", ' ', $text);
    $text = str_replace ("\r", ' ', $text);
    $text = str_replace ('<br>', ' ', $text);
    $text = wordwrap ($text, $wordlength, ' ', true);
    $text = preg_replace ("/[ ]+/", ' ', $text);
    $text_length = strlen ($text);
    $text_array = explode (" ", $text);

    $newtext = '';
    for ($array_key = 0, $length = 0; $length <= $text_length; $array_key++) {
      $length = strlen ($newtext) + strlen ($text_array[$array_key]) + 1;
      if ($length > $maxchar) break;
      $newtext = $newtext . ' ' . $text_array[$array_key];
    }

    return $newtext;
  } // function tep_limit_text

  function tep_get_products_model($product_id) {
    $product_query = tep_db_query("select products_model from products where products_id = '" . (int)$product_id . "'");
    $product = tep_db_fetch_array($product_query);

    return $product['products_model'];
  }


