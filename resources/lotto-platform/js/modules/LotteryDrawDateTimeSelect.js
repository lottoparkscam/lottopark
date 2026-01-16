import { getPreparedApiUrl } from '../../../js/Helpers/UrlHelper';
import {
  bindWinningsTableMultiplierEvents,
  setRefreshedLotteryResultsDetails,
} from './LotteryResults';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/l10n';

export default class LotteryDrawDateTimeSelect {
  constructor(lotteryName, lotteryLanguage = 'en') {
    this.drawDates = [];
    this.drawTimes = [];
    this.lotteryName = lotteryName;
    this.lotteryLanguage = lotteryLanguage;
    this.selectedDrawDate = '';
    this.selectedDrawTime = '';
    this.flatpickrInstance = null;
    this.flatpickrOptions = {
      weekday: 'long',
      hour: 'numeric',
      minute: 'numeric',
      hour12: true,
    };
    this.init();
  }

  async fetchDrawData() {
    const url = getPreparedApiUrl(
      `lotteryResult?lotteryName=${this.lotteryName}&drawDate=${this.selectedDrawDate}&drawDateTime=${this.selectedDrawTime}&language=${this.language}`,
    );

    const response = await fetch(url);
    const data = await response.json();
    this.drawDates = data.drawDates;
    this.drawTimes = data.drawTimes;

    this.renderDrawTimes();
    if (this.flatpickrInstance) {
      this.flatpickrInstance.set('disable', [
        (date) => !this.drawDates.includes(flatpickr.formatDate(date, 'Y-m-d'))
      ]);
    } else {
      this.setupDateSelect();
    }

    this.setupTimeSelect();
    setRefreshedLotteryResultsDetails(data);
    bindWinningsTableMultiplierEvents();
  }

  init() {
    this.fetchDrawData().then(() => {
      const selectedDateElement = document.querySelector('#selectedDate');
      selectedDateElement.innerHTML = this.flatpickrInstance.formatDate(
        new Date(this.drawDates[0]),
        'M j, Y',
      );
      this.refreshDefaultTimeLabel();
    });
  }

  setupDateSelect() {
    const dateSelectElement = document.getElementById('date-select');
    if (!dateSelectElement) {
      return;
    }

    if (this.flatpickrInstance) {
      return;
    }

    const headerElement = dateSelectElement.querySelector('.datetime-dropdown-header');
    const timeSelectElement = document.querySelector('#time-select .datetime-dropdown-header-value');
    const selectedDateElement = document.querySelector('#selectedDate');
    this.flatpickrInstance = flatpickr(dateSelectElement, {
      dateFormat: 'M j, Y',
      parseDate: function (datestr, format) {
        return flatpickr.parseDate(datestr, 'M j, Y');
      },
      locale: this.lotteryLanguage,
      clickOpens: false,
      onChange: (selectedDates, dateStr, instance) => {
        this.selectedDrawDate = instance.formatDate(selectedDates[0], 'Y-m-d');
        this.selectedDrawTime = '';
        if (timeSelectElement) {
          timeSelectElement.innerHTML = lotteryChooseText;
        }
        selectedDateElement.innerHTML = instance.formatDate(
          selectedDates[0],
          'M j, Y',
        );
        this.fetchDrawData().then(() => {
          this.refreshDefaultTimeLabel();
        });
      },
      onDayCreate: (dObj, dStr, fp, dayElem) => {
        if (this.drawDates.includes(fp.formatDate(dayElem.dateObj, 'Y-m-d'))) {
          dayElem.innerHTML += '<span class="event"></span>';
        }
      },
      disable: [
        (date) => !this.drawDates.includes(flatpickr.formatDate(date, 'Y-m-d')),
      ],
    });

    if (!this.headerClickListenerAttached) {
      headerElement.addEventListener('click', () => {
        this.flatpickrInstance.toggle();
      });
      this.headerClickListenerAttached = true;
    }
  }

  setupTimeSelect() {
    const timeSelectElement = document.getElementById('time-select');
    if (!timeSelectElement) {
      return;
    }
    const dropdownHeader = timeSelectElement.querySelector(
      '.datetime-dropdown-header',
    );
    const dropdownItems = timeSelectElement.querySelectorAll(
      '.datetime-dropdown-menu li',
    );

    dropdownHeader.removeEventListener('click', this.handleDropdownClick);
    dropdownItems.forEach((item) => {
      item.removeEventListener('click', this.handleDropdownItemClick);
    });
    document.removeEventListener('click', this.handleDocumentClick);

    dropdownHeader.addEventListener('click', this.handleDropdownClick);
    dropdownItems.forEach((item) => {
      item.addEventListener('click', this.handleDropdownItemClick);
    });
    document.addEventListener('click', this.handleDocumentClick);
  }

  handleDropdownClick(event) {
    const timeSelectElement = document.getElementById('time-select');
    timeSelectElement.classList.toggle('active');
    event.stopPropagation();
  }

  handleDropdownItemClick = (event) => {
    const timeSelectElement = document.getElementById('time-select');
    const activeItem = timeSelectElement.querySelector(
      '.datetime-dropdown-menu li.active',
    );
    if (activeItem) {
      activeItem.classList.remove('active');
    }
    const item = event.target;
    item.classList.add('active');
    let formattedTime = item.dataset.timeFormatted;
    timeSelectElement.querySelector(
      '.datetime-dropdown-header-value',
    ).innerHTML = formattedTime.substring(formattedTime.indexOf(' ') + 1);
    this.selectedDrawTime = item.dataset.date;
    this.fetchDrawData();
    timeSelectElement.classList.remove('active');
  };

  handleDocumentClick(event) {
    const timeSelectElement = document.getElementById('time-select');
    if (!timeSelectElement.contains(event.target)) {
      timeSelectElement.classList.remove('active');
    }
  }

  renderDrawTimes() {
    const dropdownTimeList = document.querySelector(
      '#time-select .datetime-dropdown-menu',
    );

    if (!dropdownTimeList) {
      return;
    }

    dropdownTimeList.innerHTML = '';
    this.drawTimes.forEach((time) => {
      const date = new Date(time);
      const timeString = date
        .toLocaleDateString(this.lotteryLanguage, this.flatpickrOptions)
        .replace(/^\w/, (c) => c.toUpperCase());

      const listItem = document.createElement('li');
      if (this.drawTimes[0] === time) {
        listItem.classList.add('active');
      }
      listItem.innerText = timeString;
      listItem.dataset.date = time.replace(/[-:\s]/g, '');
      listItem.dataset.timeFormatted = timeString;

      dropdownTimeList.appendChild(listItem);
    });
  }

  refreshDefaultTimeLabel() {
    const datetimeDropdownHeaderValueElement = document.querySelector(
      '#time-select .datetime-dropdown-header-value',
    );
    if (datetimeDropdownHeaderValueElement) {
      let latestTime = new Date(this.drawTimes[0])
        .toLocaleDateString(this.lotteryLanguage, this.flatpickrOptions)
        .replace(/^\w/, (c) => c.toUpperCase());
      datetimeDropdownHeaderValueElement.innerText =
        latestTime.split(' ')[1] + ' ' + latestTime.split(' ')[2];
    }
  }
}
