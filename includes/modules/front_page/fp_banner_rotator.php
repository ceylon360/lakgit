<?php
/*
  $Id: fp_banner_rotator.php v1.2 20130513 Kymation $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class fp_banner_rotator {
    var $code = 'fp_banner_rotator';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function fp_banner_rotator() {
      $this->title = MODULE_FRONT_PAGE_BANNER_ROTATOR_TITLE;
      $this->description = MODULE_FRONT_PAGE_BANNER_ROTATOR_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_BANNER_ROTATOR_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_BANNER_ROTATOR_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_BANNER_ROTATOR_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the Javascript to go in the header
        $header = '<link rel="stylesheet" type="text/css" href="ext/modules/front_page/banner_rotator/stylesheet.css" />' . PHP_EOL;
        $header .= '<script type="text/javascript" src="ext/modules/front_page/banner_rotator/bannerRotator.js"></script>' . PHP_EOL;

        $oscTemplate->addBlock($header, 'header_tags');

        // Set the banner rotator code to display on the front page
        $banner_query_raw = "
                  select
                    banners_id,
                    banners_url,
                    banners_image,
                    banners_html_text
                  from
                    " . TABLE_BANNERS . "
                  where
                    banners_group = '" . MODULE_FRONT_PAGE_BANNER_ROTATOR_GROUP . "'
                  order by
                    " . MODULE_FRONT_PAGE_BANNER_ROTATOR_BANNER_ORDER . "
                  limit
                    " . (int)MODULE_FRONT_PAGE_BANNER_ROTATOR_MAX_DISPLAY;

        $banner_query = tep_db_query($banner_query_raw);
        if (tep_db_num_rows($banner_query) > 0) {
          $body_text = '<!-- Banner Rotator BOF -->' . PHP_EOL;
          $body_text .= '  <div id="bannerRotator">' . PHP_EOL;
          $body_text .= '    <ul>' . PHP_EOL;

          while ($banner = tep_db_fetch_array($banner_query)) {
            $body_text .= '      <li>';
            if ($banner['banners_url'] != '') {
              $body_text .= '<a href="' . tep_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner['banners_id']) . '" target="_self">';
            }

            $body_text .= tep_image(DIR_WS_IMAGES . $banner['banners_image'], $banner['banners_html_text']);

            if ($banner['banners_url'] != '') {
              $body_text .= '</a>';
            }

            $body_text .= ' </li>' . PHP_EOL;
          }

          $body_text .= '    </ul>' . PHP_EOL;
          $body_text .= '    <div id="bannerNav"></div>' . PHP_EOL;
          $body_text .= '  </div>' . PHP_EOL;
          $body_text .= '  <div class="grid_24 spacer" style="width:620px;"></div>' . PHP_EOL; // Width added to fix IE6 bug
          $body_text .= '<script type="text/javascript">' . PHP_EOL;
          $body_text .= '  jQuery(document).ready(function(){' . PHP_EOL;
          $body_text .= '    bannerRotator(\'#bannerRotator\', ' . ( int ) MODULE_FRONT_PAGE_BANNER_ROTATOR_FADE_TIME . ',  ' . ( int ) MODULE_FRONT_PAGE_BANNER_ROTATOR_HOLD_TIME . ' );' . PHP_EOL;
          $body_text .= '  });' . PHP_EOL;
          $body_text .= '</script>' . PHP_EOL;
          $body_text .= '  <div class="clear"></div>' . PHP_EOL;
          $body_text .= '<!-- Banner Rotator EOF -->' . PHP_EOL;

          $oscTemplate->addBlock( $body_text, $this->group );
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_BANNER_ROTATOR_STATUS');
    }

    function install() {
      $this->_load_header_tags_first();
      
    	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_BANNER_ROTATOR_SORT_ORDER', '20', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Banner Rotator', 'MODULE_FRONT_PAGE_BANNER_ROTATOR_STATUS', 'True', 'Do you want to show the banner rotator?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Fade Time', 'MODULE_FRONT_PAGE_BANNER_ROTATOR_FADE_TIME', '500', 'The time it takes to fade from one banner to the next. 1000 = 1 second', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Hold Time', 'MODULE_FRONT_PAGE_BANNER_ROTATOR_HOLD_TIME', '4000', 'The time each banner is shown. 1000 = 1 second', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Banner Order', 'MODULE_FRONT_PAGE_BANNER_ROTATOR_BANNER_ORDER', 'banners_id', 'Order that the Banner Rotator uses to show the banners.', '6', '0', 'tep_cfg_select_option(array(\'banners_id\', \'rand()\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Banner Rotator Group', 'MODULE_FRONT_PAGE_BANNER_ROTATOR_GROUP', 'rotator', 'Name of the banner group that the Banner Rotator uses to show the banners.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Banner Rotator Max Banners', 'MODULE_FRONT_PAGE_BANNER_ROTATOR_MAX_DISPLAY', '4', 'Maximum number of banners that the Banner Rotator will show', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array (
        'MODULE_FRONT_PAGE_BANNER_ROTATOR_STATUS',
        'MODULE_FRONT_PAGE_BANNER_ROTATOR_SORT_ORDER',
        'MODULE_FRONT_PAGE_BANNER_ROTATOR_FADE_TIME',
        'MODULE_FRONT_PAGE_BANNER_ROTATOR_HOLD_TIME',
        'MODULE_FRONT_PAGE_BANNER_ROTATOR_BANNER_ORDER',
        'MODULE_FRONT_PAGE_BANNER_ROTATOR_GROUP',
        'MODULE_FRONT_PAGE_BANNER_ROTATOR_MAX_DISPLAY'
      );
    }
    
    // Force the header tags to load first, so the jQuery and jQuery UI code is available 
    //   to other scripts that load in the head section
    function _load_header_tags_first() {
      // If header_tags is not the first item on the list
      if( substr( TEMPLATE_BLOCK_GROUPS, 0, 11 ) != 'header_tags' ) {
        // Remove header_tags from wherever it is in the list
        $template_block_groups = str_replace( ';header_tags', '', TEMPLATE_BLOCK_GROUPS );
        // And add header_tags back onto the front of the list
        $template_block_groups = 'header_tags;' . $template_block_groups;
        $sql_data_array = array( 'configuration_value' => $template_block_groups );
        // Update the database with the fixed string
        tep_db_perform( TABLE_CONFIGURATION, $sql_data_array, 'update', "configuration_key = 'TEMPLATE_BLOCK_GROUPS'" );
      } // if( substr
    } // function _load_header_tags_first
    
  }

?>