import React from 'react';
import axios from '../../../helpers/interceptors';
import { Link } from 'react-router-dom';
import Swal from 'sweetalert2';
import toastr from 'toastr';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';

class AdminUsers extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      users: [],
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    axios.get('/allusers').then((res) => {
      if (res.data.code == 200) {
        let users = res.data.users;
        if (users) {
          this.setState({
            loading: false,
            users,
          });
        }
      }
    });
  }

  deleteUser(id) {
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
          .post('/admin/delete', {
            id,
          })
          .then((res) => {
            if (res.data.code == 200) {
              let users = this.state.users.filter((user) => user.id !== id);
              this.setState({ users });
              let message = res.data.message;
              toastr.options = {
                positionClass: 'toast-bottom-left',
                hideDuration: 300,
                timeOut: 10000,
              };
              toastr.clear();
              setTimeout(() => toastr.success(message), 300);
            }
          });
      }
    });
  }

  showLink(name) {
    if (this.context.user.role_id == '1') return true;
    if (!this.context.modules.find((x) => x.module_name === name)) return false;
    return true;
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { users, loading } = this.state;
    const { role_id } = this.context.user;

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-12">
          <div className="card">
            <div className="card-body">
              <h4 className="card-title">{gettext('Administrators')}</h4>
              {(role_id == 1 || role_id == 3) && (
                <Link
                  className="btn btn-primary mb-2"
                  to={{
                    pathname: '/new',
                  }}
                >
                  <i className="mdi mdi-plus"></i> {gettext('Add new Admin')}
                </Link>
              )}
              <div className="table-responsive">
                <table className="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">{gettext('Username')}</th>
                      {(this.showLink('admins-edit') ||
                        this.showLink('admins-delete')) && (
                        <th className="admins-table-manage">
                          {gettext('Manage')}
                        </th>
                      )}
                    </tr>
                  </thead>
                  <tbody>
                    {users.map((user, index) => (
                      <tr key={index + 1}>
                        <th scope="row">{index + 1}</th>
                        <td>{user.username}</td>
                        {(this.showLink('admins-edit') ||
                          this.showLink('admins-delete')) && (
                          <td>
                            {this.showLink('admins-edit') && (
                              <Link to={{ pathname: `users/${user.id}` }}>
                                <i className="users-edit mdi mdi-pencil" />
                              </Link>
                            )}
                            {this.showLink('admins-delete') && (
                              <a
                                className="cursor-pointer"
                                onClick={() => {
                                  this.deleteUser(user.id);
                                }}
                              >
                                <i className="users-delete mdi mdi-delete" />
                              </a>
                            )}
                          </td>
                        )}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

AdminUsers.contextType = CrmContext;
export default AdminUsers;
