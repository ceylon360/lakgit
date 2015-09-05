<?php
/*
  sql_setup_related_products.php
  SQL Setup Utility For Optional Related Products, Ver 4.0

  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $contrib = 'Related Products';
  $filename = 'optional_related_products.php';

  function install_ORP_to_sql($gid = 0) {
    $old_table_name = tep_db_fetch_array(tep_db_query("SHOW TABLES LIKE 'products_options_products'"));
    if (tep_not_null($old_table_name)) {
      tep_db_query("RENAME TABLE products_options_products TO products_related_products");
    }
    $insert_relationship_table = "CREATE TABLE IF NOT EXISTS `products_related_products` (
      `pop_id` int(11) NOT NULL auto_increment,
      `pop_products_id_master` int(11) NOT NULL default '0',
      `pop_products_id_slave` int(11) NOT NULL default '0',
      `pop_order_id` smallint(6) NOT NULL default '0',
      PRIMARY KEY  (`pop_id`)
    )";
    tep_db_query($insert_relationship_table);
  }

  function get_group_id($config_title) {
    $group_id_array = tep_db_fetch_array(tep_db_query("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title like '". $config_title . "'"));
    if (sizeof($group_id_array <= 1)) {
      return $group_id_array['configuration_group_id'];
    }
    remove_group_id($contrib);
    return 0;
  }

  function remove_keys($gid) {
    if (tep_not_null($gid)) {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$gid . "'");
    } else {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", keys()) . "')");
    }
  }

  function remove_group_id($title) {
      tep_db_query("delete from " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = '" . $title . "'");
  }

  function remove_table() {
      tep_db_query("DROP TABLE IF EXISTS `products_related_products`");
  }

  function keys() {
    $keys = array();
    $keys[] = 'RELATED_PRODUCTS_VERSION_INSTALLED';
    $keys[] = 'MODULE_RELATED_PRODUCTS_SHOW_THUMBS';
    $keys[] = 'RELATED_PRODUCTS_SHOW_THUMBS';
    $keys[] = 'RELATED_PRODUCTS_SHOW_NAME';
    $keys[] = 'RELATED_PRODUCTS_SHOW_MODEL';
    $keys[] = 'RELATED_PRODUCTS_SHOW_PRICE';
    $keys[] = 'RELATED_PRODUCTS_SHOW_QUANTITY';
    $keys[] = 'RELATED_PRODUCTS_SHOW_BUY_NOW';
    $keys[] = 'RELATED_PRODUCTS_USE_ROWS';
    $keys[] = 'RELATED_PRODUCTS_PER_ROW';
    $keys[] = 'RELATED_PRODUCTS_MAX_DISP';
    $keys[] = 'RELATED_PRODUCTS_RANDOMIZE';
    $keys[] = 'RELATED_PRODUCTS_MAX_ROW_LISTS_OPTIONS';
    $keys[] = 'RELATED_PRODUCTS_MAX_NAME_LENGTH';
    $keys[] = 'RELATED_PRODUCTS_MAX_DISPLAY_LENGTH';
    $keys[] = 'RELATED_PRODUCTS_ADMIN_USE_MODEL';
    $keys[] = 'RELATED_PRODUCTS_ADMIN_USE_NAME';
    $keys[] = 'RELATED_PRODUCTS_ADMIN_MODEL_SEPARATOR';
    $keys[] = 'RELATED_PRODUCTS_CONFIRM_DELETE';
    $keys[] = 'RELATED_PRODUCTS_INSERT_AND_INHERIT';
    return $keys;
  }

  switch ($HTTP_GET_VARS['install']) {
    case ('new'):
      install_ORP_to_sql();
      tep_redirect(tep_href_link($filename));
      break;
    case ('remove'):
      remove_table();
      break;
    case ('upgrade'):
      $group_id = get_group_id($contrib);
      remove_keys($group_id);
      remove_group_id($contrib);
      install_ORP_to_sql();
      tep_redirect(tep_href_link($filename));
      break;
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
<style>
.intro_section {padding:20px 20px 0px 20px;}
.intro_section p {width:590px;}
.intro_section b {font-size:.8em;font-weight:bold;color:#900;}
.setup_section {width:600px;border:solid 1px black;margin:10px;padding:3px 3px 10px 3px;}
.setup_section p {margin:10px;padding:3px}
</style>
<span class="pageHeading"><?php echo HEADING_TITLE_ORP; ?></span>
<form name="new_install" action="<?php echo tep_href_link('sql_setup_related_products.php'); ?>" method="get">
              <div class="setup_section">
                <span class="pageHeading"><?php echo SECTION_TITLE_NEW_INSTALL; ?></span>
<p><?php echo SECTION_DESCRIPTION_NEW_INSTALL; ?></p>
                <p><?php echo tep_draw_hidden_field('install', 'new') . tep_draw_button(IMAGE_BUTTON_NEW_INSTALL_SQL, 'arrowthick-1-e'); ?></p>
</div>
</form>

<form name="update_install" action="<?php echo tep_href_link('sql_setup_related_products.php'); ?>" method="get">
              <div class="setup_section">
                <span class="pageHeading"><?php echo SECTION_TITLE_UPGRADE; ?></span>
<p><?php echo SECTION_DESCRIPTION_UPGRADE; ?></p>
                <p><?php echo tep_draw_hidden_field('install', 'upgrade') . tep_draw_button(IMAGE_BUTTON_UPGRADE_SQL, 'arrowthick-1-e'); ?></p>
</div>
            </form>
            
<form name="update_install" action="<?php echo tep_href_link('sql_setup_related_products.php'); ?>" method="get">
              <div class="setup_section">
                <span class="pageHeading"><?php echo SECTION_TITLE_REMOVE; ?></span>
<p><?php echo SECTION_DESCRIPTION_REMOVE; ?></p>
                <p><?php $param = array('params' => 'onclick="var x=confirm(\''. TEXT_CONFIRM_REMOVE_SQL . '\')"');
                          echo tep_draw_hidden_field('install', 'remove')
                             . tep_draw_button(IMAGE_BUTTON_REMOVE_SQL, 'arrowthick-1-e', null, null, $param); ?></p>
</div>

            </form>                  
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>