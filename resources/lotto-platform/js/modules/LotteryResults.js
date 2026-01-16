import LotteryDrawDateTimeSelect from './LotteryDrawDateTimeSelect';

const $ = jQuery;

export function bindWinningsTableMultiplierEvents() {
  const multiplierSwitch = document.getElementById('winnings-table-multiplier');
  if (!multiplierSwitch) {
    return;
  }
  const jackpot = multiplierSwitch.dataset.jackpot;

  $('#winnings-table-multiplier').change((e) => {
    const table = $('.results-short-winnings-table');
    const cells = $('.table-cell', table);
    const target = $(e.target);
    const currencyCode = table.attr('data-currencycode');
    const lang = $('html').attr('lang') || 'en-GB';

    let currencyFormatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
      currency: currencyCode || 'USD',
    });
    cells.each((i, cell) => {
      let baseValue = $(cell).attr('data-value');
      if (!baseValue) {
        return;
      }
      $(cell).text(
        currencyFormatter.format(Math.min(target.val() * baseValue, jackpot)),
      );
    });
  });
  $('#winnings-table-multiplier')
    .val($('#winnings-table-multiplier option:last').val())
    .change();
}

export function setRefreshedLotteryResultsDetails(data) {
  const estimatedJackpotElement = document.getElementById(
    'estimatedJackpotValue',
  );
  if (estimatedJackpotElement) {
    estimatedJackpotElement.innerHTML = data.estimatedJackpotValue;
  }

  const lotteryDrawNumberElement = document.getElementById('lotteryDrawNumber');
  if (lotteryDrawNumberElement) {
    lotteryDrawNumberElement.innerHTML = data.lotteryDrawNumber;
  }

  const winNumbersElement = document.querySelector(
    "[data-type='content-lotteries-last-result-numbers']",
  );

  if (winNumbersElement) {
    winNumbersElement.innerHTML = data.winNumbersFormatted;
  }

  const lotteryResultTableContainer = document.getElementById(
    'lotteryResultContainer',
  );
  if (lotteryResultTableContainer) {
    lotteryResultTableContainer.innerHTML = data.lotteryResultTableHtml;
  }

  const lotteryPageTitleElement = document.getElementById('lotteryPageTitle');
  if (lotteryPageTitleElement) {
    lotteryPageTitleElement.innerHTML =
      data.extraTitleText.length > 0
        ? `${lotteryPageTitle} - ${data.extraTitleText}`
        : lotteryPageTitle;
  }
}

export function initLotteryResultsPage() {
  const isResultsLotteryPage = window.isLotteryResultsPage;
  if (isResultsLotteryPage) {
    new LotteryDrawDateTimeSelect(window.lotteryName, window.lotteryLanguage);
  }
}
