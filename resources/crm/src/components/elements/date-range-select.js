import React from 'react';
import CrmContext from '../../helpers/context';
import DateRangePicker from '@wojtekmaj/react-daterange-picker/dist/entry.nostyle';

class DateRangeSelect extends React.Component {
  constructor(props) {
    super(props);

    this.timeout = null;

    this.state = {
      loading: true,
    };
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      date,
      showDatePicker,
      onDateChange,
      selectDateValue,
      selectedRange,
      name,
      customClass,
    } = this.props;
    return (
      <div className={customClass ? customClass : 'ml-auto'}>
        <div className="dl m-b-10 m-r-10">
          {showDatePicker ? (
            <DateRangePicker onChange={onDateChange} value={date} />
          ) : (
            <select
              name={name}
              className="custom-select border-0 text-muted"
              value={selectDateValue}
              onChange={selectedRange}
            >
              <option value="30days">{gettext('Last 30 days')}</option>
              <option value="month">{gettext('This month')}</option>
              <option value="lastmonth">{gettext('Last month')}</option>
              <option value="year">{gettext('This year')}</option>
              <option value="range">{gettext('Date range')}</option>
            </select>
          )}
        </div>
      </div>
    );
  }
}

DateRangeSelect.contextType = CrmContext;
export default DateRangeSelect;
