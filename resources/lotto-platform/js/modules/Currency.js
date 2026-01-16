import { getPreparedApiUrl } from '../../../js/Helpers/UrlHelper';

export function fetchConvertedCurrencyData() {
  const paymentAmountInGateway = document.getElementById(
    'paymentAmountInGateway',
  );
  const paymentCurrencyInGateway = document.getElementById(
    'paymentCurrencyInGateway',
  );
  const userSelectedCurrency = document.getElementById('userSelectedCurrency');

  if (
    !paymentAmountInGateway ||
    !paymentCurrencyInGateway ||
    !userSelectedCurrency
  ) {
    return;
  }

  const preparedApiUrl = getPreparedApiUrl('currency/converter');
  preparedApiUrl.searchParams.set('amount', paymentAmountInGateway.value);
  preparedApiUrl.searchParams.set('currency', paymentCurrencyInGateway.value);
  preparedApiUrl.searchParams.set(
    'convertToCurrency',
    userSelectedCurrency.value,
  );

  fetch(preparedApiUrl, { credentials: 'include' })
    .then((response) => response.json())
    .then(({ amount, currency }) => {
      generatePayNowButtonWithAmounts(amount, currency);
    })
    .catch(() => {
      // no updates should be done on error
    });
}

/**
 * Generate values for pay now button when on deposit or lottery order page
 * Two cases that can happen:
 * 1. "Pay US$26.00" - selected gateway currency is same as user currency
 * 2. "Pay US$26.00 (€24.16)" - selected gateway currency is different to user's currency
 * Format is: Pay user_currency (gateway_currency), where gateway currency is the one for redirection and payment
 */
export function generatePayNowButtonWithAmounts(
  convertedAmount,
  convertedCurrency,
) {
  const payNowButton = document
    .getElementById('paymentSubmit')
    .querySelector('span');
  let userAccountCurrency = document
    .getElementsByClassName('payment-form')[0]
    .getAttribute('data-currency');
  let amountInUserCurrency = document.getElementById('paymentAmount').value;
  let gatewayCurrencyText = '';
  let lang = document.querySelector('html').getAttribute('lang') || 'en-GB';
  let currencyFormatter = new Intl.NumberFormat(lang, {
    style: 'currency',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    currency: userAccountCurrency || 'USD',
  });

  convertedAmount = parseFloat(convertedAmount) || 0;

  let formattedPayNowText = currencyFormatter.format(amountInUserCurrency);

  if (convertedAmount > 0) {
    payNowButton.classList.remove('hidden-normal');
    if (convertedCurrency !== userAccountCurrency) {
      gatewayCurrencyText = formatValue(
        userAccountCurrency,
        convertedAmount,
        convertedCurrency,
      );
      formattedPayNowText += ' (' + gatewayCurrencyText + ')';
    }
  } else {
    payNowButton.classList.add('hidden-normal');
  }

  payNowButton.textContent = formattedPayNowText;
}

function formatValue(userCurrency, formatToAmount, formatToCurrency) {
  let formattedCurrencyText = '';
  let lang = document.querySelector('html').getAttribute('lang') || 'en-GB';

  if (userCurrency !== formatToCurrency) {
    if (isNaN(formatToAmount)) {
      formatToAmount = 0;
    }

    let currencyFormatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      currency: formatToCurrency || 'USD',
    });
    let formatGateway = currencyFormatter.format(formatToAmount);

    formattedCurrencyText = formatGateway;
  }

  return formattedCurrencyText;
}
