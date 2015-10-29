<?php
/*
  $Id get_states.php$

  Version 1.2
  	
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/
require('includes/application_top.php');


$state = ($_GET['st']);
     $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$state . "'");
     $check = tep_db_fetch_array($check_query);
     $entry_state_has_zones = ($check['total'] > 0);
      
     if ($entry_state_has_zones == true) {
     	 $zones_array = array();
       $zones_array[0] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);                        
       $zones_query = tep_db_query("select zone_name from zones where zone_country_id = '" . (int)$state . "' order by zone_name");
       while ($zones_values = tep_db_fetch_array($zones_query)) {
       	 $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
       }
       echo tep_draw_pull_down_menu('state', $zones_array, '', 'required aria-required="true" id="inputState"');
       echo FORM_REQUIRED_INPUT;
     } else {
     	 echo tep_draw_input_field('state', NULL, 'id="inputState" class="form-control" required aria-required="true" placeholder="' . ENTRY_STATE    . '"');
     	 echo FORM_REQUIRED_INPUT;
     }
?>
