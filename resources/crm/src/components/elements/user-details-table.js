import React from "react";
import { withRouter } from "react-router-dom";
import CrmContext from "../../helpers/context";

class UserDetailsTable extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      fieldname: "",
      value: "",
      formChecked: false,
      valueInvalid: false,
      valueInvalidMessage: ""
    };
  }

  componentDidMount() {}

  redirect() {
    this.props.history.goBack();
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { user } = this.props;

    return (
      <div className="row">
        <div className="col-lg-12">
          <div className="card">
            <div className="card-body">
              <h3 className="card-title">{user.email}</h3>
              <h6 className="card-subtitle">
                {gettext("You can view user details here.")}
              </h6>
              <div className="row">
                <div className="col-sm-12">
                  <p>
                    <button
                      className="btn btn-primary btn-rounded"
                      onClick={this.redirect.bind(this)}
                    >
                      {gettext("Go Back")}
                    </button>
                  </p>
                </div>
                <div className="col-lg-12 col-md-12 col-sm-12">
                  <div className="table-responsive">
                    <table className="table">
                      <tbody>
                        <tr>
                          <td width="390">{gettext("Token")}</td>
                          <td>{user.token}</td>
                        </tr>
                        {this.context.choosedWhitelabel.id === 0 && (
                          <tr>
                            <td width="390">{gettext("Whitelabel")}</td>
                            <td>{user.whitelabel_name}</td>
                          </tr>
                        )}
                        <tr>
                          <td>{gettext("Name")}</td>
                          <td>{user.name}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Surname")}</td>
                          <td>{user.surname}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Email")}</td>
                          <td>
                            {user.is_confirmed == 1 ? (
                              <i className="mdi mdi-check"></i>
                            ) : (
                              <i className="mdi mdi-close"></i>
                            )}
                            {user.email}
                            {this.context.modules.find(
                              x => x.module_name === "users-edit-account-personal-data"
                            ) && (
                              <i
                                className="admins-edit mdi mdi-pencil link"
                                onClick={() => {
                                  this.props.changeMode("email");
                                }}
                              />
                            )}
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext("Password")}</td>
                          <td>
                            {"**********"}
                            {this.context.modules.find(
                              x => x.module_name === "users-edit-account-personal-data"
                            ) && (
                              <i
                                className="admins-edit mdi mdi-pencil link"
                                onClick={() => {
                                  this.props.changeMode("password");
                                }}
                              />
                            )}
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext("Country")}</td>
                          <td>{user.country_name}</td>
                        </tr>
                        <tr>
                          <td>{gettext("City")}</td>
                          <td>{user.city}</td>
                        </tr>
                        <tr>
                          <td>{gettext("State")}</td>
                          <td>{user.state}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Address #1")}</td>
                          <td>{user.address_1}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Address #2")}</td>
                          <td>{user.address_2}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Postal/ZIP Code")}</td>
                          <td>{user.zip}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Birthdate")}</td>
                          <td>{user.birthdate}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Phone")}</td>
                          <td>{user.phone}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Timezone")}</td>
                          <td>{user.timezone}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Gender")}</td>
                          <td>{user.gender}</td>
                        </tr>
                        <tr>
                          <td>{gettext("National ID")}</td>
                          <td>{user.national_id}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Language")}</td>
                          <td>{user.language}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Balance")}</td>
                          <td>
                            {user.balance}
                            {user.formattedBalance && (
                              <div className="currency-tooltip">
                                <i className="m-l-10 mdi mdi-information"></i>
                                <span className="currency-tooltiptext">
                                  {user.formattedBalance}
                                </span>
                              </div>
                            )}
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext("Register Date")}</td>
                          <td>{user.date_register}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Register IP")}</td>
                          <td>{user.register_ip}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Last IP")}</td>
                          <td>{user.last_ip}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Last Active")}</td>
                          <td>{user.last_active}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Last Update")}</td>
                          <td>{user.last_update}</td>
                        </tr>
                        <tr>
                          <td>{gettext("First Purchase")}</td>
                          <td>{user.first_purchase}</td>
                        </tr>
                        <tr>
                          <td>{gettext("Player lifetime")}</td>
                          <td>{user.player_lifetime} days</td>
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

UserDetailsTable.contextType = CrmContext;
export default withRouter(UserDetailsTable);
