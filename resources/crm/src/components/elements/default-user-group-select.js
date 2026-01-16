import React from 'react';
import CrmContext from '../../helpers/context';

class DefaultUserGroupSelect extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      defaultGroup: '',
    };
  }

  componentDidMount() {
    const { groups } = this.props;
    let defGroup = groups.find((x) => x.is_default == true);
    if (defGroup) {
      let defaultGroup = defGroup.id;
      this.setState({ defaultGroup });
    }
  }

  selectDefaultGroup(e) {
    let defaultGroup = e.target.value;
    this.setState({ defaultGroup });
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { groups, changeDefaultGroup } = this.props;
    const { defaultGroup } = this.state;

    return (
      <div className="m-t-20">
        <label>
          <select
            className="form-control form-control-sm"
            onChange={this.selectDefaultGroup.bind(this)}
            name="table_length"
            value={defaultGroup}
          >
            <option value="0">{gettext('None')}</option>
            {groups.map((group) => (
              <option key={group.id} value={group.id}>
                {group.name}
              </option>
            ))}
          </select>
        </label>
        <button
          className="btn btn-primary m-l-20"
          onClick={() => {
            changeDefaultGroup(defaultGroup);
          }}
        >
          {gettext('Change')}
        </button>
      </div>
    );
  }
}

DefaultUserGroupSelect.contextType = CrmContext;
export default DefaultUserGroupSelect;
