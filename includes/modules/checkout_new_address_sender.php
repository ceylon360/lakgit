<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (!isset($process)) $process = false;
  
  

?>

  <div class="contentText">

<?php /*
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
      $female = ($gender == 'f') ? true : false;
    } else {
      $male = false;
      $female = false;
    }
?>

    <div class="form-group">
      <label class="control-label col-sm-3"><?php echo ENRY_GIFT_DELIVER; ?></label>
      <div class="col-sm-9">
        <label class="radio-inline">
          <?php echo tep_draw_radio_field('gender', 'm', $male, 'required aria-required="true" aria-describedby="atGender"') . ' ' .'Yes'; ?>
        </label>
        <label class="radio-inline">
          <?php echo tep_draw_radio_field('gender', 'f', $female) . ' '. 'No'; ?>
        </label>
        <?php if (tep_not_null(ENTRY_GENDER_TEXT)) echo '<span id="atGender" class="help-block">' . ENTRY_GENDER_TEXT . '</span>'; ?>
      </div>
    </div>

<?php
  }*/
?>

    <div class="form-group">
      <label for="inputFirstName" class="control-label col-sm-3"><?php echo ENTRY_FIRST_NAME; ?></label>
      <div class="col-sm-4">
        <?php
        echo tep_draw_input_field('firstname', NULL, 'id="inputFirstName" aria-describedby="atFirstName" placeholder="' . ENTRY_FIRST_NAME . '"');
        if (tep_not_null(ENTRY_FIRST_NAME_TEXT)) echo '<span id="atFirstName" class="help-block">' . ENTRY_FIRST_NAME_TEXT . '</span>';
        ?>
      </div>
		<div class="col-sm-5">
			<?php
				echo tep_draw_input_field('lastname', NULL, 'id="inputLastName" aria-describedby="atLastName" placeholder="' . ENTRY_LAST_NAME . '"');
				if (tep_not_null(ENTRY_LAST_NAME_TEXT)) echo '<span id="atLastName" class="help-block">' . ENTRY_LAST_NAME_TEXT . '</span>';
			?>
		</div>
    </div>




	<!--
	  <div class="form-group">
		  <label for="inputStreet" class="control-label col-sm-3"><?php echo ENTRY_STREET_ADDRESS; ?></label>
		  <div class="col-sm-9">
			  <?php //  echo tep_draw_input_field('street_address', NULL, 'id="inputStreet" aria-describedby="atStreetAddress" placeholder="' . ENTRY_STREET_ADDRESS . '"');
				  echo tep_draw_textarea_field('street_address','soft', 30, 5, NULL, 'id="inputStreet" aria-describedby="atStreetAddress" placeholder="' . ENTRY_STREET_ADDRESS . '"');
				  if (tep_not_null(ENTRY_STREET_ADDRESS_TEXT)) echo '<span id="atStreetAddress" class="help-block">' . ENTRY_STREET_ADDRESS_TEXT . '</span>';
			  ?>
		  </div>
	  </div>-->

<?php
 /* if (ACCOUNT_SUBURB == 'true') {
?>

    <div class="form-group">
      <label for="inputSuburb" class="control-label col-sm-3"><?php echo ENTRY_SUBURB; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_input_field('suburb', NULL, 'id="inputSuburb" aria-describedby="atSuburb" placeholder="' . ENTRY_SUBURB . '"');
        if (tep_not_null(ENTRY_SUBURB_TEXT)) echo '<span id="atSuburb" class="help-block">' . ENTRY_SUBURB_TEXT . '</span>';
        ?>
      </div>
    </div>

<?php
  }*/
?>

  <!--  <div class="form-group">
      <label for="inputCity" class="control-label col-sm-3"><?php echo ENTRY_CITY; ?></label>
      <div class="col-sm-9">
        <?php/*
        echo tep_draw_input_field('city', NULL, 'id="inputCity" aria-describedby="atCity" placeholder="' . ENTRY_CITY. '"');
        if (tep_not_null(ENTRY_CITY_TEXT)) echo '<span id="atCity" class="help-block">' . ENTRY_CITY_TEXT . '</span>';
        */?>
      </div>
    </div>
    <div class="form-group">
      <label for="inputZip" class="control-label col-sm-3"><?php// echo ENTRY_POST_CODE; ?></label>
      <div class="col-sm-9">
        <?php/*
        echo tep_draw_input_field('postcode', NULL, 'id="inputZip" aria-describedby="atZip" placeholder="' . ENTRY_POST_CODE . '"');
        if (tep_not_null(ENTRY_POST_CODE_TEXT)) echo '<span id="atZip" class="help-block">' . ENTRY_POST_CODE_TEXT . '</span>';
        */?>
      </div>
    </div>-->

<div class="form-group has-feedback">
      <label for="inputCountry" class="control-label col-sm-3"><?php echo ENTRY_COUNTRY; ?></label>
      <div class="col-sm-9">
        <?php
        if ( $country == '' && MODULE_HEADER_TAGS_GET_STATES_DEFAULT_COUNTRY == 'True') {
          $country = STORE_COUNTRY;
        }
        echo tep_get_country_list('country', $country, 'onChange="getState(this.value)" required aria-required="true" id="inputCountry"');
        if (tep_not_null(ENTRY_COUNTRY_TEXT)) echo '<span class="help-block">' . ENTRY_COUNTRY_TEXT . '</span>';
        ?>
      </div>
    </div>

<!--
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

    <div class="form-group">
      <label for="inputCompany" class="control-label col-sm-3"><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_input_field('company', NULL, 'id="inputCompany" aria-describedby="atCompany" placeholder="' . ENTRY_TELEPHONE_NUMBER . '"');
        if (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT)) echo '<span id="atCompany" class="help-block">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>';
        ?>
      </div>
    </div>

<?php
  }
?>
-->
	</div>
