<?php
/*
  $Id: recently_viewed.php
  $Loc: catalog/includes/modules/header_tags/

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  2015 Recently Viewed 3.2r4 BS by @raiwa info@sarplataygemas.com
  based on 2.0 2008-10-28 Kymation $

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class ht_recently_viewed {
    var $code = 'ht_recently_viewed';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_recently_viewed() {
      $this->title = MODULE_HEADER_TAGS_RECENTLY_VIEWED_TITLE;
      $this->description = MODULE_HEADER_TAGS_RECENTLY_VIEWED_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_RECENTLY_VIEWED_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $PHP_SELF, $_SESSION, $recently_viewed_string, $product_check;

      if (tep_not_null(MODULE_HEADER_TAGS_RECENTLY_VIEWED_PAGES)) {
        $pages_array = array();

        foreach (explode(';', MODULE_HEADER_TAGS_RECENTLY_VIEWED_PAGES) as $page) {
          $page = trim($page);

          if (!empty($page)) {
            $pages_array[] = $page;
          }
        }

        if (in_array(basename($PHP_SELF), $pages_array)) { // register only if page is selected
        
        	// begin recently_viewed
        	// Creates/updates a session variable -- a string of products IDs separated by commas
        	// IDs are in order newest -> oldest
        	$recently_viewed_string = '';
        	if ($product_check['total'] > 0) { //We don't want to add products that don't exist/are not available

        		if (!tep_session_is_registered('recently_viewed')) {
        			tep_session_register('recently_viewed');
        		} else {
        			$recently_viewed_string = $_SESSION['recently_viewed'];
        		}

        		// Deal with sessions created by the previous version
        		if (substr_count ($recently_viewed_string, ';') > 0) {
        			$_SESSION['recently_viewed'] = '';
        			$recently_viewed_string = '';
        		}
  
        		// We only want a product to display once, so check that the product is not already in the session variable
        		$products_id = (int) $_GET['products_id'];
        		if ($recently_viewed_string == '') { // No other products
        			$recently_viewed_string = (string) $products_id; // So set the variable to the current products ID
        		} else {
        			$recently_viewed_array = explode (',', $recently_viewed_string);
        			if (in_array ($products_id, $recently_viewed_array) ) {
        				$recently_viewed_string = preg_replace('%\b,' . $products_id . '\b%', null, $recently_viewed_string); // remove visited products ID and put in first place in the next step
        			}
        			$recently_viewed_array = explode (',', $recently_viewed_string);
        			if (!in_array ($products_id, $recently_viewed_array) ) { //Check if the products ID is already at the beginning of the variable
        				$recently_viewed_string = $products_id . ',' . $recently_viewed_string; //Add the products ID to the beginning of the variable
        			}
        		}

        		$_SESSION['recently_viewed'] = $recently_viewed_string;
        		
        	} //if ($product_check['total']
        	// end recently_viewed
	      } // end if page is in array
      } // end if page array is not empty
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Recently Viewed Module', 'MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS', 'True', 'Do you want to enable the Recently Viewed module? Required for all other Recently Viewed Modules.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Pages', 'MODULE_HEADER_TAGS_RECENTLY_VIEWED_PAGES', '" . implode(';', $this->get_default_pages()) . "', 'The pages to add the script to.', '6', '0', 'ht_recently_viewed_show_pages', 'ht_recently_viewed_edit_pages(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_RECENTLY_VIEWED_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_RECENTLY_VIEWED_STATUS', 'MODULE_HEADER_TAGS_RECENTLY_VIEWED_PAGES', 'MODULE_HEADER_TAGS_RECENTLY_VIEWED_SORT_ORDER');
    }

    function get_default_pages() {
      return array('product_info.php',
      						 'product_reviews.php');
    }
  }

  function ht_recently_viewed_show_pages($text) {
    return nl2br(implode("\n", explode(';', $text)));
  }

  function ht_recently_viewed_edit_pages($values, $key) {
    global $PHP_SELF;

    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
    $files_array = array();
	  if ($dir = @dir(DIR_FS_CATALOG)) {
	    while ($file = $dir->read()) {
	      if (!is_dir(DIR_FS_CATALOG . $file)) {
	        if (substr($file, strrpos($file, '.')) == $file_extension) {
            $files_array[] = $file;
          }
        }
      }
      sort($files_array);
      $dir->close();
    }

    $values_array = explode(';', $values);

    $output = '';
    foreach ($files_array as $file) {
      $output .= tep_draw_checkbox_field('ht_recently_viewed_file[]', $file, in_array($file, $values_array)) . '&nbsp;' . tep_output_string($file) . '<br />';
    }

    if (!empty($output)) {
      $output = '<br />' . substr($output, 0, -6);
    }

    $output .= tep_draw_hidden_field('configuration[' . $key . ']', '', 'id="htrn_files"');

    $output .= '<script>
                function htrn_update_cfg_value() {
                  var htrn_selected_files = \'\';

                  if ($(\'input[name="ht_recently_viewed_file[]"]\').length > 0) {
                    $(\'input[name="ht_recently_viewed_file[]"]:checked\').each(function() {
                      htrn_selected_files += $(this).attr(\'value\') + \';\';
                    });

                    if (htrn_selected_files.length > 0) {
                      htrn_selected_files = htrn_selected_files.substring(0, htrn_selected_files.length - 1);
                    }
                  }

                  $(\'#htrn_files\').val(htrn_selected_files);
                }

                $(function() {
                  htrn_update_cfg_value();

                  if ($(\'input[name="ht_recently_viewed_file[]"]\').length > 0) {
                    $(\'input[name="ht_recently_viewed_file[]"]\').change(function() {
                      htrn_update_cfg_value();
                    });
                  }
                });
                </script>';

    return $output;
  }
?>
