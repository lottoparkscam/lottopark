import React from 'react';
import axios from '../../helpers/interceptors';
import Cookies from 'js-cookie';
import LoginForm from '../auth/login';
import SignOut from '../auth/signout';
import Dashboard from './dashboard/dashboard';
import AdminUser from './admins/admin-user';
import AdminUsers from './admins/all-admin-users';
import UsersView from './users/users-view';
import UserDetails from './users/user-details';
import UserEdit from './users/user-edit';
import Tickets from './tickets/tickets';
import TicketDetails from './tickets/ticket-details';
import MultiDrawTickets from './tickets/multi-draw-tickets';
import UserGroups from './users/user-groups';
import UserGroupEdit from './users/user-group-edit';
import NewUserGroup from './users/new-user-group';
import UserSingleFieldEdit from '../elements/user-single-field-edit';
import UserBalanceEdit from '../pages/users/user-balance-edit';
import UserManualDepositAdd from '../pages/users/user-manual-deposit-add';
import Transactions from '../pages/transactions/transactions';
import Withdrawals from '../pages/withdrawals/withdrawals';
import WithdrawalDetails from '../pages/withdrawals/withdrawal-details';
import TransactionDetails from '../pages/transactions/transaction-details';
import RaffleTickets from '../pages/raffle-tickets/raffle-tickets';
import RaffleTicketDetails from '../pages/raffle-tickets/raffle-ticket-details';
import Logs from '../pages/logs';
import ApiDoc from '../pages/api/doc';
import Unauthorized from './errors/unauthorized';
import Maintenance from './errors/maintenance';
import Loading from '../elements/loading';
import {
  AdminRoute,
  PrivateRoute,
  ProtectedRoute,
} from '../../helpers/middleware';
import { history } from '../../helpers/routing';
import { Router, Switch, Route } from 'react-router-dom';

import buildTextdomain from '../../gettext/buildTextdomain';
import { CrmProvider } from '../../helpers/context';
import toastr from 'toastr';
import CasinoTransactions from './transactions/CasinoTransactions';
import Casino from './Reports/Casino';
import CasinoGamesOrder from './Settings/Casino/CasinoGamesOrder';
import CasinoSettings from './Settings/Casino/CasinoSettings';
import AcceptanceRate from './Reports/AcceptanceRate';
import Generator from './SeoWidgets/Generator';
import LtechManualDraws from './Draws/LtechManualDraws';

class App extends React.Component {
  constructor(props) {
    super(props);

    let choosedWhitelabel = null;
    let choosed = Cookies.get('choosedWhitelabel');
    if (choosed) {
      choosedWhitelabel = JSON.parse(choosed);
    }

    this.state = {
      loading: true,
      user: null,
      token: Cookies.get('token'),
      modules: [],
      whitelabels: [],
      choosedWhitelabel: choosedWhitelabel,
      textdomain: this.getTextdomain('en_GB'),
    };
  }

  isSuperadmin() {
    return this.state.choosedWhitelabel?.name === 'Superadmin';
  }

  isNotSuperadmin() {
    return !this.isSuperadmin();
  }

  getTextdomain(lang) {
    let pofile = {
      locale_data: { messages: {} },
    };

    try {
      pofile = require(`../../../translations/${lang}/${lang}.json`);
    } catch (e) {
      console.log(e);
    }

    return buildTextdomain(pofile.locale_data.messages);
  }

  componentDidMount() {
    if (this.state.token) {
      this.verifyToken();
    } else {
      this.setState({ loading: false });
    }
  }

  verifyToken() {
    try {
      axios.get('/checklogged').then((response) => {
        const responseInvalid =
          typeof response === 'undefined' || !('data' in response);

        if (responseInvalid) {
          this.signOut();
          return;
        }

        if (response.data.code !== 200) {
          this.signOut();
        }

        this.getUser();
      });
    } catch (e) {
      console.log(e);
      this.signOut();
    }
  }

  getUser() {
    try {
      axios.get('/userprofile').then((res) => {
        if (res.data.code == 200) {
          let user = res.data.user;
          let textdomain = this.getTextdomain(user.code);
          this.setState({ user, textdomain });

          this.getModules();
        }
      });
    } catch (e) {
      console.log(e);
    }
  }

  getModules() {
    try {
      axios.get('/modules').then((res) => {
        if (res.data.code == 200) {
          let modules = [];
          let whitelabels = [];
          if (res.data.modules) {
            modules = res.data.modules;
            whitelabels = res.data.whitelabels;
            if (this.state.user.role_id == 1 || this.state.user.role_id == 2) {
              whitelabels.unshift({
                name: 'Superadmin',
                id: 0,
              });
            }
            modules.forEach((mod) => {
              if (mod.whitelabel_name === null) {
                mod.whitelabel_name = 'Superadmin';
                mod.whitelabel_id = 0;
              }
            });
          }
          if (!this.state.choosedWhitelabel) {
            let choosedWhitelabel = null;
            choosedWhitelabel = whitelabels[0];
            this.changeWhitelabel(choosedWhitelabel);
          }
          this.setState({
            modules,
            whitelabels,
            loading: false,
          });
        }
      });
    } catch (e) {
      console.log(e);
    }
  }

  handleLogIn(remember, user, token) {
    this.setState({ loading: true });
    this.getModules();
    this.setCookies(remember, user, token);
    this.setUser(user, token);
  }

  setUser(user, token) {
    let textdomain = this.getTextdomain(user.code);
    this.setState({
      user,
      token,
      textdomain,
    });
  }

  setCookies(remember = false, user, token) {
    if (Cookies.get('token')) {
      Cookies.remove('token');
    }
    Cookies.set('token', token, {
      expires: remember ? 365 : null,
      secure: true,
    });
  }

  signOut() {
    Cookies.remove('token');
    Cookies.remove('choosedWhitelabel');
    this.setState({
      user: null,
      token: null,
      choosedWhitelabel: null,
    });

    history.push('/login');
  }

  setAuthorized() {
    this.setState({
      notAuthorized: false,
    });
  }

  changeWhitelabel(whitelabel) {
    this.setState({ choosedWhitelabel: whitelabel }, () => {
      if (Cookies.get('choosedWhitelabel')) {
        Cookies.remove('choosedWhitelabel');
      }
      Cookies.set('choosedWhitelabel', JSON.stringify(whitelabel));
    });
  }

  showLink(name) {
    if (this.state.user.role_id === '1') return true;
    if (
      !this.state.modules.find(
        (x) =>
          x.module_name === name &&
          x.whitelabel_id === this.state.choosedWhitelabel.id,
      )
    )
      return false;
    return true;
  }

  showToast(type, message) {
    toastr.options = {
      positionClass: 'toast-bottom-left',
      hideDuration: 300,
      timeOut: 10000,
    };

    toastr.clear();
    switch (type) {
      case 'success':
        setTimeout(() => toastr.success(message), 300);
        break;
      case 'error':
        setTimeout(() => toastr.error(message), 300);
        break;
    }
  }

  useLoginsForUsers() {
    if (
      this.state.choosedWhitelabel.id == '0' ||
      this.state.choosedWhitelabel.use_logins_for_users == '1'
    ) {
      return true;
    }
    return false;
  }

  showV2Option(whitelabel_id) {
    let whitelabel = this.state.whitelabels.find((x) => whitelabel_id == x.id);
    if (
      this.state.user.role_id == '1' ||
      this.state.user.role_id == '2' ||
      whitelabel.type == 2
    ) {
      return true;
    }
    return false;
  }

  isWhitelabelTypeV2() {
    const { choosedWhitelabel } = this.state;
    return (
      'type' in choosedWhitelabel && parseInt(choosedWhitelabel.type) === 2
    );
  }

  render() {
    const {
      loading,
      user,
      token,
      modules,
      whitelabels,
      choosedWhitelabel,
      textdomain,
    } = this.state;
    return (
      <Router history={history}>
        <CrmProvider
          value={{
            textdomain: textdomain,
            verifyToken: this.verifyToken.bind(this),
            changeWhitelabel: this.changeWhitelabel.bind(this),
            signOut: this.signOut.bind(this),
            showLink: this.showLink.bind(this),
            showToast: this.showToast.bind(this),
            useLoginsForUsers: this.useLoginsForUsers.bind(this),
            showV2Option: this.showV2Option.bind(this),
            user: user,
            token: token,
            loading: loading,
            choosedWhitelabel: choosedWhitelabel,
            whitelabels: whitelabels,
            modules: modules,
            isSuperadmin: this.isSuperadmin.bind(this),
            isWhitelabelTypeV2: this.isWhitelabelTypeV2.bind(this),
          }}
        >
          <Switch>
            <Route exact path="/403">
              <Unauthorized />
            </Route>
            <Route exact path="/503">
              <Maintenance />
            </Route>
            <Route exact path="/login">
              <LoginForm
                handleLogIn={this.handleLogIn.bind(this)}
                setUser={this.setUser.bind(this)}
                setCookies={this.setCookies.bind(this)}
              />
            </Route>
            <Route exact path="/signout">
              <SignOut />
            </Route>
            <Route exact path="/api/doc">
              <ApiDoc />
            </Route>
            {loading ? (
              <Loading />
            ) : (
              <React.Fragment>
                <PrivateRoute
                  exact
                  path="/"
                  pageName={textdomain.gettext('Dashboard')}
                  showTitle={false}
                >
                  <Dashboard />
                </PrivateRoute>
                <PrivateRoute
                  key="edit-profile"
                  exact
                  path="/profile"
                  pageName={textdomain.gettext('Edit Profile')}
                  showTitle={true}
                >
                  <AdminUser myProfile={true} />
                </PrivateRoute>
                <ProtectedRoute
                  key="reports-casino"
                  exact
                  path="/crm/reports/casino"
                  modName="casino-reports-view"
                  pageName={textdomain.gettext('Casino Reports')}
                  showTitle={true}
                >
                  <Casino />
                </ProtectedRoute>
                <ProtectedRoute
                  key="reports-acceptance-rate"
                  exact
                  path="/crm/reports/acceptance_rate"
                  modName="acceptance-rate-report-view"
                  pageName={textdomain.gettext('Acceptance Rate')}
                  showTitle={true}
                >
                  <AcceptanceRate />
                </ProtectedRoute>
                <ProtectedRoute
                  key="logs-view"
                  exact
                  path="/crm/logs"
                  modName="logs-view"
                  pageName={textdomain.gettext('Logs')}
                  showTitle={true}
                >
                  <Logs />
                </ProtectedRoute>
                <AdminRoute
                  key="add-admin"
                  exact
                  path="/new"
                  pageName={textdomain.gettext('Add New Admin')}
                  showTitle={true}
                >
                  <AdminUser />
                </AdminRoute>
                <PrivateRoute
                  key="all-admins"
                  exact
                  path="/admin/users"
                  user={user}
                  pageName={textdomain.gettext('All Admins')}
                  showTitle={true}
                >
                  <AdminUsers />
                </PrivateRoute>
                <PrivateRoute
                  key="edit-admin"
                  exact
                  path="/admin/users/:id"
                  modName="admins-edit"
                  user={user}
                  pageName={textdomain.gettext('Edit Admin')}
                  showTitle={true}
                >
                  <AdminUser userProfile={true} />
                </PrivateRoute>
                <ProtectedRoute
                  key="view-users"
                  exact
                  path="/whitelabel/users"
                  modName="users-view"
                  pageName={textdomain.gettext('Users')}
                  showTitle={true}
                >
                  <UsersView />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-user"
                  exact
                  path="/whitelabel/users/view/:id"
                  modName="users-view"
                  pageName={textdomain.gettext('User Details')}
                  showTitle={true}
                >
                  <UserDetails />
                </ProtectedRoute>
                <ProtectedRoute
                  key="edit-user"
                  exact
                  path="/whitelabel/users/edit/:id"
                  modName="users-edit-account-personal-data"
                  pageName={textdomain.gettext('Edit User')}
                  showTitle={true}
                >
                  <UserEdit />
                </ProtectedRoute>
                <ProtectedRoute
                  key="add-user-manual-deposit"
                  exact
                  path="/whitelabel/users/edit/:id/manual_deposit/add"
                  modName="users-manual-deposit-add"
                  pageName={textdomain.gettext('Add Manual Deposit')}
                  showTitle={true}
                >
                  <UserManualDepositAdd />
                </ProtectedRoute>
                <ProtectedRoute
                  key="add-user-bonus-manual-deposit"
                  exact
                  path="/whitelabel/users/edit/:id/manual_deposit/is_bonus/add"
                  modName="users-bonus-balance-manual-deposit-add"
                  pageName={textdomain.gettext(
                    'Add Manual Deposit To Bonus Balance',
                  )}
                  showTitle={true}
                >
                  <UserManualDepositAdd isBonus={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="add-user-casino-manual-deposit"
                  exact
                  path="/whitelabel/users/edit/:id/manual_deposit/is_casino/add"
                  modName="users-manual-deposit-casino-add"
                  pageName={textdomain.gettext(
                    'Add Manual Deposit To Casino Balance',
                  )}
                  showTitle={true}
                >
                  <UserManualDepositAdd isCasino={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="edit-user-balance"
                  exact
                  path="/whitelabel/users/edit/:id/balance/edit"
                  modName="users-balance-edit"
                  pageName={textdomain.gettext('Edit Balance')}
                  showTitle={true}
                >
                  <UserBalanceEdit />
                </ProtectedRoute>
                <ProtectedRoute
                  key="edit-bonus-user-balance"
                  exact
                  path="/whitelabel/users/edit/:id/balance/is_bonus/edit"
                  modName="users-bonus-balance-edit"
                  pageName={textdomain.gettext('Edit Bonus Balance')}
                  showTitle={true}
                >
                  <UserBalanceEdit isBonus={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="edit-casino-user-balance"
                  exact
                  path="/whitelabel/users/edit/:id/balance/is_casino/edit"
                  modName="users-balance-casino-edit"
                  pageName={textdomain.gettext('Edit Casino Balance')}
                  showTitle={true}
                >
                  <UserBalanceEdit isCasino={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="edit-user-field"
                  exact
                  path="/whitelabel/users/edit/:id/:field"
                  modName="users-edit-account-personal-data"
                  pageName={textdomain.gettext('Edit User')}
                  showTitle={true}
                >
                  <UserSingleFieldEdit />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-tickets"
                  exact
                  path="/crm/tickets"
                  modName="tickets-view"
                  pageName={textdomain.gettext('Tickets')}
                  showTitle={true}
                >
                  <Tickets />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-multi-draw-tickets"
                  exact
                  path="/crm/multidraw_tickets"
                  modName="tickets-view"
                  pageName={textdomain.gettext('Multi-draw Tickets')}
                  showTitle={true}
                >
                  <MultiDrawTickets />
                </ProtectedRoute>
                <ProtectedRoute
                  key="ticket-details"
                  exact
                  path="/crm/tickets/:id"
                  modName="tickets-view"
                  pageName={textdomain.gettext('Ticket details')}
                  showTitle={true}
                >
                  <TicketDetails />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-deposits-lottery"
                  exact
                  path="/crm/deposits/lottery"
                  modName="deposits-view"
                  pageName={textdomain.gettext('Lottery Deposits')}
                  showTitle={true}
                >
                  <Transactions isDeposit={true} isCasino={false} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-deposits-casino"
                  exact
                  path="/crm/deposits/casino"
                  modName="casino-deposits-view"
                  pageName={textdomain.gettext('Casino Deposits')}
                  showTitle={true}
                >
                  <Transactions isDeposit={true} isCasino={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-transactions-lottery"
                  exact
                  path="/crm/transactions/lottery"
                  modName="transactions-view"
                  pageName={textdomain.gettext('Lottery Transactions')}
                  showTitle={true}
                >
                  <Transactions isDeposit={false} isCasino={false} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-transactions-casino"
                  exact
                  path="/crm/transactions/casino"
                  modName="casino-transactions-view"
                  pageName={textdomain.gettext('Casino Transactions')}
                  showTitle={true}
                >
                  <CasinoTransactions />
                </ProtectedRoute>
                <ProtectedRoute
                  key="transaction-details"
                  exact
                  path="/crm/transactions/lottery/view/:id"
                  modName="transactions-view"
                  pageName={textdomain.gettext('Lottery Transaction Details')}
                  showTitle={false}
                >
                  <TransactionDetails isCasino={false} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="casino-transaction-details"
                  exact
                  path="/crm/transactions/casino/view/:id"
                  modName="casino-transactions-view"
                  pageName={textdomain.gettext('Casino Transaction Details')}
                  showTitle={false}
                >
                  <TransactionDetails isCasino={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-withdrawals"
                  exact
                  path="/crm/withdrawals/lottery"
                  modName="withdrawals-view"
                  pageName={textdomain.gettext('Lottery Withdrawals')}
                  showTitle={true}
                >
                  <Withdrawals isCasino={false} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-casino-withdrawals"
                  exact
                  path="/crm/withdrawals/casino"
                  modName="casino-withdrawals-view"
                  pageName={textdomain.gettext('Casino Withdrawals')}
                  showTitle={true}
                >
                  <Withdrawals isCasino={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="withdrawal-details"
                  exact
                  path="/crm/withdrawals/lottery/view/:id"
                  modName="withdrawals-view"
                  pageName={textdomain.gettext('Lottery Withdrawal details')}
                  showTitle={false}
                >
                  <WithdrawalDetails isCasino={false} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="casino-withdrawal-details"
                  exact
                  path="/crm/withdrawals/casino/view/:id"
                  modName="casino-withdrawals-view"
                  pageName={textdomain.gettext('Casino Withdrawal details')}
                  showTitle={false}
                >
                  <WithdrawalDetails isCasino={true} />
                </ProtectedRoute>
                <ProtectedRoute
                  key="view-user-groups"
                  exact
                  path="/whitelabel/users/groups"
                  modName="user-groups-view"
                  pageName={textdomain.gettext('User Groups')}
                  showTitle={true}
                >
                  <UserGroups />
                </ProtectedRoute>
                <ProtectedRoute
                  key="edit-user-group"
                  exact
                  path="/whitelabel/users/groups/edit/:id"
                  modName="user-groups-edit"
                  pageName={textdomain.gettext('User Group edit')}
                  showTitle={true}
                >
                  <UserGroupEdit />
                </ProtectedRoute>
                <ProtectedRoute
                  key="new-user-group"
                  exact
                  path="/whitelabel/users/groups/new"
                  modName="user-groups-edit"
                  pageName={textdomain.gettext('New User Group')}
                  showTitle={true}
                >
                  <NewUserGroup />
                </ProtectedRoute>
                <ProtectedRoute
                  key="raffle-tickets"
                  exact
                  path="/crm/raffle_tickets"
                  modName="raffle-tickets-view"
                  pageName={textdomain.gettext('Raffle tickets')}
                  showTitle={true}
                >
                  <RaffleTickets />
                </ProtectedRoute>
                <ProtectedRoute
                  key="raffle-ticket-details"
                  exact
                  path="/crm/raffle_tickets/:id"
                  modName="raffle-tickets-view"
                  pageName={textdomain.gettext('Raffle ticket details')}
                  showTitle={true}
                >
                  <RaffleTicketDetails />
                </ProtectedRoute>
                {this.isNotSuperadmin() && (
                  <>
                    <ProtectedRoute
                      key="whitelabel-casino-settings"
                      exact
                      path="/crm/settings/casino"
                      modName="whitelabel-casino-settings"
                      pageName={textdomain.gettext('Casino settings')}
                      showTitle={true}
                    >
                      <CasinoSettings />
                    </ProtectedRoute>
                    <ProtectedRoute
                      key="whitelabel-casino-settings-game-order"
                      exact
                      path="/crm/settings/casino/games-order"
                      modName="whitelabel-casino-settings-game-order"
                      pageName={textdomain.gettext('Casino games order')}
                      showTitle={true}
                    >
                      <CasinoGamesOrder />
                    </ProtectedRoute>
                  </>
                )}
                <ProtectedRoute
                  key="ltech-manual-draws"
                  exact
                  path="/crm/draws/ltech-manual-draws"
                  modName="ltech-manual-draws"
                  pageName={textdomain.gettext('Ltech Manual Draws')}
                  showTitle={true}
                >
                  <LtechManualDraws />
                </ProtectedRoute>
                <ProtectedRoute
                  key="seo-widgets-generator"
                  exact
                  path="/crm/seo-widgets/generator"
                  modName="seo-widgets-generator"
                  pageName={textdomain.gettext('Seo Widgets - Generator')}
                  showTitle={true}
                >
                  <Generator />
                </ProtectedRoute>
              </React.Fragment>
            )}
          </Switch>
        </CrmProvider>
      </Router>
    );
  }
}

export default App;
