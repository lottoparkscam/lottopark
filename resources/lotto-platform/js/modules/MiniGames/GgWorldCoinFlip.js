import { getPreparedApiUrl } from '../../../../js/Helpers/UrlHelper';
import { createGameMessage } from './GameMessage';
import { createPromoCodeHandler } from './PromoCodeHandler';

const GgWorldCoinFlipGame = (() => {
  let miniGameSlug = window.miniGameSlug;
  let userSelectedNumber = null;
  let userSelectedAmountInEur = null;
  let isFlipping = false;

  const gameNameElement = document.getElementById('gameName');
  const multiplierElements = document.querySelectorAll('.gameMultiplier');
  const balanceElements = document.querySelectorAll('.gameBalanceAmount');
  const bonusBalanceElements = document.querySelectorAll('.gameBonusBalanceAmount');
  const availableBetsElement = document.getElementById('availableBets');
  const gameHistoryElement = document.getElementById('gameHistory');
  const coinElement = document.querySelector('.coin');
  const gamePlayButton = document.querySelector('.gamePlayBtn');
  const gameStakeWrapper = document.querySelector('.gameStakeWrapper');
  const gameStakeTrigger = document.querySelector('.gameStakeTrigger');
  const sideHeads = document.querySelector('.sideHeads');
  const sideTails = document.querySelector('.sideTails');
  const coinTriggerElement = document.getElementById('coin');
  const selectedStakeElements = document.querySelectorAll('.selectedStakeAmount');

  const gameMessage = createGameMessage();

  const init = () => {
    fetchGameData();
    attachEventListeners();
    addShakeAnimation();
  };

  const addShakeAnimation = () => {
    coinElement.style.animation = 'hvr-buzz-out 5s ease-in-out infinite';
  };

  const fetchGameData = () => {
    const url = prepareGameApiUrl('miniGames');
    url.searchParams.set('slug', String(miniGameSlug));

    fetch(url, { credentials: 'include' })
      .then((response) => response.json())
      .then((data) => handleGameData(data))
      .catch(() => gameMessage.show('Something went wrong. Please try again shortly.'));
  };

  const prepareGameApiUrl = (endpoint) => {
    return getPreparedApiUrl(endpoint);
  };

  const handleGameData = (data) => {
    const { name, multiplier, availableBets, defaultBet, balance, bonusBalance, history, freeSpinData} = data;

    updateGameName(name);
    updateMultiplierDisplay(multiplier);
    updateBalanceDisplay(balance);
    updateBonusBalanceDisplay(bonusBalance);
    renderAvailableBets(availableBets, freeSpinData);
    updateDefaultBet(defaultBet, freeSpinData);
    updateGameHistoryDisplay(history);
    updateFreeSpinElements(freeSpinData);
  };

  const updateGameName = (name) => {
    gameNameElement.innerText = name;
  };

  const updateMultiplierDisplay = (multiplier) => {
    multiplierElements.forEach((element) => {
      element.innerText = `x${multiplier}`;
    });
  };

  const updateBalanceDisplay = (balance) => {
    balanceElements.forEach((element) => {
      element.innerText = balance;
    });
  };

  const updateBonusBalanceDisplay = (bonusBalance) => {
    bonusBalanceElements.forEach((element) => {
      element.innerText = bonusBalance;
    });
  };

  const renderAvailableBets = (availableBets, freeSpinData) => {
    availableBetsElement.innerHTML = '';

    if (freeSpinData && freeSpinData.usedFreeSpinCount < freeSpinData.freeSpinCount) {
      availableBetsElement.insertAdjacentHTML(
        'beforeend',
        `<li data-stake="0">€${freeSpinData.freeSpinValue} (Free Spin)</li>`
      );
    }

    availableBets.forEach((betAmount) => {
      const betAmountLi = `<li data-stake="${betAmount}">€${betAmount}</li>`;
      availableBetsElement.insertAdjacentHTML('beforeend', betAmountLi);
    });

    attachBetAmountListeners();
  };

  const updateDefaultBet = (defaultBet, freeSpinData) => {
    if (freeSpinData && freeSpinData.freeSpinValue > 0) {
      userSelectedAmountInEur = 0;
      updateSelectedStakeElements('€' + freeSpinData.freeSpinValue + ' (Free Spin)');
      return;
    }

    userSelectedAmountInEur = parseFloat(defaultBet);
    updateSelectedStakeElements('€' + defaultBet);
  };

  const attachBetAmountListeners = () => {
    const betListItems = availableBetsElement.querySelectorAll('li');
    const stakeWrapper = gameStakeWrapper;
    const trigger = gameStakeTrigger;

    betListItems.forEach((li) => {
      li.addEventListener('click', () => {
        updateSelectedStake(li);
        stakeWrapper.classList.remove('active');
        trigger.classList.remove('active');
      });
    });
  };

  const updateSelectedStake = (li) => {
    const stake = parseFloat(li.getAttribute('data-stake'));
    userSelectedAmountInEur = stake;

    let isFreeSpin = stake === 0;
    updateSelectedStakeElements(isFreeSpin ? li.textContent.trim() : `€${stake}`);
  };

  const updateGameHistoryDisplay = (history) => {
    gameHistoryElement.innerHTML = '';

    history.forEach((item) => {
      const isWin = item.type === 'win';
      const isFreeSpin = item.mini_game_user_promo_code_id !== null;
      const gameHistoryItem = document.createElement('div');

      gameHistoryItem.className = `gameHistoryItem ${isWin ? '' : 'opacity'} ${item.is_bonus_balance_paid == 1 ? 'fromBonusBalance' : ''}`;
      gameHistoryItem.innerHTML = `
        <img src="${item.user_selected_number == 0 ? coinHeadsIcon : coinTailsIcon}" alt="${item.user_selected_number == 0 ? 'Heads' : 'Tails'}" />
        <strong>${isWin ? 'Win' : 'Loss'}</strong>
        <span>${isWin ? '+' : '-'} €${isWin ? item.prize : item.amount} ${isFreeSpin ? '(Free Spin)' : ''}</span>
      `;
      gameHistoryElement.append(gameHistoryItem);
    });
  };

  const updateFreeSpinElements = (freeSpinData) => {
    if (freeSpinData.hasUsedAllSpins !== null) {
      updateFreeSpinCount(freeSpinData);
      showFreeSpinElements();
    } else {
      hideFreeSpinElements();
    }
  };

  const updateFreeSpinCount = (freeSpinData) => {
    let freeSpinCountElements = document.querySelectorAll('.freeSpinCount');
    freeSpinCountElements.forEach((element) => {
      element.textContent = `${freeSpinData.usedFreeSpinCount}/${freeSpinData.freeSpinCount}`;
    });
  };

  const showFreeSpinElements = () => {
    let freeSpinElements = document.querySelectorAll('.freeSpins');
    freeSpinElements.forEach((element) => {
      element.classList.add('show');
    });
  };

  const hideFreeSpinElements = () => {
    let freeSpinElements = document.querySelectorAll('.freeSpins');
    freeSpinElements.forEach((element) => {
      element.classList.remove('show');
    });
  };

  const attachEventListeners = () => {
    sideHeads.addEventListener('click', () => updateCoinSelection('Heads'));
    sideTails.addEventListener('click', () => updateCoinSelection('Tails'));
    coinTriggerElement.addEventListener('click', () => playGame());
    gamePlayButton.addEventListener('click', (e) => { e.preventDefault(); playGame(); });
    gameStakeTrigger.addEventListener('click', toggleStakeWrapper);

    createPromoCodeHandler(GgWorldCoinFlipGame);
  };

  const updateCoinSelection = (side) => {
    if (isFlipping) return;

    ['Heads', 'Tails'].forEach((coinSide) => {
      document.querySelector(`.side${coinSide}`).classList.remove('gameBoxSelected');
    });
    document.querySelector(`.side${side}`).classList.add('gameBoxSelected');
    userSelectedNumber = side === 'Heads' ? 0 : 1;
  };

  const toggleStakeWrapper = (event) => {
    event.preventDefault();
    gameStakeWrapper.classList.toggle('active');
    gameStakeTrigger.classList.toggle('active');
  };

  const playGame = () => {
    if (!isValidGameSelection()) {
      gameMessage.show('Choose heads or tails before flipping.');
      return;
    }

    if (isFlipping) return;

    startCoinFlip();
  };

  const isValidGameSelection = () => {
    return userSelectedNumber !== null && userSelectedAmountInEur !== null;
  };

  const startCoinFlip = () => {
    isFlipping = true;
    gameMessage.hide();
    gamePlayButton.style.visibility = 'hidden';

    const url = prepareGameApiUrl('miniGames/play');
    const body = new FormData();
    body.append('slug', miniGameSlug);
    body.append('userSelectedNumber', userSelectedNumber);
    body.append('userSelectedAmountInEur', userSelectedAmountInEur);

    fetch(url, {
      method: 'POST',
      credentials: 'include',
      body,
    })
      .then((response) => response.json())
      .then((data) => handleGameResult(data))
      .catch(() => {
        isFlipping = false;
      });
  };

  const handleGameResult = ({
    isWin,
    isFreeSpin,
    isUsedBonusBalance,
    balanceBeforeFlipResult,
    balanceAfterFlipResult,
    bonusBalanceBeforeFlipResult,
    bonusBalanceAfterFlipResult,
    userSelectedNumber,
    prize,
    amount,
    errorCode
  }) => {
    if (errorCode) {
      handleGameError(errorCode);
      return;
    }

    if (!isFreeSpin) {
      updateBalanceDisplay(balanceBeforeFlipResult);
      updateBonusBalanceDisplay(bonusBalanceBeforeFlipResult)
    }

    isFlipping = true;
    animateCoinFlip(() => {
      updateGameHistory(isWin, userSelectedNumber, prize, amount, isFreeSpin, isUsedBonusBalance);
      updateBalanceDisplay(balanceAfterFlipResult);
      updateBonusBalanceDisplay(bonusBalanceAfterFlipResult);

      if (isFreeSpin) {
        fetchGameData();
      }

      if (isWin) {
        gameMessage.show('You have won <strong>€' + prize + '</strong>. Congratulations!');
      } else {
        gameMessage.show('You lost this time. Try again!');
      }
    }, userSelectedNumber, isWin);
  };

  const handleGameError = (errorCode) => {
    switch (errorCode) {
      case -1:
        gameMessage.show('Selected game not found.');
        break;
      case -2:
        gameMessage.show('Insufficient funds for the selected bet.');
        break;
      case -3:
        gameMessage.show('Choose heads or tails before flipping.');
        break;
      default:
        gameMessage.show('Something went wrong. Please try again shortly.');
        break;
    }

    resetGame();
  };

  const animateCoinFlip = (callback, userSelectedNumber, isWin) => {
    coinElement.style.animation = 'none';

    if (isWin && userSelectedNumber === 0) {
      coinElement.style.animation = '3s linear forwards flipHeads';
    } else if (isWin && userSelectedNumber === 1) {
      coinElement.style.animation = '3s linear forwards flipTails';
    } else if (!isWin && userSelectedNumber === 1) {
      coinElement.style.animation = '3s linear forwards flipHeads';
    } else if (!isWin && userSelectedNumber === 0) {
      coinElement.style.animation = '3s linear forwards flipTails';
    }

    setTimeout(() => {
      callback();
      setTimeout(() => {
        resetGame();
      }, 1000);
    }, 3000);
  };

  const updateGameHistory = (isWin, userSelectedNumber, prize, amount, isFreeSpin, isUsedBonusBalance) => {
    const gameHistoryItem = document.createElement('div');
    gameHistoryItem.className = `gameHistoryItem ${isWin ? '' : 'opacity'} ${isUsedBonusBalance ? 'fromBonusBalance' : ''}`;
    gameHistoryItem.innerHTML = `
      <img src="${userSelectedNumber === 0 ? coinHeadsIcon : coinTailsIcon}" alt="${userSelectedNumber === 0 ? 'Heads' : 'Tails'}" />
      <strong>${isWin ? 'Win' : 'Loss'}</strong>
      <span>${isWin ? '+' : '-'} €${isWin ? prize : amount} ${isFreeSpin ? '(Free Spin)' : ''}</span>
    `;
    gameHistoryElement.prepend(gameHistoryItem);

    limitGameHistorySize(gameHistoryElement);
  };

  const limitGameHistorySize = (gameHistoryElement) => {
    const historyItems = gameHistoryElement.getElementsByClassName('gameHistoryItem');
    if (historyItems.length > 10) {
      gameHistoryElement.removeChild(historyItems[historyItems.length - 1]);
    }
  };

  const updateSelectedStakeElements = (text) => {
    selectedStakeElements.forEach((element) => {
      element.textContent = text;
    });
  };

  const resetGame = () => {
    isFlipping = false;
    addShakeAnimation();
    gamePlayButton.style.visibility = 'visible';
  };

  const showPromoCodeMessage = (message, scrollToMessage) => {
    gameMessage.show(message, scrollToMessage);
  };

  return {
    init,
    fetchGameData,
    showPromoCodeMessage
  };
})();

document.addEventListener('DOMContentLoaded', GgWorldCoinFlipGame.init);
