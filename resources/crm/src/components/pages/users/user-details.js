import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import { Link } from 'react-router-dom';
import Loading from '../../elements/loading';
import axios from '../../../helpers/interceptors';
import DataWithTooltip from '../../elements/data-with-tooltip';

class UserDetails extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      user: {},
      groups: [],
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    let token = this.props.match.params.id;
    let lng = this.context.user.code;
    axios
      .post('/user/details', {
        token,
        lng,
      })
      .then((res) => {
        if (res.data.code == 200) {
          let user = res.data.user;
          let groups = res.data.groups;
          this.setState({
            user,
            groups,
            loading: false,
          });
        }
      });
  }

  redirect() {
    this.props.history.goBack();
  }

  render() {
    const { user, groups, loading } = this.state;
    const { gettext } = this.context.textdomain;
    let whitelabel = this.context.whitelabels.find(
      (whitelabel) => user.whitelabel_prefix == whitelabel.prefix,
    );

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-lg-12">
          <div className="card">
            <div className="card-body">
              <h3 className="card-title">{user.email}</h3>
              <h6 className="card-subtitle">
                {gettext('You can view user details here.')}
              </h6>
              <div className="row">
                <div className="col-sm-12">
                  <p>
                    <button
                      className="btn btn-primary btn-rounded"
                      onClick={this.redirect.bind(this)}
                    >
                      {gettext('Go Back')}
                    </button>
                  </p>
                </div>
                <div className="col-lg-12 col-md-12 col-sm-12">
                  <div className="table-responsive">
                    <table className="table">
                      <tbody>
                        <tr>
                          <td width="390">{gettext('Token')}</td>
                          <td>{user.full_token}</td>
                        </tr>
                        {this.context.choosedWhitelabel.id === 0 && (
                          <tr>
                            <td width="390">{gettext('Whitelabel')}</td>
                            <td>{user.whitelabel_name}</td>
                          </tr>
                        )}
                        <tr>
                          <td>{gettext('Name')}</td>
                          <td>{user.name}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Surname')}</td>
                          <td>{user.surname}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Email')}</td>
                          <td>
                            {user.is_confirmed == 1 ? (
                              <i className="mdi mdi-check"></i>
                            ) : (
                              <i className="mdi mdi-close"></i>
                            )}
                            {user.email}
                            {this.context.modules.find(
                              (x) =>
                                x.module_name ===
                                'users-edit-account-personal-data',
                            ) && (
                              <div className="users-tooltip">
                                <Link
                                  to={{
                                    pathname: `/whitelabel/users/edit/${user.full_token}/email`,
                                  }}
                                >
                                  <i className="users-edit mdi mdi-pencil" />
                                </Link>
                                <span className="users-tooltiptext">
                                  {gettext('Edit E-mail')}
                                </span>
                              </div>
                            )}
                          </td>
                        </tr>
                        {whitelabel.use_logins_for_users == '1' && (
                          <tr>
                            <td>{gettext('Login')}</td>
                            <td>{user.login}</td>
                          </tr>
                        )}
                        <tr>
                          <td>{gettext('Password')}</td>
                          <td>
                            {'**********'}
                            {this.context.modules.find(
                              (x) =>
                                x.module_name ===
                                'users-edit-account-personal-data',
                            ) && (
                              <div className="users-tooltip">
                                <Link
                                  to={{
                                    pathname: `/whitelabel/users/edit/${user.full_token}/password`,
                                  }}
                                >
                                  <i className="users-edit mdi mdi-pencil" />
                                </Link>
                                <span className="users-tooltiptext">
                                  {gettext('Edit password')}
                                </span>
                              </div>
                            )}
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Country')}</td>
                          <td>{user.country_name}</td>
                        </tr>
                        <tr>
                          <td>{gettext('City')}</td>
                          <td>{user.city}</td>
                        </tr>
                        <tr>
                          <td>{gettext('State')}</td>
                          <td>{user.state}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Address #1')}</td>
                          <td>{user.address_1}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Address #2')}</td>
                          <td>{user.address_2}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Postal/ZIP Code')}</td>
                          <td>{user.zip}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Birthdate')}</td>
                          <td>{user.birthdate}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Phone')}</td>
                          <td>{user.phone}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Timezone')}</td>
                          <td>{user.timezone}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Gender')}</td>
                          <td>{user.gender}</td>
                        </tr>
                        <tr>
                          <td>{gettext('National ID')}</td>
                          <td>{user.national_id}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Language')}</td>
                          <td>{user.language}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Balance')}</td>
                          <td>
                            <DataWithTooltip
                              value={user.balance_display}
                              tooltipValue={user.balance_additional_text}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Bonus balance')}</td>
                          <td>
                            <DataWithTooltip
                              value={user.bonus_balance_display}
                              tooltipValue={user.bonus_balance_additional_text}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Casino balance')}</td>
                          <td>
                            <DataWithTooltip
                              value={user.casino_balance_display}
                              tooltipValue={user.casino_balance_additional_text}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Register Date')}</td>
                          <td>{user.date_register}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Register IP')}</td>
                          <td>{user.register_ip}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Last IP')}</td>
                          <td>{user.last_ip}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Last Active')}</td>
                          <td>{user.last_active}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Last Update')}</td>
                          <td>{user.last_update}</td>
                        </tr>
                        <tr>
                          <td>{gettext('First Purchase')}</td>
                          <td>{user.first_purchase}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Player lifetime')}</td>
                          <td>{user.player_lifetime} days</td>
                        </tr>
                        <tr>
                          <td>{gettext('Prize payout group')}</td>
                          <td>{user.group_name}</td>
                        </tr>
                        <tr>
                          <td>{gettext('User groups')}</td>
                          <td>
                            {groups.map(
                              (group, index) =>
                                group.name +
                                (index == groups.length - 1 ? '' : ', '),
                            )}
                          </td>
                        </tr>
                      </tbody>
                    </table>
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

UserDetails.contextType = CrmContext;
export default withRouter(UserDetails);
