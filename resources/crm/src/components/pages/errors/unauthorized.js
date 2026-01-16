import React from 'react';
import { history } from '../../../helpers/routing';
import CrmContext from '../../../helpers/context';

class Unauthorized extends React.Component {
  constructor(props) {
    super(props);
  }

  redirect() {
    history.push('/');
  }

  render() {
    const { gettext } = this.context.textdomain;
    return (
      <div className="error-box">
        <div className="error-body text-center">
          <h1 className="error-title">403</h1>
          <h3 className="text-uppercase error-subtitle">
            {gettext('FORBIDDEN ERROR!')}
          </h3>
          <p className="text-muted m-t-30 m-b-30">
            {gettext("YOU DON'T HAVE PERMISSION TO ACCESS ON THIS SERVER.")}
          </p>
          <a
            href=""
            onClick={this.redirect.bind(this)}
            className="btn btn-info btn-rounded waves-effect waves-light m-b-40"
          >
            {gettext('Back to Home')}
          </a>{' '}
        </div>
      </div>
    );
  }
}

Unauthorized.contextType = CrmContext;
export default Unauthorized;
