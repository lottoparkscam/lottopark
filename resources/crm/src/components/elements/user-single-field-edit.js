import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../helpers/context';
import FormInvalid from './form-invalid';
import FormValid from './form-valid';
import axios from '../../helpers/interceptors';

class UserSingleFieldEdit extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      inputType: '',
      field: '',
      name: '',
      title: '',
      id: null,
      value: '',
      whitelabel_id: null,
      prependValue: '',
      formWasValidated: false,
      valueValid: true,
      valueInvalidMessage: '',
      options: [],
    };
  }

  componentDidMount() {
    const { gettext } = this.context.textdomain;
    let field = this.props.match.params.field;
    let id = this.props.match.params.id;
    let name = '';
    let title = '';
    let inputType = '';
    let prependValue = '';

    switch (field) {
      case 'email':
        name = gettext('Email');
        title = gettext('You can edit user email here.');
        this.getUserEmail();
        inputType = 'text';
        break;
      case 'password':
        name = gettext('Password');
        title = gettext('You can edit user password here.');
        this.getUserEmail();
        inputType = 'password';
        break;
      case 'affiliate':
        name = gettext('Affiliate');
        title = gettext('You can change user affiliate here.');
        this.getUserAffiliateEmail();
        inputType = 'select';
        break;
    }
    this.setState({
      inputType,
      field,
      title,
      name,
      id,
      prependValue,
    });
  }

  getUserEmail() {
    let id = this.props.match.params.id;
    try {
      axios.post('/user/email', { id }).then((res) => {
        if (res.data.code == 200) {
          let email = res.data.email;
          let value = email;
          this.setState({ email, value });
        }
      });
    } catch (e) {
      console.log(e);
    }
  }

  getUserAffiliateEmail() {
    let id = this.props.match.params.id;
    try {
      axios.post('/user/affiliate_email', { id }).then((res) => {
        if (res.data.code == 200) {
          let email = res.data.email;
          let value = res.data.affiliate;
          let affiliates = res.data.affiliates;
          let whitelabel_id = res.data.whitelabel_id;
          let options = [];
          if (affiliates.length > 0) {
            options = affiliates.map((affiliate, index) => (
              <option key={index} value={affiliate.id}>
                {affiliate.name || affiliate.surname
                  ? affiliate.name +
                    ' ' +
                    affiliate.surname +
                    ' • ' +
                    affiliate.login
                  : affiliate.login}
              </option>
            ));
          }
          this.setState({ email, value, options, whitelabel_id });
        }
      });
    } catch (e) {
      console.log(e);
    }
  }

  handleSubmit(e) {
    e.preventDefault();
    const { value, field, id, whitelabel_id } = this.state;

    switch (field) {
      case 'email':
        this.sendUpdate('/user/edit/email', { id, email: value });
        break;
      case 'password':
        this.sendUpdate('/user/edit/password', { id, password: value });
        break;
      case 'affiliate':
        this.sendUpdate('/user/edit/affiliate', {
          id,
          affiliate: value,
          whitelabel_id,
        });
        break;
    }
  }

  sendUpdate(url, params) {
    const { field, formWasValidated } = this.state;
    try {
      axios.post(url, params).then((res) => {
        console.log(res);
        if (formWasValidated) {
          this.setState({
            formWasValidated: false,
            valueValid: true,
            valueInvalidMessage: '',
          });
        }
        if (res.data.code == 200) {
          let message = res.data.message;
          this.context.showToast('success', message);
          if (field == 'email') {
            const { value } = this.state;
            this.setState({ email: value });
          }
        } else if (res.data.code == 400) {
          let error = res.data.error;
          let errors = res.data.errors;
          let message = res.data.message;

          if (error) this.setError(error);
          if (errors) {
            if (errors.amount) {
              this.setError(errors.amount);
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

  setError(message) {
    this.setState({
      valueValid: false,
      valueInvalidMessage: message,
      formWasValidated: true,
    });
  }

  onHandleChange(e) {
    let value = e.target.value;
    this.setState({ value });
  }

  redirect() {
    this.props.history.goBack();
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      email,
      inputType,
      title,
      options,
      name,
      value,
      prependValue,
      formWasValidated,
      valueValid,
      valueInvalidMessage,
    } = this.state;

    return (
      <div className="row">
        <div className="col-12">
          <div className="card">
            <form
              className={'form-horizontal needs-validation'}
              onSubmit={this.handleSubmit.bind(this)}
            >
              <div className="card-body">
                <h3 className="card-title">{email}</h3>
                <h6 className="card-subtitle">{title}</h6>
                <div className="row">
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
                </div>
                <div className="row">
                  <div className="col-sm-3">
                    <label className="text-left control-label col-form-label">
                      {name}
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
                      {inputType == 'select' ? (
                        <select
                          onChange={this.onHandleChange.bind(this)}
                          className={
                            'form-control ' +
                            (formWasValidated
                              ? valueValid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          value={value}
                        >
                          <option value="">--</option>
                          {options}
                        </select>
                      ) : (
                        <input
                          type={inputType}
                          className={
                            'form-control ' +
                            (formWasValidated
                              ? valueValid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          value={value}
                          onChange={this.onHandleChange.bind(this)}
                          id="single-input"
                          placeholder={name}
                        />
                      )}
                      {valueValid === true ? (
                        <FormValid />
                      ) : (
                        <FormInvalid message={valueInvalidMessage} />
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

UserSingleFieldEdit.contextType = CrmContext;
export default withRouter(UserSingleFieldEdit);
