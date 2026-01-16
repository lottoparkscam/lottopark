import React from 'react';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Loading from '../../elements/loading';
import DateRangeSelect from '../../elements/date-range-select';
import TableTabs from '../../elements/table-tabs';
import Pagination from '../../elements/pagination';
import TableLengthSelect from '../../elements/table-length-select';
import DonutWithTable from '../../elements/donut-with-table';
import Chart from '../../elements/chart';
import ChartistTooltip from 'chartist-plugin-tooltips-updated';
import Legend from 'chartist-plugin-legend';
import { Link } from 'react-router-dom';
import * as utils from '../../../helpers/utils';
import DataWithTooltip from '../../elements/data-with-tooltip';
import { withRouter } from 'react-router-dom';
import TableFilters from '../../elements/table-filters';
import ViewUserLink from '../../elements/view-user-link';
import ViewTransactionLink from '../../elements/view-transaction-link';
import PricingValues from '../../elements/pricing-values';

class Tickets extends React.Component {
  constructor(props) {
    super(props);

    this.timeout = null;

    this.state = {
      whitelabel: null,
      multi_draw_id: null,
      donutChartLoading: true,
      chartLoading: true,
      tableLoading: true,
      exportLoading: false,
      dateForChart: [new Date(), new Date()],
      tableData: [],
      lotteries: [],
      lotteriesSum: 0,
      chartData: [],
      showDatePickerForChart: false,
      selectDateValueForChart: 'month',
      activeTab: 'all',
      tabs: [],
      counts: {
        all: 0,
        pending: 0,
        win: 0,
        nowinnings: 0,
        canceled: 0,
      },
      page: 1,
      itemsPerPage: 50,
      sortBy: 'token',
      order: 'ASC',
      filters: [],
      columns: [
        { name: 'token', filter: '' },
        { name: 'utoken', filter: '' },
        { name: 'pricing', filter: '' },
        { name: 'amount', filter: ['', ''] },
        { name: 'bonus_amount', filter: ['', ''] },
        { name: 'date', filter: '' },
        { name: 'draw_date', filter: '' },
        { name: 'status', filter: '' },
        { name: 'prize', filter: ['', ''] },
        { name: 'payout', filter: '' },
        { name: 'line_count', filter: ['', ''] },
        { name: 'prize_payout_percent', filter: ['', ''] },
      ],
      columnsNames: null,
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    const { gettext } = this.context.textdomain;
    const whitelabel = this.context.choosedWhitelabel.id;
    let columns = this.state.columns;

    let query_params = this.props.location.query;
    if (query_params) {
      if (query_params.multi_draw_id) {
        let multi_draw_id = query_params.multi_draw_id;
        this.setState({ multi_draw_id });
      } else {
        let filters, columnsUpdated;
        [filters, columnsUpdated] = utils.prepareFiltersFromQuery(
          columns,
          query_params,
        );
        this.setState({ columns, filters });
      }
    }

    let tabs = [
      { name: 'all', title: gettext('All'), icon: 'ti-home' },
      { name: 'pending', title: gettext('Pending'), icon: 'ti-timer' },
      { name: 'win', title: gettext('Win'), icon: 'ti-cup' },
      { name: 'nowinnings', title: gettext('No winnings'), icon: 'ti-archive' },
      { name: 'canceled', title: gettext('Canceled'), icon: 'ti-close' },
    ];

    let userColumnName = gettext('User Token • User Name • User E-mail');
    if (
      this.context.choosedWhitelabel.id == '0' ||
      this.context.choosedWhitelabel.use_logins_for_users == '1'
    ) {
      userColumnName = gettext(
        'User Token • User Name • User E-mail • User Login',
      );
    }
    let columnsNames = {
      token: gettext('ID • Transaction ID • Lottery'),
      utoken: userColumnName,
      pricing: gettext('Pricing'),
      amount: gettext('Amount'),
      bonus_amount: gettext('Bonus amount'),
      date: gettext('Date'),
      draw_date: gettext('Draw Date'),
      status: gettext('Status'),
      prize: gettext('Prize'),
      payout: gettext('Paid out'),
      line_count: gettext('Lines'),
      prize_payout_percent: gettext('Prize payout'),
    };
    this.setState({ whitelabel, tabs, columnsNames }, () => {
      this.fetchTableData();
      this.setDays();
    });
  }

  fetchTableData() {
    const {
      whitelabel,
      multi_draw_id,
      activeTab,
      filters,
      page,
      itemsPerPage,
      sortBy,
      order,
    } = this.state;
    try {
      axios
        .post('/crm/tickets/table_data', {
          whitelabel,
          multi_draw_id,
          activeTab,
          filters,
          page,
          itemsPerPage,
          sortBy,
          order,
        })
        .then((res) => {
          let tableData = res.data.data;
          let all = res.data.all_count;
          let pending = res.data.pending_count;
          let win = res.data.win_count;
          let nowinnings = res.data.no_winnings_count;
          let canceled = res.data.canceled_count;
          let counts = {
            all,
            pending,
            win,
            nowinnings,
            canceled,
          };
          this.setState({ tableLoading: false, tableData, counts });
        });
    } catch (e) {
      console.log(e);
    }
  }

  dateForChartsWasChanged = (dateForChart) => {
    if (dateForChart) {
      this.setState({ dateForChart, selectDateValueForChart: 'range' }, () => {
        this.setDays();
      });
    } else {
      this.setState(
        {
          showDatePickerForChart: false,
          selectDateValueForChart: 'month',
        },
        () => {
          this.setDays();
        },
      );
    }
  };

  selectedRange(e) {
    let target = e.target;
    if (target.value === 'range') {
      this.setState({ ['showDatePickerForChart']: true });
    } else {
      let option = target.value;
      this.setState({ ['selectDateValueForChart']: option }, () => {
        this.setDays(target.name);
      });
    }
  }

  setDays() {
    let startDate;
    let endDate;
    let option = this.state['selectDateValueForChart'];

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
      endDate = this.state.dateForChart[1];
      startDate = this.state.dateForChart[0];
    }
    this.fetchForChartsData(startDate, endDate, option);
  }

  fetchForChartsData(start_date, end_date, option) {
    this.setState({
      donutChartLoading: true,
      chartLoading: true,
    });
    let urlForChartData = '/crm/tickets/lotteries_data';
    let urlForDonutChartData = '/crm/tickets/lines';

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

    const { whitelabel } = this.state;
    try {
      axios
        .post(urlForChartData, {
          whitelabel,
          start,
          end,
          option,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let lotteries = res.data.lotteries;
            let sum = 0;
            lotteries.forEach((lottery) => {
              sum += parseInt(lottery.count);
            });

            this.setState({
              lotteries,
              lotteriesSum: sum,
              donutChartLoading: false,
            });
          }
        });
      axios
        .post(urlForDonutChartData, {
          whitelabel,
          start,
          end,
          option,
        })
        .then((res) => {
          if (res.data.code == 200) {
            this.prepareCharts(start_date, end_date, res.data.tickets);
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  setActiveTab(activeTab) {
    let columns = this.state.columns;
    let index = columns.findIndex((x) => {
      return x.name == 'status';
    });
    if (activeTab == 'all') {
      if (index === -1) columns.splice(6, 0, { name: 'status', filter: '' });
    } else {
      if (index > -1) columns.splice(index, 1);
    }

    this.setState({ activeTab, page: 1, columns, dataLoading: true }, () => {
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

  prepareCharts(startDate, endDate, tickets) {
    let locale = this.context.user.code.replace('_', '-');
    var numeric = { day: 'numeric', month: 'numeric' };
    const { gettext } = this.context.textdomain;
    let max = 0;

    let chartData = {};
    let x = [];
    let y = [];

    let days = utils.getDaysArray(endDate, startDate);

    for (var i = 0; i < days.length; i++) {
      x.push(days[i].toLocaleDateString(locale, numeric));
      y.push(0);
    }

    let interpolation = Math.floor(x.length / 5);

    tickets.forEach((data) => {
      let priorDate = new Date(data.date).toLocaleDateString(locale, numeric);
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y[index] = data.count;
        if (parseFloat(data.count) > max) max = parseFloat(data.count);
      }
    });

    y = y.map(function (v, idx) {
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
          legendNames: [gettext('Tickets (lines)')],
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
    chartData['series'] = [y];
    chartData['max'] = max;
    chartData['options'] = chartOptions;

    this.setState({
      chartData,
      chartLoading: false,
    });
  }

  sortBy(col) {
    if (col === 'pricing') return;
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

  downloadCSV() {
    const { whitelabel, activeTab, filters } = this.state;
    const FileDownload = require('js-file-download');
    this.setState({ exportLoading: true });
    try {
      axios
        .post('/crm/tickets/export', {
          whitelabel,
          activeTab,
          filters,
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
            'tickets_' +
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

  filterDrawDate(date) {
    let colName = 'draw_date';
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

  filterList(e) {
    if (this.timeout) clearTimeout(this.timeout);
    var name = e.target.name;
    let value = e.target.value;

    let columns = this.state.columns;
    let filters = this.state.filters;
    let index = columns.findIndex((x) => x.name == name);
    columns[index].filter = value;
    this.setState({ columns });
    this.timeout = setTimeout(() => {
      filters = utils.prepareFilters(filters, name, value);
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

  filterAmounts(e) {
    if (this.timeout) clearTimeout(this.timeout);
    let name = e.target.name;
    let value = e.target.value;
    const { columns, filters } = this.state;

    let updatedColumns, updatedFilters;
    [updatedColumns, updatedFilters] = utils.filterAmounts(
      columns,
      filters,
      name,
      value,
    );

    this.setState(
      { columns: updatedColumns, filters: updatedFilters, page: 1 },
      () => {
        this.timeout = setTimeout(() => this.fetchTableData(), 1000);
      },
    );
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      donutChartLoading,
      chartLoading,
      tableLoading,
      dateForChart,
      chartData,
      tableData,
      lotteries,
      lotteriesSum,
      showDatePickerForChart,
      selectDateValueForChart,
      tabs,
      counts,
      activeTab,
      page,
      itemsPerPage,
      exportLoading,
      columns,
      columnsNames,
      sortBy,
      order,
    } = this.state;
    let statusOptions = [
      <option key={0} value="">
        --
      </option>,
      <option key={1} value="0">
        {gettext('Pending')}
      </option>,
      <option key={2} value="1">
        {gettext('Win')}
      </option>,
      <option key={3} value="2">
        {gettext('No winnings')}
      </option>,
      <option key={4} value="4">
        {gettext('Canceled')}
      </option>,
    ];

    let useLoginsForUsers = false;
    if (
      this.context.choosedWhitelabel.id == '0' ||
      this.context.choosedWhitelabel.use_logins_for_users == '1'
    ) {
      useLoginsForUsers = true;
    }

    let headers = [
      gettext('Lottery'),
      gettext('%'),
      gettext('Tickets (lines)'),
      gettext('Amount'),
    ];

    return (
      <div className="module-tickets">
        <DateRangeSelect
          name={'WithdrawalChart'}
          date={dateForChart}
          showDatePicker={showDatePickerForChart}
          onDateChange={this.dateForChartsWasChanged}
          selectDateValue={selectDateValueForChart}
          selectedRange={this.selectedRange.bind(this)}
          customClass={'data-picker-class'}
        />
        <div className="row">
          <div className="col-lg-3 col-md-6">
            <div className="card">
              <div className="card-body">
                <div className="row no-gutters align-items-center">
                  <h4 className="card-title">{gettext('Top lotteries')}</h4>
                  {donutChartLoading ? (
                    <Loading />
                  ) : (
                    <DonutWithTable
                      element={'Withdrawal'}
                      sum={lotteriesSum}
                      dataTable={lotteries}
                      headers={headers}
                    />
                  )}
                </div>
              </div>
            </div>
          </div>
          <div className="col-lg-9 col-md-12 order-lg-0 order-md-3">
            <div className="card">
              <div className="card-body">
                <div className="row no-gutters align-items-center">
                  <h4 className="card-title">{gettext('Tickets')}</h4>
                </div>
                <div>
                  <div className="sales ct-charts m-b-20 m-t-5">
                    {chartLoading ? (
                      <Loading />
                    ) : (
                      <Chart
                        labels={chartData.labels}
                        series={chartData.series}
                        options={chartData.options}
                        element={'chart-tickets'}
                      />
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="row">
          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('Tickets')}</h4>
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
                                      col.name == 'pricing'
                                        ? ''
                                        : 'sorting' +
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
                                  filterDrawDate={this.filterDrawDate.bind(
                                    this,
                                  )}
                                  filterAmounts={this.filterAmounts.bind(this)}
                                  filterList={this.filterList.bind(this)}
                                  statusOptions={statusOptions}
                                />
                              }
                              <td></td>
                            </tr>
                            {tableData.map((ticket, i) => (
                              <tr key={i}>
                                {columns.map((col) => (
                                  <td key={'td' + i + col.name}>
                                    {col.name == 'utoken' ? (
                                      ticket['user_full_token'] +
                                      ' • ' +
                                      ticket['user_fullname'] +
                                      ' • ' +
                                      ticket['email'] +
                                      (this.context.useLoginsForUsers()
                                        ? ' • ' + ticket['user_login']
                                        : '')
                                    ) : col.name == 'token' ? (
                                      [
                                        <p
                                          className="m-b-0"
                                          key={'p_token' + i}
                                        >
                                          {ticket['full_token']}
                                        </p>,
                                        <p
                                          className="m-b-0"
                                          key={'p_ttoken' + i}
                                        >
                                          {ticket['transaction_full_token']}
                                        </p>,
                                        <p
                                          className="m-b-0"
                                          key={'p_lname' + i}
                                        >
                                          {ticket['lname']}
                                        </p>,
                                      ]
                                    ) : col.name == 'amount' ? (
                                      <DataWithTooltip
                                        key={'amount' + i}
                                        value={ticket['amount_display']}
                                        tooltipValue={ticket['amounts_other']}
                                      />
                                    ) : col.name == 'bonus_amount' ? (
                                      <DataWithTooltip
                                        key={'bonus_amount' + i}
                                        value={ticket['bonus_amount_display']}
                                        tooltipValue={
                                          ticket['bonus_amounts_other']
                                        }
                                      />
                                    ) : col.name == 'pricing' ? (
                                      <PricingValues ticket={ticket} />
                                    ) : col.name == 'prize' ? (
                                      ticket['status_win'] && [
                                        ticket['jackpot_prize_text'] &&
                                          ticket['jackpot_prize_text'],
                                        ticket['prize_display'] && (
                                          <DataWithTooltip
                                            key={'prize' + i}
                                            title={gettext('Prize')}
                                            value={ticket['prize_display']}
                                            tooltipValue={
                                              ticket['prizes_other']
                                            }
                                          />
                                        ),
                                        ticket['prize_net_display'] && (
                                          <DataWithTooltip
                                            key={'net' + i}
                                            title={gettext('Net')}
                                            value={ticket['prize_net_display']}
                                            tooltipValue={
                                              ticket['prizes_net_other']
                                            }
                                          />
                                        ),
                                      ]
                                    ) : col.name == 'payout' ? (
                                      ticket['status_win'] && (
                                        <span
                                          className={ticket['payout_class']}
                                        >
                                          {ticket['payout_display']}
                                        </span>
                                      )
                                    ) : col.name == 'status' ? (
                                      [
                                        ticket['status_display'],
                                        ticket['status_extra_text'] && (
                                          <p
                                            key={i + 'status'}
                                            className="text-warning"
                                          >
                                            {ticket['status_extra_text']}
                                          </p>
                                        ),
                                      ]
                                    ) : col.name == 'draw_date' ? (
                                      ticket['draw_date_display']
                                    ) : col.name == 'prize_payout_percent' ? (
                                      ticket['prize_payout_display']
                                    ) : (
                                      ticket[col.name]
                                    )}
                                    {col.name == 'utoken' && [
                                      <ViewUserLink
                                        key={'user-view' + i}
                                        token={ticket.user_full_token}
                                      />,
                                      <ViewTransactionLink
                                        key={'transaction-view' + i}
                                        token={ticket.transaction_full_token}
                                      />,
                                    ]}
                                  </td>
                                ))}
                                <td>
                                  <div className="users-tooltip">
                                    <Link
                                      to={{
                                        pathname: `/crm/tickets/${ticket.full_token}`,
                                      }}
                                    >
                                      <i className="users-edit mdi mdi-view-list" />
                                    </Link>
                                    <span className="users-tooltiptext">
                                      {gettext('Details')}
                                    </span>
                                  </div>
                                  {ticket['mtoken'] && (
                                    <div className="users-tooltip">
                                      <Link
                                        className="text-warning"
                                        to={{
                                          pathname: '/crm/multidraw_tickets',
                                          query: { token: ticket['mtoken'] },
                                        }}
                                      >
                                        <i className="users-edit mdi mdi-view-list" />
                                      </Link>
                                      <span className="users-tooltiptext">
                                        {gettext('View Multi-Draw')}
                                      </span>
                                    </div>
                                  )}
                                </td>
                              </tr>
                            ))}
                            <tr className="table-search">
                              {
                                <TableFilters
                                  columns={columns}
                                  filterDate={this.filterDate.bind(this)}
                                  filterDrawDate={this.filterDrawDate.bind(
                                    this,
                                  )}
                                  filterAmounts={this.filterAmounts.bind(this)}
                                  filterList={this.filterList.bind(this)}
                                  statusOptions={statusOptions}
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

Tickets.contextType = CrmContext;
export default withRouter(Tickets);
