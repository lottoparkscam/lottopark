import React from 'react';
import CrmContext from '../../../helpers/context';
import { Sparklines, SparklinesBars } from 'react-sparklines';

class Analytics extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { count, title, data, color } = this.props;
    return (
      <div className="col-lg-3 col-md-6">
        <div className="card">
          <div className="card-body">
            <div className="d-flex align-items-center">
              <div>
                <h3 className="font-light m-b-5">{count}</h3>
                <span className="text-muted">{title}</span>
              </div>
              <div className="ml-auto">
                <div className="analytics-chart">
                  <Sparklines
                    data={data}
                    style={{
                      display: 'inline-block',
                      width: '40px',
                      height: '35px',
                      verticalAlign: 'top',
                    }}
                  >
                    <SparklinesBars style={{ fill: color }} barWidth={15} />
                  </Sparklines>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
Analytics.contextType = CrmContext;
export default Analytics;
