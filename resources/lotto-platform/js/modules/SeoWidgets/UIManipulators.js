export function hidePointersOnUnpickedTickets(isBonus) {
  const selector = isBonus
    ? '.widget-ticket-bnumbers > a'
    : '.widget-ticket-numbers > a';
  document.querySelectorAll(selector).forEach((button) => {
    const isNotCurrentlyPicked = !button.classList.contains('checked');
    if (isNotCurrentlyPicked) {
      button.classList.add('withoutPointer');
    }
  });
}

export function showAllPointers(isBonus) {
  const selector = isBonus
    ? '.widget-ticket-bnumbers > a'
    : '.widget-ticket-numbers > a';
  document.querySelectorAll(selector).forEach((button) => {
    button.classList.remove('withoutPointer');
  });
}

export function rebuildPlayButtonUrl(normalNumbers, bonusNumbers) {
  const playButton = getPlayButton();
  const quickPickUrl = new URL(playButton.href);
  quickPickUrl.searchParams.set('numbers', normalNumbers);
  quickPickUrl.searchParams.set('bnumbers', bonusNumbers);
  playButton.href = quickPickUrl;
}

export function enablePlayButton() {
  const playButton = getPlayButton();
  playButton.classList.remove('disabled');
}

export function disablePlayButton() {
  const playButton = getPlayButton();
  playButton.classList.add('disabled');
}

export function getPlayButton() {
  return document.getElementById('seo-widget-play-button');
}
