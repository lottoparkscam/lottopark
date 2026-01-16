import {
  getPreparedApiUrl,
  isCasino,
  isNotCasino,
} from '../../../js/Helpers/UrlHelper';

export function fetchUserDetails() {
  const balanceContainer = document.getElementById('user-balance-amount');
  const mobileBalanceContainer = document.getElementById(
    'mobile-user-balance-amount',
  );
  const bonusBalanceContainer = document.getElementById(
    'user-bonus-balance-amount',
  );
  const mobileBonusBalanceContainer = document.getElementById(
    'mobile-user-bonus-balance-amount',
  );
  const userInfoUserName = document.getElementById('user-info-user-name');

  const url = getPreparedApiUrl('account/details');

  if (!balanceContainer && !bonusBalanceContainer) {
    return;
  }

  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ name, balance, bonusBalance, casinoBalance }) => {
      if (isCasino()) {
        balanceContainer.innerText = casinoBalance;
        mobileBalanceContainer.innerText = casinoBalance;
      } else {
        const bonusBalanceValue = parseFloat(
          bonusBalance.replace(/[^\d.-]/g, ''),
        );

        balanceContainer.innerText = balance;
        mobileBalanceContainer.innerText = balance;

        bonusBalanceContainer.innerText =
          bonusBalanceValue > 0 ? `bonus: ${bonusBalance}` : '';
        mobileBonusBalanceContainer.innerText = bonusBalance;
      }

      userInfoUserName.innerText =
        name.length === 0 ? window.anonymousUserName : name;
    })
    .catch(() => {
      balanceContainer.innerText = '';
      let loadingSpan = document.createElement('span');
      loadingSpan.className = 'loading';
      balanceContainer.appendChild(loadingSpan);
      mobileBalanceContainer.innerText = '';

      if (isNotCasino()) {
        bonusBalanceContainer.innerText = '';
        mobileBonusBalanceContainer.innerText = '';
      }
    });
}

export function checkIsUserLogged(callback) {
  const params = new URLSearchParams(window.location.search);
  const uref = params.has('uref') ? params.get('uref') : '';

  const url = getPreparedApiUrl('account/isUserLogged');
  url.searchParams.set('uref', uref);

  fetch(url, { credentials: 'include', redirect: 'manual' })
    .then((response) => {
      const isRedirected = response.type === 'opaqueredirect';
      if (isRedirected) {
        window.location.href = response.url;
        return;
      }

      return response.json();
    })
    .then(({ isUserLogged }) => {
      callback(isUserLogged);
    });
}

export function showUserArea() {
  document.querySelectorAll('.only-logged-user').forEach((element) => {
    element.classList.remove('only-logged-user');
  });
}

export function showNotUserArea() {
  document.querySelectorAll('.only-not-logged-user').forEach((element) => {
    element.classList.remove('only-not-logged-user');
  });
}
