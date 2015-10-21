<?php
require('includes/application_top.php');

//tep_db_query("ALTER TABLE `categories_description` ADD `categories_restrict` VARCHAR(64) NULL DEFAULT NULL ");
tep_db_query("ALTER TABLE `orders` ADD `surprise` VARCHAR(64) NULL DEFAULT NULL ");
tep_db_query("ALTER TABLE `orders` ADD `anonymous` VARCHAR(64) NULL DEFAULT NULL ");
?>