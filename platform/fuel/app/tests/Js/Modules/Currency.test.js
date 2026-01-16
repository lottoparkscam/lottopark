import {
    generatePayNowButtonWithAmounts
} from '../../../../../../resources/lotto-platform/js/modules/Currency.js';

const generatePayNowButtonWithAmountsInvalidDataProvider = [
    {
        name: 'generatePayNowButtonWithAmounts with "empty string" amount value',
        convertedAmount: '',
    },
    {
        name: 'generatePayNowButtonWithAmounts with "empty object" amount value',
        convertedAmount: {},
    },
    {
        name: 'generatePayNowButtonWithAmounts with "NaN" amount value',
        convertedAmount: NaN,
    },
    {
        name: 'generatePayNowButtonWithAmounts with "0" amount value',
        convertedAmount: 0,
    },
    {
        name: 'generatePayNowButtonWithAmounts with "false" amount value',
        convertedAmount: false,
    },
    {
        name: 'generatePayNowButtonWithAmounts with "undefined" amount value',
        convertedAmount: undefined,
    },
];

test('generatePayNowButtonWithAmounts the same currency', () => {
    mockPaymentForm(50, 'EUR');

    generatePayNowButtonWithAmounts(50, 'EUR');

    const payNowButton = document
        .getElementById('paymentSubmit')
        .querySelector('span');

    expect(payNowButton.textContent).toEqual('€50.00');
    expect(payNowButton.classList.contains('hidden-normal')).toBe(false);
});

test('generatePayNowButtonWithAmounts different currency', () => {
    mockPaymentForm(50, 'EUR');

    generatePayNowButtonWithAmounts(59.40, 'USD');

    const payNowButton = document
        .getElementById('paymentSubmit')
        .querySelector('span');

    expect(payNowButton.textContent).toEqual('€50.00 (US$59.40)');
    expect(payNowButton.classList.contains('hidden-normal')).toBe(false);
});

generatePayNowButtonWithAmountsInvalidDataProvider.forEach(data => {
    test(data.name, () => {
        mockPaymentForm(50, 'EUR');

        generatePayNowButtonWithAmounts(data.convertedAmount, 'EUR');

        const payNowButton = document
            .getElementById('paymentSubmit')
            .querySelector('span');

        expect(payNowButton.textContent).toEqual('€50.00');
        expect(payNowButton.classList.contains('hidden-normal')).toBe(true);
    });
});

let mockPaymentForm = (amount, currency) => {
    let htmlElement = document.querySelector('html');
    let paymentForm = document.createElement('form');
    let paymentAmountInput = document.createElement('input');
    let paymentButton = document.createElement('button');
    let paymentButtonSpan = document.createElement('span');

    htmlElement.setAttribute('lang', 'en-GB');

    paymentForm.classList.add('payment-form');
    paymentForm.setAttribute('data-currency', currency);

    paymentAmountInput.setAttribute('id', 'paymentAmount');
    paymentAmountInput.setAttribute('type', 'hidden');
    paymentAmountInput.setAttribute('name', 'payment[amount]');
    paymentAmountInput.setAttribute('value', amount);

    paymentButton.setAttribute('id', 'paymentSubmit');
    paymentButton.appendChild(paymentButtonSpan);

    paymentForm.appendChild(paymentAmountInput);
    paymentForm.appendChild(paymentButton);

    document.body.appendChild(paymentForm);
}
