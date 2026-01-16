import React from 'react';
import CrmContext from '../../helpers/context';
import { Link } from 'react-router-dom';

class ViewUserLink extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { token } = this.props;
    return (
      this.context.showLink('users-view') && (
        <div className="users-tooltip">
          <Link
            to={{
              pathname: `/whitelabel/users/view/${token}`,
            }}
          >
            <i className="users-edit mdi mdi-account m-l-5" />
          </Link>
          <span className="users-tooltiptext">{gettext('View user')}</span>
        </div>
      )
    );
  }
}

ViewUserLink.contextType = CrmContext;
export default ViewUserLink;
