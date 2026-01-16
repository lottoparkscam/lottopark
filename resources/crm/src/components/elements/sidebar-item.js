import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../helpers/context';

import { Link } from 'react-router-dom';

class SidebarItem extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      choosedId: null,
      activeLinks: [],
    };
  }

  componentDidMount() {
    this.setActiveLinks();
  }

  componentDidUpdate() {
    if (this.context.choosedWhitelabel) {
      let choosedId = this.context.choosedWhitelabel.id;
      if (choosedId !== this.state.choosedId) {
        this.setActiveLinks();
        this.setState({ choosedId });
      }
    }
  }

  setActiveLinks() {
    let links = this.props.item.links;
    if (links) {
      let activeLinks = [];
      links.forEach((link) => {
        if (this.showLink(link)) {
          activeLinks.push(link);
        }
      });
      this.setState({
        activeLinks,
      });
    }
  }

  showLink(link) {
    if (link.hidden) return false;
    let role = this.context.user.role_id;
    if (role == 1 || role == 3) return true;
    if (
      link.machineName &&
      !this.context.modules.find(
        (x) =>
          x.module_name === link.machineName &&
          x.whitelabel_id === this.context.choosedWhitelabel.id,
      )
    )
      return false;
    return true;
  }

  render() {
    let currentPath = this.props.location.pathname;
    return this.props.item.type == 'menu-category' ? (
      <li className="nav-small-cap">
        <i className="mdi mdi-dots-horizontal"></i>
        <span className="hide-menu">{this.props.item.name}</span>
      </li>
    ) : this.props.item.links && this.state.activeLinks.length == 0 ? null : (
      this.showLink(this.props.item) && (
        <li
          className={
            'sidebar-item ' + (this.props.item.selected ? 'selected' : '')
          }
        >
          <a
            className={
              'sidebar-link waves-effect waves-dark ' +
              (this.props.item.links ? 'has-arrow ' : '') +
              (this.props.item.active ? 'active' : '')
            }
            aria-expanded="false"
            onClick={() => {
              this.props.clickItem(this.props.index);
            }}
          >
            <i className={'mdi ' + this.props.item.icon}></i>
            <span className="hide-menu">{this.props.item.name}</span>
          </a>
          {this.props.item.links && (
            <ul
              aria-expanded="false"
              className={
                'collapse first-level ' + (this.props.item.active ? 'in' : '')
              }
            >
              {this.state.activeLinks.map((link, index) => (
                <li
                  key={index}
                  className={
                    'sidebar-item ' +
                    (link.path === currentPath ? 'active' : '')
                  }
                >
                  <Link className="sidebar-link" to={link.path}>
                    <i className={'mdi ' + link.icon}></i>
                    <span className="hide-menu">{link.name}</span>
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </li>
      )
    );
  }
}

SidebarItem.contextType = CrmContext;
export default withRouter(SidebarItem);
