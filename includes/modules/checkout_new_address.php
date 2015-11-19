<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (!isset($process)) $process = false;
  
  
  
  //////////////////
$any_rest = 0;
if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
		if (tep_get_products_catrest($order->products[$i]['id'])==1) {
				if($any_rest < 1){
						$any_rest = 1;
				}
			
		}
		if (tep_get_products_catrest($order->products[$i]['id'])==2) {
			if($any_rest < 2){
						$any_rest = 2;
				}
		}
		if (tep_get_products_catrest($order->products[$i]['id'])==3) {
			if($any_rest < 3){
						$any_rest = 3;
				}
		}
		$catn= tep_get_products_catrest_p($order->products[$i]['id'],$any_rest);
		if(tep_not_null($catn)){
		$catrname=$catn;		
		}
    }
    // Out of Stocks
 
}
//for alert box
$rest_msg='';

switch ($any_rest)
{
	case 1:
	$rest_msg=TEXT_RESTRICTION_ALL;
	$restrict_gzone=110;
	break;
	
	case 2:
	$rest_msg=TEXT_RESTRICTION_CITIES;
	$restrict_gzone=102;
	break;
	
	case 3:
	$rest_msg=TEXT_RESTRICTION_COLOMBO_ONLY;
	$restrict_gzone=103;
	break;
}
//////////////////
?>

  <div class="contentText">

<?php 
?>

    <div class="form-group">
      <label for="inputFirstName" class="control-label col-sm-3"><?php echo ENTRY_FIRST_NAME; ?></label>
      <div class="col-sm-4">
        <?php
        echo tep_draw_input_field('firstname', NULL, 'id="inputFirstName" required aria-required="true" aria-describedby="atFirstName" placeholder="' . ENTRY_FIRST_NAME . '"');
        if (tep_not_null(ENTRY_FIRST_NAME_TEXT)) echo '<span id="atFirstName" class="help-block">' . ENTRY_FIRST_NAME_TEXT . '</span>';
        ?>
      </div>
		<div class="col-sm-5">
			<?php
				echo tep_draw_input_field('lastname', NULL, 'id="inputLastName" required aria-required="true" aria-describedby="atLastName" placeholder="' . ENTRY_LAST_NAME . '"');
				if (tep_not_null(ENTRY_LAST_NAME_TEXT)) echo '<span id="atLastName" class="help-block">' . ENTRY_LAST_NAME_TEXT . '</span>';
			?>
		</div>
    </div>




	
	  <div class="form-group">
		  <label for="inputStreet" class="control-label col-sm-3"><?php echo ENTRY_STREET_ADDRESS; ?></label>
		  <div class="col-sm-9">
			  <?php //  echo tep_draw_input_field('street_address', NULL, 'id="inputStreet" aria-describedby="atStreetAddress" placeholder="' . ENTRY_STREET_ADDRESS . '"');
				  echo tep_draw_textarea_field('street_address','soft', 30, 5, NULL, 'id="inputStreet" required aria-required="true" aria-describedby="atStreetAddress" placeholder="' . ENTRY_STREET_ADDRESS . '"');
				  if (tep_not_null(ENTRY_STREET_ADDRESS_TEXT)) echo '<span id="atStreetAddress" class="help-block">' . ENTRY_STREET_ADDRESS_TEXT . '</span>';
			  ?>
		  </div>
	  </div>

<?php

?>

 

<div class="form-group has-feedback">
      <label for="inputCountry" class="control-label col-sm-3"><?php echo ENTRY_COUNTRY; ?></label>
      <div class="col-sm-9">
        <?php
        if ( $country == '' && MODULE_HEADER_TAGS_GET_STATES_DEFAULT_COUNTRY == 'True') {
          $country = STORE_COUNTRY;
        }
        echo tep_get_country_list('country', $country, 'onChange="getState(this.value)"  id="inputCountry" disabled');
        if (tep_not_null(ENTRY_COUNTRY_TEXT)) echo '<span class="help-block">' . ENTRY_COUNTRY_TEXT . '</span>';
        ?>
      </div>
    </div>

<?php
if($any_rest==3 || $any_rest==2){
	echo '<div class="notice notice-warning col-sm-offset-3">'.TEXT_RESTRICTION_P_MSG.'  <strong>'.$catrname.'</strong>. '.TEXT_RESTRICTION_D_MSG.' <strong>'.$rest_msg.'</strong>'.TEXT_RESTRICTION_E_MSG.'</div>';
}

  if (ACCOUNT_STATE == 'true') {
?>
    <div class="form-group has-feedback">
      <label for="inputState" class="control-label col-sm-3"><?php echo ENTRY_STATE; ?></label>
      <div id="results" class="col-sm-9">
        <?php
		
		

		
		
		////
		$bzones_array = array();
        $bzones_array[0] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);  
		$bzone_query = tep_db_query("select p.zone_id, pd.zone_name from " . TABLE_ZONES_TO_GEO_ZONES . " p, " . TABLE_ZONES . " pd where p.geo_zone_id = '" . $restrict_gzone . "' and p.zone_id = pd.zone_id    ");
			while ($bzones_values = tep_db_fetch_array($bzone_query)) {
            	$bzones_array[] = array('id' => $bzones_values['zone_name'], 'text' => $bzones_values['zone_name']);
            }
		
 
		
		
		echo tep_draw_pull_down_menu('state', $bzones_array, '', 'id="inputState"');
		///////////////////////////////////////
		
		
		
		
     ?>
	  
      </div>
    </div>
<?php
  }
?>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

    <div class="form-group">
      <label for="inputCompany" class="control-label col-sm-3"><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_input_field('company', NULL, 'id="inputCompany" required aria-required="true" aria-describedby="atCompany" placeholder="' . ENTRY_TELEPHONE_NUMBER . '"');
        if (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT)) echo '<span id="atCompany" class="help-block">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>';
        ?>
      </div>
    </div>

<?php
  }
?>

	</div>
