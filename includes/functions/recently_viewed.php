<?php
/*
  $Id: recently_viewed.php
  $Loc: catalog/includes/functions/

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  2015 Recently Viewed 3.2r1 BS by @raiwa info@sarplataygemas.com
  based on 2.0 2008-10-28 Kymation $

  Copyright (c) 2008 osCommerce
  modified for BS by @raiwa
  Released under the GNU General Public License
*/

// begin recently_viewed
// Limit the size of text blocks to the nearest full word
//    $text is string to be truncated, 
//    $maxchar is number of characters to limit to,
//    $wordlength is maximum length of a single word, to avoid long words breaking linewrap
  function tep_recently_limit_text ($text, $maxchar, $wordlength = 40) {
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

  if(!function_exists('tep_version_readonly')) {
        function tep_version_readonly($value){
          $version_text = '<br>Version ' . $value;
          return $version_text;
        }
  }
?>
