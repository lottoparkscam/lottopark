import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Loading from '../../elements/loading';
import FormValid from '../../elements/form-valid';
import FormInvalid from '../../elements/form-invalid';

const API_ADD_MANUAL_DEPOSIT_URL = '/user/manual_deposit';

class UserManualDepositAdd extends Component {
  constructor(props) {
    super(props);

    this.state = {
      id: null,
      token: null,
      loading: true,
      options: [],
      method: '',
      methodName: '',
      formWasValidated: false,
      methodValid: false,
      methodInvalidMessage: '',
      amountValueValid: false,
      amountValueInvalidMessage: '',
      email: '',
      amount: 0,
      prependValue: '$',
    };
  }

  componentDidMount() {
    this.context.verifyToken();

    let { id: token } = this.props.match.params;

    this.setState({ token });

    this.getUserEmailCurrencyMethods();
  }

  handleSubmit(e) {
    e.preventDefault();
    this.setState({ loading: true });

    const { id, method, methodName, amount, formWasValidated } = this.state;
    const { isBonus, isCasino } = this.props;

    try {
      axios
        .post(API_ADD_MANUAL_DEPOSIT_URL, {
          id,
          method,
          methodName,
          amount,
          isBonus,
          isCasino,
        })
        .then((res) => {
          // console.log(res);
          this.setState({ loading: false });

          if (formWasValidated) {
            this.setState({
              methodValid: true,
              methodInvalidMessage: '',
              formWasValidated: false,
              amountValueValid: false,
              amountValueInvalidMessage: '',
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
    let amount = e.target.value;
    this.setState({ amount });
  }

  selectMethod(e) {
    let method = e.target.value;
    let methodName = e.target.options[e.target.selectedIndex].text;
    this.setState({ method, methodName });
  }

  setError(message) {
    this.setState({
      amountValueValid: false,
      amountValueInvalidMessage: message,
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
    this.props.history.push('/whitelabel/users');
  }

  getUserEmailCurrencyMethods() {
    let id = this.props.match.params.id;
    const { isBonus, isCasino } = this.props;

    try {
      axios
        .post('/user/email_currency_methods', { id, isBonus, isCasino })
        .then((res) => {
          // console.log(res);
          if (res.data.code == 200) {
            let email = res.data.email;
            let prependValue = res.data.currency;
            let methods = res.data.methods;
            let options = [];
            if (methods.length > 0) {
              options = methods.map((method, index) => (
                <option key={index} value={method.id}>
                  {method.name}
                </option>
              ));
            }
            this.setState({
              id,
              email,
              prependValue,
              options,
              methods,
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
      method,
      methodValid,
      methodInvalidMessage,
      amountValueValid,
      amountValueInvalidMessage,
      email,
      options,
      prependValue,
      amount,
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
                  {gettext('Here you can add manual deposit to the user.')}
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
                      {gettext('Method')}
                    </label>
                  </div>
                  <div className="col-sm-9">
                    <div className="input-group mb-3">
                      <select
                        onChange={this.selectMethod.bind(this)}
                        className="form-control"
                        value={method}
                      >
                        <option value="">--</option>
                        {options}
                      </select>
                      {methodValid === true ? (
                        <FormValid />
                      ) : (
                        <FormInvalid message={methodInvalidMessage} />
                      )}
                    </div>
                  </div>
                </div>
                <div className="row">
                  <div className="col-sm-3">
                    <label className="text-left control-label col-form-label">
                      {gettext('Amount')}
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
                            ? amountValueValid
                              ? 'is-valid'
                              : 'is-invalid'
                            : '')
                        }
                        value={amount}
                        onChange={this.onHandleChange.bind(this)}
                        id="single-input"
                        placeholder={gettext('Amount')}
                      />

                      {amountValueValid === true ? (
                        <FormValid />
                      ) : (
                        <FormInvalid message={amountValueInvalidMessage} />
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

UserManualDepositAdd.contextType = CrmContext;
export default withRouter(UserManualDepositAdd);
