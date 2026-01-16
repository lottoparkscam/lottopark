import React from 'react';
import axios from '../../helpers/interceptors';
import { history } from '../../helpers/routing';
import FormInvalid from '../elements/form-invalid';
import CrmContext from '../../helpers/context';

var bgStyle = {
  backgroundImage: "url('/assets/images/crm/auth-bg.jpg')",
  backgroundPosition: 'center',
  minHeight: '100vh',
  backgroundColor: '#f0f5f9',
  backgroundRepeat: 'no-repeat',
};

class LoginForm extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      error: false,
      errorMessage: '',
    };
  }

  componentDidMount() {
    const { gettext } = this.context.textdomain;
    document.title = gettext('Sign In - White Lotto CRM');
  }

  signIn(e) {
    e.preventDefault();

    let username = this.refs.username.value;
    let password = this.refs.password.value;
    let remember = this.refs.remember.checked;

    axios
      .post('/authenticate', {
        username: username,
        password: password,
      })
      .then((res) => {
        if (res.data.code == 400) {
          this.setState({
            error: true,
            errorMessage: res.data.message,
          });
        }
        if (res.data.code == 200) {
          let token = res.data.token;
          let user = res.data.user;
          this.props.handleLogIn(remember, user, token);
          history.push('/');
        }
      });
  }

  changeState() {
    if (this.state.error) {
      this.setState({
        error: false,
        errorMessage: '',
      });
    }
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { error } = this.state;

    return (
      <div>
        <div className="main-wrapper">
          <div
            className="auth-wrapper d-flex no-block justify-content-center align-items-center"
            style={bgStyle}
          >
            <div className="auth-box">
              <div id="loginform">
                <div className="logo">
                  <span className="db">
                    <img src="/assets/images/crm/logo-icon.png" alt="logo" />
                  </span>
                  <h5 className="font-medium m-b-20">
                    {gettext('Sign In to Admin')}
                  </h5>
                </div>
                <div className="row">
                  <div className="col-12">
                    {error && <FormInvalid message={this.state.errorMessage} />}
                    <form
                      className="form-horizontal m-t-20"
                      id="loginform"
                      onSubmit={this.signIn.bind(this)}
                    >
                      <div className="input-group mb-3">
                        <div className="input-group-prepend">
                          <span className="input-group-text" id="basic-addon1">
                            <i className="ti-user"></i>
                          </span>
                        </div>
                        <input
                          type="text"
                          className={
                            'form-control form-control-lg ' +
                            (error && 'is-invalid')
                          }
                          onChange={this.changeState.bind(this)}
                          placeholder={gettext('Username')}
                          aria-label={gettext('Username')}
                          aria-describedby="basic-addon1"
                          ref="username"
                        />
                      </div>
                      <div className="input-group mb-3">
                        <div className="input-group-prepend">
                          <span className="input-group-text" id="basic-addon2">
                            <i className="ti-pencil"></i>
                          </span>
                        </div>
                        <input
                          type="password"
                          className={
                            'form-control form-control-lg ' +
                            (error && 'is-invalid')
                          }
                          onChange={this.changeState.bind(this)}
                          placeholder={gettext('Password')}
                          aria-label={gettext('Password')}
                          aria-describedby="basic-addon1"
                          ref="password"
                        />
                      </div>
                      <div className="form-group row">
                        <div className="col-md-12">
                          <div className="custom-control custom-checkbox">
                            <input
                              type="checkbox"
                              className="custom-control-input"
                              id="customCheck1"
                              ref="remember"
                            />
                            <label
                              className="custom-control-label"
                              htmlFor="customCheck1"
                            >
                              {gettext('Remember me')}
                            </label>
                          </div>
                        </div>
                      </div>
                      <div className="form-group text-center">
                        <div className="col-xs-12 p-b-20">
                          <button
                            className="btn btn-block btn-lg btn-info"
                            type="submit"
                          >
                            {gettext('Sign In')}
                          </button>
                        </div>
                      </div>
                    </form>
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

LoginForm.contextType = CrmContext;
export default LoginForm;
