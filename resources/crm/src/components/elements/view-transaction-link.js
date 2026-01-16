import React from 'react';
import CrmContext from '../../helpers/context';
import { Link } from 'react-router-dom';

class ViewTransactionLink extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { token } = this.props;
    return (
      this.context.showLink('transactions-view') &&
      token && (
        <div className="users-tooltip">
          <Link
            to={{
              pathname: `/crm/transactions/lottery/view/${token}`,
            }}
          >
            <i className="users-edit mdi mdi-currency-usd m-l-5" />
          </Link>
          <span className="users-tooltiptext">
            {gettext('View transaction')}
          </span>
        </div>
      )
    );
  }
}

ViewTransactionLink.contextType = CrmContext;
export default ViewTransactionLink;
