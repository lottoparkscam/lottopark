import {
  getPreparedApiUrl,
  getCurrentUrl,
} from '../../../../../js/Helpers/UrlHelper';

/* eslint-disable no-undef */
/**
 * Those variables are from template-casino.php
 * @var internalInitUrl
 * @var lobbySelectUrl
 * @var translatedPlayNow
 * @var translatedPlay
 * @var translatedChoose
 */
jQuery(window).on('load', function () {
  (function ($) {
    let filteredGamesUrl = getPreparedApiUrl('slots/enabledGamesFilters');
    let allGamesUrl = getPreparedApiUrl('slots/enabledGames');

    const searchParams = getCurrentUrl().searchParams;
    const showMoreButton = $('#show-more');
    const gamesCountPerPage = 32;
    class GameList {
      constructor() {
        this.isWebGLSupported = this.hasBrowserWebGLSupport();
        this.gameView = '';
      }

      goToNextPage() {
        let currentSite = searchParams.get('site') || 1;

        let nextSite = parseInt(currentSite);
        nextSite++;
        searchParams.set('site', nextSite);
        history.pushState(null, null, searchParams.toString());

        this.fetchGames(allGamesUrl, searchParams.toString(), false);
      }

      static hasImageValidExtension(imageUrl) {
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (
          allowedExtensions.some((extension) => imageUrl.includes(extension))
        ) {
          return true;
        }
        return false;
      }

      hasBrowserWebGLSupport() {
        const webgl = document.createElement('canvas').getContext('webgl2');
        if (!webgl) {
          return false;
        }
        return true;
      }

      activityIndicator(isLoading, withFilters) {
        const container = $('.slot-games-container');
        const loader = $('.loading');
        const loaderHtml =
          '<div class="loading-container"><div class="loading"></div></div>';
        const isLoaderAdded = loader.length > 0;
        if (isLoading && isLoaderAdded) {
          return;
        }

        if (isLoading) {
          if (withFilters) {
            container.html(loaderHtml);
          } else {
            $(loaderHtml).insertAfter(container);
          }
          showMoreButton.hide();
        } else {
          loader.remove();
        }
      }

      checkFilters(inputName, inputValue) {
        const searchParams = getCurrentUrl().searchParams;
        if (inputValue.length === 0) {
          searchParams.delete(inputName);
        } else {
          searchParams.set(inputName, inputValue);
        }
        searchParams.delete('site');
        history.pushState(null, null, '?' + searchParams.toString());
        const withFilters = searchParams.toString() != 0;
        const url = withFilters ? filteredGamesUrl : allGamesUrl;
        const paramsString = searchParams.toString();
        this.gameView = '';
        gameList.fetchGames(url, paramsString, withFilters);
      }

      fetchGames(url, params, filtered) {
        params = params == '' ? '' : '&' + params;

        let urlWithParams = url + params;

        $('.fetching-error').hide();
        this.activityIndicator(true, filtered);

        showMoreButton.off();

        if (filtered) {
          searchParams.delete('site');
          window.numberGamesAtPage = 0;
        }

        showMoreButton.on('click', () => {
          if (filtered) {
            this.loadNextFilteredGames();
          } else {
            this.goToNextPage();
          }
        });

        showMoreButton.show();
        fetch(urlWithParams, {
          credentials: 'include',
        })
          .then((response) => response.json())
          .then((data) => {
            const isLastPage =
              data.isLastPage != null && parseInt(data.isLastPage) === 1;
            if (isLastPage) {
              showMoreButton.hide();
            }

            delete data.isLastPage;

            const isDataEmpty = !Object.keys(data).length;
            if (isDataEmpty) {
              const addWithProvidedFilters = filtered
                ? ' with provided filters'
                : '';
              const error = `<p class="fetching-error">Oops.. We cannot find any game${addWithProvidedFilters}.</p>`;
              $('.fetching-error').show();
              $('.slot-games-container').html(error);
              showMoreButton.hide();
              this.activityIndicator(false);
              return;
            }

            let games = GameList.filterGames(Object.entries(data));
            if (filtered) {
              const first32Games = games.splice(0, 32);
              sessionStorage.setItem('games', JSON.stringify(games));
              const leftGamesInSessionCount = games.length;
              games = first32Games;
              const shouldShowShowMoreButton =
                leftGamesInSessionCount < gamesCountPerPage;
              if (shouldShowShowMoreButton) {
                showMoreButton.hide();
              }
            }
            games.forEach(([uuid, game]) => {
              this.gameView += GameList.createGameView(uuid, game);
            });

            if (!filtered) {
              $('.slot-games-container').append(this.gameView);
            }
            if (!params.includes('site')) {
              $('.slot-games-container').html(this.gameView);
            }
            this.gameView = '';
            this.activityIndicator(false);
          });
      }

      static filterGames(games) {
        // eslint-disable-next-line no-unused-vars
        return games.filter(([uuid, game]) => {
          const invalidImage = !GameList.hasImageValidExtension(game.image);
          if (invalidImage) {
            return false;
          }

          const hideWebGL =
            !game.isWebGLSupported && game.technology === 'WebGL';
          if (hideWebGL) {
            return false;
          }

          return true;
        });
      }

      static createGameView(uuid, game) {
        const withDemo = game.has_demo
          ? `<a class="play-demo" href="${internalInitUrl}game_uuid=${uuid}&mode=demo">${translatedPlay} demo</a>`
          : '';
        const isGameWithoutLobby = !game.has_lobby;
        const buttonText = isGameWithoutLobby
          ? translatedPlayNow
          : `${translatedChoose} lobby`;
        const buttonUrl = isGameWithoutLobby ? internalInitUrl : lobbySelectUrl;
        return `
                      <div class="slot-game">
                          <div class="slot-game-image">
                              <img src="${game.image}" alt="${game.name}">
                          </div>
                          <div class="slot-game-buttons">
                              <a class="btn btn-primary" href="${buttonUrl}game_uuid=${uuid}"
                               >${buttonText}</a>
                              ${withDemo}
                          </div>
                      </div>
                  `;
      }
      loadNextFilteredGames() {
        let sessionGames = JSON.parse(sessionStorage.getItem('games'));
        let gameView = '';
        let currentSite = searchParams.get('site') || 1;
        numberGamesAtPage = currentSite == 1 ? 0 : numberGamesAtPage + 32;
        sessionGames
          .splice(numberGamesAtPage, gamesCountPerPage)
          .forEach(([uuid, game]) => {
            gameView += GameList.createGameView(uuid, game);
          });
        if (sessionGames.length === numberGamesAtPage) {
          showMoreButton.hide();
        }
        $('.slot-games-container').append(gameView);
        searchParams.set('site', ++currentSite);
      }
    }

    const gameList = new GameList();

    const hasFilters =
      searchParams.has('slot_game_name') ||
      searchParams.has('provider') ||
      searchParams.has('type');
    if (hasFilters) {
      showMoreButton.show();
      gameList.fetchGames(filteredGamesUrl, searchParams.toString(), true);
    } else {
      gameList.fetchGames(allGamesUrl, '', false);
    }

    var typingTimer;
    const doneTypingInterval = 1000;
    const searchInput = $('#slots-search');

    searchInput.on('keyup', function () {
      gameList.activityIndicator(true, true);
      clearTimeout(typingTimer);
      typingTimer = setTimeout(doneTyping, doneTypingInterval);
    });

    searchInput.on('keydown', function () {
      clearTimeout(typingTimer);
    });

    function doneTyping() {
      const inputName = searchInput.attr('name');
      let inputValue = searchInput.val();
      gameList.checkFilters(inputName, inputValue);
    }

    $('.slots-select').on('change', function () {
      const inputName = $(this).attr('name');
      let inputValue = $(this).children('option:selected').val();
      gameList.checkFilters(inputName, inputValue);
    });
  })(jQuery);
});
