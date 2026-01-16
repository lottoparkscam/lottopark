import { getPreparedApiUrl } from '../../../js/Helpers/UrlHelper';
import { isParentByPageSlug } from '../../../js/Helpers/PageHelper';

/** @param {object} data **/
function setRefreshedRaffleLotteryResultsDetails(data) {
  const raffleDrawNumberElement = document.getElementById('raffleDrawNumber');
  if (raffleDrawNumberElement) {
    raffleDrawNumberElement.innerHTML = data.drawNumbers;
  }

  const raffleWinningTicketsPrizesElement = document.getElementById(
    'raffleWinningTicketsPrizes',
  );
  if (raffleWinningTicketsPrizesElement) {
    raffleWinningTicketsPrizesElement.innerHTML = data.mainPrizes;
  }

  const raffleTableContentElement =
    document.getElementById('raffleTableContent');
  if (raffleTableContentElement) {
    raffleTableContentElement.innerHTML = data.winnersTableHtml;
  }

  const raffleDateSelectElement = document.getElementById('raffleDateSelect');
  if (raffleDateSelectElement) {
    raffleDateSelectElement.innerHTML = data.dateSelectOptions;
  }
}

/**
 * @param {string} lotteryName
 * @param {?string} date
 * @param {?string} language
 */
function contentResultsRaffleLoad(lotteryName, date = '', language = '') {
  let dateSelect = document.getElementById('raffleDateSelect');

  if (!dateSelect) {
    return;
  }

  let drawId = dateSelect.value;
  const raffleSlug = window.lotteryName;

  let url = getPreparedApiUrl(
    `raffleResult/?language=${language}&drawId=${drawId}&raffleSlug=${raffleSlug}`,
  );

  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then((data) => {
      setRefreshedRaffleLotteryResultsDetails(data);

      let raffleShowResultsElements = document.querySelectorAll(
        '.raffle-show-results',
      );
      if (raffleShowResultsElements.length > 0) {
        raffleShowResultsElements.forEach((raffleShowResult) => {
          raffleShowResult.addEventListener('click', function (event) {
            const tierId = event.target.dataset.tier;
            const rows = document.querySelectorAll(`tr[data-tier="${tierId}"]`);
            rows.forEach((row) => row.classList.toggle('hidden'));
          });
        });
      }

      dateSelect.addEventListener('change', contentResultsRaffleLoad);
    });
}

export function initRaffleResultsPage() {
  const isRaffleResultPage = isParentByPageSlug('results-raffle');
  if (isRaffleResultPage) {
    const lotteryName = window.lotteryName;
    const lotteryLanguage = window.lotteryLanguage;
    contentResultsRaffleLoad(lotteryName, '', lotteryLanguage);
  }
}
