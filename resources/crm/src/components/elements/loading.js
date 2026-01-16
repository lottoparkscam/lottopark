import React from 'react';

class Loading extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div className="preloader">
        <div className="lds-ripple">
          <img src="/assets/images/crm/loading.gif" />
        </div>
      </div>
    );
  }
}
export default Loading;
