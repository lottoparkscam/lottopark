/* load fonts ASAP */
const fontFamily = jQuery('body').data('family');
if (typeof fontFamily !== 'undefined' && fontFamily) {
  WebFont.load({
    google: {
      families: [jQuery('body').data('family') + ':latin-ext&display=optional'],
    },
    active: function () {
      if ($grid != null) {
        $grid.masonry('layout');
      }
    },
  });
}

/**
 * Set cookie.
 *
 * @param {string} name   - Cookie name
 * @param {string} value  - Cookie value
 * @param {number} expiry - Cookie expiry time in days
 *
 * @return {void}
 */
function setCookie(name, value, expiry) {
  let date = new Date();
  date.setTime(date.getTime() + expiry * 86400000);

  document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}

/**
 * Check if cookie is set.
 *
 * @param {string} name - Cookie name
 *
 * @return {boolean}
 */
function isCookieSet(name) {
  return document.cookie
    .split('; ')
    .some((value) => value.trim().startsWith(`${name}=`));
}

/**
 * Geobanner USA
 */
(function () {
  const settingsCookieExpiryInDays = 1;
  const settingsCookieName = 'GeobannerUSA';

  if (isCookieSet(settingsCookieName)) {
    return;
  }

  const geobanner = document.getElementById('geobanner-usa');

  if (!geobanner) {
    return;
  }

  const geobannerBtnClose = document.getElementById('geobanner-close');

  geobanner.classList.add('show');

  geobanner.addEventListener('click', function (e) {
    if (e.target === this || e.target.classList.contains('geobanner-dialog')) {
      geobanner.remove();
      setCookie(settingsCookieName, 'true', settingsCookieExpiryInDays);
    }
  });

  geobannerBtnClose.addEventListener('click', function () {
    geobanner.remove();
    setCookie(settingsCookieName, 'true', settingsCookieExpiryInDays);
  });
})();

/**
 * Geobanner Cameroon
 */
(function () {
  const settingsCookieExpiryInDays = 1;
  const settingsCookieName = 'GeobannerCameroon';

  if (isCookieSet(settingsCookieName)) {
    return;
  }

  const geobanner = document.getElementById('geobanner-cameroon');

  if (!geobanner) {
    return;
  }

  const geobannerBtnClose = document.getElementById('geobanner-close');

  geobanner.classList.add('show');

  geobanner.addEventListener('click', function (e) {
    if (e.target === this || e.target.classList.contains('geobanner-dialog')) {
      geobanner.remove();
      setCookie(settingsCookieName, 'true', settingsCookieExpiryInDays);
    }
  });

  geobannerBtnClose.addEventListener('click', function () {
    geobanner.remove();
    setCookie(settingsCookieName, 'true', settingsCookieExpiryInDays);
  });
})();

var $grid = null;

if (jQuery('.latest-news-content').length) {
  $grid = jQuery('.latest-news-content').masonry({
    itemSelector: '.news-container',
    columnWidth: '.grid-sizer',
    gutter: '.gutter-sizer',
    //percentPosition: true,
    transitionDuration: 0,
  });
}

jQuery(document).ready(function ($) {
  $('.mobile-menu-trigger').click(function (e) {
    e.preventDefault();
    $('nav#primary-nav').css('display', 'block');
    $('body').addClass('menu-active');
  });
  $('#mobile-close').click(function (e) {
    e.preventDefault();
    $('nav#primary-nav').css('display', '');
    $('body').removeClass('menu-active');
  });
  $('#inputCountry').change(function () {
    if ($(this).val() != '' && $('#inputPhone').val().length == 0) {
      var prefixes = $(
        '#inputPrefix option[data-territory="' + $(this).val() + '"]',
      );
      if (prefixes.length) {
        $('#inputPrefix').val(prefixes.slice(0, 1).val());
      }
    }
    if ($(this).val() != '') {
      $('#inputState').prop('disabled', false);
      $('#inputState').children().remove();
      $('#allRegions')
        .children()
        .each(function (index) {
          if (
            $(this).data('country') == $('#inputCountry').val() ||
            index == 0
          ) {
            $('#inputState').append($(this).clone());
          }
        });
      if ($('#inputState').children().length <= 1) {
        $('#inputState').prop('disabled', true);
      }
    } else {
      $('#inputState').append($('#allRegions').children().eq(0));
      $('#inputState').prop('disabled', true);
      $('#inputState').children().remove();
    }
  });

  if (
    $('#inputBirthdate').length &&
    $('#inputBirthdate').prop('disabled') == false
  ) {
    var dp_options = {
      changeMonth: true,
      changeYear: true,
      minDate: '-110Y',
      maxDate: '-18Y',
      yearRange: '-110:-18',
      dateFormat: $('#inputBirthdate').data('dateformat'),
      altFormat: 'yy-mm-dd',
      altField: $('#inputBirthdatePost'),
    };
    dp_options.defaultDate = $('#inputBirthdate').val();
    $('#inputBirthdate').datepicker(dp_options);
  }

  if ($('.latest-news-content').length) {
    $('.latest-news-mobile-pagination .page-numbers').click(function (e) {
      e.preventDefault();
      var act = $(this).parent().data('act');
      if ($(this).hasClass('next')) {
        act++;
      } else if ($(this).hasClass('prev')) {
        act--;
      } else {
        act = parseInt($(this).text());
      }
      if (act == 0) {
        act = 1;
      }
      var pages = $(this).parent().find('.page-numbers');
      if (act > pages.length - 2) {
        act = pages.length - 2;
      }
      $(this).parent().data('act', act);
      pages.removeClass('current');
      pages.eq(act).addClass('current');
      var show_next = 2;
      var show_prev = 2;
      var add_next = 0;
      var add_prev = 0;
      var sub_next = 0;
      var sub_prev = 0;
      var total = pages.length - 2;
      if (act + show_next > total) {
        // o ile zmniejszyc show_next teraz?
        sub_next = total - act - show_next; // to bedzie wartosc na minusie
        // ale chcemy zwiekszyc widocznosc poprzednich
        add_prev = -sub_next;
      }
      show_next += sub_next;

      show_prev += add_prev;

      if (act - show_prev < 1) {
        sub_prev = act - show_prev - 1;
        add_next = -sub_prev;
      }
      show_prev += sub_prev; // musi byc odwrocone, bo dodajac, chcemy zmniejszyc
      show_next += add_next;

      pages.slice(1, -1).addClass('hidden-normal');
      pages
        .slice(act - show_prev, act + show_next + 1)
        .removeClass('hidden-normal');

      pages.last().removeClass('page-numbers-inactive');
      pages.first().removeClass('page-numbers-inactive');
      if (act == total) {
        pages.last().addClass('page-numbers-inactive');
      }
      if (act == 1) {
        pages.first().addClass('page-numbers-inactive');
      }

      $(this)
        .parent()
        .prev()
        .find('.news-container')
        .addClass('latest-news-mobile-hide');
      $(this)
        .parent()
        .prev()
        .find('.news-container')
        .eq(act - 1)
        .removeClass('latest-news-mobile-hide');
      $grid.masonry();
      $('html, body').animate(
        { scrollTop: $(this).parents('.widget').offset().top },
        500,
      );
    });
  }

  $('.widget-featured-item').on('click', function () {
    var link = $(this).find('.widget-featured-button').attr('href');

    window.location.href = link;
  });
});

/**
 * Tabs and modal control for widget promo
 * @see Lotto_Widget_Promo
 */
(function () {
  const widget = document.querySelector('.widget_lotto_platform_widget_promo');

  if (!widget || (widget.children && widget.children.length <= 0)) {
    return;
  }

  const btnPopupClose = widget.querySelector('.btn-close');
  const video = widget.querySelector('.widget-video');
  const popup = widget.querySelector('.widget-popup');
  const popupEmbed = widget.querySelector('.widget-popup .widget-popup-embed');
  const allPills = widget.querySelectorAll('.widget-pills [data-target]');

  /**
   * Change widget tabs
   *
   * @param {number} e - new tab number (tab to display)
   *
   * @return {void}
   */
  function changeTabs(e) {
    const pillActive = widget.querySelector(
      '.widget-pills .active[data-target]',
    );
    const pillNew = widget.querySelector(`.widget-pills [data-target="${e}"]`);
    const tabActive = widget.querySelector('.widget-tabs .show[data-tab]');
    const tabNew = widget.querySelector(`.widget-tabs [data-tab="${e}"]`);

    pillActive.classList.remove('active');
    pillActive.ariaSelected = false;
    tabActive.classList.remove('show');

    pillNew.classList.add('active');
    pillNew.ariaSelected = true;
    tabNew.classList.add('show');
  }

  /**
   * Lazyload youtube iframe
   *
   * @param {string} source - YouTube video URL
   * @param {HTMLElement} parent - element when iframe is appended
   *
   * @return {void}
   */
  function videoLazyload(source, parent) {
    const iframe = document.createElement('iframe');
    iframe.title = 'YouTube video player';
    iframe.setAttribute(
      'allow',
      'accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture',
    );
    iframe.setAttribute('allowfullscreen', '');
    iframe.src = `https://www.youtube.com/embed/${source}?autoplay=1`;

    parent.append(iframe);
  }

  function popupToggle() {
    if (popup.classList.contains('show')) {
      popup.classList.remove('show');
      popup.querySelector('iframe').remove();
    } else {
      popup.classList.add('show');
    }
  }

  /** pills on click */
  allPills.forEach(function (e) {
    e.addEventListener('click', changeTabs.bind(null, e.dataset.target));
  });

  /** video on click */
  video.addEventListener('click', function () {
    const dataVideo = this.dataset.video;

    if (dataVideo) {
      videoLazyload(dataVideo, popupEmbed);
    }

    popupToggle();
  });

  /** btnPopupClose on click */
  btnPopupClose.addEventListener('click', popupToggle);
})();
