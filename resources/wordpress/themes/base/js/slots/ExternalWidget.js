import { isRightToLeftLanguage } from '../../../../../js/Helpers/LanguageHelper';

const renderExternalCasinoWidget = (games) => {
  const casinoSlider = document.getElementById('casinoSlider');

  if (casinoSlider) {
    const casinoGamesListHTML = renderCasinoGamesList(games);

    /* global CASINO_WIDGET_TITLE, CASINO_URL */
    const casinoTitleHTML = `<a class="casino-widget-link" href="${CASINO_URL}">${CASINO_WIDGET_TITLE}</a>`;

    casinoSlider.innerHTML = `
      <div class="small-widget-title">
        ${casinoTitleHTML}
      </div>
      <div class="slot-games-container slots-slider">
        ${casinoGamesListHTML}
      </div>`;
  }
};

/* global PLAY_NOW, PLAY, CHOOSE */
const renderCasinoGamesList = (games) => {
  let casinoWidgetHTML = '';

  for (const i in games) {
    let demoElementHtml = '';
    const isNotLobbyGame = !games[i].has_lobby;
    const playButtonContent = isNotLobbyGame ? PLAY_NOW : `${CHOOSE} lobby`;

    if (games[i].has_demo) {
      demoElementHtml = `<a class="play-demo" href="${games[i].play_demo_url}">${PLAY} demo</a>`;
    }

    casinoWidgetHTML += `
      <div class="slot-game">
          <div class="slot-game-image"> 
              <img src="${games[i].image_url}" alt="${games[i].name}|${games[i].creator_name}">
          </div>
          <div class="slot-game-buttons">
              <a class="btn btn-primary" href="${games[i].play_url}">${playButtonContent}</a>
              ${demoElementHtml}
          </div>
      </div>`;
  }

  return casinoWidgetHTML;
};

jQuery(document).ready(function () {
  function initNewCasinoSlider() {
    (function ($) {
      $('.slot-games-container').slick({
        rtl: isRightToLeftLanguage(),
        arrows: false,
        autoplay: true,
        speed: 600,
        mobileFirst: true,
        rows: 2,
        slidesToShow: 4,
        nextArrow:
          '<div class="widget-list-carousel-next"><span class="fa fa-angle-right"></span></div>',
        prevArrow:
          '<div class="widget-list-carousel-prev"><span class="fa fa-angle-left"></span></div>',
        responsive: [
          {
            breakpoint: 1300,
            settings: {
              slidesToShow: 4,
              arrows: true,
              slidesToScroll: 4,
            },
          },
          {
            breakpoint: 800,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 3,
            },
          },
          {
            breakpoint: 600,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2,
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
  }

  /* global CASINO_SLIDER_GAMES_COUNT, CASINO_API_URL */
  const url = new URL(CASINO_API_URL);
  url.searchParams.set('limit', CASINO_SLIDER_GAMES_COUNT);

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      renderExternalCasinoWidget(data);
      initNewCasinoSlider();
    });
});
