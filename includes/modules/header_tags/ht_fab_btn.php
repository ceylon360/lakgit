<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright © 2015 osCommerce

  Released under the GNU General Public License
*/

  class ht_fab_btn {
    var $code = 'ht_fab_btn';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_fab_btn() {
      $this->title = MODULE_HEADER_TAGS_FAB_BTN_TITLE;
      $this->description = MODULE_HEADER_TAGS_FAB_BTN_DESCRIPTION;
      
      if ( defined('MODULE_HEADER_TAGS_FAB_BTN_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_FAB_BTN_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_FAB_BTN_STATUS == 'True');
      }
    }

     function execute() {
       global  $oscTemplate;

$vertical_position = MODULE_HEADER_TAGS_FAB_BTN_VERTICAL_POSITION;
$speed = MODULE_HEADER_TAGS_FAB_BTN_SCROLL_SPEED;

$output = <<<EOD
    <script>
$(document).ready(function(){
      $('body').append('<div class="floatingContainer"><div class="subActionButton person"><p class="floatingText"><span class="floatingTextBG">Add Contact</span></p></div><div class="subActionButton mapMarker"><p class="floatingText"><span class="floatingTextBG">Add Address</span></p></div><div class="subActionButton note"><p class="floatingText"><span class="floatingTextBG">Add Note</span></p></div><div class="actionButton"><p class="floatingText"><span class="floatingTextBG">Add Customer</span></p></div></div><div class="toasts"><p class="floatingText">Added Note</p></div>');
  $('.floatingContainer').hover(function(){
  //$('.subActionButton').addClass('display');
}, function(){
  $('.subActionButton').removeClass('display');
  $('.actionButton').removeClass('open');
});
$('.subActionButton').hover(function(){
  $(this).find('.floatingText').addClass('show');
}, function(){
  $(this).find('.floatingText').removeClass('show');
});

$('.actionButton').hover(function(){
  $(this).addClass('open');
  $(this).find('.floatingText').addClass('show');
  $('.subActionButton').addClass('display');
}, function(){
  $(this).find('.floatingText').removeClass('show');
});
    
});
</script>   
EOD;

      $oscTemplate->addBlock($output, $this->group);
    }    
    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_FAB_BTN_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Back to Top Module', 'MODULE_HEADER_TAGS_FAB_BTN_STATUS', 'True', 'Do you want to enable the Back to top module?', '6', '', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_FAB_BTN_SORT_ORDER', '1500', 'Sort order of display. Lowest is displayed first.', '6', '', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vertical Position', 'MODULE_HEADER_TAGS_FAB_BTN_VERTICAL_POSITION', '150', 'Defines where, when scrolling down, the button is displayed. Default 150.', '6', '', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Scroll Speed ', 'MODULE_HEADER_TAGS_FAB_BTN_SCROLL_SPEED', '500', 'Defines the scrolling up speed in ms. Low value = fast - high value =slow. Default 500.', '6', '', now())");
    
    
    
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_FAB_BTN_STATUS', 'MODULE_HEADER_TAGS_FAB_BTN_SORT_ORDER', 'MODULE_HEADER_TAGS_FAB_BTN_VERTICAL_POSITION', 'MODULE_HEADER_TAGS_FAB_BTN_SCROLL_SPEED');
    }
  }

?>