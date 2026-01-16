import 'core-js/stable';
import 'regenerator-runtime/runtime';
import { initBasketData, fetchBasketItems } from './Basket';
import { initFlashMessages } from './FlashMessage';
import {
  fetchUserDetails,
  checkIsUserLogged,
  showUserArea,
  showNotUserArea,
} from './Account';
import { initPopupFromQueue, initFirstVisitPopup } from './Popup';
import { initInfoBoxes } from './Header';
import { initLotteryData, updateNextDrawDatesAfterCountdown } from './Lottery';
import {
  isNotCasino,
  isNotWordpressAdminPage,
  getPreparedApiUrl,
} from '../../../js/Helpers/UrlHelper';
import { isMainPlayPage } from '../../../js/Helpers/PageHelper';
import {
  initLotteryResultsPage,
  bindWinningsTableMultiplierEvents,
} from './LotteryResults';
import { initRaffleResultsPage } from './RaffleResults';

import { initSpecificRafflePage } from './Raffle';
import lightbox from '../../../js/plugins/Lightbox';
import { fetchConvertedCurrencyData } from './Currency';
import { setCookie, getCookie, deleteCookie, isCookieSet } from './Cookie';
import { initAppDownloadBanner } from './MobileAppDownloadBanner';

window.lightbox = lightbox;

const INTERVAL_DAY = 1000 * 60 * 60 * 24;
const INTERVAL_HOUR = 1000 * 60 * 60;
const INTERVAL_MINUTE = 1000 * 60;
const INTERVAL_SECOND = 1000;

const MAX_TICKETS = 25;

const POPUP_DEFAULT = 0;
const POPUP_CONFIRM_DELETE_ACCOUNT = 1;

const POPUP_BUTTON_QUICKPICK = 1;
const POPUP_BUTTON_CLOSE = 2;
const POPUP_BUTTON_CONFIRM = 3;
const POPUP_BUTTON_CONTINUE = 4;
const POPUP_BUTTON_URL = 5;
const POPUP_BUTTON_OK = 8;

const POPUP_CONFIRM = 2;
const NO_ERROR_TEXT = 0;
const MIN_ERROR_TEXT = 1;
const WRONG_FORMAT_ERROR_TEXT = 2;

const ID_MINI_POWERBALL = 56;
const ID_MINI_MEGA_MILLIONS = 67;
const ID_MINI_EUROMILLIONS = 68;
const ID_MINI_EUROJACKPOT = 69;
const ID_MINI_SUPERENALOTTO = 70;

const MINI_LOTTERIES_IDS = [
  ID_MINI_POWERBALL,
  ID_MINI_MEGA_MILLIONS,
  ID_MINI_EUROMILLIONS,
  ID_MINI_EUROJACKPOT,
  ID_MINI_SUPERENALOTTO,
];

/* track if checkout pixels have been sent */
let gtag_done = true;
let gtag_aff_done = true;
let facebook_done = true;
let is_affiliate_gtag = false;

const $ = jQuery;
let numberSelector;
jQuery(document).ready(function ($) {
  // This function changes servers session, so it needs to be run first
  initFlashMessages(() => {
    if (isNotWordpressAdminPage()) {
      // If global csrfToken is missing we set it here, this function needs too be run in specific order
      // In other case one session write form one request could overwrite the same session in another request
      checkIsUserLogged((isUserLogged) => {
        init();
        if (isUserLogged) {
          fetchUserDetails();
          showUserArea();
        } else {
          showNotUserArea();
        }
      });
    }
  });
});

function init() {
  // raffle promo widget
  $('.promo-widget__overflow').click((event) => {
    location.replace($(event.target).next().find('.js-play').attr('href'));
  });

  let Popup = class {
    constructor(title, message, buttons, data = []) {
      this.title = title;
      this.message = message;

      this.okButton = buttons.includes(POPUP_BUTTON_OK);
      this.quickpickButton = buttons.includes(POPUP_BUTTON_QUICKPICK);
      this.closeButton = buttons.includes(POPUP_BUTTON_CLOSE);
      this.confirmButton = buttons.includes(POPUP_BUTTON_CONFIRM);
      this.continueButton = buttons.includes(POPUP_BUTTON_CONTINUE);
      this.urlButton = buttons.includes(POPUP_BUTTON_URL);

      this.data = data;
    }

    show() {
      $('#dialog-title-div').html(this.title);
      $('#dialog-message').html(this.message);

      $('#dialog-button-ok').toggle(this.okButton);
      $('#dialog-button-close').toggle(this.closeButton);
      $('#dialog-button-continue').toggle(this.continueButton);
      $('#dialog-button-quickpick').toggle(this.quickpickButton);
      $('#dialog-button-confirm').toggle(this.confirmButton);
      $('#dialog-button-url').toggle(this.urlButton);

      showPopup(POPUP_DEFAULT, this.data);
    }
  };

  let intervals = [];
  let timer = 0;
  let big_countdowns = $('time.platform-countdown');
  let big_countdowns_items = new Array();
  let small_countdowns = $('time.simple-countdown');
  let carousels = $('.widget-list-carousel > ul');
  let carousels_items = new Array();
  let small_sliders = $('.small-widget-slider');
  let slider_items = new Array();
  let bigsliders = $('.widget-featured-content');
  let preventEnable = false;
  let play_mobile = false;

  $('.accept_term_and_conditions').on('click', function () {
    let disabled = false;
    if ($(this).is(':checked')) {
      disabled = false;
    } else {
      disabled = true;
    }

    if (parseInt($('#paymentButtonShouldBeDisabled').val()) === 1) {
      disabled = true;
      $(this).prop('checked', false);
    }

    $('#paymentSubmit').prop('disabled', disabled);
  });

  $('a').click(function (e) {
    if ($(this).hasClass('disabled')) {
      e.preventDefault();
    }
  });

  if (
    big_countdowns.length ||
    small_countdowns.length ||
    small_sliders.length ||
    bigsliders.length
  ) {
    updateCountdowns();
    intervals[0] = window.setInterval(update, 1000);
  }

  if (carousels.length) {
    carousels.data('hover', 0);
    carousels.data('off', 0);
    carousels.data('margin', 0);
    carousels.hover(function () {
      $(this).data('hover', $(this).data('hover') ? 0 : 1);
    });

    $('.widget-list-carousel-prev')
      .hover(function () {
        let ul = $(this).next().find('ul');
        ul.data('hover', ul.data('hover') ? 0 : 1);
      })
      .click(function (e) {
        e.preventDefault();
        moveCarousel($(this).next().find('ul'), 1, 1);
      });

    $('.widget-list-carousel-next')
      .hover(function () {
        let ul = $(this).prev().find('ul');
        ul.data('hover', ul.data('hover') ? 0 : 1);
      })
      .click(function (e) {
        e.preventDefault();
        moveCarousel($(this).prev().prev().find('ul'), 1);
      });
  }

  $('#astropaycardNumber').on('keyup', function () {
    let val = $(this).val();
    let new_val = '';

    val = val.replace(/\s/g, '');
    val = val.replace(/[^0-9]/g, '');

    for (let i = 0; i < val.length; i++) {
      if (i % 4 === 0 && i > 0) {
        new_val = new_val.concat(' ');
      }
      new_val = new_val.concat(val[i]);
    }

    $(this).val(new_val);
  });

  $('.widget-list-ticket')
    .hover(function () {
      $(this).find('.btn-primary').toggleClass('btn-primary-hover');
    })
    .click(function () {
      document.location.href = $(this).find('.widget-list-link').attr('href');
    });

  let widgetTicketNumbers = $(
    '.widget-ticket-numbers > a, .widget-ticket-bnumbers > a',
  ).not('.widget-ticket-number-selector');
  widgetTicketNumbers.click(toggleChecked);

  $('.widget-ticket-mobile-button a').click(function (e) {
    e.preventDefault();
    if (!$(this).hasClass('disabled')) {
      $(this).parents('.widget-ticket-entity').find('.dialog-close').click();
    }
  });

  $('.widget-ticket-button-clear').click(function () {
    $(this)
      .parents('.widget-ticket-entity')
      .removeClass('checked')
      .find('.checked')
      .removeClass('checked');
    $(this).prop('disabled', true);
    if (!$('.widget-ticket-entity .checked').length) {
      $('.widget-ticket-clear-all').prop('disabled', true);
    }
    let i = $(this).parents('.widget-ticket-entity').data('i');
    $('.widget-ticket-entity-mobile').eq(i).find('.ticket-line').remove();
    $('.widget-ticket-entity-mobile')
      .eq(i)
      .find('.widget-ticket-entity-newline')
      .show();
    $(this)
      .parents('.widget-ticket-entity')
      .find('.widget-ticket-mobile-button a')
      .addClass('disabled');
    $('.widget-ticket-entity-mobile')
      .eq(i)
      .find('.widget-ticket-entity-editline')
      .hide();
    $('.widget-ticket-entity-mobile')
      .eq(i)
      .find('.widget-ticket-entity-mobile-quickpick')
      .show();
    $('.widget-ticket-entity-mobile')
      .eq(i)
      .find('.widget-ticket-entity-mobile-delete')
      .hide();

    calculatePrice();
  });

  $('.widget-ticket-clear-all').click(function () {
    $('.widget-ticket-content').find('.checked').removeClass('checked');
    $('.widget-ticket-clear-all').prop('disabled', true);
    $('.widget-ticket-entity-mobile .ticket-line').remove();
    $('.widget-ticket-entity-mobile .widget-ticket-entity-newline').show();
    $('.widget-ticket-entity .widget-ticket-mobile-button a').addClass(
      'disabled',
    );
    $('.widget-ticket-entity-mobile .widget-ticket-entity-editline').hide();
    $(
      '.widget-ticket-entity-mobile .widget-ticket-entity-mobile-quickpick',
    ).show();
    $(
      '.widget-ticket-entity-mobile .widget-ticket-entity-mobile-delete',
    ).hide();

    checkMobileEntities();
    calculatePrice();
  });

  $('.widget-ticket-mobile-close .dialog-close').click(function () {
    checkMobileEntities();
  });

  $('.widget-ticket-button-quickpick').click(function () {
    disableTicketButtons();
    selectRandomAnimation($(this).parents('.widget-ticket-entity'));
  });

  $('.widget-ticket-quickpick-all').click(function () {
    disableTicketButtons();
    selectRandomAnimationAll($('.widget-ticket-entity'));
  });

  $('input[name="group_lottery"]').change(function () {
    let selectedLottery = $('input[name="group_lottery"]:checked');
    let index = selectedLottery.data('index');

    $('.widget-ticket-content').data(
      'multiplier',
      selectedLottery.data('multiplier'),
    );
    $('.widget-ticket-content').data('nrange', selectedLottery.data('nrange'));
    $('.widget-ticket-content').data('ncount', selectedLottery.data('ncount'));
    $('.widget-ticket-content').data('brange', selectedLottery.data('brange'));
    $('.widget-ticket-content').data('bcount', selectedLottery.data('bcount'));
    $('.widget-ticket-content').attr(
      'data-price',
      selectedLottery.attr('data-price'),
    );
    $('.widget-ticket-content').data('min', selectedLottery.data('min'));
    $('.widget-ticket-content').data(
      'min_bets',
      selectedLottery.data('min_bets'),
    );
    $('.widget-ticket-content').data(
      'max_bets',
      selectedLottery.data('max_bets'),
    );

    // show warnings for this lottery
    $('.widget-ticket-alerts-group').addClass('hidden-normal');
    $('.widget-ticket-alerts-group').eq(index).removeClass('hidden-normal');

    // switch title & content
    $('.group_play_title')
      .addClass('hidden-normal')
      .eq(index)
      .removeClass('hidden-normal');
    $('.group_play_content')
      .addClass('hidden-normal')
      .eq(index)
      .removeClass('hidden-normal');

    // reset everything
    $('#orderLotteryId').val($(this).val());
    checkPlayLines();
    checkMobileEntities();
    calculatePrice();
  });

  function getPerLine() {
    let width =
      window.innerWidth ||
      document.documentElement.clientWidth ||
      document.body.clientWidth;
    let perline = 5;
    if (width <= 1220 && width > 900) {
      perline = 4;
    }
    if (width <= 900 && width > 600) {
      perline = 3;
    }
    return perline;
  }

  function checkPlayLines() {
    const isMultidrawEnabled = parseInt($('.widget-ticket-content').attr('data-multidraw_enabled'));
    const lotteryElement = document.getElementById('orderLotteryId');
    let lotteryId = null;
    if (lotteryElement) {
      lotteryId = parseInt(lotteryElement.value);
    }

    let perline = getPerLine();
    let min_lines = isMultidrawEnabled ? (MINI_LOTTERIES_IDS.includes(lotteryId) ? 10 : 1) : $('.widget-ticket-content').data('min');
    let count = 0;
    $('.widget-ticket-entity').each(function () {
      if ($(this).find('.checked').length) {
        count++;
      }
    });

    if (typeof min_lines !== 'undefined' && count < min_lines - 1) {
      count = min_lines - 1;
    }

    let lines = Math.ceil(count / perline);

    if (lines === 0) {
      lines = 1;
    }

    if (lines > Math.ceil(MAX_TICKETS / perline)) {
      lines = Math.ceil(MAX_TICKETS / perline);
    }

    $('.widget-ticket-entity')
      .slice(0, lines * perline)
      .removeClass('hidden')
      .css('opacity', 1);
    $('.widget-ticket-entity')
      .slice(lines * perline)
      .addClass('hidden')
      .css('opacity', 0);

    $('.widget-ticket-button-more').prop(
      'disabled',
      $('.widget-ticket-entity.hidden').length > 0 ? false : true,
    );
    $('.widget-ticket-button-less').prop(
      'disabled',
      $('.widget-ticket-entity').not('.hidden').length > perline ? false : true,
    );
  }

  $('.widget-ticket-button-more').click(addNewLine);

  $('.widget-ticket-button-less').click(function () {
    let perline = getPerLine();

    if ($('.widget-ticket-entity').not('.hidden').length == perline * 2) {
      $('.widget-ticket-button-less').prop('disabled', true);
    }
    $('.widget-ticket-button-more').prop('disabled', false);
    let entities = $('.widget-ticket-entity').not('.hidden');
    let to_hide = entities.length % perline;
    if (to_hide == 0) {
      to_hide = perline;
    }

    entities
      .slice(entities.length - to_hide)
      .css('opacity', 1)
      .animate({ opacity: 0 }, 'slow', function () {
        $(this).addClass('hidden');

        $(this).removeClass('checked').find('.checked').removeClass('checked');
        $(this).find('.widget-ticket-button-clear').prop('disabled', true);

        let i = $(this).data('i');
        $('.widget-ticket-entity-mobile').eq(i).find('.ticket-line').remove();
        $('.widget-ticket-entity-mobile')
          .eq(i)
          .find('.widget-ticket-entity-newline')
          .show();
        $(this).find('.widget-ticket-mobile-button a').addClass('disabled');
        $('.widget-ticket-entity-mobile')
          .eq(i)
          .find('.widget-ticket-entity-editline')
          .hide();
        $('.widget-ticket-entity-mobile')
          .eq(i)
          .find('.widget-ticket-entity-mobile-quickpick')
          .show();
        $('.widget-ticket-entity-mobile')
          .eq(i)
          .find('.widget-ticket-entity-mobile-delete')
          .hide();

        to_hide--;
        if (to_hide == 0) {
          if (!$('.widget-ticket-entity .checked').length) {
            $('.widget-ticket-clear-all').prop('disabled', true);
          }
          checkMobileEntities();
          calculatePrice();
        }
      });
  });

  /* mobile ticket */
  $('.widget-ticket-entity-newline').click(mobileNewLine);
  $('.widget-ticket-entity-editline').click(mobileNewLine);
  $('.widget-ticket-entity-mobile .ticket-line').click(mobileNewLine);

  function mobileNewLine(e) {
    e.preventDefault();
    let i = $(this).parent().data('i');
    $('.widget-ticket-entity')
      .eq(i)
      .removeClass('mobile-hidden')
      .removeClass('hidden')
      .css('opacity', 1);
    $('body').addClass('menu-active');
  }

  function addNewLine() {
    let perline = getPerLine();
    let max_lines = Math.ceil(MAX_TICKETS / perline);
    if (
      $('.widget-ticket-entity').not('.hidden').length / perline ==
      max_lines - 1
    ) {
      $('.widget-ticket-button-more').prop('disabled', true);
    }
    $('.widget-ticket-button-less').prop('disabled', false);
    $('.widget-ticket-entity.hidden')
      .slice(0, perline)
      .removeClass('hidden')
      .css('opacity', 0)
      .animate({ opacity: 1 }, 'slow', function () {
        $(this).removeClass('hidden');
      });
  }

  function checkMobileEntities() {
    // Keno-style form
    let isFormHorizontal = document.querySelector(
      '.widget-ticket-content-horizontal',
    );

    if (isFormHorizontal || !play_mobile) {
      return;
    }
    // we need to order all the tickets, so if someone deletes one ticket in the middle, the empty ticket will not
    // show up in the middle (for mobile, for desktop we do not care)

    let tickets = [];
    let tickets_notchecked = []; // keep partially checked tickets in the end
    $('.widget-ticket-entity').each(function () {
      let entity = $(this);
      if (entity.hasClass('checked')) {
        let nums = $(this)
          .children('.widget-ticket-entity-content')
          .children('.widget-ticket-numbers')
          .children('a.checked');
        let bnums = $(this)
          .children('.widget-ticket-entity-content')
          .children('.widget-ticket-bnumbers')
          .children('a.checked');

        let ticket_line_n = [];
        let ticket_line_b = [];
        nums.each(function () {
          ticket_line_n.push($(this).text());
        });
        bnums.each(function () {
          ticket_line_b.push($(this).text());
        });
        tickets.push([ticket_line_n, ticket_line_b]);
      } else if (entity.find('.checked').length) {
        let nums = $(this)
          .children('.widget-ticket-entity-content')
          .children('.widget-ticket-numbers')
          .children('a.checked');
        let bnums = $(this)
          .children('.widget-ticket-entity-content')
          .children('.widget-ticket-bnumbers')
          .children('a.checked');

        let ticket_line_n = [];
        let ticket_line_b = [];
        nums.each(function () {
          ticket_line_n.push($(this).text());
        });
        bnums.each(function () {
          ticket_line_b.push($(this).text());
        });
        tickets_notchecked.push([
          ticket_line_n,
          ticket_line_b,
          entity
            .children('.widget-ticket-entity-content')
            .children('.widget-ticket-numbers')
            .hasClass('checked'),
          entity
            .children('.widget-ticket-entity-content')
            .children('.widget-ticket-bnumbers')
            .hasClass('checked'),
        ]);
      }
    });

    // now clear all
    $('.widget-ticket-content').find('.checked').removeClass('checked');
    $('.widget-ticket-clear-all').prop('disabled', true);
    $('.widget-ticket-button-clear').prop('disabled', true);
    $('.widget-ticket-entity-mobile .ticket-line').remove();
    $('.widget-ticket-entity-mobile .widget-ticket-entity-newline').show();
    $('.widget-ticket-entity .widget-ticket-mobile-button a').addClass(
      'disabled',
    );
    $('.widget-ticket-entity-mobile .widget-ticket-entity-editline').hide();
    $(
      '.widget-ticket-entity-mobile .widget-ticket-entity-mobile-quickpick',
    ).show();
    $(
      '.widget-ticket-entity-mobile .widget-ticket-entity-mobile-delete',
    ).hide();

    // and set up again
    for (let i = 0; i < tickets.length; i++) {
      let ticket_line = $('<div class="ticket-line"></div>');
      for (let j = 0; j < tickets[i][0].length; j++) {
        ticket_line.append(
          $('<div class="ticket-line-number">' + tickets[i][0][j] + '</div>'),
        );
        $('.widget-ticket-entity')
          .eq(i)
          .find('.widget-ticket-numbers > a')
          .eq(tickets[i][0][j] - 1)
          .addClass('checked');
      }
      for (let j = 0; j < tickets[i][1].length; j++) {
        ticket_line.append(
          $('<div class="ticket-line-bnumber">' + tickets[i][1][j] + '</div>'),
        );
        $('.widget-ticket-entity')
          .eq(i)
          .find('.widget-ticket-bnumbers > a')
          .eq(tickets[i][1][j] - 1)
          .addClass('checked');
      }
      ticket_line.click(mobileNewLine);

      $('.widget-ticket-entity-mobile')
        .eq(i)
        .find('.widget-ticket-entity-newline')
        .hide();
      $('.widget-ticket-entity')
        .eq(i)
        .find('.widget-ticket-mobile-button a')
        .removeClass('disabled');
      $('.widget-ticket-entity-mobile')
        .eq(i)
        .find('.widget-ticket-entity-editline')
        .hide();
      $('.widget-ticket-entity-mobile')
        .eq(i)
        .find('.widget-ticket-entity-mobile-quickpick')
        .show();
      $('.widget-ticket-entity-mobile')
        .eq(i)
        .find('.widget-ticket-entity-mobile-delete')
        .show();
      $('.widget-ticket-entity-mobile').eq(i).append(ticket_line);
      $('.widget-ticket-entity')
        .eq(i)
        .find('.widget-ticket-numbers')
        .addClass('checked');
      $('.widget-ticket-entity')
        .eq(i)
        .find('.widget-ticket-bnumbers')
        .addClass('checked');
      $('.widget-ticket-entity').eq(i).addClass('checked');
      enableTicketButtons($('.widget-ticket-entity').eq(i));
    }
    for (let i = 0; i < tickets_notchecked.length; i++) {
      for (let j = 0; j < tickets_notchecked[i][0].length; j++) {
        $('.widget-ticket-entity')
          .eq(i + tickets.length)
          .find('.widget-ticket-numbers > a')
          .eq(tickets_notchecked[i][0][j] - 1)
          .addClass('checked');
      }
      for (let j = 0; j < tickets_notchecked[i][1].length; j++) {
        $('.widget-ticket-entity')
          .eq(i + tickets.length)
          .find('.widget-ticket-bnumbers > a')
          .eq(tickets_notchecked[i][1][j] - 1)
          .addClass('checked');
      }

      if (tickets_notchecked[i][2]) {
        $('.widget-ticket-entity')
          .eq(i + tickets.length)
          .find('.widget-ticket-numbers')
          .addClass('checked');
      }
      if (tickets_notchecked[i][3]) {
        $('.widget-ticket-entity')
          .eq(i + tickets.length)
          .find('.widget-ticket-bnumbers')
          .addClass('checked');
      }
      $('.widget-ticket-entity-mobile')
        .eq(i + tickets.length)
        .find('.widget-ticket-entity-editline')
        .show();
      $('.widget-ticket-entity-mobile')
        .eq(i + tickets.length)
        .find('.widget-ticket-entity-newline')
        .hide();
      $('.widget-ticket-entity')
        .eq(i + tickets.length)
        .find('.widget-ticket-mobile-button a')
        .addClass('disabled');
      enableTicketButtons($('.widget-ticket-entity').eq(i));
    }

    $('.widget-ticket-entity-mobile').addClass('mobile-hidden');
    let first_not_checked = -1;
    $('.widget-ticket-entity').each(function () {
      if ($(this).hasClass('checked') || $(this).find('.checked').length) {
        $('.widget-ticket-entity-mobile')
          .eq($(this).data('i'))
          .removeClass('mobile-hidden');
      } else if (first_not_checked == -1) {
        first_not_checked = $(this).data('i');
      }
    });
    if (first_not_checked != -1) {
      $('.widget-ticket-entity-mobile')
        .eq(first_not_checked)
        .removeClass('mobile-hidden');
    }
  }

  $('.widget-ticket-mobile-close .dialog-close').click(function (e) {
    e.preventDefault();
    let i = $(this).parents('.widget-ticket-entity').data('i');
    $('.widget-ticket-entity').eq(i).addClass('mobile-hidden');
    $('body').removeClass('menu-active');
  });

  $('.widget-ticket-entity-mobile-quickpick').click(function (e) {
    e.preventDefault();
    let i = $(this).parents('.widget-ticket-entity-mobile').data('i');
    selectRandom($('.widget-ticket-entity').eq(i));
    enableTicketButtons();
    checkMobileEntities();
    calculatePrice();
  });

  $('a.small-purchase').click(function (e) {
    e.preventDefault();
    let count = $(this).data('count');
    $('.widget-ticket-entity').each(function () {
      if (!$(this).hasClass('checked') && !$(this).find('.checked').length) {
        count--;
        if (count == -1) {
          return false;
        }
        selectRandom($('.widget-ticket-entity').eq($(this).data('i')));
      }
    });
    enableTicketButtons();
    checkMobileEntities();
    calculatePrice();
  });

  $('#inputMultiDraw, #ticket_type').on('change', function () {
    calculatePrice();
  });

  $('input[name="ticket_type"]').on('change', function () {
    if ($(this).val() === '2') {
      $('.widget-ticket-content').attr('data-multidraw_enabled', '1');
      $('#order-multidraw-enabled').val('1');
    } else {
      $('.widget-ticket-content').attr('data-multidraw_enabled', '0');
      $('#order-multidraw-enabled').val('0');
    }
    checkPlayLines();
  });

  $('.widget-ticket-entity-mobile-delete').click(function (e) {
    e.preventDefault();
    let i = $(this).parents('.widget-ticket-entity-mobile').data('i');
    $('.widget-ticket-entity')
      .eq(i)
      .find('.widget-ticket-button-clear')
      .click();

    checkMobileEntities();
  });

  $('.mobile-user-menu').click(function (e) {
    e.preventDefault();
    $('.mobile-user-menu-container').slideToggle('fast');
  });

  let wrong_lines = new Array();
  $('#widget-ticket-form').submit(function (e) {
    const isMultidrawEnabled = parseInt($('.widget-ticket-content').attr('data-multidraw_enabled'));
    const lotteryId = parseInt(document.getElementById('orderLotteryId').value);
    let all_lines = '';
    let ticket = $(this).find('.widget-ticket-content');
    let min_lines = isMultidrawEnabled ? (MINI_LOTTERIES_IDS.includes(lotteryId) ? 10 : 1) : parseInt(ticket.data('min'));
    let min_bets = parseInt(ticket.data('min_bets'));
    let max_bets = parseInt(ticket.data('max_bets'));
    let isFormHorizontal = !!$('.widget-ticket-content-horizontal').length;
    let lines_cnt = $(this).find('.widget-ticket-entity.checked');
    if (lines_cnt.length < min_lines) {
      let data = $('#dialog-wrapper').data();

      let minimum_popup = new Popup(
        data['minimumTitle'],
        data['minimumContent'],
        [POPUP_BUTTON_CLOSE, POPUP_BUTTON_QUICKPICK],
        [ticket.data('min')],
      );
      minimum_popup.show();
      e.preventDefault();
      return false;
    }

    if (
      ticket.data('multiplier') != undefined &&
      lines_cnt.length % parseInt(ticket.data('multiplier')) != 0
    ) {
      let data = $('#dialog-wrapper').data();
      let multiplier_popup = new Popup(
        data['multiplierTitle'],
        data['multiplierContent'],
        [POPUP_BUTTON_CLOSE, POPUP_BUTTON_QUICKPICK],
        [ticket.data('multiplier')],
      );
      multiplier_popup.show();
      e.preventDefault();
      return false;
    }

    wrong_lines = [];
    $(this)
      .find('.widget-ticket-entity')
      .each(function (index) {
        let fnumbers = $(this).find('.widget-ticket-numbers a.checked');
        let fbnumbers = $(this).find('.widget-ticket-bnumbers a.checked');
        if (isFormHorizontal) {
          // Keno-style form
          fnumbers = $(this)
            .find('.widget-ticket-number-value')
            .filter(function () {
              return !$(this).parent().hasClass('disabled');
            });
          fbnumbers = $();
        }
        if (fnumbers.length || fbnumbers.length) {
          let select = $('#widget-ticket-slip-size-select');
          let ncount = select.length ? select.val() : ticket.data('ncount');
          if (
            ncount != fnumbers.length ||
            ticket.data('bcount') != fbnumbers.length
          ) {
            wrong_lines.push(index);
          }
          if ($(this).hasClass('checked')) {
            let numbers = [];
            let bnumbers = [];

            fnumbers.each(function () {
              let num = parseInt($(this).text());
              if (num < 1 || num > ticket.data('nrange')) {
                wrong_lines.push(index);
              }
              numbers.push(num);
            });
            fbnumbers.each(function () {
              let num = parseInt($(this).text());
              if (num < 1 || num > ticket.data('brange')) {
                wrong_lines.push(index);
              }
              bnumbers.push(num);
            });
            all_lines += numbers.join('_');
            if (bnumbers.length > 0) {
              all_lines += '-' + bnumbers.join('_');
            }
            all_lines += ';';
          }
        }
      });

    if (wrong_lines.length) {
      e.preventDefault();
      let data = $('#dialog-wrapper').data();

      let wrong_line_popup = new Popup(
        data['wrongLineTitle'],
        data['wrongLineContent'],
        [POPUP_BUTTON_CLOSE, POPUP_BUTTON_CONTINUE],
      );
      wrong_line_popup.show();
      return false;
    }
    if ((MINI_LOTTERIES_IDS.includes(lotteryId) || isMultidrawEnabled === 0) && (lines_cnt.length - 1) % max_bets < min_bets - 1) {
      e.preventDefault();
      let data = $('#dialog-wrapper').data();
      let minbets_popup = new Popup(
        data['minbetsTitle'],
        data['minbetsContent'],
        [],
      );
      minbets_popup.show();
      return false;
    }

    all_lines = all_lines.slice(0, -1);
    $('#widget-ticket-input').val(all_lines);
    disableTicketButtons();
  });

  $('.dialog-continue').click(function () {
    hidePopup();
    for (let i = 0; i < wrong_lines.length; i++) {
      $('.widget-ticket-entity')
        .eq(wrong_lines[i])
        .removeClass('checked')
        .find('.checked')
        .removeClass('checked');
    }
    $('#widget-ticket-form').submit();
  });

  $('#dialog-close').click(function (e) {
    e.preventDefault();
    hidePopup();
    enableKenoButtons();
  });

  $('.dialog-content .btn-dialog-close').click(function (e) {
    e.preventDefault();
    $(this).parents('#dialog').find('#dialog-close').click();
  });

  $('#dialog-wrapper').click(function (e) {
    if ($(e.target).attr('id') == 'dialog-wrapper') {
      $(this).find('#dialog-close').click();
    }
  });

  // Double Jack - prevent modal close
  if ($('body').attr('data-theme') === 'doublejack') {
    $('#dialog-close').off();
    $('.dialog-content .btn-dialog-close').off();
    $('#dialog-wrapper').off();
  }

  $('.dialog .dialog-url').click(function () {
    window.location.href = $(this).data('url');
  });

  function handleKenoQuickPick(request) {
    const currentCount = $('.widget-ticket-entity').not('.checked').length;

    const elementsToAdd = Math.max(0, request - currentCount);
    for (let i = 0; i < elementsToAdd; i++) {
        numberSelector.addTicketEntity();
    }

    $('.widget-ticket-button-quickpick-horizontal').prop('disabled', true);

    $('.widget-ticket-entity')
      .not('.checked')
      .slice(0, request)
      .each((i, v) =>
        $(v).find('.widget-ticket-button-quickpick-horizontal').click(),
      );
  }

  function handleQuickPick(request) {
    let isFormHorizontal = !!$('.widget-ticket-content-horizontal').length;

    disableTicketButtons();
    if (play_mobile) {
      if (isFormHorizontal === true) {
        handleKenoQuickPick(request);
        enableKenoButtons();
      } else {
        let count = request;
        $('.widget-ticket-entity').each(function () {
          if (!$(this).hasClass('checked') && !$(this).find('.checked').length) {
            count--;
            if (count == -1) {
              return false;
            }
            selectRandom($('.widget-ticket-entity').eq($(this).data('i')));
          }
        });
      }

      checkMobileEntities();
    } else {
      if (isFormHorizontal === true) {
        // Keno-style form
        handleKenoQuickPick(request);
        enableKenoButtons();
      } else {
        const currentCount = $('.widget-ticket-entity').not('.checked').not('.hidden').length;
        let perline = getPerLine();
        const rowToAdd = Math.ceil((request - currentCount) / perline);
        for (let i = 0; i < rowToAdd; i++) {
          addNewLine();
        }

        selectRandomAnimationAll(
          $('.widget-ticket-entity').not('.checked').slice(0, request));
      }
    }
  }

  $('#dialog .dialog-quickpick').on('click', function () {
    let ticket = $('.widget-ticket-content');
    let filled_cnt = $('.widget-ticket-entity.checked').length;
    let request = 0;
    if (ticket.data('multiplier') > 1) {
      if (filled_cnt > 4) {
        request =
          parseInt(ticket.data('multiplier')) -
          (filled_cnt % parseInt(ticket.data('multiplier')));
      } else {
        request = parseInt(ticket.data('multiplier')) - filled_cnt;
      }
      while (
        $('.widget-ticket-entity').not('.hidden').length <
        filled_cnt + request
      ) {
        $('.widget-ticket-button-more').trigger('click');
      }
    } else {
      request = parseInt(ticket.data('min')) - filled_cnt;
    }

    handleQuickPick(request);
    hidePopup();
  });

  $('.btn-checkout-details').click(function (e) {
    e.preventDefault();
    $(this).closest('.new-checkout-list-item').find('.new-checkout-list-lines').toggleClass('hidden-normal');
  });

  $('#dialog .confirm').click(function (e) {
    e.preventDefault();
    $('#dialog .dialog-title .pull-left')
      .eq(POPUP_CONFIRM)
      .text($(this).data('title'));
    $('#dialog .dialog-content p')
      .eq(POPUP_CONFIRM)
      .text($(this).data('confirm'));
    $('#dialog .dialog-confirm').attr('href', $(this).attr('href'));
    showPopup(POPUP_CONFIRM);
  });

  if ($('#popup_message_accept').length) {
    //checking is element exists
    let popup_data = $('#popup_message_accept').data();
    let popup_from_queue = new Popup(
      popup_data['title'],
      popup_data['content'],
      [POPUP_BUTTON_URL, POPUP_BUTTON_CLOSE],
    );
    popup_from_queue.show();
  }

  if ($('#results-table').length) {
    $('#results-table').tablesorter({
      sortList: [[2, 1]],
      sortRestart: true,
      headers: {
        0: {
          sorter: false,
        },
        1: {
          sorter: true,
        },
        2: {
          sortInitialOrder: 'desc',
        },
        3: {
          sorter: false,
        },
        4: {
          sortInitialOrder: 'desc',
        },
        5: {
          sorter: false,
        },
      },
    });
  }

  if ($('#info-table').length) {
    $('#info-table').tablesorter({
      sortList: [[2, 0]],
      sortRestart: true,
      headers: {
        0: {
          sorter: false,
        },
        4: {
          sorter: false,
        },
        5: {
          sortInitialOrder: 'desc',
        },
        6: {
          sortInitialOrder: 'desc',
        },
        7: {
          sorter: false,
        },
      },
    });
  }

  $('#results-table tbody tr').click(function () {
    document.location.href = $(this)
      .find('td')
      .eq(5)
      .find('a')
      .eq(0)
      .attr('href');
  });

  $('#info-table tbody tr').click(function () {
    document.location.href = $(this)
      .find('td')
      .eq(7)
      .find('a')
      .eq(0)
      .attr('href');
  });

  $('.table-transactions.clickable tbody tr').click(function () {
    document.location.href = $(this)
      .find('td')
      .last()
      .find('a')
      .last()
      .attr('href');
  });

  $('.table-payment tbody tr').click(function () {
    document.location.href = $(this).find('td').last().find('a').attr('href');
  });

  $('.results-short-link-disabled').click(function (e) {
    e.preventDefault();
  });

  $('.tooltip').hover(
    function () {
      let trigger = $(this);
      trigger.css('position', 'relative');
      let width = trigger.outerWidth();
      let height = trigger.outerHeight();

      if (!$(this).find('.tooltip-cloud').length) {
        let tooltip = $('<div>', {
          class: 'tooltip-cloud',
          html: $(this).data('tooltip'),
        });
        tooltip.css('opacity', 0);
        tooltip.appendTo(trigger);

        if (trigger.hasClass('tooltip-bottom')) {
          tooltip
            .css('top', parseInt(height) + 5)
            .css('left', -parseInt(tooltip.outerWidth() - width) / 2);
        } else {
          tooltip
            .css('top', -parseInt(tooltip.outerHeight() - height) / 2)
            .css('left', parseInt(width) + 5);
        }
        tooltip.stop().animate({ opacity: 1 }, 200);
      } else {
        let tooltip = $(this).find('.tooltip-cloud');
        tooltip.stop().animate({ opacity: 1 }, 200);
      }
    },
    function () {
      let tooltip = $(this).find('.tooltip-cloud');
      tooltip.stop().animate({ opacity: 0 }, 200, function () {
        tooltip.remove();
      });
    },
  );

  function showInfobox(element) {
    let link = element.find('a');
    let url = element.find('a').get(0).pathname.split('/');
    if (url.length >= 3 && $('#infobox_' + url[url.length - 2]).length) {
      let infobox = $('#infobox_' + url[url.length - 2]);
      infobox.css('top', link.offset().top + link.height());
      infobox.css('left', link.offset().left);
      infobox.stop().fadeIn('fast');
    }
  }

  function hideInfobox(element) {
    let url = element.find('a').get(0).pathname.split('/');
    if (url.length >= 3 && $('#infobox_' + url[url.length - 2]).length) {
      let infobox = $('#infobox_' + url[url.length - 2]);
      infobox.stop().fadeOut('fast');
    }
  }

  let last_infobox_trigger = null;
  $('.infobox').hover(
    function () {
      showInfobox($(this));
      last_infobox_trigger = $(this);
    },
    function () {
      hideInfobox($(this));
    },
  );

  $('.infobox-wrapper').hover(
    function () {
      showInfobox(last_infobox_trigger);
    },
    function () {
      hideInfobox(last_infobox_trigger);
    },
  );

  $('.cvv-info-tooltip').click(function (e) {
    e.preventDefault();
  });

  $('.userSelectedCurrencySelector').on('click', function () {
    $('.userSelectedCurrencySelector').on('change', function () {
      // Important flag for backend, so it converts currency independently and avoid data manipulation
      $('#userSelectedCurrency').val(this.value);
      // Use API to fetch gateway amount in selected currency
      fetchConvertedCurrencyData();
    });
  });
  if (!$('.deposit-amount').hasClass('deposit-amount-active')) {
    $('.deposit-amount').eq(1).addClass('deposit-amount-active');
  }
  $('.deposit-amount').click(function () {
    $('.deposit-amount').removeClass('deposit-amount-active');
    let value = 0;
    let value_in_gateway_currency = 0;

    if ($(this).hasClass('platform-form')) {
      $(this).find('input').focus();
      value = $(this).find('input').val();

      value = value.replace(',', '.');

      if (parseFloat(value) === 0 || value.length === 0) {
        value = 0;
      }
      value_in_gateway_currency = parseFloat(
        $('#deposit-amount-gateway').data('gatewayvalue'),
      );

      if (value === 0) {
        $('.deposit-amount.platform-form input').val('');
      }
      if ($(this).find('input').val().length > 0) {
        $(this).addClass('deposit-amount-active');
      }

      checkMinimumOrder();
    } else {
      $('.deposit-amount.platform-form input').val('0');
      value = parseFloat($(this).data('value'));
      value_in_gateway_currency = parseFloat(
        $(this).data('gatewaycurrencyvalue'),
      );

      let pgateway_currency_code = $('#paymentCurrencyInGateway').val();
      let lang = $('html').attr('lang') || 'en-GB';
      let cur_formatter = new Intl.NumberFormat(lang, {
        style: 'currency',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        currency: pgateway_currency_code || 'USD',
      });
      let format = cur_formatter.format(0);

      $('#deposit-amount-gateway').data('gatewayvalue', '0').text(format);
      $('#paymentCustom').val('0');
      $(this).addClass('deposit-amount-active');
    }

    $('#paymentAmountInGateway').val(value_in_gateway_currency);

    changeOrderValue(value);

    checkMinimumPayment();

    // Use API to fetch gateway amount in selected currency if custom currency was selected by user
    if ($('#userSelectedCurrency').val()) {
      fetchConvertedCurrencyData();
    }
  });

  $('.deposit-amount.platform-form input').on('keyup', function () {
    let pgateway_currency_code = $('#paymentCurrencyInGateway').val();
    let user_currency_code = $('.payment-form').data('currency');
    let lang = $('html').attr('lang') || 'en-GB';

    let cur_formatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      currency: pgateway_currency_code || 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    let converted_multiplier = parseFloat(
      $('.payment-form').data('convertedmultiplier'),
    );
    let result_val = 0.0;
    let value = $(this).val();
    let current_val = 0.0;
    let new_value = value.toString().replace(',', '.');

    if (
      typeof new_value == 'undefined' ||
      isNaN(new_value) ||
      new_value.length === 0
    ) {
      new_value = 0;
    }

    current_val = new_value;
    new_value = parseFloat(new_value);

    if (user_currency_code !== pgateway_currency_code) {
      result_val = parseFloat(current_val / converted_multiplier);
      result_val = Math.round(result_val * 100) / 100;
    } else {
      result_val = current_val;
    }

    let format = cur_formatter.format(result_val);

    $('#deposit-amount-gateway').data('gatewayvalue', result_val).text(format);
    $('#paymentAmountInGateway').val(result_val);

    changeOrderValue(new_value);
    $('#paymentCustom').val(new_value);
    checkMinimumOrder();

    $(this).parents('.deposit-amount').click();
  });

  $('.deposit-amount.platform-form input').on('blur', function () {
    let current_val = $(this).val();
    let pgateway_currency_code = $('#paymentCurrencyInGateway').val();
    let lang = $('html').attr('lang') || 'en-GB';
    let value = '0.00';
    let cur_formatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      currency: pgateway_currency_code || 'USD',
    });
    let format = cur_formatter.format(value);

    if (current_val.length === 0) {
      $('.deposit-amount.platform-form input').val('0');
      $('#deposit-amount-gateway').data('gatewayvalue', '0').text(format);
      $('#paymentCustom').val('0');
    }
  });

  $('.show-all-dates').on('click', function () {
    let dates = $(this).parent().find('.all-dates');
    if (dates.css('display') == 'inline') {
      dates.css('display', 'none');
      $(this).text($(this).data('show'));
    } else {
      dates.css('display', 'inline');
      $(this).text($(this).data('hide'));
    }
  });

  $('.deposit-amount').on('blur', function () {
    let current_val = $('#deposit-amount-gateway').val();
    let pgateway_currency_code = $('#paymentCurrencyInGateway').val();
    let lang = $('html').attr('lang') || 'en-GB';
    let value = '0.00';
    let cur_formatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      currency: pgateway_currency_code || 'USD',
    });
    let format = cur_formatter.format(value);

    if (current_val.length === 0) {
      $('.deposit-amount.platform-form input').val('0');
      $('#deposit-amount-gateway').data('gatewayvalue', '0').text(format);
      $('#paymentCustom').val('0');
    }
  });

  function checkMinimumOrder() {
    let minorder = $('.payment-form').data('minorder');
    let value_input = $('.deposit-amount.platform-form input').val();
    let which_show = NO_ERROR_TEXT;
    let value = parseFloat(value_input);

    value_input = value_input.replace(',', '.');

    if ($('.deposit-amount.platform-form input').val().length > 0) {
      $('.deposit-amount.platform-form').addClass('deposit-amount-active');
      if (isNaN(value_input)) {
        which_show = WRONG_FORMAT_ERROR_TEXT;
      } else if (value === 0 || parseFloat(minorder) > value) {
        which_show = MIN_ERROR_TEXT;
      }
    } else {
      $('.deposit-amount.platform-form').removeClass('deposit-amount-active');
    }
    let text_msg = '';
    switch (which_show) {
      case NO_ERROR_TEXT: // Show no error text
        $('.deposit-amount.platform-form').removeClass('deposit-amount-error');
        break;
      case MIN_ERROR_TEXT: // Show min error text
        text_msg = $('#deposit-amount-error-cloud').data('textmindeposit');
        $('#deposit-amount-error-cloud').text(text_msg);
        $('.deposit-amount.platform-form').addClass('deposit-amount-error');
        break;
      case WRONG_FORMAT_ERROR_TEXT: // Show wrong format error text
        text_msg = $('#deposit-amount-error-cloud').data('textwrongformat');
        $('#deposit-amount-error-cloud').text(text_msg);
        $('.deposit-amount.platform-form').addClass('deposit-amount-error');
        break;
    }
  }

  function checkMinimumPayment() {
    let minorder = $('.payment-form').data('minorder');
    let minpaymentbycurency = $('.payment-form').data('minpaymentbycurency');
    let value = $('#paymentAmount').val();

    if (isNaN(value)) {
      value = 0;
    }

    if (minpaymentbycurency >= minorder && minpaymentbycurency > value) {
      $('.purchase-warning').show();
    } else {
      $('.purchase-warning').hide();
    }
  }

  function formatValue(user_currency, value_in_gateway_currency) {
    let gateway_currency = $('.payment-form').data('gatewaycurrency');
    let gateway_currency_text = '';
    let lang = $('html').attr('lang') || 'en-GB';

    if (user_currency !== gateway_currency) {
      if (isNaN(value_in_gateway_currency)) {
        value_in_gateway_currency = 0;
      }

      let cur_formatter = new Intl.NumberFormat(lang, {
        style: 'currency',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        currency: gateway_currency || 'USD',
      });
      let format_gateway = cur_formatter.format(value_in_gateway_currency);

      gateway_currency_text = format_gateway;
    }

    return gateway_currency_text;
  }

  function changeOrderValue(value) {
    // This setting was set, but it should be gateway currency
    let user_currency = $('.payment-form').attr('data-currency');
    let gateway_currency_text = '';
    let lang = $('html').attr('lang') || 'en-GB';
    let cur_formatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      currency: user_currency || 'USD',
    });

    if (isNaN(value)) {
      value = 0;
    }
    value = parseFloat(value);

    let n = value.toString().match(/^(-?\d+)\.?(\d{0,2})?/)[1];
    let s = value.toString().match(/^(-?\d+)\.?(\d{0,2})?/)[2];
    let format = cur_formatter.format(value);

    if (typeof s == 'undefined') {
      s = '00';
    }

    if (value > 0) {
      $('#paymentSubmit span').removeClass('hidden-normal');
      let currency_in_gateway = $('#paymentCurrencyInGateway').val();
      if (currency_in_gateway !== user_currency) {
        let value_in_gateway_currency = $('#paymentAmountInGateway').val();
        gateway_currency_text = formatValue(
          user_currency,
          value_in_gateway_currency,
        );
        format += ' (' + gateway_currency_text + ')';
      }
    } else {
      $('#paymentSubmit span').addClass('hidden-normal');
    }

    $('#paymentSubmit span').text(format);
    $('#paymentAmount').val(value);
    $('#depositAmount').val(value);

    // HMM!!
    $('.entropay-info').data('pounds', n).data('cents', s);

    checkPaymentForm();
  }

  $('.payment-nav ul li').each(function (index) {
    $(this)
      .find('a')
      .click(function (e) {
        e.preventDefault();

        $('.payment-type-item')
          .addClass('hidden-normal')
          .find('input:text, .payment-selector')
          .prop('disabled', 'disabled');

        $('.payment-type-item')
          .eq(index)
          .removeClass('hidden-normal')
          .find('input:text, .payment-selector')
          .removeAttr('disabled');

        // Clear user selected currency when changing payment method
        $('#userSelectedCurrency').val('');
        /* Trigger change to refresh Pay button with correct currency.
         * If payment method does not support selecting currency then nothing will happen
         * Otherwise it calculates currency amount and sets required data in form and ui
         */
        $('.payment-type-item')
          .eq(index)
          .find('.userSelectedCurrencySelector')
          .trigger('change');

        $(this).parents('ul').find('li').removeClass('active');

        $(this).parent().addClass('active');

        let is_deposit = parseInt($('.payment-form').data('deposit'));
        let user_currency_code = $('.payment-form').data('currency');
        let gateway_currency_code = $(this).parent().data('gatewaycode');
        let converted_multiplier = parseFloat(
          $(this).parent().data('convertedmultiplier'),
        );

        let lang = $('html').attr('lang') || 'en-GB';
        let cur_formatter = new Intl.NumberFormat(lang, {
          style: 'currency',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
          currency: gateway_currency_code || 'USD',
        });

        let min_payment_by_method = parseFloat(
          $(this).parent('li').data('min_payment'),
        );

        let should_button_be_disabled = parseInt(
          $(this).parent('li').data('has_accept_terms_checkbox'),
        );

        if (should_button_be_disabled === 1) {
          $('#hasAcceptTermsCheckbox').val(1);
          if ($('.accept_term_and_conditions').length > 0) {
            $('.accept_term_and_conditions').prop('checked', false);
          }
        } else {
          $('#hasAcceptTermsCheckbox').val(0);
        }

        let min_order_form = parseFloat($('.payment-form').data('minorder'));
        let value_in_user_currency_temp = $('#paymentAmount').val();
        let value_temp = value_in_user_currency_temp.replace(',', '.');
        let value_in_user_currency = parseFloat(value_temp);
        let min_order = 0;

        let button_payment_hide = parseInt(
          $(this).parent('li').data('button_payment_hide'),
        );
        if (button_payment_hide === 1) {
          $('#paymentSubmit').css('display', 'none');
        } else {
          $('#paymentSubmit').css('display', 'initial');
        }

        $('#paymentCurrencyInGateway').val(gateway_currency_code);
        $('.payment-form').data('gatewaycurrency', gateway_currency_code);
        $('.payment-form').data('convertedmultiplier', converted_multiplier);

        if (is_deposit === 1) {
          $('.deposit-amount').each(function () {
            let deposit_value_user_cur = 0;
            let result_val = 0.0;
            let format = '';

            if ($(this).hasClass('platform-form')) {
              deposit_value_user_cur = $('#inputAmount').val();
            } else {
              deposit_value_user_cur = $(this).data('value');
            }

            let new_value = deposit_value_user_cur.toString().replace(',', '.');
            if (typeof new_value == 'undefined') {
              new_value = '0';
            }

            deposit_value_user_cur = new_value;

            if (user_currency_code !== gateway_currency_code) {
              $('.deposit-amount-gateway-currency').show();
              $('.deposit-amount').addClass('pull-up-deposit-amount');
              result_val = parseFloat(
                deposit_value_user_cur / converted_multiplier,
              );
              result_val = Math.round(result_val * 100) / 100;
            } else {
              $('.deposit-amount-gateway-currency').hide();
              $('.deposit-amount').removeClass('pull-up-deposit-amount');
              result_val = deposit_value_user_cur;
            }

            if (isNaN(result_val)) {
              result_val = 0;
            }

            format = cur_formatter.format(result_val);

            if ($(this).hasClass('platform-form')) {
              if (result_val === 0) {
                result_val = '0.00';
              }

              $('#deposit-amount-gateway')
                .data('gatewayvalue', result_val)
                .text(format);
            } else {
              $(this)
                .data('gatewaycurrencyvalue', result_val)
                .find('span')
                .text(format);
            }

            if ($(this).hasClass('deposit-amount-active')) {
              $('#paymentAmountInGateway').val(result_val);
              changeOrderValue(deposit_value_user_cur);
            }
          });
        } else {
          let total_order_value_gateway = parseFloat(
            $(this).parent().data('totalordervalueingateway'),
          );
          $('#paymentAmountInGateway').val(total_order_value_gateway);
          changeOrderValue(value_in_user_currency);
        }

        const paymentTypeString = $(this).parent().data('type') + '_' + $(this).parent().data('subtype');
        document.getElementById('paymentTypeMobile-radio-' + paymentTypeString).checked = true;

        $('#paymentType').val($(this).parent().data('type'));
        $('#paymentSubtype').val($(this).parent().data('subtype'));

        if ($(this).parent().data('bclass') !== 'undefined') {
          $('#paymentSubmit').data('bclass', $(this).parent().data('bclass'));
        } else {
          $('#paymentSubmit').removeData('bclass');
        }

        if (min_payment_by_method > min_order_form) {
          min_order = min_payment_by_method;
        }

        $('.payment-form').data('minpaymentbycurency', min_order);

        if (parseFloat(min_order) > parseFloat(value_in_user_currency)) {
          $('.purchase-warning').show();
        } else {
          $('.purchase-warning').hide();
        }

        checkPaymentForm();
      });
  });

  $('.payment-nav ul li.active a').click();

  $('input[name="paymentTypeMobile"]').change(function () {
    let type = $(this).val().split('_');
    $('.payment-nav ul li').removeClass('active');
    $('.payment-nav ul li').each(function () {
      if ($(this).data('type') == type[0] && $(this).data('subtype') == type[1]) {
        $(this).find('a').click();
      }
    });
    $('#newPaymentTypeMobile .payment-item').removeClass('active');
    $('#newPaymentTypeMobile .payment-item').each(function () {
      if ($(this).find('input').val() == `${type[0]}_${type[1]}`) {
        $(this).addClass('active');
      }
    });
    const activePaymentIndexMobile = $('input[name="paymentTypeMobile"]').closest('li').index($(this).closest('li'));
    setCookie('activePaymentIndexMobile', activePaymentIndexMobile, 900);
  });

  function checkPaymentForm() {
    let minorder = $('.payment-form').data('minorder');
    let cardminorder = $('.payment-form').data('cardminorder');

    if (isNaN(cardminorder)) {
      cardminorder = 0;
    }

    let minpaymentbycurency = $('.payment-form').data('minpaymentbycurency');

    if (minorder != null && parseFloat(minorder) > parseFloat(cardminorder)) {
      cardminorder = minorder;
    }

    let value = parseFloat($('#paymentAmount').val());
    if (isNaN(value)) {
      value = 0.0;
    }

    let disabled = value == 0.0 || parseFloat(minorder) > value;

    let card_disabled = value == 0.0 || parseFloat(cardminorder) > value;
    if (card_disabled == false) {
      $('.platform-alert-credit-card-warning').addClass('hidden-normal');
      $('.payment-cc-items').removeClass('hidden-normal');
    } else {
      $('.platform-alert-credit-card-warning').removeClass('hidden-normal');
      $('.payment-cc-items').addClass('hidden-normal');
    }

    if (minpaymentbycurency > value) {
      disabled = true;
    }

    // amount is ok, let's check CC
    if (
      shouldDisablePaymentButtonForAstroPay() ||
      ($('#paymentType').val() == 2 && (!checkCCData() || card_disabled)) ||
      $('#payment-method-ul li.active').data('deposit-only') == true
    ) {
      disabled = true;
    }

    if ($('#paymentType').val() == 1) {
      disabled = false;
    }

    $('#paymentSubmit').prop('disabled', disabled);

    let should_be_disabled = 0;
    if (disabled) {
      should_be_disabled = 1;
      if (
        parseInt($('#hasAcceptTermsCheckbox').val()) === 1 &&
        $('.accept_term_and_conditions').is(':checked')
      ) {
        $('.accept_term_and_conditions').prop('checked', false);
      }
    } else {
      if (parseInt($('#hasAcceptTermsCheckbox').val()) === 1) {
        if ($('.accept_term_and_conditions').is(':checked')) {
          $('#paymentSubmit').prop('disabled', false);
        } else {
          $('#paymentSubmit').prop('disabled', true);
        }
      }
    }

    $('#paymentButtonShouldBeDisabled').val(should_be_disabled);
  }

  /**
   * Check if payment button should be disabled for AstroPay.
   * @returns true if button should be disabled.
   */
  function shouldDisablePaymentButtonForAstroPay() {
    let warning_message = $('#astro-pay-unsupported-message'),
      is_payment_disabled = warning_message.length > 0;

    if (!is_payment_disabled) {
      return false;
    }

    let is_message_visible = !warning_message
      .parent()
      .hasClass('hidden-normal');

    return is_message_visible; // if message is visible button should be disabled.
  }

  $('.entropay-info a').click(function (e) {
    e.preventDefault();
    let pounds = $(this).parent().data('pounds');
    let cents = $(this).parent().data('cents');
    let attr = '';
    if (pounds != undefined && cents != undefined) {
      attr += parseInt(pounds) + '-' + parseInt(cents);
    } else {
      attr = '0-0';
    }
    document.location.href = $(this).attr('href') + attr;
  });

  $('.payment-form').on('submit', function (e) {
    if ($('#paymentSubmit').data('bclass') == 'entropay') {
      e.preventDefault();
      $('.payment-nav ul li[data-type="2"] a').click();
    }
  });

  function checkCCData() {
    let is_ok = true;

    if (!$('#paymentCCCard').length || $('#paymentCCCard').val() == 0) {
      if (
        !$('#paymentCCNumber').length ||
        $('#paymentCCNumber').val().length < 12
      ) {
        is_ok = false;
      }
      if (!$('#paymentCCCVV').length || $('#paymentCCCVV').val().length < 3) {
        is_ok = false;
      }
      if (
        !$('#paymentCCHolder').length ||
        $('#paymentCCHolder').val().length == 0
      ) {
        is_ok = false;
      }
    } else {
      if (!$('#paymentCCCVV').length || $('#paymentCCCVV').val().length < 3) {
        is_ok = false;
      }
    }

    return is_ok;
  }

  if ($('#paymentCCCard').length) {
    $('#paymentCCCard').change(function () {
      if ($(this).val() == 0) {
        $('#paymentCCNumber').parent().show();
        $('#paymentCCHolder').parent().show();
        $('#paymentCCExpirationDate').parent().show();
        $('#paymentCCSave').parent().parent().show();
      } else {
        $('#paymentCCNumber').parent().hide();
        $('#paymentCCHolder').parent().hide();
        $('#paymentCCExpirationDate').parent().hide();
        $('#paymentCCSave').parent().parent().hide();
      }
    });
  }

  $('#paymentCCNumber').change(checkPaymentForm).on('keyup', checkPaymentForm);
  $('#paymentCCCVV').change(checkPaymentForm).on('keyup', checkPaymentForm);
  $('#paymentCCHolder').change(checkPaymentForm).on('keyup', checkPaymentForm);
  $('#paymentCCCard').change(checkPaymentForm);

  $('.myaccount-filter select').change(function () {
    $(this).closest('form').submit();
  });

  $('.table-sort .tablesorter-header').click(function () {
    document.location.href = $(this).data('href');
  });

  $('.actual-lang').click(function (e) {
    e.preventDefault();
  });

  $('.widget-featured-pager').hover(
    function () {
      $(this).parent().data('block', 1);
    },
    function () {
      $(this).parent().data('block', 0);
    },
  );

  $('.widget-featured-item').hover(
    function () {
      $(this).parent().data('block', 1);
    },
    function () {
      $(this).parent().data('block', 0);
    },
  );

  $('.widget-featured-pager a').click(function (e) {
    e.preventDefault();
    let slider = $(this).closest('.widget-featured-content');
    let page_active = $(this);
    let prev = slider.data('active');
    let items = slider.find('.widget-featured-item');
    let next = $(this).data('index');
    slider.data('active', next);
    slider
      .find('.widget-featured-pager a')
      .removeClass('widget-featured-page-active');
    page_active.addClass('widget-featured-page-active');

    items.eq(prev).fadeOut('slow', function () {
      items.eq(next).fadeIn('slow');
    });
  });

  $('.small-widget-slider').data('active', 0);
  $('.small-widget-slider-page')
    .hover(
      function () {
        let slider = $(this).closest('.small-widget-slider');
        slider.data('block', 1);
        let active = parseInt($(this).text());
        slider
          .find('.small-widget-slider-page')
          .slice(0, active)
          .addClass('small-widget-slider-page-marked');
        slider
          .find('.small-widget-slider-page')
          .slice(active)
          .addClass('small-widget-slider-page-unmarked');

        slider
          .find('.small-widget-slider-pager-line-fill')
          .stop()
          .width('0%')
          .slice(0, active - 1)
          .width('100%');
      },
      function () {
        let slider = $(this).closest('.small-widget-slider');
        slider.data('block', 0);
        let active = slider.find('.small-widget-slider-page-active').length - 1;
        slider.data('active', active - 1);
        slider
          .find('.small-widget-slider-page')
          .removeClass('small-widget-slider-page-marked')
          .removeClass('small-widget-slider-page-unmarked');
        slider
          .find('.small-widget-slider-pager-line-fill')
          .stop()
          .width('0%')
          .slice(0, active)
          .width('100%');
      },
    )
    .click(function () {
      let slider = $(this).closest('.small-widget-slider');
      let active = parseInt($(this).text());
      slider.data('active', active);
      slider
        .find('.small-widget-slider-page')
        .removeClass('small-widget-slider-page-active')
        .slice(0, active)
        .addClass('small-widget-slider-page-active');

      slider_items[slider.data('index')].hide();
      slider_items[slider.data('index')]
        .eq(active - 1)
        .fadeIn(250, function () {});
    });

  $('a.faq-toggle').click(function (e) {
    e.preventDefault();
    $(this).find('.fa').toggleClass('fa-plus').toggleClass('fa-minus');
    $(this).parent().next().fadeToggle(200);
    $(this).parent().toggleClass('active');
  });

  if ($('.lotto-lightbox').length) {
    $('.lotto-lightbox').data('index', 1);
    lightbox.option({
      albumLabel: $('.lotto-lightbox').data('label'),
      fadeDuration: 200,
    });
  }

  $('.myaccount-scan-next').click(function (e) {
    e.preventDefault();
    let ind = $('.lotto-lightbox').data('index') + 1;
    if ($(this).hasClass('myaccount-scan-btn-disabled')) {
      return;
    }
    $('.lotto-lightbox').data('index', ind);
    $('.myaccount-scan-page span').text(ind);
    $('.myaccount-scan')
      .hide()
      .eq(ind - 1)
      .show();
    if (ind == $('.myaccount-scan').length) {
      $(this).addClass('myaccount-scan-btn-disabled');
    }
    $('.myaccount-scan-prev').removeClass('myaccount-scan-btn-disabled');
  });

  $('.myaccount-scan-prev').click(function (e) {
    e.preventDefault();
    let ind = $('.lotto-lightbox').data('index') - 1;
    if ($(this).hasClass('myaccount-scan-btn-disabled')) {
      return;
    }
    $('.lotto-lightbox').data('index', ind);
    $('.myaccount-scan-page span').text(ind);
    $('.myaccount-scan')
      .hide()
      .eq(ind - 1)
      .show();
    if (ind == 1) {
      $(this).addClass('myaccount-scan-btn-disabled');
    }
    $('.myaccount-scan-next').removeClass('myaccount-scan-btn-disabled');
  });

  $('.mobile-language').change(function () {
    document.location.href = $(this).val();
  });

  $('#results-mobile-sort').change(function () {
    let sort_data = $(this).val().split('_');
    $('#results-table').trigger('sorton', [[sort_data]]);
  });

  $('#results-table').bind('sortEnd', function (e) {
    let sort_list = e.target.config.sortList[0];
    $('#results-mobile-sort').val(sort_list.join('_'));
  });

  $('#info-mobile-sort').change(function () {
    let sort_data = $(this).val().split('_');
    $('#info-table').trigger('sorton', [[sort_data]]);
  });

  $('#info-table').bind('sortEnd', function (e) {
    let sort_list = e.target.config.sortList[0];
    $('#info-mobile-sort').val(sort_list.join('_'));
  });

  $('#footer-mobile-nav').change(function () {
    if ($(this).val() != '0') {
      document.location.href = $(this).val();
    }
  });

  $('#info-mobile-sort').change(function () {
    var sort_data = $(this).val().split('_');
    $('.table-info-details').trigger('sorton', [[sort_data]]);
  });

  $('#myaccount-tickets-mobile-sort').change(function () {
    document.location.href = $(this).val();
  });

  $('#info-table').bind('sortEnd', function (e) {
    let sort_list = e.target.config.sortList[0];
    $('#info-mobile-sort').val(sort_list.join('_'));
  });

  $('#faq-mobile-cats').change(function () {
    window.location.href = $(this).find('option:selected').data('link');
  });

  $('.menu-trigger').hover(
    function () {
      $(this).next().show();
    },
    function () {
      $(this).next().hide();
    },
  );

  $('.menu-wrapper')
    .hover(
      function () {
        $(this).show();
      },
      function () {
        $(this).hide();
      },
    )
    .hide();

  $('.order-info-delete-item').click(function (e) {
    e.preventDefault();
    let item = $(this);
    $.ajax({
      url: '/ajax/order-delete/' + $('.order-info-delete-item').index(item),
      cache: false,
    }).done(function (data) {
      if (data[0] == '1') {
        let data = data.split(':');
        $('span.order-count').text(data[1]);
        let discounted_sum = data[3];
        if (discounted_sum > 0) {
          $('span.order-info-amount').text(data[4]);
          $('.order-info-summary-amount #sum').text(data[2]);
          $('.order-info-summary-amount #sum').addClass('sum-discounted');
          $('.order-info-summary-amount #sum-full').text(data[4]);
        } else {
          $('span.order-info-amount').text(data[2]);
          $('.order-info-summary-amount #sum').text(data[2]);
          $('.order-info-summary-amount #sum').removeClass('sum-discounted');
          $('.order-info-summary-amount #sum-full').hide();
        }
        if (data[1] == 0) {
          $('.order-info-area .menu-wrapper').remove();
        }
        item.parent().fadeOut('fast', function () {
          $(this).remove();
        });
      }
    });
  });

  $('#newsCategorySelect').change(function () {
    window.location.href = $(this).val();
  });

  $('.lotto-toggle').click(function (e) {
    let text = $(this).text();
    e.preventDefault();
    $(this).next().fadeToggle('fast');
    $(this).text($(this).data('togglename'));
    $(this).data('togglename', text);
  });

  $('.myaccount-remove-dialog-close').click(function (e) {
    e.preventDefault();
    hidePopup();
    $('#myaccount-remove-confirmation-password').prop('value', '');
    $('.myaccount-remove-link')
      .prop('disabled', 'disabled')
      .addClass('disabled');
    $('#myaccount-remove-submit')
      .prop('value', '')
      .addClass('disabled')
      .prop('disabled', 'disabled');
  });

  $('#myaccount-remove-link').click(function (e) {
    e.preventDefault();

    $('#myaccount-remove-confirmation-password').prop('value', '');
    $('.myaccount-remove-link')
      .prop('disabled', 'disabled')
      .addClass('disabled');

    $('#dialog-title-div .pull-left').text($(this).data('title'));
    $('.dialog-content p')
      .eq(POPUP_CONFIRM_DELETE_ACCOUNT)
      .text($(this).data('confirm'));

    showPopup(POPUP_CONFIRM_DELETE_ACCOUNT);
  });

  $('#myaccount-remove-confirmation-password').on('keyup', function () {
    let curVal = $(this).val();
    let delButton = $('#myaccount-remove-submit');

    if (curVal.length > 0) {
      delButton.removeClass('disabled');
      delButton.removeAttr('disabled');
    } else {
      delButton.addClass('disabled');
      delButton.attr('disabled', 'disabled');
    }
  });

  $('#myaccount-refer-copytoclipboard').click(function (e) {
    e.preventDefault();
    let inputLink = $('#myaccount-refer-link');
    inputLink.select();
    document.execCommand('copy');
    inputLink.blur();
  });

  $('#myaccount-afflink-copytoclipboard').click(function (e) {
    e.preventDefault();
    let inputLink = $('#myaccount-aff-link');
    inputLink.select();
    document.execCommand('copy');
    inputLink.blur();
  });

  $('select.content-nav').change(function () {
    if ($(this).val() != '0' && $(this).val() != '') {
      window.location.href = $(this).val();
    }
  });

  function hidePopup() {
    $('body').removeClass('body-modal');
    $('#dialog-wrapper').animate({ opacity: 0 }, function () {
      $(this).hide();
    });
  }

  if ($('#dialog-wrapper').length) {
    if ($('#dialog-wrapper').hasClass('dialog-show')) {
      $('#dialog-wrapper').show();
    }
    let dialog = $('#dialog');
    dialog.css('marginLeft', -dialog.width() / 2);
    dialog.css('marginTop', -dialog.height() / 2);
  }

  function showPopup(num, args, dialogSelector = '#dialog') {
    $('#dialog-wrapper').stop(true);
    if (args != undefined && args.length > 0) {
      for (let i = 1; i <= args.length; i++) {
        $('.dialog-content', dialogSelector)
          .eq(num)
          .find('p')
          .text(
            $('.dialog-content', dialogSelector)
              .eq(num)
              .find('p')
              .text()
              .replace('%' + i + 's', args[i - 1]),
          );
      }
    }
    $('.dialog-title .pull-left', dialogSelector)
      .addClass('hidden-normal')
      .eq(num)
      .removeClass('hidden-normal');
    $('.dialog-content', dialogSelector)
      .addClass('hidden-normal')
      .eq(num)
      .removeClass('hidden-normal');
    $('#dialog-wrapper').css('opacity', 0).show().animate({ opacity: 1 });
    $('body').addClass('body-modal');
    let dialog = $('#dialog');
    dialog.css('marginLeft', -dialog.width() / 2);
    dialog.css('marginTop', -dialog.height() / 2);
  }

  function getMultiDrawPrice(price) {
    let multidraw_tickets = $('#inputMultiDraw')
      .find(':selected')
      .data('tickets');
    let multidraw_discount = $('#inputMultiDraw')
      .find(':selected')
      .data('discount');

    price = price * parseFloat(multidraw_tickets);
    price = price * ((100.0 - parseFloat(multidraw_discount)) / 100.0);
    price = price / parseFloat(multidraw_tickets);
    price = Math.round(price);
    price = price * parseFloat(multidraw_tickets);

    return price;
  }

  function calculatePrice(scroll) {
    if (scroll == undefined || scroll == null) {
      scroll = false;
    }

    if (!window.isCurrencySet) {
      return;
    }

    let pricing = $('.widget-ticket-content');
    let items = pricing
      .find('.widget-ticket-entity.checked')
      .not('.processing').length;
    let span = $('.widget-ticket-summary-content-total-value span');
    let currency_code = pricing.attr('data-currencycode');
    let lang = $('html').attr('lang') || 'en-GB';
    let ticket_type = $('#ticket_type:checked').val();
    let ticketMultiplier = $('#widget-ticket-stake-select').val() ?? null;

    let cur_formatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      currency: currency_code || 'USD',
    });

    if (scroll) {
      $('html, body').animate(
        {
          scrollTop:
            $('.widget-ticket-summary').offset().top -
            $(window).height() +
            $('.widget-ticket-summary').outerHeight(true) +
            30,
        },
        500,
      );
    }

    let price = 0;
    if (items > 0) {
      price = items * parseInt(pricing.attr('data-price'));
      if (ticket_type == 2) {
        price = getMultiDrawPrice(price);
      }
    }

    let format = pricing.attr('data-format');
    let n = Math.floor(price / 100);
    let s = price - n * 100;

    format = format.replace('{c}', pricing.attr('data-currency'));
    format = format.replace('{n}', n);
    format = format.replace('{s}', (s < 10 ? '0' : '') + s);

    let value = price / 100;
    if (ticketMultiplier > 1) {
      value *= ticketMultiplier;
    }
    format = cur_formatter.format(value);

    /* group price formatting */
    $('input[name="group_lottery"]').each(function () {
      let item_price = $(this).attr('data-price');
      if (ticket_type == 2) {
        let multidraw_tickets = $('#inputMultiDraw')
          .find(':selected')
          .data('tickets');
        item_price = getMultiDrawPrice(item_price) / multidraw_tickets;
      }
      $(this)
        .parent()
        .find('strong span')
        .text(cur_formatter.format(item_price / 100));
    });
    /* end of group price formatting */

    if (preventEnable == false) {
      $('.widget-ticket-summary-button').prop('disabled', false);
    }
    span.text(format);

    if (items == 0) {
      $('.widget-ticket-summary-button').prop('disabled', true);
    }
  }

  function disableTicketButtons() {
    $('.widget-ticket-button-quickpick').prop('disabled', true);
    $('.widget-ticket-quickpick-all').prop('disabled', true);
    $('.widget-ticket-button-clear').prop('disabled', true);
    $('.widget-ticket-clear-all').prop('disabled', true);
    $('.widget-ticket-summary-button').prop('disabled', true);
  }

  function enableKenoButtons() {
    $('.widget-ticket-button-quickpick-horizontal:disabled').prop('disabled', false);
    $('.widget-ticket-button-more-horizontal:disabled').prop('disabled', false);
    $('.widget-ticket-button-clear:disabled').prop('disabled', false);
    $('.widget-ball').css('pointer-events', 'auto').off('focus');
    $('#widget-ticket-slip-size-select').css('pointer-events', 'auto').off('focus');
    $('#widget-ticket-stake-select').css('pointer-events', 'auto').off('focus');
    $('.widget-ticket-summary-button').prop('disabled', false);
  }

  function enableTicketButtons() {
    if (preventEnable == false) {
      $('.widget-ticket-button-quickpick').prop('disabled', false);
      $('.widget-ticket-quickpick-all').prop('disabled', false);
      $('.widget-ticket-entity').each(function () {
        if ($(this).find('.checked').length) {
          $(this).find('.widget-ticket-button-clear').prop('disabled', false);
        }
      });
      $('.widget-ticket-clear-all').prop('disabled', false);
      $('.widget-ticket-summary-button').prop('disabled', false);
    }
  }

  intervals[1] = -1;

  let random_anim_count = 0;
  let g_tickets = null;
  let g_ticket = null;
  let g_callback = null;

  function selectRandomAnimationAll(tickets, callback) {
    g_callback = callback;
    if (intervals[1] == -1) {
      random_anim_count = 0;
      tickets.addClass('processing');
      g_tickets = tickets;
      intervals[1] = window.setInterval(
        selectRandomAnimationForAllTickets,
        200,
      );
    }
  }

  function selectRandomAnimation(ticket) {
    if (intervals[1] == -1) {
      ticket.addClass('processing');
      ticket.find('.checked').addClass('has-checked');
      random_anim_count = 0;
      g_tickets = $('.widget-ticket-entity.checked');
      g_tickets.addClass('has-checked');
      g_ticket = ticket;
      g_ticket.removeClass('has-checked');
      intervals[1] = window.setInterval(selectRandomAnimationForTicket, 200);
    }
  }

  function selectRandomAnimationForAllTickets() {
    random_anim_count++;
    g_tickets.each(function () {
      if ($(this).hasClass('hidden')) {
        return;
      }
      selectRandom($(this));
    });

    if (random_anim_count > 5) {
      enableTicketButtons();
      window.clearInterval(intervals[1]);
      intervals[1] = -1;
      g_tickets.removeClass('processing');
      checkMobileEntities();
      calculatePrice(true);
      if (g_callback != undefined) {
        g_callback();
      }
    }
  }

  function selectRandomAnimationForTicket() {
    random_anim_count++;
    if ($(this).hasClass('hidden')) {
      return;
    }
    selectRandom(g_ticket);
    if (random_anim_count > 5) {
      enableTicketButtons();
      window.clearInterval(intervals[1]);
      intervals[1] = -1;
      g_tickets.removeClass('has-checked');
      g_ticket.removeClass('processing');
      checkMobileEntities();
      calculatePrice();
    }
  }

  let g_mobiles = $('.widget-ticket-entity-mobile');
  let g_xtickets = $('.widget-ticket-entity');
  let g_xtickets_nc = [];
  let g_xtickets_bc = [];
  let g_nums = [];
  let g_bnums = [];

  g_xtickets.each(function (index) {
    g_nums[index] = $(this)
      .children('.widget-ticket-entity-content')
      .children('.widget-ticket-numbers')
      .children('a');
    g_bnums[index] = $(this)
      .children('.widget-ticket-entity-content')
      .children('.widget-ticket-bnumbers')
      .children('a');
    g_xtickets_nc[index] = $(this)
      .children('.widget-ticket-entity-content')
      .children('.widget-ticket-numbers');
    g_xtickets_bc[index] = $(this)
      .children('.widget-ticket-entity-content')
      .children('.widget-ticket-bnumbers');
  });

  function selectRandom(ticket) {
    let j = ticket.data('i');
    ticket.removeClass('checked');
    g_xtickets_nc[j].removeClass('checked');
    g_xtickets_bc[j].removeClass('checked');
    g_nums[j].removeClass('checked');
    g_bnums[j].removeClass('checked');

    let ncount = parseInt(ticket.parent().data('ncount'));
    let bcount = parseInt(ticket.parent().data('bcount'));
    let nrange = parseInt(ticket.parent().data('nrange'));
    let brange = parseInt(ticket.parent().data('brange'));
    let nrandom = getRandomValues(ncount, nrange);
    let brandom = getRandomValues(bcount, brange);
    let ticket_line = $('<div class="ticket-line"></div>');

    nrandom.sort(sortNumber);
    brandom.sort(sortNumber);

    for (let i = 0; i < ncount; i++) {
      ticket_line.append(
        $('<div class="ticket-line-number">' + nrandom[i] + '</div>'),
      );
      g_nums[j].eq(nrandom[i] - 1).addClass('checked');
    }
    for (let i = 0; i < bcount; i++) {
      ticket_line.append(
        $('<div class="ticket-line-bnumber">' + brandom[i] + '</div>'),
      );
      g_bnums[j].eq(brandom[i] - 1).addClass('checked');
    }
    ticket.addClass('checked');
    g_xtickets_nc[j].addClass('checked');
    g_xtickets_bc[j].addClass('checked');

    g_mobiles.eq(j).find('.ticket-line').remove();
    ticket_line.click(mobileNewLine);
    g_mobiles.eq(j).append(ticket_line);
    g_mobiles.eq(j).find('.widget-ticket-entity-newline').hide();
    g_mobiles.eq(j).find('.widget-ticket-entity-editline').hide();
    g_mobiles.eq(j).find('.widget-ticket-entity-mobile-quickpick').hide();
    g_mobiles.eq(j).find('.widget-ticket-entity-mobile-delete').show();
  }

  function sortNumber(a, b) {
    return a - b;
  }

  function toggleChecked(e) {
    e.preventDefault();
    let parent = $(this).parents('.widget-ticket-entity');
    let type = 1; // bonus
    if ($(this).parent().hasClass('widget-ticket-numbers')) {
      type = 0; // number
    }
    let nums = parent.find('.widget-ticket-numbers');
    let bnums = parent.find('.widget-ticket-bnumbers');

    let ncount = nums.find('.checked').length;
    let bcount = bnums.length ? bnums.find('.checked').length : 0;

    if (
      (type == 0 && ncount < parent.parent().data('ncount')) ||
      (type == 1 && bcount < parent.parent().data('bcount')) ||
      $(this).hasClass('checked')
    ) {
      if (!$(this).hasClass('checked')) {
        if (type == 0) {
          ncount++;
        } else {
          bcount++;
        }
      } else {
        if (type == 0) {
          ncount--;
        } else {
          bcount--;
        }
      }
      $(this).toggleClass('checked');
    } else {
      // TODO: alert
      return;
    }

    if (ncount == 0 && bcount == 0) {
      parent.find('.widget-ticket-button-clear').prop('disabled', true);
      $('.widget-ticket-clear-all').prop('disabled', true);
    } else {
      parent.find('.widget-ticket-button-clear').prop('disabled', false);
      $('.widget-ticket-clear-all').prop('disabled', false);
    }

    if (
      ncount == parent.parent().data('ncount') &&
      bcount == parent.parent().data('bcount')
    ) {
      parent.addClass('checked');
      let j = parent.data('i');
      let ticket_line = $('<div class="ticket-line"></div>');
      for (let i = 0; i < ncount; i++) {
        ticket_line.append(
          $(
            '<div class="ticket-line-number">' +
              nums.find('.checked').eq(i).text() +
              '</div>',
          ),
        );
      }
      for (let i = 0; i < bcount; i++) {
        ticket_line.append(
          $(
            '<div class="ticket-line-bnumber">' +
              bnums.find('.checked').eq(i).text() +
              '</div>',
          ),
        );
      }

      $('.widget-ticket-entity-mobile').eq(j).find('.ticket-line').remove();
      ticket_line.click(mobileNewLine);
      $('.widget-ticket-entity-mobile').eq(j).append(ticket_line);
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-entity-newline')
        .hide();
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-mobile-button a')
        .removeClass('disabled');
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-entity-editline')
        .hide();
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-entity-mobile-quickpick')
        .show();
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-entity-mobile-delete')
        .show();
      checkMobileEntities();
    } else {
      parent.removeClass('checked');
      let j = parent.data('i');
      $('.widget-ticket-entity-mobile').eq(j).find('.ticket-line').remove();
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-entity-newline')
        .show(); //? editline
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-mobile-button a')
        .addClass('disabled');
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-entity-mobile-quickpick')
        .show();
      $('.widget-ticket-entity-mobile')
        .eq(j)
        .find('.widget-ticket-entity-mobile-delete')
        .hide();
      //checkMobileEntities();
    }
    if (type == 0) {
      if (ncount == parent.parent().data('ncount')) {
        nums.addClass('checked');
      } else {
        nums.removeClass('checked');
      }
    }

    if (type == 1) {
      if (bcount == parent.parent().data('bcount')) {
        bnums.addClass('checked');
      } else {
        bnums.removeClass('checked');
      }
    }

    calculatePrice();
  }

  function update() {
    updateCountdowns();
    if (timer % 5 == 0) {
      updateCarousels(timer == 0 ? 1 : 0);
      updateSmallSliders(timer == 0 ? 1 : 0);
      updateSliders(timer == 0 ? 1 : 0);
    }
    timer++;
  }

  function updateSliders(prevent) {
    prevent = prevent == undefined ? 0 : prevent;
    bigsliders.each(function () {
      let slider = $(this);
      if (
        slider.data('block') != 1 &&
        slider.data('off') != 1 &&
        !prevent &&
        document.hasFocus()
      ) {
        let pages = slider.find('.widget-featured-pager a');
        let items = slider.find('.widget-featured-item');
        if (items.length > 1) {
          let item = slider.data('active');
          let prev = item;
          item++;
          if (item == pages.length) {
            item = 0;
          }
          slider.data('active', item);
          items.eq(prev).fadeOut('slow', function () {
            pages
              .removeClass('widget-featured-page-active')
              .eq(item)
              .addClass('widget-featured-page-active');
            items.eq(item).fadeIn('slow');
          });
        }
      }
    });
  }

  function updateSmallSliders(prevent) {
    prevent = prevent == undefined ? 0 : prevent;
    small_sliders.each(function (index) {
      let slider = $(this);
      if (slider_items[index] == undefined) {
        slider.data('active', 0);
        slider.data('index', index);
        slider_items[index] = $(this).find('.small-widget-slider-content-item');
      }
      if (slider.data('block') != 1) {
        let active = slider.data('active');
        if (!prevent) {
          let prev = active;
          active++;
          if (active > slider_items[index].length - 1) {
            active = 0;
            slider.find('.small-widget-slider-pager-line-fill').width('0%');
            slider
              .find('.small-widget-slider-page')
              .removeClass('small-widget-slider-page-active');
          }

          slider
            .find('.small-widget-slider-page')
            .eq(active)
            .addClass('small-widget-slider-page-active');
          slider.data('active', active);
          slider_items[index]
            .stop()
            .eq(prev)
            .fadeOut(250, function () {
              slider_items[index]
                .stop()
                .eq(active)
                .fadeIn(250, function () {});
            });
        }

        slider
          .find('.small-widget-slider-pager-line-fill')
          .eq(slider.data('active'))
          .animate({ width: '100%' }, 5000);
      }
    });
  }

  function updateCarousels(prevent) {
    prevent = prevent == undefined ? 0 : prevent;
    carousels.each(function (index) {
      let carousel = $(this);
      if (carousels_items[index] == undefined) {
        carousel.data('index', index);
        carousel.data('block', 0);
        carousels_items[index] = $(this).children('li.widget-list-ticket');
        carousels_items[index]
          .slice($(this).parent().data('showitems'))
          .css('opacity', 0);
      }
      if (!prevent) {
        moveCarousel(carousel);
      }
    });
  }

  function moveCarousel(carousel, ow_hover, prev) {
    ow_hover = ow_hover == undefined ? 0 : ow_hover;
    prev = prev == undefined ? 0 : prev;
    let index = carousel.data('index');
    if (
      carousel.data('block') == 0 &&
      carousel.data('off') == 0 &&
      carousels_items[index].length > carousel.parent().data('showitems') &&
      (!carousel.data('hover') || ow_hover)
    ) {
      carousel.data('block', 1);
      let bitems = [
        prev ? parseInt(carousel.parent().data('showitems')) : 0,
        prev ? 0 : parseInt(carousel.parent().data('showitems')),
      ];
      if (prev) {
        let item = carousels_items[index].last().detach();
        carousel.prepend(item);
        carousels_items[index] = carousel.children('li.widget-list-ticket');
        carousel.css(
          'left',
          -(
            carousels_items[index].eq(0).outerWidth(true) +
            parseInt(carousels_items[index].eq(1).css('marginLeft'))
          ),
        );
      }
      carousels_items[index].eq(bitems[0]).animate({ opacity: 0 }, 800);
      carousels_items[index]
        .eq(bitems[1])
        .delay(200)
        .animate({ opacity: 1 }, 800);
      let options = [];
      if (prev) {
        options = { left: 0 };
      } else {
        options = {
          left: -(
            carousels_items[index].eq(0).outerWidth(true) +
            parseInt(carousels_items[index].eq(1).css('marginLeft'))
          ),
        };
      }

      carousel.animate(options, 1000, 'swing', function () {
        if (!prev) {
          let item = carousels_items[index].eq(bitems[0]).detach();
          carousel.append(item);
          carousel.css('left', 0);
          carousels_items[index] = carousel.children('li.widget-list-ticket');
        }
        carousel.data('block', 0);
      });
    }
  }

  function getCountDiff(diff) {
    let count = [];
    count[0] = Math.floor(diff / INTERVAL_DAY);
    diff = diff - count[0] * INTERVAL_DAY;
    count[1] = Math.floor(diff / INTERVAL_HOUR);
    diff = diff - count[1] * INTERVAL_HOUR;
    count[2] = Math.floor(diff / INTERVAL_MINUTE);
    diff = diff - count[2] * INTERVAL_MINUTE;
    count[3] = Math.floor(diff / INTERVAL_SECOND);
    return count;
  }

  function updateCountdowns() {
    let now = new Date().getTime(); // convert to UTC
    big_countdowns.each(function (index_main) {
      if (typeof $(this).attr('datetime') === 'undefined') {
        return;
      }
      const datetime = $(this).attr('datetime');
      let timestamp = parseInt(datetime) * 1000;
      let diff = timestamp - now;
      let digits = [];
      let countdown = $(this);
      if (diff > 0) {
        let cnt_diff = getCountDiff(diff);

        digits[0] = Math.floor(cnt_diff[0] / 10);
        digits[1] = cnt_diff[0] - digits[0] * 10;
        digits[2] = Math.floor(cnt_diff[1] / 10);
        digits[3] = cnt_diff[1] - digits[2] * 10;
        digits[4] = Math.floor(cnt_diff[2] / 10);
        digits[5] = cnt_diff[2] - digits[4] * 10;
        digits[6] = Math.floor(cnt_diff[3] / 10);
        digits[7] = cnt_diff[3] - digits[6] * 10;
      } else {
        for (let i = 0; i <= 7; i++) {
          digits[i] = 0;
        }
      }
      if (big_countdowns_items[index_main] == undefined) {
        countdown.data('index', index_main);
        big_countdowns_items[index_main] = [
          $(this).find('.digit-next-up'),
          $(this).find('.digit-bottom'),
          $(this).find('.digit-next-bottom'),
          $(this).find('.digit'),
        ];
      }

      let test3d = has3d();
      big_countdowns_items[index_main][3].each(function (index) {
        let act = $(this);
        if (act.text() != digits[index]) {
          // check for 3D support (IE9+)
          if (test3d) {
            big_countdowns_items[index_main][2].eq(index).text(digits[index]);
            big_countdowns_items[index_main][0]
              .show()
              .eq(index)
              .text(digits[index]);
            $({ deg: 0 }).animate(
              { deg: 90 },
              {
                duration: 400,
                step: function (now) {
                  big_countdowns_items[index_main][1].eq(index).css({
                    transform: 'rotateX(' + now + 'deg)',
                  });
                },
                complete: function () {
                  $({ deg: -90 }).animate(
                    { deg: 0 },
                    {
                      duration: 400,
                      step: function (now) {
                        big_countdowns_items[index_main][0].eq(index).css({
                          transform: 'rotateX(' + now + 'deg)',
                        });
                      },
                      complete: function () {
                        big_countdowns_items[index_main][1]
                          .eq(index)
                          .text(digits[index]);
                        big_countdowns_items[index_main][3]
                          .eq(index)
                          .text(digits[index]);
                        if (index % 2 == 0) {
                          if (digits[index] == 0) {
                            big_countdowns_items[index_main][3]
                              .eq(index)
                              .addClass('desktop-only');
                          } else {
                            big_countdowns_items[index_main][3]
                              .eq(index)
                              .removeClass('desktop-only');
                          }
                        }
                        big_countdowns_items[index_main][1]
                          .eq(index)
                          .css({ transform: 'rotateX(0deg)' });
                        big_countdowns_items[index_main][0]
                          .eq(index)
                          .css({ transform: 'rotateX(90deg)' });
                      },
                    },
                  );
                },
              },
            );
          } else {
            big_countdowns_items[index_main][2].eq(index).text(digits[index]);
            big_countdowns_items[index_main][0].eq(index).text(digits[index]);
            big_countdowns_items[index_main][1].eq(index).text(digits[index]);
            big_countdowns_items[index_main][3].eq(index).text(digits[index]);
          }
        }
      });
    });

    small_countdowns.each(function () {
      if (typeof $(this).attr('datetime') === 'undefined') {
        return;
      }
      let timestamp = parseInt($(this).attr('datetime') * 1000);
      let diff = timestamp - now;
      let cnt_diff = getCountDiff(diff);
      let count_type = $(this).attr('data-count-type');
      let elems = $(this).find('.countdown-item');

      const isPassed = diff <= 0;
      if (isPassed) {
        const isNewNextDrawNotLoaded =
          $(this).attr('data-new-next-draw-loaded') !== 'true';
        // Load new nextDraw dates
        const isKeno = $(this).attr('data-lottery-slug').indexOf('-keno') >= 0;
        if (isNewNextDrawNotLoaded && isKeno) {
          $(this).attr('data-new-next-draw-loaded', 'true');
          updateNextDrawDatesAfterCountdown();
        }
        return;
      }

      $(this).attr('data-new-next-draw-loaded', 'false');

      if (parseInt(count_type) === 2) {
        // if less than an hour
        if (diff < 1000 * 60 * 60) {
          // set minutes
          elems.eq(0).text(cnt_diff[2]);
          // set seconds
          elems.eq(1).text(cnt_diff[3]);

          // if less than a day
        } else if (diff < 1000 * 60 * 60 * 24) {
          // set hours
          elems.eq(0).text(cnt_diff[1]);

          // set minutes
          elems.eq(1).text(cnt_diff[2]);
        } else {
          // set days
          elems.eq(0).text(cnt_diff[0]);

          // set hours
          elems.eq(1).text(cnt_diff[1]);
        }
      } else {
        if (diff > 0) {
          for (let i = 0; i <= 3; i++) {
            let df = cnt_diff.length - elems.length;
            if (i >= df) {
              elems.eq(i - df).text(cnt_diff[i]);
            }
          }
        } else {
          for (let i = 0; i <= 3; i++) {
            elems.eq(i).text(0);
          }
        }
      }
    });
  }
  window.refreshCountdowns = updateCountdowns;

  function has3d() {
    if (!window.getComputedStyle) {
      return false;
    }

    let el = document.createElement('p'),
      has3d,
      transforms = {
        webkitTransform: '-webkit-transform',
        OTransform: '-o-transform',
        msTransform: '-ms-transform',
        MozTransform: '-moz-transform',
        transform: 'transform',
      };

    // Add it to the body to get the computed style.
    document.body.insertBefore(el, null);

    for (let t in transforms) {
      if (el.style[t] !== undefined) {
        el.style[t] = 'translate3d(1px,1px,1px)';
        has3d = window.getComputedStyle(el).getPropertyValue(transforms[t]);
      }
    }

    document.body.removeChild(el);

    return has3d !== undefined && has3d.length > 0 && has3d !== 'none';
  }

  // MEDIA QUERIES
  // media query event handler
  if (matchMedia) {
    // for carousel display 1
    let mq = window.matchMedia('(min-width: 601px)');

    // for carousel display 2
    let mq3 = window.matchMedia('(min-width: 801px)');

    // for all carousels
    let mq4 = window.matchMedia('(min-width: 1221px)');

    // play lottery break 600 (mobile)
    let mq6 = window.matchMedia('(min-width: 601px)');

    // additional breaks
    // play lottery break 1220
    let mq7 = window.matchMedia('(max-width: 1220px)');

    // play lottery break 900
    let mq8 = window.matchMedia('(max-width: 900px)');

    mq.addListener(carouselMediaChangeD1);
    mq3.addListener(carouselMediaChangeD2);
    mq4.addListener(carouselMediaChange);
    mq6.addListener(playMediaChange);
    mq7.addListener(playMediaChange2);
    mq8.addListener(playMediaChange2);
    carouselMediaChangeD1(mq);
    carouselMediaChangeD2(mq3);
    carouselMediaChange(mq4);
    playMediaChange(mq6);
  }

  function playMediaChange(mq) {
    if (mq.matches) {
      play_mobile = false;
      checkPlayLines();
    } else {
      play_mobile = true;
      checkMobileEntities();
    }
  }

  function playMediaChange2() {
    checkPlayLines();
  }

  // media query change
  function carouselMediaChange(mq) {
    if (mq.matches) {
      carousels.data('margin', 0);
      // window width is at least 1220
    } else {
      // window width is less than 1220
      carousels.data('margin', 1);
    }
  }

  function carouselMediaChangeD1(mq) {
    if (mq.matches) {
      carousels.each(function () {
        if (
          !$(this)
            .parents('.widget-list-content')
            .hasClass('widget-list-display2')
        ) {
          let carousel = $(this);
          carousel.data('off', 0);
          carousel
            .children('li.widget-list-ticket')
            .slice(carousel.parent().data('showitems') - 1)
            .show();
        }
      });
    } else {
      carousels.each(function () {
        if (
          !$(this)
            .parents('.widget-list-content')
            .hasClass('widget-list-display2')
        ) {
          let carousel = $(this);
          carousel.data('off', 1);
          carousel
            .children('li.widget-list-ticket')
            .slice(carousel.parent().data('showitems'))
            .hide();
          carousel
            .children('li.widget-list-ticket')
            .slice(0, carousel.parent().data('showitems') - 1)
            .stop()
            .css('opacity', 1);
        }
      });
    }
  }

  function carouselMediaChangeD2(mq) {
    if (mq.matches) {
      carousels.each(function () {
        if (
          $(this)
            .parents('.widget-list-content')
            .hasClass('widget-list-display2')
        ) {
          let carousel = $(this);
          carousel.data('off', 0);
          carousel
            .children('li.widget-list-ticket')
            .slice(carousel.parent().data('showitems') - 1)
            .show();
        }
      });
    } else {
      carousels.each(function () {
        if (
          $(this)
            .parents('.widget-list-content')
            .hasClass('widget-list-display2')
        ) {
          let carousel = $(this);
          carousel.data('off', 1);
          carousel
            .children('li.widget-list-ticket')
            .slice(carousel.parent().data('showitems'))
            .hide();
          carousel
            .children('li.widget-list-ticket')
            .slice(0, carousel.parent().data('showitems') - 1)
            .stop()
            .css('opacity', 1);
        }
      });
    }
  }

  /* disallow multiple submits */
  $('.platform-form').submit(function () {
    $(this).find('input[type="submit"]').prop('disabled', true);
    $(this).find('button[type="submit"]').prop('disabled', true);
  });

  $('.payment-form #paymentSubmit').click(function (e) {
    if ($('input[name="input[promo_code]"]').val()) {
      let popup_data = $('#popup_message').data();
      let popup_from_queue = new Popup(
        popup_data['title'],
        popup_data['content'],
        [POPUP_BUTTON_OK],
      );
      popup_from_queue.show();
      e.preventDefault();
    }
    if (typeof gtag_checkout_events === 'function') {
      gtag_done = false;
      if (is_affiliate_gtag) {
        gtag_aff_done = false;
      }
      gtag_checkout_events(e);
    }
    if (typeof facebook_checkout_events === 'function') {
      facebook_done = false;
      facebook_checkout_events(e);
    }
  });

  let delayed_submit_should_run = true;
  let payment_form_is_submitted = false;

  function delayed_submit() {
    if (delayed_submit_should_run == false) {
      return;
    }
    gtag_done = facebook_done = gtag_aff_done = true;
    delayed_submit_should_run = false;
    if (!payment_form_is_submitted) {
      $('.payment-form').submit();
    }
  }

  $('.payment-form').submit(function (e) {
    // make sure the payment form will be submitted even if
    // pixels are blocked by a browser
    setTimeout(delayed_submit, 2000);
    $(this).find('button[type="submit"]').prop('disabled', true);
    if (!gtag_done || !facebook_done) {
      if (!is_affiliate_gtag || (is_affiliate_gtag && !gtag_aff_done)) {
        e.preventDefault();
        return;
      }
    }
    payment_form_is_submitted = true;
  });

  if ($('#newPaymentTypeMobile').is(':visible')) {
    selectFirstPaymentMobile();
  } else {
    if (!$('#payment-method-ul li').hasClass('active')) {
      selectFirstPaymentDesktop();
    }
  }

  /* NumberSelector (Keno-style numbers selection on play page */

  class TicketEntity {
    constructor(element) {
      this.element = element;
      this.numbersArray = []; // apparently getter has to have a different name than the property
    }

    // Get sorted array of numbers picked by the player
    get numbers() {
      this.sortNumbers();
      return this.numbersArray;
    }

    // Check if player picked a certain number
    contains(number) {
      return this.numbersArray.includes(number);
    }

    // Set number value of certain array element only if the new value is not to be found in the array already.
    // Returns true upon successful operation
    // Returns false when the new value has already been inserted into the array
    setNumber(index, value) {
      if (this.contains(value)) {
        // new value has already been inserted into the array, do nothing
        return false;
      }
      this.numbersArray[index] = value;

      return true;
    }

    // Get the size of the numbers array
    get numbersCount() {
      return this.numbersArray.length;
    }

    // Sort numbers array
    sortNumbers() {
      this.numbersArray = this.numbersArray.sort((a, b) => a - b);
    }

    // Clear numbers array
    clear() {
      this.numbersArray = [];
    }

    // Push new numeric value into the array
    push(element) {
      let number = parseInt(element);
      if (isNaN(number) || !isFinite(number)) {
        throw "Element that's being pushed into array is not a valid number";
      }
      if (number <= 0) {
        throw "Element that's being pushed into array is lesser than 1";
      }
      // TODO: Check if element is not greater than max allowed
      this.numbersArray.push(element);
    }

    // Populate the numbers array with random
    random(length, range) {
      this.clear();
      this.numbersArray = getRandomValues(length, range);
    }
  }

  class NumberSelector {
    constructor() {
      // var block
      this.selectors = {
        // Classes used in template wordpress/wp-content/themes/base/widget/ticket/lottery/keno.php
        numberSelector: '#number-selector',
        numbersWrapper: '.dialog-buttons',
        slipSizeSelect: '#widget-ticket-slip-size-select',
        ticketForm: '#widget-ticket-form',
        ticketEntitiesWrapper: '.widget-ticket-content',
        ticketEntity: '.widget-ticket-entity',
        ball: '.widget-ticket-number-selector',
        ballTemplate: '.widget-ticket-number-selector:first',
        numberSelectorBallWrapper: '.ticket-number-selector-ball-wrapper',
        numberSelectorBallTemplate:
          '.ticket-number-selector-ball-wrapper:first',
        nums: '.widget-ticket-numbers',
        quickpickButton: '.widget-ticket-button-quickpick-horizontal',
        moreButton: '.widget-ticket-button-more-horizontal',
        clearButton: '.widget-ticket-button-clear',
        numbersCount: '.pick_numbers_count',
        continueButton: '#play-continue',
        ticketMultiplierSelector: '#widget-ticket-stake-select',
      };
      // jQuery DOM objects
      this.dom = {};
      // jQuery DOM objects for cloning only
      this.templates = {};
      // Array of TicketEntity objects
      this.ticketEntities = [];
      // Flag which is set to true when init method fails (for easier debugging)
      this.failedToInit = false;
      // Number of ticket entities displayed on page load
      this.initialTicketEntitiesCount = 5;
      // Controls whether animation should be used or not
      this.animate = true;

      // Event handlers
      this.actions = {
        // When the ball is clicked
        ticketEntityBallClick: (e) => {
          e.preventDefault();
          let targetEntity = $(e.target);
          if (targetEntity.hasClass('widget-ticket-number-value')) {
            targetEntity = targetEntity.parent();
          }
          let firstBlankBall = targetEntity
            .parent()
            .find('.widget-ticket-number-value:empty')
            .first()
            .parent();
          let isThisBallBlank = targetEntity
            .find('.widget-ticket-number-value')
            .is(':empty');
          if (
            isThisBallBlank &&
            firstBlankBall.length &&
            !targetEntity.is(firstBlankBall)
          ) {
            firstBlankBall.click();
            return false;
          }
          this.popup(
            targetEntity
              .parents(numberSelector.selectors.ticketEntity)
              .index() - 1,
            targetEntity.index(),
            parseInt(targetEntity.text()),
          );
          return false;
        },

        // When the ticket multiplier changes (option gets selected)
        multiplierChange: () => {
          calculatePrice(false);
          this.updateButtonLinePrice();
        },

        // When the number in popup is clicked
        selectPopupNumber: (e) => {
          e.preventDefault();
          let targetNumber = e.target;
          let ticketEntityIndex = this.dom.numberSelector.attr(
            'data-ticket-entity-index',
          );
          let ballIndex = this.dom.numberSelector.attr('data-ball-index');
          this.selectNumber(
            ticketEntityIndex,
            ballIndex,
            parseInt($(targetNumber).text()),
          );
        },

        // When Quick-Pick button is clicked
        quickpick: (e) => {
          e.preventDefault();
          let targetEntityIndex =
            $(e.target).parents(this.selectors.ticketEntity).index() - 1;
          let targetEntity = this.ticketEntities[targetEntityIndex];
          let maxBalls =
            this.dom.ticketEntitiesWrapper.data('numbers_per_line');
          let nrange = parseInt(this.dom.ticketEntitiesWrapper.data('nrange'));
          targetEntity.random(maxBalls, nrange);
          this.drawBalls(targetEntityIndex);
        },

        // When continue button is clicked
        continue: () => {
          this.blockKenoAddMoreLine();
          this.blockKenoQuickPick();
          this.blockKenoPickers();
          this.blockKenoPlayNumbersSelect();
          this.blockKenoStakeSelect();
        },

        // When More button is clicked
        more: (e) => {
          e.preventDefault();
          this.addTicketEntity();
        },

        // When Clear button (trash bin) is clicked
        clearTicketEntity: (e) => {
          e.preventDefault();
          let targetEntityIndex =
            $(e.target).parents(this.selectors.ticketEntity).index() - 1;
          this.removeTicketEntity(targetEntityIndex);
        },

        // When ticket entity size is changed (select)
        sizeChange: (e) => {
          this.drawBalls();
          let targetValue = e.target.value;
          this.dom.ticketEntitiesWrapper.attr(
            'data-numbers_per_line',
            targetValue,
          );
        },
      };
      // var block end

      // Check things before init
      if (this.beforeInitCheck() === false) {
        this.failedToInit = true;
        return false;
      }
      // Initialize DOM elements
      this.initDom();
      let domTicketEntities = $(
        this.selectors.ticketEntity,
        this.dom.ticketEntitiesWrapper,
      );
      // Check things after initializing DOM elements
      try {
        if (this.afterInitCheck() === false) {
          throw 'Cannot create new NumberSelector object. After init check failed.';
        }
      } catch (ex) {
        this.failedToInit = true;
        return false;
      }
      // Initialize element templates
      this.initTemplates();
      // Draw ticket entities
      domTicketEntities.remove();
      for (let i = 0; i < this.initialTicketEntitiesCount; i++) {
        this.addTicketEntity();
      }
      // Bind events
      this.bindEvents();
      // Draw balls (animate all)
      this.drawBalls(false, false, true);
      this.maxBets = 10;
      this.updateButtonLinePrice();
    }

    // Initialize DOM elements
    initDom() {
      this.dom.numberSelector = $(this.selectors.numberSelector);
      this.dom.numbersWrapper = $(
        this.selectors.numbersWrapper,
        this.dom.numberSelector,
      );
      this.dom.slipSizeSelect = $(this.selectors.slipSizeSelect);
      this.dom.ticketForm = $(this.selectors.ticketForm);
      this.dom.ticketEntitiesWrapper = $(
        this.selectors.ticketEntitiesWrapper,
        this.dom.ticketForm,
      );
      this.dom.moreButton = $(this.selectors.moreButton);
      this.dom.continueButton = $(this.selectors.continueButton);
      this.dom.ticketMultiplierSelector = $(
        this.selectors.ticketMultiplierSelector,
      );
      $('option:last', this.dom.slipSizeSelect).prop('selected', true);
    }

    // Initialize element templates
    initTemplates() {
      this.templates.ball = $(
        $(this.selectors.ballTemplate).first()[0].outerHTML,
      );
      this.templates.numberSelectorBall = $(
        $(this.selectors.numberSelectorBallTemplate).first()[0].outerHTML,
      );
      this.templates.ticketEntity = $(
        $(
          this.selectors.ticketEntity,
          this.dom.ticketEntitiesWrapper,
        ).first()[0].outerHTML,
      );
    }

    // Bind event listeners
    bindEvents() {
      this.dom.slipSizeSelect.change(this.actions.sizeChange);
      this.dom.moreButton.bind('click', this.actions.more);
      this.dom.continueButton.bind('click', this.actions.continue);
      $('.dialog-close', this.dom.numberSelector).bind('click', () =>
        this.dom.numberSelector.hide(),
      );
      this.dom.ticketMultiplierSelector.bind(
        'change',
        this.actions.multiplierChange,
      );
    }

    // Validate ticket entity data before sending form
    validateTicketEntity(ticketEntityIndex) {
      return (
        this.ticketEntities[ticketEntityIndex].numbersCount ===
        parseInt(this.dom.slipSizeSelect.val())
      );
    }

    // Select number in popup
    selectNumber(ticketEntityIndex, ballIndex, newNumberValue) {
      let ticketEntity = this.ticketEntities[ticketEntityIndex];
      if (ticketEntity.setNumber(ballIndex, newNumberValue) === true) {
        this.dom.numberSelector.hide();
        this.drawBalls(
          ticketEntityIndex,
          ticketEntity.numbers.indexOf(newNumberValue),
        );
      }
      if (ticketEntity.numbersCount < parseInt(this.dom.slipSizeSelect.val())) {
        this.popup(ticketEntityIndex, ticketEntity.numbersCount);
      }
    }

    beforeInitCheck() {
      return $(this.selectors.numberSelector).length !== 0;
    }

    afterInitCheck() {
      let tempObj = { ...this.selectors, ...this.dom };
      for (let [, value] of Object.entries(tempObj)) {
        if (typeof value === 'undefined') {
          return false;
        }
      }

      return true;
    }

    // Add new TicketEntity object and draw
    addTicketEntity() {
      if (this.ticketEntities.length + 1 > this.maxBets) {
        return;
      }
      let ticketEntity = this.templates.ticketEntity.clone();
      let quickpick = $(this.selectors.quickpickButton, ticketEntity);
      let clearButton = $(this.selectors.clearButton, ticketEntity);
      quickpick.bind('click', this.actions.quickpick);
      clearButton.bind('click', this.actions.clearTicketEntity);
      this.dom.ticketEntitiesWrapper.append(ticketEntity);
      this.ticketEntities.push(new TicketEntity(ticketEntity));
      if (this.ticketEntities.length + 1 > this.maxBets) {
        this.dom.moreButton.prop('disabled', true);
      }
      this.drawBalls(this.ticketEntities.length - 1);
    }

    removeTicketEntity(targetEntityIndex) {
      this.ticketEntities.splice(targetEntityIndex, 1);
      $(this.selectors.ticketEntity, this.dom.ticketEntitiesWrapper)
        .eq(targetEntityIndex)
        .remove();
      this.dom.moreButton.prop('disabled', false);
      if (!this.ticketEntities.length) {
        this.addTicketEntity();
        return;
      }
      this.drawBalls();
    }

    getLinePriceFormatted() {
      let pricing = this.dom.ticketEntitiesWrapper;
      let currency_code = pricing.attr('data-currencycode');
      let lang = $('html').attr('lang') || 'en-GB';
      let price = parseFloat(pricing.attr('data-price')) / 100;
      let multiplier = this.dom.ticketMultiplierSelector.val();
      let cur_formatter = new Intl.NumberFormat(lang, {
        style: 'currency',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        currency: currency_code || 'USD',
      });

      return cur_formatter.format(price * multiplier);
    }

    updateButtonLinePrice() {
      $('strong', this.dom.moreButton).text(this.getLinePriceFormatted());
    }

    blockKenoAddMoreLine() {
      $('.widget-ticket-button-more-horizontal').prop('disabled', true);
    }

    blockKenoQuickPick() {
      $('.widget-ticket-button-quickpick-horizontal').prop('disabled', true);
    }

    blockKenoPickers() {
      $('.widget-ball')
        .css('pointer-events', 'none')
        .on('focus', function () {
          $(this).blur();
        });
    }

    blockKenoPlayNumbersSelect() {
      $('#widget-ticket-slip-size-select')
        .css('pointer-events', 'none')
        .on('focus', function () {
          $(this).blur();
        });
    }

    blockKenoStakeSelect() {
      $('#widget-ticket-stake-select')
        .css('pointer-events', 'none')
        .on('focus', function () {
          $(this).blur();
        });
    }

    // Draw balls on ticket entity
    drawBalls(
      animatedTicketEntityIndex = false,
      animatedBallIndex = false,
      animateAll = false,
    ) {
      let maxBalls = this.dom.ticketEntitiesWrapper.data('numbers_per_line');
      let selectedNumbersPerLine = this.dom.slipSizeSelect.val();
      $(this.selectors.numbersCount).text(selectedNumbersPerLine);
      $.each(this.ticketEntities, (i, v) => {
        const ballsWrapper = v.element
          .find(this.selectors.ballTemplate)
          .parent();
        ballsWrapper.children().remove();
        v.numbersArray = v.numbers.slice(0, selectedNumbersPerLine);
        for (let j = 0; j < maxBalls; j++) {
          let ballElement = this.templates.ball.clone();
          if (j >= selectedNumbersPerLine) {
            ballElement.addClass('disabled');
          } else {
            ballElement.bind('click', this.actions.ticketEntityBallClick);
          }
          ballElement
            .find('.widget-ticket-number-value')
            .text(v.numbers[j] ?? '');
          ballsWrapper.append(ballElement);
          if (
            this.animate !== false &&
            (animateAll === true || animatedTicketEntityIndex !== false)
          ) {
            let animation = () => {
              ballElement.addClass('fade-in-down');
            };
            if (
              animateAll === true ||
              (animatedTicketEntityIndex === i &&
                (animatedBallIndex === false || animatedBallIndex === j))
            ) {
              let delay = 0;
              const ballDelay = 35;
              if (animatedBallIndex === false) {
                delay = j * ballDelay;
              }
              setTimeout(animation, delay);
            }
          }
        }
        $(v.element)
          .removeClass('checked')
          .addClass(this.validateTicketEntity(i) ? 'checked' : '');
        calculatePrice(false);
      });
    }

    // Prepare & display number selection popup
    popup(ticketEntityIndex, ballIndex, value) {
      let ticketEntity = this.ticketEntities[ticketEntityIndex];
      let ticketEntityNumbers = ticketEntity.numbers;
      this.dom.numberSelector.attr(
        'data-ticket-entity-index',
        ticketEntityIndex,
      );
      this.dom.numberSelector.attr('data-ball-index', ballIndex);
      this.dom.numberSelector.show();
      $(
        this.selectors.numberSelectorBallWrapper,
        this.dom.numberSelector,
      ).remove();
      const ballsWrapper = $('.dialog-buttons', this.dom.numberSelector);
      let maxBalls = this.dom.ticketEntitiesWrapper.data('nrange');
      for (let i = 0; i < maxBalls; i++) {
        let ballNumber = i + 1;
        const ballWrapper = this.templates.numberSelectorBall.clone();
        const ball = $('>a', ballWrapper);
        ball.text(ballNumber);
        if (ticketEntityNumbers.includes(ballNumber) !== false) {
          ball.addClass('selected');
        }
        ball.bind('click', this.actions.selectPopupNumber);
        ballsWrapper.append(ballWrapper);
      }
      const changingBall = $(
        this.selectors.numberSelectorBallWrapper,
        ballsWrapper,
      ).eq(value - 1);
      $('>a', changingBall).addClass('changing');
    }
  }

  if ($('#number-selector').length > 0) {
    numberSelector = new NumberSelector();
  }

  let detailedResultsTable = $('.table-results-detailed');

  function parseFloatAlt(val) {
    if (isNaN(val)) {
      if ((val = val.match(/([0-9,]+\d)/g))) {
        val = val[0].replace(/[^\d]+/g, '');
      }
    }
    return parseFloat(val);
  }

  function drawDetailedResultsTableWithMultipliers(multiplier) {
    $('tbody tr', detailedResultsTable).removeClass('active').hide();
    $('tbody tr[data-multiplier="' + multiplier + '"]')
      .addClass('active')
      .show();
    let winnersTotal = 0;
    let prizesTotal = 0;
    $('tbody .active', detailedResultsTable).each(function () {
      winnersTotal += parseFloatAlt(
        $('.table-results-detailed-winners', this).text(),
      );
      prizesTotal += parseFloatAlt(
        $(
          '.table-results-detailed-jackpot .table-results-detailed-amount',
          this,
        ).text(),
      );
    });
    $('tfoot .table-results-detailed-winners', detailedResultsTable).text(
      winnersTotal,
    );

    let lang = $('html').attr('lang') || 'en-GB';
    let currency_code = detailedResultsTable.attr('data-currency');
    let cur_formatter = new Intl.NumberFormat(lang, {
      style: 'currency',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      currency: currency_code || 'USD',
    });
    $(
      'tfoot .table-results-detailed-jackpot .table-results-detailed-amount',
      detailedResultsTable,
    ).text(cur_formatter.format(prizesTotal));
  }

  $('#results-detailed-multiplier').change(function () {
    drawDetailedResultsTableWithMultipliers($(this).val());
  });

  if ($('#results-detailed-multiplier').length > 0) {
    drawDetailedResultsTableWithMultipliers(10);
  }

  bindWinningsTableMultiplierEvents();

  if (isNotWordpressAdminPage()) {
    initBasketData();
    fetchBasketItems();
    initPopupFromQueue(Popup);
    initFirstVisitPopup(Popup);
    initInfoBoxes();
    initLotteryResultsPage();
    initSpecificRafflePage();
    initRaffleResultsPage();
    initAppDownloadBanner();
  }

  if (isNotCasino()) {
    initLotteryData().then(() => {
      const lotteryElement = document.getElementById('orderLotteryId');
      const lotteryId = lotteryElement ? parseInt(lotteryElement.value) : null;
      const isMiniLoteries = lotteryId !== null && MINI_LOTTERIES_IDS.includes(lotteryId);

      handleQuickPick(isMiniLoteries ? 10 : 3);

      if (play_mobile) {
        calculatePrice();
      }
    });
  }
}

function getRandomValues(count, range) {
  var final = new Array();
  if (count == 0) {
    return final;
  }
  var random = null;
  do {
    random = Math.floor(Math.random() * range + 1);
    if ($.inArray(random, final) == -1) {
      final.push(random);
    }
  } while (final.length < count);
  return final;
}

jQuery('#form-promo-code').on('submit', function () {
  const activePayment = document.querySelector(
    '#payment-method-ul > li.active',
  );
  const activePaymentIndex = Array.from(
    activePayment.parentElement.children,
  ).indexOf(activePayment);

  setCookie('activePaymentIndex', activePaymentIndex, 900);
});

function selectFirstPaymentMobile() {
  if (isCookieSet('activePaymentIndexMobile')) {
    const activePaymentIndexMobile = getCookie('activePaymentIndexMobile');
    $('input[name="paymentTypeMobile"]')
      .eq(activePaymentIndexMobile)
      .prop('checked', true)
      .trigger('change');
    deleteCookie('activePaymentIndexMobile');
  } else {
    $('input[name="paymentTypeMobile"]')
      .eq(0)
      .prop('checked', true)
      .trigger('change');
    $('#newPaymentTypeMobile .payment-item').eq(0).addClass('active');
  }
}

function selectFirstPaymentDesktop() {
  if (isCookieSet('activePaymentIndex')) {
    const activePaymentIndex = getCookie('activePaymentIndex');
    $('#payment-method-ul li').eq(activePaymentIndex).find('a').click();
    deleteCookie('activePaymentIndex');
  } else {
    $('#payment-method-ul li').eq(0).find('a').click();
  }
}

// Countdowns
const Countdown = function (element, isKeno = false) {
  this.element = $(element);
  this.countdownDays = $('.days', this.element);
  this.countdownHours = $('.hours', this.element);
  this.countdownMinutes = $('.minutes', this.element);
  this.countdownSeconds = $('.seconds', this.element);
  this.countdownSeparatorHours = $('.separator-hours', this.element);

  this.update = (distance) => {
    let days = parseInt(distance / (60 * 60 * 24));
    let hours = parseInt((distance % (60 * 60 * 24)) / (60 * 60));
    let minutes = parseInt((distance % (60 * 60)) / 60);
    let seconds = parseInt(distance % 60);

    this.countdownDays.html(days);

    if (isKeno) {
      if (hours === 0) {
        this.countdownHours.css('display', 'none');
        this.countdownSeparatorHours.css('display', 'none');
      }
      if (hours > 0) {
        this.countdownHours.html(hours.toString().padStart(2, '0'));
        this.countdownHours.css('display', '');
        this.countdownSeparatorHours.css('display', '');
      }
    } else {
      this.countdownHours.html(hours);
    }

    this.countdownMinutes.html(minutes.toString().padStart(2, '0'));
    this.countdownSeconds.html(seconds.toString().padStart(2, '0'));
  };

  this.getNewTimestamp = async () => {
    const url = getPreparedApiUrl('lottery');
    const searchParams = url.searchParams;
    let lotterySlug = window.lotterySlug;
    if (isMainPlayPage()) {
      lotterySlug = this.element.attr('data-lottery-slug');
    }
    searchParams.set('lotterySlug', lotterySlug);
    const isNotOrderOrSuccessPage = lotterySlug !== 'success';
    if (isNotOrderOrSuccessPage) {
      const response = await fetch(url, { credentials: 'include' });
      const { nextRealDrawTimestamp } = await response.json();

      // Set new timestamp in DOM element
      this.element.attr('datetime', nextRealDrawTimestamp);

      // Restart function
      this.init();
    }
  };

  this.init = () => {
    this.handler = setInterval(this.refresh, 1000);
    $(this.element).css('visibility', 'visible');
  };

  this.stop = () => {
    clearInterval(this.handler);
  };

  this.refresh = () => {
    let drawDate = this.element.attr('datetime');
    let now = Math.floor(Date.now() / 1000);
    let distance = drawDate - now;
    let repeat = this.element.attr('repeatMoreThanOnce');

    if (distance <= 0) {
      this.stop();
      this.countdownDays.html(0);
      this.countdownHours.html(0);
      this.countdownMinutes.html('00');
      this.countdownSeconds.html('00');
      if (repeat !== 'false') {
        this.getNewTimestamp();
      }
    } else {
      this.update(distance);
    }
  };

  return this.init();
};

$(function () {
  let kenoCountdowns = [];
  $('.countdown').each(function () {
    if ($(this).hasClass('keno-countdown')) {
      kenoCountdowns.push(new Countdown($(this), true));
    } else {
      kenoCountdowns.push(new Countdown($(this)));
    }
  });
  window.kenoCountdowns = kenoCountdowns;
});

const selectWithdrawalType = document.querySelector(
  '.platform-form-withdrawal #inputWithdrawalType',
);

if (selectWithdrawalType) {
  const minWithdrawalValueElement = document.querySelector(
    '#minWithdrawalValue',
  );
  const inputWithdrawalAmountDefaultHtml = minWithdrawalValueElement.innerHTML;

  const applyMultiplication = () => {
    const isBtcSelected = selectWithdrawalType.value === '4';
    const inputWithdrawalAmount = document.querySelector(
      '#inputWithdrawalAmount',
    );

    if (isBtcSelected) {
      // change agreed with the business
      const minWithdrawalValueHtml = minWithdrawalValueElement.innerHTML;
      const regex = /\d+(\.\d+)?(,\d+)?/;
      const match = minWithdrawalValueHtml.match(regex);

      if (match) {
        const foundNumber = parseFloat(match[0]);
        const multipliedNumber = foundNumber * 2;
        inputWithdrawalAmount.min = multipliedNumber;
        minWithdrawalValueElement.innerHTML = minWithdrawalValueHtml.replace(
          regex,
          multipliedNumber.toFixed(2),
        );
      }
    } else {
      inputWithdrawalAmount.removeAttribute('min');
      minWithdrawalValueElement.innerHTML = inputWithdrawalAmountDefaultHtml;
    }
  };

  applyMultiplication();
  selectWithdrawalType.addEventListener('change', applyMultiplication);
}

$('#paymentSubmit').on('click', function () {
  if (!window.dataLayer ||  !Array.isArray(window.dataLayer)) {
    return;
  }
  const beginCheckoutData = window.beginCheckoutData || {};

  // Sending of the "begin_checout" event
  // See commented method: @see \Events_User_Cart_Checkout::run
  window.dataLayer.push(beginCheckoutData);
});

// LIVE SEARCH
(function() {
  const searchInput = document.getElementById('wl-search-input');
  const searchBtnReset = document.getElementById('wl-search-btn-reset');
  const allBoxesLotteries = document.querySelectorAll('.widget-list-grid .widget-list-ticket, .minigames-row .minigames-item');

  function searchLotteries() {
    const dataRegex = new RegExp(this.value,'i');

    allBoxesLotteries.forEach(function(e) {
      const dataText = e.querySelector('.widget-list-link, .minigames-title-name').textContent.trim();

      if (dataText.search(dataRegex) < 0) {
        e.style.display = 'none';
      } else {
        e.style.removeProperty('display');
      }
    });
  }

  function searchLotteriesReset() {
    searchInput.value = '';

    allBoxesLotteries.forEach(function(e) {
      e.style.removeProperty('display');
    });
  }

  if (searchInput && searchBtnReset && allBoxesLotteries.length > 0) {
    searchInput.addEventListener('keyup', searchLotteries);
    searchBtnReset.addEventListener('click',searchLotteriesReset);
  }
})();
