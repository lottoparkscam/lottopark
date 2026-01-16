import React from 'react';
import CrmContext from '../../helpers/context';

class TableLengthSelect extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { handleChange, itemsPerPage } = this.props;
    return (
      <div className="users-view-table-length">
        <label>
          {gettext('Show') + ' '}
          <select
            className="form-control form-control-sm"
            onChange={handleChange}
            name="table_length"
            value={itemsPerPage}
          >
            <option value="50">{gettext('50')}</option>
            <option value="100">{gettext('100')}</option>
            <option value="200">{gettext('200')}</option>
            <option value="500">{gettext('500')}</option>
          </select>
          {' ' + gettext('entries')}
        </label>
      </div>
    );
  }
}

TableLengthSelect.contextType = CrmContext;
export default TableLengthSelect;
