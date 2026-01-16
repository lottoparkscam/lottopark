import { getPreparedApiUrl } from '../../../js/Helpers/UrlHelper';
import { isHomePage, isRafflePlayPage } from '../../../js/Helpers/PageHelper';

export function initFlashMessages(callback) {
  const shouldNotInitFlashMessages = !isHomePage() && !isRafflePlayPage();
  if (shouldNotInitFlashMessages) {
    callback();
    return;
  }

  const flashMessagesContainer = document.getElementById('flashmessages');
  const body = new FormData();
  /** @type {string} */
  const resendLink =
    typeof window.resendLink !== 'undefined' ? window.resendLink : '';
  /** @type {string} */
  const activationText =
    typeof window.activationText !== 'undefined' ? window.activationText : '';
  body.append('resendLink', resendLink);
  body.append('activationText', activationText);
  body.append('isFrontPage', isHomePage());

  const url = getPreparedApiUrl('flashMessage/all');
  fetch(url, {
    method: 'POST',
    credentials: 'include',
    body,
  })
    .then((response) => response.json())
    .then(({ flashMessages }) => {
      if (flashMessagesContainer) {
        flashMessagesContainer.insertAdjacentHTML('afterend', flashMessages);
      }

      callback();
    });
}
