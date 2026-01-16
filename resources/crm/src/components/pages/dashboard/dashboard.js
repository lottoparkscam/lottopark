import React from 'react';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Analytics from '../../elements/dashboard_elements/analytics';
import DonutChart from '../../elements/donutChart';
import Map from '../../elements/dashboard_elements/map';
import CountryStats from '../../elements/dashboard_elements/countryStats';
import Chart from '../../elements/chart';
import ChartistTooltip from 'chartist-plugin-tooltips-updated';
import Legend from 'chartist-plugin-legend';
import Loading from '../../elements/loading';
import DateRangePicker from '@wojtekmaj/react-daterange-picker/dist/entry.nostyle';

class Dashboard extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      date: [new Date(), new Date()],
      selectDateValue: 'month',
      showDatePicker: false,
      whitelabel_id: null,
      registeredData: [],
      ftdData: [],
      ftdCount: 0,
      ftpData: [],
      ftpCount: 0,
      depositsData: [],
      soldTicketsData: [],
      paymentGatewaysData: [],
      requestedWithdrawalData: [],
      acceptedWithdrawalData: [],
      wonTicketsData: [],
      ftpTicketsAmount: 0,
      stpTicketsAmount: 0,
      amountData: [],
      costData: [],
      incomeData: [],
      registeredCount: 0,
      depositsCount: 0,
      soldTicketsLinesCount: 0,
      paymentGatewaysSumCount: 0,
      paymentGatewaysDetailsData: [],
      requestedWithdrawalSumCount: 0,
      acceptedWithdrawalSumCount: 0,
      soldTicketsAmount: 0,
      wonTicketsCount: 0,
      amountCount: 0,
      costCount: 0,
      incomeCount: 0,
      topLotteries: [],
      topCountries: [],
      topLanguages: [],
      whitelabels: [],
      topLotteriesData: [['', 1]],
      ticketsFtpStpData: [['', 1]],
      whitelabelsData: [['', 1]],
      chartData: null,
      usersChartData: null,
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    const whitelabel_id = this.context.choosedWhitelabel.id;
    const config = this.context.user.config_data;
    let selectDateValue = this.state.selectDateValue;

    if (
      config &&
      config !== selectDateValue &&
      (config == 'month' ||
        config == 'lastmonth' ||
        config == 'year' ||
        config == '30days' ||
        config == 'today' ||
        config == 'yesterday' ||
        config == 'range')
    ) {
      selectDateValue = config;
      if (config == 'range') {
        this.setState({ selectDateValue, showDatePicker: true });
      } else this.setState({ selectDateValue });
    }

    this.setState({ whitelabel_id }, () => {
      this.setDays();
    });
  }

  componentDidUpdate() {
    if (this.state.whitelabel_id !== this.context.choosedWhitelabel.id) {
      this.setState(
        {
          loading: true,
          whitelabel_id: this.context.choosedWhitelabel.id,
        },
        () => {
          this.setDays();
        },
      );
    }
  }

  fetchData(start_date, end_date, option) {
    const whitelabel_id = this.state.whitelabel_id;
    const { gettext } = this.context.textdomain;
    const language_code = this.context.user.code;
    let start =
      start_date.getFullYear() +
      '-' +
      (parseInt(start_date.getMonth()) + 1) +
      '-' +
      start_date.getDate();
    let end =
      end_date.getFullYear() +
      '-' +
      (parseInt(end_date.getMonth()) + 1) +
      '-' +
      end_date.getDate();

    try {
      axios
        .post('/dashboard/data', {
          whitelabel_id,
          start_date: start,
          end_date: end,
          language_code,
          range: option,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let locale = this.context.user.code.replace('_', '-');
            let currency = 'USD';
            if (this.context.choosedWhitelabel.id > 0) {
              currency = this.context.choosedWhitelabel.currency_code;
            }
            let registeredDataRaw = res.data.registered;
            let registeredCount = res.data.registered_count;
            let ftdDataRaw = res.data.ftd;
            let ftpDataRaw = res.data.ftp;
            let ftdCount = res.data.ftd_count;
            let ftpCount = res.data.ftp_count;
            let depositsLastSevenDaysDataRaw =
              res.data.deposits_last_seven_days;
            let depositsCount = res.data.deposits_count;
            let soldTicketsLinesDataRaw = res.data.sold_tickets_lines;
            let soldTicketsLinesCount = res.data.sold_tickets_lines_count;
            let paymentGatewaysDataRaw = res.data.payment_gateways;
            let paymentGatewaysSumCount = new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: currency,
            }).format(res.data.payment_gateways_sum);
            let paymentGatewaysDetailsData = res.data.payment_gateways_details;
            paymentGatewaysDetailsData.forEach((gateway) => {
              gateway.name = gateway.name ? gateway.name : gettext('No payment method');
              gateway.amount = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currency,
              }).format(gateway.amount);
            });
            let requestedWithdrawalDataRaw = res.data.requested_withdrawal;
            let requestedWithdrawalSumCount = new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: currency,
            }).format(res.data.requested_withdrawal_sum);
            let acceptedWithdrawalDataRaw = res.data.accepted_withdrawal;
            let acceptedWithdrawalSumCount = new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: currency,
            }).format(res.data.accepted_withdrawal_sum);
            let soldTicketsAmount = res.data.sold_tickets_amount;
            let wonTicketsDataRaw = res.data.won_tickets;
            let wonTicketsCount = res.data.won_tickets_count;
            let ftpTicketsAmount = res.data.ftp_tickets_amount;
            let stpTicketsAmount = res.data.stp_tickets_amount;
            let amountLastSevenDaysDataRaw = res.data.amount_last_seven_days;
            let amountDateRange = res.data.amount_date_range;
            let amountTotal = res.data.total_amount;
            let amountCount = new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: currency,
            }).format(amountTotal);
            let costLastSevenDaysDataRaw = res.data.cost_data_last_seven_days;
            let costDateRange = res.data.cost_date_range;
            let costCount = new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: currency,
            }).format(res.data.total_cost);
            let incomeDataRaw = res.data.income;
            let incomeDateRange = res.data.income_date_range;
            let incomeCount = new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: currency,
            }).format(res.data.total_income);

            let topLotteries = res.data.top_lotteries;
            topLotteries.forEach((lottery) => {
              lottery.amountDisplay = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currency,
              }).format(lottery.amount);
            });

            let topCountries = res.data.top_countries;
            topCountries.forEach((country) => {
              country.amountDisplay = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currency,
              }).format(country.amount);
            });
            let topLanguages = res.data.top_languages;
            topLanguages.forEach((language) => {
              language.amountDisplay = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currency,
              }).format(language.amount);
            });
            let whitelabels = res.data.whitelabels;
            if (whitelabels.length > 0) {
              whitelabels.forEach((whitelabel) => {
                whitelabel.amountDisplay = new Intl.NumberFormat(locale, {
                  style: 'currency',
                  currency: currency,
                }).format(whitelabel.amount);
                whitelabel.incomeDisplay = new Intl.NumberFormat(locale, {
                  style: 'currency',
                  currency: currency,
                }).format(whitelabel.income);
              });
            }
            let registrations = res.data.registrations;
            let first_deposits = res.data.first_deposits;

            let today = new Date();
            let sevenDaysAgo = new Date(
              new Date().setDate(today.getDate() - 6),
            );
            let days = this.getDaysArray(today, sevenDaysAgo);

            let registeredData = this.prepareData(days, registeredDataRaw);
            let ftdData = this.prepareData(days, ftdDataRaw);
            let ftpData = this.prepareData(days, ftpDataRaw);
            let depositsLastSevenDays = this.prepareData(
              days,
              depositsLastSevenDaysDataRaw,
            );
            let soldTicketsData = this.prepareData(
              days,
              soldTicketsLinesDataRaw,
            );
            let paymentGatewaysData = this.prepareData(
              days,
              paymentGatewaysDataRaw,
            );
            let requestedWithdrawalData = this.prepareData(
              days,
              requestedWithdrawalDataRaw,
            );
            let acceptedWithdrawalData = this.prepareData(
              days,
              acceptedWithdrawalDataRaw,
            );
            let wonTicketsData = this.prepareData(days, wonTicketsDataRaw);
            let amountData = this.prepareData(days, amountLastSevenDaysDataRaw);
            let costData = this.prepareData(days, costLastSevenDaysDataRaw);
            let incomeData = this.prepareData(days, incomeDataRaw);

            let ticketsFtpStpData = [['', 1]];
            if (ftpTicketsAmount > 0 || stpTicketsAmount > 0) {
              ticketsFtpStpData = [
                [gettext('New users'), ftpTicketsAmount],
                [gettext('Returning users'), stpTicketsAmount],
              ];
            }

            this.setState(
              {
                registeredData,
                registeredCount,
                ftdData,
                ftdCount,
                ftpData,
                ftpCount,
                depositsData: depositsLastSevenDays,
                depositsCount,
                soldTicketsData,
                paymentGatewaysData,
                requestedWithdrawalData,
                acceptedWithdrawalData,
                soldTicketsLinesCount: soldTicketsLinesCount,
                paymentGatewaysSumCount: paymentGatewaysSumCount,
                paymentGatewaysDetailsData: paymentGatewaysDetailsData,
                requestedWithdrawalSumCount: requestedWithdrawalSumCount,
                acceptedWithdrawalSumCount: acceptedWithdrawalSumCount,
                soldTicketsAmount,
                wonTicketsData,
                wonTicketsCount,
                amountData,
                amountCount,
                amountTotal,
                costData,
                costCount,
                incomeData,
                incomeCount,
                topLotteries,
                topLanguages,
                ftpTicketsAmount,
                stpTicketsAmount,
                ticketsFtpStpData,
                topCountries,
                whitelabels,
                loading: false,
              },
              () => {
                this.prepareTopSellersChartData();
                if (this.context.user.role_id == 1) {
                  this.prepareWhitelabelsChartData();
                }
                this.prepareChart(
                  start_date,
                  end_date,
                  amountDateRange,
                  costDateRange,
                  incomeDateRange,
                  registrations,
                  first_deposits,
                );
              },
            );
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  prepareChart(
    startDate,
    endDate,
    amountDateRange,
    costDateRange,
    incomeDateRange,
    registered,
    deposits,
  ) {
    let locale = this.context.user.code.replace('_', '-');
    let currency = 'USD';
    if (this.context.choosedWhitelabel.id > 0) {
      currency = this.context.choosedWhitelabel.currency_code;
    }
    var numeric = { day: 'numeric', month: 'numeric' };
    const { gettext } = this.context.textdomain;
    let max = 0;
    let umax = 0;
    let min = 0;

    let chartData = {};
    let usersChartData = {};
    let x = [];
    let y1 = [];
    let y2 = [];
    let y3 = [];
    let uy1 = [];
    let uy2 = [];

    let days = this.getDaysArray(endDate, startDate);

    for (var i = 0; i < days.length; i++) {
      x.push(days[i].toLocaleDateString(locale, numeric));
      y1.push(0);
      y2.push(0);
      y3.push(0);
      uy1.push(0);
      uy2.push(0);
    }

    let interpolation = Math.floor(x.length / 5);

    amountDateRange.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y1[index] = data.count;
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
        if (parseFloat(data.count) < min) min = parseFloat(data.count);
      }
    });

    costDateRange.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y2[index] = data.count;
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
        if (parseFloat(data.count) < min) min = parseFloat(data.count);
      }
    });

    incomeDateRange.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y3[index] = data.count;
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
        if (parseFloat(data.count) < min) min = parseFloat(data.count);
      }
    });

    registered.forEach((data) => {
      let priorDate = new Date(data.date_register).toLocaleDateString(
        locale,
        numeric,
      );
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        uy1[index]++;
        if (uy1[index] > umax) umax = uy1[index];
      }
    });

    deposits.forEach((data) => {
      let priorDate = new Date(data.first_deposit).toLocaleDateString(
        locale,
        numeric,
      );
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        uy2[index]++;
        if (uy2[index] > umax) umax = uy2[index];
      }
    });
    uy1 = uy1.map(function (v, idx) {
      return { meta: 'Date: ' + x[idx], value: v };
    });
    uy2 = uy2.map(function (v, idx) {
      return { meta: 'Date: ' + x[idx], value: v };
    });

    y1 = y1.map(function (v, idx) {
      return { meta: 'Date: ' + x[idx], value: v };
    });
    y2 = y2.map(function (v, idx) {
      return { meta: 'Date: ' + x[idx], value: v };
    });
    y3 = y3.map(function (v, idx) {
      return { meta: 'Date: ' + x[idx], value: v };
    });

    let chartOptions = {
      low: 0,
      high: max,
      low: min,
      showArea: true,
      fullWidth: true,
      plugins: [
        ChartistTooltip({
          transformTooltipTextFnc: function (tooltip) {
            let xy = tooltip.split(',');
            let value = new Intl.NumberFormat(locale, {
              style: 'currency',
              currency: currency,
            }).format(xy[0]);
            return value;
          },
        }),
        Legend({
          legendNames: [gettext('Amount'), gettext('Cost'), gettext('Income')],
          clickable: false,
          position: 'bottom',
        }),
      ],
      axisX: {
        labelInterpolationFnc: function (value, index) {
          return index % interpolation === 0 ? value : null;
        },
      },
      axisY: {
        offset: 80,
        labelInterpolationFnc: function (value) {
          return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
          }).format(value);
        },
      },
    };

    let uChartOptions = {
      low: 0,
      high: umax,
      showArea: true,
      fullWidth: true,
      plugins: [
        ChartistTooltip(),
        Legend({
          legendNames: [gettext('First deposit'), gettext('Register')],
          clickable: false,
          position: 'bottom',
        }),
      ],
      axisX: {
        labelInterpolationFnc: function (value, index) {
          return index % interpolation === 0 ? value : null;
        },
      },
      axisY: {
        onlyInteger: true,
      },
    };

    chartData['labels'] = x;
    chartData['series'] = [y1, y2, y3];
    chartData['options'] = chartOptions;

    usersChartData['labels'] = x;
    usersChartData['series'] = [uy2, uy1];
    usersChartData['options'] = uChartOptions;

    this.setState({
      chartData,
      usersChartData,
    });
  }

  prepareData(days, rawData) {
    let data = [0, 0, 0, 0, 0, 0, 0];
    days.forEach((day, index) => {
      let dayConverted = day.toISOString().slice(0, 10);
      let ind = rawData.findIndex((x) => x.date == dayConverted);
      if (ind > -1) {
        data[index] = rawData[ind].count;
      }
    });

    return data;
  }

  getDaysArray(start, end) {
    let days = [];

    var day = 1000 * 60 * 60 * 24;

    var diff = (start.getTime() - end.getTime()) / day;
    for (var i = 0; i <= diff; i++) {
      var xx = end.getTime() + day * i;

      days.push(new Date(xx));
    }

    return days;
  }

  onDateChange = (date) => {
    if (date) {
      this.setState({ date, selectDateValue: 'range' }, () => this.setDays());
    } else {
      this.setState({ showDatePicker: false, selectDateValue: 'month' }, () =>
        this.setDays(),
      );
    }
  };

  selectedRange(e) {
    if (e.target.value === 'range') {
      this.setState({ showDatePicker: true });
    } else {
      let option = e.target.value;
      this.setState({ selectDateValue: option }, () => {
        this.setDays();
      });
    }
  }

  setDays() {
    let startDate;
    let endDate;
    let option = this.state.selectDateValue;

    if (option == '30days') {
      endDate = new Date();
      startDate = new Date(new Date().setDate(endDate.getDate() - 30));
    } else if (option == 'today') {
      endDate = new Date();
      startDate = endDate;
    } else if (option == 'yesterday') {
      endDate = new Date(new Date().setDate(new Date().getDate() - 1));
      startDate = endDate;
    } else if (option == 'month') {
      endDate = new Date();
      startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
    } else if (option == 'lastmonth') {
      let firstDayOfMonth = new Date(
        new Date().getFullYear(),
        new Date().getMonth(),
        1,
      );
      endDate = new Date(firstDayOfMonth - 1);
      startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
    } else if (option == 'year') {
      endDate = new Date();
      startDate = new Date(endDate.getFullYear(), 0, 1);
    } else if (option == 'range') {
      endDate = this.state.date[1];
      startDate = this.state.date[0];
    }
    console.log(startDate);
    console.log(endDate);
    this.fetchData(startDate, endDate, option);
  }

  prepareTopSellersChartData() {
    const { amountTotal, topLotteries } = this.state;
    const { gettext } = this.context.textdomain;

    let topLotteriesData = [['', 1]];
    let othersCount = amountTotal;
    if (topLotteries.length >= 3) {
      othersCount =
        amountTotal -
        topLotteries[0].amount -
        topLotteries[1].amount -
        topLotteries[2].amount;

      topLotteriesData = [
        [topLotteries[0].name, topLotteries[0].amount],
        [topLotteries[1].name, topLotteries[1].amount],
        [topLotteries[2].name, topLotteries[2].amount],
        [gettext('Others'), othersCount],
      ];
    } else if (topLotteries.length > 0) {
      topLotteriesData = [];
      topLotteries.forEach((lottery) => {
        othersCount -= lottery.amount;
        topLotteriesData.push([lottery.name, lottery.amount]);
      });
    }
    this.setState({ topLotteriesData });
  }

  prepareWhitelabelsChartData() {
    const { whitelabels, amountTotal } = this.state;
    const { gettext } = this.context.textdomain;

    let whitelabelsData = [['', 1]];
    let othersCount = amountTotal;

    if (whitelabels.length >= 3) {
      othersCount =
        amountTotal -
        whitelabels[0].amount -
        whitelabels[1].amount -
        whitelabels[2].amount;

      whitelabelsData = [
        [whitelabels[0].name, whitelabels[0].amount],
        [whitelabels[1].name, whitelabels[1].amount],
        [whitelabels[2].name, whitelabels[2].amount],
        [gettext('Others'), othersCount],
      ];
    } else if (whitelabels.length > 0) {
      whitelabelsData = [];
      whitelabels.forEach((whitelabel) => {
        othersCount -= whitelabel.amount;
        whitelabelsData.push([whitelabel.name, whitelabel.amount]);
      });
    }
    this.setState({ whitelabelsData });
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      loading,
      date,
      showDatePicker,
      selectDateValue,
      registeredData,
      ftdData,
      ftdCount,
      ftpData,
      ftpCount,
      depositsData,
      soldTicketsData,
      paymentGatewaysData,
      requestedWithdrawalData,
      acceptedWithdrawalData,
      wonTicketsData,
      ftpTicketsAmount,
      stpTicketsAmount,
      amountData,
      costData,
      incomeData,
      registeredCount,
      depositsCount,
      soldTicketsLinesCount,
      paymentGatewaysSumCount,
      paymentGatewaysDetailsData,
      requestedWithdrawalSumCount,
      acceptedWithdrawalSumCount,
      soldTicketsAmount,
      wonTicketsCount,
      amountCount,
      amountTotal,
      costCount,
      incomeCount,
      topLotteries,
      topCountries,
      topLanguages,
      whitelabels,
      topLotteriesData,
      ticketsFtpStpData,
      whitelabelsData,
      chartData,
      usersChartData,
    } = this.state;
    let locale = this.context.user.code.replace('_', '-');
    return loading ? (
      <Loading />
    ) : (
      <div className="dashboard">
        <div className="row">
          <h4 className="page-title m-l-10">{gettext('Dashboard')}</h4>
          <div className="ml-auto">
            <div className="dl m-b-10 m-r-10">
              {showDatePicker ? (
                <DateRangePicker onChange={this.onDateChange} value={date} />
              ) : (
                <select
                  className="custom-select border-0 text-muted"
                  value={selectDateValue}
                  onChange={this.selectedRange.bind(this)}
                >
                  <option value="today">{gettext('Today')}</option>
                  <option value="yesterday">{gettext('Yesterday')}</option>
                  <option value="30days">{gettext('Last 30 days')}</option>
                  <option value="month">{gettext('This month')}</option>
                  <option value="lastmonth">{gettext('Last month')}</option>
                  <option value="year">{gettext('This year')}</option>
                  <option value="range">{gettext('Date range')}</option>
                </select>
              )}
            </div>
          </div>
        </div>
        <div className="row">
          <Analytics
            title={gettext('Registered users')}
            data={registeredData}
            count={registeredCount.toLocaleString(locale)}
            color={'#137eff'}
          />
          <Analytics
            title={gettext('FTDs')}
            data={ftdData}
            count={ftdCount.toLocaleString(locale)}
            color={'#13dafe'}
          />
          <Analytics
            title={gettext('FTPs')}
            data={ftpData}
            count={ftpCount.toLocaleString(locale)}
            color={'#13dafe'}
          />
          <Analytics
            title={gettext('Deposits')}
            data={depositsData}
            count={depositsCount.toLocaleString(locale)}
            color={'#ab8ce4'}
          />
          <Analytics
            title={gettext('Sold Lines')}
            data={soldTicketsData}
            count={soldTicketsLinesCount.toLocaleString(locale)}
            color={'#fb9678'}
          />
          <Analytics
            title={gettext('Payment Gateways Sum')}
            data={paymentGatewaysData}
            count={paymentGatewaysSumCount.toLocaleString(locale)}
            color={'#ffffff'}
          />
          <Analytics
            title={gettext('Requested Withdrawal Sum')}
            data={requestedWithdrawalData}
            count={requestedWithdrawalSumCount.toLocaleString(locale)}
            color={'#ffffff'}
          />
          <Analytics
            title={gettext('Accepted Withdrawal Sum')}
            data={acceptedWithdrawalData}
            count={acceptedWithdrawalSumCount.toLocaleString(locale)}
            color={'#ffffff'}
          />
        </div>
        <div className="row">
          <Analytics
            title={gettext('Amount')}
            data={amountData}
            count={amountCount.toLocaleString(locale)}
            color={'#137eff'}
          />
          <Analytics
            title={gettext('Cost')}
            data={costData}
            count={costCount.toLocaleString(locale)}
            color={'#13dafe'}
          />
          <Analytics
            title={gettext('Income')}
            data={incomeData}
            count={incomeCount.toLocaleString(locale)}
            color={'#ab8ce4'}
          />
          <Analytics
            title={gettext('Won Lines')}
            data={wonTicketsData}
            count={wonTicketsCount.toLocaleString(locale)}
            color={'#fb9678'}
          />
        </div>
        <div className="row">
          <div className="col-lg-3 col-md-6">
            <div className="card">
              <div className="card-body">
                <h4 className="card-title mb-0">{gettext('Top sellers')}</h4>
                <div className="m-t-10 c3 crm-chart donut-chart">
                  <DonutChart
                    columns={topLotteriesData}
                    element="top-sellers"
                  />
                </div>
                <div className="row text-center m-b-30">
                  {topLotteries[0] && (
                    <div className="col">
                      <h4 className="m-b-0 font-medium">
                        {((topLotteries[0].amount / amountTotal) * 100).toFixed(
                          2,
                        ) + '%'}
                      </h4>
                      <span className="text-muted">{topLotteries[0].name}</span>
                    </div>
                  )}
                  {topLotteries[1] && (
                    <div className="col">
                      <h4 className="m-b-0 font-medium">
                        {((topLotteries[1].amount / amountTotal) * 100).toFixed(
                          2,
                        ) + '%'}
                      </h4>
                      <span className="text-muted">{topLotteries[1].name}</span>
                    </div>
                  )}
                  {topLotteries[2] && (
                    <div className="col">
                      <h4 className="m-b-0 font-medium">
                        {((topLotteries[2].amount / amountTotal) * 100).toFixed(
                          2,
                        ) + '%'}
                      </h4>
                      <span className="text-muted">{topLotteries[2].name}</span>
                    </div>
                  )}
                </div>
                <div className="table-responsive">
                  <table className="table table-hover">
                    <thead>
                      <tr>
                        <th>{gettext('Lottery')}</th>
                        <th>{gettext('%')}</th>
                        <th>{gettext('Amount')}</th>
                      </tr>
                    </thead>
                    <tbody>
                      {topLotteries.length > 0 &&
                        topLotteries.map((lottery, index) => (
                          <tr key={index}>
                            <td className="txt-oflo">{lottery.name}</td>
                            <td className="txt-oflo">
                              {Math.round(
                                (lottery.amount / amountTotal) * 100 * 100,
                              ) /
                                100 +
                                '%'}
                            </td>
                            <td>
                              <span className="font-medium">
                                {lottery.amountDisplay}
                              </span>
                            </td>
                          </tr>
                        ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('Revenue')}</h4>
                <div className="m-t-10 c3 crm-chart donut-chart">
                  <DonutChart columns={ticketsFtpStpData} element="revenue" />
                </div>
                <div className="row text-center">
                  <div className="col-6">
                    <h4 className="m-b-0 font-medium">
                      {ftpTicketsAmount > 0
                        ? (
                            (ftpTicketsAmount / soldTicketsAmount) *
                            100
                          ).toFixed(1) + '%'
                        : ftpTicketsAmount + '%'}
                    </h4>
                    <span className="text-muted">{gettext('New users')}</span>
                  </div>
                  <div className="col-6">
                    <h4 className="m-b-0 font-medium">
                      {stpTicketsAmount > 0
                        ? (
                            (stpTicketsAmount / soldTicketsAmount) *
                            100
                          ).toFixed(1) + '%'
                        : stpTicketsAmount + '%'}
                    </h4>
                    <span className="text-muted">
                      {gettext('Returning users')}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('Payment Gateways')}</h4>
                <div className="table-responsive">
                  <table className="table table-striped">
                    <thead className="thead-light">
                      <tr>
                        <th scope="col">{gettext('Gateway')}</th>
                        <th scope="col">{gettext('Sum')}</th>
                      </tr>
                    </thead>
                    <tbody>
                    {paymentGatewaysDetailsData.length > 0 && paymentGatewaysDetailsData.map((gateway, index) => (
                      <tr key={index}>
                        <td>{gateway.name}</td>
                        <td>{gateway.amount}</td>
                      </tr>
                    ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div className="col-lg-6 col-md-12 order-lg-0 order-md-3">
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('Sales Ratio')}</h4>
                <div className="sales ct-charts mt-5 m-b-20 dashboard-chart">
                  {chartData && (
                    <Chart
                      labels={chartData.labels}
                      series={chartData.series}
                      options={chartData.options}
                      element={'sales-chart'}
                    />
                  )}
                </div>
              </div>
            </div>
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">
                  {gettext('Registrations and deposits')}
                </h4>
                <div className="users-chart ct-charts m-t-30">
                  {usersChartData && (
                    <Chart
                      labels={usersChartData.labels}
                      series={usersChartData.series}
                      options={usersChartData.options}
                      element={'users-chart'}
                    />
                  )}
                </div>
              </div>
            </div>
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('Top Region Sales')}</h4>
                {topCountries.length > 0 && (
                  <div>
                    <Map data={topCountries} />
                    <div className="row m-t-40 text-center text-lg-left">
                      {topCountries[0] && (
                        <CountryStats
                          label="label-success"
                          countryName={topCountries[0].country_name}
                          ticketsCount={topCountries[0].count}
                          amount={topCountries[0].amountDisplay}
                          percent={topCountries[0].balance}
                        />
                      )}
                      {topCountries[1] && (
                        <CountryStats
                          label="label-info"
                          countryName={topCountries[1].country_name}
                          ticketsCount={topCountries[1].count}
                          amount={topCountries[1].amountDisplay}
                          percent={topCountries[1].balance}
                        />
                      )}
                      {topCountries[2] && (
                        <CountryStats
                          label="label-danger"
                          countryName={topCountries[2].country_name}
                          ticketsCount={topCountries[2].count}
                          amount={topCountries[2].amountDisplay}
                          percent={topCountries[2].balance}
                        />
                      )}
                      {topCountries[3] && (
                        <CountryStats
                          label="label-purple"
                          countryName={topCountries[3].country_name}
                          ticketsCount={topCountries[3].count}
                          amount={topCountries[3].amountDisplay}
                          percent={topCountries[3].balance}
                        />
                      )}
                      {topCountries[4] && (
                        <CountryStats
                          label="label-megna"
                          countryName={topCountries[4].country_name}
                          ticketsCount={topCountries[4].count}
                          amount={topCountries[4].amountDisplay}
                          percent={topCountries[4].balance}
                        />
                      )}
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
          <div className="col-lg-3 col-md-6">
            {this.context.user.role_id == 1 &&
              this.context.choosedWhitelabel.id === 0 && (
                <div className="card">
                  <div className="card-body">
                    <h4 className="card-title mb-0">
                      {gettext('Whitelabels')}
                    </h4>
                    <div className="m-t-10 c3 crm-chart donut-chart">
                      <DonutChart
                        columns={whitelabelsData}
                        element="whitelabels"
                      />
                    </div>
                    <div className="row text-center m-b-30">
                      {whitelabels[0] && (
                        <div className="col">
                          <h4 className="m-b-0 font-medium">
                            {(
                              (whitelabels[0].amount / amountTotal) *
                              100
                            ).toFixed(2) + '%'}
                          </h4>
                          <span className="text-muted">
                            {whitelabels[0].name}
                          </span>
                        </div>
                      )}
                      {whitelabels[1] && (
                        <div className="col">
                          <h4 className="m-b-0 font-medium">
                            {(
                              (whitelabels[1].amount / amountTotal) *
                              100
                            ).toFixed(2) + '%'}
                          </h4>
                          <span className="text-muted">
                            {whitelabels[1].name}
                          </span>
                        </div>
                      )}
                      {whitelabels[2] && (
                        <div className="col">
                          <h4 className="m-b-0 font-medium">
                            {(
                              (whitelabels[2].amount / amountTotal) *
                              100
                            ).toFixed(2) + '%'}
                          </h4>
                          <span className="text-muted">
                            {whitelabels[2].name}
                          </span>
                        </div>
                      )}
                    </div>
                    <div className="table-responsive">
                      <table className="table table-hover">
                        <thead>
                          <tr>
                            <th>{gettext('Whitelabel')}</th>
                            <th>{gettext('Income')}</th>
                            <th>{gettext('Amount')}</th>
                          </tr>
                        </thead>
                        <tbody>
                          {whitelabels.length > 0 &&
                            whitelabels.map((whitelabel, index) => (
                              <tr key={index}>
                                <td className="txt-oflo">{whitelabel.name}</td>
                                <td className="txt-oflo">
                                  {whitelabel.incomeDisplay}
                                </td>
                                <td>
                                  <span className="font-medium">
                                    {whitelabel.amountDisplay}
                                  </span>
                                </td>
                              </tr>
                            ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              )}
            <div className="card">
              <div className="card-body">
                <h4 className="card-title mb-0">{gettext('Top countries')}</h4>
              </div>
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead className="thead-light">
                    <tr>
                      <th scope="col">{gettext('Country')}</th>
                      <th scope="col">{gettext('%')}</th>
                      <th scope="col">{gettext('Lines')}</th>
                      <th scope="col">{gettext('Amount')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {topCountries.length > 0 &&
                      topCountries.map((country, index) => (
                        <tr key={index}>
                          <td className="txt-oflo">{country.country_name}</td>
                          <td className="txt-oflo">
                            {Math.round(
                              (country.amount / amountTotal) * 100 * 100,
                            ) /
                              100 +
                              '%'}
                          </td>
                          <td className="txt-oflo">{country.count}</td>
                          <td>
                            <span className="font-medium">
                              {country.amountDisplay}
                            </span>
                          </td>
                        </tr>
                      ))}
                  </tbody>
                </table>
              </div>
            </div>
            <div className="card">
              <div className="card-body">
                <h4 className="card-title mb-0">{gettext('Top languages')}</h4>
              </div>
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead className="thead-light">
                    <tr>
                      <th scope="col">{gettext('Language')}</th>
                      <th scope="col">{gettext('%')}</th>
                      <th scope="col">{gettext('Lines')}</th>
                      <th scope="col">{gettext('Amount')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {topLanguages.length > 0 &&
                      topLanguages.map((language, index) => (
                        <tr key={index}>
                          <td className="txt-oflo">{language.name}</td>
                          <td className="txt-oflo">
                            {Math.round(
                              (language.count / soldTicketsLinesCount) *
                                100 *
                                100,
                            ) /
                              100 +
                              '%'}
                          </td>
                          <td className="txt-oflo">{language.count}</td>
                          <td>
                            <span className="font-medium">
                              {language.amountDisplay}
                            </span>
                          </td>
                        </tr>
                      ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
Dashboard.contextType = CrmContext;
export default Dashboard;
