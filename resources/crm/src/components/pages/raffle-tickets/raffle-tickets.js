import React from 'react';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Loading from '../../elements/loading';
import TableTabs from '../../elements/table-tabs';
import Pagination from '../../elements/pagination';
import TableLengthSelect from '../../elements/table-length-select';
import { Link } from 'react-router-dom';
import * as utils from '../../../helpers/utils';
import TableFilters from '../../elements/table-filters';
import ViewUserLink from '../../elements/view-user-link';
import ViewTransactionLink from '../../elements/view-transaction-link';
import DataWithTooltip from '../../elements/data-with-tooltip';
import PricingValues from '../../elements/pricing-values';

class RaffleTickets extends React.Component {
  constructor(props) {
    super(props);

    this.timeout = null;

    this.state = {
      whitelabel: null,
      tableLoading: true,
      exportLoading: false,
      activeTab: 'all',
      tableData: [],
      tabs: [],
      counts: {
        all: 0,
        pending: 0,
        win: 0,
        nowinnings: 0,
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
        { name: 'status', filter: '' },
        { name: 'prize', filter: ['', ''] },
        { name: 'is_paid_out', filter: '' },
        { name: 'created_at', filter: '' },
        { name: 'draw_date', filter: '' },
      ],
      columnsNames: null,
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    const { gettext } = this.context.textdomain;

    let tabs = [
      { name: 'all', title: gettext('All'), icon: 'ti-home' },
      { name: 'pending', title: gettext('Pending'), icon: 'ti-timer' },
      { name: 'win', title: gettext('Win'), icon: 'ti-cup' },
      { name: 'nowinnings', title: gettext('No winnings'), icon: 'ti-archive' },
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
      token: gettext('ID • Transaction ID • Raffle'),
      utoken: userColumnName,
      pricing: gettext('Pricing'),
      amount: gettext('Amount'),
      bonus_amount: gettext('Bonus amount'),
      status: gettext('Status'),
      prize: gettext('Prize'),
      is_paid_out: gettext('Paid out'),
      created_at: gettext('Date'),
      draw_date: gettext('Draw date'),
    };
    const whitelabel = this.context.choosedWhitelabel.id;
    this.setState({ whitelabel, tabs, columnsNames }, () => {
      this.fetchTableData();
    });
  }

  fetchTableData() {
    const {
      whitelabel,
      activeTab,
      filters,
      page,
      itemsPerPage,
      sortBy,
      order,
    } = this.state;
    try {
      axios
        .post('/crm/raffle_tickets/table_data', {
          whitelabel,
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
          let nowinnings = res.data.nowinnings_count;
          let counts = {
            all,
            pending,
            win,
            nowinnings,
          };
          this.setState({ tableLoading: false, tableData, counts });
        });
    } catch (e) {
      console.log(e);
    }
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

  downloadCSV() {
    const { whitelabel, filters, activeTab } = this.state;
    const FileDownload = require('js-file-download');
    this.setState({ exportLoading: true });
    try {
      axios
        .post('/crm/raffle_tickets/export', {
          whitelabel,
          filters,
          activeTab,
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
            'raffle_tickets_' +
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

  setActiveTab(activeTab) {
    let columns = this.state.columns;
    let index = columns.findIndex((x) => {
      return x.name == 'status';
    });
    if (activeTab == 'all') {
      if (index === -1) columns.splice(3, 0, { name: 'status', filter: '' });
    } else {
      if (index > -1) columns.splice(index, 1);
    }

    this.setState({ activeTab, page: 1, columns, dataLoading: true }, () => {
      this.fetchTableData();
    });
  }

  filterCreatedAt(date) {
    let colName = 'created_at';
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
      activeTab,
      tableLoading,
      tableData,
      tabs,
      counts,
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
    ];

    let useLoginsForUsers = false;
    if (
      this.context.choosedWhitelabel.id == '0' ||
      this.context.choosedWhitelabel.use_logins_for_users == '1'
    ) {
      useLoginsForUsers = true;
    }

    return (
      <div className="module-tickets">
        <div className="row">
          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('Raffle Tickets')}</h4>
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
                                  filterCreatedAt={this.filterCreatedAt.bind(
                                    this,
                                  )}
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
                                      (useLoginsForUsers
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
                                          key={'p_rname' + i}
                                        >
                                          {ticket['rname']}
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
                                      ticket['status_win'] &&
                                      ticket['prize_display'] && (
                                        <DataWithTooltip
                                          key={'prize' + i}
                                          title={gettext('Prize')}
                                          value={ticket['prize_display']}
                                          tooltipValue={ticket['prizes_other']}
                                        />
                                      )
                                    ) : col.name == 'status' ? (
                                      ticket['status_display']
                                    ) : col.name == 'is_paid_out' ? (
                                      ticket['status_win'] && (
                                        <span
                                          className={ticket['payout_class']}
                                        >
                                          {ticket['payout_display']}
                                        </span>
                                      )
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
                                        token={ticket.full_token}
                                      />,
                                    ]}
                                  </td>
                                ))}
                                <td>
                                  <div className="users-tooltip">
                                    <Link
                                      to={{
                                        pathname: `/crm/raffle_tickets/${ticket.full_token}`,
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
                              {
                                <TableFilters
                                  columns={columns}
                                  filterCreatedAt={this.filterCreatedAt.bind(
                                    this,
                                  )}
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

RaffleTickets.contextType = CrmContext;
export default RaffleTickets;
