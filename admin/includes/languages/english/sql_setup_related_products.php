<?php
/*
  sql_setup_related_products.php
  SQL Setup Utility For Optional Related Products, Ver 4.0

  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/

define('HEADING_TITLE_ORP', 'SQL Setup for Optional Related Products');
define('TEXT_ORP_INTRODUCTION', 'To complete your installation of Optional Related Products, a number of changes must be made to your database. This setup page is intended to make that easy for you. With the click of a single button, your database will be updated with the new related products table for your new or upgrade install.');
define('TEXT_ORP_WARNING', 'Please Note: It is highly recommended that you backup your database before making changes. Although this utility is intended to add the new table, it is still "Use At Your Own Risk". ');
define('SECTION_TITLE_NEW_INSTALL', 'New Install');
define('SECTION_DESCRIPTION_NEW_INSTALL', 'If this is your first installation of Optional Related Products, click on the button below to create the new SQL table.');
define('SECTION_TITLE_UPGRADE', 'Upgrade Earlier Version to Version 5.0 BS');
define('SECTION_DESCRIPTION_UPGRADE', 'If you have previously installed Optional Related Products and want to upgrade to Version 5.0 BS, this is the option to select. Click on the button below and your configuration options will be updated to correspond with the changes in version 5.0 BS, without affecting the data you\'ve worked so hard to prepare.');
define('SECTION_TITLE_REMOVE', 'Remove Optional Related Products from the Database');
define('SECTION_DESCRIPTION_REMOVE', 'Whether you want to remove everything and start over, or just want to remove this contribution, this is the option for you. The table with all the Optional Related Products will be removed from your database! To protect your data from accidental deletion, this option requires confirmation.');
define('TEXT_CONFIRM_REMOVE_SQL', 'Click on OK to remove Optional Related Products from your SQL database.');

define('IMAGE_BUTTON_NEW_INSTALL_SQL', 'Install SQL for New Install of Related Products, Version 5.0 BS');
define('IMAGE_BUTTON_UPGRADE_SQL', 'Update SQL for Upgrade Install of Related Products, Version 5.0 BS');
define('IMAGE_BUTTON_REMOVE_SQL', 'Remove SQL for all versions of Related Products');
?>