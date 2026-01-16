import { isParentByPageSlug } from '../../../js/Helpers/PageHelper';
import { getPreparedApiUrl } from '../../../js/Helpers/UrlHelper';
import { initPlayPage } from './Raffle/Play';
import { isSpecificRafflePage } from '../../../js/Helpers/PageHelper';

export function initSpecificRafflePage() {
  if (!isSpecificRafflePage()) {
    return;
  }

  const url = getPreparedApiUrl('raffle');
  const searchParams = url.searchParams;
  searchParams.set('slug', window.lotterySlug);
  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then(
      ({
        isEnabled,
        amountOfAvailableNumbers,
        prize,
        linePrice,
        linePriceFormatted,
        defaultPlaySummary,
        poolIsSoldOut,
        soldOutText,
        takenNumbers,
        isUserLogged,
        usersCurrencyCode,
        linePriceInUsersCurrency,
        usersBonusBalanceAmount,
        jsCurrencyFormat,
        isSellEnabled,
        csrfToken,
      }) => {
        const isDisabled = !isEnabled;
        if (isDisabled) {
          window.location = '/';
        }

        const isSellDisabled = !isSellEnabled;
        const isRafflePlayPage = isParentByPageSlug('play-raffle');
        if (isRafflePlayPage && isSellDisabled) {
          window.location = '/';
        }

        const availableNumbersContainer = document.getElementById(
          'raffle-information-available-numbers',
        );
        if (availableNumbersContainer) {
          availableNumbersContainer.innerHTML = amountOfAvailableNumbers;
        }

        const availableNumbersContainers = document.querySelectorAll(
          '.available-numbers-to-update',
        );
        availableNumbersContainers.forEach((numbersContainer) => {
          numbersContainer.innerHTML = amountOfAvailableNumbers;
        });

        const prizeContainer = document.querySelector(
          '.raffle-prize-to-update',
        );
        if (prizeContainer) {
          prizeContainer.innerHTML = prize;
        }

        const priceContainer = document.querySelector('.raffle-line-price');
        if (priceContainer) {
          priceContainer.innerHTML = linePriceFormatted;
          priceContainer.dataset.price = linePrice;
        }

        const playSummaryContainer = document.querySelector(
          '.raffle-total-value',
        );
        if (playSummaryContainer) {
          playSummaryContainer.innerHTML = defaultPlaySummary;
        }

        if (poolIsSoldOut) {
          const soldOutContainer = document.getElementById(
            'raffle-is-sold-out-container',
          );
          if (soldOutContainer) {
            soldOutContainer.innerHTML = `
                <div class="platform-alert platform-alert-info">
                    <p><i class="fa fa-exclamation-circle"></i> ${soldOutText} </p>
                </div>`;
          }
        }

        const raffleNumbers = document.querySelectorAll('.raffle-number');
        raffleNumbers.forEach((number) => {
          // Parse to remove zeroes e.g. from 0002 to 2
          const currentNumber = parseInt(number.innerHTML);
          const isNumberTaken = takenNumbers.includes(currentNumber);
          if (isNumberTaken) {
            number.classList.add('raffle-taken-number');
          }
        });

        const raffleConfigContainer = document.getElementById('raffle-config');
        if (raffleConfigContainer) {
          let raffleConfig = JSON.parse(raffleConfigContainer.dataset.raffle);
          raffleConfig.is_logged_in = isUserLogged;
          raffleConfig.taken_numbers = takenNumbers;
          raffleConfig.user_currency_code = usersCurrencyCode;
          raffleConfig.user_line_price = linePriceInUsersCurrency;
          raffleConfig.user_bonus_balance = usersBonusBalanceAmount;
          raffleConfig.js_currency_format = jsCurrencyFormat;
          raffleConfig.t = csrfToken;
          raffleConfigContainer.dataset.raffle = JSON.stringify(raffleConfig);
        }

        initPlayPage();
      },
    );
}
