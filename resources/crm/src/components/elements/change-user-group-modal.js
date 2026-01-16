import React from 'react';
import CrmContext from '../../helpers/context';
import Multiselect from 'multiselect-react-dropdown';

class ChangeUserGroupModal extends React.Component {
  constructor(props) {
    super(props);

    this.state = {};
  }

  render() {
    const { gettext } = this.context.textdomain;
    const {
      visible,
      close,
      options,
      selectedGroups,
      setSelectedGroups,
      changeGroups,
    } = this.props;
    return (
      <div id="responsive-modal" className="modal show modal-bg">
        {' '}
        {visible ? (
          <div className="modal-dialog">
            {' '}
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">{gettext('Choose groups')}</h4>
              </div>
              <div className="modal-body">
                <div className="row">
                  <Multiselect
                    options={options}
                    selectedValues={selectedGroups}
                    onSelect={setSelectedGroups}
                    onRemove={setSelectedGroups}
                    displayValue="name"
                    placeholder="Select some items..."
                  />
                </div>
              </div>
              <div className="modal-footer">
                <button
                  className="btn btn-danger waves-effect waves-light"
                  onClick={changeGroups}
                >
                  {gettext('Add to groups')}
                </button>
                <button
                  className="btn btn-default waves-effect"
                  onClick={close}
                >
                  {gettext('Cancel')}
                </button>
              </div>
            </div>
          </div>
        ) : null}{' '}
      </div>
    );
  }
}

ChangeUserGroupModal.contextType = CrmContext;
export default ChangeUserGroupModal;
