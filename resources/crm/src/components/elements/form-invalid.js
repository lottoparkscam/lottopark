import React from 'react';

class FormInvalid extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { message } = this.props;
    return <div className="invalid-feedback">{message}</div>;
  }
}

export default FormInvalid;
