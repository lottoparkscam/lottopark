import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';
import { Card, Icon } from 'semantic-ui-react';

class Settings extends React.Component {
  constructor(props) {
    super(props);
  }

  redirect(path) {
    this.props.history.push(path);
  }

  render() {
    const { availableSettings, isLoading } = this.props;

    return isLoading ? (
      <Loading />
    ) : (
      <Card.Group itemsPerRow={3} stackable doubling style={{ marginTop: 20 }}>
        {availableSettings.map((setting, index) => {
          return (
            <Card
              key={index}
              style={{ cursor: 'pointer' }}
              onClick={() => this.redirect(setting.path)}
            >
              <div
                style={{
                  background: 'black',
                  width: '100%',
                  color: 'white',
                  height: 200,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 90,
                }}
              >
                <Icon style={{ height: '20%' }} className={setting.icon}></Icon>
              </div>
              <Card.Content>
                <Card.Header>{setting.name}</Card.Header>
                <Card.Description>{setting.description}</Card.Description>
              </Card.Content>
            </Card>
          );
        })}
      </Card.Group>
    );
  }
}

Settings.contextType = CrmContext;
export default withRouter(Settings);
