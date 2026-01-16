import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../helpers/context';
import { Link } from 'react-router-dom';
import axios from '../../helpers/interceptors';
import FormValid from './form-valid';
import FormInvalid from './form-invalid';

class Form extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const {
      title,
      object,
      fields,
      redirect,
      formWasValidated,
      onHandleChange,
      onHandleChangeCheckbox,
      submitForm,
    } = this.props;
    const { gettext } = this.context.textdomain;
    return (
      <div className="col-12">
        <div className="card">
          <form
            className={'form-horizontal r-separator needs-validation'}
            onSubmit={submitForm}
          >
            <div className="card-body">
              <h4 className="card-title">{title}</h4>
            </div>

            <div className="card-body">
              {Object.keys(fields).map((field) => (
                <div className="form-group row p-b-15" key={field}>
                  <label
                    htmlFor={'form-' + field}
                    className="col-sm-3 text-left control-label col-form-label"
                  >
                    {fields[field].title}
                  </label>
                  <div className="col-sm-9">
                    {fields[field].type === 'text' ? (
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
                        value={object[field]}
                        onChange={onHandleChange}
                        name={field}
                        key={field}
                        id={'form-' + field}
                        placeholder={fields[field].title}
                      />
                    ) : (
                      fields[field].type === 'checkbox' && (
                        <input
                          type="checkbox"
                          className={
                            'custom-control custom-checkbox ' +
                            (formWasValidated
                              ? fields[field].valid
                                ? 'is-valid'
                                : 'is-invalid'
                              : '')
                          }
                          name={field}
                          key={field}
                          checked={object[field] == 1}
                          onChange={onHandleChangeCheckbox}
                        />
                      )
                    )}

                    {fields[field].valid === true ? (
                      <FormValid />
                    ) : (
                      <FormInvalid message={fields[field].invalidMessage} />
                    )}
                  </div>
                </div>
              ))}

              <div className="form-group m-b-0 text-right">
                <button
                  type="submit"
                  className="btn btn-info waves-effect waves-light btn-save"
                >
                  {gettext('Save')}
                </button>
                <button
                  type="button"
                  onClick={redirect}
                  className="btn btn-dark waves-effect waves-light"
                >
                  {gettext('Cancel')}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    );
  }
}

Form.contextType = CrmContext;
export default withRouter(Form);
