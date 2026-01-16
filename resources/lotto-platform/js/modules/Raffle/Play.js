export function initPlayPage() {
  const $ = jQuery;

  // lottery king widget
  $('[href^="#prize"]').click(() => {
    $('.pool-article').toggle();
  });

  const raffleConfig = $('#raffle-config').data('raffle');
  if (!raffleConfig) {
    return;
  }

  const formatCurrency = function (value, currencyCode) {
    let lang = 'en-GB';
    return new Intl.NumberFormat(lang, {
      style: 'currency',
      currency: currencyCode,
    }).format(value);
  };

  const ticket = {
    observers: [],
    mode: 'all', // 'all', 'even', 'odd'

    linePrice: raffleConfig.ticket_price,
    currency: raffleConfig.currency_code,
    totalPrice: 0,

    userLinePrice: raffleConfig.user_line_price,
    userCurrency: raffleConfig.user_currency_code,
    userTotalPrice: 0,

    selectedNumbers: [],
    maxNumbersCount: raffleConfig.max_bet,
    allNumbersCount: raffleConfig.max_lines_per_draw + 1,

    clear() {
      let selectedNumbers = [...this.selectedNumbers];
      selectedNumbers.forEach((number) => this.deleteNumber(number));
    },

    pickRandomNumbers() {
      let freeNumbers = [];

      if (this.mode === 'all') {
        freeNumbers = this.freeNumbers();
      }
      if (this.mode === 'odd') {
        freeNumbers = this.oddNumbers();
      }
      if (this.mode === 'even') {
        freeNumbers = this.evenNumbers();
      }
      const number =
        freeNumbers[Math.floor(Math.random() * freeNumbers.length)];
      this.addNumber(number);
      this.notify('randomPicked', number);
    },

    freeNumbers() {
      const range = [...Array(this.allNumbersCount).keys()];
      delete range[0];
      const occupiedNumbers = [
        ...raffleConfig.taken_numbers,
        ...this.selectedNumbers,
      ];
      return range.filter((number) => !occupiedNumbers.includes(number));
    },

    evenNumbers() {
      const freeNumbers = [...this.freeNumbers()];
      return freeNumbers.filter((number) => number % 2 === 0);
    },

    oddNumbers() {
      const freeNumbers = [...this.freeNumbers()];
      return freeNumbers.filter((number) => number % 2 !== 0);
    },

    addNumber(number) {
      if (this.numberExists(number)) {
        return false;
      }
      if (this.selectedNumbers.length >= this.maxNumbersCount) {
        this.notify('limitExceeded');
        return false;
      }
      this.selectedNumbers.push(number);
      this.recalculatePrice();
      this.notify('add', number);
    },
    deleteNumber(number) {
      if (!this.numberExists(number)) {
        return false;
      }
      const index = this.selectedNumbers.indexOf(number);
      this.selectedNumbers.splice(index, 1);
      this.recalculatePrice();
      this.notify('delete', number);
      if (this.selectedNumbers.length < this.maxNumbersCount) {
        this.notify('limitOk');
      }
    },

    numberExists(number) {
      return this.selectedNumbers.filter((f) => f === number).length !== 0;
    },

    recalculatePrice() {
      this.userTotalPrice = this.userLinePrice * this.selectedNumbers.length;
      this.notify('ticketChanged');
    },

    subscribe(callback) {
      this.observers.push(callback);
    },
    notify(event, number = null) {
      this.observers.forEach((observer) =>
        observer({
          event,
          number,
          ticket: this,
        }),
      );
    },
  };

  // alert about limit
  ticket.subscribe((event) => {
    if (event.event === 'limitOk') {
      $('#alert-max-numbers-count').addClass('hidden');
      $('.raffle-ticket-add-random').attr('disabled', false);
    }
    if (event.event === 'limitExceeded') {
      $('#alert-max-numbers-count').removeClass('hidden');
      $('.raffle-ticket-add-random').attr('disabled', true);
    }
  });

  // disable / enable buttons
  ticket.subscribe((event) => {
    $('.js-enabled-on-tickets').attr(
      'disabled',
      event.ticket.selectedNumbers.length === 0,
    );
    $('.raffle-order-ticket').attr(
      'disabled',
      !raffleConfig.is_logged_in || event.ticket.selectedNumbers.length === 0,
    );
  });

  // random picked
  ticket.subscribe((event) => {
    if (event.event === 'randomPicked') {
      const lineHeight = 24;
      $('.widget-ticket-numbers')[0].scrollTop =
        (event.number / 10) * lineHeight;
    }
  });

  // add color
  ticket.subscribe((event) => {
    if (event.event === 'add') {
      $(`[data-number="${event.number}"]`).addClass('raffle-picked-number');
    }
    if (event.event === 'delete') {
      $(`[data-number="${event.number}"]`).removeClass('raffle-picked-number');
    }
  });

  // widget data
  ticket.subscribe((event) => {
    if (event.event === 'ticketChanged') {
      if (!raffleConfig.is_logged_in) {
        $('#alert-must-be-logged_in').removeClass('hidden');
      }
      $('.raffle-chosen-number-count > span').text(
        event.ticket.selectedNumbers.length,
      );
      $('.raffle-total-value').text(
        formatCurrency(
          event.ticket.userTotalPrice,
          raffleConfig.user_currency_code,
        ),
      );

      // ticket summary tickets numbers
      $('.widget-chosen-tickets-container').empty();

      const formatNumber = function (number) {
        let numberFormat = '0000';
        return (
          numberFormat.substring(
            0,
            numberFormat.length - number.toString().length,
          ) + number.toString()
        );
      };

      ticket.selectedNumbers.forEach((number) => {
        $('.widget-chosen-tickets-container').append(
          `<div class="widget-chosen-ticket">${formatNumber(number)}</div>`,
        );
      });

      $('.js-enabled-on-free-tickets').attr(
        'disabled',
        event.ticket.freeNumbers().length === 0,
      );

      // tabs count update
      const freeNumbers = event.ticket.freeNumbers();
      const even = event.ticket.evenNumbers();
      const odd = event.ticket.oddNumbers();

      $('.all-numbers span').text(freeNumbers.length);
      $('.even-numbers span').text(even.length);
      $('.odds-numbers span').text(odd.length);
    }
  });

  $('.raffle-number.js-playable:not(.raffle-taken-number)').click((event) => {
    const number = $(event.target).data('number');
    if (!ticket.numberExists(number)) {
      ticket.addNumber(number);
    } else {
      ticket.deleteNumber(number);
    }
  });

  $('.raffle-ticket-clear-all').click(() => {
    ticket.clear();
  });

  $('.raffle-ticket-add-random').click(() => ticket.pickRandomNumbers());

  $('.raffle-order-ticket').click(() => {
    const form = document.createElement('form');
    document.body.appendChild(form);

    let token = document.createElement('input');
    token.type = 'hidden';
    token.name = raffleConfig.n;
    token.value = raffleConfig.t;
    form.appendChild(token);

    form.method = 'post';
    form.action = raffleConfig.purchase_url;
    ticket.selectedNumbers.forEach((number) => {
      let input = document.createElement('input');
      input.type = 'hidden';
      input.name = `numbers[]`;
      input.value = number;
      form.appendChild(input);
    });
    form.submit();
  });

  // triggers add picked numbers to stock
  // when user selected already used numbers and is redirected
  $('.raffle-picked-number').click();

  $('#js-availability-switcher-checkbox').click((event) => {
    if ($(event.target).is(':checked')) {
      $('.raffle-taken-number').addClass('hidden');
    } else {
      $('.raffle-taken-number').removeClass('hidden');
    }
  });

  $('.widget-raffle-filter-button').click(function () {
    $('.widget-raffle-filter-button').removeClass('active');
    $(this).addClass('active');
  });

  const availableOnly = () =>
    $('#js-availability-switcher-checkbox').prop('checked');

  $('.all-numbers').click(function () {
    if (availableOnly()) {
      $('.raffle-number').not('.raffle-taken-number').removeClass('hidden');
    } else {
      $('.raffle-number').removeClass('hidden');
    }
    ticket.mode = 'all';
  });
  $('.odds-numbers').click(function () {
    if (availableOnly()) {
      $('.raffle-number:even')
        .not('.raffle-taken-number')
        .removeClass('hidden');
      $('.raffle-number:odd').not('.raffle-taken-number').addClass('hidden');
    } else {
      $('.raffle-number:even').removeClass('hidden');
      $('.raffle-number:odd').addClass('hidden');
    }
    ticket.mode = 'odd';
  });
  $('.even-numbers').click(function () {
    if (availableOnly()) {
      $('.raffle-number:even').not('.raffle-taken-number').addClass('hidden');
      $('.raffle-number:odd').not('.raffle-taken-number').removeClass('hidden');
    } else {
      $('.raffle-number:even').addClass('hidden');
      $('.raffle-number:odd').removeClass('hidden');
    }
    ticket.mode = 'even';
  });

  $('.raffle-order-ticket').click(() => $(this).prop('disabled', true));

  ticket.recalculatePrice();
  if ($('#js-availability-switcher-checkbox').is(':checked')) {
    $('.raffle-taken-number').addClass('hidden');
  }
}
