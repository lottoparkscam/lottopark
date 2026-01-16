import React from 'react';
import CrmContext from '../../helpers/context';
import DonutChart from './donutChart';
import PerfectScrollbar from 'react-perfect-scrollbar';
import 'react-perfect-scrollbar/dist/css/styles.css';

class DonutWithTable extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { sum, dataTable, element, headers } = this.props;

    let data = [['', 1]];

    if (dataTable.length > 0) {
      let othersCount = sum;
      if (dataTable.length >= 3) {
        othersCount =
          sum - dataTable[0].count - dataTable[1].count - dataTable[2].count;

        data = [
          [dataTable[0].name, dataTable[0].count],
          [dataTable[1].name, dataTable[1].count],
          [dataTable[2].name, dataTable[2].count],
          [gettext('Others'), othersCount],
        ];
      } else if (dataTable.length > 0) {
        data = [];
        dataTable.forEach((item) => {
          othersCount -= item.count;
          data.push([item.name, item.count]);
        });
      }
    }

    return (
      <div className="donut-with-table">
        <div className="m-t-10 c3 donut-chart">
          <DonutChart columns={data} element={element} />
        </div>
        <div className="row text-center m-b-30">
          {dataTable[0] && (
            <div className="col">
              <h4 className="m-b-0 font-medium">
                {((dataTable[0].count / sum) * 100).toFixed(2) + '%'}
              </h4>
              <span className="text-muted">
                {dataTable[0].name}({dataTable[0].count})
              </span>
            </div>
          )}
          {dataTable[1] && (
            <div className="col">
              <h4 className="m-b-0 font-medium">
                {((dataTable[1].count / sum) * 100).toFixed(2) + '%'}
              </h4>
              <span className="text-muted">
                {dataTable[1].name}({dataTable[1].count})
              </span>
            </div>
          )}
          {dataTable[2] && (
            <div className="col">
              <h4 className="m-b-0 font-medium">
                {((dataTable[2].count / sum) * 100).toFixed(2) + '%'}
              </h4>
              <span className="text-muted">
                {dataTable[2].name}({dataTable[2].count})
              </span>
            </div>
          )}
        </div>
        <PerfectScrollbar>
          <div className="table-responsive">
            <table className="table table-hover">
              <thead>
                <tr>
                  <th>{headers[0]}</th>
                  <th>{headers[1]}</th>
                  <th>{headers[2]}</th>
                  <th>{headers[3]}</th>
                </tr>
              </thead>
              <tbody>
                {dataTable.length > 0 &&
                  dataTable.map((item, index) => (
                    <tr key={index}>
                      <td className="txt-oflo">{item.name}</td>
                      <td className="txt-oflo">
                        {Math.round((item.count / sum) * 100 * 100) / 100 + '%'}
                      </td>
                      <td>
                        <span className="font-medium">{item.count}</span>
                      </td>
                      <td>
                        <span className="font-medium">
                          {item.amountSumDisplay}
                        </span>
                      </td>
                    </tr>
                  ))}
              </tbody>
            </table>
          </div>
        </PerfectScrollbar>
      </div>
    );
  }
}

DonutWithTable.contextType = CrmContext;
export default DonutWithTable;
