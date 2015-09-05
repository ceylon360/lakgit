<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_footer_modal_cart {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_footer_modal_cart() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_FOOTER_MODAL_CART_TITLE;
      $this->description = MODULE_CONTENT_FOOTER_MODAL_CART_DESCRIPTION;

      if ( defined('MODULE_CONTENT_FOOTER_MODAL_CART_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_FOOTER_MODAL_CART_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_FOOTER_MODAL_CART_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $cart, $currencies, $languages_id;

      if (tep_session_is_registered('new_products_id_in_cart') && DISPLAY_CART == 'false') {

        $cart_contents_string = '<table class="table table-striped table-condensed">';
        if ($cart->count_contents() > 0) {
          $products = $cart->get_products();
          for ($i=0, $n=sizeof($products); $i<$n; $i++) {
            $cart_contents_string .= '<tr>';
			//image
            $cart_contents_string .= '<td class="hidden-xs hidden-sm"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], $width = '50px', $height = '50px') . '</a></td>';

			$cart_contents_string .= '<td>';
            $cart_contents_string .= $products[$i]['quantity'] . ' x <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">';
            $cart_contents_string .= $products[$i]['name'] . '</a>';

            // Push all attributes information in an array
            if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
              while (list($option, $value) = each($products[$i]['attributes'])) {
                $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                            from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                            where pa.products_id = '" . (int)$products[$i]['id'] . "'
                                             and pa.options_id = '" . (int)$option . "'
                                             and pa.options_id = popt.products_options_id
                                             and pa.options_values_id = '" . (int)$value . "'
                                             and pa.options_values_id = poval.products_options_values_id
                                             and popt.language_id = '" . (int)$languages_id . "'
                                             and poval.language_id = '" . (int)$languages_id . "'");
                $attributes_values = tep_db_fetch_array($attributes);

                $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
                $products[$i][$option]['options_values_id'] = $value;
                $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
                $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
                $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];

                $cart_contents_string .= '<br /><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
              }
            }

            $cart_contents_string .= '</td>';
            //image
           // $cart_contents_string .= '<td class="hidden-xs hidden-sm"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], $width = '50px', $height = '50px') . '</a></td>';

            $products_tax = tep_get_tax_rate($products[$i]['tax_class_id']);
            $popup_price = $currencies->calculate_price($products[$i]['final_price'], $products_tax, $products[$i]['quantity']);
            $cart_contents_string .= '<td style="text-align: right; padding: 1px;" width="20%">'  . $currencies->format($popup_price) . '</td></tr>';
          }
        }
        $cart_contents_string .= '</table>';

        ob_start();
        include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/modal_cart.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);

$script = <<<EOL
<script type="text/javascript">
  $(window).load(function(){
      $('#upCart').modal('show');
  });
</script>
EOL;
        $oscTemplate->addBlock($script, 'footer_scripts');
        tep_session_unregister('new_products_id_in_cart');
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_FOOTER_MODAL_CART_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Generic Modal Cart Footer Module', 'MODULE_CONTENT_FOOTER_MODAL_CART_STATUS', 'True', 'Do you want to enable the Modal Cart content module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_FOOTER_MODAL_CART_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      $keys = array('MODULE_CONTENT_FOOTER_MODAL_CART_STATUS', 'MODULE_CONTENT_FOOTER_MODAL_CART_SORT_ORDER');
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $keys) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_FOOTER_MODAL_CART_STATUS', 'DISPLAY_CART', 'MODULE_CONTENT_FOOTER_MODAL_CART_SORT_ORDER');
    }
  }

