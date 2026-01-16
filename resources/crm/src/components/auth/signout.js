import React from 'react';
import CrmContext from '../../helpers/context';
import { history } from '../../helpers/routing';

class SignOut extends React.Component {
  componentDidMount() {
    this.context.signOut();
    history.push('/login');
  }

  render() {
    return null;
  }
}

SignOut.contextType = CrmContext;
export default SignOut;
