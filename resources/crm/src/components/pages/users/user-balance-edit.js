import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Loading from '../../elements/loading';
import FormValid from '../../elements/form-valid';
import FormInvalid from '../../elements/form-invalid';

const API_EDIT_BALANCE_URL = '/user/edit/balance';

class UserBalanceEdit extends Component {
  constructor(props) {
    super(props);

    this.state = {
      id: null,
      token: null,
      loading: true,
      balanceValueValid: false,
      balanceValueInvalidMessage: '',
      formWasValidated: false,
      methodValid: false,
      methodInvalidMessage: '',
      email: '',
      balance: 0,
      prependValue: '$',
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    let { id: token } = this.props.match.params;

    this.setState({ token });

    this.getUserBalanceEmail();
  }

  handleSubmit(e) {
    e.preventDefault();
    this.setState({ loading: true });

    const { id, balance, formWasValidated } = this.state;
    const { isBonus, isCasino } = this.props;

    try {
      axios
        .post(API_EDIT_BALANCE_URL, { id, balance, isBonus, isCasino })
        .then((res) => {
          // console.log(res);
          this.setState({ loading: false });

          if (formWasValidated) {
            this.setState({
              methodValid: true,
              methodInvalidMessage: '',
              formWasValidated: false,
              balanceValueValid: false,
              balanceValueInvalidMessage: '',
              loading: false,
            });
          }
          if (res.data.code == 200) {
            let message = res.data.message;
            this.context.showToast('success', message);
          } else if (res.data.code == 400) {
            let error = res.data.error;
            let errors = res.data.errors;
            let message = res.data.message;

            if (error) this.setError(error);
            if (errors) {
              if (errors.amount) {
                this.setError(errors.amount);
              }
              if (errors.method) {
                this.setMethodError(errors.method);
              }
            }
            if (message) {
              this.context.showToast('error', message);
            }
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  onHandleChange(e) {
    let balance = e.target.value;
    this.setState({ balance });
  }

  setError(message) {
    this.setState({
      balanceValueValid: false,
      balanceValueInvalidMessage: message,
      formWasValidated: true,
    });
  }

  setMethodError(message) {
    this.setState({
      methodValid: false,
      methodInvalidMessage: message,
      formWasValidated: true,
    });
  }

  redirect() {
    this.props.history.goBack();
  }

  getUserBalanceEmail() {
    let id = this.props.match.params.id;
    const { isBonus, isCasino } = this.props;

    try {
      axios
        .post('/user/balance_email', { id, isBonus, isCasino })
        .then((res) => {
          if (res.data.code == 200) {
            let email = res.data.email;
            let balance = res.data.balance;

            if (isBonus) {
              balance = res.data.bonus_balance;
            }

            if (isCasino) {
              balance = res.data.casino_balance;
            }

            let prependValue = res.data.currency;
            this.setState({
              id,
              email,
              balance,
              prependValue,
              loading: false,
            });
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  render() {
    const { gettext } = this.context.textdomain;

    const {
      loading,
      formWasValidated,
      balanceValueValid,
      balanceValueInvalidMessage,
      email,
      prependValue,
      balance,
    } = this.state;

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-lg-12">
          <div className="card">
            <form
              className={'form-horizontal needs-validation'}
              onSubmit={this.handleSubmit.bind(this)}
            >
              <div className="card-body">
                <h3 className="card-title">{email}</h3>
                <h6 className="card-subtitle">
                  {gettext('You can edit user balance here.')}
                </h6>
                <div className="col-sm-12">
                  <p>
                    <button
                      type="button"
                      className="btn btn-primary btn-rounded"
                      onClick={this.redirect.bind(this)}
                    >
                      {gettext('Go Back')}
                    </button>
                  </p>
                </div>
                <div className="row">
                  <div className="col-sm-3">
                    <label className="text-left control-label col-form-label">
                      {gettext('Balance')}
                    </label>
                  </div>
                  <div className="col-sm-9">
                    <div className="input-group mb-3">
                      {prependValue !== '' && (
                        <div className="input-group-prepend">
                          <label
                            className="input-group-text"
                            htmlFor="single-input"
                          >
                            {prependValue}
                          </label>
                        </div>
                      )}

                      <input
                        type="text"
                        className={
                          'form-control ' +
                          (formWasValidated
                            ? balanceValueValid
                              ? 'is-valid'
                              : 'is-invalid'
                            : '')
                        }
                        value={balance}
                        onChange={this.onHandleChange.bind(this)}
                        id="single-input"
                        placeholder={gettext('Balance')}
                      />

                      {balanceValueValid === true ? (
                        <FormValid />
                      ) : (
                        <FormInvalid message={balanceValueInvalidMessage} />
                      )}
                    </div>
                  </div>
                </div>
                <div className="form-group m-b-0 text-right">
                  <button
                    type="submit"
                    className="btn btn-info waves-effect waves-light btn-save"
                  >
                    {gettext('Save')}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    );
  }
}

UserBalanceEdit.contextType = CrmContext;
export default withRouter(UserBalanceEdit);
