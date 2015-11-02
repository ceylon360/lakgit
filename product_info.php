<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');


  if (!isset($HTTP_GET_VARS['products_id'])) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);

//  $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
 //cat state
  if (!empty($hiddencats)) {
    $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and p.products_id = p2c.products_id and (not (p2c.categories_id in (" . implode(',', $hiddencats) . "))) and pd.language_id = '" . (int)$languages_id . "'");
  } else {
    $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
  }
  //cat state
 $product_check = tep_db_fetch_array($product_check_query);

  require(DIR_WS_INCLUDES . 'template_top.php');

  if ($product_check['total'] < 1) {
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-warning"><?php echo TEXT_PRODUCT_NOT_FOUND; ?></div>
  </div>

  <div class="pull-right">
    <?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', tep_href_link(FILENAME_DEFAULT)); ?>
  </div>
</div>

<?php
  } else {
    $product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description,pd.products_note, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
    $product_info = tep_db_fetch_array($product_info_query);

    tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed+1 where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and language_id = '" . (int)$languages_id . "'");

    if ($new_price = tep_get_products_special_price($product_info['products_id'])) {
      $products_price = '<del>' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</del> <span class="productSpecialPrice" itemprop="price">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
    } else {
      $products_price = '<span itemprop="price">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
    }

    if ($product_info['products_date_available'] > date('Y-m-d H:i:s')) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/PreOrder" />';
    } elseif ((STOCK_CHECK == 'true') && ($product_info['products_quantity'] < 1)) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/OutOfStock" />';
    } else {
      $products_price .= '<link itemprop="availability" href="http://schema.org/InStock" />';
    }

    $products_price .= '<meta itemprop="priceCurrency" content="' . tep_output_string($currency) . '" />';

    $products_name = '<a href="' . tep_href_link('product_info.php', 'products_id=' . $product_info['products_id']) . '" itemprop="url"><span itemprop="name">' . $product_info['products_name'] . '</span></a>';
$stock_check .='';
    if (tep_not_null($product_info['products_model'])) {
      $products_name .= '<br /><small class="mdl">[<span itemprop="sku">' . $product_info['products_model'] . '</span>]</small>';
    
	//stock info
	    if (tep_get_products_stock($product_info['products_id'])>0 ) {
      $stock_check .='<div class="product-stock">In Stock</div>';
    } else {
      $stock_check .='<div class="product-out-stock">Out of Stock</div>';
    }
	//stock info
	}
?>

<?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')). 'action=add_product', 'NONSSL'), 'post', 'class="form-horizontal" role="form"'); ?>

<div class="item-container">	
			


<div itemscope itemtype="http://schema.org/Product">
<meta itemprop="name" content="<?php echo $product_info['products_name'] ?>" />
<meta itemprop="sku" content="<?php echo $product_info['products_model'] ?>" />
<!--
<div class="page-header">
  <h1 class="pull-right" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><?php echo $products_price; ?></h1>
  <h1><?php echo $products_name; ?></h1>
  <div><?php echo $stock_check; ?></div>
</div>
-->
<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

<div class="contentContainer">
  <div class="contentText">




	  

    <div class="clearfix"></div>

<?php
    if ($product_info['products_date_available'] > date('Y-m-d H:i:s')) {
?>

    <div class="alert alert-info"><?php echo sprintf(TEXT_DATE_AVAILABLE, tep_date_long($product_info['products_date_available'])); ?></div>

<?php
    }
?>

  </div>

<?php
    $reviews_query = tep_db_query("select count(*) as count, avg(reviews_rating) as avgrating from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' and reviews_status = 1");
    $reviews = tep_db_fetch_array($reviews_query);

    if ($reviews['count'] > 0) {
      echo '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><meta itemprop="ratingValue" content="' . $reviews['avgrating'] . '" /><meta itemprop="ratingCount" content="' . $reviews['count'] . '" /></span>';
    }
?>
<div class="card card-gray animated fadeIn row" >

				<div class="col-md-4">
				<?php
    if (tep_not_null($product_info['products_image'])) {

      echo tep_image(DIR_WS_IMAGES . $product_info['products_image'], NULL, NULL, NULL, 'itemprop="image" style="display:none;"');

      $photoset_layout = '1';

      $pi_query = tep_db_query("select image, htmlcontent from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product_info['products_id'] . "' order by sort_order");
      $pi_total = tep_db_num_rows($pi_query);

      if ($pi_total > 0) {
        $pi_sub = $pi_total-1;

        while ($pi_sub > 5) {
          $photoset_layout .= 5;
          $pi_sub = $pi_sub-5;
        }

        if ($pi_sub > 0) {
          $photoset_layout .= ($pi_total > 5) ? 5 : $pi_sub;
        }
?>

    <div class="piGal pull-right" data-imgcount="<?php echo $photoset_layout; ?>">

<?php
        $pi_counter = 0;
        $pi_html = array();

        while ($pi = tep_db_fetch_array($pi_query)) {
          $pi_counter++;

          if (tep_not_null($pi['htmlcontent'])) {
            $pi_html[] = '<div id="piGalDiv_' . $pi_counter . '">' . $pi['htmlcontent'] . '</div>';
          }

          echo tep_image(DIR_WS_IMAGES . $pi['image'], '', '', '', 'id="piGalImg_' . $pi_counter . '"');
        }
?>

    </div>

<?php
        if ( !empty($pi_html) ) {
          echo '    <div style="display: none;">' . implode('', $pi_html) . '</div>';
        }
      } else {
?>

    <div class="piGal pull-right">
      <?php echo tep_image(DIR_WS_IMAGES . $product_info['products_image'], addslashes($product_info['products_name'])); ?>
    </div>

<?php
      }
    }
?>
				</div>
					
				<div class="col-md-8">
				<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
					<div class="product-title"><?php echo $products_name; ?></div>
					<div class="product-desc" itemprop="description"><?php echo stripslashes($product_info['products_description']); ?></div>
			<!--		<div class="product-rating"><i class="fa fa-star gold"></i> <i class="fa fa-star gold"></i> <i class="fa fa-star gold"></i> <i class="fa fa-star gold"></i> <i class="fa fa-star-o"></i> </div> -->
					      <tr>
		  <!-- <td><table border="0" cellspacing="0" cellpadding="2">
			 
			  <?php
				  $text_attributes_query = tep_db_query("select pta.*, cbta.products_text_attributes_text from products_text_attributes as pta, products_text_attributes_enabled as ptae, customers_basket_text_attributes as cbta where ptae.products_text_attributes_id = pta.products_text_attributes_id and ptae.products_id = " . tep_get_prid($HTTP_GET_VARS['products_id']) . " and cbta.products_text_attributes_id = pta.products_text_attributes_id and cbta.session_id = '" . tep_session_id() . "'");
				  if (tep_db_num_rows($text_attributes_query) == 0)  
				  $text_attributes_query = tep_db_query("select pta.* from products_text_attributes as pta, products_text_attributes_enabled as ptae where ptae.products_text_attributes_id = pta.products_text_attributes_id and ptae.products_id = " . tep_get_prid($HTTP_GET_VARS['products_id']));
				  
				  while ($text_attributes = tep_db_fetch_array($text_attributes_query)) {
				  ?>
				  <div>
					  <p class="main"><?php echo $text_attributes['products_text_attributes_name'] . ': </p><p>' . tep_draw_input_field('products_text_attributes_' . $text_attributes['products_text_attributes_id'], tep_not_null($text_attributes['products_text_attributes_text']) ? $text_attributes['products_text_attributes_text'] : ''); ?></p>
				  </div>
				  <?php
				  }
			  ?>
			  
		  </table></td>
		  test-->
		  <td><table border="0" cellspacing="0" cellpadding="2">
			  
			  <?php
				  $text_attributes_query = tep_db_query("select pta.*, cbta.products_text_attributes_text from products_text_attributes as pta, products_text_attributes_enabled as ptae, customers_basket_text_attributes as cbta where ptae.products_text_attributes_id = pta.products_text_attributes_id and ptae.products_id = " . tep_get_prid($HTTP_GET_VARS['products_id']) . " and cbta.products_text_attributes_id = pta.products_text_attributes_id and cbta.session_id = '" . tep_session_id() . "'");
				  if (tep_db_num_rows($text_attributes_query) == 0)  
				  $text_attributes_query = tep_db_query("select pta.* from products_text_attributes as pta, products_text_attributes_enabled as ptae where ptae.products_text_attributes_id = pta.products_text_attributes_id and ptae.products_id = " . tep_get_prid($HTTP_GET_VARS['products_id']));
				  
				  while ($text_attributes = tep_db_fetch_array($text_attributes_query)) {
				  ?>
				  <div class="group">
				  
					<!--  <p class="main"><?php echo $text_attributes['products_text_attributes_name'] . ': </p><p>' . tep_draw_input_field('products_text_attributes_' . $text_attributes['products_text_attributes_id'], tep_not_null($text_attributes['products_text_attributes_text']) ? $text_attributes['products_text_attributes_text'] : ''); ?></p>-->
				  <?php 
				 // $attr_options="type,value,min,max,placeholder,required";
				 
				 // $tx_attr_options="number,10,2,50,num,required";
				  $tx_attr_options=$text_attributes['products_text_attributes_options'];
				  
				  list($tx_attr_options_type, $tx_attr_options_value, $tx_attr_options_min,$tx_attr_options_max,$tx_attr_options_placeholder,$tx_attr_options_required,$tx_attr_options_length ) = explode(',', $tx_attr_options);
				 
				// echo $text_attributes['products_text_attributes_name'];
				 if($tx_attr_options_type=='textarea'){
					echo	tep_draw_textarea_field('products_text_attributes_' . $text_attributes['products_text_attributes_id'], 'soft', $tx_attr_options_min, $tx_attr_options_max, $tx_attr_options_value, 'placeholder="' . $tx_attr_options_placeholder . '" maxlength="' . $tx_attr_options_length . '" '.$tx_attr_options_required, $reinsert_value = true);
				 }
				 else{
				  echo tep_draw_input_field('products_text_attributes_' . $text_attributes['products_text_attributes_id'],$tx_attr_options_value, 'placeholder="' . $tx_attr_options_placeholder . '" max="' . $tx_attr_options_max . '"  min="' . $tx_attr_options_min . '" '.$tx_attr_options_required, $tx_attr_options_type, $reinsert_value = true, 'class="form-control1"');
				  
				 }
				echo '<span class="highlight"></span>';
				 echo '<span class="bar"></span>';
				  echo '<label>'.$text_attributes['products_text_attributes_name'].'</label>';
				  ?>
					
						</div>
				  <?php
				  }
			  ?>
			  
		  </table></td>
		  <!--test-->
      </tr>
					<hr>
					

<?php
    $products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "'");
    $products_attributes = tep_db_fetch_array($products_attributes_query);
    if ($products_attributes['total'] > 0) {
?>

    <h4><?php echo TEXT_PRODUCT_OPTIONS; ?></h4>

    <p>
<?php
      $products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_name");
      while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
        $products_options_array = array();
        $products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'");
        while ($products_options = tep_db_fetch_array($products_options_query)) {
          $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
          if ($products_options['options_values_price'] != '0') {
            $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
          }
        }

        if (is_string($HTTP_GET_VARS['products_id']) && isset($cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']])) {
          $selected_attribute = $cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']];
        } else {
          $selected_attribute = false;
        }
?>
      
	  <strong><?php echo $products_options_name['products_options_name'] . ':'; ?></strong><?php echo tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute, 'style="width: 200px;"'); ?><br />

		
			<?php

      }
?>
    </p>

<?php
    }
?>

	  <!-- countdown time-->
	  <?php if ($new_price = tep_get_products_special_price($product_info['products_id'])) {
$expdate=tep_get_products_special_date($product_info['products_id'])?>
					
					<script type="text/javascript" src="http://harshen.github.io/jquery-countdownTimer/jquery.countdownTimer.min.js"></script>
					 
					<p>End In</p>
					<input type="hidden" id="exp" name="expire_date" value="<?php echo $expdate;?>">
					<div id="countdowntimer"><span id="future_date"><span></div>
					</br>
					<script type="text/javascript">
$(function(){
    var expier= $("#exp").val();
    expier = expier.replace("-", "/");
    $("#future_date").countdowntimer({
        dateAndTime : expier,
    size : "lg",
        regexpMatchFormat: "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
            regexpReplaceWith: "<div id='exp-ct'>$1<div id='exp-tt'>days</div></div>  <div id='exp-ct'>$2<div id='exp-tt'>hours</div ></div>  <div id='exp-ct'>$3<div id='exp-tt'>min</div></div>  <div id='exp-ct'>$4<div id='exp-tt'>sec</div></div>"
    });
});
</script>
	  <?php
	  }
	  else{
			
	  }
	  ?>
	  
    <div class="clearfix"></div>
<br>
					<div class="product-price"><?php echo $products_price; ?></div>
					<?php	if (tep_not_null($product_info['products_note'])) {
						echo '<div class="notice notice-danger">' . $product_info['products_note'] . '</div>';
						}  
							else{
									echo $stock_check;
									      
						}
					
					?>
					</span>
					<hr>
					<div class="buttonSet row">
    <div class="col-xs-6"><?php 
	if ($product_info['products_quantity'] < 1){
			echo tep_draw_button('out of stock', 'fa fa-times', null, 'secondary', array('type' => 'button'), 'btn-danger disabled');
	}
	else{
	
	
	echo tep_draw_hidden_field('products_id', $product_info['products_id']) . tep_draw_button(IMAGE_BUTTON_IN_CART, 'glyphicon glyphicon-shopping-cart', null, 'primary', null, 'btn-success'); 
	}
	?></div>
    <div class="col-xs-6 text-right"><?php echo tep_draw_button(IMAGE_BUTTON_REVIEWS . (($reviews['count'] > 0) ? ' (' . $reviews['count'] . ')' : ''), 'glyphicon glyphicon-comment', tep_href_link(FILENAME_PRODUCT_REVIEWS, tep_get_all_get_params())); ?></div>
  </div>
				</div>
			 
		</div>
</DIV>



  <div class="row">
    <?php echo $oscTemplate->getContent('product_info'); ?>
  </div>

<?php
    if ((USE_CACHE == 'true') && empty($SID)) {
      echo tep_cache_also_purchased(3600);
    } else {
      include(DIR_WS_MODULES . FILENAME_ALSO_PURCHASED_PRODUCTS);
    }

    if ($product_info['manufacturers_id'] > 0) {
      $manufacturer_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$product_info['manufacturers_id'] . "'");
      if (tep_db_num_rows($manufacturer_query)) {
        $manufacturer = tep_db_fetch_array($manufacturer_query);
        echo '<span itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization"><meta itemprop="name" content="' . tep_output_string($manufacturer['manufacturers_name']) . '" /></span>';
      }
    }
?>

</div>

</div>

</form>

<?php
  }
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
