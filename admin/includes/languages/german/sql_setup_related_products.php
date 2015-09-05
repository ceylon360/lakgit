<?php
/*
  sql_setup_related_products.php
  SQL Setup Utility For Optional Related Products, Ver 4.0

  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/

define('HEADING_TITLE_ORP', 'SQL-Setup für Ähnliche Artikel');
define('TEXT_ORP_INTRODUCTION', 'Um Ihre Installation von Ähnliche Artikel abzuschließen, müssen eine Reihe von Änderungen an der Datenbank vorgenommen werden. Diese Setup-Seite beabsichtigt dies für Sie leicht zu machen. Mit dem Klick auf einen einzigen Button, wird Ihre Datenbank für Ihre Aktualisierung oder Neuinstallierung aktualisiert.');
define('TEXT_ORP_WARNING', 'Bitte beachten Sie: Es wird dringend empfohlen, dass Sie Ihre Datenbank sichern, bevor Sie Änderungen vornehmen. Obwohl dieses Script versucht Ihre Datenbank sicher zu aktualisieren, gilt immer noch "Verwendung auf eigene Gefahr". ');
define('SECTION_TITLE_NEW_INSTALL', 'Neuinstallierung');
define('SECTION_DESCRIPTION_NEW_INSTALL', 'Wenn dies Ihre erste Installation von Ähnliche Artikel ist, klicken Sie auf den folgenden Button, um die neuen SQL-Tabelle hinzuzufügen.');
define('SECTION_TITLE_UPGRADE', 'Aktualisieren von früheren Versionen auf Version 5.0 BS');
define('SECTION_DESCRIPTION_UPGRADE', 'Wenn Sie Ähnliche Artikel zuvor installiert hatten und auf Version 5.0 BS aktualisieren möchten, ist dies die auszuwählende Option. Klicken Sie auf den folgenden Button, und Ihre Konfigurationsoptionen werden aktualisiert, um den Änderungen in der Version 5.0 BS zu entsprechen, ohne dass die Daten, die Sie so hart erarbeitet haben, verlorengehen.');
define('SECTION_TITLE_REMOVE', 'Ähnliche Artikel aus der Datenbank entfernen.');
define('SECTION_DESCRIPTION_REMOVE', 'Egal, ob Sie alles entfernen und neu starten möchten, oder einfach nur, dieses Add-On desinstallieren möchten, ist dies die Option für Sie. Die Tabelle mit allen Ähnliche Artikel wird aus der Datenbank entfernt werden! Um Ihre Daten vor versehentlichem Löschen zu schützen, erfordert diese Option Bestätigung.');
define('TEXT_CONFIRM_REMOVE_SQL', 'Klicken Sie auf OK, um die Ähnliche Artikel Tabelle aus der SQL-Datenbank zu entfernen.');

define('IMAGE_BUTTON_NEW_INSTALL_SQL', 'SQL für eine Neue Installation von Ähnliche Artikel installieren, Version 5.0 BS');
define('IMAGE_BUTTON_UPGRADE_SQL', 'SQL für Upgrade von Ähnliche Artikel aktualisieren, Version 5.0 BS');
define('IMAGE_BUTTON_REMOVE_SQL', 'SQL für alle Versionen von Ähnliche Artikel Entfernen');
?>