import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../../helpers/context';
import Settings from '../Settings';

class CasinoSettings extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      availableSettings: [],
      isLoading: true,
    };
  }

  componentDidMount() {
    const { gettext } = this.context.textdomain;
    const availableSettings = [
      {
        name: gettext('Games order'),
        icon: 'ordered list',
        path: '/crm/settings/casino/games-order',
        description: gettext('Change order of your casino games.'),
      },
    ];

    this.setState({ availableSettings: availableSettings }, () =>
      this.setState({ isLoading: false }),
    );
  }

  redirect(path) {
    this.props.history.push(path);
  }

  render() {
    const { availableSettings, isLoading } = this.state;

    return (
      <Settings
        availableSettings={availableSettings}
        isLoading={isLoading}
      ></Settings>
    );
  }
}

CasinoSettings.contextType = CrmContext;
export default withRouter(CasinoSettings);
