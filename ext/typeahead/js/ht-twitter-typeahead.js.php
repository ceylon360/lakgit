<?php
/*
  $Id$ Twitter Typeahead Autocomplete Search v1.2 for oscommerce 2.3.4BS

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/
?>

<script type="text/javascript"><!--
var search_input = $('form[name="quick_find"] input[name="keywords"]');

if (search_input.is(":visible")) {

  var limit_list = <?php echo (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_LIMIT_LIST > 0 ? MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_LIMIT_LIST : 10); ?>;
  var min_length = <?php echo (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_MIN_LENGTH > 1 ? MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_MIN_LENGTH : 2); ?>;
  var sort_list = <?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SORT_LIST; ?>;
  var keyword_highlight = <?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_KEYWORD_HIGHLIGHT; ?>;
  var keyword_hint = <?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_KEYWORD_HINT; ?>;
  var focus_onKeyword = <?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_FOCUS_ON_KEYWORD; ?>;
  var product_preview = <?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_PRODUCT_PREVIEW; ?>;
  var product_preview_delay = <?php echo (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_PRODUCT_PREVIEW_DELAY > 0 ? MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_PRODUCT_PREVIEW_DELAY : 600); ?>;
  var show_popover_info = <?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SHOW_POPOVER_INFO; ?>;
  var suggestion_menu_height = <?php echo (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_MAX_HEIGHT > 0 ? MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_MAX_HEIGHT : 292); ?>;
  var suggestion_menu_height_options_adj = 17; // adjustment for the suggestion header when "view all results" and/or "advanced search results" is shown - x 1 for one, x2 for two
  var cursorchange_suggestion = '';
  var suggestion_length = 0;

  var suggestion_header_options = 0;
  if ('<?php echo basename($PHP_SELF) != 'advanced_search.php'; ?>') {
    suggestion_header_options = 1;
  }

  var search_autocomplete_attr = search_input.attr('autocomplete');
  if (typeof search_autocomplete_attr === typeof undefined || search_autocomplete_attr === false || search_autocomplete_attr == 'on') {
    search_input.attr('autocomplete', 'off');
  }

  if (product_preview == true) {
    var bodyContent = $("#bodyContent");
    var bodyContent_class = bodyContent.attr('class');
    bodyContent.after('<div id="bodyContent_preview" class="'+bodyContent_class+'" style="display: none;"></div>');
    var bodyContent_preview = $("#bodyContent_preview");
    var current_preview = 0;
    var changetimer;
    var fadetimer;
  }

  $(function() {

    if (show_popover_info) {
      var cookie_js_loaded = $('script[src*="ext/jquery/cookie.js"]').length;
      var popover_cookie = 'false';
      if (!cookie_js_loaded) {
        $.getScript('ext/jquery/cookie.js')
	        .done(function() {
                popover_cookie = $.cookie('popover') || 'true';
                do_typeahead(popover_cookie, cookie_js_loaded);
        });
      } else {
        popover_cookie = $.cookie('popover') || 'true';
        do_typeahead(popover_cookie, cookie_js_loaded);
      }
    } else {
      do_typeahead('false', false);
    }

  });

  function do_typeahead(popover_cookie, cookie_js_loaded) {
  
    if (show_popover_info) {
      if (popover_cookie == 'false') {
        show_popover_info = false;
//        show_popover_info = true; // for testing cookie
      } else {
        var popover_shown = false;
      }
    }

    if (show_popover_info) {
      search_input.popover({
        animation: true,
        trigger: 'manual',
        placement: 'auto',
        html: true,
        title: '<?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_POPOVER_TITLE; ?><button id="popoverclose" type="button" class="close" aria-label="Close" title="<?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_POPOVER_CLOSE; ?>">&times;</button>',
        content: '<?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_POPOVER_CONTENT; ?>'
      });
    }

    var products = new Bloodhound({
      datumTokenizer: function(datum) {
        return Bloodhound.tokenizers.whitespace(datum.name);
      },
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      remote: {
        url: "ext/modules/header_tags/twitter_typeahead/autocomplete.php?term=%query",
        wildcard : '%query',
        transform: function(data) {
          suggestion_length = data.length;

          if (sort_list) {
            var key = $.trim(search_input.val());
            key = key.replace(/["\\()]+/g,'');
            key = key.replace(/ +/g," ");
            var srchTerms = key.split(" ");
            var sort_key = "";

            $.each(srchTerms, function( index, value ){
              if (index > 0) {
                sort_key += " ";
              }
              sort_key += value;

              var startsWithMatcher = new RegExp("^" + sort_key, "i")
                , startsWith = $.grep(data, function(value) {
                    return startsWithMatcher.test(value.name);
                })
                , notStartsWith = $.grep(data, function(value) {
                    return 0 > $.inArray(value, startsWith);
                });
              data = startsWith.concat(notStartsWith);
            });
          }

          return $.map(data, function(product) {
              return {
                  name: product.name,
                  price: product.price,
                  model: product.model,
                  manufacturer: product.manufacturer,
                  image: product.img,
                  link: product.link,
                  pid: product.pid
              };
          });

        }
      }
    });

    products.initialize();

    search_input.addClass('twitter-typeahead');

    search_input.typeahead({
      minLength: min_length,
      hint: keyword_hint,
      highlight: false // not using built-in highlight as it does not match letters from mutiple keywords to multiple word suggestions, using function replacer(str) further below
    },
    {
      name: 'products',
      display: 'name',
      limit: limit_list,
      source: products.ttAdapter(),
      templates: {
         notFound: function() {
            if (show_popover_info && popover_shown) {
              search_input.popover('hide');
              popover_shown = false;
            }
            return '<div><div style="padding: 0 10px 0px 10px; color: red;"><?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_NOTFOUND; ?><button id="menuclose" type="button" class="close notfound" aria-label="Close" title="<?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_CLOSE; ?>">&times;</button></div><?php echo (basename($PHP_SELF) != 'advanced_search.php' ? '<div style="padding: 0px 10px 5px 20px; font-size: 12px;"><span class="fa fa-angle-right" style="color: #747EBB;"></span> <a href="' . tep_href_link('advanced_search.php', 'keywords=\'+search_input.val()+\'', 'NONSSL', true, false) . '">' . MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_LINK_ADVANCED_SEARCH . '</a></div>' : ''); ?></div>';
         },
         pending: '<img src="images/ui-anim_basic_16x16.gif" style="padding-left: 10px;" />',
         header: function() {

            var header_options = suggestion_header_options;
            var menu_height = suggestion_menu_height;

            if (suggestion_length > limit_list) {
              header_options++;
            }
            if (header_options == 1) {
              menu_height = menu_height+suggestion_menu_height_options_adj;
            } else if (header_options == 2) {
              menu_height = menu_height+suggestion_menu_height_options_adj*2;
            }
            $('form[name="quick_find"] .tt-menu').css({"max-height" : menu_height+"px"});

            return '<div style="border-bottom: 1px solid #E9E7E7;"><div style="padding: 0 10px 0px 10px;"><span style="font-size: 14px; font-weight: bold;"><?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_HEADING; ?></span><span style="font-size: 12px; color: #5D5F61;"> ('+(suggestion_length > limit_list ? '<?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_HEADING_SHOWING; ?> '+limit_list+' <?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_HEADING_OF; ?> '+suggestion_length : '<?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_HEADING_TOTAL; ?> '+suggestion_length)+')</span><button id="menuclose" type="button" class="close found" aria-label="Close" title="<?php echo MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_CLOSE; ?>">&times;</button></div>'+
                   '<div style="padding: 0px 10px 5px 20px; font-size: 12px;">'+(suggestion_length > limit_list ? '<span class="fa fa-angle-right" style="color: #747EBB;"></span> <?php echo '<a href="' . tep_href_link('advanced_search_result.php', 'keywords=\'+search_input.val()+\'', $request_type, false) . '">' . MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_LINK_VIEW_ALL . '</a>'; ?><br />' : '')+'<?php echo (basename($PHP_SELF) != 'advanced_search.php' ? '<span class="fa fa-angle-right" style="color: #747EBB;"></span> <a href="' . tep_href_link('advanced_search.php', 'keywords=\'+search_input.val()+\'', 'NONSSL', true, false) . '">' . MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_SUGGESTIONS_LINK_ADVANCED_SEARCH . '</a>' : ''); ?></div></div>';
         },
         suggestion: function(data) {

            if (keyword_highlight) {
              var key = $.trim(search_input.val());
              key = key.replace(/["\\()]+/g,'');
              key = key.replace(/ +/g," ");
              var srchTerms = key.split(" ");
              var re = new RegExp(srchTerms.join("|"), "gi");
              var n = data.name.replace(re, replacer);
              var m = data.model != '' ? data.model.replace(re, replacer) : '';
              var mf = data.manufacturer != '' ? data.manufacturer.replace(re, replacer) : '';
            } else {
              var n = data.name;
              var m = data.model;
              var mf = data.manufacturer;
            }

            return "<div style='padding: 0px 10px 0px 5px;'><table border='0' width='100%' cellspacing='0' cellpadding='0'><tr><td valign='center' align='center' width='10%' style='padding-right: 6px;'><img src='"+data.image+"' width='40' height='' /></td>"+
                   "<td valign='center' align='left' width='90%' style='padding: 5px 0px 5px 0px;'><span style='font-size: 14px; line-height: 1.0;'>"+n+"<br />"+(mf == '' ? "" : "<span style='color: #6C6B6B; font-size: 12px;'>"+mf+"</span>&nbsp;&#32;")+(m == '' ? "" : "<span style='color: #6C6B6B; font-size: 12px;'>&#91;"+m+"&#93;</span>&nbsp;&#32;")+data.price+"</span></td></tr></table><style>img[src='']{display: none;}</style></div>";
         }
      }
    }).on('typeahead:render', function(ev, selections, async, ds) {
      $(".tt-menu").css({"opacity": 1.0});
      suggestion_length = selections.length;
      if (show_popover_info && !popover_shown && suggestion_length > 0) {
        $(this).popover('show');
        popover_shown = true;
      }
    }).on('typeahead:close', function() {
      if (show_popover_info && popover_shown) {
        $(this).popover('hide');
        popover_shown = false;
      }
    }).on('typeahead:select', function(ev, selection, ds) {
        location.href = selection.link;
    }).on('typeahead:cursorchange', function(ev, suggestion, ds) {
      if (focus_onKeyword == false && suggestion) {
        cursorchange_suggestion = suggestion.name;
      } else {
        cursorchange_suggestion = $(this).typeahead('val');
      }
      if (product_preview == true) {
        $(".tt-menu").css({"opacity": 1.0});
        if (changetimer) {
          clearTimeout(changetimer);
        }
        changetimer = setTimeout(function(){
          if (suggestion && current_preview != suggestion.pid) {
            if (bodyContent.is(":visible")) {
              bodyContent.fadeTo("fast", 0.5).hide();
              bodyContent_preview.html('').show();
            } else {
              bodyContent_preview.html('').fadeTo("fast", 0.5);
            }
            bodyContent_preview.css({"min-height": "400px"});
            if (fadetimer) {
              clearTimeout(fadetimer);
            }
            fadetimer = setTimeout(function(){
              current_preview = suggestion.pid;
              $(".tt-menu").css({"opacity": 0.5});
              bodyContent_preview.html('<div class="text-center" style="padding: 150px 0 150px 0;"><img src="images/ajax-loader.gif" /></div>');
              bodyContent_preview.load("ext/modules/header_tags/twitter_typeahead/product_info_tt.php?products_id="+suggestion.pid, function(){ bodyContent_preview.css({"min-height": "auto"}); $(".tt-menu").css({"opacity": 0.95}); }).fadeTo("fast", 1);
            }, 100);
          } else {
            $(".tt-menu").css({"opacity": 0.95});
          }
        }, product_preview_delay);
      }
    }).keydown(function(event) {
      if (event.keyCode == 27) {
        event.preventDefault();
        close_menu($(this));
      }
      if (cursorchange_suggestion != '') {
        if (event.altKey == false && event.ctrlKey == false) {
          if ((event.keyCode >= 48 && event.keyCode <= 57 && event.shiftKey == false) ||
              (event.keyCode >= 65 && event.keyCode <= 90) ||
              (event.keyCode >= 96 && event.keyCode <= 111) || event.keyCode == 8  || event.keyCode == 46) {
            cursorchange_suggestion = '';
          }
        }
      }
    }).blur(function() {
      if (cursorchange_suggestion != '') {
        $(this).val(cursorchange_suggestion);
      }
    }).focus(function() {
      if (show_popover_info && !popover_shown && suggestion_length !== 0) {
        $(this).popover('show');
        popover_shown = true;
      }
    }).change(function() {
      if ($(this).val() == '' && cursorchange_suggestion != '') {
        cursorchange_suggestion = '';
      }
    });

    $(document).click(function(e) {
      if (show_popover_info) {
        if (e.target.id=="popoverclose") {
          search_input.popover('destroy');
          search_input.focus();
          show_popover_info = false;
          if (!cookie_js_loaded) {
            $.getScript('ext/jquery/cookie.js')
	            .done(function() {
                    $.cookie('popover', 'false');
//                    $.cookie('popover', 'true'); // for testing cookie
            });
          } else {
            $.cookie('popover', 'false');
//            $.cookie('popover', 'true'); // for testing cookie
          }
        }
      }

      if (e.target.id=="menuclose") {
        close_menu(search_input);
      }
    });

    $(document).keydown(function(e) {
      if (e.keyCode == 27 && $(".tt-menu").is(":visible")) {
        close_menu(search_input);
      }
    });

  } // end function do_typeahead

  function replacer(str) {
    return '<span style="font-weight: <?php echo (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_HIGHLIGHT_BOLD == 'true' ? 'bold' : 'normal'); ?>; color: #<?php echo (MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_HIGHLIGHT_COLOR != '' ? MODULE_HEADER_TAGS_TWITTER_TYPEAHEAD_HIGHLIGHT_COLOR : '5543C6'); ?>">' + str + '</span>';
  }

  function close_menu(el) {
    if (cursorchange_suggestion != '') {
      el.typeahead('val', cursorchange_suggestion);
    }
    el.typeahead('close');
    el.blur();
  }

  function fix_piGal() {
    var attr = $(".piGal img").attr('id');
    if (typeof attr === typeof undefined || attr === false) {
      $(".piGal img").attr('id', 'piGalImg_1');
    }
  }

  if ('<?php echo basename($PHP_SELF); ?>' == 'product_info.php') {
    fix_piGal();
  }

} // end if (search_input.is(":visible"))
//--></script>
