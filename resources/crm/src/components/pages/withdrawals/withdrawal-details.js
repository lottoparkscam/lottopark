import React from 'react';
import { withRouter } from 'react-router-dom';
import axios from '../../../helpers/interceptors';
import Swal from 'sweetalert2';
import toastr from 'toastr';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';
import DataWithTooltip from '../../elements/data-with-tooltip';

const DETAILS_URL = '/crm/withdrawal/details';
const DECLINE_URL = '/crm/withdrawal/decline';
const CANCEL_URL = '/crm/withdrawal/cancel';
const APPROVE_URL = '/crm/withdrawal/approve';

class WithdrawalDetails extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      details: [],
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    this.fetchTransactionData();
  }

  fetchTransactionData() {
    let token = this.props.match.params.id;
    let whitelabel_id = this.context.choosedWhitelabel.id;
    try {
      axios
        .post(DETAILS_URL, {
          token,
          whitelabel_id,
          isCasino: this.props.isCasino,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let details = res.data.details;
            this.setState({
              loading: false,
              details,
            });
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  declineWithdrawal(token, whitelabel) {
    this.withdrawalAction(DECLINE_URL, token, whitelabel);
  }
  approveWithdrawal(token, whitelabel) {
    this.withdrawalAction(APPROVE_URL, token, whitelabel);
  }

  withdrawalAction(url, token, whitelabel) {
    const { gettext } = this.context.textdomain;

    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Yes'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        let whitelabel_id = this.context.choosedWhitelabel.id;
        try {
          axios
            .post(url, {
              token,
              whitelabel,
              whitelabel_id,
              isCasino: this.props.isCasino,
            })
            .then((res) => {
              if (res.data.code == 200) {
                this.fetchTransactionData();
                let message = res.data.message;
                this.showToast(message);
              }
            });
        } catch (e) {
          console.log(e);
        }
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

  showLink(name) {
    if (this.context.user.role_id == '1') return true;
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

  redirect() {
    this.props.history.goBack();
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { details, loading } = this.state;

    let whitelabel = this.context.whitelabels.find(
      (x) => details.whitelabel_id == x.id,
    );

    let additionalData = [];

    if (details.data) {
      details.data.forEach(({ label, value }) => {
        additionalData.push(
          <tr key={`additional_details_${label}`}>
            <td width="390">{label}</td>
            <td>{value}</td>
          </tr>,
        );
      });
    }

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-lg-12">
          <div className="card">
            <div className="card-body">
              <h3 className="card-title">
                {gettext('Withdrawal details')}{' '}
                <small className="text-success">{details.full_token}</small>
              </h3>
              <h6 className="card-subtitle">
                {gettext('You can view withdrawal details here.')}
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
                          <td width="390">{gettext('ID')}</td>
                          <td>{details.full_token}</td>
                        </tr>
                        <tr>
                          <td width="390">{gettext('User ID')}</td>
                          <td>{details.user_token_full}</td>
                        </tr>
                        <tr>
                          <td>{gettext('First name')}</td>
                          <td>{details.user_name}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Last name')}</td>
                          <td>{details.user_surname}</td>
                        </tr>
                        <tr>
                          <td>{gettext('E-mail')}</td>
                          <td>{details.user_email}</td>
                        </tr>
                        {whitelabel.use_logins_for_users == '1' && (
                          <tr>
                            <td>{gettext('Login')}</td>
                            <td>{details['user_login']}</td>
                          </tr>
                        )}
                        <tr>
                          <td>{gettext('Date')}</td>
                          <td>{details.date}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Date approved')}</td>
                          <td>{details.date_confirmed}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Method')}</td>
                          <td>{details.method_name}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Amount')}</td>
                          <td>
                            <DataWithTooltip
                              value={details.amount_display}
                              tooltipValue={details.amount_additional_text}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>
                            {gettext(
                              this.props.isCasino
                                ? 'Casino balance'
                                : 'User balance',
                            )}
                          </td>
                          <td>
                            <DataWithTooltip
                              value={details.user_balance_display}
                              tooltipValue={details.balance_additional_text}
                              additionalClass={
                                details.user_balance_class_danger
                              }
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Prize payout group')}</td>
                          <td>{details.user_prize_group_name}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Status')}</td>
                          <td>{details.status_display}</td>
                        </tr>
                      </tbody>
                    </table>
                    <h3 className="box-title m-t-40" key="box-title">
                      {gettext('Request details')}
                    </h3>
                    <table className="table" key="table">
                      <tbody>
                        {details.data.name && (
                          <tr>
                            <td width="390">{gettext('First name')}</td>
                            <td>{details.data.name}</td>
                          </tr>
                        )}
                        {details.data.surname && (
                          <tr>
                            <td width="390">{gettext('Last name')}</td>
                            <td>{details.data.surname}</td>
                          </tr>
                        )}
                        {additionalData}
                      </tbody>
                    </table>
                    {this.showLink(
                      this.props.isCasino
                        ? 'casino-withdrawals-edit'
                        : 'withdrawals-edit',
                    ) && [
                      details.status == '0' && (
                        <button
                          key="button-approve"
                          type="button"
                          className={
                            'm-r-10 btn waves-effect waves-light btn-success'
                          }
                          onClick={() =>
                            this.approveWithdrawal(
                              details.token,
                              details.whitelabel_id,
                            )
                          }
                        >
                          {gettext('Approve')}
                        </button>
                      ),
                      details.status == '0' && (
                        <button
                          key="button-decline"
                          type="button"
                          className="m-r-10 btn waves-effect waves-light btn-danger"
                          onClick={() => {
                            this.declineWithdrawal(
                              details.token,
                              details.whitelabel_id,
                            );
                          }}
                        >
                          {gettext('Decline')}
                        </button>
                      ),
                    ]}
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

WithdrawalDetails.contextType = CrmContext;
export default withRouter(WithdrawalDetails);
