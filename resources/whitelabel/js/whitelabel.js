$(function () {
  const SKRILL = 2;
  const NETELLER = 3;
  const CUBITS = 4;
  const TPAY = 5;
  const SOFORT = 6;
  const ENTERCASH = 7;
  const PIASTRIX = 8;
  const ECOPAYZ = 9;
  const PAYSAFECARD = 10;
  const ENTROPAY = 11;
  const COIN_PAYMENTS = 12;
  const ASIA_PAYMENT_GATEWAY = 13;
  const PAYPAL = 14;
  const BITBAYPAY = 15;
  const DUSUPAY = 16;
  const EASY_PAYMENT_GATEWAY = 17; // 20.03.2019 11:11 Vordis TODO: shared with admin.js
  const APCOPAY_CC = 18;
  const ASTRO_PAY = 19;
  const STRIPE = 20;
  const FLUTTERWAVE = 21;
  const FLUTTERWAVE_AFRICA = 22;
  const ASTRO_PAY_CARD = 23;
  const CREDIT_CARD_SANDBOX = 24;
  const TRUEVOCC = 25;
  const VISANET = 26;
  const CUSTOM = 27;
  const BHARTIPAY = 28;
  const SEPA = 29;
  const JETON = 30;
  const TAMSPAY = 31;
  const ASTRO = 32;
  const TRUSTPAYMENTS = 33;
  const PAYOP = 34;
  const WONDERLANDPAY = 35;
  const PICKSELL = 36;
  const PSPGATE = 37;
  const ZEN = 38;
  const ONRAMPER = 39;
  const NOWPAYMENTS = 40;
  const GCASH = 42;
  const LENCO = 43;

  const PAYMENT_AFF_BANK = 1;
  const PAYMENT_AFF_SKRILL = 2;
  const PAYMENT_AFF_NETELLER = 3;
  const PAYMENT_AFF_BTC = 4;
  const PAYMENT_AFF_DEBIT_CARD = 5;
  const PAYMENT_AFF_PAYPAL = 6;

  $('.text-info').show();

  if (
    !(
      isNaN($('#inputSiteCurrency').val()) ||
      isNaN($('#defaultsystemcurrencyid').val())
    )
  ) {
    if ($('#inputSiteCurrency').val() !== $('#defaultsystemcurrencyid').val()) {
      $('.text-info').show();
    } else {
      $('.text-info').hide();
    }
  }

  $('#confirmModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var modal = $(this);
    modal.find('.modal-body').text(button.data('confirm'));
    modal.find('#confirmOK').attr('href', button.data('href'));
  });

  let generateRandomPassword = (length = 12) => {
    let randomPassword = '';
    const CHARACTERS =
      'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for (let i = 0; i < length; i++) {
      randomPassword += CHARACTERS.charAt(
        Math.floor(Math.random() * CHARACTERS.length),
      );
    }
    return randomPassword;
  };

  if ($('#generatePassword').length) {
    $('#generatePassword').click(function () {
      let newPassword = generateRandomPassword();
      $('#inputPassword').val(newPassword);
    });
  }

  $('input.clear').val('');

  $('.table-sort .tablesorter-header').click(function () {
    document.location.href = $(this).data('href');
  });

  $('.datepicker').datepicker({ autoclose: true, weekStart: 1 });

  $('[data-toggle="tooltip"]').tooltip({ html: true });
  $('[data-toggle="popover"]').popover({ html: true });

  $('ul.pagination a').click(function (e) {
    if ($(this).attr('href') == '#') {
      e.preventDefault();
      $(this).blur();
    }
  });

  if ($('#showPaymentLogotype').is(':checked')) {
    $('#inputCustomLogotype').prop('disabled', false);
  } else {
    $('#inputCustomLogotype').prop('disabled', true);
  }

  $('#showPaymentLogotype').on('click', function () {
    if ($(this).is(':checked')) {
      $('#inputCustomLogotype').prop('disabled', false);
    } else {
      $('#inputCustomLogotype').prop('disabled', true);
      $('#inputCustomLogotype').val('');
    }
  });

  // Very simple feature to make easy enter new login data
  $('#inputRegisterEmail').on('keyup', function () {
    var current_value = $(this).val();

    // If found digit or at sign on the begining it will be removed
    if (current_value.search(/^[\d|@]/i) === 0) {
      $(this).val('');
      return;
    }

    var register_input_entered = $('#inputRegisterLogin').data('entered');
    // The field was previuosly entered - do not change it
    if (register_input_entered === 2) {
      return;
    }

    // If found at sign on it will stop process
    if (current_value.search(/@/i) !== -1) {
      $('#inputRegisterLogin').data('entered', 1);
      return;
    } else if (current_value.length === 0) {
      $('#inputRegisterLogin').data('entered', 0);
    } else {
      $('#inputRegisterLogin').data('entered', 0);
    }

    // Once again check value for data
    register_input_entered = $('#inputRegisterLogin').data('entered');
    if (register_input_entered === 1) {
      return;
    }

    $('#inputRegisterLogin').val(current_value);
  });

  $('#inputRegisterLogin').on('keyup', function () {
    var current_value = $(this).val();

    // If found digit on the begining it will be removed
    if (current_value.search(/^[\d|@]/i) === 0) {
      $(this).val('');
      return;
    }

    var current_value_length = $(this).val().length;
    var entered = 0;

    if (current_value_length > 0) {
      entered = 2; // Entered first in that field instead of email
    } else {
      entered = 0;
    }
    $(this).data('entered', entered);
  });

  $('#registerProcessing').modal('hide');
  $('.form-signup').on('submit', function () {
    $('#submitAffRegister').prop('disabled', true);
    $('#registerProcessing').modal('show');
  });

  $('#inputPaymentCurrency').on('change', function () {
    var selected_code = $(this)
      .find('option')
      .eq(this.selectedIndex)
      .data('code');
    $('#minPurchaseCurrencyCode').text(selected_code);
    $('#inputMinPurchaseByCurrency').val('0.00');
  });

  $('#inputPaymentMethod')
    .change(function () {
      var inputPaymentMethod = parseInt($(this).val());

      $('.payment-details').addClass('hidden');

      switch (
        inputPaymentMethod // TODO: {Vordis 2019-05-17 14:48:18} shared with admin.js should be in parent class using objects or mixin
      ) {
        case SKRILL:
          $('#paymentDetailsSkrill').removeClass('hidden');
          break;
        case NETELLER:
          $('#paymentDetailsNeteller').removeClass('hidden');
          break;
        case CUBITS:
          $('#paymentDetailsCubits').removeClass('hidden');
          break;
        case TPAY:
          $('#paymentDetailsTpay').removeClass('hidden');
          break;
        case SOFORT:
          $('#paymentDetailsSofort').removeClass('hidden');
          break;
        case ENTERCASH:
          $('#paymentDetailsEntercash').removeClass('hidden');
          break;
        case PIASTRIX:
          $('#paymentDetailsPiastrix').removeClass('hidden');
          break;
        case ECOPAYZ:
          $('#paymentDetailsEcoPayz').removeClass('hidden');
          break;
        case PAYSAFECARD:
          $('#paymentDetailsPaysafecard').removeClass('hidden');
          break;
        case ENTROPAY:
          $('#paymentDetailsEntropay').removeClass('hidden');
          break;
        case COIN_PAYMENTS:
          $('#paymentDetailsCoinPayments').removeClass('hidden');
          break;
        case ASIA_PAYMENT_GATEWAY:
          $('#paymentDetailsAsiapaymentgateway').removeClass('hidden');
          break;
        case PAYPAL:
          $('#paymentDetailsPaypal').removeClass('hidden');
          break;
        case BITBAYPAY:
          $('#paymentDetailsBitBayPay').removeClass('hidden');
          break;
        case DUSUPAY:
          $('#paymentDetailsDusuPay').removeClass('hidden');
          break;
        case EASY_PAYMENT_GATEWAY:
          $('#paymentDetailsEasyPaymentGateway').removeClass('hidden');
          break;
        case APCOPAY_CC:
          $('#paymentDetailsApcopayCC').removeClass('hidden');
          break;
        case ASTRO_PAY:
          $('#paymentDetailsAstroPay').removeClass('hidden');
          break;
        case STRIPE:
          $('#paymentDetailsStripe').removeClass('hidden');
          break;
        case FLUTTERWAVE:
          $('#paymentDetailsFlutterwave').removeClass('hidden');
          break;
        case FLUTTERWAVE_AFRICA:
          $('#paymentDetailsFlutterwaveAfrica').removeClass('hidden');
          break;
        case ASTRO_PAY_CARD:
          $('#paymentDetailsAstroPayCard').removeClass('hidden');
          break;
        case CREDIT_CARD_SANDBOX:
          $('#paymentDetailsCreditCardSandbox').removeClass('hidden');
          break;
        case TRUEVOCC:
          $('#paymentDetailsTruevocc').removeClass('hidden');
          break;
        case VISANET:
          $('#paymentDetailsVisaNet').removeClass('hidden');
          break;
        case CUSTOM:
          $('#paymentDetailsCustom').removeClass('hidden');
          break;
        case BHARTIPAY:
          $('#paymentDetailsBhartipay').removeClass('hidden');
          break;
        case SEPA:
          $('#paymentDetailsSepa').removeClass('hidden');
          break;
        case JETON:
          $('#paymentDetailsJeton').removeClass('hidden');
          break;
        case TAMSPAY:
          $('#paymentDetailsTamspay').removeClass('hidden');
          break;
        case ASTRO:
          $('#paymentDetailsAstro').removeClass('hidden');
          break;
        case TRUSTPAYMENTS:
          $('#paymentDetailsTrustpayments').removeClass('hidden');
          break;
        case PAYOP:
          $('#paymentDetailsPayOp').removeClass('hidden');
          break;
        case WONDERLANDPAY:
          $('#paymentDetailsWonderlandPay').removeClass('hidden');
          break;
        case PICKSELL:
          $('#paymentDetailsPicksell').removeClass('hidden');
          break;
        case PSPGATE:
          $('#paymentDetailsPspGate').removeClass('hidden');
          break;
        case ZEN:
          $('#paymentDetailsZen').removeClass('hidden');
          break;
        case ONRAMPER:
          $('#paymentDetailsOnramper').removeClass('hidden');
          break;
        case NOWPAYMENTS:
          $('#paymentDetailsNowPayments').removeClass('hidden');
          break;
        case GCASH:
          $('#paymentDetailsGcash').removeClass('hidden');
          break;
        case LENCO:
          $('#paymentDetailsLenco').removeClass('hidden');
          break;
        default:
          break;
      }
    })
    .change();

  $('#inputAffPaymentMethod')
    .change(function () {
      var inputAffPaymentMethod = parseInt($(this).val());

      $('.payment-details').addClass('hidden');

      switch (inputAffPaymentMethod) {
        case PAYMENT_AFF_BANK: // Bank Account - this was commented as Skrill, but I think it was wrong
          $('#paymentDetailsBankAccount').removeClass('hidden');
          break;
        case PAYMENT_AFF_SKRILL:
          $('#paymentDetailsSkrill').removeClass('hidden');
          break;
        case PAYMENT_AFF_NETELLER:
          $('#paymentDetailsNeteller').removeClass('hidden');
          break;
        case PAYMENT_AFF_BTC:
          $('#paymentDetailsBTC').removeClass('hidden');
          break;
        case PAYMENT_AFF_PAYPAL:
          $('#paymentDetailsPaypal').removeClass('hidden');
          break;
        default:
          break;
      }
    })
    .change();

  $('#inputCCMethod')
    .change(function () {
      $('.payment-details').addClass('hidden');
      if ($(this).val() == 1) {
        // emerchantpay
        $('#paymentDetailsEMerchantPay').removeClass('hidden');
      }
    })
    .change();

  $('#inputProvider')
    .change(function () {
      $('#minlines-help').addClass('hidden');
      if (
        $(this).find('option').eq(this.selectedIndex).data('multiplier') > 0
      ) {
        $('#minlines-help')
          .find('span')
          .text(
            $(this).find('option').eq(this.selectedIndex).data('multiplier'),
          );
        $('#minlines-help').removeClass('hidden');
      }
    })
    .change();

  $('#inputCountry').change(function (e) {
    if ($(this).val() != '' && $('#inputPhone').val().length == 0) {
      var prefixes = $(
        '#inputPrefix option[data-territory="' + $(this).val() + '"]',
      );
      if (prefixes.length) {
        $('#inputPrefix').val(prefixes.slice(0, 1).val());
      }
    }

    if ($(this).val() != '') {
      $('#inputState').prop('disabled', false);
      $('#inputState').children().remove();
      $('#allRegions')
        .children()
        .each(function (index) {
          if (
            $(this).data('country') == $('#inputCountry').val() ||
            index == 0
          ) {
            $('#inputState').append($(this).clone());
          }
        });

      if ($('#inputState').children().length <= 1) {
        $('#inputState').prop('disabled', true);
      }
    } else {
      $('#inputState').children().remove();
      $('#inputState').append($('#allRegions').children().eq(0).clone());
      $('#inputState').prop('disabled', true);
    }
  });

  if ($('.recalculate-price').length) {
    $('.recalculate-price').change(recalculatePrice).eq(0).change();
    $('.recalculate-price').on('keyup', recalculatePrice);
  }

  // Reset all filters on Whitelabel/users page
  $('#filter-form-whitelabel').on('click', function (e) {
    e.preventDefault();

    $('.filterSelect').prop('selectedIndex', 0);
    $('.filterInput').val('');

    $(':submit').click();
  });

  //    $('#showHideCurrencyTab').on('click', function() {
  //        var datahide = $(this).data('hidetext');
  //        var datashow = $(this).data('showtext');
  //        var datacurrent = $(this).data('current');
  //
  //        if ($.trim(datacurrent) === datahide) {
  //            $(this).text(datashow);
  //            $(this).data('current', datashow);
  //        } else {
  //            $(this).text(datahide);
  //            $(this).data('current', datahide);
  //        }
  //
  //        $('#currenciesTab').toggle('slow');
  //    });
  //
  //    $('#showHideCountryCurrencyTab').on('click', function() {
  //        var datahide = $(this).data('hidetext');
  //        var datashow = $(this).data('showtext');
  //        var datacurrent = $(this).data('current');
  //
  //        if ($.trim(datacurrent) === datahide) {
  //            $(this).text(datashow);
  //            $(this).data('current', datashow);
  //        } else {
  //            $(this).text(datahide);
  //            $(this).data('current', datahide);
  //        }
  //
  //        $('#countriesCurrenciesTab').toggle('slow');
  //    });

  // Function to recalculate values in deposit boxes based on Euro
  // default values are about 20 for first box, 50 for second box, 100 for third box
  // Note! In fact those default values could be changed in the system
  $('#inputSiteCurrency').on('change', function () {
    var curr_value = parseInt($(this).val());
    var def_system_curr_id = parseInt($('#defaultsystemcurrencyid').val());
    var selected_code = $(this)
      .find('option')
      .eq(this.selectedIndex)
      .data('code');
    var currency_rate = $(this)
      .find('option')
      .eq(this.selectedIndex)
      .data('rate');
    var full_converted_rate = parseFloat(
      $(this).find('option').eq(this.selectedIndex).data('convertedmultiplier'),
    );
    var rounded_converted_rate = parseFloat(
      $(this).find('option').eq(this.selectedIndex).data('roundedingateway'),
    );
    var gateway_currency_rate = parseFloat($('#gatewaycurrencyrate').val());

    if (curr_value !== def_system_curr_id) {
      $('.text-info').show();
    } else {
      $('.text-info').hide();
    }

    $('.deposit-currency').text(selected_code);

    $('#currencyrate').val(currency_rate);
    $('#convertedmultiplier').val(full_converted_rate);

    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputFirstBoxDeposit',
      'firstBoxDepositInEuro',
    );
    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputSecondBoxDeposit',
      'secondBoxDepositInEuro',
    );
    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputThirdBoxDeposit',
      'thirdBoxDepositInEuro',
    );

    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputMinPurchaseAmount',
      'inputMinPurchaseAmountInEuro',
    );
    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputMindepositAmount',
      'inputMindepositAmountInEuro',
    );
    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputMinWithdrawal',
      'inputMinWithdrawalInEuro',
    );
    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputMaxOrderAmount',
      'inputMaxOrderAmountInEuro',
    );
    get_converted_value(
      currency_rate,
      full_converted_rate,
      rounded_converted_rate,
      gateway_currency_rate,
      'inputMaxDepositAmount',
      'inputMaxDepositAmountInEuro',
    );
  });

  function get_converted_value(
    currency_rate,
    full_converted_rate,
    rounded_converted_rate,
    gateway_currency_rate,
    field_name,
    field_name_euro,
  ) {
    var box_price = 0.0;
    var in_gateway_currency_result = 0.0;

    var gateway_currency_source = $('#' + field_name).data(
      'defaultmultiingateway',
    );
    var value_in_gateway_currency = parseInt(gateway_currency_source);

    if (value_in_gateway_currency > 100) {
      value_in_gateway_currency =
        Math.ceil(value_in_gateway_currency / 10) * 10;
    }

    if (currency_rate !== gateway_currency_rate) {
      box_price = value_in_gateway_currency * rounded_converted_rate;

      if (box_price > 0) {
        var length_box_price = parseInt(box_price.toString().length) - 1;
        var division_value = 0;

        if (rounded_converted_rate < 1) {
          length_box_price = 1;
        }

        division_value = Math.pow(10, length_box_price);
        box_price = Math.ceil(box_price / division_value) * division_value;
      }

      //box_price = in_gateway_currency * full_converted_rate;
      box_price = Math.round(box_price * 100) / 100;
      in_gateway_currency_result = box_price / full_converted_rate;
      in_gateway_currency_result =
        Math.round(in_gateway_currency_result * 100) / 100;
    } else {
      box_price = value_in_gateway_currency;
      in_gateway_currency_result = value_in_gateway_currency;
    }

    box_price = format_currency_number(box_price);
    in_gateway_currency_result = format_currency_number(
      in_gateway_currency_result,
    );

    $('#' + field_name)
      .val(box_price)
      .data('oldvalue', box_price);

    $('#' + field_name_euro).text(in_gateway_currency_result);
  }

  $('.special-box').on('blur', function () {
    var current_val = $(this).val();
    var old_val = $(this).data('oldvalue');
    var span_box_id = $(this).data('spanboxid');
    var nan_text = $(this).data('nantext');

    var converted_multiplier = parseFloat($('#convertedmultiplier').val());
    var currency_rate = parseFloat($('#currencyrate').val());
    var gateway_currency_rate = parseFloat($('#gatewaycurrencyrate').val());

    var result_val = 0.0;

    if (isNaN(current_val)) {
      var modal = $('#infoModal');
      modal.find('.modal-body').text(nan_text);
      modal.modal('show');
      $(this).val(old_val).focus();
      return;
    }

    current_val = format_currency_number(current_val);
    $(this).val(current_val);
    current_val = parseFloat(current_val);

    if (currency_rate !== gateway_currency_rate) {
      result_val = parseFloat(current_val / converted_multiplier);
      result_val = Math.round(result_val * 100) / 100;
    } else {
      result_val = current_val;
    }

    result_val = format_currency_number(result_val);

    old_val = current_val;
    old_val = format_currency_number(old_val);

    $('#' + span_box_id).text(result_val);
    $(this).data('oldvalue', old_val);
  });

  function format_currency_number(value) {
    var pointer_pos = value.toString().indexOf('.');

    if (pointer_pos < 0) {
      value += '.00';
      pointer_pos = value.toString().indexOf('.');
    }
    if (pointer_pos === value.length - 1) {
      value += '00';
      pointer_pos = value.toString().indexOf('.');
    }
    if (pointer_pos === value.toString().length - 2) {
      value += '0';
      pointer_pos = value.toString().indexOf('.');
    }
    if (pointer_pos === 0) {
      value = '0' + value;
    }

    return value;
  }

  function recalculatePrice() {
    var model = parseInt($('#inputModel').val());
    var ticket_price = parseFloat($('#calculatedPrice').data('ticketprice'));
    var ticket_fee = parseFloat($('#calculatedPrice').data('ticketfee'));
    var wl_margin = parseFloat($('#calculatedPrice').data('margin'));
    var income = parseFloat($('#inputIncome').val());
    var income_type = parseInt($('#inputIncomeType').val());
    var insured_tiers = parseFloat($('#inputInsuredTiers').val());
    var volume = parseInt($('#inputVolume').val());
    var jackpot = parseFloat($('#inputJackpot').val());
    var currency = $('#calculatedPrice').data('currency');
    var site_currency = $('#calculatedPrice').data('sitecurrency');
    var lottery_id = $('#calculatedPrice').data('lottery');
    var usd_rate = parseFloat($('#calculatedPrice').data('rate'));
    var site_rate = parseFloat($('#calculatedPrice').data('siterate'));

    if ($('#inputModel').val() == '1') {
      $('.insurance-only').removeClass('hidden');
      $('.purchase-only').addClass('hidden');
    } else {
      $('.insurance-only').addClass('hidden');
      $('.purchase-only').removeClass('hidden');
    }

    if (income <= 0 || isNaN(income)) {
      return;
    }
    if (volume <= 0 || isNaN(volume)) {
      return;
    }

    var final_price = null;

    switch (model) {
      case 0:
        if (income_type == 0) {
          final_price = ticket_price + ticket_fee + income;
        } else {
          final_price = ticket_price + ticket_fee;
          income = (final_price * parseFloat(income)) / 100.0;
          final_price += income;
        }

        currency_price = final_price * (1.0 / usd_rate);
        currency_price = Math.round(currency_price * site_rate * 100) / 100;

        final_price = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(final_price);
        var ticket_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(ticket_price);
        var ticket_fee_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(ticket_fee);
        var income_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(income);
        currency_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: site_currency,
        }).format(currency_price);

        $('#calculatedPrice').text(final_price);
        $('#calculatedPriceSite').text(currency_price_fmt);
        $('#ticketPrice').text(ticket_price_fmt);
        $('#ticketFee').text(ticket_fee_fmt);
        $('#income').text(income_fmt);
        $('#propFinalPrice-container').addClass('hidden');
        $('#finalIncome-container').addClass('hidden');
        $('#winnings-container').addClass('hidden');
        $('#minPrice-container').addClass('hidden');
        $('#totalCost-container').addClass('hidden');

        break;
      case 1:
        if (jackpot < 0 || isNaN(jackpot)) {
          return;
        }

        $.post(
          '/ajax/insurance',
          {
            lottery: lottery_id,
            tiers: insured_tiers,
            volume: volume,
            jackpot: jackpot,
          },
          function (data) {
            if (data == '0') {
              alert('Unexpected error! Please contact us.');
            }

            data = JSON.parse(data);
            ins_ticket_price = parseFloat(data[0]);
            winnings_price = parseFloat(data[1]);

            if (income_type == 0) {
              final_price = ins_ticket_price + winnings_price + income;
            } else {
              final_price = ins_ticket_price + winnings_price;
              income = (final_price * parseFloat(income)) / 100.0;
              final_price += income;
            }

            if (final_price < ticket_price + ticket_fee) {
              prop_final_price = final_price;
              total_cost = ins_ticket_price + winnings_price;
              var prop_final_price_fmt = Intl.NumberFormat(
                $('html').attr('lang'),
                { style: 'currency', currency: currency },
              ).format(final_price);
              $('#propFinalPrice').text(prop_final_price_fmt);
              $('#propFinalPrice-container').removeClass('hidden');
              final_price = ticket_price + ticket_fee;
              var final_income = income + final_price - prop_final_price;
              var final_income_fmt = Intl.NumberFormat($('html').attr('lang'), {
                style: 'currency',
                currency: currency,
              }).format(final_income);
              $('#finalIncome').text(final_income_fmt);
              var min_price = ticket_price + ticket_fee;
              var min_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
                style: 'currency',
                currency: currency,
              }).format(min_price);
              $('#minPrice').text(min_price_fmt);
              $('#finalIncome-container').removeClass('hidden');
              $('#minPrice-container').removeClass('hidden');

              var total_cost_fmt = Intl.NumberFormat($('html').attr('lang'), {
                style: 'currency',
                currency: currency,
              }).format(total_cost);
              $('#totalCost').text(total_cost_fmt);
              $('#totalCost-container').removeClass('hidden');
            } else {
              $('#propFinalPrice-container').addClass('hidden');
              $('#finalIncome-container').addClass('hidden');
              $('#minPrice-container').addClass('hidden');
              $('#totalCost-container').addClass('hidden');
            }

            if (data[2] == 1) {
              var winnings_fmt = Intl.NumberFormat($('html').attr('lang'), {
                style: 'currency',
                currency: currency,
              }).format(data[1]);

              $('#winnings').text(winnings_fmt);
              $('#winnings-container').removeClass('hidden');
            } else {
              $('#winnings-container').addClass('hidden');
            }

            currency_price = final_price * (1.0 / usd_rate);
            currency_price = Math.round(currency_price * site_rate * 100) / 100;

            final_price = Intl.NumberFormat($('html').attr('lang'), {
              style: 'currency',
              currency: currency,
            }).format(final_price);
            var ticket_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
              style: 'currency',
              currency: currency,
            }).format(ins_ticket_price);
            var income_fmt = Intl.NumberFormat($('html').attr('lang'), {
              style: 'currency',
              currency: currency,
            }).format(income);
            var currency_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
              style: 'currency',
              currency: site_currency,
            }).format(currency_price);

            $('#calculatedPrice').text(final_price);
            $('#calculatedPriceSite').text(currency_price_fmt);
            $('#ticketPrice').text(ticket_price_fmt);
            $('#income').text(income_fmt);
          },
        );

        break;
      case 2:
        // TODO
        if (income_type == 0) {
          final_price = ticket_price + ticket_fee + income;
        } else {
          final_price = ticket_price + ticket_fee;
          income = (final_price * parseFloat(income)) / 100.0;
          final_price += income;
        }

        currency_price = final_price * (1.0 / usd_rate);
        currency_price = Math.round(currency_price * site_rate * 100) / 100;

        final_price = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(final_price);
        var ticket_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(ticket_price);
        var ticket_fee_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(ticket_fee);
        var income_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(income);
        currency_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: site_currency,
        }).format(currency_price);

        $('#calculatedPrice').text(final_price);
        $('#calculatedPriceSite').text(currency_price_fmt);
        $('#ticketPrice').text(ticket_price_fmt);
        $('#ticketFee').text(ticket_fee_fmt);
        $('#income').text(income_fmt);
        $('#propFinalPrice-container').addClass('hidden');
        $('#finalIncome-container').addClass('hidden');
        $('#winnings-container').addClass('hidden');
        $('#minPrice-container').addClass('hidden');
        $('#totalCost-container').addClass('hidden');
        break;
      case 3:
        if (income_type == 0) {
          final_price = income;
        } else {
          final_price = 0;
          income = (ticket_price * parseFloat(income)) / 100.0;
          final_price += income;
        }

        if (final_price < ticket_price + ticket_fee) {
          prop_final_price = final_price;
          var prop_final_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
            style: 'currency',
            currency: currency,
          }).format(final_price);
          $('#propFinalPrice').text(prop_final_price_fmt);
          $('#propFinalPrice-container').removeClass('hidden');
          final_price = ticket_price + ticket_fee;
          var final_income = income + final_price - prop_final_price;
          var final_income_fmt = Intl.NumberFormat($('html').attr('lang'), {
            style: 'currency',
            currency: currency,
          }).format(final_income);
          $('#finalIncome').text(final_income_fmt);
          $('#finalIncome-container').removeClass('hidden');
          $('#minPrice-container').removeClass('hidden');
        } else {
          $('#propFinalPrice-container').addClass('hidden');
          $('#finalIncome-container').addClass('hidden');
          $('#minPrice-container').addClass('hidden');
        }

        currency_price = final_price * (1.0 / usd_rate);
        currency_price = Math.round(currency_price * site_rate * 100) / 100;

        final_price = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(final_price);
        var ticket_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(0);
        var ticket_fee_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(0);
        var income_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: currency,
        }).format(income);
        var currency_price_fmt = Intl.NumberFormat($('html').attr('lang'), {
          style: 'currency',
          currency: site_currency,
        }).format(currency_price);

        $('#calculatedPrice').text(final_price);
        $('#calculatedPriceSite').text(currency_price_fmt);
        $('#ticketPrice').text(ticket_price_fmt);
        $('#ticketFee').text(ticket_fee_fmt);
        $('#income').text(income_fmt);
        $('#winnings-container').addClass('hidden');
        $('#totalCost-container').addClass('hidden');

        break;
    }

    if (site_currency == currency) {
      $('#priceSite-container').addClass('hidden');
    } else {
      $('#priceSite-container').removeClass('hidden');
    }
  }

  $('.banners-generator #link, #widget_js, #widget_div').on(
    'click',
    function () {
      $(this).focus();
      $(this).select();
    },
  );

  // Widgets
  showPopupWhenInputValueByElementIdIsIncorrect('medium');
  showPopupWhenInputValueByElementIdIsIncorrect('content');
  showPopupWhenInputValueByElementIdIsIncorrect('campaign');

  function showPopupWhenInputValueByElementIdIsIncorrect(id) {
    const errorHandler = document.getElementById('widget-form-error');
    const errorHandlerExists = errorHandler !== null;
    if (errorHandlerExists) {
      document.addEventListener('click', () => {
        const usedItem = event.target;
        const isUserFillInput =
          usedItem.tagName.toLowerCase() === 'input' &&
          usedItem.id.toLowerCase() === id;
        if (isUserFillInput) {
          setInterval(() => {
            const elementUnderValidate = document.getElementById(id);
            if (
              elementUnderValidate.value.length > 0 &&
              /^[a-zA-Z0-9\-\_]+$/.test(elementUnderValidate.value) === false
            ) {
              if (document.getElementById('widget-form-error-' + id) === null) {
                errorHandler.innerHTML +=
                  '<div class="alert alert-danger" role="alert" id="widget-form-error-' +
                  id +
                  '">' +
                  '<p>The field ' +
                  id +
                  ' contains invalid characters.</p>' +
                  '</div>';
                setTimeout(() => {
                  document.getElementById('widget-form-error-' + id).remove();
                }, 6000);
              }
              elementUnderValidate.value = null;
            }
          }, 500);
        }
      });
    }
  }

  $(document).on('change', '#widget_option', function () {
    $('.widget-lotteries, .widget-subtypes').hide();
    $('.widget-lotteries, .widget-subtypes').removeClass('active');

    var lotteries = $(this).find(':selected').data('lotteries');

    for (i = 1; i <= lotteries; i++) {
      $('#banner_lottery_' + i).show();
    }

    $('#widget_option_' + $(this).val()).show();
    $('#widget_option_' + $(this).val()).addClass('active');

    $('#submit-widget-button').removeAttr('disabled');

    buildWidget();
  });

  $(document).on(
    'change',
    '.widget-subtypes select, .widget-lotteries select, #widget_size, #widget_lang',
    function () {
      buildWidget();
    },
  );

  $(document).on(
    'keyup',
    '#custom_width, .widgets-generator #medium, .widgets-generator #campaign, .widgets-generator #content',
    function () {
      buildWidget();
    },
  );

  $(document).on(
    'blur',
    '#custom_width, .widgets-generator #medium, .widgets-generator #campaign, .widgets-generator #content',
    function () {
      buildWidget();
    },
  );

  $(document).on('change', '#widget_size', function () {
    var value = $(this).find(':selected').val();

    if (value == 'custom') $('#additional-widget-width').slideDown();
    else $('#additional-widget-width').slideUp();
  });

  // Show data on button click (pre)
  $('.show-data').click(function (e) {
    e.preventDefault();
    $(this).next().toggleClass('hidden');
  });

  $(document).on('click', '#email_preview', function () {
    var content = tinyMCE.activeEditor.getContent();
    var lang = $('#lang_code').val();

    $.post(
      '/mailsettings/preview',
      { lang: lang, content: content },
      function (data) {
        $('#email_preview_content').html(data);
      },
    );
    //send to server and process response
  });

  $(document).ready(function () {
    if ($('#input-register').is(':checked')) {
      $('#aff-list').show();
    }
    if (
      $('#input-register').is(':checked') &&
      $('#inputBonusMoney').is(':checked')
    ) {
      $('#inputPercent').attr('disabled', true);
    }
    if ($('#input-deposit').is(':checked')) {
      $('#inputDiscount').attr('disabled', true);
      if ($("input[name='input[bonus_type]']:checked").val() != '2') {
        setBonusTypeFreeTicket();
      }
    }
    if ($("input[name='input[codes_type]']:checked").val() == '1') {
      $('#input-series').show();
    }
    if ($("input[name='input[bonus_type]']:checked").val() != '0') {
      $('#inputPromoCodeLottery').val('0');
      $('#input-lottery').hide();
      $('#input-discount').show();
      if ($("input[name='input[bonus_type]']:checked").val() == '2') {
        setBonusLabelsShown();
      } else {
        setDiscountLabelsShown();
      }
    }
    if (
      $("#form-welcome-bonus select[name='input[lottery_register]']")
        .find(':selected')
        .val() !== '0'
    ) {
      $('#form-welcome-bonus #register-options').show();
    }
  });

  $('#input-deposit').change(function () {
    if (this.checked) {
      $('#inputDiscount').attr('disabled', true);
      if ($("input[name='input[bonus_type]']:checked").val() != '2') {
        setBonusTypeFreeTicket();
      }
    } else {
      $('#inputDiscount').removeAttr('disabled');
    }
  });

  $('#input-register').change(function () {
    if (this.checked) {
      if ($("input[name='input[bonus_type]']:checked").val() == '2') {
        $('#inputAmount').prop('checked', true);
        $('#inputPercent').attr('disabled', true);
      }
      $('#aff-list').show();
    } else {
      $('#inputPercent').removeAttr('disabled');
      $('#inputAffiliate').val('0');
      $('#aff-list').hide();
    }
  });

  $('#input-purchase').change(function () {
    if (this.checked) {
      $('#inputBonusMoney').attr('disabled', true);
      if ($("input[name='input[bonus_type]']:checked").val() == '2') {
        setBonusTypeFreeTicket();
      }
    } else {
      $('#inputBonusMoney').removeAttr('disabled');
    }
  });

  var setBonusTypeFreeTicket = function () {
    $('#inputFreeTicket').prop('checked', true);
    $('#input-lottery').show();
    $('#input-discount').hide();
  };

  var setBonusLabelsShown = function () {
    $('.label-discount').hide();
    $('.label-bonus').show();
  };

  var setDiscountLabelsShown = function () {
    $('.label-bonus').hide();
    $('.label-discount').show();
  };

  $("input[name='input[codes_type]']").change(function () {
    if ($("input[name='input[codes_type]']:checked").val() == '1') {
      $('#input-series').show();
    } else {
      $('#inputCodesNum').val('');
      $('#inputCodesUserNum').val('');
      $('#input-series').hide();
    }
  });

  $("input[name='input[bonus_type]']").change(function () {
    if ($("input[name='input[bonus_type]']:checked").val() == '0') {
      $('#input-discount').hide();
      $('#input-lottery').show();
    } else {
      $('#inputPromoCodeLottery').val('0');
      $('#input-lottery').hide();
      $('#input-discount').show();
    }
    if ($("input[name='input[bonus_type]']:checked").val() == '1') {
      setDiscountLabelsShown();
    } else if ($("input[name='input[bonus_type]']:checked").val() == '2') {
      setBonusLabelsShown();
    }
    if (
      $('#input-register').is(':checked') &&
      $('#inputBonusMoney').is(':checked')
    ) {
      $('#inputPercent').attr('disabled', true);
      $('#inputAmount').prop('checked', true);
    }
  });

  $('.blocked_country_toggle_deletable').change(function (event) {
    var target = event.currentTarget;
    var href = target.getAttribute('data-href');
    target.setAttribute('disabled', 'disabled');
    window.location = href;
  });
});

function buildWidget() {
  var widget = $('.widget-subtypes.active select').val();

  var widgetDomain = $('#widget_form').data('domain');
  var width = $('#custom_width').val().replace(/\D/g, '');
  var lottery1 = $('#lottery1').val();
  var lottery2 = $('#lottery2').val();
  var lottery3 = $('#lottery3').val();
  var ref = $('#ref').val();
  var medium = $('#medium').val();
  var content = $('#content').val();
  var campaign = $('#campaign').val();
  var lang = $('#widget_lang').val();
  var langCode = lang.substring(0, lang.indexOf('_'));

  if ($('#widget_size').val() == 'full') {
    width = 'full';
  }

  if (widget && lottery1 && width && $('#preview_widget').length) {
    $('#widget-preview-script').remove();
    $('#preview_widget').show();

    if (langCode == 'en') {
      langCode = '';
    } else {
      langCode = langCode + '/';
    }

    var uniqid = new Date().valueOf();
    var script = document.createElement('script');
    var previewSRC =
      '//' +
      widgetDomain +
      '/' +
      langCode +
      'api/internal/widget/generate?widget=' +
      widget +
      '&preview=true&lottery1=' +
      lottery1 +
      '&lottery2=' +
      lottery2 +
      '&lottery3=' +
      lottery3 +
      '&width=' +
      width +
      '&id=widget-preview';
    var codeSRC =
      'https://' +
      widgetDomain +
      '/' +
      langCode +
      'api/internal/widget/generate?widget=' +
      widget +
      '&width=' +
      width +
      '&id=widget-' +
      uniqid;

    var lotteries = $('#widget_option')
      .find('option:selected')
      .data('lotteries');

    for (i = 1; i <= lotteries; i++) {
      codeSRC += '&lottery' + i + '=' + $('#lottery' + i).val();
    }

    if (ref) {
      previewSRC += '&ref=' + ref;
      codeSRC += '&ref=' + ref;
    }

    var medium = encodeCampaign('medium');
    var campaign = encodeCampaign('campaign');
    var content = encodeCampaign('content');

    if (medium) {
      previewSRC += '&medium=' + medium;
      codeSRC += '&medium=' + medium;
    }
    if (campaign) {
      previewSRC += '&campaign=' + campaign;
      codeSRC += '&campaign=' + campaign;
    }
    if (content) {
      previewSRC += '&content=' + content;
      codeSRC += '&content=' + content;
    }

    script.src = previewSRC;
    script.type = 'text/javascript';
    script.id = 'widget-preview-script';

    document.getElementsByTagName('head')[0].appendChild(script);

    $('#widget_div').text('<div id="widget-' + uniqid + '"></div>');
    $('#widget_js').text('<script src="' + codeSRC + '"></script>');
  }
}

function encodeCampaign(key) {
  var string = $('#' + key)
    .val()
    .replace(/[\"\'~`!@#$%^&*()_={}[\]:;,.<>+\/?-]+|^\s+$/g, '')
    .replace(/\s+/gi, ' ');

  return encodeURI(string);
}

$("#form-welcome-bonus select[name='input[lottery_register]']").change(
  function () {
    if ($(this).find(':selected').val() !== '0') {
      $('#register-options').show();

      $('#registerWebsite').prop('checked', true);
      $('#registerApi').prop('checked', true);
    } else {
      $('#registerWebsite').prop('checked', false);
      $('#registerApi').prop('checked', false);

      $('#register-options').hide();
    }
  },
);
