import { getPreparedApiUrl } from '../../../../js/Helpers/UrlHelper';

export function createPromoCodeHandler(MiniGame) {
  const promoInput = document.querySelector('.promoCodeInput');
  const applyButton = document.querySelector('.promoCodeInputGroup button');

  async function applyPromoCode() {
    const promoCode = promoInput.value;

    if (!promoCode) {
      return;
    }

    const url = getPreparedApiUrl('miniGames/applyPromoCode');
    const body = new FormData();
    body.append('slug', String(window.miniGameSlug));
    body.append('promoCode', promoCode);

    fetch(url, {
      method: 'POST',
      credentials: 'include',
      body,
    })
      .then((response) => response.json())
      .then(({ isSuccess, message }) => {
        MiniGame.showPromoCodeMessage(message, true);

        if (isSuccess) {
          MiniGame.fetchGameData();
          clearPromoCodeInput();
        }
      });
  }

  function clearPromoCodeInput() {
    promoInput.value = '';
    updateButtonState();
  }

  function updateButtonState() {
    if (promoInput.value.length >= 2) {
      applyButton.disabled = false;
      applyButton.style.backgroundColor = '#67bd2e';
    } else {
      applyButton.disabled = true;
      applyButton.style.backgroundColor = '';
    }
  }

  promoInput.addEventListener('input', updateButtonState);
  applyButton.addEventListener('click', applyPromoCode);
}