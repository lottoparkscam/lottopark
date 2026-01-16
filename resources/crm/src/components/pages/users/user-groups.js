import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import { Link } from 'react-router-dom';
import axios from '../../../helpers/interceptors';
import Loading from '../../elements/loading';
import DefaultUserGroupSelect from '../../elements/default-user-group-select';
import Swal from 'sweetalert2';
import toastr from 'toastr';

class UserGroups extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      groups: [],
      whitelabel_id: null,
    };
  }

  componentDidMount() {
    this.context.verifyToken();

    let whitelabel_id = this.context.choosedWhitelabel.id;
    this.setState({ whitelabel_id }, () => {
      this.fetchGroups();
    });
  }

  fetchGroups() {
    const { whitelabel_id } = this.state;
    axios.post('/whitelabel_user_groups', { whitelabel_id }).then((res) => {
      if (res.data.code == 200) {
        let groups = res.data.groups;
        this.setState({
          groups,
          loading: false,
          whitelabel_id,
        });
      }
    });
  }

  deleteGroup(id) {
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
          .post('/whitelabel_user_groups/delete', {
            id,
          })
          .then((res) => {
            let message = res.data.message;
            if (res.data.code == 400) {
              this.context.showToast('error', message);
            } else if (res.data.code == 200) {
              this.fetchGroups();
              this.context.showToast('success', message);
            }
          });
      }
    });
  }

  showChangePanel() {
    let choosedWl = this.context.choosedWhitelabel.id;
    let userRole = this.context.user.role_id;
    if (!choosedWl || choosedWl == 0) {
      return false;
    }
    if (userRole != '1' && userRole != '3') {
      return false;
    }
    return true;
  }

  changeDefaultGroup(group) {
    const { gettext } = this.context.textdomain;
    const { whitelabel_id } = this.state;
    let new_default = null;
    if (group != 0) {
      new_default = group;
    }
    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Yes'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        axios
          .post('/whitelabel_user_groups/change', {
            whitelabel_id,
            new_default,
          })
          .then((res) => {
            if (res.data.code == 200) {
              let message = res.data.message;
              this.showToast(message);
              this.fetchGroups();
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

  render() {
    const { loading, groups } = this.state;
    const { showLink } = this.context;
    const { gettext } = this.context.textdomain;

    return loading ? (
      <Loading />
    ) : (
      <div className="user-groups-page">
        {this.showChangePanel() && (
          <div className="row">
            <div className="col">
              <div className="card">
                <div className="card-body">
                  <h4 className="card-title">
                    {gettext('Whitelabel default group')}
                    <DefaultUserGroupSelect
                      groups={groups}
                      changeDefaultGroup={this.changeDefaultGroup.bind(this)}
                    />
                  </h4>
                </div>
              </div>
            </div>
          </div>
        )}
        <div className="row">
          <div className="col">
            <div className="card">
              <div className="card-body">
                <h4 className="card-title">{gettext('User groups')}</h4>
                {showLink('user-groups-edit') &&
                  this.context.choosedWhitelabel.id > 0 && (
                    <Link
                      className="btn btn-primary mb-2"
                      to={{
                        pathname: '/whitelabel/users/groups/new',
                      }}
                    >
                      <i className="mdi mdi-plus"></i>{' '}
                      {gettext('Add new Group')}
                    </Link>
                  )}
                <div className="table-responsive">
                  <table className="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">{gettext('Name')}</th>
                        <th scope="col">{gettext('Prize Payout')}</th>
                        <th scope="col">{gettext('Default')}</th>
                        <th scope="col">{gettext('Selectable by User')}</th>
                        <th className="admins-table-manage">
                          {gettext('Manage')}
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      {groups.map((group, index) => (
                        <tr key={index + 1}>
                          <th scope="row">{index + 1}</th>
                          <td>{group.name}</td>
                          <td>{group.prize_payout_percent_display}</td>
                          <td>
                            {group.is_default ? gettext('Yes') : gettext('No')}
                          </td>
                          <td>
                            {group.is_selectable_by_user === '1'
                              ? gettext('Yes')
                              : gettext('No')}
                          </td>
                          {
                            <td>
                              {showLink('user-groups-edit') && (
                                <div className="users-tooltip">
                                  <Link
                                    to={{
                                      pathname: `/whitelabel/users/groups/edit/${group.id}`,
                                    }}
                                  >
                                    <i className="users-edit mdi mdi-pencil" />
                                  </Link>
                                  <span className="users-tooltiptext">
                                    {gettext('Edit group')}
                                  </span>
                                </div>
                              )}
                              {showLink('user-groups-delete') && (
                                <div className="users-tooltip">
                                  <a
                                    className="cursor-pointer"
                                    onClick={() => {
                                      this.deleteGroup(group.id);
                                    }}
                                  >
                                    <i className="users-delete mdi mdi-delete" />
                                  </a>
                                  <span className="users-tooltiptext">
                                    {gettext('Delete group')}
                                  </span>
                                </div>
                              )}
                              {showLink('users-view') && (
                                <div className="users-tooltip">
                                  <Link
                                    to={{
                                      pathname: `/whitelabel/users`,
                                      query: { group_id: group.id },
                                    }}
                                  >
                                    <i className="users-edit mdi mdi-account-multiple m-l-5" />
                                  </Link>
                                  <span className="users-tooltiptext">
                                    {gettext('View all users from this group')}
                                  </span>
                                </div>
                              )}
                            </td>
                          }
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

UserGroups.contextType = CrmContext;
export default withRouter(UserGroups);
