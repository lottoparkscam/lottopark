import { getPreparedApiUrl } from '../../../../js/Helpers/UrlHelper';
import { createPromoCodeHandler } from './PromoCodeHandler';

const MiniGame = (() => {
  let selectedSymbol = null;
  let selectedStakeAmount = null;
  let isSelectSideMessageShown = false;
  let isGameInProgress = false;
  let gameEnded = false;
  let gameResultData = {};

  const multiplierElements = document.querySelectorAll('.gameMultiplier');
  const balanceElements = document.querySelectorAll('.gameBalanceAmount');
  const bonusBalanceElements = document.querySelectorAll('.gameBonusBalanceAmount');
  const availableBetsElement = document.getElementById('availableBets');
  const gameHistoryElement = document.getElementById('gameHistory');
  const gameGridContainer = document.querySelector('.gameGrid');
  const lineLayerContainer = document.querySelector('.lineLayer');
  const selectedStakeElements = document.querySelectorAll('.selectedStakeAmount');
  const gameStakeWrapper = document.querySelector('.gameStakeWrapper');
  const gameStakeTrigger = document.querySelector('.gameStakeTrigger');

  const init = () => {
    fetchGameData();
    attachEventListeners();
  };

  const fetchGameData = (showStartMessage = true) => {
    const url = prepareGameApiUrl('miniGames');
    url.searchParams.set('slug', String(window.miniGameSlug));

    fetch(url, { credentials: 'include' })
      .then((response) => response.json())
      .then((data) => handleGameData(data, showStartMessage))
      .catch(() => showGameMessage('somethingWentWrong'));
  };

  const prepareGameApiUrl = (endpoint) => {
    return getPreparedApiUrl(endpoint);
  };

  const handleGameData = (data, showStartMessage = true) => {
    const { multiplier, availableBets, defaultBet, balance, bonusBalance, history, freeSpinData } = data;

    updateMultiplierDisplay(multiplier);
    updateBalanceDisplay(balance);
    updateBonusBalanceDisplay(bonusBalance);
    renderAvailableBets(availableBets, freeSpinData);
    updateDefaultBet(defaultBet, freeSpinData);
    updateGameHistoryDisplay(history);
    updateFreeSpinElements(freeSpinData);

    if (showStartMessage) {
      showGameMessage('start');
    }
  };

  const updateMultiplierDisplay = (multiplier) => {
    multiplierElements.forEach((element) => {
      element.textContent = `x${multiplier}`;
    });
  };

  const updateBalanceDisplay = (balance) => {
    balanceElements.forEach((element) => {
      element.textContent = balance;
    });
  };

  const updateGameHistoryDisplay = (history) => {
    gameHistoryElement.innerHTML = '';

    history.forEach((item) => {
      const isWin = item.type === 'win';
      const isFreeSpin = item.mini_game_user_promo_code_id !== null;
      const gameHistoryItem = document.createElement('div');

      gameHistoryItem.className = `gameHistoryItem ${isWin ? '' : 'opacity'} ${item.is_bonus_balance_paid == 1 ? 'fromBonusBalance' : ''}`;
      gameHistoryItem.innerHTML = `
        <img src="${item.user_selected_number == 0 ? window.giftImg : window.treeImg}" alt="${item.user_selected_number == 0 ? 'Gift' : 'Tree'}" />
        <strong>${isWin ? 'Win' : 'Loss'}</strong>
        <span>${isWin ? '+' : '-'} €${isWin ? item.prize : item.amount} ${isFreeSpin ? '(Free)' : ''}</span>
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
    const freeSpinCountElements = document.querySelectorAll('.freeSpinCount');
    freeSpinCountElements.forEach((element) => {
      element.textContent = `${freeSpinData.usedFreeSpinCount}/${freeSpinData.freeSpinCount}`;
    });
  };

  const showFreeSpinElements = () => {
    const freeSpinElements = document.querySelectorAll('.freeSpins');
    freeSpinElements.forEach((element) => {
      element.classList.add('show');
    });
  };

  const hideFreeSpinElements = () => {
    const freeSpinElements = document.querySelectorAll('.freeSpins');
    freeSpinElements.forEach((element) => {
      element.classList.remove('show');
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
        `<li data-stake="0">€${freeSpinData.freeSpinValue} (Free)</li>`
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
      selectedStakeAmount = 0;
      updateSelectedStakeElements(`€${freeSpinData.freeSpinValue} (Free)`);
      return;
    }

    selectedStakeAmount = parseFloat(defaultBet);
    updateSelectedStakeElements(`€${defaultBet}`);
  };

  const updateSelectedStakeElements = (text) => {
    selectedStakeElements.forEach((element) => {
      element.textContent = text;
    });
  };

  const attachBetAmountListeners = () => {
    const betListItems = availableBetsElement.querySelectorAll('li');
    const stakeWrapper = document.querySelector('.gameStakeWrapper');
    const trigger = document.querySelector('.gameStakeTrigger');

    betListItems.forEach((li) => {
      li.addEventListener('click', () => {
        if (isGameInProgress) return;
        updateSelectedStake(li);
        stakeWrapper.classList.remove('active');
        trigger.classList.remove('active');
      });
    });
  };

  const updateSelectedStake = (li) => {
    const stake = parseFloat(li.getAttribute('data-stake'));
    selectedStakeAmount = stake;
    const isFreeSpin = stake === 0;
    updateSelectedStakeElements(isFreeSpin ? li.textContent.trim() : `€${stake}`);
  };

  const attachEventListeners = () => {
    const selectSides = document.querySelectorAll('.gameBoxSelectSide');
    initSelectSide(selectSides);

    const playButton = document.getElementById('playMusic');
    const playButtonIcon = document.querySelector('#playMusic .fa-solid');
    const backgroundMusic = document.getElementById('backgroundMusic');
    const clickMusic = document.getElementById('clickMusic');
    initMusicControl(playButton, playButtonIcon, backgroundMusic, clickMusic);

    gameStakeTrigger.addEventListener('click', toggleStakeWrapper);

    createPromoCodeHandler(MiniGame);
  };

  const toggleStakeWrapper = (event) => {
    event.preventDefault();
    if (isGameInProgress) return;
    gameStakeWrapper.classList.toggle('active');
    gameStakeTrigger.classList.toggle('active');
  };

  const initSelectSide = (selectSides) => {
    selectSides.forEach((side) => {
      side.addEventListener('click', function () {
        if (isGameInProgress) return;
        selectSides.forEach((s) => s.classList.remove('gameBoxSelected'));
        this.classList.add('gameBoxSelected');
        selectedSymbol = parseInt(this.getAttribute('data-select'));
        if (isSelectSideMessageShown) {
          isSelectSideMessageShown = false;
          showGameMessage('start');
        }
      });
    });
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

  const initGameGrid = (jsonGrid, handleTileClick) => {
    gameGridContainer.innerHTML = '';
    jsonGrid.forEach((row, rowIndex) => {
      row.forEach((cell, cellIndex) => {
        const gameGridItem = document.createElement('div');
        gameGridItem.classList.add('gameGridItem');
        const icon = cell === 0 ? window.giftImg : window.treeImg;
        const alt = cell === 0 ? 'Gift Icon' : 'Tree Icon';
        gameGridItem.innerHTML = `<img src="${icon}" alt="${alt}" /><div class="itemInvisible"></div>`;
        gameGridItem.setAttribute('data-row', rowIndex);
        gameGridItem.setAttribute('data-cell', cellIndex);
        gameGridItem.querySelector('.itemInvisible').addEventListener('click', () => handleTileClick(gameGridItem, rowIndex, cellIndex));
        gameGridContainer.appendChild(gameGridItem);
      });
    });
  };

  const handleTileClick = (tile, rowIndex, cellIndex, jsonGrid, revealedItems) => {
    if (gameEnded) return;

    tile.classList.add('revealed');
    tile.querySelector('.itemInvisible').style.pointerEvents = 'none';
    revealedItems.push({ rowIndex, cellIndex, value: jsonGrid[rowIndex][cellIndex], element: tile });
    playSound(clickSoundFilePath);

    const winningLine = checkForWinningLine(revealedItems, true);

    if (winningLine) {
      setTimeout(() => {
        if (!gameEnded) {
          endGame(winningLine, jsonGrid, revealedItems, gameResultData);
        }
      }, 1000);
    } else if (revealedItems.length === jsonGrid.length * jsonGrid[0].length) {
      const fullWinningLine = checkForWinningLine(jsonGrid);
      if (!gameEnded) {
        endGame(fullWinningLine, jsonGrid, revealedItems, gameResultData);
      }
    }
  };

  const checkForWinningLine = (gridOrRevealedItems, isRevealedItems = false) => {
    const winningLines = [
      { positions: [[0, 0], [0, 1], [0, 2]], lineClass: 'horizontal-1' },
      { positions: [[1, 0], [1, 1], [1, 2]], lineClass: 'horizontal-2' },
      { positions: [[2, 0], [2, 1], [2, 2]], lineClass: 'horizontal-3' },
      { positions: [[0, 0], [1, 0], [2, 0]], lineClass: 'vertical-1' },
      { positions: [[0, 1], [1, 1], [2, 1]], lineClass: 'vertical-2' },
      { positions: [[0, 2], [1, 2], [2, 2]], lineClass: 'vertical-3' },
      { positions: [[0, 0], [1, 1], [2, 2]], lineClass: 'diagonal-1' },
      { positions: [[0, 2], [1, 1], [2, 0]], lineClass: 'diagonal-2' }
    ];

    for (const line of winningLines) {
      const values = line.positions.map(([row, col]) => {
        if (isRevealedItems) {
          const item = gridOrRevealedItems.find(i => i.rowIndex === row && i.cellIndex === col);
          return item ? item.value : undefined;
        } else {
          return gridOrRevealedItems[row][col];
        }
      });

      if (values.every(value => value === values[0] && value !== undefined && value !== null)) {
        return { symbol: values[0], lineClass: line.lineClass, positions: line.positions };
      }
    }
    return null;
  };

  const startNewGame = () => {
    gameEnded = false;
    const url = prepareGameApiUrl('miniGames/play');
    const body = new FormData();
    body.append('slug', miniGameSlug);
    body.append('userSelectedNumber', selectedSymbol);
    body.append('userSelectedAmountInEur', selectedStakeAmount);

    fetch(url, {
      method: 'POST',
      credentials: 'include',
      body,
    })
      .then((response) => response.json())
      .then((data) => handleGameResult(data))
      .catch(() => showGameMessage('somethingWentWrong'));
  };

  const handleGameResult = (
    {
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
      additionalData,
      errorCode
    }) => {
    if (errorCode) {
      handleGameError(errorCode);
      return;
    }

    gameResultData = {
      isWin,
      isFreeSpin,
      isUsedBonusBalance,
      userSelectedNumber,
      amount,
      prize,
      balanceBeforeFlipResult,
      balanceAfterFlipResult,
      bonusBalanceAfterFlipResult,
    };

    if (!isFreeSpin) {
      updateBalanceDisplay(balanceBeforeFlipResult);
      updateBonusBalanceDisplay(bonusBalanceBeforeFlipResult)
    }

    const jsonGrid = additionalData.jsonGrid;
    const revealedItems = [];
    lineLayerContainer.innerHTML = '';

    const revealAllButton = document.querySelector('.revealAllButton');
    const newRevealAllButton = revealAllButton.cloneNode(true);
    revealAllButton.parentNode.replaceChild(newRevealAllButton, revealAllButton);

    initGameGrid(jsonGrid, (tile, rowIndex, cellIndex) =>
      handleTileClick(tile, rowIndex, cellIndex, jsonGrid, revealedItems)
    );

    const gameOverlay = document.querySelector('.gameOverlay');
    const gameBoard = document.querySelector('.gameBoard');
    gameOverlay.classList.remove('visible', 'fade-in-animation');
    gameBoard.classList.add('visible');

    newRevealAllButton.disabled = false;
    newRevealAllButton.addEventListener('click', () => {
      if (!gameEnded) {
        endGame(null, jsonGrid, revealedItems, gameResultData);
        newRevealAllButton.disabled = true;
      }
    });

    isGameInProgress = true;
  };

  const updateGameHistory = (isWin, userSelectedNumber, prize, amount, isFreeSpin, isUsedBonusBalance) => {
    const gameHistoryItem = document.createElement('div');
    gameHistoryItem.className = `gameHistoryItem ${isWin ? '' : 'opacity'} ${isUsedBonusBalance ? 'fromBonusBalance' : ''}`;
    gameHistoryItem.innerHTML = `
      <img src="${userSelectedNumber === 0 ? giftImg : treeImg}" alt="${userSelectedNumber === 0 ? 'Gift' : 'Tree'}" />
      <strong>${isWin ? 'Win' : 'Loss'}</strong>
      <span>${isWin ? '+' : '-'} €${isWin ? prize : amount} ${isFreeSpin ? '(Free)' : ''}</span>
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

  const handleGameError = (errorCode) => {
    switch (errorCode) {
      case -1:
        showGameMessage('notFound');
        break;
      case -2:
        showGameMessage('insufficientBalance');
        break;
      default:
        showGameMessage('somethingWentWrong');
        break;
    }
  };

  const endGame = (winningLine, jsonGrid, revealedItems, gameResultData) => {
    if (gameEnded) return;
    gameEnded = true;

    const { balanceAfterFlipResult, bonusBalanceAfterFlipResult, prize, isWin, userSelectedNumber, amount, isFreeSpin, isUsedBonusBalance } = gameResultData || {};

    revealAllTiles(jsonGrid, revealedItems);

    if (!winningLine) {
      winningLine = checkForWinningLine(jsonGrid);
    }

    if (winningLine) {
      setTimeout(() => {
        showWinningLine(winningLine, revealedItems);
      }, 1000);
    }

    setTimeout(() => {
      if (gameResultData) {
        if (isWin && winningLine && winningLine.symbol === selectedSymbol) {
          showGameMessage('win', prize);
          updateBalanceDisplay(balanceAfterFlipResult);
          updateBonusBalanceDisplay(bonusBalanceAfterFlipResult);
          playSound(wonSoundFilePath);
        } else {
          showGameMessage('loss');
          playSound(lossSoundFilePath);
        }

        if (isFreeSpin) {
          fetchGameData(false);
        }

        updateGameHistory(isWin, userSelectedNumber, prize, amount, isFreeSpin, isUsedBonusBalance);
        isGameInProgress = false;
      }
    }, 2000);
  };

  const revealAllTiles = (jsonGrid, revealedItems) => {
    const unrevealedItems = document.querySelectorAll('.itemInvisible:not([style*="pointer-events: none"])');
    unrevealedItems.forEach(item => {
      item.style.pointerEvents = 'none';
      const tileElement = item.parentElement;
      tileElement.classList.add('revealed');
      const rowIndex = parseInt(tileElement.getAttribute('data-row'));
      const cellIndex = parseInt(tileElement.getAttribute('data-cell'));
      revealedItems.push({ rowIndex, cellIndex, value: jsonGrid[rowIndex][cellIndex], element: tileElement });
    });

    playSound(clickSoundFilePath);
  };

  const showWinningLine = (winningLine, revealedItems) => {
    lineLayerContainer.innerHTML = '';

    const lineColorClass = winningLine.symbol === 0 ? 'red' : 'white';
    lineLayerContainer.innerHTML = `<div class="line ${lineColorClass} ${winningLine.lineClass}"></div>`;
    winningLine.positions.forEach(pos => {
      const item = revealedItems.find(item => item.rowIndex === pos[0] && item.cellIndex === pos[1]);
      if (item) {
        item.element.classList.add('winLine');
      }
    });
  };

  const showPromoCodeMessage = (message, scrollToMessage) => {
    const gameOverlay = document.querySelector('.gameOverlay');
    const gameOverlayContent = gameOverlay.querySelector('.gameOverlayContent');
    gameOverlayContent.innerHTML = '';

    let messageHTML = `
      <div class="gameBoxMessage sadSanta pcgm">
          <img src="${window.happySantaImg}" alt="Happy Santa" />
          <div class="gameBoxMessageContent">
              <div class="gameBoxMessageTitle bigText">${message}</div>
              <a href="#" class="gameButtonPlay">Click here to start</a>
          </div>
      </div>`;

    gameOverlayContent.innerHTML = messageHTML;
    gameOverlay.classList.add('visible', 'fade-in-animation');

    if (scrollToMessage) {
      const gameBoxMessage = gameOverlayContent.querySelector('.gameBoxMessage');
      const offsetTop = gameBoxMessage.getBoundingClientRect().top + window.scrollY - 50;

      window.scrollTo({
        top: offsetTop,
        behavior: 'smooth'
      });
    }

    const playButton = gameOverlay.querySelector('.gameButtonPlay');
    playButton.addEventListener('click', (e) => {
      if (!playButton.href.includes(depositUrl)) {
        e.preventDefault();
      } else {
        isGameInProgress = true;
      }

      if (selectedSymbol === null) {
        showGameMessage('start');
        return;
      }
      gameOverlay.classList.remove('visible', 'fade-in-animation');
      startNewGame();
    });
  };

  const showGameMessage = (result, winAmount = '') => {
    const gameOverlay = document.querySelector('.gameOverlay');
    const gameOverlayContent = gameOverlay.querySelector('.gameOverlayContent');
    const gameBoard = document.querySelector('.gameBoard');
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
      case 'start':
        imageSrc = window.happySantaImg;
        messageTitle = 'Choose your symbol:';
        buttonText = 'Click here to start';
        messageHTML = `
                <div class="gameBoxMessage start">
                    <div class="gameBoxMessageContent">
                        <div class="gameBoxMessageTitle bigText">${messageTitle}</div>
                        <div class="gameBoxSelect">
                            <div class="gameBoxSelectSide ${selectedSymbol === 0 ? 'gameBoxSelected' : ''}" data-select="0">
                                <img src="${window.giftImg}" alt="Gift" />
                            </div>
                            <div class="gameBoxSelectSide ${selectedSymbol === 1 ? 'gameBoxSelected' : ''}"" data-select="1">
                                <img src="${window.treeImg}" alt="Tree" />
                            </div>
                        </div>
                        <a href="${buttonLink}" class="gameButtonPlay">${buttonText}</a>
                    </div>
                </div>`;
        break;
      case 'win':
        imageSrc = window.happySantaImg;
        messageTitle = `You won €${winAmount}!`;
        buttonText = 'Play again!';
        break;
      case 'loss':
        imageSrc = window.sadSantaImg;
        messageTitle = 'Not this time!';
        buttonText = 'Play again!';
        additionalClass = 'sadSanta';
        break;
      case 'insufficientBalance':
        imageSrc = window.sadSantaImg;
        messageTitle = 'Insufficient funds for the selected bet.';
        buttonText = 'Click here to top up';
        buttonLink = depositUrl;
        isExitButton = true;
        additionalClass = 'sadSanta';
        break;
      case 'notFound':
        imageSrc = window.sadSantaImg;
        messageTitle = 'Selected game not found.';
        buttonText = 'Click here to start';
        additionalClass = 'sadSanta';
        break;
      case 'somethingWentWrong':
        imageSrc = window.sadSantaImg;
        messageTitle = 'Something went wrong. Please try again shortly.';
        buttonText = 'Click here to start';
        additionalClass = 'sadSanta';
        break;
    }

    if (!messageHTML) {
      messageHTML = `
        <div class="gameBoxMessage ${additionalClass}">
            ${isExitButton ? '<i class="fa-solid fa-xmark closeButton"></i>' : ''}
            <img src="${imageSrc}" alt="Santa" />
            <div class="gameBoxMessageContent">
                ${messageTitle ? `<div class="gameBoxMessageTitle bigText ${isExitButton ? 'pt' : ''}">${messageTitle}</div>` : ''}
                <a href="${buttonLink}" class="gameButtonPlay">${buttonText}</a>
            </div>
        </div>`;
    }

    gameOverlayContent.innerHTML = messageHTML;
    gameOverlay.classList.add('visible', 'fade-in-animation');

    const selectSides = gameOverlayContent.querySelectorAll('.gameBoxSelectSide');
    if (selectSides) {
      selectSides.forEach(side => {
        side.addEventListener('click', function () {
          selectSides.forEach(s => s.classList.remove('gameBoxSelected'));
          this.classList.add('gameBoxSelected');
          selectedSymbol = parseInt(this.getAttribute('data-select'));
        });
      });
    }

    const playButton = gameOverlay.querySelector('.gameButtonPlay');
    playButton.addEventListener('click', (e) => {
      e.preventDefault();

      if (result === 'win' || result === 'loss') {
        gameBoard.classList.remove('visible');
        showGameMessage('start');
        return;
      }

      if (selectedSymbol === null) {
        const selectSides = document.querySelectorAll('.gameBoxSelectSide');
        selectSides.forEach(side => {
          side.classList.add('flashOrangeBorder');
        });

        setTimeout(() => {
          selectSides.forEach(side => {
            side.classList.remove('flashOrangeBorder');
          });
        }, 1000);

        return;
      }
      startNewGame();
    });

    const closeButton = gameOverlay.querySelector('.closeButton');
    if (closeButton) {
      closeButton.addEventListener('click', () => {
        showGameMessage('start');
      });
    }
  };

  // caching sounds to prevent overlapping
  const sounds = {};

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
    showPromoCodeMessage,
    fetchGameData
  };
})();

document.addEventListener('DOMContentLoaded', () => {
  MiniGame.init();
});
