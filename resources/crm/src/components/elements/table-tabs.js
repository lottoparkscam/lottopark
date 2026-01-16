import React from 'react';

class TableTabs extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { tabs, counts, activeTab, setActiveTab } = this.props;
    return (
      <ul className="nav nav-tabs" role="tablist">
        {tabs.map((tab) => (
          <li className="nav-item" key={tab.name}>
            <a
              className={
                'cursor-pointer nav-link ' + (activeTab == tab.name && 'active')
              }
              role="tab"
              onClick={() => {
                setActiveTab(tab.name);
              }}
            >
              <span className="hidden-sm-up">
                <i className={tab.icon}></i>
              </span>
              <span className="hidden-xs-down">
                {' ' + tab.title + ' (' + counts[tab.name] + ')'}
              </span>
            </a>
          </li>
        ))}
      </ul>
    );
  }
}
export default TableTabs;
