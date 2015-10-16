<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

// the following cPath references come from application_top.php
  $category_depth = 'top';
  if (isset($cPath) && tep_not_null($cPath)) {
    $categories_products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
    $categories_products = tep_db_fetch_array($categories_products_query);
    if ($categories_products['total'] > 0) {
      $category_depth = 'products'; // display products
    } else {
     // $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "'");
	 //cat state
	 $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "' and status_categ = 1");
	//cat state
      $category_parent = tep_db_fetch_array($category_parent_query);
      if ($category_parent['total'] > 0) {
        $category_depth = 'nested'; // navigate through the categories
      } else {
        $category_depth = 'products'; // category has no products, but display the 'no products' message
      }
    }
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DEFAULT);

  require(DIR_WS_INCLUDES . 'template_top.php');

  if ($category_depth == 'nested') {
   // $category_query = tep_db_query("select cd.categories_name, c.categories_image from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
    //cat state
	$category_query = tep_db_query("select cd.categories_name, c.categories_image, c.categories_banner, cd.categories_description,cd.categories_note,cd.categories_note_sel from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and status_categ = 1 and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
	//cat state
	//, cd.categories_description // added above query for get categories description
	$category = tep_db_fetch_array($category_query);
?>

<!--<div class="page-header">
  <h2><?php echo $category['categories_name']; ?></h2>
</div> -->
<div class="contentContainer">
  <div class="contentText">
    <div class="row">
<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

	<div class="">
		<div class="col-md-12">
			<?php echo'<img class="bg_blur_lv" src="'.DIR_WS_IMAGES . $category['categories_banner'].'" height="200" width="200">' ?>
    	</div>
        <div class="col-md-12  col-xs-12">
			<?php echo'<img class="img-thumbnail picture_lv hidden-xs" src="'.DIR_WS_IMAGES . $category['categories_image'].'" height="200" width="200">' ?>
			<?php echo'<img class="img-thumbnail visible-xs picture_mob" src="'.DIR_WS_IMAGES . $category['categories_image'].'" height="200" width="200">' ?>
		   <div class="header_lv">
                <h1 class="hidden-xs cat_txt"><?php echo $category['categories_name']; ?></h1>
                <h1 class="visible-xs cat_txt2"><?php echo $category['categories_name']; ?></h1>
                <span class="cat_des"><?php
//cat description
if (tep_not_null($category['categories_description'])) {
  echo '<div class="hidden-xs cat_des">' . $category['categories_description'] . '</div>';
  echo '<div class="visible-xs cat_des2">' . $category['categories_description'] . '</div>';
}  
//cat description
?></span>

	<!--<div class="notice notice-danger">
        <strong>Notice</strong> Hilton Cakes are delivered only in Colombo and it's suburbs . 
    </div>-->
           </div>
		   <hr>
        </div>
    </div>
</hr>	



<?php
//cat description
//if (tep_not_null($category['categories_description'])) {
 // echo '<div class="well well-sm">' . $category['categories_description'] . '</div>';
//}  
//cat description
?>



	

<?php
    if (isset($cPath) && strpos('_', $cPath)) {
// check to see if there are deeper categories within the current category
      $category_links = array_reverse($cPath_array);
      for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
       // $categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
        //cat state
		$categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and status_categ = 1 and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
		//cat state
		$categories = tep_db_fetch_array($categories_query);
        if ($categories['total'] < 1) {
          // do nothing, go through the loop
        } else {
          //$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
          //cat state
		  $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and status_categ = 1 and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
		  //cat state
		  break; // we've found the deepest category the customer is in
        }
      }
    } else {
      //$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
    //cat state
	$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and status_categ = 1 and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
	//cat state
	}

    while ($categories = tep_db_fetch_array($categories_query)) {
      $cPath_new = tep_get_path($categories['categories_id']);
      echo '<div class="col-sm-6 col-md-2 lowMargin animated fadeInLeft">';
      echo '  <div class="text-center">';
      echo '    <a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $categories['categories_image'], $categories['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '</a>';
      echo '    <div class="caption text-center">';
      echo '      <h5><a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . $categories['categories_name'] . '</a></h5>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
    }

// needed for the new products module shown below
    $new_products_category_id = $current_category_id;
?>
      </div>

    <br />

<?php include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS); ?>

  </div>
</div>

<?php
  } elseif ($category_depth == 'products' || (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id']))) {
// create column list
    $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                         'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                         'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                         'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                         'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                         'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                         'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                         'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

    asort($define_list);

    $column_list = array();
    reset($define_list);
    while (list($key, $value) = each($define_list)) {
      if ($value > 0) $column_list[] = $key;
    }

    $select_column_list = '';

    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
          $select_column_list .= 'p.products_model, ';
          break;
        case 'PRODUCT_LIST_NAME':
          $select_column_list .= 'pd.products_name, ';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $select_column_list .= 'm.manufacturers_name, ';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $select_column_list .= 'p.products_quantity, ';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $select_column_list .= 'p.products_image, ';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $select_column_list .= 'p.products_weight, ';
          break;
      }
    }
//cat state
$ptoc = "";
    $hiddenlist = "";
    if (!empty($hiddencats)) {
      $ptoc = TABLE_PRODUCTS_TO_CATEGORIES . " p2c, ";
      $hiddenlist = " and (not (p2c.categories_id in (" . implode(',', $hiddencats) . ")))";
    }
//cat state
// show the products of a specified manufacturer
    if (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id'])) {
      if (isset($HTTP_GET_VARS['filter_id']) && tep_not_null($HTTP_GET_VARS['filter_id'])) {
// We are asked to show only a specific category
        $listing_sql = "select " . $select_column_list . " p.products_id, SUBSTRING_INDEX(pd.products_description, ' ', 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$HTTP_GET_VARS['filter_id'] . "'";
      } else {
// We show them all
        $listing_sql = "select " . $select_column_list . " p.products_id, SUBSTRING_INDEX(pd.products_description, ' ', 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . $ptoc . TABLE_MANUFACTURERS . " m where p.products_status = '1' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'" . ($ptoc != '' ? ' and p.products_id = p2c.products_id ' : '');
      }
    } else {
// show the products in a given categorie
      if (isset($HTTP_GET_VARS['filter_id']) && tep_not_null($HTTP_GET_VARS['filter_id'])) {
// We are asked to show only specific catgeory
        $listing_sql = "select " . $select_column_list . " p.products_id, SUBSTRING_INDEX(pd.products_description, ' ', 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['filter_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";
      } else {
// We show them all
        $listing_sql = "select " . $select_column_list . " p.products_id, SUBSTRING_INDEX(pd.products_description, ' ', 20) as products_description, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";
      }
    }
//cat state	
$listing_sql .= $hiddenlist;
//cat state

    if ( (!isset($HTTP_GET_VARS['sort'])) || (!preg_match('/^[1-8][ad]$/', $HTTP_GET_VARS['sort'])) || (substr($HTTP_GET_VARS['sort'], 0, 1) > sizeof($column_list)) ) {
      for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
        if ($column_list[$i] == 'PRODUCT_LIST_NAME') {
          $HTTP_GET_VARS['sort'] = $i+1 . 'a';
          $listing_sql .= " order by pd.products_name";
          break;
        }
      }
    } else {
      $sort_col = substr($HTTP_GET_VARS['sort'], 0 , 1);
      $sort_order = substr($HTTP_GET_VARS['sort'], 1);

      switch ($column_list[$sort_col-1]) {
        case 'PRODUCT_LIST_MODEL':
          $listing_sql .= " order by p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_NAME':
          $listing_sql .= " order by pd.products_name " . ($sort_order == 'd' ? 'desc' : '');
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $listing_sql .= " order by m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $listing_sql .= " order by p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_IMAGE':
          $listing_sql .= " order by pd.products_name";
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $listing_sql .= " order by p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_PRICE':
          $listing_sql .= " order by final_price " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
      }
    }

    $catname = HEADING_TITLE;
    if (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id'])) {
      // $image = tep_db_query("select manufacturers_image, manufacturers_name as catname from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'");
      //man description
	  $image = tep_db_query("select m.manufacturers_image, m.manufacturers_name as catname, mi.manufacturers_description as catdesc from manufacturers m, manufacturers_info mi where m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "'");
	  //man description
	  $image = tep_db_fetch_array($image);
      $catname = $image['catname'];
    } elseif ($current_category_id) {
      $image = tep_db_query("select c.categories_image, c.categories_banner, cd.categories_name as catname, cd.categories_description as catdesc,cd.categories_note,cd.categories_note_sel from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
      //, cd.categories_description as catdesc added //above code for categories_description
	  $image = tep_db_fetch_array($image);
      $catname = $image['catname'];
	  
    }
?>
	<div class="row">
		<div class="col-md-4">
			<!--<img class="bg_blur" src="http://www3.hilton.com/resources/media/hi/COLHITW/en_US/img/shared/full_page_image_gallery/main/HL_exterior_675x359_FitToBoxSmallDimension_Center.jpg" alt="">-->
			<?php echo'<img class="bg_blur" src="'.DIR_WS_IMAGES . $image['categories_banner'].'" height="200" width="200">' ?>
			</div>
        <div class="col-md-8  col-xs-12">
			<?php echo'<img class="img-thumbnail picture hidden-xs hidden-sm" src="'.DIR_WS_IMAGES . $image['categories_image'].'" height="200" width="200">' ?>
			<?php echo'<img class="img-thumbnail visible-xs visible-sm picture_mob" src="'.DIR_WS_IMAGES . $image['categories_image'].'" height="200" width="200">' ?>
		   <div class="header">
                <h1><?php echo $catname; ?></h1>
                
                <span class="cat_des"><?php
			//cat description
			if (tep_not_null($image['catdesc'])) {
			  echo '<div class="">' . $image['catdesc'] . '</div>';
			}
			//cat description
			?></span>
			   <?php 
			   $note_color='';
			   if (tep_not_null($image['categories_note'])) {
					
					switch($image['categories_note_sel']){
							
							case '1':
							$note_color='danger';
							break;
							
							case '2':
							$note_color='info';
							break;
							
							case '3':
							$note_color='success';
							break;
							
							case '4':
							$note_color='warning';
							break;
							
							default:
							$note_color='default';
							break;
					}
					
				   echo '<div class="notice notice-'.$note_color.'"><strong>Notice </strong>' . $image['categories_note'] . '</div>';
			   }?>
			 
           </div>
        </div>
    </div>   

			<!-- start sub categories buttons //-->	

<?php
    if (isset($cPath) && strpos($cPath, '_')) {
// check to see if there are deeper categories within the current category
      $category_links = array_reverse($cPath_array);
      for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
        $categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        if ($categories['total'] < 1) {
          // do nothing, go through the loop
        } else {
          $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
          break; // we've found the deepest category the customer is in
        }
      }
    } else {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
    }
	$category_name0 = '';
	if ($catname = $categories['categories_name']){
		$category_name0='<strong>'.$categories['categories_name'].'</strong>';
		
		}
		else{
			$category_name0=$categories['categories_name'];
			}
//my query for name of main cat
   $maincat_query = tep_db_query("select c.categories_id, cd.categories_name,c.categories_id, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
			  $maincat = tep_db_fetch_array($maincat_query);
      $maincat = $maincat['parent_id'];
	  
	  $maincatname_query = tep_db_query("select categories_id, categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . $maincat . "' and language_id = '" . (int)$languages_id . "'");
			  $maincatname = tep_db_fetch_array($maincatname_query);
	  $maincatname = $maincatname['categories_name'];
	  
 


echo '<div class="contentContainer  hidden-xs"><div class="row-centered">';

	while ($categories = tep_db_fetch_array($categories_query)) {
		$category_name0 = '';
		$cPath_new = tep_get_path($categories['categories_id']);
		if ($image['catname'] == $categories['categories_name']){
			
			$category_name0='<div class="col-xs-2 col-sm-1 col-md-1 lowMargin col-centered animated fadeInLeft caticon-selected" data-toggle="tooltip" title="'.$categories['categories_name'].'"><a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $categories['categories_image'], $categories['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '</a></div>' . "\n";
		}
		else{
			$category_name0='<div class="col-xs-2 col-sm-1 col-md-1 lowMargin col-centered animated fadeInLeft img-caticon" data-toggle="tooltip" title="'.$categories['categories_name'].'"><a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $categories['categories_image'], $categories['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '</a></div>' . "\n";
		}
		
		
		echo $category_name0;
	}


echo '</div></div>';

////////////////////////for mobile

	if (isset($cPath) && strpos($cPath, '_')) {
		// check to see if there are deeper categories within the current category
		$category_links = array_reverse($cPath_array);
		for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
			$categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
			$categories = tep_db_fetch_array($categories_query);
			if ($categories['total'] < 1) {
				// do nothing, go through the loop
			} else {
				$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
				break; // we've found the deepest category the customer is in
			}
		}
    } else {
		$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
    }
	
	 echo '  <nav class="navbar navbar-default visible-xs">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand hidden-md hidden-lg " href="#">Other '.$maincatname.' </a>
    </div>
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1"> ';
   
   
   
 echo '<ul class="nav navbar-nav">';
 
    while ($categories = tep_db_fetch_array($categories_query)) {
      $category_name0 = '';
	  $cPath_new = tep_get_path($categories['categories_id']);
	if ($image['catname'] == $categories['categories_name']){
		
		$category_name0='<li><a class=""href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '"> ' .$categories['categories_name']. '</a></li>' . "\n";
		}
		else{
		$category_name0='<li><a class=""href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '"> ' .$categories['categories_name']. '</a></li>' . "\n";
			}
      
      echo $category_name0;
       }
echo '</ul>';
echo '
</div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav> ';
//////////////////////



// needed for the new products module shown below
    $new_products_category_id = $current_category_id;
?>
             

<!-- end sub categories buttons //-->
<script>
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip(); 
	});
</script>
</br>
<!-- end sub categories buttons //-->

<?php
//cat description
//if (tep_not_null($image['catdesc'])) {
  //echo '<div class="well well-sm">' . $image['catdesc'] . '</div>';
//}
//cat description
?>
<div class="contentContainer">

<?php
// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0) {
      if (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id'])) {
        $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and c.status_categ = 1" . (!empty($hiddencats) ? " and (not (p2c.categories_id in (" . implode(',', $hiddencats) . ")))" : "") . " and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' order by cd.categories_name";
      } else {
        $filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";
      }
      $filterlist_query = tep_db_query($filterlist_sql);
      if (tep_db_num_rows($filterlist_query) > 1) {
        echo '<div>' . tep_draw_form('filter', FILENAME_DEFAULT, 'get') . '<p align="right">' . TEXT_SHOW . '&nbsp;';
        if (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id'])) {
          echo tep_draw_hidden_field('manufacturers_id', $HTTP_GET_VARS['manufacturers_id']);
          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
        } else {
          echo tep_draw_hidden_field('cPath', $cPath);
          $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
        }
        echo tep_draw_hidden_field('sort', $HTTP_GET_VARS['sort']);
        while ($filterlist = tep_db_fetch_array($filterlist_query)) {
          $options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name']);
        }
        echo tep_draw_pull_down_menu('filter_id', $options, (isset($HTTP_GET_VARS['filter_id']) ? $HTTP_GET_VARS['filter_id'] : ''), 'onchange="this.form.submit()"');
        echo tep_hide_session_id() . '</p></form></div>' . "\n";
      }
    }

    include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);
?>

</div>

<?php
  } else { // default page
?>

<!--<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div> -->

<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

<div class="contentContainer">
<div class="row"><?php echo $oscTemplate->getBlocks('front_page'); ?></div>
 <!-- <div class="alert alert-info">
    <?php // echo tep_customer_greeting(); ?>
  </div> -->
<!--recently product-->

<div class="row">
    <?php echo $oscTemplate->getContent('index'); ?>
  </div>

<!--recently product-->

<?php
    if (tep_not_null(TEXT_MAIN)) {
?>

  <!--<div class="contentText">
    <?php // echo TEXT_MAIN; ?>
  </div> -->

<?php
    }

   // include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS);
   // include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS);
?>

</div>

<?php
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
