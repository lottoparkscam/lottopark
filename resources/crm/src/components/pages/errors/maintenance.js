import React from 'react';
import CrmContext from '../../../helpers/context';

class Maintenance extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { gettext } = this.context.textdomain;
    return (
      <div className="error-box">
        <div className="error-body text-center">
          <img src="/assets/images/crm/logo-icon.png" />
          <h4 className="text-dark font-24 m-t-15">
            {gettext('White Lotto CRM')}
          </h4>
          <div className="m-t-30">
            <h3>{gettext('CRM is under maintenance')}</h3>
            <h5 className="text-muted font-medium">
              {gettext('Please check back again.')}
            </h5>
          </div>
        </div>
      </div>
    );
  }
}

Maintenance.contextType = CrmContext;
export default Maintenance;
