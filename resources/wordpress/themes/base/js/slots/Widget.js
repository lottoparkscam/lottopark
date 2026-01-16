import { isRightToLeftLanguage } from '../../../../../js/Helpers/LanguageHelper';
import { getPreparedApiUrl } from '../../../../../js/Helpers/UrlHelper';

const renderCasinoWidget = ({ games, isVisible, casinoUrl }) => {
  const casinoSlider = document.getElementById('casinoSlider');

  if (casinoSlider) {
    const casinoGamesListHTML = renderCasinoGamesList(games);

    if (!isVisible) {
      casinoSlider.style.display = 'none';
    }

    /* global CASINO_WIDGET_TITLE */
    const casinoTitleHTML = isVisible
      ? `<a class="casino-widget-link" href="${casinoUrl}">${CASINO_WIDGET_TITLE}</a>`
      : CASINO_WIDGET_TITLE;

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

  for (let uuid in games) {
    let demoElementHtml = '';
    const isNotLobbyGame = !games[uuid].has_lobby;
    const playButtonContent = isNotLobbyGame ? PLAY_NOW : `${CHOOSE} lobby`;

    if (games[uuid].has_demo) {
      demoElementHtml = `<a class="play-demo" href="${games[uuid].init_demo_link}">${PLAY} demo</a>`;
    }

    casinoWidgetHTML += `
      <div class="slot-game">
          <div class="slot-game-image">
              <img src="${games[uuid].image}" alt="${games[uuid].name}">
          </div>
          <div class="slot-game-buttons">
              <a class="btn btn-primary" href="${games[uuid].init_link}">${playButtonContent}</a>
              ${demoElementHtml}
          </div>
      </div>`;
  }

  return casinoWidgetHTML;
};

jQuery(window).on('load', function () {
  function initCasinoSlider() {
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

  /* global CASINO_SLIDER_GAMES_COUNT, CASINO_INTERNAL_INIT_URL, CASINO_LANGUAGE, CASINO_IS_EXISTS, CASINO_LOBBY_INIT_URL */
  const url = getPreparedApiUrl('casino/slider/data');
  url.searchParams.set('gamesCount', CASINO_SLIDER_GAMES_COUNT);
  url.searchParams.set('internalInitUrl', CASINO_INTERNAL_INIT_URL);
  url.searchParams.set('lobbyInitUrl', CASINO_LOBBY_INIT_URL);
  url.searchParams.set('lang', CASINO_LANGUAGE);
  url.searchParams.set('casinoExists', CASINO_IS_EXISTS);

  fetch(url, {
    credentials: 'include',
  })
    .then((response) => response.json())
    .then((data) => {
      renderCasinoWidget(data);
      initCasinoSlider();
    });
});
