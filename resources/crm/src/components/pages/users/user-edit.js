import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import FormValid from '../../elements/form-valid';
import FormInvalid from '../../elements/form-invalid';
import DatePicker from 'react-date-picker';
import axios from '../../../helpers/interceptors';
import Multiselect from 'multiselect-react-dropdown';
import Loading from '../../elements/loading';

class UserEdit extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      token: null,
      loading: true,
      countries: [],
      timezones: [],
      regions: [],
      prefixes: [],
      groups: [],
      userGroups: [],
      fields: {
        name: { title: 'First name', valid: true, invalidMessage: '' },
        surname: { title: 'Last name', valid: true, invalidMessage: '' },
        country: { title: 'Country', valid: true, invalidMessage: '' },
        city: { title: 'City', valid: true, invalidMessage: '' },
        state: { title: 'Region', valid: true, invalidMessage: '' },
        zip: { title: 'Postal/ZIP Code', valid: true, invalidMessage: '' },
        address_1: { title: 'Address #1', valid: true, invalidMessage: '' },
        address_2: { title: 'Address #2', valid: true, invalidMessage: '' },
        birthdate: { title: 'Birthdate', valid: true, invalidMessage: '' },
        phone_country: {
          title: 'Phone prefix',
          valid: true,
          invalidMessage: '',
        },
        phone: { title: 'Phone', valid: true, invalidMessage: '' },
        timezone: { title: 'Timezone', valid: true, invalidMessage: '' },
        gender: { title: 'Gender', valid: true, invalidMessage: '' },
        national_id: { title: 'National ID', valid: true, invalidMessage: '' },
        prize_payout_whitelabel_user_group_id: {
          title: 'Prize payout group',
          valid: true,
          invalidMessage: '',
        },
      },
      user: {
        name: '',
        surname: '',
        country: '',
        city: '',
        state: '',
        zip: '',
        address_1: '',
        address_2: '',
        birthdate: '',
        phone_country: '',
        phone: '',
        timezone: '',
        gender: '',
        national_id: '',
        prize_payout_whitelabel_user_group_id: '',
      },
      oldUser: {},
      formWasValidated: false,
      selectedGroups: [],
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    let token = this.props.match.params.id;
    try {
      axios
        .post('/user/countries_timezones', {
          token,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let countries = res.data.countries;
            let timezones = res.data.timezones;
            let prefixes = res.data.prefixes;
            let regions = res.data.regions;

            this.setState(
              {
                token,
                countries,
                timezones,
                prefixes,
                regions,
              },
              () => this.fetchUserDetails(),
            );
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  fetchUserDetails() {
    let token = this.state.token;
    try {
      axios
        .post('/user/edit_details', {
          token,
        })
        .then((res) => {
          if (res.data.code == 200) {
            let user = res.data.user;
            if (!user.prize_payout_whitelabel_user_group_id)
              user.prize_payout_whitelabel_user_group_id = '';
            let oldUser = { ...user };
            let groups = res.data.groups;
            let userGroups = res.data.user_groups;
            let selectedGroups = [];
            userGroups.forEach((group) => {
              selectedGroups.push({ id: group.id, name: group.name });
            });
            if (user.birthdate) {
              user.birthdate = new Date(user.birthdate);
            }
            this.setState({
              user,
              oldUser,
              groups,
              userGroups,
              selectedGroups,
              loading: false,
            });
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  onHandleChange(e) {
    let user = this.state.user;
    user[e.target.name] = e.target.value;
    this.setState({ user });
    if (e.target.name == 'country') {
      try {
        axios.post('/user/regions', { country: e.target.value }).then((res) => {
          let regions = res.data.regions;
          this.setState({ regions });
        });
      } catch (e) {
        console.log(e);
      }
    }
  }

  handleSubmit(e) {
    e.preventDefault();
    this.setState({ loading: true });

    const { fields, user, oldUser } = this.state;
    const { gettext } = this.context.textdomain;

    let token = this.props.match.params.id;
    let updatedUser = {};
    Object.keys(fields).forEach((property) => {
      if (user[property] != oldUser[property]) {
        updatedUser[property] = user[property];
      }
    });
    if (updatedUser.prize_payout_whitelabel_user_group_id == '')
      updatedUser.prize_payout_whitelabel_user_group_id = null;
    let selectedGroups = this.state.selectedGroups;
    if (selectedGroups.indexOf(0) > -1) {
      selectedGroups = [];
    }
    try {
      axios
        .post('/update/user', {
          token,
          updatedUser,
          selectedGroups,
        })
        .then((res) => {
          let message = res.data.message;
          if (res.data.code == 400) {
            this.context.showToast('error', message);
            let errors = res.data.errors;
            this.setErrors(errors);
          } else if (res.data.code == 404) {
            this.context.showToast('error', message);
            this.redirect();
          } else if (res.data.code == 200) {
            let fields = this.state.fields;
            let user = this.state.user;
            let oldUser = { ...user };

            Object.keys(fields).forEach((field) => {
              fields[field].valid = true;
              fields[field].invalidMessage = '';
            });
            this.setState(
              {
                formWasValidated: false,
                fields,
                oldUser,
              },
              () => this.fetchUserDetails(),
            );
            this.context.showToast('success', message);
          }
        });
    } catch (e) {
      console.log(e);
    }
  }

  setErrors(errors) {
    Object.keys(errors).forEach((field) => {
      let fields = this.state.fields;
      fields[field].valid = false;
      fields[field].invalidMessage = errors[field];
      this.setState({
        fields,
        formWasValidated: true,
        loading: false,
      });
    });
  }

  redirect() {
    this.props.history.goBack();
  }

  setBirthdate = (birthdate) => {
    this.setState((prevState) => ({
      user: {
        ...prevState.user,
        birthdate,
      },
    }));
  };

  setSelectedGroups(selectedGroups) {
    let user = this.state.user;

    if (selectedGroups.indexOf(0) > -1) {
      selectedGroups = [0];
      user.prize_payout_whitelabel_user_group_id = '';
      this.setState({ selectedGroups, user });
    } else {
      const groups = this.state.groups;
      let newUserGroups = [];

      selectedGroups.forEach((group) => {
        let groupObj = groups.find((x) => x.id == group.id);
        newUserGroups.push({ id: groupObj.id, name: groupObj.name });
      });

      let findUserGroup = newUserGroups.find(
        (group) => group.id === user.prize_payout_whitelabel_user_group_id,
      );
      if (!findUserGroup && newUserGroups.length > 0) {
        user.prize_payout_whitelabel_user_group_id = newUserGroups[0].id;
      }

      this.setState({ selectedGroups, userGroups: newUserGroups, user });
    }
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      loading,
      user,
      fields,
      formWasValidated,
      countries,
      timezones,
      prefixes,
      regions,
      groups,
      userGroups,
      selectedGroups,
    } = this.state;

    let countriesOptions;
    if (Object.keys(countries).length > 0) {
      countriesOptions = Object.keys(countries).map((code, index) => (
        <option key={'country' + index} value={code}>
          {countries[code]}
        </option>
      ));
    }
    let timezonesOptions;
    if (Object.keys(countries).length > 0) {
      timezonesOptions = Object.keys(timezones).map((timezone, index) => (
        <option key={'timezone' + index} value={timezone}>
          {timezones[timezone]}
        </option>
      ));
    }
    let regionsOptions;
    if (Object.keys(regions).length > 0) {
      regionsOptions = Object.keys(regions).map((region, index) => (
        <option key={'region' + index} value={region}>
          {regions[region][2] + ' - ' + regions[region][1]}
        </option>
      ));
    }
    let prefixesOptions;
    if (Object.keys(countries).length > 0) {
      prefixesOptions = Object.keys(countries).map(
        (country, index) =>
          prefixes[country] &&
          prefixes[country].map((prefix, i) => (
            <option key={'prefix' + index + i} value={country}>
              {countries[country] + ' (+ ' + prefix + ')'}
            </option>
          )),
      );
    }
    let userGroupsOptions;
    if (userGroups.length > 0) {
      userGroupsOptions = userGroups.map((group, index) => (
        <option key={'user-group' + index} value={group.id}>
          {group.name}
        </option>
      ));
    }
    let groupsOptions = [{ name: gettext('None'), id: 0 }];
    groups.forEach((group) => {
      groupsOptions.push({
        name: group.name,
        id: group.id,
      });
    });

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-lg-12">
          <div className="card">
            <form
              className={'form-horizontal needs-validation'}
              onSubmit={this.handleSubmit.bind(this)}
            >
              <div className="card-body">
                <h3 className="card-title">{user.email}</h3>
                <h6 className="card-subtitle">
                  {gettext('You can edit user details here.')}
                </h6>
                <div className="row">
                  <div className="col-sm-12">
                    <p>
                      <button
                        type="button"
                        className="btn btn-primary btn-rounded"
                        onClick={this.redirect.bind(this)}
                      >
                        {gettext('Go Back')}
                      </button>
                    </p>
                  </div>
                </div>
                {Object.keys(fields).map((field) => (
                  <div className="form-group row p-b-15" key={field}>
                    <label
                      htmlFor={'form-' + field}
                      className="col-sm-3 text-left control-label col-form-label"
                    >
                      {fields[field].title}
                    </label>
                    <div className="col-sm-9">
                      {field == 'gender' ||
                      field == 'country' ||
                      field == 'timezone' ||
                      field == 'phone_country' ||
                      field == 'state' ||
                      field == 'prize_payout_whitelabel_user_group_id' ? (
                        <select
                          className={
                            'form-control ' +
                            (formWasValidated
                              ? fields[field].valid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          onChange={this.onHandleChange.bind(this)}
                          id={'form-' + field}
                          name={field}
                          key={field}
                          value={user[field]}
                        >
                          {field == 'gender'
                            ? [
                                <option value="" key={'no-gender'}>
                                  --
                                </option>,
                                <option value="1" key={'male-gender'}>
                                  {gettext('Male')}
                                </option>,
                                <option value="2" key={'female-gender'}>
                                  {gettext('Female')}
                                </option>,
                              ]
                            : field == 'country'
                            ? [
                                <option value="" key={'no-country'}>
                                  --
                                </option>,
                                countriesOptions,
                              ]
                            : field == 'timezone'
                            ? [
                                <option value="" key={'no-timezone'}>
                                  --
                                </option>,
                                timezonesOptions,
                              ]
                            : field == 'phone_country'
                            ? [
                                <option value="" key={'no-phone-country'}>
                                  --
                                </option>,
                                prefixesOptions,
                              ]
                            : field == 'state'
                            ? [
                                <option value="" key={'no-state'}>
                                  --
                                </option>,
                                regionsOptions,
                              ]
                            : field == 'prize_payout_whitelabel_user_group_id'
                            ? [
                                <option
                                  value=""
                                  key={'no-user-group'}
                                  disabled={'disabled'}
                                >
                                  --
                                </option>,
                                userGroupsOptions,
                              ]
                            : ''}
                        </select>
                      ) : field == 'birthdate' ? (
                        <DatePicker
                          className={
                            'form-control ' +
                            (formWasValidated
                              ? fields[field].valid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          calendarIcon={null}
                          onChange={this.setBirthdate}
                          value={user.birthdate}
                        />
                      ) : (
                        <input
                          type="text"
                          className={
                            'form-control ' +
                            (formWasValidated
                              ? fields[field].valid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          value={user[field]}
                          onChange={this.onHandleChange.bind(this)}
                          name={field}
                          key={field}
                          id={'form-' + field}
                          placeholder={fields[field].title}
                        />
                      )}

                      {fields[field].valid === true ? (
                        <FormValid />
                      ) : (
                        <FormInvalid message={fields[field].invalidMessage} />
                      )}
                    </div>
                  </div>
                ))}
              </div>
              <div className="card-body bg-light">
                <h4 className="card-title m-t-10 p-b-20">
                  {gettext('User groups')}
                </h4>
                <div className="form-group row p-b-15">
                  <label className="col-sm-3 text-left control-label col-form-label">
                    {gettext('User groups')}
                  </label>
                  <div className="col-sm-9">
                    <Multiselect
                      options={groupsOptions}
                      selectedValues={selectedGroups}
                      onSelect={this.setSelectedGroups.bind(this)}
                      onRemove={this.setSelectedGroups.bind(this)}
                      displayValue="name"
                      placeholder="Select some items..."
                    />
                  </div>
                </div>
              </div>
              <div className="card-body">
                <div className="form-group m-b-0 text-right">
                  <button
                    type="submit"
                    className="btn btn-info waves-effect waves-light btn-save"
                  >
                    {gettext('Save')}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    );
  }
}

UserEdit.contextType = CrmContext;
export default withRouter(UserEdit);
