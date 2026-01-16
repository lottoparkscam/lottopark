import React from 'react';

class CountryStats extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { label, countryName, ticketsCount, amount, percent } = this.props;

    return (
      <div className="col-sm-6 col-md">
        <div className="mb-2 mt-2">
          <span className={'label label-rounded ' + label}>
            {percent > 0 && '+ '}
            {percent + '%'}
          </span>
          <h5 className="font-normal text-muted m-t-10 m-b-5">{countryName}</h5>
          <span className="font-20 font-medium">
            {ticketsCount}{' '}
            <span className="font-14 font-normal text-muted">
              {'(' + amount + ')'}
            </span>
          </span>
        </div>
      </div>
    );
  }
}
export default CountryStats;
