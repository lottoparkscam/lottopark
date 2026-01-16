import { getPreparedApiUrl, isCasino } from '../../../js/Helpers/UrlHelper';

export function initPopupFromQueue(popupClass) {
  if (isCasino()) {
    return;
  }

  const url = getPreparedApiUrl('popup/fromQueue');

  const POPUP_BUTTON_CLOSE = 2;
  const POPUP_BUTTON_URL = 5;
  const POPUP_BUTTON_OK = 8;

  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ title, content, isPromocode }) => {
      if (!title) {
        return;
      }

      const buttons = isPromocode
        ? [POPUP_BUTTON_OK, POPUP_BUTTON_CLOSE]
        : [POPUP_BUTTON_URL, POPUP_BUTTON_CLOSE];
      new popupClass(title, content, buttons).show();
    });
}

export function initFirstVisitPopup(popupClass) {
  if (isCasino()) {
    return;
  }

  const dialogWrapperNotExists = !document.getElementById('dialog-wrapper');
  if (dialogWrapperNotExists) {
    return;
  }

  const POPUP_BUTTON_OK = 8;

  const url = getPreparedApiUrl('popup/shouldShowFirstVisit');
  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ shouldShow }) => {
      const dialogWrapperDataset =
        document.getElementById('dialog-wrapper').dataset;
      const firstVisitPopupExists = dialogWrapperDataset.firstVisit === 'true';
      if (shouldShow && firstVisitPopupExists) {
        const timeout =
          parseInt(dialogWrapperDataset.firstVisitPopupTimeout) * 1000;
        setTimeout(function () {
          new popupClass(
            dialogWrapperDataset.firstVisitTitle,
            dialogWrapperDataset.firstVisitContent,
            [POPUP_BUTTON_OK],
          ).show();
        }, timeout);
      }
    });
}
