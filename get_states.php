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
tep_redirect(tep_href_link('account_pwa.php', '', 'SSL'));

$state = ($_GET['st']);
     $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$state . "'");
     $check = tep_db_fetch_array($check_query);
     $entry_state_has_zones = ($check['total'] > 0);
	 $zones_array2 = array();
      $zones_array2[] = array('id' => 'Gampaha', 'text' => 'Gampaha');
	  $zones_array2[] = array('id' => 'Ragama', 'text' => 'Ragama');
	  $zones_array2[] = array('id' => 'Galle', 'text' => 'Galle');
	  
     if ($entry_state_has_zones == false) {
     	 $zones_array = array();
       $zones_array[0] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);                        
       $zones_query = tep_db_query("select zone_name from zones where zone_country_id = '196' order by zone_name");
       while ($zones_values = tep_db_fetch_array($zones_query)) {
       	 $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
       }
	   
	   
/////
		 function udiffCompare($a, $b)
		 {
			 return $a['id'] == $b['id'] ? 0 : -1;
		 }
		 
		 $arrdiff = array_udiff($zones_array, $zones_array2, 'udiffCompare');
		 
		// echo '<pre>';
		 //print_r($arrdiff);

		 
       echo tep_draw_pull_down_menu('state', $arrdiff, '', 'required aria-required="true" id="inputState"');
       echo FORM_REQUIRED_INPUT;
     } else {
     	 echo tep_draw_input_field('state', NULL, 'id="inputState" class="form-control" required aria-required="true" placeholder="' . ENTRY_STATE    . '"');
     	 echo FORM_REQUIRED_INPUT;
     }
?>
