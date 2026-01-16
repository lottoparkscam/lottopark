import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Form from '../../elements/form';

class NewUserGroup extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      fields: {
        name: { type: 'text', title: 'Name', valid: true, invalidMessage: '' },
        prize_payout_percent: {
          type: 'text',
          title: 'Prize payout percent',
          valid: true,
          invalidMessage: '',
        },
        is_selectable_by_user: {
          type: 'checkbox',
          title: 'Is selectable by user',
          valid: true,
          invalidMessage: '',
        },
      },
      group: {
        name: '',
        prize_payout_percent: 100,
        is_selectable_by_user: 0,
      },
      formWasValidated: false,
    };
  }

  componentDidMount() {
    this.context.verifyToken();
  }

  saveGroup(e) {
    e.preventDefault();

    const { group } = this.state;
    const { id } = this.context.choosedWhitelabel;

    try {
      axios
        .post('/whitelabel_user_groups/new', {
          id,
          group,
        })
        .then((res) => {
          if (res) {
            if (res.data.code == 400) {
              let errors = res.data.errors;
              this.setErrors(errors);
            }
            if (res.data.code == 200) {
              let message = res.data.message;
              this.context.showToast('success', message);
              this.redirect();
            }
          }
        });
    } catch (e) {
      console.log('e ' + e);
    }
  }

  redirect() {
    const { history } = this.props;
    history.goBack();
  }

  onHandleChange(e) {
    let group = this.state.group;
    group[e.target.name] = e.target.value;
    this.setState({
      group,
      [e.target.name + 'Valid']: true,
      [e.target.name + 'InvalidMessage']: '',
    });
  }

  onHandleChangeCheckbox(e) {
    let group = this.state.group;
    group.is_selectable_by_user = e.target.checked ? 1 : 0;
    this.setState({ group });
  }

  setErrors(errors) {
    Object.keys(errors).forEach((field) => {
      let fields = this.state.fields;
      fields[field].valid = false;
      fields[field].invalidMessage = errors[field];
      this.setState({
        fields,
        formWasValidated: true,
      });
    });
  }

  render() {
    const { formWasValidated, group, fields } = this.state;
    const { gettext } = this.context.textdomain;
    return (
      <div className="row">
        <Form
          title={gettext('Group Data')}
          object={group}
          fields={fields}
          submitForm={this.saveGroup.bind(this)}
          redirect={this.redirect.bind(this)}
          formWasValidated={formWasValidated}
          onHandleChange={this.onHandleChange.bind(this)}
          onHandleChangeCheckbox={this.onHandleChangeCheckbox.bind(this)}
        />
      </div>
    );
  }
}

NewUserGroup.contextType = CrmContext;
export default withRouter(NewUserGroup);
