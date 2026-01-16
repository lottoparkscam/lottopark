import React from 'react';
import CrmContext from '../../../helpers/context';
import DonutChart from '../../elements/donutChart';
import Chart from '../../elements/chart';
import Pagination from '../../elements/pagination';
import Loading from '../../elements/loading';
import ChartistTooltip from 'chartist-plugin-tooltips-updated';
import Legend from 'chartist-plugin-legend';
import DateRangePicker from '@wojtekmaj/react-daterange-picker/dist/entry.nostyle';
import axios from '../../../helpers/interceptors';
import { Link } from 'react-router-dom';
import * as utils from '../../../helpers/utils';
import List from '../../elements/list';
import { changeDateFormatForCarbon } from '../../../helpers/date';

const tabsList = [
  {
    name: 'all_per_method',
    label: 'Per Method',
    icon: 'ti-home',
    columns: [
      {
        name: 'method_name',
        label: 'Method Name',
        filterType: List.filters.search,
        specialFormat: List.specialFormats.capitalizeWords,
      },
      {
        name: 'pending_rate',
        label: 'Pending rate',
        filterType: List.filters.percent,
      },
      {
        name: 'pending_count',
        label: 'Pending Count',
        filterType: List.filters.integer,
      },
      {
        name: 'pending_amount',
        label: 'Pending Amount',
        filterType: List.filters.integer,
      },
      {
        name: 'approved_rate',
        label: 'Approved Rate',
        filterType: List.filters.percent,
      },
      {
        name: 'approved_count',
        label: 'Approved Count',
        filterType: List.filters.integer,
      },
      {
        name: 'approved_amount',
        label: 'Approved Amount',
        filterType: List.filters.integer,
      },
      {
        name: 'error_rate',
        label: 'Error Rate',
        filterType: List.filters.percent,
      },
      {
        name: 'error_count',
        label: 'Error Count',
        filterType: List.filters.integer,
      },
      {
        name: 'error_amount',
        label: 'Error Amount',
        filterType: List.filters.integer,
      },
    ],
  },
];

class Transactions extends React.Component {
  constructor(props) {
    super(props);

    this.timeout = null;

    const currentDate = new Date();
    const firstDayOfCurrentMonth = new Date(
      currentDate.getFullYear(),
      currentDate.getMonth(),
      1,
    );
    const lastDayOfCurrentMonth = new Date(
      new Date(
        currentDate.getFullYear(),
        currentDate.getMonth() + 1,
        0,
        23,
        59,
        59,
      ),
    );

    this.state = {
      chartLoading: true,
      tableLoading: true,
      exportLoading: false,
      customChartData: null,
      customCardTitle: 'Transactions',
      customPeriodTransactionNumber: 0,
      showDatePicker: false,
      date: [new Date(), new Date()],
      selectDateValue: 'month',
      chartData: null,
      activeTab: 'all',
      filters: [],
      page: 1,
      itemsPerPage: 50,
      sortBy: 'date',
      order: 'DESC',
      columns: [
        { name: 'full_token', shown: true, filter: '' },
        { name: 'user_name', shown: true, filter: '' },
        { name: 'method', shown: true, filter: '' },
        { name: 'amount', shown: true, filter: ['', ''] },
        { name: 'bonus_amount', shown: true, filter: ['', ''] },
        { name: 'date', shown: true, filter: '' },
        { name: 'date_confirmed', shown: true, filter: '' },
        { name: 'tickets_count', shown: true, filter: ['', ''] },
        { name: 'status', shown: true, filter: '' },
      ],
      columnsNames: null,
      allCount: 0,
      purchasesCount: 0,
      pendingCount: 0,
      approvedCount: 0,
      errorCount: 0,
      pendingAmountSumDisplayForChart: '0',
      approvedAmountSumDisplayForChart: '0',
      errorAmountSumDisplayForChart: '0',
      tableData: [],
      methods: [],
      startDate: changeDateFormatForCarbon(firstDayOfCurrentMonth),
      endDate: changeDateFormatForCarbon(lastDayOfCurrentMonth),
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    const { gettext } = this.context.textdomain;
    const whitelabel_id = this.context.choosedWhitelabel.id;
    let userColumnName = gettext('User Token • User Name • User E-mail');
    if (this.context.useLoginsForUsers()) {
      userColumnName = gettext(
        'User Token • User Name • User E-mail • User Login',
      );
    }
    let columnsNames = {
      full_token: gettext('Token'),
      user_name: userColumnName,
      method: gettext('Method'),
      amount: gettext('Amount'),
      bonus_amount: gettext('Bonus amount'),
      date: gettext('Date'),
      date_confirmed: gettext('Date confirmed'),
      tickets_count: gettext('Tickets/Processed'),
      status: gettext('Status'),
    };
    this.setState({ columnsNames, whitelabel_id }, () => this.fetchTableData());
    this.setDays();
  }

  setDays() {
    let startDate;
    let endDate;
    let option = this.state.selectDateValue;

    if (option == '30days') {
      endDate = new Date();
      startDate = new Date(new Date().setDate(endDate.getDate() - 30));
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
    this.setState({
      endDate: changeDateFormatForCarbon(endDate),
      startDate: changeDateFormatForCarbon(startDate),
    });
    this.fetchChartData(startDate, endDate);
  }

  fetchChartData(start_date, end_date) {
    let whitelabel_id = this.context.choosedWhitelabel.id;
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
        .post('/crm/transactions/data_date_range', {
          whitelabel_id,
          start_date: start,
          end_date: end,
          language_code,
          isCasino: this.props.isCasino,
          isDeposit: this.props.isDeposit,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let pending = res.data.pending;
            let approved = res.data.approved;
            let error = res.data.error;

            this.prepareChart(start_date, end_date, pending, approved, error);
          }
        });
    } catch (e) {
      console.error(e);
    }
  }

  fetchTableData() {
    const {
      whitelabel_id,
      activeTab,
      filters,
      page,
      itemsPerPage,
      sortBy,
      order,
    } = this.state;
    try {
      axios
        .post('/crm/transactions/table_data', {
          whitelabel_id,
          activeTab,
          filters,
          page,
          itemsPerPage,
          sortBy,
          order,
          isCasino: this.props.isCasino,
          isDeposit: this.props.isDeposit,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let tableData = res.data.table_data;
            let methods = res.data.methods;

            let allCount = res.data.all_count;
            let purchasesCount = res.data.purchases_count;
            let pendingCount = res.data.pending_count;
            let approvedCount = res.data.approved_count;
            let errorCount = res.data.error_count;
            this.setState({
              allCount,
              purchasesCount,
              pendingCount,
              approvedCount,
              errorCount,
              tableData,
              methods,
              tableLoading: false,
            });
          }
        });
    } catch (e) {
      console.error(e);
    }
  }

  prepareChart(startDate, endDate, pending, approved, error) {
    let locale = this.context.user.code.replace('_', '-');
    var numeric = { day: 'numeric', month: 'numeric' };
    const { gettext } = this.context.textdomain;
    let max = 0;

    let chartData = {};
    let customChartData;
    let x = [];
    let y1 = [];
    let y2 = [];
    let y3 = [];
    let dataCountPending = 0;
    let dataCountApproved = 0;
    let dataCountError = 0;
    let customPeriodTransactionNumber = 0;

    let days = this.getDaysArray(endDate, startDate);

    for (var i = 0; i < days.length; i++) {
      x.push(days[i].toLocaleDateString(locale, numeric));
      y1.push(0);
      y2.push(0);
      y3.push(0);
    }

    let interpolation = Math.floor(x.length / 5);

    pending.groupedTransactionsByDatePerMonth.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y1[index] = data.count;
        dataCountPending += parseInt(data.count);
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
      }
    });

    approved.groupedTransactionsByDatePerMonth.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y2[index] = data.count;
        dataCountApproved += parseInt(data.count);
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
      }
    });

    error.groupedTransactionsByDatePerMonth.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y3[index] = data.count;
        dataCountError += parseInt(data.count);
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
      }
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
      showArea: true,
      fullWidth: true,
      plugins: [
        ChartistTooltip(),
        Legend({
          legendNames: [
            gettext('Pending'),
            gettext('Approved'),
            gettext('Error'),
          ],
          clickable: false,
          position: 'bottom',
        }),
      ],
      axisX: {
        labelInterpolationFnc: function (value, index) {
          return index % interpolation === 0 ? value : null;
        },
      },
    };

    chartData['labels'] = x;
    chartData['series'] = [y1, y2, y3];
    chartData['options'] = chartOptions;

    customChartData = [
      [gettext('Pending'), dataCountPending],
      [gettext('Approved'), dataCountApproved],
      [gettext('Error'), dataCountError],
    ];

    customPeriodTransactionNumber =
      dataCountPending + dataCountApproved + dataCountError;

    if (pending.additionalData) {
      this.setState({
        pendingAmountSumDisplayForChart:
          pending.additionalData.amountSumDisplay,
      });
    } else {
      this.setState({
        pendingAmountSumDisplayForChart: 0,
      });
    }

    if (approved.additionalData) {
      this.setState({
        approvedAmountSumDisplayForChart:
          approved.additionalData.amountSumDisplay,
      });
    } else {
      this.setState({
        approvedAmountSumDisplayForChart: 0,
      });
    }

    if (error.additionalData) {
      this.setState({
        errorAmountSumDisplayForChart: error.additionalData.amountSumDisplay,
      });
    } else {
      this.setState({
        errorAmountSumDisplayForChart: 0,
      });
    }

    this.setState({
      customCardTitle:
        startDate.toLocaleDateString() + ' - ' + endDate.toLocaleDateString(),
      customPeriodTransactionNumber: customPeriodTransactionNumber,
      customChartData,
      chartData,
      chartLoading: false,
    });
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

  onChange = (date) => {
    if (date) {
      this.setState(
        { date, selectDateValue: 'range', chartLoading: true },
        () => this.setDays(),
      );
    } else {
      this.setState(
        {
          showDatePicker: false,
          selectDateValue: 'month',
          chartLoading: true,
        },
        () => this.setDays(),
      );
    }
  };

  selectedRange(e) {
    if (e.target.value === 'range') {
      this.setState({ showDatePicker: true });
    } else {
      let option = e.target.value;
      this.setState({ selectDateValue: option, chartLoading: true }, () => {
        this.setDays();
      });
    }
  }

  setActiveTab(activeTab) {
    let columns = this.state.columns;
    let statusIndex = columns.findIndex((x) => x.name == 'status');
    let dateConfirmedIndex = columns.findIndex(
      (x) => x.name == 'date_confirmed',
    );
    let ticketsIndex = columns.findIndex((x) => x.name == 'tickets_count');

    if (activeTab == 'deposits') {
      columns[ticketsIndex].shown == true &&
        (columns[ticketsIndex].shown = false);
    } else {
      columns[ticketsIndex].shown == false &&
        (columns[ticketsIndex].shown = true);
    }
    if (
      activeTab == 'pending' ||
      activeTab == 'approved' ||
      activeTab == 'error'
    ) {
      columns[statusIndex].shown == true &&
        (columns[statusIndex].shown = false);
    } else {
      columns[statusIndex].shown == false &&
        (columns[statusIndex].shown = true);
    }
    if (activeTab == 'pending' || activeTab == 'error') {
      columns[dateConfirmedIndex].shown == true &&
        (columns[dateConfirmedIndex].shown = false);
    } else {
      columns[dateConfirmedIndex].shown == false &&
        (columns[dateConfirmedIndex].shown = true);
    }

    this.setState({ activeTab, page: 1, columns, tableLoading: true }, () => {
      this.fetchTableData();
    });
  }

  onHandleChange(e) {
    let itemsPerPage = e.target.value;
    this.setState(
      {
        itemsPerPage,
        page: 1,
        tableLoading: true,
      },
      () => this.fetchTableData(),
    );
  }

  sortBy(col) {
    let sortBy = this.state.sortBy;
    let order = this.state.order;
    if (sortBy === col) {
      if (order === 'ASC') order = 'DESC';
      else order = 'ASC';
      this.setState(
        {
          order,
        },
        () => this.fetchTableData(),
      );
    } else {
      order = 'DESC';
      this.setState(
        {
          sortBy: col,
          order,
        },
        () => this.fetchTableData(),
      );
    }
  }

  paginate = (page) => {
    this.setState({ page, tableLoading: true }, () => this.fetchTableData());
  };

  filterDate(date) {
    let colName = 'date';
    this.filterDates(date, colName);
  }

  filterDateConfirmed(date) {
    let colName = 'date_confirmed';
    this.filterDates(date, colName);
  }

  filterDates(date, colName) {
    const { columns, filters } = this.state;
    let updatedColumns, updatedFilters;
    [updatedColumns, updatedFilters] = utils.filterDates(
      columns,
      filters,
      date,
      colName,
    );
    this.setState(
      { columns: updatedColumns, filters: updatedFilters, page: 1 },
      () => {
        this.fetchTableData();
      },
    );
  }

  filterAmounts(e) {
    if (this.timeout) clearTimeout(this.timeout);
    let name = e.target.name.split('-');
    let column = name[0];
    let fromto = name[1];
    let value = e.target.value;

    let columns = this.state.columns;
    let filters = this.state.filters;
    var colIndex = columns.findIndex((x) => x.name == column);
    let index = filters.findIndex((x) => x.column == column);
    let range = columns[colIndex].filter;
    if (fromto == 'from') range[0] = value;
    else if (fromto == 'to') range[1] = value;
    columns[colIndex].filter = range;
    let filter = { column: column, start: range[0], end: range[1] };
    if (index == -1) {
      filters.push(filter);
    } else {
      if (range[0] == 0 && range[1] == 0) filters.splice(index, 1);
      else filters[index] = filter;
    }
    this.setState({ columns, filters, page: 1 }, () => {
      this.timeout = setTimeout(() => this.fetchTableData(), 1000);
    });
  }
  filterList(e) {
    if (this.timeout) clearTimeout(this.timeout);
    var name = e.target.name;
    let value = e.target.value;

    let columns = this.state.columns;
    let index = columns.findIndex((x) => x.name == name);
    columns[index].filter = value;
    this.setState({ columns });
    this.timeout = setTimeout(() => {
      let filters = this.state.filters;
      let index = filters.findIndex((x) => {
        return x.column == name;
      });
      if (index !== -1) {
        if (value == '' || value == null) {
          filters.splice(index, 1);
        } else filters[index].value = value;
      } else {
        let filter = { column: name, value: value };
        filters.push(filter);
      }
      this.setState(
        {
          filters,
          page: 1,
          dataLoading: true,
        },
        () => this.fetchTableData(),
      );
    }, 1000);
  }

  showLink(name) {
    if (this.context.user.role_id == '1') return true;
    if (
      !this.context.modules.find(
        (x) =>
          x.module_name === name &&
          x.whitelabel_id === this.context.choosedWhitelabel.id,
      )
    )
      return false;
    return true;
  }

  downloadCSV() {
    const { whitelabel_id, activeTab, filters } = this.state;
    const FileDownload = require('js-file-download');
    this.setState({ exportLoading: true });
    try {
      axios
        .post('/crm/transactions/export', {
          whitelabel_id,
          activeTab,
          filters,
          isCasino: this.props.isCasino,
          isDeposit: this.props.isDeposit,
        })
        .then((res) => {
          let date = new Date();
          let year = date.getFullYear();
          let month = date.getMonth() + 1;
          let day = date.getDate();
          let hours = date.getHours();
          let minutes = date.getMinutes();
          let seconds = date.getSeconds();

          let filename =
            'transactions_' +
            activeTab +
            '_' +
            year +
            '-' +
            month +
            '-' +
            day +
            '_' +
            hours +
            ':' +
            minutes +
            ':' +
            seconds +
            '.csv';

          FileDownload(res.data, filename);
          this.setState({ exportLoading: false });
        });
    } catch (e) {
      console.error(e);
    }
  }

  filters() {
    const { gettext } = this.context.textdomain;
    const { columns, methods } = this.state;

    let methodsOptions = [
      <option key={'balance'} value={'balance'}>
        {gettext('Balance')}
      </option>,
      <option key={'bonus_balance'} value={'bonus_balance'}>
        {gettext('Bonus balance')}
      </option>,
    ];

    if (Object.keys(methods).length > 0) {
      methodsOptions.push(
        Object.keys(methods).map((method, index) => (
          <option key={index} value={methods[method].id}>
            {methods[method].name}
          </option>
        )),
      );
    }

    return columns.map(
      (col) =>
        col.shown && (
          <td key={'search1' + col.name}>
            {col.name == 'date' ? (
              <DateRangePicker
                onChange={this.filterDate.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'date_confirmed' ? (
              <DateRangePicker
                onChange={this.filterDateConfirmed.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'method' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="method"
                value={col.filter}
              >
                <option value="">--</option>
                {methodsOptions}
              </select>
            ) : col.name == 'status' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="status"
                value={col.filter}
              >
                <option value="">--</option>
                <option value="0">{gettext('Pending')}</option>
                <option value="1">{gettext('Approved')}</option>
                <option value="2">{gettext('Error')}</option>
              </select>
            ) : col.name == 'amount' ||
              col.name == 'bonus_amount' ||
              col.name == 'tickets_count' ? (
              <div>
                {' '}
                <input
                  className="input-40"
                  type="text"
                  name={col.name + '-from'}
                  onChange={this.filterAmounts.bind(this)}
                  value={col.filter[0]}
                />
                <span>{gettext(' to ')}</span>
                <input
                  className="input-40"
                  type="text"
                  name={col.name + '-to'}
                  onChange={this.filterAmounts.bind(this)}
                  value={col.filter[1]}
                />
              </div>
            ) : (
              <input
                type="text"
                name={col.name}
                onChange={this.filterList.bind(this)}
                value={col.filter}
              />
            )}
          </td>
        ),
    );
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      chartLoading,
      tableLoading,
      exportLoading,
      customCardTitle,
      customChartData,
      customPeriodTransactionNumber,
      showDatePicker,
      page,
      itemsPerPage,
      sortBy,
      order,
      selectDateValue,
      chartData,
      activeTab,
      allCount,
      purchasesCount,
      pendingCount,
      approvedCount,
      errorCount,
      columns,
      columnsNames,
      tableData,
      approvedAmountSumDisplayForChart,
      pendingAmountSumDisplayForChart,
      errorAmountSumDisplayForChart,
      startDate,
      endDate,
    } = this.state;
    const endpoint = `/crm/transaction_per_method?isDeposit=${this.props.isDeposit}&isCasino=${this.props.isCasino}&startDate=${startDate}&endDate=${endDate}`;
    return (
      <div className="transactions-page">
        <div className="row">
          <div className="col-12 m-b-10 d-flex justify-content-end">
            <div>
              {showDatePicker ? (
                <DateRangePicker
                  onChange={this.onChange}
                  value={this.state.date}
                />
              ) : (
                <select
                  className="custom-select border-0 text-muted height-41"
                  value={selectDateValue}
                  onChange={this.selectedRange.bind(this)}
                >
                  <option value="30days">{gettext('Last 30 days')}</option>
                  <option value="month">{gettext('This month')}</option>
                  <option value="lastmonth">{gettext('Last month')}</option>
                  <option value="year">{gettext('This year')}</option>
                  <option value="range">{gettext('Date range')}</option>
                </select>
              )}
            </div>
          </div>
          <div className="col-lg-3 col-md-12">
            <div className="card transactions-h-315">
              <div className="card-body">
                <h4 className="card-title">{customCardTitle}</h4>
                {chartLoading ? (
                  <Loading />
                ) : (
                  <div>
                    <div className="m-t-10 c3 crm-chart donut-chart">
                      <DonutChart
                        columns={customChartData}
                        element="transactions-chart"
                      />
                    </div>
                    <div className="row text-center m-b-30">
                      <div className="col">
                        <h4 className="m-b-0 font-medium">
                          {customChartData[0][1]
                            ? (
                                (customChartData[0][1] /
                                  customPeriodTransactionNumber) *
                                100
                              ).toFixed(2) + '%'
                            : '0.00%'}
                        </h4>
                        <span className="text-muted">
                          {gettext('Pending')}({customChartData[0][1]})
                        </span>
                        <br />
                        <span>{pendingAmountSumDisplayForChart}</span>
                      </div>
                      <div className="col">
                        <h4 className="m-b-0 font-medium">
                          {customChartData[1][1]
                            ? (
                                (customChartData[1][1] /
                                  customPeriodTransactionNumber) *
                                100
                              ).toFixed(2) + '%'
                            : '0.00%'}
                        </h4>
                        <span className="text-muted">
                          {gettext('Approved')}({customChartData[1][1]})
                        </span>
                        <br />
                        <span>{approvedAmountSumDisplayForChart}</span>
                      </div>
                      <div className="col">
                        <h4 className="m-b-0 font-medium">
                          {customChartData[2][1]
                            ? (
                                (customChartData[2][1] /
                                  customPeriodTransactionNumber) *
                                100
                              ).toFixed(2) + '%'
                            : '0.00%'}
                        </h4>
                        <span className="text-muted">
                          {gettext('Error')}({customChartData[2][1]})
                        </span>
                        <br />
                        <span>{errorAmountSumDisplayForChart}</span>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
          <div className="col-lg-9 col-md-12">
            <div className="card transactions-h-315">
              <div className="card-body">
                <div className="row">
                  <h4 className="card-title m-l-10">
                    {gettext('Transactions')}
                  </h4>
                </div>
                <div className="sales ct-charts m-b-20 m-t-5 transactions-h-200">
                  {chartLoading ? (
                    <Loading />
                  ) : (
                    <Chart
                      labels={chartData.labels}
                      series={chartData.series}
                      options={chartData.options}
                      element={'chart'}
                    />
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
        {chartLoading ? (
          <Loading />
        ) : (
          <List tabs={tabsList} endpoint={endpoint} tabsDatabaseField="" />
        )}
        <div className="card">
          <div className="card-body">
            <h4 className="card-title">{gettext('Transactions List')}</h4>
            <ul className="nav nav-tabs" role="tablist">
              <li className="nav-item">
                <a
                  className={
                    'cursor-pointer nav-link ' +
                    (activeTab == 'all' && 'active')
                  }
                  role="tab"
                  onClick={() => {
                    this.setActiveTab('all');
                  }}
                >
                  <span className="hidden-sm-up">
                    <i className="ti-home"></i>
                  </span>
                  <span className="hidden-xs-down">
                    {' ' + gettext('All') + ' (' + allCount + ')'}
                  </span>
                </a>
              </li>
              {this.props.isDeposit && (
                <li className="nav-item">
                  <a
                    className={
                      'cursor-pointer nav-link ' +
                      (activeTab == 'purchases' && 'active')
                    }
                    role="tab"
                    onClick={() => {
                      this.setActiveTab('purchases');
                    }}
                  >
                    <span className="hidden-sm-up">
                      <i className="ti-shopping-cart-full"></i>
                    </span>
                    <span className="hidden-xs-down">
                      {' ' + gettext('Purchases') + ' (' + purchasesCount + ')'}
                    </span>
                  </a>
                </li>
              )}
              <li className="nav-item">
                <a
                  className={
                    'cursor-pointer nav-link ' +
                    (activeTab == 'pending' && 'active')
                  }
                  role="tab"
                  onClick={() => {
                    this.setActiveTab('pending');
                  }}
                >
                  <span className="hidden-sm-up">
                    <i className="ti-timer"></i>
                  </span>
                  <span className="hidden-xs-down">
                    {' ' + gettext('Pending') + ' (' + pendingCount + ')'}
                  </span>
                </a>
              </li>
              <li className="nav-item">
                <a
                  className={
                    'cursor-pointer nav-link ' +
                    (activeTab == 'approved' && 'active')
                  }
                  role="tab"
                  onClick={() => {
                    this.setActiveTab('approved');
                  }}
                >
                  <span className="hidden-sm-up">
                    <i className="ti-check-box"></i>
                  </span>
                  <span className="hidden-xs-down">
                    {' ' + gettext('Approved') + ' (' + approvedCount + ')'}
                  </span>
                </a>
              </li>
              <li className="nav-item">
                <a
                  className={
                    'cursor-pointer nav-link ' +
                    (activeTab == 'error' && 'active')
                  }
                  role="tab"
                  onClick={() => {
                    this.setActiveTab('error');
                  }}
                >
                  <span className="hidden-sm-up">
                    <i className="ti-alert"></i>
                  </span>
                  <span className="hidden-xs-down">
                    {' ' + gettext('Error') + ' (' + errorCount + ')'}
                  </span>
                </a>
              </li>
            </ul>
            <div className="table-responsive">
              <div className="container-fluid">
                <div className="row m-t-25 m-b-15">
                  <div className="col-sm-12 col-md-6">
                    <div className="users-view-table-length">
                      <label>
                        {gettext('Show') + ' '}
                        <select
                          className="form-control form-control-sm"
                          onChange={this.onHandleChange.bind(this)}
                          name="table_length"
                          value={itemsPerPage}
                        >
                          <option value="50">{gettext('50')}</option>
                          <option value="100">{gettext('100')}</option>
                          <option value="200">{gettext('200')}</option>
                          <option value="500">{gettext('500')}</option>
                        </select>
                        {' ' + gettext('entries')}
                      </label>
                    </div>
                  </div>
                  <div className="col-sm-12 col-md-6">
                    <div className="text-right loader-small">
                      {exportLoading ? (
                        <Loading />
                      ) : (
                        <button
                          type="button"
                          className="btn btn-outline-info m-b-10 m-r-10"
                          onClick={this.downloadCSV.bind(this)}
                        >
                          <i className="mdi mdi-download m-r-10"></i>
                          {gettext('Export to CSV')}
                        </button>
                      )}
                    </div>
                  </div>
                </div>
                <div className="row">
                  <div className="col">
                    {tableLoading ? (
                      <Loading />
                    ) : (
                      <table className="table table-striped table-bordered datatable-select-inputs">
                        <thead>
                          <tr>
                            {columnsNames &&
                              columns.map(
                                (col) =>
                                  col.shown && (
                                    <th
                                      key={'th' + col.name}
                                      onClick={() => this.sortBy(col.name)}
                                      className={
                                        'sorting' +
                                        (sortBy == col.name
                                          ? order == 'ASC'
                                            ? '_asc'
                                            : '_desc'
                                          : '')
                                      }
                                    >
                                      {columnsNames[col.name]}
                                    </th>
                                  ),
                              )}
                            <th>{gettext('Manage')}</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr className="table-search">
                            {this.filters()}
                            <td></td>
                          </tr>
                          {tableData.map((transaction, i) => (
                            <tr key={i}>
                              {columns.map(
                                (col) =>
                                  col.shown && (
                                    <td key={'td' + i + col.name}>
                                      {col.name == 'user_name' ? (
                                        transaction['user_token_full'] +
                                        ' • ' +
                                        transaction['user_name'] +
                                        ' ' +
                                        transaction['user_surname'] +
                                        ' • ' +
                                        transaction['user_email'] +
                                        (this.context.useLoginsForUsers()
                                          ? ' • ' + transaction['user_login']
                                          : '')
                                      ) : col.name == 'tickets_count' ? (
                                        transaction['tickets_count'] +
                                        '/' +
                                        transaction['tickets_processed_count']
                                      ) : col.name == 'user_balance_display' &&
                                        transaction.user_balance_class_danger ? (
                                        <span className="text-danger">
                                          {transaction.user_balance_display}
                                        </span>
                                      ) : col.name == 'status' ? (
                                        transaction.status_display
                                      ) : col.name == 'amount' ? (
                                        transaction.amount_display
                                      ) : col.name == 'bonus_amount' ? (
                                        transaction.bonus_amount_display
                                      ) : (
                                        transaction[col.name]
                                      )}
                                      {col.name == 'user_name' &&
                                        this.showLink('users-view') && (
                                          <div className="users-tooltip">
                                            <Link
                                              to={{
                                                pathname: `/whitelabel/users/view/${transaction.user_token_full}`,
                                              }}
                                            >
                                              <i className="users-edit mdi mdi-account m-l-5" />
                                            </Link>
                                            <span className="users-tooltiptext">
                                              {gettext('View user')}
                                            </span>
                                          </div>
                                        )}
                                    </td>
                                  ),
                              )}
                              <td>
                                <div className="users-tooltip">
                                  <Link
                                    to={{
                                      pathname: `/crm/transactions/${
                                        this.props.isCasino
                                          ? 'casino/'
                                          : 'lottery/'
                                      }view/${transaction.full_token}`,
                                    }}
                                  >
                                    <i className="users-edit mdi mdi-view-list" />
                                  </Link>

                                  <span className="users-tooltiptext">
                                    {gettext('Details')}
                                  </span>
                                </div>
                              </td>
                            </tr>
                          ))}
                          <tr className="table-search">
                            {this.filters()}
                            <td></td>
                          </tr>
                        </tbody>
                      </table>
                    )}
                  </div>
                </div>
                <div className="row">
                  <div className="col-sm-12 col-md-5">
                    <div className="dataTables_info">
                      {gettext('Showing') + ' '}
                      {itemsPerPage * (page - 1) + 1} {gettext('to') + ' '}
                      {itemsPerPage * (page - 1) + tableData.length}{' '}
                      {gettext('of') + ' '}
                      {activeTab == 'all'
                        ? allCount
                        : activeTab == 'purchases'
                        ? purchasesCount
                        : activeTab == 'pending'
                        ? pendingCount
                        : activeTab == 'approved'
                        ? approvedCount
                        : activeTab == 'error' && errorCount}{' '}
                      {gettext('entries')}
                    </div>
                  </div>
                  <div className="col-sm-12 col-md-7">
                    <Pagination
                      page={page}
                      itemsPerPage={itemsPerPage}
                      totalItems={
                        activeTab == 'all'
                          ? allCount
                          : activeTab == 'purchases'
                          ? purchasesCount
                          : activeTab == 'pending'
                          ? pendingCount
                          : activeTab == 'approved'
                          ? approvedCount
                          : activeTab == 'error' && errorCount
                      }
                      paginate={this.paginate.bind(this)}
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

Transactions.contextType = CrmContext;
export default Transactions;
