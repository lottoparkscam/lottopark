import React from 'react';
import CrmContext from '../../../helpers/context';
import DonutChart from '../../elements/donutChart';
import Chart from '../../elements/chart';
import Pagination from '../../elements/pagination';
import Loading from '../../elements/loading';
import ChartistTooltip from 'chartist-plugin-tooltips-updated';
import Legend from 'chartist-plugin-legend';
import axios from '../../../helpers/interceptors';
import Swal from 'sweetalert2';
import { Link } from 'react-router-dom';
import TableLengthSelect from '../../elements/table-length-select';
import DateRangeSelect from '../../elements/date-range-select';
import TableTabs from '../../elements/table-tabs';
import * as utils from '../../../helpers/utils';
import TableFilters from '../../elements/table-filters';
import DataWithTooltip from '../../elements/data-with-tooltip';
import List from '../../elements/list';
import { changeDateFormatForCarbon } from '../../../helpers/date';

const tabsList = [
  {
    name: 'all_per_method',
    label: 'Per Method',
    icon: 'ti-home',
    columns: [
      {
        name: 'name',
        label: 'Withdrawal',
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
        name: 'canceled_rate',
        label: 'Canceled Rate',
        filterType: List.filters.percent,
      },
      {
        name: 'canceled_count',
        label: 'Canceled Count',
        filterType: List.filters.integer,
      },
      {
        name: 'canceled_amount',
        label: 'Canceled Amount',
        filterType: List.filters.integer,
      },
      {
        name: 'declined_rate',
        label: 'Declined Rate',
        filterType: List.filters.percent,
      },
      {
        name: 'declined_count',
        label: 'Declined Count',
        filterType: List.filters.integer,
      },
      {
        name: 'declined_amount',
        label: 'Declined Amount',
        filterType: List.filters.integer,
      },
    ],
  },
];
class Withdrawals extends React.Component {
  constructor(props) {
    super(props);

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
    this.timeout = null;
    this.state = {
      whitelabel_id: null,
      donutChartLoading: true,
      chartLoading: true,
      tableLoading: true,
      exportLoading: false,
      date: [new Date(), new Date()],
      showDatePicker: false,
      selectDateValue: 'month',
      chartData: null,
      activeTab: 'all',
      filters: [],
      page: 1,
      itemsPerPage: 50,
      sortBy: 'date',
      order: 'DESC',
      tabs: [],
      counts: {
        all: 0,
        pending: 0,
        approved: 0,
        declined: 0,
        canceled: 0,
      },
      columns: [
        { name: 'token', filter: '' },
        { name: 'user_name', filter: '' },
        { name: 'method', filter: '' },
        { name: 'user_balance', filter: ['', ''] },
        { name: 'amount', filter: ['', ''] },
        { name: 'date', filter: '' },
        { name: 'date_confirmed', filter: '' },
        { name: 'user_prize_group_name', filter: '' },
        { name: 'status', filter: '' },
        { name: 'request_details', filter: '' },
      ],
      columnsNames: null,
      pendingCountForChart: 0,
      approvedCountForChart: 0,
      declinedCountForChart: 0,
      canceledCountForChart: 0,
      allWithdrawalNumberForChart: 0,
      pendingAmountSumDisplayForChart: '0',
      approvedAmountSumDisplayForChart: '0',
      declinedAmountSumDisplayForChart: '0',
      canceledAmountSumDisplayForChart: '0',
      donutChartColumns: [['', 1]],
      tableData: [],
      methods: [],
      startDate: changeDateFormatForCarbon(firstDayOfCurrentMonth),
      endDate: changeDateFormatForCarbon(lastDayOfCurrentMonth),
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    const { gettext } = this.context.textdomain;
    let whitelabel_id = this.context.choosedWhitelabel.id;
    let tabs = [
      { name: 'all', title: gettext('All'), icon: 'ti-home' },
      { name: 'pending', title: gettext('Pending'), icon: 'ti-timer' },
      { name: 'approved', title: gettext('Approved'), icon: 'ti-check-box' },
      { name: 'declined', title: gettext('Declined'), icon: 'ti-archive' },
      { name: 'canceled', title: gettext('Canceled'), icon: 'ti-close' },
    ];
    let userColumnName = gettext('User Token • User Name • User E-mail');
    if (this.context.useLoginsForUsers()) {
      userColumnName = gettext(
        'User Token • User Name • User E-mail • User Login',
      );
    }
    let columnsNames = {
      token: gettext('Token'),
      user_name: userColumnName,
      method: gettext('Method'),
      user_balance: gettext(
        this.props.isCasino ? 'Casino balance' : 'User balance',
      ),
      amount: gettext('Amount'),
      date: gettext('Date'),
      date_confirmed: gettext('Date confirmed'),
      user_prize_group_name: gettext('Prize payout group'),
      status: gettext('Status'),
      request_details: gettext('Request details'),
    };
    this.setState({ columnsNames, whitelabel_id, tabs }, () =>
      this.fetchTableData(),
    );
    axios
      .post('/crm/withdrawals/data', {
        whitelabel_id,
        isCasino: this.props.isCasino,
      })
      .then((res) => {
        let methods = res.data.methods;

        this.setState({
          methods,
          donutChartLoading: false,
        });
      });
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
        .post('/crm/withdrawals/table_data', {
          whitelabel_id,
          activeTab,
          filters,
          page,
          itemsPerPage,
          sortBy,
          order,
          isCasino: this.props.isCasino,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let tableData = res.data.table_data;

            let counts = {
              all: res.data.all,
              pending: res.data.pending,
              approved: res.data.approved,
              declined: res.data.declined,
              canceled: res.data.canceled,
            };
            this.setState({
              counts,
              tableData,
              tableLoading: false,
            });
          }
        });
    } catch (e) {
      console.log(e);
    }
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
        .post('/crm/withdrawals/data_date_range', {
          whitelabel_id,
          start_date: start,
          end_date: end,
          language_code,
          isCasino: this.props.isCasino,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let pending = res.data.pending;
            let approved = res.data.approved;
            let declined = res.data.declined;
            let canceled = res.data.canceled;

            this.prepareDonutChartStates(pending, approved, canceled, declined);
            this.prepareChart(
              start_date,
              end_date,
              pending.groupByDatePerMonth,
              approved.groupByDatePerMonth,
              declined.groupByDatePerMonth,
              canceled.groupByDatePerMonth,
            );
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  prepareDonutChartStates(pending, approved, canceled, declined) {
    const { gettext } = this.context.textdomain;
    let donutChartColumns = [
      [gettext('Pending'), 0],
      [gettext('Approved'), 0],
      [gettext('Declined'), 0],
      [gettext('Canceled'), 0],
    ];
    this.setState({
      donutChartLoading: true,
      pendingAmountSumDisplayForChart: '0',
      approvedAmountSumDisplayForChart: '0',
      declinedAmountSumDisplayForChart: '0',
      canceledAmountSumDisplayForChart: '0',
      pendingCountForChart: 0,
      approvedCountForChart: 0,
      declinedCountForChart: 0,
      canceledCountForChart: 0,
      allWithdrawalNumberForChart: 0,
      donutChartColumns: donutChartColumns,
    });

    const isPendingLengthBiggerThanZero =
      pending.groupByDatePerMonth.length > 0;
    const isApprovedLengthBiggerThanZero =
      approved.groupByDatePerMonth.length > 0;
    const isDeclinedLengthBiggerThanZero =
      declined.groupByDatePerMonth.length > 0;
    const isCanceledLengthBiggerThanZero =
      canceled.groupByDatePerMonth.length > 0;

    if (
      !isPendingLengthBiggerThanZero &
      !isApprovedLengthBiggerThanZero &
      !isDeclinedLengthBiggerThanZero &
      !isCanceledLengthBiggerThanZero
    ) {
      donutChartColumns.push(['', 1]);
    }
    let allWithdrawalNumberForChart = 0;
    if (isPendingLengthBiggerThanZero) {
      const pendingCountForChart = pending.additionalData.count;
      donutChartColumns[0] = [gettext('Pending'), pendingCountForChart];
      allWithdrawalNumberForChart += parseInt(pendingCountForChart);
      this.setState({
        pendingAmountSumDisplayForChart:
          pending.additionalData.amountSumDisplay,
        pendingCountForChart: pendingCountForChart,
        donutChartColumns: donutChartColumns,
      });
    }

    if (isApprovedLengthBiggerThanZero) {
      const approvedCountForChart = approved.additionalData.count;
      donutChartColumns[1] = [gettext('Approved'), approvedCountForChart];
      allWithdrawalNumberForChart += parseInt(approvedCountForChart);
      this.setState({
        approvedAmountSumDisplayForChart:
          approved.additionalData.amountSumDisplay,
        approvedCountForChart: approvedCountForChart,
        donutChartColumns: donutChartColumns,
      });
    }

    if (isDeclinedLengthBiggerThanZero) {
      const declinedCountForChart = declined.additionalData.count;
      donutChartColumns[2] = [gettext('Declined'), declinedCountForChart];
      allWithdrawalNumberForChart += parseInt(declinedCountForChart);
      this.setState({
        declinedAmountSumDisplayForChart:
          declined.additionalData.amountSumDisplay,
        declinedCountForChart: declinedCountForChart,
        donutChartColumns: donutChartColumns,
      });
    }

    if (isCanceledLengthBiggerThanZero) {
      const canceledCountForChart = canceled.additionalData.count;
      donutChartColumns[3] = [gettext('Canceled'), canceledCountForChart];
      allWithdrawalNumberForChart += parseInt(canceledCountForChart);
      this.setState({
        canceledAmountSumDisplayForChart:
          canceled.additionalData.amountSumDisplay,
        canceledCountForChart: canceledCountForChart,
        donutChartColumns: donutChartColumns,
      });
    }

    this.setState({
      donutChartLoading: false,
      allWithdrawalNumberForChart,
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

  prepareChart(start_date, end_date, pending, approved, declined, canceled) {
    let locale = this.context.user.code.replace('_', '-');
    var numeric = { day: 'numeric', month: 'numeric' };
    const { gettext } = this.context.textdomain;
    let max = 0;

    let chartData = {};
    let x = [];
    let y1 = [];
    let y2 = [];
    let y3 = [];
    let y4 = [];

    let days = this.getDaysArray(end_date, start_date);

    for (var i = 0; i < days.length; i++) {
      x.push(days[i].toLocaleDateString(locale, numeric));
      y1.push(0);
      y2.push(0);
      y3.push(0);
      y4.push(0);
    }

    let interpolation = Math.floor(x.length / 5);

    pending.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y1[index] = data.count;
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
      }
    });

    approved.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y2[index] = data.count;
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
      }
    });

    declined.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y3[index] = data.count;
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
      }
    });

    canceled.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y4[index] = data.count;
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
    y4 = y4.map(function (v, idx) {
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
            gettext('Declined'),
            gettext('Canceled'),
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
    chartData['series'] = [y1, y2, y3, y4];
    chartData['options'] = chartOptions;

    this.setState({
      chartData,
      chartLoading: false,
    });
  }

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

  onDateChange = (date) => {
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

  setActiveTab(activeTab) {
    this.setState({ activeTab, page: 1, tableLoading: true }, () => {
      this.fetchTableData();
    });
  }

  paginate = (page) => {
    this.setState({ page, tableLoading: true }, () => this.fetchTableData());
  };

  handleChange(e) {
    let itemsPerPage = e.target.value;
    this.setState(
      {
        itemsPerPage,
        page: 1,
        loading: true,
      },
      () => this.fetchTableData(),
    );
  }

  declineWithdrawal(token, whitelabel) {
    let url = '/crm/withdrawal/decline';
    this.withdrawalAction(url, token, whitelabel);
  }
  approveWithdrawal(token, whitelabel) {
    let url = '/crm/withdrawal/approve';
    this.withdrawalAction(url, token, whitelabel);
  }

  withdrawalAction(url, token, whitelabel) {
    const { gettext } = this.context.textdomain;

    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Yes'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        let whitelabel_id = this.context.choosedWhitelabel.id;
        try {
          axios
            .post(url, {
              token,
              whitelabel,
              whitelabel_id,
              isCasino: this.props.isCasino,
            })
            .then((res) => {
              if (res.data.code == 200) {
                this.fetchTableData();
                let message = res.data.message;
                this.context.showToast('success', message);
              } else if (res.data.code == 400) {
                let message = res.data.message;
                this.context.showToast('error', message);
              }
            });
        } catch (e) {
          console.log(e);
        }
      }
    });
  }

  downloadCSV() {
    const { whitelabel_id, activeTab, filters } = this.state;
    const FileDownload = require('js-file-download');
    this.setState({ exportLoading: true });
    try {
      axios
        .post('/crm/withdrawals/export', {
          whitelabel_id,
          activeTab,
          filters,
          isCasino: this.props.isCasino,
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
            'withdrawals_' +
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
      console.log(e);
    }
  }

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

  render() {
    const { gettext } = this.context.textdomain;
    const {
      donutChartLoading,
      chartLoading,
      tableLoading,
      exportLoading,
      date,
      showDatePicker,
      page,
      itemsPerPage,
      sortBy,
      order,
      selectDateValue,
      chartData,
      activeTab,
      columns,
      columnsNames,
      tableData,
      tabs,
      counts,
      methods,
      canceledAmountSumDisplayForChart,
      pendingAmountSumDisplayForChart,
      approvedAmountSumDisplayForChart,
      declinedAmountSumDisplayForChart,
      donutChartColumns,
      pendingCountForChart,
      approvedCountForChart,
      declinedCountForChart,
      canceledCountForChart,
      allWithdrawalNumberForChart,
      startDate,
      endDate,
    } = this.state;
    const columnClasses = (columnName) => {
      let classes = '';

      if (columnName === 'request_details') {
        classes += 'text-nowrap';
      }

      return classes;
    };

    let statusOptions = [
      <option value="" key="optnull">
        --
      </option>,
      <option value="0" key="opt0">
        {gettext('Pending')}
      </option>,
      <option value="1" key="opt1">
        {gettext('Approved')}
      </option>,
      <option value="2" key="opt2">
        {gettext('Declined')}
      </option>,
      <option value="3" key="opt3">
        {gettext('Canceled')}
      </option>,
    ];

    let methodsOptions = [
      <option value="" key="optnull">
        --
      </option>,
    ];
    methods.forEach((method, i) => {
      methodsOptions.push(
        <option value={method.id} key={'opt' + i}>
          {method.name}
        </option>,
      );
    });

    let donutColors = ['#c9d9ea', '#5ac146', '#ff6b40', '#ffbc34'];
    const endpoint = `/crm/withdrawal_report_per_method?startDate=${startDate}&endDate=${endDate}&isCasino=${this.props.isCasino}`;

    return (
      <div className="withdrawals-page">
        <DateRangeSelect
          name={'Withdrawals'}
          date={date}
          showDatePicker={showDatePicker}
          onDateChange={this.onDateChange.bind(this)}
          selectDateValue={selectDateValue}
          selectedRange={this.selectedRange.bind(this)}
          customClass={'data-picker-class'}
        />
        <div className="row">
          <div className="col-lg-3 col-md-12">
            <div className="card transactions-h-315">
              <div className="card-body">
                <h4 className="card-title">{gettext('Withdrawals')}</h4>
                {donutChartLoading ? (
                  <Loading />
                ) : (
                  <div>
                    <div className="m-t-10 c3 crm-chart donut-chart">
                      <DonutChart
                        columns={donutChartColumns}
                        element="withdrawals-chart"
                        colors={donutColors}
                      />
                    </div>
                    <div className="donut-chart-additional-data text-center m-b-30">
                      <div className="col">
                        <h4 className="m-b-0 font-medium">
                          {(allWithdrawalNumberForChart == 0
                            ? 0
                            : (pendingCountForChart /
                                allWithdrawalNumberForChart) *
                              100
                          ).toFixed(2) + '%'}
                        </h4>
                        <span className="text-muted">
                          {gettext('Pending')}({pendingCountForChart})
                        </span>
                        <br />
                        <span>{pendingAmountSumDisplayForChart}</span>
                      </div>
                      <div className="col">
                        <h4 className="m-b-0 font-medium">
                          {(allWithdrawalNumberForChart == 0
                            ? 0
                            : (approvedCountForChart /
                                allWithdrawalNumberForChart) *
                              100
                          ).toFixed(2) + '%'}
                        </h4>
                        <span className="text-muted">
                          {gettext('Approved')}({approvedCountForChart})
                        </span>
                        <br />
                        <span>{approvedAmountSumDisplayForChart}</span>
                      </div>
                      <div className="col">
                        <h4 className="m-b-0 font-medium">
                          {(allWithdrawalNumberForChart == 0
                            ? 0
                            : (declinedCountForChart /
                                allWithdrawalNumberForChart) *
                              100
                          ).toFixed(2) + '%'}
                        </h4>
                        <span className="text-muted">
                          {gettext('Declined')}({declinedCountForChart})
                        </span>
                        <br />
                        <span>{declinedAmountSumDisplayForChart}</span>
                      </div>
                      <div className="col">
                        <h4 className="m-b-0 font-medium">
                          {(allWithdrawalNumberForChart == 0
                            ? 0
                            : (canceledCountForChart /
                                allWithdrawalNumberForChart) *
                              100
                          ).toFixed(2) + '%'}
                        </h4>
                        <span className="text-muted">
                          {gettext('Canceled')}({canceledCountForChart})
                        </span>
                        <br />
                        <span>{canceledAmountSumDisplayForChart}</span>
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
                    {gettext('Withdrawals')}
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
        <div className="row">
          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('Withdrawals')}</h4>
                <TableTabs
                  tabs={tabs}
                  counts={counts}
                  setActiveTab={this.setActiveTab.bind(this)}
                  activeTab={activeTab}
                />
                <div className="table-responsive">
                  <div className="container-fluid">
                    <div className="row m-t-25 m-b-15">
                      <div className="col-sm-12 col-md-6">
                        <TableLengthSelect
                          handleChange={this.handleChange.bind(this)}
                          itemsPerPage={itemsPerPage}
                        />
                      </div>
                      <div className="col-sm-12 col-md-6">
                        <div className="text-right loader-small">
                          {exportLoading ? (
                            <Loading />
                          ) : (
                            <button
                              type="button"
                              className="btn btn-outline-info m-r-10"
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
                      {tableLoading ? (
                        <Loading />
                      ) : (
                        <table className="table table-striped table-bordered datatable-select-inputs">
                          <thead>
                            <tr>
                              {columnsNames &&
                                columns.map((col) => (
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
                                ))}
                              <th>{gettext('Manage')}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr className="table-search">
                              {
                                <TableFilters
                                  columns={columns}
                                  filterDate={this.filterDate.bind(this)}
                                  filterDateConfirmed={this.filterDateConfirmed.bind(
                                    this,
                                  )}
                                  filterAmounts={this.filterAmounts.bind(this)}
                                  filterList={this.filterList.bind(this)}
                                  statusOptions={statusOptions}
                                  methodsOptions={methodsOptions}
                                />
                              }
                              <td></td>
                            </tr>
                            {tableData.length > 0 ? (
                              tableData.map((withdrawal, i) => (
                                <tr key={i}>
                                  {columns.map((col) => (
                                    <td
                                      key={'td' + i + col.name}
                                      className={columnClasses(col.name)}
                                    >
                                      {col.name == 'token' ? (
                                        withdrawal.full_token
                                      ) : col.name == 'user_name' ? (
                                        withdrawal.user_token_full +
                                        ' • ' +
                                        withdrawal.user_fullname +
                                        ' • ' +
                                        withdrawal.user_email +
                                        (this.context.useLoginsForUsers()
                                          ? ' • ' + withdrawal.user_login
                                          : '')
                                      ) : col.name == 'user_balance' ? (
                                        <DataWithTooltip
                                          key={'balance' + i}
                                          value={
                                            withdrawal.user_balance_display
                                          }
                                          tooltipValue={
                                            withdrawal.balance_additional_text
                                          }
                                          additionalClass={
                                            withdrawal.user_balance_class_danger
                                          }
                                        />
                                      ) : col.name == 'amount' ? (
                                        <DataWithTooltip
                                          key={'amount' + i}
                                          value={withdrawal.amount_display}
                                          tooltipValue={
                                            withdrawal.amount_additional_text
                                          }
                                        />
                                      ) : col.name == 'status' ? (
                                        withdrawal.status_display
                                      ) : col.name == 'method' ? (
                                        withdrawal.method_name
                                      ) : col.name === 'request_details' ? (
                                        <pre>{withdrawal[col.name]}</pre>
                                      ) : (
                                        withdrawal[col.name]
                                      )}
                                    </td>
                                  ))}
                                  <td>
                                    <div className="users-tooltip">
                                      <Link
                                        to={{
                                          pathname: `/crm/withdrawals/${
                                            this.props.isCasino
                                              ? 'casino/'
                                              : 'lottery/'
                                          }view/${withdrawal.token}`,
                                        }}
                                      >
                                        <i className="users-edit mdi mdi-view-list" />
                                      </Link>

                                      <span className="users-tooltiptext">
                                        {gettext('Details')}
                                      </span>
                                    </div>
                                    {this.context.showLink(
                                      this.props.isCasino
                                        ? 'casino-withdrawals-edit'
                                        : 'withdrawals-edit',
                                    ) && [
                                      withdrawal.status == '0' && (
                                        <div
                                          className="users-tooltip"
                                          key="button-approve"
                                        >
                                          <a
                                            onClick={() => {
                                              this.approveWithdrawal(
                                                withdrawal.token,
                                                withdrawal.whitelabel_id,
                                              );
                                            }}
                                          >
                                            <i
                                              className={
                                                'link users-edit text-success mdi mdi-checkbox-marked'
                                              }
                                            />
                                          </a>

                                          <span className="users-tooltiptext">
                                            {gettext('Approve')}
                                          </span>
                                        </div>
                                      ),
                                      withdrawal.status == '0' && (
                                        <div
                                          className="users-tooltip"
                                          key="button-decline"
                                        >
                                          <a
                                            onClick={() => {
                                              this.declineWithdrawal(
                                                withdrawal.token,
                                                withdrawal.whitelabel_id,
                                              );
                                            }}
                                          >
                                            <i className="link users-edit text-danger mdi mdi-close-box" />
                                          </a>

                                          <span className="users-tooltiptext">
                                            {gettext('Decline')}
                                          </span>
                                        </div>
                                      ),
                                    ]}
                                  </td>
                                </tr>
                              ))
                            ) : (
                              <tr>
                                <td colSpan="10">
                                  {gettext('No withdrawals.')}
                                </td>
                              </tr>
                            )}
                            <tr className="table-search">
                              {
                                <TableFilters
                                  columns={columns}
                                  filterDate={this.filterDate.bind(this)}
                                  filterDateConfirmed={this.filterDateConfirmed.bind(
                                    this,
                                  )}
                                  filterAmounts={this.filterAmounts.bind(this)}
                                  filterList={this.filterList.bind(this)}
                                  statusOptions={statusOptions}
                                  methodsOptions={methodsOptions}
                                />
                              }
                              <td></td>
                            </tr>
                          </tbody>
                        </table>
                      )}
                    </div>
                    <div className="row">
                      <div className="col-sm-12 col-md-5">
                        <div
                          className="dataTables_info"
                          role="status"
                          aria-live="polite"
                        >
                          {gettext('Showing') + ' '}
                          {itemsPerPage * (page - 1) + 1} {gettext('to') + ' '}
                          {itemsPerPage * (page - 1) + tableData.length}{' '}
                          {gettext('of') + ' '}
                          {counts[activeTab]} {gettext('entries')}
                        </div>
                      </div>
                      <div className="col-sm-12 col-md-7">
                        <Pagination
                          page={page}
                          itemsPerPage={itemsPerPage}
                          totalItems={counts[activeTab]}
                          paginate={this.paginate.bind(this)}
                        />
                      </div>
                    </div>
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

Withdrawals.contextType = CrmContext;
export default Withdrawals;
