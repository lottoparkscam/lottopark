import React from 'react';
import CrmContext from '../../helpers/context';
import DateRangePicker from '@wojtekmaj/react-daterange-picker/dist/entry.nostyle';

class TableFilters extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      columns,
      filterDate,
      filterDrawDate,
      filterFirstDrawDate,
      filterValidToDrawDate,
      filterCurrentDrawDate,
      filterDateConfirmed,
      filterCreatedAt,
      filterAmounts,
      filterList,
      statusOptions,
      methodsOptions,
    } = this.props;

    return columns.map((col) => (
      <td key={'search1' + col.name}>
        {col.name == 'date' ? (
          <DateRangePicker
            onChange={filterDate}
            value={col.filter}
            calendarIcon={null}
          />
        ) : col.name == 'draw_date' ? (
          <DateRangePicker
            onChange={filterDrawDate}
            value={col.filter}
            calendarIcon={null}
          />
        ) : col.name == 'first_draw' ? (
          <DateRangePicker
            onChange={filterFirstDrawDate}
            value={col.filter}
            calendarIcon={null}
          />
        ) : col.name == 'valid_to_draw' ? (
          <DateRangePicker
            onChange={filterValidToDrawDate}
            value={col.filter}
            calendarIcon={null}
          />
        ) : col.name == 'current_draw' ? (
          <DateRangePicker
            onChange={filterCurrentDrawDate}
            value={col.filter}
            calendarIcon={null}
          />
        ) : col.name == 'date_confirmed' ? (
          <DateRangePicker
            onChange={filterDateConfirmed}
            value={col.filter}
            calendarIcon={null}
          />
        ) : col.name == 'created_at' ? (
          <DateRangePicker
            onChange={filterCreatedAt}
            value={col.filter}
            calendarIcon={null}
          />
        ) : col.name == 'status' ? (
          <select onChange={filterList} name="status" value={col.filter}>
            {statusOptions}
          </select>
        ) : col.name == 'method' ? (
          <select onChange={filterList} name="method" value={col.filter}>
            {methodsOptions}
          </select>
        ) : col.name == 'payout' || col.name == 'is_paid_out' ? (
          <select onChange={filterList} name={col.name} value={col.filter}>
            <option value="">--</option>
            <option value="0">{gettext('No')}</option>
            <option value="1">{gettext('Yes')}</option>
          </select>
        ) : col.name == 'amount' ||
          col.name == 'bonus_amount' ||
          col.name == 'prize' ||
          col.name == 'line_count' ||
          col.name == 'prize_payout_percent' ||
          col.name == 'tickets' ||
          col.name == 'user_balance' ? (
          <div>
            {' '}
            <input
              className="input-40"
              type="text"
              name={col.name + '-from'}
              onChange={filterAmounts}
              value={col.filter[0]}
            />
            <span>{gettext(' to ')}</span>
            <input
              className="input-40"
              type="text"
              name={col.name + '-to'}
              onChange={filterAmounts}
              value={col.filter[1]}
            />
          </div>
        ) : (
          col.name !== 'pricing' && (
            <input
              type="text"
              name={col.name}
              onChange={filterList}
              value={col.filter}
            />
          )
        )}
      </td>
    ));
  }
}

TableFilters.contextType = CrmContext;
export default TableFilters;
