import React from 'react';
import { withRouter } from 'react-router-dom';
import axios from '../../../helpers/interceptors';
import toastr from 'toastr';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';

class TransactionDetails extends React.Component {
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
        .post('/crm/transaction/details', {
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
    const { details, type, loading } = this.state;
    let whitelabel = this.context.whitelabels.find(
      (x) => details.whitelabel_id == x.id,
    );

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-lg-12">
          <div className="card">
            <div className="card-body">
              <h3 className="card-title">
                {gettext('Transaction details')}{' '}
                <small className="text-success">{details.full_token}</small>
              </h3>
              <h6 className="card-subtitle">
                {gettext('You can view transaction details here.')}
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
                            <td>{details.user_login}</td>
                          </tr>
                        )}
                        <tr>
                          <td>{gettext('Date')}</td>
                          <td>{details.date}</td>
                        </tr>
                        {details.type == '1' && (
                          <tr>
                            <td>{gettext('Date confirmed')}</td>
                            <td>{details.date_confirmed}</td>
                          </tr>
                        )}
                        <tr>
                          <td>{gettext('Method')}</td>
                          <td>{details.method}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Amount')}</td>
                          <td>{details.amount_display}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Bonus amount')}</td>
                          <td>{details.bonus_amount_display}</td>
                        </tr>
                        <tr key="income">
                          <td>{gettext('Income')}</td>
                          <td>{details.income_display}</td>
                        </tr>
                        <tr key="cost">
                          <td>{gettext('Cost')}</td>
                          <td>{details.cost_display}</td>
                        </tr>
                        <tr key="payment_cost">
                          <td>{gettext('Payment cost')}</td>
                          <td>{details.payment_cost_display}</td>
                        </tr>
                        <tr key="royalties">
                          <td>{gettext('Royalties')}</td>
                          <td>{details.margin_display}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Status')}</td>
                          <td>{details.status_display}</td>
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

TransactionDetails.contextType = CrmContext;
export default withRouter(TransactionDetails);
