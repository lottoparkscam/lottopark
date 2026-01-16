import React from 'react';
import Cookies from 'js-cookie';
import LeftSidebar from '../elements/left-sidebar';
import Header from '../elements/header';
import CrmContext from '../../helpers/context';
import { gettext } from '../../gettext/gettext';

class DefaultLayout extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      hideLeftNavbar: Cookies.get('navbarHidden')
        ? Cookies.get('navbarHidden')
        : 'shown',
      showTopLogo: false,
      mobileShowNavbar: false,
    };
  }

  componentDidMount() {
    this.setPageTitle(this.context.choosedWhitelabel.name);
  }

  setPageTitle(name) {
    const { gettext } = this.context.textdomain;
    let title =
      this.props.pageName + ' - ' + name + ' - ' + gettext('White Lotto CRM');
    document.title = title;
  }

  toggleNavbar() {
    const { hideLeftNavbar } = this.state;
    let navbarHidden = 'shown';
    if (hideLeftNavbar === 'hidden') {
      navbarHidden = 'shown';
    } else navbarHidden = 'hidden';

    Cookies.set('navbarHidden', navbarHidden);

    this.setState({
      hideLeftNavbar: navbarHidden,
    });
  }

  toggleMobileNavbar() {
    this.setState((prevState) => ({
      mobileShowNavbar: !prevState.mobileShowNavbar,
    }));
  }

  showTopLogo() {
    this.setState({
      showTopLogo: true,
    });
  }

  hideTopLogo() {
    this.setState({
      showTopLogo: false,
    });
  }

  render() {
    const { hideLeftNavbar, mobileShowNavbar } = this.state;
    return (
      this.context && (
        <div
          id="main-wrapper"
          data-theme="light"
          data-layout="vertical"
          data-navbarbg="skin6"
          data-sidebartype={
            hideLeftNavbar === 'hidden' ? 'mini-sidebar' : 'full'
          }
          data-sidebar-position="fixed"
          data-header-position="fixed"
          data-boxed-layout="full"
          className={
            mobileShowNavbar
              ? 'show-sidebar'
              : hideLeftNavbar === 'hidden'
              ? 'mini-sidebar'
              : 'hide-sidebar'
          }
        >
          <Header
            hideTopLogo={this.hideTopLogo.bind(this)}
            showTopLogo={this.showTopLogo.bind(this)}
            toggleNavbar={this.toggleNavbar.bind(this)}
            toggleMobileNavbar={this.toggleMobileNavbar.bind(this)}
            setPageTitle={this.setPageTitle.bind(this)}
            showTopLogo={this.state.showTopLogo}
            hideLeftNavbar={this.state.hideLeftNavbar}
          />
          <LeftSidebar
            hideTopLogo={this.hideTopLogo.bind(this)}
            showTopLogo={this.showTopLogo.bind(this)}
            toggleNavbar={this.toggleNavbar.bind(this)}
          />
          <div className="page-wrapper">
            {this.props.showTitle && (
              <div className="page-breadcrumb">
                <div className="row">
                  <div className="col-5 align-self-center">
                    <h4 className="page-title">{this.props.pageName}</h4>
                  </div>
                </div>
              </div>
            )}

            <div className="container-fluid">{this.props.children}</div>
          </div>
        </div>
      )
    );
  }
}
DefaultLayout.contextType = CrmContext;
export default DefaultLayout;
