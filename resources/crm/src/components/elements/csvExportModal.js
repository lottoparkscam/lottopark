import React from 'react';
import CrmContext from '../../helpers/context';

class CsvExportModal extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      choosedColumns: [],
    };
  }

  componentDidMount() {
    const { columns } = this.props;
    let choosedColumns = [];

    columns.forEach((column) => {
      if (column.shown) {
        choosedColumns.push(column.name);
      }
    });
    this.setState({ choosedColumns });
  }

  toggleCol(e) {
    const { columnsNames } = this.props;
    let name = e.target.name;
    let checked = e.target.checked;
    let choosedColumns = this.state.choosedColumns;

    let index = choosedColumns.indexOf(name);
    if (index > -1 && !checked) {
      choosedColumns.splice(index, 1);
    } else if (index === -1 && checked) {
      choosedColumns.push(name);
    }
    this.setState({
      choosedColumns,
    });
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { visible, close, downloadCSV, columns, columnsNames } = this.props;
    const { choosedColumns } = this.state;
    return (
      <div id="responsive-modal" className="modal show modal-bg">
        {' '}
        {visible ? (
          <div className="modal-dialog">
            {' '}
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">
                  {gettext('Choose columns to export')}
                </h4>
              </div>
              <div className="modal-body">
                <div className="row">
                  <div className="col columns-hidden">
                    {columnsNames &&
                      columns.map((col) => (
                        <label key={'label' + col.name}>
                          <input
                            type="checkbox"
                            name={col.name}
                            checked={
                              choosedColumns.indexOf(col.name) > -1
                                ? true
                                : false
                            }
                            onChange={this.toggleCol.bind(this)}
                          />
                          {' ' + columnsNames[col.name]}
                        </label>
                      ))}
                  </div>
                </div>
              </div>
              <div className="modal-footer">
                <button
                  className="btn btn-danger waves-effect waves-light"
                  onClick={() => downloadCSV(choosedColumns)}
                >
                  {gettext('Export')}
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

CsvExportModal.contextType = CrmContext;
export default CsvExportModal;
