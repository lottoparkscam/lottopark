import React from 'react';
import axios from '../../../helpers/interceptors';
import Pagination from '../../elements/pagination';
import CrmContext from '../../../helpers/context';
import { Link } from 'react-router-dom';
import Chart from '../../elements/chart';
import ChartistTooltip from 'chartist-plugin-tooltips-updated';
import Legend from 'chartist-plugin-legend';
import DateRangePicker from '@wojtekmaj/react-daterange-picker/dist/entry.nostyle';
import CsvExportModal from '../../elements/csvExportModal';
import Swal from 'sweetalert2';
import toastr from 'toastr';
import Loading from '../../elements/loading';
import DateRangeSelect from '../../elements/date-range-select';
import { withRouter } from 'react-router-dom';
import ChangeUserGroupModal from '../../elements/change-user-group-modal';
import * as utils from '../../../helpers/utils';
import DataWithTooltip from '../../elements/data-with-tooltip';

class UsersView extends React.Component {
  constructor(props) {
    super(props);

    this.timeout = null;

    this.state = {
      chartLoading: true,
      dataLoading: true,
      isModalOpen: false,
      date: [new Date(), new Date()],
      languages: [],
      timezones: [],
      groups: [],
      countries: {},
      currencies: {},
      regions: [],
      whitelabel_id: null,
      group_id: null,
      activeTab: 'active',
      activeCount: 0,
      inactiveCount: 0,
      deletedCount: 0,
      usersList: [],
      filters: [],
      page: 1,
      itemsPerPage: 50,
      sortBy: 'name',
      order: 'ASC',
      choosedUsers: [],
      columns: [
        { name: 'full_token', shown: false, filter: '' },
        { name: 'name', shown: false, filter: '' },
        { name: 'surname', shown: false, filter: '' },
        { name: 'birthdate', shown: false, filter: '' },
        { name: 'gender', shown: false, filter: '' },
        { name: 'email', shown: false, filter: '' },
        { name: 'phone', shown: false, filter: '' },
        { name: 'language', shown: false, filter: '' },
        { name: 'timezone', shown: false, filter: '' },
        { name: 'country_name', shown: false, filter: '' },
        { name: 'state', shown: false, filter: '' },
        { name: 'city', shown: false, filter: '' },
        { name: 'address_1', shown: false, filter: '' },
        { name: 'address_2', shown: false, filter: '' },
        { name: 'zip', shown: false, filter: '' },
        { name: 'national_id', shown: false, filter: '' },
        { name: 'user_currency_code', shown: false, filter: '' },
        { name: 'balance', shown: false, filter: ['', ''] },
        { name: 'bonus_balance', shown: false, filter: ['', ''] },
        { name: 'casino_balance', shown: false, filter: ['', ''] },
        { name: 'date_register', shown: false, filter: '' },
        { name: 'register_ip', shown: false, filter: '' },
        { name: 'register_country_name', shown: false, filter: '' },
        { name: 'last_ip', shown: false, filter: '' },
        { name: 'first_deposit', shown: false, filter: '' },
        { name: 'second_deposit', shown: false, filter: '' },
        { name: 'last_deposit_date', shown: false, filter: '' },
        { name: 'last_active', shown: false, filter: '' },
        { name: 'last_country_name', shown: false, filter: '' },
        { name: 'first_purchase', shown: false, filter: '' },
        { name: 'second_purchase', shown: false, filter: '' },
        { name: 'last_purchase_date', shown: false, filter: '' },
        { name: 'purchaseCountForDate', shown: false, filter: '' },
        {
          name: 'first_deposit_amount_manager',
          shown: false,
          filter: ['', ''],
        },
        { name: 'last_deposit_amount_manager', shown: false, filter: ['', ''] },
        { name: 'total_deposit_manager', shown: false, filter: ['', ''] },
        { name: 'total_withdrawal_manager', shown: false, filter: ['', ''] },
        { name: 'total_purchases_manager', shown: false, filter: ['', ''] },
        {
          name: 'last_purchase_amount_manager',
          shown: false,
          filter: ['', ''],
        },
        { name: 'total_net_income_manager', shown: false, filter: ['', ''] },
        { name: 'net_winnings_manager', shown: false, filter: ['', ''] },
        { name: 'sale_status', shown: false, filter: '' },
        { name: 'pnl_manager', shown: false, filter: ['', ''] },
        { name: 'system_type', shown: false, filter: '' },
        { name: 'browser_type', shown: false, filter: '' },
        { name: 'last_update', shown: false, filter: '' },
        { name: 'group', shown: false, filter: '' },
        { name: 'player_lifetime', shown: false, filter: '' },
      ],
      columnsNames: null,
      chartData: null,
      chartOptions: null,
      showDatePicker: false,
      selectDateValue: 'month',
      csvData: [],
      showChangeGroupSelect: false,
      selectedGroups: [],
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    let whitelabel = this.context.choosedWhitelabel;
    let whitelabel_id = whitelabel.id;
    let columns = this.state.columns;

    let query_params = this.props.location.query;
    if (query_params) {
      if (query_params.group_id) {
        let group_id = query_params.group_id;
        this.setState({ group_id });
      } else {
        let filters = [];
        Object.keys(query_params).forEach((filter) => {
          let index = columns.findIndex((x) => x.name == filter);
          let filter_obj = { column: filter, value: query_params[filter] };
          columns[index].filter = query_params[filter];
          columns[index].shown = true;
          filters.push(filter_obj);
        });
        this.setState({ columns, filters });
      }
    }
    this.setState({ whitelabel_id }, () => this.fetchUsersList());

    if (whitelabel_id == 0) {
      columns.push({ name: 'whitelabel_name', index: 11, shown: false });

      this.setState({
        columns,
      });
    }
    if (parseInt(whitelabel.use_logins_for_users) === 1 || whitelabel_id == 0) {
      columns.splice(6, 0, { name: 'login', shown: false, filter: '' });
    }
    try {
      axios
        .post('/users_view_data', {
          whitelabel_id,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let languages = Object.values(res.data.languages);
            let timezones = res.data.timezones;
            let countries = res.data.countries;
            let currencies = res.data.currencies;
            let groups = res.data.groups;
            let columns = this.state.columns;
            if (res.data.columns) {
              let visibleColumns = res.data.columns.split(',');
              visibleColumns.forEach((column) => {
                let col = columns.find((x) => x.name === column);
                col && (col.shown = true);
              });
            }

            this.setState({
              languages,
              timezones,
              countries,
              currencies,
              columns,
              groups,
            });
          }
        });
    } catch (e) {
      console.log(e);
    }
    const { gettext } = this.context.textdomain;
    let columnsNames = {
      email: gettext('Email'),
      name: gettext('Name'),
      surname: gettext('Surname'),
      login: gettext('Login'),
      birthdate: gettext('Birthdate'),
      gender: gettext('Gender'),
      full_token: gettext('Token'),
      language: gettext('Language'),
      balance: gettext('Balance'),
      bonus_balance: gettext('Bonus balance'),
      casino_balance: gettext('Casino balance'),
      user_currency_code: gettext('Currency'),
      register_ip: gettext('Register IP'),
      register_country_name: gettext('Register country'),
      last_ip: gettext('Last IP'),
      phone: gettext('Phone'),
      timezone: gettext('Timezone'),
      state: gettext('Region'),
      city: gettext('City'),
      address_1: gettext('Address #1'),
      address_2: gettext('Address #2'),
      zip: gettext('Postal/ZIP Code'),
      country_name: gettext('Country'),
      national_id: gettext('National ID'),
      date_register: gettext('Register date'),
      first_deposit: gettext('First deposit'),
      second_deposit: gettext('Second deposit'),
      last_deposit_date: gettext('Last deposit'),
      last_active: gettext('Last active'),
      last_country_name: gettext('Last country'),
      first_purchase: gettext('First purchase'),
      second_purchase: gettext('Second purchase'),
      last_purchase_date: gettext('Last purchase'),
      purchaseCountForDate: gettext('Purchase count for date'),
      first_deposit_amount_manager: gettext('First deposit amount'),
      last_deposit_amount_manager: gettext('Last deposit amount'),
      total_deposit_manager: gettext('Total deposit'),
      total_withdrawal_manager: gettext('Total withdrawal'),
      total_purchases_manager: gettext('Total purchases'),
      last_purchase_amount_manager: gettext('Last purchase amount'),
      total_net_income_manager: gettext('Total net income'),
      net_winnings_manager: gettext('Net winnings'),
      sale_status: gettext('Sale status'),
      pnl_manager: gettext('PnL'),
      system_type: gettext('System'),
      browser_type: gettext('Browser'),
      whitelabel_name: gettext('Whitelabel'),
      last_update: gettext('Last update'),
      date_delete: gettext('Delete date'),
      group: gettext('Prize payout group'),
      player_lifetime: gettext('Player Lifetime (days)'),
    };
    this.setState({ columnsNames });
    this.setDays();
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

  prepareChart(registered, deposit, startDate, endDate) {
    let locale = this.context.user.code.replace('_', '-');
    const { gettext } = this.context.textdomain;
    let max = 0;

    let chartData = {};
    let x = [];
    let y1 = [];
    let y2 = [];

    var numeric = { day: 'numeric', month: 'numeric' };
    let days = this.getDaysArray(endDate, startDate);
    for (var i = 0; i < days.length; i++) {
      x.push(days[i].toLocaleDateString(locale, numeric));
      y1.push(0);
      y2.push(0);
    }

    let interpolation = Math.floor(x.length / 5);

    registered.forEach((data) => {
      let priorDate = new Date(data.date_register).toLocaleDateString(
        locale,
        numeric,
      );
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y1[index]++;
        if (y1[index] > max) max = y1[index];
      }
    });

    deposit.forEach((data) => {
      let priorDate = new Date(data.first_deposit).toLocaleDateString(
        locale,
        numeric,
      );
      let index = x.indexOf(priorDate);
      if (index !== -1) {
        y2[index]++;
        if (y2[index] > max) max = y2[index];
      }
    });
    y1 = y1.map(function (v, idx) {
      return { meta: 'Date: ' + x[idx], value: v };
    });
    y2 = y2.map(function (v, idx) {
      return { meta: 'Date: ' + x[idx], value: v };
    });

    chartData['labels'] = x;
    chartData['series'] = [y2, y1];
    chartData['max'] = max;

    let chartOptions = {
      low: 0,
      high: max,
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

    this.setState({
      chartData,
      chartOptions,
      chartLoading: false,
    });
  }

  setDays() {
    this.setState({ chartLoading: true });
    let startDate;
    let endDate;
    let option = this.state.selectDateValue;
    let whitelabel_id = this.context.choosedWhitelabel.id;

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

    let end_date =
      endDate.getFullYear() +
      '-' +
      (parseInt(endDate.getMonth()) + 1) +
      '-' +
      endDate.getDate();
    let start_date =
      startDate.getFullYear() +
      '-' +
      (parseInt(startDate.getMonth()) + 1) +
      '-' +
      startDate.getDate();
    try {
      axios
        .post('/usersstats', {
          start_date,
          end_date,
          whitelabel_id,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let registered = res.data.registered;
            let deposit = res.data.deposit;

            this.prepareChart(registered, deposit, startDate, endDate);
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

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

  fetchUsersList() {
    const {
      whitelabel_id,
      group_id,
      activeTab,
      filters,
      page,
      itemsPerPage,
      sortBy,
      order,
    } = this.state;
    try {
      axios
        .post('/userslist', {
          whitelabel_id,
          group_id,
          activeTab,
          filters,
          page,
          itemsPerPage,
          sortBy,
          order,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let usersList = res.data.users;
            let activeCount = res.data.activeCount;
            let inactiveCount = res.data.inactiveCount;
            let deletedCount = res.data.deletedCount;

            this.setState({
              usersList,
              activeCount,
              inactiveCount,
              deletedCount,
              dataLoading: false,
            });
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  deleteUser(token) {
    const { gettext } = this.context.textdomain;

    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Delete'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        axios
          .post('/user/delete', {
            token,
          })
          .then((res) => {
            if (res.data.code == 200) {
              this.fetchUsersList();
              let message = res.data.message;
              this.showToast(message);
            }
          });
      }
    });
  }

  restoreUser(token) {
    const { gettext } = this.context.textdomain;

    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Restore'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        axios
          .post('/user/restore', {
            token,
          })
          .then((res) => {
            if (res.data.code == 200) {
              this.fetchUsersList();
              let message = res.data.message;
              this.showToast(message);
            }
          });
      }
    });
  }

  activateUser(token) {
    const { gettext } = this.context.textdomain;

    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Activate'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        axios
          .post('/user/activate', {
            token,
          })
          .then((res) => {
            if (res.data.code == 200) {
              this.fetchUsersList();
              let message = res.data.message;
              this.showToast(message);
            }
          });
      }
    });
  }

  confirmUser(token) {
    const { gettext } = this.context.textdomain;

    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Confirm'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        axios
          .post('/user/confirm', {
            token,
          })
          .then((res) => {
            if (res.data.code == 200) {
              let usersList = this.state.usersList;
              usersList.find((x) => x.token == token).is_confirmed = 1;
              this.setState(usersList);
              let message = res.data.message;
              this.showToast(message);
            }
          });
      }
    });
  }

  showToast(message) {
    toastr.options = {
      positionClass: 'toast-bottom-left',
      hideDuration: 300,
      timeOut: 10000,
    };
    toastr.clear();
    setTimeout(() => toastr.success(message), 300);
  }

  onHandleChange(e) {
    let itemsPerPage = e.target.value;
    this.setState(
      {
        itemsPerPage,
        page: 1,
        dataLoading: true,
      },
      () => this.fetchUsersList(),
    );
  }

  paginate = (page) => {
    this.setState({ page, dataLoading: true }, () => this.fetchUsersList());
  };

  setActiveTab(activeTab) {
    let columns = this.state.columns;
    let index = columns.findIndex((x) => {
      return x.name == 'date_delete';
    });
    if (activeTab == 'deleted') {
      if (index === -1) columns.push({ name: 'date_delete', shown: false });
    } else {
      if (index > -1) columns.splice(index, 1);
    }

    this.setState({ activeTab, page: 1, columns, dataLoading: true }, () => {
      this.fetchUsersList();
    });
  }

  sortBy(col) {
    if (col === 'country_name' || col === 'language') {
      return null;
    }
    col = this.getOriginalColumnName(col);
    let sortBy = this.state.sortBy;
    let order = this.state.order;
    if (sortBy === col) {
      if (order === 'ASC') order = 'DESC';
      else order = 'ASC';
      this.setState(
        {
          order,
        },
        () => this.fetchUsersList(),
      );
    } else {
      order = 'DESC';
      this.setState(
        {
          sortBy: col,
          order,
        },
        () => this.fetchUsersList(),
      );
    }
  }

  getOriginalColumnName(name) {
    switch (name) {
      case 'register_country_name':
        return 'register_country';
      case 'full_token':
        return 'token';
      default:
        return name;
    }
  }

  toggleHiddenCols(e) {
    let name = e.target.name;
    let isChecked = e.target.checked;
    let columns = this.state.columns;
    let index = columns.findIndex((x) => {
      return x.name == name;
    });
    columns[index].shown = isChecked;
    this.setState({
      columns,
    });
    let visibleCols = [];
    columns.forEach((column) => {
      if (column.shown) {
        visibleCols.push(column.name);
      }
    });
    let slug = visibleCols.join(',');
    try {
      axios
        .post('/update_visible_columns', {
          slug,
        })
        .then((res) => {
          if (res.data.code == 200) {
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  chooseAllUsers(e) {
    let choosedUsers = this.state.choosedUsers;
    let usersList = this.state.usersList;
    let isChecked = e.target.checked;
    if (isChecked) {
      usersList.forEach((user) => {
        choosedUsers.push(user.full_token);
      });
    } else {
      usersList.forEach((user) => {
        choosedUsers.splice(choosedUsers.indexOf(user.full_token), 1);
      });
    }
    this.setState({
      choosedUsers,
    });
  }

  toggleChooseUser(id) {
    let choosedUsers = this.state.choosedUsers;
    if (choosedUsers.indexOf(id) == -1) {
      choosedUsers.push(id);
    } else {
      choosedUsers.splice(choosedUsers.indexOf(id), 1);
    }
    this.setState({
      choosedUsers,
    });
  }

  fetchRegions(country) {
    try {
      axios.post('/user/regions', { country }).then((res) => {
        let regions = res.data.regions;
        this.setState({ regions });
      });
    } catch (e) {
      console.log(e);
    }
  }

  filterList(e) {
    if (this.timeout) clearTimeout(this.timeout);
    var name = e.target.name;
    let value = e.target.value;

    let columns = this.state.columns;
    let index = columns.findIndex((x) => {
      switch (name) {
        case 'language_id':
          return x.name == 'language';
        case 'whitelabel_id':
          return x.name == 'whitelabel_name';
        case 'country':
          return x.name == 'country_name';
        case 'last_country':
          return x.name == 'last_country_name';
        case 'register_country':
          return x.name == 'register_country_name';
        case 'currency_id':
          return x.name == 'user_currency_code';
        default:
          return x.name == name;
      }
    });
    columns[index].filter = value;
    if (name == 'country') {
      value == '' && (columns.find((x) => x.name == 'state').filter = '');
    }
    this.setState({ columns });
    if (name == 'full_token') {
      name = 'token';
    } else if (name == 'country') {
      if (value !== '') this.fetchRegions(value);
      else {
        if (this.state.regions.length > 0) this.setState({ regions: [] });
        let filters = this.state.filters;
        let index = filters.findIndex((x) => {
          return x.column == 'state';
        });
        if (index !== -1) {
          filters.splice(index, 1);
        }
      }
    }

    this.timeout = setTimeout(() => {
      if (name == 'whitelabel_id') {
        this.setState(
          {
            whitelabel_id: parseInt(value),
          },
          () => {
            this.fetchUsersList();
          },
        );
      } else {
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
          () => this.fetchUsersList(),
        );
      }
    }, 1000);
  }

  filterBirthdate(date) {
    let colName = 'birthdate';
    this.filterDates(date, colName);
  }

  filterDateRegister(date) {
    let colName = 'date_register';
    this.filterDates(date, colName);
  }

  filterFirstDeposit(date) {
    let colName = 'first_deposit';
    this.filterDates(date, colName);
  }

  filterSecondDeposit(date) {
    let colName = 'second_deposit';
    this.filterDates(date, colName);
  }

  filterLastDepositDate(date) {
    let colName = 'last_deposit_date';
    this.filterDates(date, colName);
  }

  filterLastActive(date) {
    let colName = 'last_active';
    this.filterDates(date, colName);
  }

  filterFirstPurchase(date) {
    let colName = 'first_purchase';
    this.filterDates(date, colName);
  }

  filterSecondPurchase(date) {
    let colName = 'second_purchase';
    this.filterDates(date, colName);
  }

  filterLastUpdate(date) {
    let colName = 'last_update';
    this.filterDates(date, colName);
  }

  filterLastPurchaseDate(date) {
    let colName = 'last_purchase_date';
    this.filterDates(date, colName);
  }

  filterPurchaseDate(date) {
    const colName = 'purchaseCountForDate';
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
        this.fetchUsersList();
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
      this.timeout = setTimeout(() => this.fetchUsersList(), 1000);
    });
  }

  onChange = (date) => {
    if (date) {
      this.setState(
        { date, selectDateValue: 'range', chartLoading: true },
        () => this.setDays(),
      );
    } else {
      this.setState({ showDatePicker: false, selectDateValue: 'month' }, () =>
        this.setDays(),
      );
    }
  };

  downloadCSV(columns) {
    const { whitelabel_id, activeTab, filters, group_id } = this.state;
    const FileDownload = require('js-file-download');
    try {
      axios
        .post('/whitelabel/users/export', {
          whitelabel_id,
          activeTab,
          filters,
          columns,
          group_id,
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
            'users_' +
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
        });
    } catch (e) {
      console.log(e);
    }
    this.toggleModal();
  }

  toggleModal = () => {
    let isModalOpen = !this.state.isModalOpen;
    this.setState({ isModalOpen });
  };

  filters() {
    const { gettext } = this.context.textdomain;
    const {
      languages,
      timezones,
      countries,
      currencies,
      regions,
      filters,
      groups,
      columns,
    } = this.state;

    let langsOptions;
    if (languages.length > 0) {
      langsOptions = languages.map(({ id, name }, index) => (
        <option key={index} value={id}>
          {name}
        </option>
      ));
    }
    let whitelabelsOptions;
    let whitelabels = this.context.whitelabels;
    if (whitelabels.length > 0) {
      whitelabelsOptions = whitelabels.map(
        ({ id, name }, index) =>
          id != 0 && (
            <option key={index} value={id}>
              {name}
            </option>
          ),
      );
    }
    let countriesOptions;
    if (Object.keys(countries).length > 0) {
      countriesOptions = Object.keys(countries).map((code, index) => (
        <option key={index} value={code}>
          {countries[code]}
        </option>
      ));
    }
    let currenciesOptions;
    if (Object.keys(currencies).length > 0) {
      currenciesOptions = Object.keys(currencies).map((currency, index) => (
        <option key={index} value={currencies[currency].id}>
          {currencies[currency].code}
        </option>
      ));
    }
    let regionsOptions;
    if (Object.keys(regions).length > 0) {
      regionsOptions = Object.keys(regions).map((region, index) => (
        <option key={index} value={region}>
          {regions[region][2] + ' - ' + regions[region][1]}
        </option>
      ));
    }
    let timezonesOptions;
    if (Object.keys(timezones).length > 0) {
      timezonesOptions = Object.keys(timezones).map((timezone, index) => (
        <option key={index} value={timezone}>
          {timezones[timezone]}
        </option>
      ));
    }
    let groupsOptions;
    if (groups.length > 0) {
      groupsOptions = groups.map((group, index) => (
        <option key={index} value={group.id}>
          {group.name}
        </option>
      ));
    }
    let amounts = [
      'balance',
      'bonus_balance',
      'casino_balance',
      'first_deposit_amount_manager',
      'last_deposit_amount_manager',
      'total_deposit_manager',
      'total_withdrawal_manager',
      'total_purchases_manager',
      'total_net_income_manager',
      'last_purchase_amount_manager',
      'net_winnings_manager',
      'pnl_manager',
    ];

    return columns.map(
      (col) =>
        col.shown && (
          <td key={'search1' + col.name}>
            {col.name == 'birthdate' ? (
              <DateRangePicker
                onChange={this.filterBirthdate.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'date_register' ? (
              <DateRangePicker
                onChange={this.filterDateRegister.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'first_deposit' ? (
              <DateRangePicker
                onChange={this.filterFirstDeposit.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'second_deposit' ? (
              <DateRangePicker
                onChange={this.filterSecondDeposit.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'last_deposit_date' ? (
              <DateRangePicker
                onChange={this.filterLastDepositDate.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'last_active' ? (
              <DateRangePicker
                onChange={this.filterLastActive.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'first_purchase' ? (
              <DateRangePicker
                onChange={this.filterFirstPurchase.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'second_purchase' ? (
              <DateRangePicker
                onChange={this.filterSecondPurchase.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'last_update' ? (
              <DateRangePicker
                onChange={this.filterLastUpdate.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'last_purchase_date' ? (
              <DateRangePicker
                onChange={this.filterLastPurchaseDate.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'purchaseCountForDate' ? (
              <DateRangePicker
                onChange={this.filterPurchaseDate.bind(this)}
                value={col.filter}
                calendarIcon={null}
              />
            ) : col.name == 'language' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="language_id"
                value={col.filter}
              >
                <option value="">--</option>
                {langsOptions}
              </select>
            ) : col.name == 'timezone' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="timezone"
                value={col.filter}
              >
                <option value="">--</option>
                {timezonesOptions}
              </select>
            ) : col.name == 'whitelabel_name' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="whitelabel_id"
                value={col.filter}
              >
                <option value="0">--</option>
                {whitelabelsOptions}
              </select>
            ) : col.name == 'country_name' ||
              col.name == 'last_country_name' ||
              col.name == 'register_country_name' ? (
              <select
                onChange={this.filterList.bind(this)}
                name={
                  col.name == 'country_name'
                    ? 'country'
                    : col.name == 'last_country_name'
                    ? 'last_country'
                    : col.name == 'register_country_name'
                    ? 'register_country'
                    : ''
                }
                value={col.filter}
              >
                <option value="">--</option>
                {countriesOptions}
              </select>
            ) : col.name == 'user_currency_code' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="currency_id"
                value={col.filter}
              >
                <option value="">--</option>
                {currenciesOptions}
              </select>
            ) : col.name == 'group' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="group"
                value={col.filter}
              >
                <option value="">--</option>
                {groupsOptions}
              </select>
            ) : col.name == 'state' ? (
              <select
                disabled={!filters.find((x) => x.column == 'country')}
                onChange={this.filterList.bind(this)}
                name="state"
                value={col.filter}
              >
                <option value="">--</option>
                {regionsOptions}
              </select>
            ) : col.name == 'gender' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="gender"
                value={col.filter}
              >
                <option value="">--</option>
                <option value="1">{gettext('Male')}</option>
                <option value="2">{gettext('Female')}</option>
              </select>
            ) : col.name == 'sale_status' ? (
              <select
                onChange={this.filterList.bind(this)}
                name="sale_status"
                value={col.filter}
              >
                <option value="">--</option>
                <option value="1">{gettext('Started deposit')}</option>
                <option value="2">{gettext('Deposited')}</option>
                <option value="3">{gettext('Started purchase')}</option>
                <option value="4">{gettext('Purchased')}</option>
              </select>
            ) : amounts.indexOf(col.name) > -1 ? (
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
            ) : col.name == 'player_lifetime' ? (
              <span></span>
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

  hasModuleAccess(name) {
    if (this.context.user.role_id == '1') return true; // superadmin belongs to whitelabel of ID = 0/NULL
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

  showV2options(userId) {
    if (this.context.user.role_id == '1') return true; // superadmin
    if (this.context.user.role_id == '2') return true; // administrator
    let user = this.state.usersList.find((x) => x.id == userId);
    let whitelabel = this.context.whitelabels.find(
      (x) => user.whitelabel_id == x.id,
    );
    let isUserOnWhitelabelV2 = whitelabel.type == 2;
    if (isUserOnWhitelabelV2) {
      return true;
    }
    return false;
  }

  toggleChangeGroup() {
    const { showChangeGroupSelect } = this.state;
    this.setState({ showChangeGroupSelect: !showChangeGroupSelect });
  }

  setSelectedGroups(selectedGroups) {
    if (selectedGroups.indexOf(0) > -1) {
      selectedGroups = [0];
    }
    this.setState({ selectedGroups });
  }

  changeGroups() {
    const { gettext } = this.context.textdomain;
    const { whitelabel_id, choosedUsers } = this.state;
    let selectedGroups = this.state.selectedGroups;
    if (selectedGroups.length > 0) {
      if (selectedGroups.indexOf(0) > -1) {
        selectedGroups = [];
      }
      try {
        axios
          .post('/whitelabel/users/update_groups', {
            whitelabel_id,
            selectedGroups,
            choosedUsers,
          })
          .then((res) => {
            let message = res.data.message;
            if (res.data.code == 200) {
              this.context.showToast('success', message);
              this.fetchUsersList();
            } else if (res.data.code == 400) {
              this.context.showToast('error', message);
            }
          });
        this.toggleChangeGroup();
      } catch (e) {
        console.log(e);
      }
      this.setState({ selectedGroups: [] });
    } else {
      let message = gettext("You didn't choose groups.");
      this.context.showToast('error', message);
    }
  }

  decorateBalanceColumns(user, column) {
    const { gettext } = this.context.textdomain;
    const { timezones, activeTab } = this.state;
    let balanceColumns = [];
    let hideManageButtons =
      activeTab !== 'active' || !this.showV2options(user.id);

    switch (column.name) {
      case 'timezone':
        balanceColumns = timezones[user[column.name]];
        break;

      case 'balance':
        balanceColumns.push(
          <DataWithTooltip
            key={'balance'}
            value={user.balance_display}
            tooltipValue={user.balance_additional_text}
          />,
        );

        if (hideManageButtons) break;

        balanceColumns.push(
          <div key="manage-buttons-balance">
            {this.hasModuleAccess('users-balance-edit') && (
              <div className="users-tooltip">
                <Link
                  to={{
                    pathname: `/whitelabel/users/edit/${user.full_token}/balance/edit`,
                  }}
                >
                  <i className="users-edit mdi mdi-pencil m-l-5" />
                </Link>
                <span className="users-tooltiptext">
                  {gettext('Edit balance')}
                </span>
              </div>
            )}
            {this.hasModuleAccess('users-manual-deposit-add') && (
              <div className="users-tooltip">
                <Link
                  to={{
                    pathname: `/whitelabel/users/edit/${user.full_token}/manual_deposit/add`,
                  }}
                >
                  <i className="users-edit mdi mdi-briefcase-upload m-l-5" />
                </Link>
                <span className="users-tooltiptext">
                  {gettext('Add manual deposit')}
                </span>
              </div>
            )}
          </div>,
        );

        break;

      case 'bonus_balance':
        balanceColumns.push(
          <DataWithTooltip
            key={'bonus_balance'}
            value={user.bonus_balance_display}
            tooltipValue={user.bonus_balance_additional_text}
          />,
        );

        if (hideManageButtons) break;

        balanceColumns.push(
          <div key="manage-buttons-bonus-balance">
            {this.hasModuleAccess('users-bonus-balance-edit') && (
              <div className="users-tooltip">
                <Link
                  to={{
                    pathname: `/whitelabel/users/edit/${user.full_token}/balance/is_bonus/edit`,
                  }}
                >
                  <i className="users-edit mdi mdi-pencil m-l-5" />
                </Link>
                <span className="users-tooltiptext">
                  {gettext('Edit bonus balance')}
                </span>
              </div>
            )}
            {this.hasModuleAccess('users-bonus-balance-manual-deposit-add') && (
              <div className="users-tooltip">
                <Link
                  to={{
                    pathname: `/whitelabel/users/edit/${user.full_token}/manual_deposit/is_bonus/add`,
                  }}
                >
                  <i className="users-edit mdi mdi-briefcase-upload m-l-5" />
                </Link>
                <span className="users-tooltiptext">
                  {gettext('Add manual deposit')}
                </span>
              </div>
            )}
          </div>,
        );

        break;

      case 'casino_balance':
        balanceColumns.push(
          <DataWithTooltip
            key={'casino_balance'}
            value={user.casino_balance_display}
            tooltipValue={user.casino_balance_additional_text}
          />,
        );

        if (hideManageButtons) break;

        balanceColumns.push(
          <div key="manage-buttons-casino-balance">
            {this.hasModuleAccess('users-balance-casino-edit') && (
              <div className="users-tooltip">
                <Link
                  to={{
                    pathname: `/whitelabel/users/edit/${user.full_token}/balance/is_casino/edit`,
                  }}
                >
                  <i className="users-edit mdi mdi-pencil m-l-5" />
                </Link>
                <span className="users-tooltiptext">
                  {gettext('Edit casino balance')}
                </span>
              </div>
            )}
            {this.hasModuleAccess('users-manual-deposit-casino-add') && (
              <div className="users-tooltip">
                <Link
                  to={{
                    pathname: `/whitelabel/users/edit/${user.full_token}/manual_deposit/is_casino/add`,
                  }}
                >
                  <i className="users-edit mdi mdi-briefcase-upload m-l-5" />
                </Link>
                <span className="users-tooltiptext">
                  {gettext('Add manual deposit')}
                </span>
              </div>
            )}
          </div>,
        );

        break;

      default:
        balanceColumns = user[column.name];
    }

    return balanceColumns;
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      whitelabel_id,
      chartLoading,
      dataLoading,
      isModalOpen,
      activeTab,
      activeCount,
      inactiveCount,
      deletedCount,
      usersList,
      itemsPerPage,
      page,
      sortBy,
      order,
      choosedUsers,
      columns,
      columnsNames,
      chartData,
      chartOptions,
      showDatePicker,
      selectDateValue,
      date,
      groups,
      showChangeGroupSelect,
      selectedGroups,
    } = this.state;

    let groupsSelectOptions = [];
    groups.forEach((group) => {
      groupsSelectOptions.push({
        name: group.name,
        value: group.id,
      });
    });

    return (
      <div className="row">
        <div className="col-12">
          <div className="card">
            <div className="card-body">
              <h4 className="card-title">
                {gettext('Registrations and deposits')}
              </h4>
              <div className="d-flex align-items-center">
                <DateRangeSelect
                  date={date}
                  showDatePicker={showDatePicker}
                  onDateChange={this.onChange}
                  selectDateValue={selectDateValue}
                  selectedRange={this.selectedRange.bind(this)}
                />
              </div>
              <div className="users-chart ct-charts m-t-30">
                {chartLoading ? (
                  <Loading />
                ) : (
                  <Chart
                    labels={chartData.labels}
                    series={chartData.series}
                    options={chartOptions}
                    element={'chart'}
                  />
                )}
              </div>
            </div>
          </div>
        </div>
        <div className="col-12">
          <div className="card">
            <div className="card-body">
              <h4 className="card-title">{gettext('Users List')}</h4>
              <ul className="nav nav-tabs" role="tablist">
                <li className="nav-item">
                  <a
                    className={
                      'cursor-pointer nav-link ' +
                      (activeTab == 'active' && 'active')
                    }
                    role="tab"
                    onClick={() => {
                      this.setActiveTab('active');
                    }}
                  >
                    <span className="hidden-sm-up">
                      <i className="ti-home"></i>
                    </span>
                    <span className="hidden-xs-down">
                      {' ' + gettext('Active') + ' (' + activeCount + ')'}
                    </span>
                  </a>
                </li>
                <li className="nav-item">
                  <a
                    className={
                      'cursor-pointer nav-link ' +
                      (activeTab == 'inactive' && 'active')
                    }
                    role="tab"
                    onClick={() => {
                      this.setActiveTab('inactive');
                    }}
                  >
                    <span className="hidden-sm-up">
                      <i className="ti-user"></i>
                    </span>
                    <span className="hidden-xs-down">
                      {' ' + gettext('Inactive') + ' (' + inactiveCount + ')'}
                    </span>
                  </a>
                </li>
                <li className="nav-item">
                  <a
                    className={
                      'cursor-pointer nav-link ' +
                      (activeTab == 'deleted' && 'active')
                    }
                    role="tab"
                    onClick={() => {
                      this.setActiveTab('deleted');
                    }}
                  >
                    <span className="hidden-sm-up">
                      <i className="ti-email"></i>
                    </span>
                    <span className="hidden-xs-down">
                      {' ' + gettext('Deleted') + ' (' + deletedCount + ')'}
                    </span>
                  </a>
                </li>
              </ul>
              <div className="table-responsive m-b-15">
                <div className="container-fluid">
                  <div className="row m-t-25 m-b-15">
                    <div className="col">
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
                  </div>
                  <div className="row">
                    <div className="col columns-hidden">
                      {columnsNames &&
                        columns.map((col) => (
                          <label key={'label' + col.name}>
                            <input
                              type="checkbox"
                              name={col.name}
                              checked={col.shown}
                              onChange={this.toggleHiddenCols.bind(this)}
                            />
                            {' ' + columnsNames[col.name]}
                          </label>
                        ))}
                    </div>
                  </div>
                  <div className="row">
                    <div className="ml-auto">
                      <button
                        type="button"
                        className="btn btn-outline-info m-b-10 m-r-10"
                        onClick={this.toggleModal.bind(this)}
                      >
                        <i className="mdi mdi-download m-r-10"></i>
                        {gettext('Export to CSV')}
                      </button>
                      {isModalOpen && (
                        <CsvExportModal
                          visible={isModalOpen}
                          downloadCSV={this.downloadCSV.bind(this)}
                          close={this.toggleModal.bind(this)}
                          columns={columns}
                          columnsNames={columnsNames}
                        />
                      )}
                    </div>
                  </div>
                  <div className="row users-table">
                    <div className="col">
                      {dataLoading ? (
                        <Loading />
                      ) : (
                        <table className="table table-striped table-bordered datatable-select-inputs">
                          <thead>
                            <tr>
                              <th></th>
                              {columnsNames &&
                                columns.map(
                                  (col) =>
                                    col.shown && (
                                      <th
                                        key={'th' + col.name}
                                        onClick={() => this.sortBy(col.name)}
                                        className={
                                          col.name == 'country_name' ||
                                          col.name == 'language'
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
                                    ),
                                )}
                              <th>{gettext('Manage')}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr className="table-search">
                              <td>
                                <input
                                  type="checkbox"
                                  checked={
                                    choosedUsers.length == usersList.length
                                  }
                                  onChange={this.chooseAllUsers.bind(this)}
                                />
                              </td>
                              {this.filters()}
                              <td></td>
                            </tr>
                            {usersList.map((user, index) => (
                              <tr key={index}>
                                <td>
                                  <input
                                    type="checkbox"
                                    name={user.full_token}
                                    checked={
                                      choosedUsers.indexOf(user.full_token) !==
                                      -1
                                    }
                                    onChange={() =>
                                      this.toggleChooseUser(user.full_token)
                                    }
                                  />
                                </td>
                                {columns.map(
                                  (col) =>
                                    col.shown && (
                                      <td key={'td' + index + col.name}>
                                        {col.name == 'email' &&
                                          (user.is_confirmed == 1 ? (
                                            <div className="users-tooltip">
                                              <i className="mdi mdi-check"></i>
                                              <span className="users-tooltiptext">
                                                {gettext('E-mail confirmed')}
                                              </span>
                                            </div>
                                          ) : (
                                            <div className="users-tooltip">
                                              <i className="mdi mdi-close"></i>
                                              <span className="users-tooltiptext">
                                                {gettext(
                                                  'E-mail not confirmed',
                                                )}
                                              </span>
                                            </div>
                                          ))}
                                        {this.decorateBalanceColumns(user, col)}
                                        {col.name == 'email' &&
                                          activeTab == 'active' &&
                                          user.is_confirmed == 0 && (
                                            <div className="users-tooltip">
                                              <a
                                                onClick={() => {
                                                  this.confirmUser(
                                                    user.full_token,
                                                  );
                                                }}
                                              >
                                                <i className="link users-edit mdi mdi-account-check" />
                                              </a>
                                              <span className="users-tooltiptext">
                                                {gettext('Confirm E-mail')}
                                              </span>
                                            </div>
                                          )}
                                      </td>
                                    ),
                                )}
                                <td className="text-center">
                                  {this.hasModuleAccess(
                                    'users-edit-account-personal-data',
                                  ) && (
                                    <span className="users-edit-buttons">
                                      {activeTab == 'active' && (
                                        <div className="users-tooltip">
                                          <Link
                                            to={{
                                              pathname: `/whitelabel/users/edit/${user.full_token}`,
                                            }}
                                          >
                                            <i className="users-edit mdi mdi-pencil" />
                                          </Link>
                                          <span className="users-tooltiptext">
                                            {gettext('Edit user')}
                                          </span>
                                        </div>
                                      )}
                                      {activeTab == 'deleted' && (
                                        <div className="users-tooltip">
                                          <a
                                            onClick={() => {
                                              this.restoreUser(user.full_token);
                                            }}
                                          >
                                            <i className="link users-edit mdi mdi-restore" />
                                          </a>
                                          <span className="users-tooltiptext">
                                            {gettext('Restore user')}
                                          </span>
                                        </div>
                                      )}
                                    </span>
                                  )}
                                  {activeTab == 'active' && (
                                    <div className="users-tooltip">
                                      <Link
                                        to={{
                                          pathname: `/whitelabel/users/view/${user.full_token}`,
                                        }}
                                      >
                                        <i className="users-edit mdi mdi-view-list" />
                                      </Link>

                                      <span className="users-tooltiptext">
                                        {gettext('Details')}
                                      </span>
                                    </div>
                                  )}
                                  {activeTab == 'inactive' && (
                                    <div className="users-tooltip">
                                      <a
                                        onClick={() => {
                                          this.activateUser(user.full_token);
                                        }}
                                      >
                                        <i className="link users-edit mdi mdi-checkbox-marked" />
                                      </a>

                                      <span className="users-tooltiptext">
                                        {gettext('Activate user')}
                                      </span>
                                    </div>
                                  )}
                                  {(activeTab == 'active' ||
                                    activeTab == 'inactive') &&
                                    this.hasModuleAccess('users-delete') && (
                                      <div className="users-tooltip">
                                        <a
                                          className="cursor-pointer"
                                          onClick={() => {
                                            this.deleteUser(user.full_token);
                                          }}
                                        >
                                          <i className="users-delete mdi mdi-delete" />
                                        </a>

                                        <span className="users-tooltiptext">
                                          {gettext('Delete user')}
                                        </span>
                                      </div>
                                    )}
                                </td>
                              </tr>
                            ))}
                            <tr className="table-search">
                              <td>
                                <input
                                  type="checkbox"
                                  checked={
                                    choosedUsers.length == usersList.length
                                  }
                                  onChange={this.chooseAllUsers.bind(this)}
                                />
                              </td>
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
                      <div
                        className="dataTables_info"
                        role="status"
                        aria-live="polite"
                      >
                        {gettext('Showing') + ' '}
                        {itemsPerPage * (page - 1) + 1} {gettext('to') + ' '}
                        {itemsPerPage * (page - 1) + usersList.length}{' '}
                        {gettext('of') + ' '}
                        {activeTab == 'active'
                          ? activeCount
                          : activeTab == 'inactive'
                          ? inactiveCount
                          : activeTab == 'deleted' && deletedCount}{' '}
                        {gettext('entries')}
                      </div>
                    </div>
                    <div className="col-sm-12 col-md-7">
                      <Pagination
                        page={page}
                        itemsPerPage={itemsPerPage}
                        totalItems={
                          activeTab == 'active'
                            ? activeCount
                            : activeTab == 'inactive'
                            ? inactiveCount
                            : activeTab == 'deleted' && deletedCount
                        }
                        paginate={this.paginate.bind(this)}
                      />
                    </div>
                  </div>
                </div>
              </div>
              {whitelabel_id > 0 && (
                <div className="row">
                  {showChangeGroupSelect && (
                    <ChangeUserGroupModal
                      visible={showChangeGroupSelect}
                      options={groupsSelectOptions}
                      close={this.toggleChangeGroup.bind(this)}
                      selectedGroups={selectedGroups}
                      setSelectedGroups={this.setSelectedGroups.bind(this)}
                      changeGroups={this.changeGroups.bind(this)}
                    />
                  )}
                  <button
                    type="button"
                    className="btn btn-info"
                    disabled={choosedUsers.length == 0}
                    onClick={this.toggleChangeGroup.bind(this)}
                  >
                    <i className="mdi mdi-account-plus m-r-10"></i>
                    {gettext('Add to Groups')}
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  }
}

UsersView.contextType = CrmContext;
export default withRouter(UsersView);
