import React from 'react';
import { withRouter } from 'react-router-dom';
import SidebarItem from './sidebar-item';
import CrmContext from '../../helpers/context';
import PerfectScrollbar from 'react-perfect-scrollbar';
import 'react-perfect-scrollbar/dist/css/styles.css';

class LeftSidebar extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      menuItems: [],
    };
  }

  componentDidMount() {
    const { gettext } = this.context.textdomain;
    let menuItems = [
      {
        name: gettext('MANAGEMENT'),
        type: 'menu-category',
      },
      {
        name: gettext('Dashboard'),
        icon: 'mdi-av-timer',
        selected: false,
        active: false,
        path: '/',
        type: 'menu-item',
      },
      {
        name: gettext('Administrators'),
        icon: 'mdi-face',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Admin Users'),
            path: '/admin/users',
            icon: 'mdi-account-multiple',
            machineName: 'admins-view',
            type: 'menu-subitem',
          },
        ],
      },
      {
        name: gettext('Users'),
        icon: 'mdi-account',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('All users'),
            path: '/whitelabel/users',
            icon: 'mdi-account-multiple',
            machineName: 'users-view',
            type: 'menu-subitem',
          },
          {
            name: gettext('Groups'),
            path: '/whitelabel/users/groups',
            icon: 'mdi-account-multiple-plus',
            machineName: 'user-groups-view',
            type: 'menu-subitem',
          },
        ],
      },
      {
        name: gettext('Tickets'),
        icon: 'mdi-bookmark',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('All Tickets'),
            icon: 'mdi-bookmark',
            machineName: 'tickets-view',
            selected: false,
            active: false,
            type: 'menu-item',
            path: '/crm/tickets',
          },
          {
            name: gettext('Multi-draw Tickets'),
            icon: 'mdi-bookmark-plus',
            machineName: 'tickets-view',
            selected: false,
            active: false,
            type: 'menu-item',
            path: '/crm/multidraw_tickets',
          },
        ],
      },
      {
        name: gettext('Raffle Tickets'),
        icon: 'mdi-star',
        selected: false,
        active: false,
        path: '/crm/raffle_tickets',
        machineName: 'raffle-tickets-view',
        type: 'menu-item',
      },
      {
        name: gettext('Transactions'),
        icon: 'mdi-currency-usd',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Lotteries'),
            icon: 'mdi-ticket-percent',
            selected: false,
            active: false,
            path: '/crm/transactions/lottery',
            machineName: 'transactions-view',
            type: 'menu-item',
          },
          {
            name: gettext('Casino'),
            icon: 'mdi-coin',
            selected: false,
            active: false,
            path: '/crm/transactions/casino',
            machineName: 'casino-transactions-view',
            type: 'menu-item',
          },
        ],
      },
      {
        name: gettext('Deposits'),
        icon: 'mdi-credit-card',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Lotteries'),
            icon: 'mdi-ticket-percent',
            selected: false,
            active: false,
            path: '/crm/deposits/lottery',
            machineName: 'deposits-view',
            type: 'menu-item',
          },
          {
            name: gettext('Casino'),
            icon: 'mdi-coin',
            selected: false,
            active: false,
            path: '/crm/deposits/casino',
            machineName: 'casino-deposits-view',
            type: 'menu-item',
          },
        ],
      },
      {
        name: gettext('Withdrawals'),
        icon: 'mdi-reply-all',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Lotteries'),
            icon: 'mdi-ticket-percent',
            selected: false,
            active: false,
            path: '/crm/withdrawals/lottery',
            machineName: 'withdrawals-view',
            type: 'menu-item',
          },
          {
            name: gettext('Casino'),
            icon: 'mdi-coin',
            selected: false,
            active: false,
            path: '/crm/withdrawals/casino',
            machineName: 'casino-withdrawals-view',
            type: 'menu-item',
          },
        ],
      },
      {
        name: gettext('Account'),
        icon: 'mdi-account-box',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Edit profile'),
            path: '/profile',
            icon: 'mdi-account-circle',
            type: 'menu-subitem',
          },
        ],
      },
      {
        name: gettext('Reports'),
        icon: 'mdi-chart-line',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Casino'),
            icon: 'mdi-coin',
            machineName: 'casino-reports-view',
            selected: false,
            active: false,
            type: 'menu-item',
            path: '/crm/reports/casino',
          },
          {
            name: gettext('Acceptance Rate'),
            icon: 'mdi-credit-card',
            machineName: 'acceptance-rate-report-view',
            selected: false,
            active: false,
            type: 'menu-item',
            path: '/crm/reports/acceptance_rate',
          },
        ],
      },
      {
        name: gettext('Logs'),
        icon: 'mdi-content-paste',
        selected: false,
        active: false,
        path: '/crm/logs',
        machineName: 'logs-view',
        type: 'menu-item',
      },
      {
        name: gettext('Settings'),
        icon: 'mdi-settings',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Casino'),
            icon: 'mdi-coin',
            selected: false,
            active: false,
            hidden: this.context.isSuperadmin(),
            type: 'menu-item',
            path: '/crm/settings/casino',
          },
        ],
      },
      {
        name: gettext('Draws'),
        icon: 'mdi-numeric',
        selected: false,
        active: false,
        type: 'menu-item',
        links: [
          {
            name: gettext('Ltech Manual Draws'),
            icon: 'mdi-pencil',
            selected: false,
            active: false,
            hidden: this.context.user.role_id != 1,
            type: 'menu-item',
            path: '/crm/draws/ltech-manual-draws',
          },
        ],
      },
      {
        name: gettext('Seo Widgets'),
        icon: 'mdi-application',
        selected: false,
        active: false,
        type: 'menu-item',
        machineName: 'seo-widgets-generator',
        links: [
          {
            name: gettext('Generator'),
            path: '/crm/seo-widgets/generator',
            icon: 'mdi-table-edit',
            type: 'menu-subitem',
            selected: false,
            active: false,
          },
        ],
      },
    ];

    this.setState({ menuItems }, () => {
      let currentPath = this.props.location.pathname;
      const checkPath = (obj) => currentPath.includes(obj.path);
      this.setState((prevState) => ({
        menuItems: prevState.menuItems.map((obj) =>
          (obj.path === '/' && currentPath === obj.path) ||
          (obj.path !== '/' &&
            (currentPath.includes(obj.path) ||
              (obj.links && obj.links.some(checkPath))))
            ? Object.assign(obj, {
                selected: true,
                active: true,
              })
            : Object.assign(obj, { selected: false, active: false }),
        ),
      }));
    });
  }

  clickItem(ind) {
    this.setState((prevState) => ({
      menuItems: prevState.menuItems.map((obj, index) =>
        index === ind
          ? Object.assign(obj, {
              active: !prevState.menuItems[index].active,
            })
          : Object.assign(obj, { active: false }),
      ),
    }));
    if (this.state.menuItems[ind].path) {
      this.props.history.push(this.state.menuItems[ind].path);
    }
  }

  render() {
    return (
      <aside
        className="left-sidebar"
        data-sidebarbg="skin5"
        onMouseOver={this.props.showTopLogo}
        onMouseOut={this.props.hideTopLogo}
      >
        <PerfectScrollbar>
          <div className="scroll-sidebar">
            <nav className="sidebar-nav">
              <ul id="sidebarnav">
                {this.state.menuItems.map((item, index) => (
                  <SidebarItem
                    item={item}
                    index={index}
                    key={index}
                    clickItem={this.clickItem.bind(this)}
                  />
                ))}
              </ul>
            </nav>
          </div>
        </PerfectScrollbar>
      </aside>
    );
  }
}

LeftSidebar.contextType = CrmContext;
export default withRouter(LeftSidebar);
