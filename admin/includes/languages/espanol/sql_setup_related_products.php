<?php
/*
  sql_setup_related_products.php
  SQL Setup Utility For Optional Related Products, Ver 4.0

  Copyright (c) 2007 Anita Cross (http://www.callofthewildphoto.com/)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
*/

define('HEADING_TITLE_ORP', 'Instalación de SQL para Productos relacionados');
define('TEXT_ORP_INTRODUCTION', 'Para completar la instalación de Productos relacionados, se deben hacer una serie de cambios en su base de datos. Esta página de configuración tiene la intención de que sea fácil para usted. Con el clic en un botón, la base de Datos se actualizará con la nueva tabla de Productos Relacionados Para su nueva Instalación o Actualización.');
define('TEXT_ORP_WARNING', 'Nota: Es muy recomendable que usted haga una copia de seguridad de su base de datos antes de realizar cambios. Aunque esta utilidad intenta añadir la nueva tabla de modo seguro, El uso es "Bajo su propia responsabilidad". ');
define('SECTION_TITLE_NEW_INSTALL', 'Nueva Instalación');
define('SECTION_DESCRIPTION_NEW_INSTALL', 'Si esta es su primera instalación de Productos relacionados, haga clic en el botón de abajo para crear la nueva tabla SQL.');
define('SECTION_TITLE_UPGRADE', 'Actualizar una versión anterior a la Versión 5.0 BS');
define('SECTION_DESCRIPTION_UPGRADE', 'Si ha instalado previamente Productos relacionados y desea actualizar a la versión 5.0 BS, esta es la opción por seleccionar. Haga clic en el botón de abajo y las opciones de configuración se actualizarán en correspondencia con los cambios en la versión 5.0 BS, sin afectar a los datos que ha preparado con tanto trabajo.');
define('SECTION_TITLE_REMOVE', 'Desinstalar la tabla Productos relacionados de la base de datos');
define('SECTION_DESCRIPTION_REMOVE', 'Tanto si desea eliminar todo y empezar de nuevo, o simplemente quiere eliminar esta contribución, esta es la opción para usted. La tabla con todos los productos relacionados será eliminado de su base de datos! Para proteger los datos contra el borrado accidental, esta opción requiere confirmación.');
define('TEXT_CONFIRM_REMOVE_SQL', 'Haga clic en Aceptar para eliminar Productos relacionados de su base de datos SQL.');

define('IMAGE_BUTTON_NEW_INSTALL_SQL', 'Instalar SQL para Nueva Instalación de Productos relacionados, Versión 5.0, BS');
define('IMAGE_BUTTON_UPGRADE_SQL', 'Actualización de SQL productos relacionados, Versión 5.0, BS');
define('IMAGE_BUTTON_REMOVE_SQL', 'Quite SQL para todas las versiones de Productos Relacionados');
?>