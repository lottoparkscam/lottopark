import React, { Component } from 'react';
import axios from '../../helpers/interceptors';
import { capitalize, capitalizeWords } from '../../helpers/notation';
import CrmContext from '../../helpers/context';
import Loading from './loading';
import Pagination from './pagination';
import DateRangePicker from '@wojtekmaj/react-daterange-picker/dist/entry.nostyle';
import * as utils from '../../helpers/utils';
import DateRangeSelect from './date-range-select';
import getSymbolFromCurrency from 'currency-symbol-map';
import { decode } from 'html-entities';

/**
 * Props:
 *    - header
 *    - tabs {tabName: [{name, label, filterType}]}
 *    - itemsPerPage (default 50)
 *    - sortBy (default first column of active tab)
 */
class List extends Component {
  static contextType = CrmContext;
  static filters = {
    search: 'search',
    integer: 'integer',
    amount: 'amount',
    date: 'date',
    boolean: 'boolean',
    percent: 'percent',
  };
  static specialFormats = {
    json: 'json',
    boolean: 'boolean',
    capitalize: 'capitalize',
    capitalizeWords: 'capitalizeWords',
    beautifySlug: 'beautifySlug',
  };

  constructor(props) {
    super(props);
    const { tabs, itemsPerPage, sortBy } = this.props;
    const now = new Date();
    const year = now.getFullYear();

    this.timeout = null;
    this.state = {
      activeTab: tabs[0].name,
      page: 1,
      itemsPerPage: itemsPerPage || 50,
      sortBy: sortBy || tabs[0].columns[0].name,
      order: 'DESC',
      whitelabelId: null,
      tableLoading: true,
      tableReloading: false,
      exportLoading: false,
      tableData: [],
      tabs: [],
      filters: [],
      date: [
        new Date(year, now.getMonth(), 1),
        new Date(year, now.getMonth() + 1, 0),
      ],
      showDatePicker: false,
      dateRangeType: 'month',
      summary: [],
      total: null,
      currencies: null,
    };
  }
  componentDidMount() {
    const whitelabelId = this.context.choosedWhitelabel.id;
    const tabs = this.translateTabs();
    this.setState({ tabs, whitelabelId }, this.fetchTableData);
  }
  translateTabs() {
    const { gettext } = this.context.textdomain;
    const tabs = JSON.parse(JSON.stringify(this.props.tabs));
    return tabs.map((tab) => {
      tab.label = gettext(tab.label);
      tab.columns = tab.columns.map((column) => {
        column.label = gettext(column.label);
        return column;
      });
      return tab;
    });
  }
  getActiveTab() {
    const { activeTab, tabs } = this.state;
    return tabs.filter((tab) => tab.name === activeTab)[0];
  }
  getActiveTabIndex() {
    const { activeTab, tabs } = this.state;
    return tabs.findIndex((tab) => tab.name === activeTab);
  }
  calculateSummary() {
    const { tableData } = this.state;
    const { showSummary } = this.props;
    const columns = this.getActiveTab().columns;
    let summary = [];

    if (!showSummary) {
      return;
    }

    tableData.forEach((row) => {
      columns
        .filter(({ filterType }) => filterType === List.filters.amount)
        .forEach(({ name }) => {
          if (name in summary) {
            summary[name] += parseFloat(row[name]);
          } else {
            summary[name] = parseFloat(row[name]);
          }
        });
    });

    this.setState({ summary });
  }
  fetchTableData() {
    const {
      whitelabelId,
      activeTab,
      page,
      itemsPerPage,
      sortBy,
      order,
      tabs,
      filters,
      date,
    } = this.state;

    this.setState({ tableReloading: true, total: null, summary: [] });
    const { endpoint, tabsDatabaseField } = this.props;
    const tableColumns = this.getActiveTab().columns.map(
      (column) => column.name,
    );
    const [fromDate, toDate] = date;
    const fromDateYear = fromDate.getFullYear();
    const fromDateMonth = ('0' + (fromDate.getMonth() + 1)).slice(-2);
    const fromDateDay = ('0' + fromDate.getDate()).slice(-2);
    const toDateYear = toDate.getFullYear();
    const toDateMonth = ('0' + (toDate.getMonth() + 1)).slice(-2);
    const toDateDay = ('0' + toDate.getDate()).slice(-2);

    try {
      axios
        .post(endpoint, {
          whitelabelId,
          activeTab,
          page,
          itemsPerPage,
          sortBy,
          order,
          tableColumns,
          tabs: tabs.map((tab) => tab.name),
          tabsDatabaseField,
          filters,
          fromDatetime: `${fromDateYear}-${fromDateMonth}-${fromDateDay} 0:00:00`,
          toDatetime: `${toDateYear}-${toDateMonth}-${toDateDay} 23:59:59`,
          columnNameToFilterByDate: this.props.columnNameToFilterByDate || '',
        })
        .then((response) => {
          if (response.data.code === 200) {
            const { tableData, itemsCountPerTab } = response.data;
            let total = null;
            let currencies = null;

            if ('total' in response.data) {
              total = response.data.total;
            }

            if ('currencies' in response.data) {
              currencies = response.data.currencies;
            }

            this.setState(
              {
                tableData,
                itemsCountPerTab,
                tableLoading: false,
                tableReloading: false,
                total,
                currencies,
              },
              this.calculateSummary,
            );
          } else {
            this.context.showToast('error', 'Data cannot be downloaded');
            this.setState({ tableLoading: false, tableReloading: false });
          }
        });
    } catch (exception) {
      this.context.showToast('error', 'Data cannot be downloaded');
      this.setState({ tableLoading: false, tableReloading: false });
    }
  }
  setActiveTab(activeTabName) {
    const activeTab = this.state.tabs.filter(
      (tab) => tab.name === activeTabName,
    )[0];
    const tabs = this.state.tabs.map((tab) => {
      tab.columns = tab.columns.map((column) => {
        column.filter = null;
        return column;
      });
      return tab;
    });
    this.setState(
      {
        activeTab: activeTabName,
        page: 1,
        tableReloading: true,
        sortBy: activeTab.columns[0].name,
        order: 'DESC',
        filters: [],
        tabs,
      },
      this.fetchTableData,
    );
  }
  onItemsPerPageChange(event) {
    const itemsPerPage = event.target.value;
    this.setState(
      {
        itemsPerPage,
        page: 1,
        tableReloading: true,
      },
      this.fetchTableData,
    );
  }
  sortBy(column) {
    let { sortBy, order } = this.state;

    if (sortBy === column) {
      order = order === 'DESC' ? 'ASC' : 'DESC';
      this.setState({ order }, this.fetchTableData);
    } else {
      this.setState({ sortBy: column, order: 'DESC' }, this.fetchTableData);
    }
  }
  paginate(page) {
    this.setState({ page, tableReloading: true }, this.fetchTableData);
  }
  formatColumnContent(rawContent, format) {
    if (!rawContent) {
      return '';
    }

    if (Array.isArray(format)) {
      return format.reduce((content, type) => {
        return this.formatContentByType(content, type);
      }, rawContent);
    } else {
      return this.formatContentByType(rawContent, format);
    }
  }
  formatContentByType(rawContent, type) {
    let formattedContent;
    switch (type) {
      case List.specialFormats.json:
        const contentFormatted = JSON.stringify(
          JSON.parse(decode(rawContent)),
          undefined,
          '\t',
        );
        formattedContent = <pre>{contentFormatted}</pre>;
        break;
      case List.specialFormats.boolean:
        formattedContent = rawContent == '1' ? 'Yes' : 'No';
        break;
      case List.specialFormats.capitalize:
        formattedContent = capitalize(rawContent);
        break;
      case List.specialFormats.capitalizeWords:
        formattedContent = capitalizeWords(rawContent);
        break;
      case List.specialFormats.beautifySlug:
        const regex = /-/g;
        const separatedWords = rawContent.replace(regex, ' ');
        formattedContent = capitalizeWords(separatedWords);
        break;
      default:
        formattedContent = rawContent;
    }

    return formattedContent;
  }
  getTabs() {
    const { gettext } = this.context.textdomain;
    const { activeTab, tabs, itemsCountPerTab } = this.state;
    let preparedTabs = [];

    for (const tab of tabs) {
      const { name, label, icon } = tab;
      const isWhitelabelTypeV2 = this.context.isWhitelabelTypeV2();
      const shouldHideForV1 =
        !this.context.isSuperadmin() &&
        !isWhitelabelTypeV2 &&
        'hideForV1' in tab &&
        tab.hideForV1;
      const shouldHideForV2 =
        !this.context.isSuperadmin() &&
        isWhitelabelTypeV2 &&
        'hideForV2' in tab &&
        tab.hideForV2;
      if (shouldHideForV1 || shouldHideForV2) {
        continue;
      }
      preparedTabs.push(
        <li className="nav-item" key={`tab_${name}`}>
          <a
            className={
              'cursor-pointer nav-link ' + (activeTab === name && 'active')
            }
            role="tab"
            onClick={() => {
              this.setActiveTab(name);
            }}
          >
            <span className="hidden-sm-up">
              <i className={icon} />
            </span>
            <span className="hidden-xs-down">
              {` ${gettext(label)} (${itemsCountPerTab[name]})`}
            </span>
          </a>
        </li>,
      );
    }

    return (
      <ul className="nav nav-tabs" role="tablist">
        {preparedTabs}
      </ul>
    );
  }
  getTableHeader() {
    const { sortBy, order } = this.state;
    const activeTab = this.getActiveTab();
    let headers = [];

    for (const column of activeTab.columns) {
      const { name, label } = column;
      headers.push(
        <th
          key={`th_${name}`}
          onClick={() => this.sortBy(name)}
          className={
            'sorting' +
            (sortBy === name ? (order === 'ASC' ? '_asc' : '_desc') : '')
          }
        >
          {label}
        </th>,
      );
    }

    return headers;
  }
  getTableBody() {
    const { tableData } = this.state;
    const columns = this.getActiveTab().columns;
    let rows = [];

    for (const [key, row] of Object.entries(tableData)) {
      rows.push(
        <tr key={`list_row_${key}`}>
          {columns.map(({ name, specialFormat, filterType }) => {
            let rowContent = this.formatColumnContent(row[name], specialFormat);
            switch (filterType) {
              case List.filters.amount:
                rowContent = parseFloat(rowContent).toFixed(2);
                break;
              case List.filters.percent:
                rowContent = parseFloat(rowContent).toFixed(2);
                rowContent += '%';
                break;
              default:
                break;
            }
            const currencySign = this.getColumnCurrency(name);
            return (
              <td key={`td${key}${name}`}>
                {rowContent}
                {currencySign}
              </td>
            );
          })}
        </tr>,
      );
    }

    return rows;
  }
  filterDate(date, columnName) {
    let { filters, tabs } = this.state;
    const columns = this.getActiveTab().columns;
    const [updatedColumns, updatedFilters] = utils.filterDates(
      columns,
      filters,
      date,
      columnName,
      { type: List.filters.date },
    );
    const tabIndex = this.getActiveTabIndex();
    tabs[tabIndex].columns = updatedColumns;
    this.setState(
      { tabs, tableReloading: true, filters: updatedFilters, page: 1 },
      this.fetchTableData,
    );
  }
  filterAmount(event) {
    if (this.timeout) {
      clearTimeout(this.timeout);
    }

    const [columnName, rangeEdge] = event.target.name.split('-');
    const value = event.target.value;
    let columns = this.getActiveTab().columns;
    let { filters, tabs } = this.state;
    const columnIndex = columns.findIndex(
      (column) => column.name === columnName,
    );
    const filterIndex = filters.findIndex(
      (filter) => filter.column === columnName,
    );
    let range = columns[columnIndex].filter || [];

    if (rangeEdge === 'from') {
      range[0] = value;
    } else if (rangeEdge === 'to') {
      range[1] = value;
    }

    columns[columnIndex].filter = range;

    const filter = {
      column: columns[columnIndex].name,
      start: range[0],
      end: range[1],
      type: List.filters.amount,
    };

    if (filterIndex === -1) {
      filters.push(filter);
    } else {
      if (range[0] === 0 && range[1] === 0) {
        filters.splice(filterIndex, 1);
      } else {
        filters[filterIndex] = filter;
      }
    }

    const tabIndex = this.getActiveTabIndex();
    tabs[tabIndex].columns = columns;

    this.setState({ tabs, filters, tableReloading: true, page: 1 }, () => {
      this.timeout = setTimeout(this.fetchTableData.bind(this), 1000);
    });
  }
  filterSearch(event, filterType) {
    this.setState({ tableReloading: true });

    if (this.timeout) {
      clearTimeout(this.timeout);
    }

    const elementName = event.target.name;
    const value = event.target.value;
    let columns = this.getActiveTab().columns;
    const columnIndex = columns.findIndex(
      (column) => column.name === elementName,
    );
    columns[columnIndex].filter = value;
    this.setState({ columns });

    this.timeout = setTimeout(() => {
      let { filters } = this.state;
      const filterIndex = filters.findIndex(
        (filter) => filter.column === elementName,
      );

      if (filterIndex !== -1) {
        if (value === '' || value === null) {
          filters.splice(filterIndex, 1);
        } else filters[filterIndex].value = value;
      } else {
        const filter = { column: elementName, value: value, type: filterType };
        filters.push(filter);
      }
      this.setState(
        { filters, page: 1, tableReloading: true },
        this.fetchTableData,
      );
    }, 1000);
  }
  onRangeSelect(event) {
    const value = event.target.value;
    const now = new Date();
    const year = now.getFullYear();
    switch (value) {
      case '30days':
        const thirtyDaysInMilliseconds = 2592000000;
        const dateThirtyDaysAgoTimestamp = now - thirtyDaysInMilliseconds;
        const dateThirtyDaysAgo = new Date(dateThirtyDaysAgoTimestamp);
        this.setState({ date: [dateThirtyDaysAgo, now] }, this.fetchTableData);
        break;
      case 'month':
        this.setState(
          {
            date: [
              new Date(year, now.getMonth(), 1),
              new Date(year, now.getMonth() + 1, 0),
            ],
          },
          this.fetchTableData,
        );
        break;
      case 'lastmonth':
        this.setState(
          {
            date: [
              new Date(year, now.getMonth() - 1, 1),
              new Date(year, now.getMonth(), 0),
            ],
          },
          this.fetchTableData,
        );
        break;
      case 'year':
        this.setState(
          {
            date: [new Date(year, 0, 1), new Date(year + 1, 0, 0)],
          },
          this.fetchTableData,
        );
        break;
      case 'range':
        this.setState({ showDatePicker: value === 'range' });
        break;
    }

    this.setState({ dateRangeType: value });
  }
  onDateChange(date) {
    if (!date) {
      const now = new Date();
      const year = now.getFullYear();
      this.setState(
        {
          date: [
            new Date(year, now.getMonth(), 1),
            new Date(year, now.getMonth() + 1, 0),
          ],
          dateRangeType: 'month',
          showDatePicker: false,
        },
        this.fetchTableData,
      );
    } else {
      this.setState({ date }, this.fetchTableData);
    }
  }
  getFilters() {
    let preparedFilters = [];
    const { columns } = this.getActiveTab();
    const { gettext } = this.context.textdomain;

    for (const [key, column] of Object.entries(columns)) {
      if (!column.filterType) {
        preparedFilters.push(<td key={`filter_${key}`} />);
      }

      let filterContent = '';
      const { filterType, name, filter } = column;
      switch (filterType) {
        case List.filters.date:
          filterContent = (
            <DateRangePicker
              onChange={(date) => this.filterDate(date, name)}
              value={filter}
              calendarIcon={null}
            />
          );
          break;
        case List.filters.amount:
        case List.filters.integer:
          const from = filter && 0 in filter ? filter[0] : '';
          const to = filter && 1 in filter ? filter[1] : '';
          filterContent = (
            <div>
              <input
                className="input-40"
                type="text"
                name={name + '-from'}
                onChange={this.filterAmount.bind(this)}
                value={from || ''}
              />
              <span>{gettext(' to ')}</span>
              <input
                className="input-40"
                type="text"
                name={name + '-to'}
                onChange={this.filterAmount.bind(this)}
                value={to || ''}
              />
            </div>
          );
          break;
        case List.filters.search:
          filterContent = (
            <input
              type="text"
              name={name}
              onChange={(event) =>
                this.filterSearch(event, List.filters.search)
              }
              value={filter || ''}
            />
          );
          break;
        case List.filters.boolean:
          filterContent = (
            <input
              type="text"
              name={name}
              onChange={(event) =>
                this.filterSearch(event, List.filters.boolean)
              }
              value={filter || ''}
            />
          );
          break;
      }

      preparedFilters.push(
        <td key={`filter_${column.name}`}>{filterContent}</td>,
      );
    }

    return <tr className="table-search">{preparedFilters}</tr>;
  }
  getItemsPerPageSelect() {
    const { itemsPerPage } = this.state;
    const { gettext } = this.context.textdomain;
    return (
      <div className="users-view-table-length">
        <label>
          {gettext('Show') + ' '}
          <select
            className="form-control form-control-sm"
            onChange={this.onItemsPerPageChange.bind(this)}
            name="table_length"
            value={itemsPerPage}
          >
            <option value="50">{gettext('50')}</option>
            <option value="100">{gettext('100')}</option>
            <option value="200">{gettext('200')}</option>
            <option value="500">{gettext('500')}</option>
          </select>
          {' ' + gettext('entries')}
        </label>
      </div>
    );
  }
  getPagination() {
    const { gettext } = this.context.textdomain;
    const { itemsPerPage, tableData, activeTab, page, itemsCountPerTab } =
      this.state;
    return (
      <div className="row">
        <div className="col-sm-12 col-md-5">
          <div className="dataTables_info">
            {gettext('Showing') + ' '}
            {itemsPerPage * (page - 1) +
              1 -
              (tableData.length === 0 ? 1 : 0)}{' '}
            {gettext('to') + ' '}
            {itemsPerPage * (page - 1) + tableData.length} {gettext('of') + ' '}
            {itemsCountPerTab[activeTab]} {gettext('entries')}
          </div>
        </div>
        <div className="col-sm-12 col-md-7">
          <Pagination
            page={page}
            itemsPerPage={itemsPerPage}
            totalItems={itemsCountPerTab[activeTab]}
            paginate={this.paginate.bind(this)}
          />
        </div>
      </div>
    );
  }
  getColumnCurrency(columnName) {
    const { currencies } = this.state;
    if (!currencies) {
      return '';
    }
    return getSymbolFromCurrency(currencies[columnName] || '');
  }

  downloadCSV() {
    const FileDownload = require('js-file-download');
    this.setState({ exportLoading: true });

    const {
      whitelabelId,
      activeTab,
      page,
      itemsPerPage,
      sortBy,
      order,
      tabs,
      filters,
      date,
    } = this.state;

    const { endpoint, tabsDatabaseField } = this.props;
    const tableColumns = this.getActiveTab().columns.map(
      (column) => column.name,
    );
    const [fromDate, toDate] = date;
    const fromDateYear = fromDate.getFullYear();
    const fromDateMonth = ('0' + (fromDate.getMonth() + 1)).slice(-2);
    const fromDateDay = ('0' + fromDate.getDate()).slice(-2);
    const toDateYear = toDate.getFullYear();
    const toDateMonth = ('0' + (toDate.getMonth() + 1)).slice(-2);
    const toDateDay = ('0' + toDate.getDate()).slice(-2);

    try {
      axios
        .post(endpoint, {
          export: true,
          whitelabelId,
          activeTab,
          page,
          itemsPerPage,
          sortBy,
          order,
          tableColumns,
          tabs: tabs.map((tab) => tab.name),
          tabsDatabaseField,
          filters,
          fromDatetime: `${fromDateYear}-${fromDateMonth}-${fromDateDay} 0:00:00`,
          toDatetime: `${toDateYear}-${toDateMonth}-${toDateDay} 23:59:59`,
        })
        .then((response) => {
          if (response.data.code === 200) {
            const { tableData } = response.data;

            if (tableData.length === 0) {
              this.context.showToast('error', 'No data to export');
              this.setState({ exportLoading: false });
              return;
            }

            let exportData = '';
            const { columns } = this.getActiveTab();
            const headerKeys = Object.keys(tableData[0]);
            const headers = headerKeys.map((key) => {
              return columns.filter(({ name }) => name === key)[0].label;
            });
            exportData = headers.join(',') + '\n';
            tableData.forEach((row) => {
              exportData += Object.values(row).join(',') + '\n';
            });

            let date = new Date();
            let year = date.getFullYear();
            let month = date.getMonth() + 1;
            let day = date.getDate();
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let seconds = date.getSeconds();

            const filename =
              'Exportfile_' +
              activeTab +
              '_' +
              year +
              '-' +
              month +
              '-' +
              day +
              '_' +
              hours +
              ':' +
              minutes +
              ':' +
              seconds +
              '.csv';

            this.setState({ exportLoading: false });
            FileDownload(exportData, filename);
          } else {
            this.context.showToast('error', 'Data cannot be downloaded');
            this.setState({ exportLoading: false });
          }
        });
    } catch (exception) {
      this.context.showToast('error', 'Data cannot be downloaded');
      this.setState({ exportLoading: false });
    }
  }
  render() {
    const { gettext } = this.context.textdomain;
    const { header, showSummary, showDatePicker, showExportButton } =
      this.props;
    const { tableLoading, tableReloading, summary, total, exportLoading } =
      this.state;
    const summaryIsNotEmpty = Object.keys(summary).length > 0;
    const activeTab = this.getActiveTab();
    const columns = activeTab ? activeTab.columns : [];
    const summaryIsNotHidden = activeTab && !activeTab.hideSummary;
    const datePickerIsNotHidden = activeTab && !activeTab.hideDatePicker;

    if (tableLoading) {
      return <Loading />;
    }

    const tabs = this.getTabs();
    const tableHeader = this.getTableHeader();
    const tableBody = this.getTableBody();
    const filters = this.getFilters();
    const itemsPerPageSelect = this.getItemsPerPageSelect();
    const pagination = this.getPagination();

    return (
      <div className="card">
        <div className="card-body">
          <h4 className="card-title">{gettext(header)}</h4>
          {tabs}
          <div className="table-responsive">
            <div className="container-fluid">
              <div className="row m-t-25 m-b-15">
                <div className="col-sm-12 col-md-6">{itemsPerPageSelect}</div>
                <div className="col-sm-12 col-md-6">
                  {showDatePicker && datePickerIsNotHidden && (
                    <div className="row no-gutters align-items-center">
                      <DateRangeSelect
                        name={'Date'}
                        date={this.state.date}
                        showDatePicker={this.state.showDatePicker}
                        onDateChange={this.onDateChange.bind(this)}
                        selectDateValue={this.state.dateRangeType}
                        selectedRange={this.onRangeSelect.bind(this)}
                      />
                    </div>
                  )}
                  <div className="text-right loader-small">
                    {showExportButton &&
                      (exportLoading ? (
                        <Loading />
                      ) : (
                        <button
                          type="button"
                          className="btn btn-outline-info m-r-10"
                          onClick={this.downloadCSV.bind(this)}
                        >
                          <i className="mdi mdi-download m-r-10"></i>
                          {gettext('Export to CSV')}
                        </button>
                      ))}
                  </div>
                </div>
              </div>
              <div className="row">
                <div className="col">
                  <table className="table table-striped table-bordered datatable-select-inputs">
                    <thead>
                      <tr>{tableHeader}</tr>
                    </thead>
                    <tbody>
                      {filters}
                      {tableReloading ? (
                        <tr>
                          <td colSpan={columns.length}>
                            <div
                              style={{ position: 'relative', height: '64px' }}
                            >
                              <Loading />
                            </div>
                          </td>
                        </tr>
                      ) : (
                        tableBody
                      )}
                      {filters}
                      {showSummary &&
                        summaryIsNotEmpty &&
                        summaryIsNotHidden && (
                          <tr key="sum_row">
                            {columns.map(({ name, filterType }) => {
                              const currencySign = this.getColumnCurrency(name);
                              const sumContent = parseFloat(
                                summary[name],
                              ).toFixed(2);
                              return (
                                <td key={`summary_${name}`}>
                                  {filterType === List.filters.amount &&
                                    name in summary && (
                                      <b>
                                        SUM: {sumContent}
                                        {currencySign}
                                      </b>
                                    )}
                                </td>
                              );
                            })}
                          </tr>
                        )}
                      {total && (
                        <tr key="total_row">
                          {columns.map(({ name }) => {
                            const currencySign = this.getColumnCurrency(name);
                            const totalContent = parseFloat(
                              total[name],
                            ).toFixed(2);
                            return (
                              <td key={`total_${name}`}>
                                {name in total && (
                                  <b>
                                    TOTAL: {totalContent}
                                    {currencySign}
                                  </b>
                                )}
                              </td>
                            );
                          })}
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
              {pagination}
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default List;
