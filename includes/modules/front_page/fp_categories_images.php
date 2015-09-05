<?php
/*
  $Id: fp_categories_images.php v1.2 20130513 Kymation $
  Most of the execute() code is from the stock osCommerce New Products module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class fp_categories_images {
    var $code = 'fp_categories_images';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function fp_categories_images() {
      $this->title = MODULE_FRONT_PAGE_CATEGORIES_IMAGES_TITLE;
      $this->description = MODULE_FRONT_PAGE_CATEGORIES_IMAGES_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $languages_id, $language, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        $categories_content = '<!-- Categories Images BOF -->' . PHP_EOL;
        if( constant( 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE_' . strtoupper( $language ) ) != '') {
          $categories_content .= '  <h2>' . sprintf( constant( 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE_' . strtoupper( $language ) ), strftime('%B')) . '</h2>' . PHP_EOL;
        }
        $categories_content .= '  <div class="contentText">' . "\n";

        // Retrieve the data on the products in the categories list and load into an array
        $categories_query_raw = "
                select
                  c.categories_id,
                  c.categories_image,
                  cd.categories_name
                from " . TABLE_CATEGORIES_DESCRIPTION . " cd
                  join " . TABLE_CATEGORIES . " c
                    on (c.categories_id = cd.categories_id)
                where
                  c.parent_id = '0'
                  and cd.language_id = '" . (int) $languages_id . "'
                order by
                  c.sort_order
              ";
        //print 'Categories Query: ' . $categories_query_raw . '<br />';
        $categories_query = tep_db_query($categories_query_raw);

        while ($categories = tep_db_fetch_array($categories_query)) {
          $categories_id = $categories['categories_id'];
          $categories_data[$categories_id] = array (
            'id' => $categories_id,
            'name' => $categories['categories_name'],
            'image' => $categories['categories_image']
          );
        } //while ($categories

        // Set up the box in the selected style
        if (count($categories_data) > 0) { // Show only if we have categories in the array
          switch (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_STYLE) {
            // Show the categories in a fixed grid (# of columns is set in Admin)
            case 'Grid' :
              $categories_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . PHP_EOL;
              $row = 0;
              $col = 0;
              $space_above = false;
              foreach ($categories_data as $category) {
                if ($col == 0) {
                  $categories_content .= '      <tr>' . PHP_EOL;
                }

                $width = (floor(100 / MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS));

                $categories_content .= '        <td width="' . $width . '%" align="center" valign="top">' . PHP_EOL;
                $categories_content .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';

                // Show the products image if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE == 'True') {
                  $categories_content .= tep_image(DIR_WS_IMAGES . $category['image'], $category['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="2" vspace="3"');
                  $space_above = true;
                } //if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE

                // Show the products name if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME == 'True') {
                  if ($space_above == true) {
                    $categories_content .= "<br />" . PHP_EOL;
                  }
                  $categories_content .= $category['name'];
                  $space_above = true;
                } //if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME

                $categories_content .= '</a></td>' . PHP_EOL;

                $col++;
                if ($col >= MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS) {
                  $col = 0;
                  $row++;
                  $categories_content .= '</tr>' . PHP_EOL;
                } //if ($col

              } //foreach ($categories_data

              if ($col < MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS) {
                $categories_content .= '</tr>' . PHP_EOL;
              } //if ($col

              $categories_content .= '</table>' . PHP_EOL;
              break;

              // Show the categories in a floating grid (# of columns depends on browser width)
            case 'Float' :
              // Link to the stylesheet and javascript to go in the header
              $header = '<link rel="stylesheet" type="text/css" href="ext/modules/front_page/categories_images/stylesheet.css" />' . PHP_EOL;
              $header .= '<script type="text/javascript"><!--' . PHP_EOL;
              $header .= 'function set_CSS(el_id, CSS_attr, CSS_val) {' . PHP_EOL;
              $header .= '  var el = document.getElementById(el_id);' . PHP_EOL;
              $header .= '  if (el) el.style[CSS_attr] = CSS_val;' . PHP_EOL;
              $header .= '}' . PHP_EOL;
              $header .= '//-->' . PHP_EOL;
              $header .= '</script>' . PHP_EOL;

              $oscTemplate->addBlock($header, 'header_tags');

              // Set up the on-page code
              $box_number = 1;
              $space_above = false;
              foreach ($categories_data as $category) {
                $categories_content .= '<div class="imageBox" id="box_' . $box_number . '"';
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_MOUSEOVER == 'True') {
                  // Change the colors in the next line to change the mousover color of the border
                  // See the User's Manual for instructions
                  $categories_content .= ' onmouseover="set_CSS(\'box_' . $box_number . '\',\'borderColor\',\'#aabbdd\')" onmouseout="set_CSS(\'box_' . $box_number . '\',\'borderColor\',\'#182d5c\')" ';
                }
                $categories_content .= '>';

                $categories_content .= '<div class="link_column"><a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';

                // Show the category image if selected
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE == 'True') {
                  $categories_content .= tep_image(DIR_WS_IMAGES . $category['image'], $category['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="2" vspace="3"');
                } //if (CATEGORIES_IMAGES_BOX_SHOW_IMAGE

                // Show the category name if selected
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME == 'True') {
                  $categories_content .= '<strong>' . $category['name'] . '</strong>';
                } //if (CATEGORIES_IMAGES_BOX_SHOW_NAME

                $categories_content .= '</a></div>';

                // Show the subcategories if selected
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES == 'True') {
                  $subcategories_query_raw = "
                                    select
                                      c.categories_id,
                                      cd.categories_name
                                    from
                                      " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                      join " . TABLE_CATEGORIES . " c
                                        on (c.categories_id = cd.categories_id)
                                    where
                                      c.parent_id = " . $category['id'] . "
                                      and cd.language_id = '" . (int) $languages_id . "'
                                    order by
                                      c.sort_order
                                  ";
                  //print 'Subcategories Query: ' . $subcategories_query_raw . '<br />';
                  $subcategories_query = tep_db_query($subcategories_query_raw);

                  while ($subcategories = tep_db_fetch_array($subcategories_query)) {
                    $categories_content .= '<div class="link_column"><a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id'] . '_' . $subcategories['categories_id']) . '" class="category_link_sub">' . $subcategories['categories_name'] . '</a></div>';
                  }
                } //if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES

                $categories_content .= '</div>' . PHP_EOL;

                $box_number++;
              } //foreach ($categories_data
              break;

              // Show products in row format, similar to Category list
            case 'Row' :
            default :
              $categories_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . PHP_EOL;
              foreach ($categories_data as $category) {
                $categories_content .= '<tr>' . PHP_EOL;
                // Show the products image if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE == 'True') {
                  $categories_content .= '<td>';
                  $categories_content .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';
                  $categories_content .= tep_image(DIR_WS_IMAGES . $category['image'], $category['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="2" vspace="3"');
                  $categories_content .= '</a>';
                  $categories_content .= '</td>' . PHP_EOL;
                } //if (CATEGORIES_IMAGES_BOX_SHOW_IMAGE

                // Show the products name if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME == 'True') {
                  $categories_content .= '<td>';
                  $categories_content .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';
                  $categories_content .= '<b>' . $category['name'] . '</b>';
                  $categories_content .= '</a>';
                  $categories_content .= '</td>' . PHP_EOL;
                } //if (CATEGORIES_IMAGES_BOX_SHOW_NAME

                $categories_content .= '</tr>' . PHP_EOL;
              } // foreach ($categories_data

              $categories_content .= '</table>' . PHP_EOL;
              break;

          } // switch

          $categories_content .= '  </div>' . PHP_EOL;
          $categories_content .= '<!-- Categories Images EOF -->' . PHP_EOL;

          $oscTemplate->addBlock($categories_content, $this->group);
        } // if( count
      }
    } // function execute

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS');
    }

    function install() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['directory'];
      }

    	$this->_load_header_tags_first();
      
    	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER', '25', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Categories Images', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS', 'True', 'Do you want to show the Categories Images box on the front page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Title', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE', 'Products', 'Title to show on the front page.', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Box Style', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_STYLE', 'Grid', 'Show the Categories Images box in grid format, floating (variable width) grid, or with each category on a line', '6', '3', 'tep_cfg_select_option(array(\'Grid\', \'Float\', \'Rows\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Box Mouseover', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_MOUSEOVER', 'True', 'Show the mouseover effect on each Category (Must select Float in Box style above)', '6', '4', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Columns', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS', '3', 'Number of columns of categories to show in the Categories Images box', '6', '5', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Image', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE', 'True', 'Show the category image in the Categories Images box', '6', '6', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Name', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME', 'True', 'Show the category name in the Categories Images box', '6', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Subcategories', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES', 'True', 'Show the subcategories list under each category (Float mode only)', '6', '8', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      foreach( $this->languages_array as $language_name ) {
        tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ( '" . ucwords( $language_name ) . " Title', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE_" . strtoupper( $language_name ) . "', 'Title %s', 'Enter the title that you want on your box in " . $language_name . " (%s inserts the current month).', '6', '14', now())" );
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['directory'];
      }
      
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_STYLE';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_MOUSEOVER';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME';
      $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES';
      
      foreach( $this->languages_array as $language_name ) {
        $keys[] = 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE_' . strtoupper( $language_name );
      }

      return $keys;
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