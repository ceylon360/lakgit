<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_CATALOG,
    'apps' => array(
      array(
        'code' => FILENAME_CATEGORIES,
        'title' => BOX_CATALOG_CATEGORIES_PRODUCTS,
        'link' => tep_href_link(FILENAME_CATEGORIES)
      ),
	
	array(
        'code' => FILENAME_QUICK_INVENTORY,
        'title' => BOX_CATALOG_QUICK_INVENTORY,
        'link' => tep_href_link(FILENAME_QUICK_INVENTORY)
      ),
      array(
        'code' => FILENAME_PRODUCTS_ATTRIBUTES,
        'title' => BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES,
        'link' => tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES)
      ),
	 // recent view product
	  array(
		'code' => 'text_attributes.php',
		'title' => 'Text Attributes',
		'link' => tep_href_link('text_attributes.php')
	  ),
	  array(
        'code' => 'easypopulate.php',
        'title' => 'Easy Populate',
        'link' => tep_href_link('easypopulate.php')
      ),

      array(
        'code' => FILENAME_MANUFACTURERS,
        'title' => BOX_CATALOG_MANUFACTURERS,
        'link' => tep_href_link(FILENAME_MANUFACTURERS)
      ),
      array(
        'code' => FILENAME_REVIEWS,
        'title' => BOX_CATALOG_REVIEWS,
        'link' => tep_href_link(FILENAME_REVIEWS)
      ),
      array(
        'code' => FILENAME_SPECIALS,
        'title' => BOX_CATALOG_SPECIALS,
        'link' => tep_href_link(FILENAME_SPECIALS)
      ),
      array(
        'code' => FILENAME_PRODUCTS_EXPECTED,
        'title' => BOX_CATALOG_PRODUCTS_EXPECTED,
        'link' => tep_href_link(FILENAME_PRODUCTS_EXPECTED)
      ),
        /* Optional Related Products (ORP) */
      array(
        'code' => 'optional_related_products.php',
        'title' => BOX_CATALOG_CATEGORIES_RELATED_PRODUCTS,
        'link' => tep_href_link('optional_related_products.php')
      )	  
        //ORP:end
    )
  );
?>
