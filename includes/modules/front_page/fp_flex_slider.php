<?php
/*
  $Id: fp_flex_slider.php v1.1.5 20120314 Bruyndoncx $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
  
  Adaptation from banner_rotator by Kymation
*/

  class fp_flex_slider {
    var $code = 'fp_flex_slider';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function fp_flex_slider() {
      $this->title = MODULE_FRONT_PAGE_FLEX_SLIDER_TITLE;
      $this->description = MODULE_FRONT_PAGE_FLEX_SLIDER_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_FLEX_SLIDER_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_FLEX_SLIDER_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_FLEX_SLIDER_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $cPath, $languages_id;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the Javascript to go in the header
        $header = '<link rel="stylesheet" type="text/css" href="ext/modules/front_page/flex_slider/flexslider.css" />' . PHP_EOL;
        $header .= '<script type="text/javascript" src="ext/modules/front_page/flex_slider/jquery.flexslider-min.js"></script>' . PHP_EOL;
        $header .= '<script type="text/javascript">' . PHP_EOL;
        $header .= '  $(document).ready(function(){' . PHP_EOL;
        $header .= "    $('.flexslider').flexslider({" . PHP_EOL;
        $header .= '        animation: "'. MODULE_FRONT_PAGE_FLEX_SLIDER_ANIMATION .'",' . PHP_EOL;
        $header .= '        slideshowSpeed: ' . (int) MODULE_FRONT_PAGE_FLEX_SLIDER_SLIDESHOWSPEED . ', '. PHP_EOL;
        $header .= '        animationSpeed: ' . (int) MODULE_FRONT_PAGE_FLEX_SLIDER_ANIMATIONSPEED .', '. PHP_EOL;
        $header .= '        smoothHeight: ' . MODULE_FRONT_PAGE_FLEX_SLIDER_SMOOTHHEIGHT . PHP_EOL ;
        $header .= '      });' . PHP_EOL; 
        $header .= '  });' . PHP_EOL;
        $header .= '</script>' . PHP_EOL;
          
        $oscTemplate->addBlock($header, 'header_tags');

        // Set the slider code to display on the front page
        $banner_query_raw = "
                  select
                    banners_id,
                    banners_url,
                    banners_image,
                    banners_html_text
                  from
                    " . TABLE_BANNERS . "
                  where
                    banners_group = '" . MODULE_FRONT_PAGE_FLEX_SLIDER_GROUP . "'
                    and status = 1
                  order by
                    " . MODULE_FRONT_PAGE_FLEX_SLIDER_BANNER_ORDER . "
                  limit
                    " . (int)MODULE_FRONT_PAGE_FLEX_SLIDER_MAX_DISPLAY;

        $banner_query = tep_db_query($banner_query_raw);
        if (tep_db_num_rows($banner_query) > 0) {
          $body_text = '<!-- Flex Slider BOF -->' . PHP_EOL;
          $body_text .= '  <div class="flexslider clearboth" style="max-width: 100%;">' . PHP_EOL;
          $body_text .= '    <ul class="slides">' . PHP_EOL;

          while ($banner = tep_db_fetch_array($banner_query)) {
            $body_text .= '      <li>';
            if ($banner['banners_url'] != '') {
              $body_text .= '<a href="' . tep_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner['banners_id']) . '" target="_self">';
            }

            $body_text .= tep_image(DIR_WS_IMAGES . $banner['banners_image'], $banner['banners_html_text']);
            if ($banner['banners_html_text'] != '') $body_text .= '<div class="announcement">'.$banner['banners_html_text'] .'</div>'. PHP_EOL;
            if ($banner['banners_url'] != '') {
              $body_text .= '</a>';
            }        

            $body_text .= ' </li>' . PHP_EOL;
          }

          $body_text .= '    </ul>' . PHP_EOL;
          $body_text .= '  </div>' . PHP_EOL;
        
          $body_text .= '  <div class="clear"></div>' . PHP_EOL;
          $body_text .= '<!-- Flex Slider EOF -->' . PHP_EOL;

          $oscTemplate->addBlock( $body_text, $this->group );
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_FLEX_SLIDER_STATUS');
    }

    function install() {
      $this->_load_header_tags_first();
      
    	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_FLEX_SLIDER_SORT_ORDER', '45', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Flex Slider', 'MODULE_FRONT_PAGE_FLEX_SLIDER_STATUS', 'True', 'Do you want to show the flex slider?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Animation', 'MODULE_FRONT_PAGE_FLEX_SLIDER_ANIMATION', 'slide', 'Animation effect to use for the Flex Slider.', '6', '1', 'tep_cfg_select_option(array(\'slide\', \'fade\'), ',now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('slideshowSpeed', 'MODULE_FRONT_PAGE_FLEX_SLIDER_SLIDESHOWSPEED', '7000', 'The speed of the slideshow cycling, in milliseconds. 1000 = 1 second', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('animationSpeed', 'MODULE_FRONT_PAGE_FLEX_SLIDER_ANIMATIONSPEED', '600', 'The speed of the animation in milliseconds. 1000 = 1 second', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable smoothHeight', 'MODULE_FRONT_PAGE_FLEX_SLIDER_SMOOTHHEIGHT', 'true', 'Do you want to use smoothHeight on the flex slider?', '6', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Banner Order', 'MODULE_FRONT_PAGE_FLEX_SLIDER_BANNER_ORDER', 'banners_id', 'Order that the Flex Slider uses to show the banners.', '6', '0', 'tep_cfg_select_option(array(\'banners_id\', \'banners_id desc \', \'rand()\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Banner Rotator Group', 'MODULE_FRONT_PAGE_FLEX_SLIDER_GROUP', 'slider', 'Name of the banner group that the Flex Slider uses to show the banners.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Banner Rotator Max Banners', 'MODULE_FRONT_PAGE_FLEX_SLIDER_MAX_DISPLAY', '4', 'Maximum number of banners that the Flex Slider will show', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array (
        'MODULE_FRONT_PAGE_FLEX_SLIDER_STATUS',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_SORT_ORDER',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_ANIMATION',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_SLIDESHOWSPEED',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_ANIMATIONSPEED',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_SMOOTHHEIGHT',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_BANNER_ORDER',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_GROUP',
        'MODULE_FRONT_PAGE_FLEX_SLIDER_MAX_DISPLAY'
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