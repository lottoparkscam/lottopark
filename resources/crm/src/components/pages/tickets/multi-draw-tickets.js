import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Loading from '../../elements/loading';
import TableTabs from '../../elements/table-tabs';
import Pagination from '../../elements/pagination';
import TableLengthSelect from '../../elements/table-length-select';
import { Link } from 'react-router-dom';
import * as utils from '../../../helpers/utils';
import TableFilters from '../../elements/table-filters';

class MultiDrawTickets extends React.Component {
  constructor(props) {
    super(props);

    this.timeout = null;

    this.state = {
      whitelabel: null,
      tableLoading: true,
      exportLoading: false,
      tableData: [],
      tabs: [],
      counts: {
        all: 0,
      },
      page: 1,
      itemsPerPage: 50,
      sortBy: 'token',
      order: 'ASC',
      filters: [],
      columns: [
        { name: 'token', filter: '' },
        { name: 'utoken', filter: '' },
        { name: 'tickets', filter: ['', ''] },
        { name: 'first_draw', filter: '' },
        { name: 'valid_to_draw', filter: '' },
        { name: 'current_draw', filter: '' },
        { name: 'date', filter: '' },
      ],
      columnsNames: null,
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    const { gettext } = this.context.textdomain;
    let columns = this.state.columns;

    let query_params = this.props.location.query;
    if (query_params) {
      let filters, columnsUpdated;
      [filters, columnsUpdated] = utils.prepareFiltersFromQuery(
        columns,
        query_params,
      );
      this.setState({ columns: columnsUpdated, filters });
    }

    let tabs = [{ name: 'all', title: gettext('All'), icon: 'ti-home' }];
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
      token: gettext('Multidraw ID'),
      utoken: userColumnName,
      tickets: gettext('Multidraw Tickets'),
      first_draw: gettext('First draw'),
      valid_to_draw: gettext('Valid to draw'),
      current_draw: gettext('Current draw'),
      date: gettext('Date'),
    };
    const whitelabel = this.context.choosedWhitelabel.id;
    this.setState({ whitelabel, tabs, columnsNames }, () => {
      this.fetchData();
    });
  }

  fetchData() {
    const { whitelabel, filters, page, itemsPerPage, sortBy, order } =
      this.state;
    try {
      axios
        .post('/crm/multidraw_tickets/table_data', {
          whitelabel,
          filters,
          page,
          itemsPerPage,
          sortBy,
          order,
        })
        .then((res) => {
          let tableData = res.data.data;
          let all = res.data.all_count;
          let counts = {
            all,
          };
          this.setState({ tableLoading: false, tableData, counts });
        });
    } catch (e) {
      console.log(e);
    }
  }

  paginate = (page) => {
    this.setState({ page, tableLoading: true }, () => this.fetchData());
  };

  handleChange(e) {
    let itemsPerPage = e.target.value;
    this.setState(
      {
        itemsPerPage,
        page: 1,
        loading: true,
      },
      () => this.fetchData(),
    );
  }

  downloadCSV() {
    const { whitelabel, filters } = this.state;
    const FileDownload = require('js-file-download');
    this.setState({ exportLoading: true });
    try {
      axios
        .post('/crm/multidraw_tickets/export', {
          whitelabel,
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
            'tickets_multidraw' +
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
    let sortBy = this.state.sortBy;
    let order = this.state.order;
    if (sortBy === col) {
      if (order === 'ASC') order = 'DESC';
      else order = 'ASC';
      this.setState(
        {
          order,
        },
        () => this.fetchData(),
      );
    } else {
      order = 'DESC';
      this.setState(
        {
          sortBy: col,
          order,
        },
        () => this.fetchData(),
      );
    }
  }

  filterDate(date) {
    let colName = 'date';
    this.filterDates(date, colName);
  }

  filterFirstDrawDate(date) {
    let colName = 'first_draw';
    this.filterDates(date, colName);
  }

  filterValidToDrawDate(date) {
    let colName = 'valid_to_draw';
    this.filterDates(date, colName);
  }

  filterCurrentDrawDate(date) {
    let colName = 'current_draw';
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
        this.fetchData();
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
        () => this.fetchData(),
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
        this.timeout = setTimeout(() => this.fetchData(), 1000);
      },
    );
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
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
                <h4 className="card-title">{gettext('Multidraw Tickets')}</h4>
                <TableTabs
                  tabs={tabs}
                  counts={counts}
                  setActiveTab={() => {}}
                  activeTab={'all'}
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
                                  filterCurrentDrawDate={this.filterCurrentDrawDate.bind(
                                    this,
                                  )}
                                  filterFirstDrawDate={this.filterFirstDrawDate.bind(
                                    this,
                                  )}
                                  filterValidToDrawDate={this.filterValidToDrawDate.bind(
                                    this,
                                  )}
                                  filterAmounts={this.filterAmounts.bind(this)}
                                  filterList={this.filterList.bind(this)}
                                />
                              }
                              <td></td>
                            </tr>
                            {tableData.map((ticket, i) => (
                              <tr key={i}>
                                {columns.map((col) => (
                                  <td key={'td' + i + col.name}>
                                    {col.name == 'utoken'
                                      ? ticket['user_full_token'] +
                                        ' • ' +
                                        ticket['user_fullname'] +
                                        ' • ' +
                                        ticket['email'] +
                                        (useLoginsForUsers
                                          ? ' • ' + ticket['user_login']
                                          : '')
                                      : col.name == 'token'
                                      ? [
                                          <p
                                            className="m-b-0"
                                            key={'p_token' + i}
                                          >
                                            {ticket['full_token']}
                                          </p>,
                                          <p
                                            className="m-b-0"
                                            key={'p_lname' + i}
                                          >
                                            {ticket['lname']}
                                          </p>,
                                        ]
                                      : ticket[col.name]}
                                  </td>
                                ))}
                                <td>
                                  <div className="users-tooltip">
                                    <Link
                                      to={{
                                        pathname: '/crm/tickets',
                                        query: {
                                          multi_draw_id: ticket['token'],
                                        },
                                      }}
                                    >
                                      <i className="users-edit mdi mdi-view-list" />
                                    </Link>
                                    <span className="users-tooltiptext">
                                      {gettext('View Tickets')}
                                    </span>
                                  </div>
                                </td>
                              </tr>
                            ))}
                            <tr className="table-search">
                              {
                                <TableFilters
                                  columns={columns}
                                  filterDate={this.filterDate.bind(this)}
                                  filterCurrentDrawDate={this.filterCurrentDrawDate.bind(
                                    this,
                                  )}
                                  filterFirstDrawDate={this.filterFirstDrawDate.bind(
                                    this,
                                  )}
                                  filterValidToDrawDate={this.filterValidToDrawDate.bind(
                                    this,
                                  )}
                                  filterAmounts={this.filterAmounts.bind(this)}
                                  filterList={this.filterList.bind(this)}
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
                          {counts['all']} {gettext('entries')}
                        </div>
                      </div>
                      <div className="col-sm-12 col-md-7">
                        <Pagination
                          page={page}
                          itemsPerPage={itemsPerPage}
                          totalItems={counts['all']}
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

MultiDrawTickets.contextType = CrmContext;
export default withRouter(MultiDrawTickets);
