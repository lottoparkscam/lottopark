jQuery(document).on('ready', function () {
  jQuery(document).on('widget-added', function (event, widget) {
    jQuery(widget)
      .find('.lotto-toggle')
      .on('click', function (e) {
        e.preventDefault();

        const toggleButtonText = jQuery(this).text();
        jQuery(this).parent().parent().next().toggle();
        jQuery(this).text(jQuery(this).data('togglename'));
        jQuery(this).data('togglename', toggleButtonText);
      });

    var gk_media_init = function (selector, button_selector) {
      var clicked_button = false;

      jQuery(selector).each(function (i, input) {
        var button = jQuery(input).next(button_selector);
        button.on('click', function (e) {
          e.preventDefault();
          clicked_button = jQuery(this);

          if (window.wp.media.frames.gk_frame) {
            window.wp.media.frames.gk_frame.open();
            return;
          }
          window.wp.media.frames.gk_frame = window.wp.media({
            title: 'Select image',
            multiple: false,
            library: {
              type: 'image',
            },
            button: {
              text: 'Use selected image',
            },
          });

          var gk_media_set_image = function () {
            var selection = window.wp.media.frames.gk_frame
              .state()
              .get('selection');

            if (!selection) {
              return;
            }

            selection.each(function (attachment) {
              var url = attachment.attributes.url;
              clicked_button.prev(selector).val(url);
              clicked_button.prev(selector).change();
            });
          };

          window.wp.media.frames.gk_frame.on('close', gk_media_set_image);
          window.wp.media.frames.gk_frame.on('select', gk_media_set_image);
          window.wp.media.frames.gk_frame.open();
        });
      });
    };

    gk_media_init('.media-input', '.media-button');

    jQuery(widget)
      .find('.lotto-widget-featured-type')
      .on('change', function () {
        if (jQuery(this).val() == 4) {
          jQuery(this)
            .parent()
            .parent()
            .find('.lotto-widget-featured-toggle-order')
            .removeClass('hidden');
        } else {
          jQuery(this)
            .parent()
            .parent()
            .find('.lotto-widget-featured-toggle-order')
            .addClass('hidden');
        }
      });

    jQuery(widget)
      .find('.lotto-featured-type-select')
      .on('change', function () {
        if (jQuery(this).val() == 3) {
          jQuery(this)
            .parent()
            .parent()
            .find('.lotto-featured-type-content')
            .slideDown();
        } else {
          jQuery(this)
            .parent()
            .parent()
            .find('.lotto-featured-type-content')
            .slideUp();
        }
      });

    function allowedSidebars(allowed) {
      // this variable will have index of first visible sidebar
      var first = null;
      jQuery(widget)
        .find('.widgets-chooser-sidebars li')
        .removeClass('widgets-chooser-selected')
        .each(function (index) {
          // the data('sidebarId') is set up by wordpress, let's make us of it
          if (-1 === jQuery.inArray(jQuery(this).data('sidebarId'), allowed)) {
            jQuery(this).hide();
          } else if (first == null) {
            first = index;
          }
        });
      // choose first visible sidebar as default
      if (first != null) {
        jQuery(widget)
          .find('.widgets-chooser-sidebars li')
          .eq(first)
          .addClass('widgets-chooser-selected');
      }
    }

    jQuery(widget)
      .find('#available-widgets .widget .widget-title')
      .on('click.widgets-chooser', function () {
        let widget = jQuery(this).closest('.widget');
        // we want to run our script only on slideDown, not slideUp
        if (!widget.hasClass('widget-in-question')) {
          // there is only one sidebar list per all widgets, so we have to show all the sidebars every time
          // additionally make the first sidebar selected by default
          jQuery(widget)
            .find('.widgets-chooser-sidebars li')
            .removeClass('widgets-chooser-selected')
            .show()
            .eq(0)
            .addClass('widgets-chooser-selected');
          switch (widget.find('input[name="id_base"]').val()) {
            // Important! This switch case does not have break; intentionally. It works like OR condition, so multiple cases get same behaviour
            case 'lotto_platform_widget_featured':
            case 'lotto_platform_widget_list':
            case 'lotto_platform_widget_banner':
            case 'lotto_platform_widget_raffle_carousel':
              allowedSidebars([
                'frontpage-sidebar-id',
                'play-lottery-sidebar-id',
                'play-lottery-more-sidebar-id',
                'play-sidebar-id',
                'play-more-sidebar-id',
                'results-sidebar-id',
                'results-more-sidebar-id',
                'lottery-results-sidebar-id',
                'lottery-results-more-sidebar-id',
                'info-sidebar-id',
                'info-more-sidebar-id',
                'lottery-info-sidebar-id',
                'lottery-info-more-sidebar-id',
                'page-raffle-sidebar-top-id',
              ]);
              break;
            case 'lotto_platform_widget_draw':
            case 'lotto_platform_widget_ticket':
              // allowed sidebars for widget
              allowedSidebars([
                'lottery-results-sidebar-id',
                'lottery-results-more-sidebar-id',
                'lottery-info-sidebar-id',
                'lottery-info-more-sidebar-id',
                'play-lottery-sidebar-id',
                'play-lottery-more-sidebar-id',
              ]);
              break;
            case 'lotto_platform_widget_news':
              allowedSidebars([
                'frontpage-sidebar-id',
                'play-lottery-sidebar-id',
                'play-lottery-more-sidebar-id',
                'play-sidebar-id',
                'play-more-sidebar-id',
                'results-sidebar-id',
                'results-more-sidebar-id',
                'lottery-results-sidebar-id',
                'lottery-results-more-sidebar-id',
                'info-sidebar-id',
                'info-more-sidebar-id',
                'lottery-info-sidebar-id',
                'lottery-info-more-sidebar-id',
                'casino-frontpage-sidebar-bottom-id',
                'casino-frontpage-sidebar-top-id',
                'casino-frontpage-sidebar-content-id',
                'page-raffle-sidebar-top-id',
              ]);
              break;
          }
        }
      });
    jQuery(widget)
      .find('.widget')
      .on('dragcreate dragstart', function (event, ui) {
        switch (jQuery(this).find('input[name="id_base"]').val()) {
          // Important! This switch case does not have break; intentionally. It works like OR condition, so multiple cases get same behaviour
          case 'lotto_platform_widget_featured':
          case 'lotto_platform_widget_list':
          case 'lotto_platform_widget_banner':
          case 'lotto_platform_widget_raffle_carousel':
            jQuery(this).draggable({
              connectToSortable:
                '#frontpage-sidebar-id, #play-lottery-sidebar-id, #play-lottery-more-sidebar-id, #play-sidebar-id, #play-more-sidebar-id, #results-sidebar-id, #results-more-sidebar-id, #lottery-results-sidebar-id, #lottery-results-more-sidebar-id, #info-sidebar-id, #info-more-sidebar-id, #lottery-info-sidebar-id, #lottery-info-more-sidebar-id, #page-raffle-sidebar-top-id',
            });
            break;
          case 'lotto_platform_widget_draw':
          case 'lotto_platform_widget_ticket':
            jQuery(this).draggable({
              connectToSortable:
                '#lottery-results-sidebar-id, #lottery-results-more-sidebar-id, #lottery-info-sidebar-id, #lottery-info-more-sidebar-id, #play-lottery-sidebar-id, #play-lottery-more-sidebar-id',
            });
            break;
          case 'lotto_platform_widget_news':
            jQuery(this).draggable({
              connectToSortable:
                '#frontpage-sidebar-id, #play-lottery-sidebar-id, #play-lottery-more-sidebar-id, #play-sidebar-id, #play-more-sidebar-id, #results-sidebar-id, #results-more-sidebar-id, #lottery-results-sidebar-id, #lottery-results-more-sidebar-id, #info-sidebar-id, #info-more-sidebar-id, #lottery-info-sidebar-id, #lottery-info-more-sidebar-id, #casino-frontpage-sidebar-bottom-id, #casino-frontpage-sidebar-top-id, #casino-frontpage-sidebar-content-id, #page-raffle-sidebar-top-id',
            });
            break;
        }
      });

    var colorPickerLoaded = false;

    jQuery(document).on('click', '.widget-top', function () {
      if (!colorPickerLoaded) {
        loadWpColorPicker();
      }
    });
    jQuery(document).on('widget-added widget-updated', function () {
      loadWpColorPicker();
    });

    function loadWpColorPicker() {
      jQuery('.color-picker, .inactive-sidebar .color-picker').wpColorPicker({
        change: _.throttle(function () {
          // Trigger change every 3 sec to update widget preview
          jQuery(this).trigger('change');
        }, 3000),
      });
      colorPickerLoaded = true;
    }
  });
});
