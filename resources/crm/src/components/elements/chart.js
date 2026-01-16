import React from 'react';
import Chartist from 'chartist';

class Chart extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      chart: null,
      labels: [],
      series: [],
    };
  }

  componentDidUpdate() {
    let data = { labels: this.props.labels, series: this.props.series };
    let options = this.props.options;

    this.state.chart.update(data, options);
  }
  componentDidMount() {
    const { labels, series, options, element } = this.props;

    var chart = new Chartist.Line(
      `#${element}`,
      {
        labels: labels,
        series: series,
      },
      options,
    );
    chart.on('draw', function (ctx) {
      if (ctx.type === 'area') {
        ctx.element.attr({
          x1: ctx.x1 + 0.001,
        });
      }
    });
    chart.on('created', function (ctx) {
      var defs = ctx.svg.elem('defs');
      defs
        .elem('linearGradient', {
          id: 'gradient',
          x1: 0,
          y1: 2,
          x2: 0,
          y2: 0,
        })
        .elem('stop', {
          offset: 0,
          'stop-color': 'rgba(255, 255, 255, 1)',
        })
        .parent()
        .elem('stop', {
          offset: 1,
          'stop-color': 'rgba(64, 196, 255, 1)',
        });
    });
    this.setState({ chart, options });
  }

  render() {
    const { element } = this.props;
    return <div id={element}></div>;
  }
}

export default Chart;
