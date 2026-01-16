import React from 'react';

class DataWithTooltip extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { title, value, tooltipValue, additionalClass } = this.props;
    return (
      <div className={'m-b-0 ' + (additionalClass ? additionalClass : '')}>
        {title ? title + ': ' + value : value}
        {tooltipValue && (
          <div className="users-tooltip">
            <i className="m-l-5 mdi mdi-information"></i>
            <span className="users-tooltiptext">
              {tooltipValue.split('\n').map((item, i) => (
                <p className="m-b-0" key={i}>
                  {item}
                </p>
              ))}
            </span>
          </div>
        )}
      </div>
    );
  }
}

export default DataWithTooltip;
