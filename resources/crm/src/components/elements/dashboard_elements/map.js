import React from 'react';
import { VectorMap } from 'react-jvectormap';

class Map extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const data = [...this.props.data];
    let notSpecifiedIndex = data.findIndex((x) => !x.coordinates);
    if (notSpecifiedIndex > -1) data.splice(notSpecifiedIndex, 1);
    let markers = data.map((a) => {
      return {
        latLng: a.coordinates,
        name: a.country_name + ': ' + a.amountDisplay,
        style: {
          fill: '#2961ff',
          opacity: '0.7',
        },
      };
    });
    let mapMarkersValues = data.map((a) => parseInt(a.amount));

    return (
      <div>
        <VectorMap
          map={'world_mill'}
          backgroundColor="transparent"
          zoomOnScroll={false}
          color={'rgba(223,226,233, 0.8)'}
          containerStyle={{
            width: '100%',
            height: '520px',
          }}
          containerClassName="map"
          regionStyle={{
            initial: {
              fill: 'rgba(223,226,233, 0.8)',
              'stroke-width': 1,
              stroke: 'rgba(223,226,233, 0.8)',
            },
          }}
          markerStyle={{
            initial: {
              r: 5,
              fill: '#dfe2e9',
              'fill-opacity': 0.8,
              stroke: '#dfe2e9',
              'stroke-width': 1,
              'stroke-opacity': 0.8,
            },
          }}
          series={{
            markers: [
              {
                attribute: 'r',
                scale: [5, 10],
                values: mapMarkersValues,
              },
            ],
          }}
          markers={markers}
          hoverOpacity={null}
          normalizeFunction={'linear'}
          scaleColors={['#93d5ed', '#93d5ee']}
          selectedColor={'#cbd0db'}
          selectedRegions={[]}
          showTooltip={true}
        />
      </div>
    );
  }
}
export default Map;
