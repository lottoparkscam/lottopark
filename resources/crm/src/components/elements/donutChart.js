import React from 'react';
import c3 from 'c3';

class DonutChart extends React.Component {
  constructor(props) {
    super(props);
  }

  renderChart() {
    let colorsPattern = ['#137eff', '#8b5edd', '#5ac146', '#eceff1'];
    if (this.props.colors) {
      colorsPattern = this.props.colors;
    }
    c3.generate({
      bindto: '#' + this.props.element,
      data: {
        columns: this.props.columns,

        type: 'donut',
        tooltip: {
          show: true,
        },
      },
      donut: {
        label: {
          show: false,
        },
        width: 15,
      },

      legend: {
        hide: true,
      },
      color: {
        pattern: colorsPattern,
      },
    });
  }

  componentDidMount() {
    this.renderChart();
  }

  componentDidUpdate() {
    this.renderChart();
  }

  render() {
    return (
      <div>
        <div className="c3" id={this.props.element}></div>
      </div>
    );
  }
}

export default DonutChart;
