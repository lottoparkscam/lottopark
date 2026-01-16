$(function () {
  const WHITELABEL_TYPE_V1 = 1;
  const WHITELABEL_TYPE_V2 = 2;

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
  const EASY_PAYMENT_GATEWAY = 17; // 20.03.2019 11:11 Vordis TODO: shared with whitelabel.js
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

  $('#confirmswitchModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var modal = $(this);
    var id = button.data('id');
    modal.find('#confirmswitchA').attr('href', '/lotteries/switch/' + id);
  });

  $('#confirmModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var modal = $(this);
    modal.find('.modal-body').text(button.data('confirm'));
    modal.find('#confirmOK').attr('href', button.data('href'));
  });

  $('.confirm').click(function (e) {
    if (!confirm($(this).data('confirm'))) {
      $(this).data('confirmed', 0);
      e.preventDefault();
      return false;
    }
    $(this).data('confirmed', 1);
  });

  $('[data-toggle="tooltip"]').tooltip({ html: true });
  $('[data-toggle="popover"]').popover({ html: true });

  $('.datepicker').datepicker({ autoclose: true, weekStart: 1 });
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

  $('.show-data').click(function (e) {
    e.preventDefault();
    $(this).next().toggleClass('hidden');
  });

  if ($('#generatePassword').length) {
    $('#inputPassword').keyup(function () {
      $('#generatedPassword').hide();
    });
    $('#generatePassword').click(function () {
      $.ajax('/ajax/password').done(function (password) {
        $('#inputPassword').val(password);
        $('#generatedPassword').fadeIn().find('span').text(password);
      });
    });
  }

  $('#inputCCMethod')
    .change(function () {
      $('.payment-details').addClass('hidden');
      if ($(this).val() == 1) {
        // emerchantpay
        $('#paymentDetailsEMerchantPay').removeClass('hidden');
      }
    })
    .change();

  $('#inputPaymentMethod')
    .change(function () {
      var paymentMethod = parseInt($(this).val());

      $('.payment-details').addClass('hidden');

      switch (paymentMethod) {
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
        case LENCO:
          $('#paymentDetailsLenco').removeClass('hidden');
          break;
        case GCASH:
          $('#paymentDetailsGcash').removeClass('hidden');
          break;
        default:
          break;
      }
    })
    .change();

  $('#inputWLType')
    .change(function () {
      if (parseInt($(this).val()) === WHITELABEL_TYPE_V1) {
        $('#inputPrepaid').parent().parent().hide();
        $('#inputPrepaidAlertLimit').parent().parent().hide();
      } else {
        $('#inputPrepaid').parent().parent().show();
        $('#inputPrepaidAlertLimit').parent().parent().show();
      }
    })
    .change();

  // Reset all filters on Admin/users page
  $('#filter-form-admin').on('click', function (e) {
    e.preventDefault();

    $('.filterSelect').prop('selectedIndex', 0);
    $('.filterInput').val('');

    $(':submit').click();
  });

  $('.table-sort .tablesorter-header').click(function () {
    document.location.href = $(this).data('href');
  });

  $('#inputPaymentCurrency').on('change', function () {
    var selected_code = $(this)
      .find('option')
      .eq(this.selectedIndex)
      .data('code');
    $('#minPurchaseCurrencyCode').text(selected_code);
    $('#inputMinPurchaseByCurrency').val('0.00');
  });

  $('#inputManagerCurrency').on('change', function () {
    var selected_code = $(this)
      .find('option')
      .eq(this.selectedIndex)
      .data('code');
    $('#prepaidCurrencyCode').text(selected_code);
    $('#prepaidAlertLimitCurrencyCode').text(selected_code);
    $('#inputPrepaid').val('0.00');
    $('#inputPrepaidAlertLimit').val('0.00');
  });

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
});
