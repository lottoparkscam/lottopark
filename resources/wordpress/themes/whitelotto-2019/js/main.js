(function () {
  let scroll_flag;

  jQuery(document).ready(function ($) {
    $(window)
      .scroll(function () {
        if ($(window).outerWidth() > 1249) {
          if ($(window).scrollTop() >= 50) {
            if (!scroll_flag) {
              scroll_flag = true;
              $('#sticky-header')
                .removeClass('d-none')
                .addClass('sticky-header')
                .css('top', -72)
                .stop()
                .animate({ top: 0 }, 500);
            }
          } else {
            scroll_flag = false;
            $('#sticky-header').removeClass('sticky-header').addClass('d-none');
          }
        } else {
          scroll_flag = false;
          $('#sticky-header').removeClass('sticky-header').addClass('d-none');
        }
      })
      .scroll();
  });
})();

(function () {
  /**
   * Handle capping for wl-banner.
   * @author Jakub Muda
   */

  /** Fix for old jQuery version */
  const $ = jQuery;

  const $wlBannerCollapse = $('#wl-banner-collapse');
  const wlBody = document.body;
  const settingsCookieExpiryInDays = 1;
  const settingsCookieName = 'showWlBanner';

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
    const dataCookie = document.cookie
      .split('; ')
      .some((value) => value.trim().startsWith(`${name}=`));

    return dataCookie;
  }

  /**
   * Set cookie on banner hidden
   */
  $wlBannerCollapse.on('hidden.bs.collapse', function () {
    setCookie(settingsCookieName, 'false', settingsCookieExpiryInDays);
    wlBody.setAttribute('data-wl-banner', '0');
  });

  /** Show collapse if cookie not set */
  if (!isCookieSet(settingsCookieName)) {
    $wlBannerCollapse.collapse('show');
    wlBody.setAttribute('data-wl-banner', '1');
  }
})();
