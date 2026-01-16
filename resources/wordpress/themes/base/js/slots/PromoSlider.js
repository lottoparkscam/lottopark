import { isRightToLeftLanguage } from '../../../../../js/Helpers/LanguageHelper';

jQuery(window).on('load', function () {
  (function () {
    jQuery('.promo-slider').slick({
      centerMode: true,
      centerPadding: '20px',
      rtl: isRightToLeftLanguage(),
      arrows: false,
      autoplay: true,
      speed: 600,
      mobileFirst: true,
      slidesToShow: 1,
      nextArrow:
        '<div class="widget-list-carousel-next"><span class="fa fa-angle-right"></span></div>',
      prevArrow:
        '<div class="widget-list-carousel-prev"><span class="fa fa-angle-left"></span></div>',
      responsive: [
        {
          breakpoint: 1300,
          settings: {
            variableWidth: true,
            slidesToShow: 1,
            arrows: true,
            controlsInside: false,
            slidesToScroll: 1,
          },
        },
        {
          breakpoint: 800,
          settings: {
            variableWidth: true,
            slidesToShow: 1,
            slidesToScroll: 1,
          },
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
          },
        },
        {
          breakpoint: 0,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
          },
        },
      ],
    });
  })(jQuery);
});
