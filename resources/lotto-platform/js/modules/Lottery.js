import { getPreparedApiUrl } from '../../../js/Helpers/UrlHelper';
import {
  isPlayPage,
  isLotteriesPage,
  isSpecificPlayPage,
  isNotSpecificLotteriesPage,
  isSpecificLotteriesPage,
} from '../../../js/Helpers/PageHelper';

/**
 * This function load lottery data to Infoboxes and Widgets e.g. nextDraw, lastDrawNumbers, jackpot etc.
 * @returns {void}
 */
export async function initLotteryData() {
  const url = getPreparedApiUrl('lottery/all');
  const searchParams = url.searchParams;
  searchParams.set('titleStartTag', window.titleStartTag ?? '');
  searchParams.set('titleEndTag', window.titleEndTag ?? '');
  searchParams.set('lotteryLink', window.lotteryLink ?? '');
  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ lotteries, lastResultsHtml }) => {
      let playableLotteriesSlugs = [];
      let enabledLotteriesSlugs = [];

      initWidgets(lotteries);

      lotteries.forEach((lottery) => {
        enabledLotteriesSlugs.push(lottery.slug);

        if (lottery.isPlayable) {
          playableLotteriesSlugs.push(lottery.slug);
        }

        // We would like to update only parent of 'lotteries' page
        if (isLotteriesPage() && isNotSpecificLotteriesPage()) {
          updateLotteryPage(lottery);
        }

        if (isSpecificLotteriesPage(lottery.slug)) {
          updateSpecificLotteryPage(lottery);
        }

        if (infoboxExists(lottery.slug)) {
          updateInfoBoxData(lottery);
        }

        if (isSpecificPlayPage(lottery.slug) && lottery.isPlayable) {
          updateSpecificPlayPage(lottery);
        }
      });

      document.querySelectorAll('.small-widget-results').forEach((item) => {
        item.innerHTML += lastResultsHtml;
        item.querySelector('.loading-container')?.remove();
      });

      // Remove nav menu for not playable lotteries
      const isSpecificNotPlayablePage =
        (isSpecificPlayPage() || isSpecificLotteriesPage()) &&
        !playableLotteriesSlugs.includes(window.currentPageSlug);
      if (isSpecificNotPlayablePage) {
        const navMenuContainer = document.querySelector('.content-nav');
        navMenuContainer?.remove();

        const shortInformationContainer = document.querySelector(
          '.info-short-content ',
        );
        shortInformationContainer?.remove();

        const detailedInformationContainer = document.querySelector(
          '.info-detailed-content',
        );
        detailedInformationContainer?.remove();
      }

      if (isPlayPage()) {
        // Remove ticket widgets on play page of disabled or not playable lotteries
        const listTicketWidgets =
          document.querySelectorAll(`.widget-list-ticket`);
        listTicketWidgets.forEach((listTicketWidget) => {
          const widgetsLotterySlug = listTicketWidget.dataset.lotterySlug;
          const shouldHideLottery =
            !playableLotteriesSlugs.includes(widgetsLotterySlug);
          if (shouldHideLottery) {
            listTicketWidget.remove();
          }
        });
      }

      // Remove infoboxes of disabled or not playable lotteries
      const infoboxesContainers = document.querySelectorAll('.infobox-wrapper');
      infoboxesContainers.forEach((infobox) => {
        const infoboxLotterySlug = infobox.dataset.lotterySlug;
        const shouldHideInfobox =
          !playableLotteriesSlugs.includes(infoboxLotterySlug);
        if (shouldHideInfobox) {
          infobox.remove();
        }
      });

      // Remove play button of disabled or not playable lotteries
      const playButtons = document.querySelectorAll('.play-button');
      playButtons.forEach((playButton) => {
        const playButtonSlug = playButton.dataset.lotterySlug;
        const shouldHidePlayButton =
          !playableLotteriesSlugs.includes(playButtonSlug);
        if (shouldHidePlayButton) {
          playButton.href = '#';
          playButton.style.opacity = 0.4;
          playButton.style.cursor = 'default';
        }
      });

      // Remove row when lottery is disabled
      const resultsLotteryRows = document.querySelectorAll(
        '.results-lottery-row',
      );
      resultsLotteryRows.forEach((resultsLotteryRow) => {
        const resultsLotterySlug = resultsLotteryRow.dataset.lotterySlug;
        const shouldRemoveRow =
          !enabledLotteriesSlugs.includes(resultsLotterySlug);
        if (shouldRemoveRow) {
          resultsLotteryRow.remove();
        }
      });

      const informationLotteryRows = document.querySelectorAll(
        '.information-lottery-row',
      );
      informationLotteryRows.forEach((informationLotteryRow) => {
        const informationLotterySlug =
          informationLotteryRow.dataset.lotterySlug;
        const shouldRemoveRow = !enabledLotteriesSlugs.includes(
          informationLotterySlug,
        );
        if (shouldRemoveRow) {
          informationLotteryRow.remove();
        }
      });

      // Remove content on specific lottery page of disabled or not playable lottery
      const isCurrentLotteryNotPlayable = !playableLotteriesSlugs.includes(
        window.currentPageSlug,
      );
      const shouldRemoveTicketWidgetOnSpecificPlayPage =
        isSpecificPlayPage() && isCurrentLotteryNotPlayable;
      if (shouldRemoveTicketWidgetOnSpecificPlayPage) {
        const contentContainer = document.querySelector(
          '.widget_lotto_platform_widget_ticket',
        );
        contentContainer?.remove();
      }
    });
  await new Promise(resolve => setTimeout(resolve, 1000));
}

async function initWidgets(lotteries) {
  lotteries.forEach((lottery) => {
    updateWidgetsData(lottery);
  });

  if (window.refreshCountdowns) {
    window.refreshCountdowns();
  }

  window.kenoCountdowns.forEach((countdown) => countdown.refresh());
}

function updateWidgetsData({
  slug,
  jackpotFormatted,
  jackpotHasThousands,
  nextRealDrawShort,
  nextRealDrawTimestamp,
  nextDrawForListWidget,
  pendingText,
  isPending,
  ticketRemainingCount
}) {
  if (!window.pendingText) {
    window.pendingText = pendingText;
  }

  document.querySelectorAll(`.jackpot-to-update-${slug}`).forEach((item) => {
    item.innerHTML = jackpotFormatted;

    if (!jackpotHasThousands) {
      return;
    }

    if (item.classList.contains('widget-featured-amount')) {
      item.classList.add('widget-featured-amount-small');
    }

    if (item.classList.contains('widget-list-hamount')) {
      item.classList.add('widget-list-hamount-small');
    }
  });

  document
    .querySelectorAll(`.next-real-draw-short-to-update-${slug}`)
    .forEach((item) => {
      item.setAttribute('datetime', nextRealDrawTimestamp);
      item.innerHTML = nextRealDrawShort;
      item.querySelector('.loading')?.remove();
    });

  document
    .querySelectorAll(`.next-real-draw-timestamp-to-update-${slug}`)
    .forEach((item) => {
      item.setAttribute('datetime', nextRealDrawTimestamp);
    });

  document
    .querySelectorAll(`.widget-list-countdown-to-update-${slug}`)
    .forEach((item) => {
      if (isPending) {
        const pendingElement = document.createElement('span');
        pendingElement.classList.add('widget-list-pending-text');
        pendingElement.innerText = pendingText;
        item.replaceWith(pendingElement);
        return;
      }
      item.innerHTML = nextDrawForListWidget.replace(
        /inline[\u0000-\u001F\u007F-\u009F]*(hrs|days|min)/g,
        '$1',
      );
    });

  document
    .querySelectorAll(`.widget-remain-tickets-${slug}`)
    .forEach((item) => {
      const remainTicketElement = document.createElement('span');
      remainTicketElement.classList.add('widget-list-pending-text');
      remainTicketElement.textContent = ticketRemainingCount + " tickets remaining";
      item.replaceWith(remainTicketElement);
    });
}

function updateLotteryPage({
  slug,
  nextRealDrawTimestamp,
  nextDrawDate,
  jackpot,
  isPending,
  pendingText,
}) {
  const lotteryRow = document.querySelector(`[data-lottery-slug='${slug}']`);

  const nextDrawTimestamp = lotteryRow.querySelector(
    "[data-type='content-lotteries-next-draw-timestamp']",
  );
  nextDrawTimestamp.dataset.text = nextRealDrawTimestamp;

  const nextDraw = nextDrawTimestamp.querySelector(
    "[data-type='content-lotteries-next-draw']",
  );
  nextDraw.innerText = nextDrawDate;

  const jackpotContainer = lotteryRow.querySelector(
    "[data-type='content-lotteries-jackpot']",
  );
  jackpotContainer.innerText = isPending ? pendingText : jackpot;
}

function updateSpecificLotteryPage({
  lastNumbersFormatted,
  lastDrawTextFormatted,
}) {
  const lastNumbersContainers = document.querySelectorAll(
    "[data-type='content-lotteries-last-result-numbers']",
  );
  lastNumbersContainers.forEach((item) => {
    item.innerHTML = lastNumbersFormatted;
  });

  const lastDrawTextContainers = document.querySelectorAll(
    "[data-type='content-lotteries-last-draw-text']",
  );
  lastDrawTextContainers.forEach((item) => {
    item.innerHTML = `(${lastDrawTextFormatted})`;
  });

  const url = getPreparedApiUrl('lottery');
  const searchParams = url.searchParams;
  searchParams.set('lotterySlug', window.lotterySlug);
  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ drawDatesFormatted, estimatedJackpotsPerTiers }) => {
      if (drawDatesFormatted) {
        const drawDatesContainer = document.getElementById(
          'content-lotteries-draw-dates',
        );
        if (drawDatesContainer) {
          drawDatesContainer.innerHTML = '';
          drawDatesFormatted.forEach((drawDate) => {
            drawDatesContainer.innerHTML += drawDate + '<br/>';
          });
        }
      }

      estimatedJackpotsPerTiers.forEach((estimatedJackpot, index) => {
        const tier = index + 1;
        const isNotJackpot = tier > 1;
        if (isNotJackpot) {
          const estimatedJackpotContainer = document.querySelector(
            `[data-type='content-lotteries-estimated-jackpot-tier-${tier}']`,
          );
          if (estimatedJackpotContainer) {
            estimatedJackpotContainer.innerHTML = estimatedJackpot;
          }
        }
      });
    });
}

function updateSpecificPlayPage({ slug, isKeno }) {
  const lotteryUrl = getPreparedApiUrl('lottery');
  const lotteryUrlSearchParams = lotteryUrl.searchParams;
  lotteryUrlSearchParams.set('lotterySlug', window.lotterySlug);
  fetch(lotteryUrl, { credentials: 'include' })
    .then((response) => response.json())
    .then(
      ({
        firstQuickPickPrice,
        secondQuickPickPrice,
        firstQuickPickPath,
        secondQuickPickPath,
        firstQuickPickDescription,
        secondQuickPickDescription,
        firstMultiplier,
        secondMultiplier,
        linePrice,
        linePriceFormatted,
        firstQuickPickCount,
        secondQuickPickCount,
      }) => {
        const isNotKeno = !isKeno;
        if (isNotKeno) {
          const firstSmallPurchaseToUpdate = document.querySelector(
            '.first-small-purchase-to-update',
          );
          firstSmallPurchaseToUpdate.setAttribute(
            'href',
            firstSmallPurchaseToUpdate.href + firstQuickPickPath,
          );
          firstSmallPurchaseToUpdate.dataset.count = firstQuickPickCount;
          const firstDescriptionContainer =
            firstSmallPurchaseToUpdate.querySelector(
              '.small-purchase-description-primary-text',
            );
          firstDescriptionContainer.innerHTML = firstQuickPickDescription;
          const firstPriceContainer = firstSmallPurchaseToUpdate.querySelector(
            '.small-purchase-description-secondary-text',
          );
          firstPriceContainer.innerHTML = firstQuickPickPrice;

          const secondSmallPurchaseToUpdate = document.querySelector(
            '.second-small-purchase-to-update',
          );
          secondSmallPurchaseToUpdate.setAttribute(
            'href',
            secondSmallPurchaseToUpdate.href + secondQuickPickPath,
          );
          secondSmallPurchaseToUpdate.dataset.count = secondQuickPickCount;
          const secondDescriptionContainer =
            secondSmallPurchaseToUpdate.querySelector(
              '.small-purchase-description-primary-text',
            );
          secondDescriptionContainer.innerHTML = secondQuickPickDescription;
          const secondPriceContainer =
            secondSmallPurchaseToUpdate.querySelector(
              '.small-purchase-description-secondary-text',
            );
          secondPriceContainer.innerHTML = secondQuickPickPrice;

          const linePriceContainer = document.querySelector(
            `.line-price-to-update-${slug}`,
          );
          if (linePriceContainer) {
            linePriceContainer.innerHTML = linePriceFormatted;
          }
        }

        if (isKeno) {
          const kenoLinePriceContainer = document.querySelector(
            `.keno-line-price-to-update-${slug} strong`,
          );
          if (kenoLinePriceContainer) {
            kenoLinePriceContainer.innerHTML = linePriceFormatted;
          }
        }

        const widgetTicketContentContainer = document.querySelector(
          '.widget-ticket-content',
        );
        widgetTicketContentContainer.dataset.price = parseFloat(
          linePrice * 100,
        ).toFixed(2);
      },
    );

  const isBuyingDisabledUrl = getPreparedApiUrl('lottery/isBuyingDisabled');
  const isBuyingDisabledSearchParams = isBuyingDisabledUrl.searchParams;
  isBuyingDisabledSearchParams.set('lotterySlug', window.lotterySlug);
  fetch(isBuyingDisabledUrl, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ isBuyingDisabled, buyingDisabledAlert }) => {
      if (isBuyingDisabled) {
        const widgetTicketContainer = document.querySelector(
          '.content-box:has(.widget-ticket-wrapper)',
        );
        widgetTicketContainer.innerHTML =
          '<div class="platform-alert platform-alert-error' +
          ' widget-ticket-alert"><p><span class="fa fa-exclamation-circle"></span>' +
          buyingDisabledAlert +
          '</p>' +
          '</div>';
      }
    });

  const currencyUrl = getPreparedApiUrl('currency');
  const currencySearchParams = currencyUrl.searchParams;
  currencySearchParams.set('lotterySlug', window.lotterySlug);
  fetch(currencyUrl, { credentials: 'include' })
    .then((response) => response.json())
    .then(
      ({
        currentCurrencyCode,
        currentCurrencySign,
        jsCurrencyFormat,
        zeroInCurrentCurrency,
      }) => {
        const widgetTicketContentContainer = document.querySelector(
          '.widget-ticket-content',
        );
        widgetTicketContentContainer.dataset.currency = currentCurrencySign;
        widgetTicketContentContainer.dataset.currencycode = currentCurrencyCode;
        widgetTicketContentContainer.dataset.format = jsCurrencyFormat;
        window.isCurrencySet = true;

        const sumContainer = document.querySelector('.sum-container');
        if (sumContainer) {
          sumContainer.innerHTML = zeroInCurrentCurrency;
        }
      },
    );
}

function updateInfoBoxData({
  slug,
  jackpotHasThousands,
  jackpotFormatted,
  nextRealDrawTimestamp,
  nextRealDrawFromNow,
  quickPickPath,
  quickPickLinesText,
  quickPickLinesPriceText,
}) {
  const infoboxContainer = document.querySelector(`#infobox_${slug}`);
  const nextDrawContainer =
    infoboxContainer.querySelector('.infobox-next-draw');
  const jackpotContainer = infoboxContainer.querySelector('.infobox-jackpot');
  const quickPickContainer =
    infoboxContainer.querySelector('.infobox-purchase');
  const quickPickFirstDescription = quickPickContainer.querySelector(
    '.infobox-purchase-description-primary-text',
  );
  const quickPickSecondDescription = quickPickContainer.querySelector(
    '.infobox-purchase-description-secondary-text',
  );

  if (jackpotHasThousands === 'true') {
    nextDrawContainer.classList.add('infobox-amount-small');
  }

  nextDrawContainer.setAttribute('datetime', nextRealDrawTimestamp);
  nextDrawContainer.innerHTML = nextRealDrawFromNow;
  nextDrawContainer.querySelector('.loading')?.remove();

  jackpotContainer.innerHTML = jackpotFormatted;

  quickPickContainer.href += quickPickPath;
  quickPickFirstDescription.innerText = quickPickLinesText;
  quickPickSecondDescription.innerText = quickPickLinesPriceText;
}

function infoboxExists(lotterySlug) {
  return document.getElementById(`infobox_${lotterySlug}`) !== null;
}

export function updateNextDrawDatesAfterCountdown() {
  const url = getPreparedApiUrl('lottery/all');
  const searchParams = url.searchParams;
  searchParams.set('titleStartTag', window.titleStartTag ?? '');
  searchParams.set('titleEndTag', window.titleEndTag ?? '');
  searchParams.set('lotteryLink', window.lotteryLink ?? '');
  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ lotteries }) => {
      lotteries.forEach(
        ({ slug, nextRealDrawTimestamp, nextRealDrawShort }) => {
          document
            .querySelectorAll(`.next-real-draw-short-to-update-${slug}`)
            .forEach((item) => {
              item.setAttribute('datetime', nextRealDrawTimestamp);
              item.innerHTML = nextRealDrawShort;
              item.querySelector('.loading')?.remove();
            });

          document
            .querySelectorAll(`.next-real-draw-timestamp-to-update-${slug}`)
            .forEach((item) => {
              item.setAttribute('datetime', nextRealDrawTimestamp);
            });
        },
      );
    });
}
