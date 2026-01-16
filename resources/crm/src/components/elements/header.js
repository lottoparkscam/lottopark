import React from 'react';
import CrmContext from '../../helpers/context';
import { withRouter } from 'react-router-dom';
import PerfectScrollbar from 'react-perfect-scrollbar';
import 'react-perfect-scrollbar/dist/css/styles.css';
import { Link } from 'react-router-dom';

class Header extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      showMobile: false,
      showTop: false,
      showDropdown: false,
    };
  }

  mobileToggle() {
    this.setState((prevState) => ({
      showMobile: !prevState.showMobile,
    }));
    this.props.toggleMobileNavbar();
  }

  toggleTopBar() {
    this.setState((prevState) => ({
      showTop: !prevState.showTop,
    }));
  }

  toggleDropdown() {
    this.setState((prevState) => ({
      showDropdown: !prevState.showDropdown,
    }));
  }

  change(whitelabel) {
    this.context.changeWhitelabel(whitelabel);
    this.toggleDropdown();
    this.props.history.push('/');
    this.props.setPageTitle(whitelabel.name);
    window.location.reload();
  }

  render() {
    const { gettext } = this.context.textdomain;
    return (
      <header className="topbar" data-navbarbg="skin6">
        <nav className="navbar top-navbar navbar-expand-md navbar-light">
          <div
            className={
              'navbar-header ' + (this.props.showTopLogo && 'expand-logo')
            }
            data-logobg="skin5"
          >
            <div
              className="nav-toggler waves-effect waves-light d-block d-md-none"
              onClick={this.mobileToggle.bind(this)}
            >
              <i className={this.state.showMobile ? 'ti-close' : 'ti-menu'}></i>
            </div>

            <div className="navbar-brand">
              <a className="logo">
                <span className="logo-text">
                  <img
                    src="/assets/images/crm/logo.png"
                    className="light-logo"
                    alt="homepage"
                  />
                </span>
              </a>
              <div
                className="sidebartoggler d-none d-md-block"
                onClick={this.props.toggleNavbar}
                data-sidebartype="mini-sidebar"
              >
                <i
                  className={
                    'cursor-pointer mdi mdi-toggle-switch font-20 ' +
                    (!this.props.showTopLogo &&
                      this.props.hideLeftNavbar === 'hidden' &&
                      'mdi-toggle-switch-off')
                  }
                ></i>
              </div>
            </div>

            <div
              className="topbartoggler d-block d-md-none waves-effect waves-light collapsed"
              onClick={this.toggleTopBar.bind(this)}
              data-toggle="collapse"
              data-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <i className="ti-more"></i>
            </div>
          </div>

          <div
            className={
              'navbar-collapse collapse ' + (this.state.showTop ? 'show' : '')
            }
            id="navbarSupportedContent"
            data-navbarbg="skin6"
          >
            <ul className="navbar-nav float-right">
              <li className="nav-item dropdown">
                <a
                  className="nav-link dropdown-toggle waves-effect waves-dark pro-pic"
                  onClick={this.toggleDropdown.bind(this)}
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  <span className="m-l-5 font-medium d-sm-inline-block">
                    {this.context.choosedWhitelabel
                      ? this.context.choosedWhitelabel.name
                      : ''}{' '}
                    ({this.context.user.email}){' '}
                    <i className="mdi mdi-chevron-down"></i>
                  </span>
                </a>
                <div
                  className={
                    'whitelabel-list dropdown-menu dropdown-menu-right user-dd animated flipInY ' +
                    (this.state.showDropdown ? 'show' : '')
                  }
                >
                  <span className="with-arrow">
                    <span className="bg-primary"></span>
                  </span>
                  <div className="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
                    <div className=""></div>
                    <div className="m-l-10">
                      <h4 className="m-b-0">
                        {this.context.choosedWhitelabel
                          ? this.context.choosedWhitelabel.name
                          : ''}
                      </h4>
                    </div>
                  </div>
                  <PerfectScrollbar>
                    <div className="profile-dis scrollable">
                      {this.context.whitelabels
                        ? this.context.whitelabels.map((whitelabel, index) => (
                            <div key={index}>
                              <a
                                className="dropdown-item cursor-pointer"
                                onClick={() => {
                                  this.change(whitelabel);
                                }}
                              >
                                <i className="ti-user m-r-5 m-l-5"></i>{' '}
                                {whitelabel.name}
                              </a>
                              <div className="dropdown-divider"></div>
                            </div>
                          ))
                        : gettext('Loading...')}
                    </div>
                  </PerfectScrollbar>
                </div>
              </li>
              <li className="nav-item">
                <Link
                  className="nav-link waves-effect waves-dark pro-pic"
                  to="/signout"
                >
                  <i className="mdi mdi-power"></i>
                  <span className="hide-menu m-l-5 font-medium">
                    {gettext('Sign Out')}
                  </span>
                </Link>
              </li>
            </ul>
          </div>
        </nav>
      </header>
    );
  }
}

Header.contextType = CrmContext;
export default withRouter(Header);
