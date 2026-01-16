jQuery('body').addClass('casino');
const isUserLoggedIn = jQuery('.user-balance');
if (isUserLoggedIn) {
  jQuery('.user-balance').hide();
  jQuery('.menu-wrapper').css('right', 0);
  jQuery('.menu-wrapper').css('left', 'unset');
  jQuery('.user-name').css('padding-right', '10px');
}

jQuery(window).on('load', function () {
  (function ($) {
    const fullScreenButton = $('#fullScreenButton');
    fullScreenButton.on('click', () => {
      const isFullScreenModeAvailable =
        document.fullscreenEnabled ||
        document.webkitFullscreenEnabled ||
        document.mozFullScreenEnabled ||
        document.msFullscreenEnabled;
      if (isFullScreenModeAvailable) {
        const iframe = document.querySelector('#playGameIframe');
        if (iframe.requestFullscreen) {
          iframe.requestFullscreen();
        } else if (iframe.webkitRequestFullscreen) {
          iframe.webkitRequestFullscreen();
        } else if (iframe.mozRequestFullScreen) {
          iframe.mozRequestFullScreen();
        } else if (iframe.msRequestFullscreen) {
          iframe.msRequestFullscreen();
        }
      }
    });
  })(jQuery);
});
