<?php
/*
  $Id: optional_related_products.php, ver 5.0 08/03/2015 Exp $

  Copyright (c) 2015 Rainer Schmied @raiwa (info@sarplataygemas.com)

  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  Based on: products_options.php, ver 2.0 05/01/2005
  Copyright (c) 2004-2005 Daniel Bahna (daniel.bahna@gmail.com)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  $languages = tep_get_languages();

// include the related products functions
  require_once(DIR_WS_FUNCTIONS . 'related_products_funcs.php');

  $version = tep_db_fetch_array(tep_db_query("select configuration_value as version, configuration_group_id as gID from configuration where configuration_key = 'MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_VERSION_INSTALLED'"));

  if ( !defined('MODULE_CONTENT_PRODUCT_INFO_RELATED_PRODUCTS_STATUS') ) {
    tep_redirect(tep_href_link('modules_content.php', 'module=cm_pi_related_products&action=install'));
  }

  $related_table_name = tep_db_fetch_array(tep_db_query("SHOW TABLES LIKE 'products_related_products'"));
  if ( $version['version'] != TEXT_VERSION_CONTROL || !tep_not_null($related_table_name) ){
    tep_redirect(tep_href_link('sql_setup_related_products.php'));
  }
  
  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  $attribute_page = null;
  if (isset($HTTP_GET_VARS['attribute_page'])) $attribute_page = $HTTP_GET_VARS['attribute_page'];

  $products_id_view = $HTTP_GET_VARS['products_id_view'];
  $products_id_master = $HTTP_GET_VARS['products_id_master'];
  if ($products_id_master) { $products_id_view = $products_id_master; }

  if (tep_not_null($action)) {
    $page_info = '';
    if (isset($HTTP_GET_VARS['attribute_page'])) $page_info .= 'attribute_page=' . $HTTP_GET_VARS['attribute_page'] . '&';
    if (tep_not_null($page_info)) {
      $page_info = substr($page_info, 0, -1);
    }

    switch ($action) {
      case 'Insert':
        $products_id_master = tep_db_prepare_input($_REQUEST['products_id_master']);
        $products_id_slave = tep_db_prepare_input($_REQUEST['products_id_slave']);
        $pop_order_id = tep_db_prepare_input($_REQUEST['pop_order_id']);

        if ($products_id_master != $products_id_slave) {
          $check = tep_db_query("select p.pop_id from products_related_products p where p.pop_products_id_master=" . $products_id_master ." and p.pop_products_id_slave=" . $products_id_slave);
          if (!tep_db_fetch_array($check)) {
            tep_db_query("insert into products_related_products values ('', '" . (int)$products_id_master . "', '" . (int)$products_id_slave . "', '". (int)$pop_order_id."')");
          }
        }
        tep_redirect(tep_href_link('optional_related_products.php', $page_info.'&products_id_master='.$products_id_master.'&products_id_slave='.$products_id_slave.'&products_id_view='.$products_id_view));
        break;

      case 'Reciprocate':
        $products_id_master = tep_db_prepare_input($_REQUEST['products_id_master']);
        $products_id_slave = tep_db_prepare_input($_REQUEST['products_id_slave']);
        $pop_order_id = tep_db_prepare_input($_REQUEST['pop_order_id']);
        if ($products_id_master != $products_id_slave) {
          $check = tep_db_query("select p.pop_id from products_related_products p where p.pop_products_id_master=" . $products_id_master ." and p.pop_products_id_slave=" . $products_id_slave);
          if (!tep_db_fetch_array($check)) {
            tep_db_query("insert into products_related_products values ('', '" . (int)$products_id_master . "', '" . (int)$products_id_slave . "', '". (int)$pop_order_id."')");
          }
          $check = tep_db_query("select p.pop_id from products_related_products p where p.pop_products_id_master=" . $products_id_slave ." and p.pop_products_id_slave=" . $products_id_master );
          if (!tep_db_fetch_array($check)) {
            tep_db_query("insert into products_related_products values ('', '" . (int)$products_id_slave . "', '" . (int)$products_id_master . "', '". (int)$pop_order_id."')");
          }
        }
        tep_redirect(tep_href_link('optional_related_products.php', $page_info.'&products_id_master='.$products_id_master.'&products_id_slave='.$products_id_slave.'&products_id_view='.$products_id_view));
        break;

      case 'Inherit':
        $products_id_master = tep_db_prepare_input($_REQUEST['products_id_master']);
        $products_id_slave = tep_db_prepare_input($_REQUEST['products_id_slave']);
        $pop_order_id = tep_db_prepare_input($_REQUEST['pop_order_id']);

        if ($products_id_master != $products_id_slave) {
          if (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_INSERT_AND_INHERIT == 'True') {
            $check = tep_db_query("select p.pop_id from products_related_products p where p.pop_products_id_master=" . $products_id_master." and p.pop_products_id_slave=" . $products_id_slave);
            if (!tep_db_fetch_array($check)) {
               tep_db_query("insert into products_related_products values ('', '" . (int)$products_id_master . "', '" . (int)$products_id_slave . "', '". (int)$pop_order_id."')");
            }
          }
          $products = tep_db_query("select p.pop_products_id_slave from products_related_products p where p.pop_products_id_master=" . $products_id_slave . " order by p.pop_id");
          while ($products_values = tep_db_fetch_array($products)) {
            $products_id_slave2 = $products_values['pop_products_id_slave'];
            if ($products_id_master != $products_id_slave2) {

              $check = tep_db_query("select p.pop_id from products_related_products p where p.pop_products_id_master=" . $products_id_master." and p.pop_products_id_slave=" . $products_id_slave2);
              if (!tep_db_fetch_array($check)) {
                tep_db_query(" insert into products_related_products values ('', '" . (int)$products_id_master . "', '" . (int)$products_id_slave2 . "', '". (int)$pop_order_id."')");
              }
            }
          }
        }
        tep_redirect(tep_href_link('optional_related_products.php', $page_info.'&products_id_master='.$products_id_master.'&products_id_slave='.$products_id_slave.'&products_id_view='.$products_id_view));
        break;

      case 'update_product_attribute':
        $products_id_master = tep_db_prepare_input($_REQUEST['products_id_master']);
        $products_id_slave = tep_db_prepare_input($_REQUEST['products_id_slave']);
        $pop_order_id = tep_db_prepare_input($_REQUEST['pop_order_id']);
        $pop_id = tep_db_prepare_input($_REQUEST['pop_id']);

        tep_db_query("update products_related_products set pop_products_id_master = '" . (int)$products_id_master . "', pop_products_id_slave = '" . (int)$products_id_slave . "', pop_order_id = '".(int)$pop_order_id."' where pop_id = '" . (int)$pop_id . "'");
        tep_redirect(tep_href_link('optional_related_products.php', $page_info.'&products_id_view='.$products_id_view));
        break;
      case 'delete_attribute':
        $pop_id = tep_db_prepare_input($HTTP_GET_VARS['pop_id']);

        tep_db_query("delete from products_related_products where pop_id = '" . (int)$pop_id . "'");

        tep_redirect(tep_href_link('optional_related_products.php', $page_info.'&products_id_view='.$products_id_view));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<span class="pageHeading"><?php echo HEADING_TITLE_RELATED; ?></span>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td><form name="formview"><select name="products_id_view" onChange="return formview.submit();">
<?php

    echo '<option name="Show All Products" value="">Show All Products</option>';
    $products = tep_db_query("select p.products_id, p.products_model, pd.products_name from products p, products_description pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
    while ($products_values = tep_db_fetch_array($products)) {
        $model = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL == 'True')?$products_values['products_model'] . MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR:'';
        $name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_NAME == 'True')?$products_values['products_name']:'';
        if ($products_id_view == $products_values['products_id']) {
              echo '<option name="' . $name . '" value="' . $products_values['products_id'] . '" SELECTED>' . $model . $name . '</option>';
        } else {
              echo '<option name="' . $name . '" value="' . $products_values['products_id'] . '">' . $model . $name . '</option>';
        }
    }
?>
            </select></form>&nbsp;</td>
            <td><?php echo tep_image(DIR_WS_IMAGES . 'pixel_trans.gif', '', '1', '53'); ?>&nbsp;</td>
            <td><?php echo '<a href="' . tep_href_link('modules_content.php', 'module=cm_pi_related_products&action=edit') . '">' . TEXT_CONFIGURATION . '</a>'; ?><br>
                <?php echo '<a href="' . tep_href_link('sql_setup_related_products.php') . '">' . TEXT_SETUP . '</a>'; ?>
            </td>
          </tr>
        </table>
<?php
  if ($action == 'update_attribute') {
    $form_action = 'update_product_attribute';
  } else {
    $form_action = 'add_product_attributes';
  }

  if (!isset($attribute_page)) {
    $attribute_page = 1;
  }
  $prev_attribute_page = $attribute_page - 1;
  $next_attribute_page = $attribute_page + 1;
  $form_params = 'action=' . $form_action . '&option_page=' . $option_page . '&value_page=' . $value_page . '&attribute_page=' . $attribute_page;
?>
    <form name="attributes" action="<?php echo tep_href_link('optional_related_products.php', $form_params); ?>" method="get"><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="5" class="smallText">
<?php
  $per_page = MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_ROW_LISTS_OPTIONS;

  $attributes = "
         SELECT
                pa.*
           FROM products_related_products pa
           LEFT JOIN products_description pd
             ON pa.pop_products_id_master = pd.products_id
            AND pd.language_id = '" . (int)$languages_id . "'";

  if ($products_id_view) { $attributes .= "
          WHERE pd.products_id = '$products_id_view'"; }
  $attributes .= "
       ORDER BY pd.products_name, pa.pop_order_id, pa.pop_id";

  $attribute_query = tep_db_query($attributes);

  $attribute_page_start = ($per_page * $attribute_page) - $per_page;
  $num_rows = tep_db_num_rows($attribute_query);

  if ($num_rows <= $per_page) {
     $num_pages = 1;
  } else if (($num_rows % $per_page) == 0) {
     $num_pages = ($num_rows / $per_page);
  } else {
     $num_pages = ($num_rows / $per_page) + 1;
  }
  $num_pages = (int) $num_pages;

  $attributes = $attributes . " LIMIT $attribute_page_start, $per_page";

  $view_id = '';
  if ($products_id_view) {
    $products_id_view = $products_id_master?$products_id_master:$products_id_view;
    $view_id = '&products_id_view=' . $products_id_view;
  }

  // Previous
  if ($prev_attribute_page) {
    echo '<a href="' . tep_href_link('optional_related_products.php', 'attribute_page=' . $prev_attribute_page . $view_id) . '"> &lt;&lt; </a> | ';
  }

  for ($i = 1; $i <= $num_pages; $i++) {
    if ($i != $attribute_page) {
      echo '<a href="' . tep_href_link('optional_related_products.php', 'attribute_page=' . $i) . $view_id . '">' . $i . '</a> | ';
    } else {
      echo '<b><font color="red">' . $i . '</font></b> | ';
    }
  }

  // Next
  if ($attribute_page != $num_pages) {
    echo '<a href="' . tep_href_link('optional_related_products.php', 'attribute_page=' . $next_attribute_page . $view_id) . '"> &gt;&gt; </a>';
  }
?>
            </td>
          </tr>
          <tr>
            <td colspan="5"><?php echo tep_black_line(); ?></td>
          </tr>
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>(To)&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>(From)&nbsp;</td>
            <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ORDER; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
          </tr>
          <tr>
            <td colspan="5"><?php echo tep_black_line(); ?></td>
          </tr>
<?php
  $next_id = 1;
  $attributes = tep_db_query($attributes);
  while ($attributes_values = tep_db_fetch_array($attributes)) {
    $products_name_master = tep_get_products_name($attributes_values['pop_products_id_master']);
    $products_name_slave = tep_get_products_name($attributes_values['pop_products_id_slave']);
    if (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL == 'True') {
      $mModel = tep_get_products_model($attributes_values['pop_products_id_master']) . MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR . ' ';
      $sModel = tep_get_products_model($attributes_values['pop_products_id_slave']) . MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR . ' ';
    } else {
      $mModel = $sModel = '';
    }
    $pop_order_id = $attributes_values['pop_order_id'];
    $rows++;
?>
          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
    if (($action == 'update_attribute') && ($HTTP_GET_VARS['pop_id'] == $attributes_values['pop_id'])) {
?>
            <td class="smallText">&nbsp;<?php echo $attributes_values['pop_id']; ?><input type="hidden" name="pop_id" value="<?php echo $attributes_values['pop_id']; ?>">&nbsp;</td>
            <td class="smallText">&nbsp;<select name="products_id_master">
<?php
      $products = tep_db_query("select p.products_id, p.products_model, pd.products_name from products p, products_description pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
      while($products_values = tep_db_fetch_array($products)) {
        $model = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL == 'True')?$products_values['products_model'] . MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR:'';
        $name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_NAME == 'True')?$products_values['products_name']:'';
        $product_name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH == '0')?$name:substr($name, 0, MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH);
        if ($attributes_values['pop_products_id_master'] == $products_values['products_id']) {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '" SELECTED>' . $model . $product_name . '</option>';
        } else {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $model . $product_name . '</option>';
        }
      }
?>
            </select>&nbsp;</td>
            <td class="smallText">&nbsp;<select name="products_id_slave">
<?php
      $products = tep_db_query("select p.products_id, p.products_model, pd.products_name from products p, products_description pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
      while($products_values = tep_db_fetch_array($products)) {
        $model = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL == 'True')?$products_values['products_model'] . MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR:'';
        $name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_NAME == 'True')?$products_values['products_name']:'';
        $product_name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH == '0')?$name:substr($name, 0, MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH);
        if ($attributes_values['pop_products_id_slave'] == $products_values['products_id']) {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '" SELECTED>' . $model . $product_name . '</option>';
        } else {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $model . $product_name . '</option>';
        }
      }
?>
            </select>&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<input type="text" name="pop_order_id" value="<?php echo $attributes_values['pop_order_id']; ?>" size="6">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo tep_draw_hidden_field('action', 'update_product_attribute') .  tep_draw_button(IMAGE_UPDATE, 'refresh'); ?>&nbsp;<?php echo '<a href="' . tep_href_link('optional_related_products.php', '&attribute_page=' . $attribute_page . '&products_id_view='.$products_id_view, 'NONSSL') . '">'; ?><?php echo tep_draw_button(IMAGE_CANCEL, 'cancel'); ?></a>&nbsp;</td>

            
            
<?php
    } else {
//  basic browse table list
?>
            <td class="smallText">&nbsp;<?php echo $attributes_values["pop_id"]; ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php echo $mModel ?><?php echo (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_DISPLAY_LENGTH== '0')?$products_name_master:substr($products_name_master, 0, MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_DISPLAY_LENGTH); ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php echo $sModel ?><?php echo (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_DISPLAY_LENGTH== '0')?$products_name_slave:substr($products_name_slave, 0, MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_DISPLAY_LENGTH); ?>&nbsp;</td>
            <td class="smallText" align="center">&nbsp;<?php echo $pop_order_id; ?>&nbsp;</td>
            <td align="center" class="smallText">

               <?php
                 $params = 'action=update_attribute&pop_id='
                          . $attributes_values['pop_id']
                          . '&attribute_page=' . $attribute_page
                          . '&products_id_view=' . $products_id_view;
                     echo '<a href="' . tep_href_link('optional_related_products.php', $params, 'NONSSL') . '">'; ?>
                     <?php echo IMAGE_EDIT; ?></a>&nbsp;&nbsp;
               <?php
                 $params = 'action=delete_attribute&pop_id='
                          . $attributes_values['pop_id']
                          . '&attribute_page=' . $attribute_page
                          . '&products_id_view=' . $products_id_view;
                     if (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_CONFIRM_DELETE == 'False') { ?>
               <a href="<?php echo tep_href_link('optional_related_products.php', $params, 'NONSSL')?>"><?php echo IMAGE_DELETE; ?></a>
               <?php }else { ?>
               <a href="<?php echo tep_href_link('optional_related_products.php', $params, 'NONSSL')?>" onClick="return confirm('<?php echo sprintf(TEXT_CONFIRM_DELETE_ATTRIBUTE, addslashes($products_name_slave), addslashes($products_name_master)); ?>');"><?php echo IMAGE_DELETE; ?></a>
               <?php } ?></td>
<?php
    }
    $max_attributes_id_query = tep_db_query("select max(pop_id) + 1 as next_id from products_related_products");
    $max_attributes_id_values = tep_db_fetch_array($max_attributes_id_query);
    $next_id = $max_attributes_id_values['next_id'];
?>
          </tr>
<?php
  }
  if ($action != 'update_attribute') {
?>
          <tr>
            <td colspan="5"><?php echo tep_black_line(); ?></td>
          </tr>
          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
            <td class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
      	    <td class="smallText"><b>A:</b>&nbsp;<select name="products_id_master">
<?php
    $products = tep_db_query("select p.products_id, p.products_model, pd.products_name from products p, products_description pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
    $products_id_master = $HTTP_GET_VARS['products_id_master'];
    if (!$products_id_master) { $products_id_master = $products_id_view; }
    while ($products_values = tep_db_fetch_array($products)) {
      $model = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL == 'True')?$products_values['products_model'] . MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR:'';
      $name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_NAME == 'True')?$products_values['products_name']:'';
      $product_name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH == '0')?$name:substr($name, 0, MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH);
      if ($products_id_master == $products_values['products_id']) {
        echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '" SELECTED>' . $model . $product_name . '</option>';
      } else {
        echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $model . $product_name . '</option>';
      }
    }
?>
            </select>&nbsp;</td>
            <td class="smallText"><b>B:</b>&nbsp;<select name="products_id_slave">
<?php
    $products = tep_db_query("select p.products_id, p.products_model, pd.products_name from products p, products_description pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
    while ($products_values = tep_db_fetch_array($products)) {
      $model = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_MODEL == 'True')?$products_values['products_model'] . MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MODEL_SEPARATOR:'';
      $name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_USE_NAME == 'True')?$products_values['products_name']:'';
      $product_name = (MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH == '0')?$name:substr($name, 0, MODULE_CONTENT_PRODUCT_INFO_ADMIN_RELATED_PRODUCTS_MAX_NAME_LENGTH);
      if ($HTTP_GET_VARS['products_id_slave'] == $products_values['products_id']) {
        echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '" SELECTED>' . $model . $product_name . '</option>';
      } else {
        echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $model . $product_name . '</option>';
      }
    }
?>
            </select>&nbsp;</td>
            <td class="smallText" align="center">&nbsp;<input type="text" name="pop_order_id" size="3">&nbsp;</td>
          </tr>
          <tr><td colspan="5" align="center" class="smallText">
            <?php echo  tep_draw_button(BUTTON_INSERT, 'arrowthick-1-e', null, 'primary', array('params' => 'name="action" value="Insert"')); ?>
            <?php echo  tep_draw_button(BUTTON_RECIPROCATE, 'arrowthick-2-e-w', null, 'primary', array('params' => 'name="action" value="Reciprocate"')); ?>
            <?php echo  tep_draw_button(BUTTON_INHERIT, 'arrowthick-1-s', null, 'primary', array('params' => 'name="action" value="Inherit"')); ?>
          </td></tr>
<?php
  }
?>
          <tr>
            <td colspan="5"><?php echo tep_black_line(); ?></td>
          </tr>
        </table>
        <input type="hidden" name="products_id_view" value="<?php echo $products_id_view; ?>">
        </form>          
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>