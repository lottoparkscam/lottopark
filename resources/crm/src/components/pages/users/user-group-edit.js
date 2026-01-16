import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import Form from '../../elements/form';

class UserGroupEdit extends React.Component {
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
        prize_payout_percent: '',
        is_selectable_by_user: 0,
      },
      oldGroup: {},
      formWasValidated: false,
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    if (this.props.newGroup) {
    }
    let id = this.props.match.params.id;
    let whitelabel_id = this.context.choosedWhitelabel.id;
    try {
      axios
        .post('/whitelabel_user_groups/details', {
          id,
          whitelabel_id,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let group = {};
            group.name = res.data.group.name;
            group.prize_payout_percent = res.data.group.prize_payout_percent;
            group.is_selectable_by_user = res.data.group.is_selectable_by_user;
            let oldGroup = { ...group };
            this.setState({
              group,
              oldGroup,
            });
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  editGroup(e) {
    e.preventDefault();

    const { fields, group, oldGroup } = this.state;
    let id = this.props.match.params.id;
    let updatedGroup = {};
    Object.keys(fields).forEach((property) => {
      if (group[property] != oldGroup[property]) {
        updatedGroup[property] = group[property];
      }
    });

    try {
      axios
        .post('/whitelabel_user_groups/update', {
          id,
          updatedGroup,
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
          submitForm={this.editGroup.bind(this)}
          redirect={this.redirect.bind(this)}
          formWasValidated={formWasValidated}
          onHandleChange={this.onHandleChange.bind(this)}
          onHandleChangeCheckbox={this.onHandleChangeCheckbox.bind(this)}
        />
      </div>
    );
  }
}

UserGroupEdit.contextType = CrmContext;
export default withRouter(UserGroupEdit);
