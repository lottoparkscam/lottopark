import React from 'react';
import axios from '../../../helpers/interceptors';
import toastr from 'toastr';
import { withRouter } from 'react-router-dom';
import FormValid from '../../elements/form-valid';
import FormInvalid from '../../elements/form-invalid';
import Multiselect from 'multiselect-react-dropdown';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';

const WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID = 3;
const WHITE_LABEL_ADMINISTRATOR_ROLE_ID = 4;

class AdminUser extends React.Component {
  _isMounted = false;
  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      roles: [],
      languages: [],
      timezones: [],
      user: {
        username: '',
        name: '',
        surname: '',
        email: '',
        password: '',
        confirmPassword: '',
        timezone: 'UTC',
        language_id: '1',
        role_id: '',
        accessList: {},
      },
      accessModules: [],
      usernameValid: true,
      nameValid: true,
      surnameValid: true,
      emailValid: true,
      passwordValid: true,
      confirmPasswordValid: true,
      timezoneValid: true,
      role_idValid: true,
      language_idValid: true,
      formWasValidated: false,
      usernameInvalidMessage: '',
      nameInvalidMessage: '',
      surnameInvalidMessage: '',
      emailInvalidMessage: '',
      passwordInvalidMessage: '',
      confirmPasswordInvalidMessage: '',
      timezoneInvalidMessage: '',
      language_idInvalidMessage: '',
      supportEmailInvalidMessage: '',
      paymentEmailInvalidMessage: '',
      role_idInvalidMessage: '',
      selected: [],
      addedModules: [],
      deletedModules: [],
    };
  }

  componentDidMount() {
    this._isMounted = true;
    this.context.verifyToken();
    axios.get('/langsrolestimezones').then((res) => {
      if (res.data.code == 200) {
        let roles = res.data.roles;
        let languages = Object.values(res.data.languages);
        let timezones = res.data.timezones;

        if (this._isMounted) {
          this.setState({
            roles,
            languages,
            timezones,
            loading: false,
          });
        }
      }
    });

    if (!this.props.myProfile) {
      let accessModules = [];
      this.context.modules.forEach((mod) => {
        let newItem = { ...mod, checked: false };
        accessModules.push(newItem);
      });
      if (this._isMounted) {
        this.setState({
          accessModules,
        });
      }

      if (this.props.userProfile) {
        let id = this.props.match.params.id;
        if (id === this.context.user.id) {
          this.props.history.push('/profile');
        }
        axios
          .post('/admin/details', {
            id,
          })
          .then((res) => {
            if (res.data.code == 200) {
              let user = res.data.user;
              if (this.context.user.role_id !== '1' && user.role_id === '1') {
                this.props.history.push('/403');
              }
              let modules = res.data.user_modules;
              delete user.id;
              user.password = '';
              user.confirmPassword = '';
              user.accessList = {};
              if (this._isMounted) {
                this.setState({ user: user, loading: false });
              }

              let selected = [];

              if (modules.length > 0) {
                modules.forEach((access) => {
                  if (access.whitelabel_id == null) access.whitelabel_id = 0;
                  const selectedIds = selected.map((item) => item.id);
                  const isSuperadminNotSelected =
                    selectedIds.indexOf(access.whitelabel_id) === -1;
                  if (isSuperadminNotSelected) {
                    selected.push({
                      name: access.whitelabel_name ?? 'Superadmin',
                      id: access.whitelabel_id,
                    });
                  }
                  this.updateAccesses(
                    access.module_id,
                    access.whitelabel_id,
                    true,
                  );
                });
              }

              let userCopy = JSON.parse(JSON.stringify(this.state.user));
              let oldUser = userCopy;

              if (this._isMounted) {
                this.setState({
                  selected,
                  oldUser,
                });
              }
            }
          });
      }
    }
    if (this.props.myProfile) {
      let user = { ...this.context.user };
      user.password = '';
      user.confirmPassword = '';
      let oldUser = { ...user };

      if (this._isMounted) {
        this.setState({
          user,
          oldUser,
          loading: false,
        });
      }
    }
  }

  onHandleChange(e) {
    let user = this.state.user;
    user[e.target.name] = e.target.value;
    let selected = [...this.state.selected];
    if (e.target.name == 'role_id') {
      selected = [];
      user.accessList = {};
      let accessModules = [...this.state.accessModules];
      accessModules.forEach((mod) => {
        this.updateAccesses(mod.module_id, mod.whitelabel_id, false);
      });
      if (this._isMounted) {
        this.setState({ accessModules });
      }
    }
    if (this._isMounted) {
      this.setState({
        user,
        [e.target.name + 'Valid']: true,
        [e.target.name + 'InvalidMessage']: '',
        selected,
      });
    }

    if (this.state.user.role_id == 1) {
      this.state.accessModules.forEach((mod) => {
        this.updateAccesses(mod.module_id, mod.whitelabel_id, true);
      });
    }
    if (this.state.user.role_id == WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID) {
      this.state.accessModules.forEach((mod) => {
        if (
          mod.module_name == 'admins-view' ||
          mod.module_name == 'admins-edit' ||
          mod.module_name == 'admins-delete'
        ) {
          this.updateAccesses(mod.module_id, mod.whitelabel_id, true);
        }
      });
    }
  }

  handleSubmit(e) {
    e.preventDefault();

    if (this.props.myProfile || this.props.userProfile) {
      this.updateUser();
    } else {
      this.addUser();
    }
  }

  updateUser() {
    const { user, oldUser, addedModules, deletedModules, accessModules } =
      this.state;
    const { gettext } = this.context.textdomain;

    let updatedUser = {};
    Object.keys(user).forEach((property) => {
      if (property != 'accessList') {
        if (user[property] != oldUser[property]) {
          updatedUser[property] = user[property];
        }
      }
    });
    if (addedModules.length > 0) {
      updatedUser.addedModules = addedModules;
    }
    if (deletedModules.length > 0) {
      updatedUser.deletedModules = deletedModules;
    }

    if (this.props.userProfile) {
      let addedWhitelabels = [];
      let deletedWhitelabels = [];
      let oldWhitelabels = Object.keys(oldUser.accessList);

      addedModules.forEach((mod) => {
        if (
          mod.whitelabel_id !== 0 &&
          oldWhitelabels.indexOf(mod.whitelabel_id) == -1 &&
          addedWhitelabels.indexOf(mod.whitelabel_id) == -1
        ) {
          addedWhitelabels.push(mod.whitelabel_id);
        }
      });
      deletedModules.forEach((mod) => {
        let whitelabel = mod.whitelabel_id;
        if (whitelabel == 0) return false;
        let wlExists = accessModules.find(
          (x) => x.whitelabel_id === whitelabel && x.checked,
        );
        if (!wlExists) {
          if (deletedWhitelabels.indexOf(whitelabel) == -1) {
            deletedWhitelabels.push(whitelabel);
          }
        }
      });
      if (addedWhitelabels.length > 0) {
        updatedUser.addedWhitelabels = addedWhitelabels;
      }
      if (deletedWhitelabels.length > 0) {
        updatedUser.deletedWhitelabels = deletedWhitelabels;
      }
    }

    let id = null;
    let mode = null;
    if (this.props.myProfile) {
      id = this.state.user.id;
      mode = 'self';
    } else if (this.props.userProfile) {
      id = this.props.match.params.id;
    }

    try {
      axios
        .post('/update/admin', {
          id: id,
          updatedUser,
          mode,
        })
        .then((res) => {
          if (res) {
            if (res.data.code == 400) {
              let errors = res.data.errors;
              this.setErrors(errors);
            }
            if (res.data.code == 200) {
              this.showToast(gettext('User updated successfully.'));
              if (!this.props.myProfile) this.redirect();
            }
          }
        });
    } catch (e) {
      console.log('e ' + e);
    }
  }

  addUser() {
    const { user } = this.state;
    const { gettext } = this.context.textdomain;

    try {
      axios
        .post('/add/newadmin', {
          user,
        })
        .then((res) => {
          if (res) {
            if (res.data.code == 400) {
              let errors = res.data.errors;
              this.setErrors(errors);
            } else if (res.data.code == 200) {
              if (this._isMounted) {
                this.setState({
                  user: {
                    username: '',
                    name: '',
                    surname: '',
                    email: '',
                    password: '',
                    confirmPassword: '',
                    timezone: '',
                    language_id: '',
                    role_id: '',
                    accessList: [],
                  },
                  usernameValid: null,
                  nameValid: null,
                  surnameValid: null,
                  emailValid: null,
                  passwordValid: null,
                  confirmPasswordValid: null,
                  timezoneValid: null,
                  role_idValid: null,
                  language_idValid: null,
                  supportEmailValid: null,
                  paymentEmailValid: null,
                  formWasValidated: false,
                });
              }
              this.showToast(gettext('User added successfully.'));
              this.redirect();
            }
          }
        });
    } catch (e) {
      console.log('e ' + e);
    }
  }

  showToast(message) {
    toastr.options = {
      positionClass: 'toast-bottom-left',
      hideDuration: 300,
      timeOut: 10000,
    };
    toastr.clear();
    setTimeout(() => toastr.success(message), 300);
  }

  setErrors(errors) {
    Object.keys(errors).forEach((field) => {
      this.setState({
        [field + 'Valid']: false,
        [field + 'InvalidMessage']: errors[field],
        formWasValidated: true,
      });
    });
  }

  redirect() {
    const { myProfile, history } = this.props;
    if (myProfile) {
      history.push('/');
    } else history.goBack();
  }

  componentWillUnmount() {
    this._isMounted = false;
  }

  handleSelectedChanged = (selected) => {
    if (this._isMounted) {
      this.setState({ selected });
    }

    if (this.state.user.role_id == WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID) {
      let user = { ...this.state.user };
      user.accessList = {};
      if (this._isMounted) {
        this.setState({
          user,
        });
      }
      if (selected.length > 0) {
        selected.forEach((sel) => {
          this.state.accessModules.forEach((mod) => {
            if (mod.whitelabel_id == sel.id) {
              this.updateAccesses(mod.module_id, sel.id, true);
            }
          });
        });
      }
    }
    var is_whitelabel_administrator =
      this.state.user.role_id == WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID ||
      this.state.user.role_id == WHITE_LABEL_ADMINISTRATOR_ROLE_ID;
    if (is_whitelabel_administrator) {
      if (this.props.userProfile) {
        Object.keys(this.state.oldUser.accessList).forEach((whitelabel) => {
          if (whitelabel != 0) {
            const selectedIds = selected.map((item) => item.id);
            const isSuperadminNotSelected =
              selectedIds.indexOf(whitelabel) == -1;
            if (isSuperadminNotSelected) {
              this.state.accessModules.forEach((mod) => {
                if (mod.whitelabel_id == whitelabel) {
                  this.updateAccesses(mod.module_id, mod.whitelabel_id, false);
                }
              });
            }
          }
        });
      }
    }
  };

  handleAccessChanged(e) {
    const item = e.target.name.split(':');
    const whitelabel = item[0];
    const mod = item[1];
    const isChecked = e.target.checked;
    this.updateAccesses(mod, whitelabel, isChecked);
  }

  updateAccesses(mod, whitelabel, isChecked) {
    let list = [...this.state.accessModules];
    let index = list.findIndex((x) => {
      return x.module_id == mod && x.whitelabel_id == whitelabel;
    });

    if (index > -1) {
      list[index].checked = isChecked;
      if (this._isMounted) {
        this.setState({
          accessModules: list,
        });
      }
      let user = { ...this.state.user };

      let userIndex = user.accessList.hasOwnProperty(whitelabel);
      let itemIndex;
      if (userIndex) itemIndex = user.accessList[whitelabel].indexOf(mod);

      if (isChecked) {
        if (!itemIndex) {
          user.accessList[whitelabel] = [];
        }
        user.accessList[whitelabel].push(mod);
      } else {
        if (itemIndex > -1) {
          user.accessList[whitelabel].splice(itemIndex, 1);
          if (user.accessList[whitelabel].length == 0) {
            delete user.accessList[whitelabel];
          }
        }
      }
      if (this._isMounted) {
        this.setState({
          user,
        });
      }

      if (this.props.userProfile && this.state.oldUser) {
        let oldUser = { ...this.state.oldUser };

        let oldUserIndex = oldUser.accessList.hasOwnProperty(whitelabel);
        let oldItemIndex = -1;
        if (oldUserIndex)
          oldItemIndex = oldUser.accessList[whitelabel].indexOf(mod);

        let obj = { whitelabel_id: whitelabel, module_id: mod };
        let addedModules = this.state.addedModules;
        let deletedModules = this.state.deletedModules;
        if (isChecked) {
          let index = deletedModules.findIndex((x) => {
            return x.module_id == mod && x.whitelabel_id == whitelabel;
          });
          if (index > -1) {
            deletedModules.splice(index, 1);
          }
          if (oldItemIndex == -1) {
            addedModules.push(obj);
          }
        } else {
          let index = addedModules.findIndex((x) => {
            return x.module_id == mod && x.whitelabel_id == whitelabel;
          });
          if (index > -1) {
            addedModules.splice(index, 1);
          }
          if (oldItemIndex > -1) {
            deletedModules.push(obj);
          }
        }
        if (this._isMounted) {
          this.setState({
            addedModules,
            deletedModules,
          });
        }
      }
    }
  }

  fillTable() {
    const { selected, accessModules } = this.state;
    const selectedIds = selected.map((item) => item.id);
    let table = [];
    var modulesNames = [];

    accessModules.forEach((mod) => {
      const isSuperadminNotSelected =
        selectedIds.indexOf(mod.whitelabel_id) == -1;
      if (isSuperadminNotSelected) return false;
      if (this.state.user.role_id !== WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID) {
        if (modulesNames.indexOf(mod.module_name) < 0) {
          modulesNames.push(mod.module_name);
        }
      }
    });

    modulesNames.forEach((modName, i) => {
      let children = [];

      selectedIds.forEach((sel, i) => {
        let index = this.context.modules.findIndex(
          (x) => x.whitelabel_id == sel,
        );
        if (index > -1) {
          let crmModule = accessModules.find((mod) => {
            return mod.whitelabel_id == sel && mod.module_name == modName;
          });

          let inputDisabled = !crmModule;

          if (crmModule && 'whitelabel_id' in crmModule) {
            const whitelabel = this.context.whitelabels.find(
              (wl) => wl.id == crmModule.whitelabel_id,
            );

            const excludedModNames = [
              'users-manual-deposit-add',
              'users-balance-edit',
              'users-bonus-balance-manual-deposit-add',
              'users-bonus-balance-edit',
              'users-manual-deposit-casino-add',
              'users-balance-casino-edit',
            ];

            let isAdministratorType2 = this.state.user.role_id == 2;
            inputDisabled =
              !isAdministratorType2 &&
              (!crmModule ||
                (whitelabel &&
                  excludedModNames.includes(modName) &&
                  whitelabel.type == 1));
          }

          children.push(
            <td key={'td' + i} className="p-0">
              <label className="label-area">
                <input
                  type="checkbox"
                  key={'check' + sel + modName}
                  name={
                    crmModule &&
                    crmModule.whitelabel_id + ':' + crmModule.module_id
                  }
                  checked={crmModule && crmModule.checked}
                  disabled={inputDisabled}
                  onChange={this.handleAccessChanged.bind(this)}
                />
              </label>
            </td>,
          );
        }
      });

      table.push(
        <tr key={'trmod' + i}>
          <th>{modName}</th>
          {children}
        </tr>,
      );
    });
    return table;
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      loading,
      languages,
      roles,
      timezones,
      user,
      usernameValid,
      nameValid,
      surnameValid,
      emailValid,
      passwordValid,
      confirmPasswordValid,
      timezoneValid,
      language_idValid,
      supportEmailValid,
      paymentEmailValid,
      role_idValid,
      usernameInvalidMessage,
      nameInvalidMessage,
      surnameInvalidMessage,
      emailInvalidMessage,
      passwordInvalidMessage,
      confirmPasswordInvalidMessage,
      timezoneInvalidMessage,
      role_idInvalidMessage,
      language_idInvalidMessage,
      supportEmailInvalidMessage,
      paymentEmailInvalidMessage,
      formWasValidated,
      selected,
    } = this.state;
    let rolesOptions;
    let langsOptions;
    if (roles.length > 0) {
      rolesOptions = roles.map(({ id, role }, index) =>
        this.context.user && this.context.user.role_id == 1 ? (
          <option key={index} value={id}>
            {role}
          </option>
        ) : (
          (id == 3 || id == 4) && (
            <option key={index} value={id}>
              {role}
            </option>
          )
        ),
      );
    }
    if (languages.length > 0) {
      langsOptions = languages.map(({ id, name }, index) => (
        <option key={index} value={id}>
          {name}
        </option>
      ));
    }
    if (!this.props.myProfile) {
      var options = [];
      this.context.whitelabels.forEach((whitelabel) => {
        if (this.state.user.role_id != 2 && whitelabel.id === 0) return false;
        options.push({
          name: whitelabel.name,
          id: whitelabel.id,
        });
      });
    }
    let roleDescriptions = [
      '',
      gettext('Has access to everything.'),
      gettext(
        'Has access to selected modules, selected white-labels and their modules.',
      ),
      gettext('Has access to selected white-labels and all their modules.'),
      gettext('Has access to selected white-labels and selected modules.'),
    ];

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-12">
          <div className="card">
            <form
              className={'form-horizontal r-separator needs-validation'}
              onSubmit={this.handleSubmit.bind(this)}
            >
              <div className="card-body">
                <h4 className="card-title">{gettext('User Data')}</h4>
                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-username"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Username')}
                  </label>
                  <div className="col-sm-9">
                    <input
                      type="text"
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? usernameValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.username}
                      onChange={this.onHandleChange.bind(this)}
                      name="username"
                      id="form-username"
                      placeholder={gettext('Username')}
                    />
                    {usernameValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={usernameInvalidMessage} />
                    )}
                  </div>
                </div>
                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-name"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Name')}
                  </label>
                  <div className="col-sm-9">
                    <input
                      type="text"
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? nameValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.name}
                      onChange={this.onHandleChange.bind(this)}
                      name="name"
                      id="form-name"
                      placeholder={gettext('Name')}
                    />
                    {nameValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={nameInvalidMessage} />
                    )}
                  </div>
                </div>
                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-surname"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Surname')}
                  </label>
                  <div className="col-sm-9">
                    <input
                      type="text"
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? surnameValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.surname}
                      onChange={this.onHandleChange.bind(this)}
                      name="surname"
                      id="form-surname"
                      placeholder={gettext('Surname')}
                    />
                    {surnameValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={surnameInvalidMessage} />
                    )}
                  </div>
                </div>
                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-email"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Email')}
                  </label>
                  <div className="col-sm-9">
                    <input
                      type="text"
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? emailValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.email}
                      onChange={this.onHandleChange.bind(this)}
                      name="email"
                      id="form-email"
                      placeholder={gettext('Email')}
                    />
                    {emailValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={emailInvalidMessage} />
                    )}
                  </div>
                </div>
                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-password"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Password')}
                  </label>
                  <div className="col-sm-9">
                    <input
                      type="password"
                      autoComplete="new-password"
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? passwordValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.password}
                      onChange={this.onHandleChange.bind(this)}
                      name="password"
                      id="form-password"
                      placeholder={gettext('Password')}
                    />
                    {passwordValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={passwordInvalidMessage} />
                    )}
                  </div>
                </div>
                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-password"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Confirm Password')}
                  </label>
                  <div className="col-sm-9">
                    <input
                      type="password"
                      autoComplete="new-password"
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? confirmPasswordValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.confirmPassword}
                      onChange={this.onHandleChange.bind(this)}
                      name="confirmPassword"
                      id="form-confirm-password"
                      placeholder={gettext('Confirm Password')}
                    />
                    {confirmPasswordValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={confirmPasswordInvalidMessage} />
                    )}
                  </div>
                </div>
              </div>
              <div className="card-body bg-light">
                <h4 className="card-title m-t-10 p-b-20">
                  {gettext('User Settings')}
                </h4>
                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-timezone"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Timezone')}
                  </label>
                  <div className="col-sm-9">
                    <select
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? timezoneValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.timezone}
                      onChange={this.onHandleChange.bind(this)}
                      name="timezone"
                      id="form-timezone"
                    >
                      <option value="">--</option>
                      {Object.keys(timezones).map((key) => (
                        <option key={key} value={key}>
                          {timezones[key]}
                        </option>
                      ))}
                    </select>
                    {timezoneValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={timezoneInvalidMessage} />
                    )}
                  </div>
                </div>

                <div className="form-group row p-b-15">
                  <label
                    htmlFor="form-language"
                    className="col-sm-3 text-right control-label col-form-label"
                  >
                    {gettext('Language')}
                  </label>
                  <div className="col-sm-9">
                    <select
                      className={
                        'form-control ' +
                        (formWasValidated
                          ? language_idValid
                            ? 'is-valid'
                            : 'is-invalid'
                          : '')
                      }
                      value={user.language_id}
                      onChange={this.onHandleChange.bind(this)}
                      name="language_id"
                      id="form-language"
                    >
                      <option value="">--</option>
                      {langsOptions}
                    </select>
                    {language_idValid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={language_idInvalidMessage} />
                    )}
                  </div>
                </div>
                {this.state.user.role_id ==
                  WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID && (
                  <div className="contactEmails">
                    <div className="form-group row p-b-15">
                      <label
                        htmlFor="form-support-contact"
                        className="col-sm-3 text-right control-label col-form-label"
                      >
                        {gettext('Support email')}
                      </label>
                      <div className="col-sm-9">
                        <input
                          type="text"
                          className={
                            'form-control ' +
                            (formWasValidated
                              ? supportEmailValid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          value={user.supportEmail}
                          onChange={this.onHandleChange.bind(this)}
                          name="supportEmail"
                          id="form-support-contact"
                          placeholder={gettext('Contact mail')}
                        />
                        {supportEmailValid === true ? (
                          <FormValid />
                        ) : (
                          <FormInvalid message={supportEmailInvalidMessage} />
                        )}
                      </div>
                    </div>
                    <div className="form-group row p-b-15">
                      <label
                        htmlFor="form-payment-contact"
                        className="col-sm-3 text-right control-label col-form-label"
                      >
                        {gettext('Payment email')}
                      </label>
                      <div className="col-sm-9">
                        <input
                          type="text"
                          className={
                            'form-control ' +
                            (formWasValidated
                              ? paymentEmailValid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          value={user.paymentEmail}
                          onChange={this.onHandleChange.bind(this)}
                          name="paymentEmail"
                          id="form-payment-contact"
                          placeholder={gettext('Payment email')}
                        />
                        {paymentEmailValid === true ? (
                          <FormValid />
                        ) : (
                          <FormInvalid message={paymentEmailInvalidMessage} />
                        )}
                      </div>
                    </div>
                  </div>
                )}
                {!this.props.myProfile && (
                  <div className="form-group row p-b-15">
                    <label
                      htmlFor="form-role"
                      className="col-sm-3 text-right control-label col-form-label"
                    >
                      {gettext('User Role')}
                    </label>
                    <div className="col-sm-9">
                      <select
                        className={
                          'form-control ' +
                          (formWasValidated
                            ? role_idValid
                              ? 'is-valid'
                              : 'is-invalid'
                            : '')
                        }
                        value={user.role_id}
                        onChange={this.onHandleChange.bind(this)}
                        name="role_id"
                        id="form-role"
                      >
                        <option value="">--</option>
                        {rolesOptions}
                      </select>
                      {role_idValid === true ? (
                        <FormValid />
                      ) : (
                        <FormInvalid message={role_idInvalidMessage} />
                      )}
                      <span>{roleDescriptions[user.role_id]}</span>
                    </div>
                  </div>
                )}
                {user.role_id &&
                  user.role_id !== '1' &&
                  !this.props.myProfile && (
                    <div className="card-body bg-light">
                      <h4 className="card-title m-t-10 p-b-20">
                        {gettext('User Access')}
                      </h4>
                      <div className="row p-b-15">
                        <Multiselect
                          options={options}
                          selectedValues={selected}
                          onSelect={this.handleSelectedChanged.bind(this)}
                          onRemove={this.handleSelectedChanged.bind(this)}
                          displayValue="name"
                          placeholder="Select some items..."
                        />
                      </div>
                      {user.role_id != 3 && selected.length > 0 && (
                        <div className="form-group row p-b-15">
                          <div className="table-responsive">
                            <table className="table table-striped">
                              <thead>
                                <tr>
                                  <th></th>
                                  {selected.map(
                                    (item, index) =>
                                      this.context.whitelabels.find(
                                        (x) => x.id === item.id,
                                      ) && (
                                        <th key={'th' + index}>
                                          {item.id === 0
                                            ? 'Superadmin'
                                            : this.context.whitelabels.find(
                                                (x) => x.id === item.id,
                                              ).name}
                                        </th>
                                      ),
                                  )}
                                </tr>
                              </thead>
                              <tbody>{this.fillTable()}</tbody>
                            </table>
                          </div>
                        </div>
                      )}
                    </div>
                  )}
              </div>
              <div className="card-body">
                <div className="form-group m-b-0 text-right">
                  <button
                    type="submit"
                    className="btn btn-info waves-effect waves-light btn-save"
                  >
                    {gettext('Save')}
                  </button>
                  <button
                    type="button"
                    onClick={this.redirect.bind(this)}
                    className="btn btn-dark waves-effect waves-light"
                  >
                    {gettext('Cancel')}
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

AdminUser.contextType = CrmContext;
export default withRouter(AdminUser);
