export function isParentByPageSlug(pageSlug) {
  return window.parentSlug === pageSlug;
}

export function isPageBySlug(pageSlug) {
  return window.currentPageSlug === pageSlug;
}

/**
 * Check if current page is any child of 'lotteries' parent
 * Lotteries Parent is Info Page about lottery
 *
 * You can also give lotterySlug to check page with specific slug
 * @param {string|null} lotterySlug
 * @returns {boolean}
 */
export function isSpecificLotteriesPage(lotterySlug = null) {
  const isLotteriesParent = isParentByPageSlug('lotteries');
  const isSpecificLotteriesPage = window.parentSlug !== window.currentPageSlug;

  if (!lotterySlug) {
    return isLotteriesParent && isSpecificLotteriesPage;
  }

  return isLotteriesParent && isPageBySlug(lotterySlug);
}

export function isNotSpecificLotteriesPage(lotterySlug = null) {
  return !isSpecificLotteriesPage(lotterySlug);
}

export function isLotteriesPage() {
  return isParentByPageSlug('lotteries');
}

export function isSpecificRafflePage() {
  return (
    isParentByPageSlug('information-raffle') ||
    isParentByPageSlug('play-raffle') ||
    isParentByPageSlug('results-raffle')
  );
}

export function isPlayPage() {
  return isParentByPageSlug('play');
}

export function isRafflePlayPage() {
  return isParentByPageSlug('play-raffle');
}

export function isMainPlayPage() {
  return window.currentPageSlug === 'play';
}

/**
 * Check if current page is any child of 'play' parent
 * Play Parent is place where you can pick numbers for a ticket
 *
 * You can also give lotterySlug to check page with specific slug
 * @param {string|null} lotterySlug
 * @returns {boolean}
 */
export function isSpecificPlayPage(lotterySlug = null) {
  const isPlayParent = isParentByPageSlug('play');
  const isSpecificPlayPage = window.parentSlug !== window.currentPageSlug;

  if (!lotterySlug) {
    return isPlayParent && isSpecificPlayPage;
  }

  return isPlayParent && isPageBySlug(lotterySlug);
}

/**
 * Check if current page is any child of 'results' parent
 * Results Parent is place where you find last draw numbers
 *
 * You can also give lotterySlug to check page with specific slug
 * @param {string|null} lotterySlug
 * @returns {boolean}
 */
export function isSpecificResultsPage(lotterySlug = null) {
  const isResultsParent = isParentByPageSlug('results');
  const isSpecificResultsPage = window.parentSlug !== window.currentPageSlug;

  if (!lotterySlug) {
    return isResultsParent && isSpecificResultsPage;
  }

  return isResultsParent && isPageBySlug(lotterySlug);
}

export function isHomePage() {
  return window.currentPageSlug === 'home';
}
