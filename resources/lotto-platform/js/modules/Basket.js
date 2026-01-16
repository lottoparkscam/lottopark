import { isCasino, getPreparedApiUrl } from '../../../js/Helpers/UrlHelper';

export function initBasketData() {
  const orderAmountContainer = document.getElementById('order-info-amount');
  const orderCountContainer = document.getElementById('order-count');
  const loginButton = document.getElementById('btn-login');
  const signUpButton = document.getElementById('btn-signup');

  if (isCasino()) {
    return;
  }

  if (orderAmountContainer && orderCountContainer) {
    const url = getPreparedApiUrl('order/summary');
    fetch(url, { credentials: 'include' })
      .then((response) => response.json())
      .then(({ sum, sumAfterDiscount, count }) => {
        updateSummary(sum, sumAfterDiscount, count);

        if (parseInt(count) > 0 && loginButton && signUpButton) {
          loginButton && loginButton.classList.add('btn-login-order');
          signUpButton && signUpButton.classList.add('btn-sign-up-order');
        }
      })
      .catch(() => {
        orderAmountContainer.innerText = '0';
        orderCountContainer.innerText = '0';
      });
  }
}

export function fetchBasketItems() {
  const mainBasketContainer = document.getElementById('mainBasketContainer');
  const basketDataContainer = document.getElementById('basketDataContainer');
  const addNotEmptyClassesContainer = document.getElementById(
    'add-notempty-classes',
  );

  const isOrderPage =
    typeof window.orderPathInUserLanguage !== 'undefined' &&
    window.location.href === window.orderPathInUserLanguage;
  if (isOrderPage) {
    mainBasketContainer.remove();
    return;
  }

  if (isCasino()) {
    return;
  }

  const url = getPreparedApiUrl('order/items');
  fetch(url, { credentials: 'include' })
    .then((response) => response.json())
    .then((items) => {
      if (items.length === 0) {
        mainBasketContainer.remove();
        return;
      }

      addNotEmptyClassesContainer.classList.add('order-info-notempty');
      basketDataContainer.innerHTML = '';
      items.forEach(({ label, price }) => {
        basketDataContainer.innerHTML += `<li>
                      ${label}<a href="#" class="order-info-delete-item">
                          <span class="fa fa-times" title="${window.deleteInUserLanguage}"></span>
                      </a><span class="order-info-price">${price}</span>
                  </li>`;
      });

      addEventListenerOnDeleteItems();
    })
    .catch(() => {});
}

function addEventListenerOnDeleteItems() {
  const orderItemDeleteButtons = document.querySelectorAll(
    '.order-info-delete-item',
  );
  orderItemDeleteButtons.forEach((deleteButton) => {
    deleteButton.addEventListener('click', (event) =>
      deleteItem(event, deleteButton),
    );
  });
}

function updateSummary(sum, sumAfterDiscount, count) {
  const orderAmountContainer = document.getElementById('order-info-amount');
  const orderCountContainer = document.getElementById('order-count');
  const sumContainerInOrderDetails = document.querySelector(
    '.order-info-summary #sum',
  );
  const fullSumContainerInOrderDetails = document.querySelector(
    '.order-info-summary #sum-full',
  );

  orderAmountContainer.innerText = sum;
  orderCountContainer.innerText = count;

  if (sumContainerInOrderDetails) {
    sumContainerInOrderDetails.innerText = sum;
  }

  if (fullSumContainerInOrderDetails) {
    fullSumContainerInOrderDetails.innerText = sumAfterDiscount;
  }

  const isDiscount = sum !== sumAfterDiscount;
  if (sumContainerInOrderDetails) {
    sumContainerInOrderDetails.classesName = isDiscount
      ? 'sum-discounted'
      : 'sum';
  }

  window.orderSummary = {
    sum: sum,
    sumAfterDiscount: sumAfterDiscount,
  };
}

function deleteItem(event, deleteButton) {
  const $ = jQuery;

  event.preventDefault();

  const orderItemDeleteButtons = document.querySelectorAll(
    '.order-info-delete-item',
  );
  const id = Array.from(orderItemDeleteButtons).indexOf(deleteButton);

  const url = getPreparedApiUrl('order/deleteItem');
  url.searchParams.set('id', String(id));
  fetch(url, {
    method: 'POST',
    credentials: 'include',
  })
    .then((response) => response.json())
    .then(({ sum, sumAfterDiscount, count }) => {
      $(deleteButton)
        .parent()
        .fadeOut('fast', function () {
          $(this).remove();
          updateSummary(sum, sumAfterDiscount, count);
        });

      if (count <= 0) {
        document.querySelector('.order-info-area .menu-wrapper').remove();
      }
    });
}
