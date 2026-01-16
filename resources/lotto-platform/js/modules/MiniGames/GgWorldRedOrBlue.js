import { getPreparedApiUrl } from '../../../../js/Helpers/UrlHelper';
import { createPromoCodeHandler } from './PromoCodeHandler';

const GgWorldRedOrBlueGame = (() => {
  let miniGameSlug = window.miniGameSlug;
  let userSelectedNumber = null;
  let userSelectedAmountInEur = null;
  let isFlipping = false;

  const multiplierElements = document.querySelectorAll('.gameMultiplier');
  const balanceElements = document.querySelectorAll('.gameBalanceAmount');
  const bonusBalanceElements = document.querySelectorAll('.gameBonusBalanceAmount');
  const availableBetsElement = document.getElementById('availableBets');
  const gameHistoryElement = document.getElementById('gameHistory');
  const gamePlayButton = document.querySelector('.gamePlayBtn');
  const gameStakeWrapper = document.querySelector('.gameStakeWrapper');
  const gameStakeTrigger = document.querySelector('.gameStakeTrigger');
  const sideHeads = document.querySelector('.sideHeads');
  const sideTails = document.querySelector('.sideTails');
  const boxTriggerElement = document.getElementById('box'); // element .box
  const selectedStakeElements = document.querySelectorAll('.selectedStakeAmount');
  let isSelectSideMessageShown = false;
  const sounds = {};

  const init = () => {
    fetchGameData();
    attachEventListeners();
  };

  const fetchGameData = () => {
    const url = prepareGameApiUrl('miniGames');
    url.searchParams.set('slug', String(miniGameSlug));

    fetch(url, { credentials: 'include' })
      .then((response) => response.json())
      .then((data) => handleGameData(data))
      .catch((e) => { showGameMessage('somethingWentWrong'); console.log(e); });
  };

  const prepareGameApiUrl = (endpoint) => {
    return getPreparedApiUrl(endpoint);
  };

  const handleGameData = (data) => {
    const { multiplier, availableBets, defaultBet, balance, bonusBalance, history, freeSpinData } = data;

    updateMultiplierDisplay(multiplier);
    updateBalanceDisplay(balance);
    updateBonusBalanceDisplay(bonusBalance);
    renderAvailableBets(availableBets, freeSpinData);
    updateDefaultBet(defaultBet, freeSpinData);
    updateGameHistoryDisplay(history);
    updateFreeSpinElements(freeSpinData);
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
        playSound(clickSoundFilePath);
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
            <img src="${item.user_selected_number == 0 ? window.redBox : window.blueBox}" alt="${item.user_selected_number == 0 ? 'Red' : 'Blue'}" />
            <strong>${isWin ? 'Win' : 'Loss'}</strong>
            <span>${isWin ? '+' : '-'} €${isWin ? item.prize : item.amount} ${isFreeSpin ? '(Free Spin)' : ''}</span>
          `;
      gameHistoryElement.append(gameHistoryItem);
    });
  };

  const updateFreeSpinElements = (freeSpinData) => {
    if (freeSpinData && freeSpinData.hasUsedAllSpins !== null) {
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
    sideHeads.addEventListener('click', () => updateBoxSelection('Heads'));
    sideTails.addEventListener('click', () => updateBoxSelection('Tails'));
    boxTriggerElement.addEventListener('click', () => playGame());
    gamePlayButton.addEventListener('click', (e) => { e.preventDefault(); playGame(); });
    gameStakeTrigger.addEventListener('click', toggleStakeWrapper);

    const playButton = document.getElementById('playMusic');
    const playButtonIcon = document.querySelector('#playMusic .fa-solid');
    const backgroundMusic = document.getElementById('backgroundMusic');
    const clickMusic = document.getElementById('clickMusic');
    initMusicControl(playButton, playButtonIcon, backgroundMusic, clickMusic);

    createPromoCodeHandler(GgWorldRedOrBlueGame);
  };

  const initMusicControl = (playButton, playButtonIcon, backgroundMusic, clickMusic) => {
    playButton.addEventListener('click', () => {
      toggleMusic(playButtonIcon, backgroundMusic, clickMusic);
    });
  };

  const toggleMusic = (playButtonIcon, backgroundMusic, clickMusic) => {
    backgroundMusic.volume = 0.1;
    backgroundMusic.muted = !backgroundMusic.muted;
    clickMusic.muted = backgroundMusic.muted;

    for (const sound of Object.values(sounds)) {
      sound.muted = backgroundMusic.muted;
    }

    if (!backgroundMusic.muted) {
      backgroundMusic.play();
      clickMusic.volume = 0.7;
      playButtonIcon.classList.remove('fa-volume-xmark');
      playButtonIcon.classList.add('fa-volume-high');
    } else {
      playButtonIcon.classList.add('fa-volume-xmark');
      playButtonIcon.classList.remove('fa-volume-high');
    }
  };

  const updateBoxSelection = (side) => {
    if (isFlipping) return;

    playSound(clickSoundFilePath);

    ['Heads', 'Tails'].forEach((boxSide) => {
      const element = document.querySelector(`.side${boxSide}`);
      element.classList.remove('gameBoxSelectedRed', 'gameBoxSelectedBlue');
    });

    if (side === 'Heads') {
      document.querySelector('.sideHeads').classList.add('gameBoxSelectedRed');
      userSelectedNumber = 0;
    } else {
      document.querySelector('.sideTails').classList.add('gameBoxSelectedBlue');
      userSelectedNumber = 1;
    }

    if (isSelectSideMessageShown) {
      const gameOverlay = document.querySelector('.gameOverlay');
      gameOverlay.classList.remove('visible', 'fade-in-animation');
      isSelectSideMessageShown = false;
    }
  };

  const toggleStakeWrapper = (event) => {
    event.preventDefault();
    playSound(clickSoundFilePath);
    gameStakeWrapper.classList.toggle('active');
    gameStakeTrigger.classList.toggle('active');
  };

  const playGame = () => {
    playSound(clickSoundFilePath);

    if (!isValidGameSelection()) {
      showGameMessage('chooseSide');
      return;
    }

    if (isFlipping) return;

    startGame();
  };

  const isValidGameSelection = () => {
    return userSelectedNumber !== null && userSelectedAmountInEur !== null;
  };

  const startGame = () => {
    isFlipping = true;
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
                              userSelectedNumber,
                              amount,
                              prize,
                              balanceBeforeFlipResult,
                              balanceAfterFlipResult,
                              bonusBalanceBeforeFlipResult,
                              bonusBalanceAfterFlipResult,
                              errorCode
                            }) => {
    if (errorCode) {
      handleGameError(errorCode);
      return;
    }

    if (!isFreeSpin) {
      updateBalanceDisplay(balanceBeforeFlipResult);
      updateBonusBalanceDisplay(bonusBalanceBeforeFlipResult);
    }

    animateBoxWithEffect(() => {
      updateGameHistory(isWin, userSelectedNumber, prize, amount, isFreeSpin, isUsedBonusBalance);
      updateBalanceDisplay(balanceAfterFlipResult);
      updateBonusBalanceDisplay(bonusBalanceAfterFlipResult);

      if (isFreeSpin) {
        fetchGameData();
      }

      if (isWin) {
        playSound(wonSoundFilePath);
        showGameMessage('win', prize);
      } else {
        showGameMessage('loss');
        playSound(lossSoundFilePath);
      }

      setTimeout(() => {
        resetGame();
      }, 1000)
    }, userSelectedNumber, isWin);
  };

  const handleGameError = (errorCode) => {
    switch (errorCode) {
      case -1:
        showGameMessage('notFound');
        break;
      case -2:
        showGameMessage('insufficientBalance');
        break;
      case -3:
        showGameMessage('chooseSide');
        break;
      default:
        showGameMessage('somethingWentWrong');
        break;
    }
    resetGame();
  };

  const animateBoxWithEffect = (callback, userSelectedNumber, isWin) => {
    boxTriggerElement.classList.remove('shake', 'fade-out', 'fade-in');
    void boxTriggerElement.offsetWidth;
    boxTriggerElement.innerHTML = '';

    const handleShakeEnd = (e) => {
      if (e.animationName === 'shake-desktop' || e.animationName === 'shake-mobile') {
        boxTriggerElement.removeEventListener('animationend', handleShakeEnd);
        boxTriggerElement.classList.remove('shake');

        boxTriggerElement.classList.add('fade-out');
        boxTriggerElement.addEventListener('animationend', handleFadeOut);
      }
    };

    const handleFadeOut = (e) => {
      if (e.animationName === 'fadeOut') {
        boxTriggerElement.removeEventListener('animationend', handleFadeOut);
        boxTriggerElement.style.background = 'none';

        const randomImage = (userSelectedNumber === 0)
          ? (isWin ? window.redBox : window.blueBox)
          : (isWin ? window.blueBox : window.redBox);
        boxTriggerElement.innerHTML = `<img src="${randomImage}" class="winBox pop-in" alt="Winner" />`;
        boxTriggerElement.classList.remove('fade-out');
        void boxTriggerElement.offsetWidth; // wymusza reflow
        boxTriggerElement.classList.add('fade-in');
        boxTriggerElement.addEventListener('animationend', handleFadeIn);
      }
    };

    const handleFadeIn = (e) => {
      if (e.animationName === 'popIn') {
        boxTriggerElement.removeEventListener('animationend', handleFadeIn);
        setTimeout(() => {
            callback();
        }, 1500);
      }
    };

    boxTriggerElement.addEventListener('animationend', handleShakeEnd);
    boxTriggerElement.classList.add('shake');
  };

  const updateGameHistory = (isWin, userSelectedNumber, prize, amount, isFreeSpin, isUsedBonusBalance) => {
    const gameHistoryItem = document.createElement('div');
    gameHistoryItem.className = `gameHistoryItem ${isWin ? '' : 'opacity'} ${isUsedBonusBalance ? 'fromBonusBalance' : ''}`;
    gameHistoryItem.innerHTML = `
          <img src="${userSelectedNumber === 0 ? window.redBox : window.blueBox}" alt="${userSelectedNumber === 0 ? 'Red' : 'Blue'}" />
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
    gamePlayButton.style.visibility = 'visible';
    boxTriggerElement.innerHTML = '';
    boxTriggerElement.style.background = 'url("'+boxImage+'") center center no-repeat';
  };

  const showGameMessage = (result, winAmount = '') => {
    const gameOverlay = document.querySelector('.gameOverlay');
    const gameOverlayContent = gameOverlay.querySelector('.gameOverlayContent');
    gameOverlayContent.innerHTML = '';

    isSelectSideMessageShown = (result === 'selectSide');

    let messageHTML = '';
    let imageSrc = '';
    let messageTitle = '';
    let buttonText = '';
    let buttonLink = '#';
    let additionalClass = '';
    let isExitButton = false;

    switch (result) {
      case 'win':
        imageSrc = userSelectedNumber === 0 ? window.happyRedChar : window.happyBlueChar;
        messageTitle = `You won €${winAmount}!`;
        buttonText = 'Play again!';
        break;
      case 'loss':
        imageSrc = userSelectedNumber === 0 ? window.sadRedChar : window.sadBlueChar;
        messageTitle = 'Not this time!';
        buttonText = 'Play again!';
        additionalClass = 'sadChar';
        break;
      case 'insufficientBalance':
        imageSrc = window.sadBlueChar;
        messageTitle = 'Insufficient funds for the selected bet.';
        buttonText = 'Click here to top up';
        buttonLink = depositUrl;
        additionalClass = 'sadChar';
        isExitButton = true;
        break;
      case 'notFound':
        imageSrc = window.sadBlueChar;
        messageTitle = 'Selected game not found.';
        buttonText = 'Close';
        additionalClass = 'sadChar';
        break;
      case 'chooseSide':
        imageSrc = window.sadBlueChar;
        messageTitle = 'Choose red or blue before slipping';
        buttonText = 'Close';
        additionalClass = 'sadChar';
        isSelectSideMessageShown = true;
        break;
      case 'somethingWentWrong':
        imageSrc = window.sadBlueChar;
        messageTitle = 'Something went wrong. Please try again shortly.';
        buttonText = 'Close';
        additionalClass = 'sadChar';
        break;
    }

    if (!messageHTML) {
      messageHTML = `
            <div class="gameBoxMessage ${additionalClass}">
                ${isExitButton ? '<i class="fa-solid fa-xmark closeButton"></i>' : ''}
                <img src="${imageSrc}" alt="Character" />
                <div class="gameBoxMessageContent">
                    ${messageTitle ? `<div class="gameBoxMessageTitle bigText">${messageTitle}</div>` : ''}
                    <a href="${buttonLink}" class="gameButton">${buttonText}</a>
                </div>
            </div>`;
    }

    gameOverlayContent.innerHTML = messageHTML;
    gameOverlay.classList.add('visible', 'fade-in-animation');

    const gameButton = gameOverlay.querySelector('.gameButton');
    if (gameButton) {
      gameButton.addEventListener('click', (e) => {
        if (gameButton.getAttribute('href') === '#') {
          e.preventDefault();
          gameOverlay.classList.remove('visible', 'fade-in-animation');
        }
      });
    }

    const closeButton = gameOverlay.querySelector('.closeButton');
    if (closeButton) {
      closeButton.addEventListener('click', () => {
        gameOverlay.classList.remove('visible', 'fade-in-animation');
      });
    }
  };

  const showPromoCodeMessage = (message, scrollToMessage) => {
    console.log('debug')
    const gameOverlay = document.querySelector('.gameOverlay');
    const gameOverlayContent = gameOverlay.querySelector('.gameOverlayContent');
    gameOverlayContent.innerHTML = '';

    let messageHTML = `
        <div class="gameBoxMessage sadChar">
              <i class="fa-solid fa-xmark closeButton"></i>
              <img src="${window.sadBlueChar}" alt="Character" />
              <div class="gameBoxMessageContent">
                 <div class="gameBoxMessageTitle bigText">${message}</div>
              </div>
        </div>`;

    gameOverlayContent.innerHTML = messageHTML;
    gameOverlay.classList.add('visible', 'fade-in-animation');

    const closeButton = gameOverlay.querySelector('.closeButton');
    if (closeButton) {
      closeButton.addEventListener('click', () => {
        gameOverlay.classList.remove('visible', 'fade-in-animation');
      });
    }

    if (scrollToMessage) {
      const gameBoxMessage = gameOverlayContent.querySelector('.gameBoxMessage');
      const offsetTop = gameBoxMessage.getBoundingClientRect().top + window.scrollY - 120;

      window.scrollTo({
        top: offsetTop,
        behavior: 'smooth'
      });
    }
  };

  const playSound = (soundFile) => {
    if (!sounds[soundFile]) {
      sounds[soundFile] = new Audio(soundFile);
    }

    const sound = sounds[soundFile];
    sound.muted = document.getElementById('backgroundMusic').muted;
    if (!sound.muted) {
      sound.currentTime = 0;
      sound.volume = 0.7;
      sound.play();
    }
  };

  return {
    init,
    fetchGameData,
    showPromoCodeMessage
  };
})();

document.addEventListener('DOMContentLoaded', GgWorldRedOrBlueGame.init);