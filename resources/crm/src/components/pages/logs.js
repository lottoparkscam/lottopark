import React, { useState, useContext, useEffect } from 'react';
import CrmContext from '../../helpers/context';
import Loading from '../elements/loading';
import List from '../elements/list';

const LOGS_ACTION_ENDPOINT = '/crm/logs_actions';
const defaultColumns = [
  {
    name: 'date',
    label: 'Date',
    filterType: List.filters.date,
  },
  {
    name: 'admin_user.email',
    label: 'User',
    filterType: List.filters.search,
  },
  {
    name: 'message',
    label: 'Message',
    filterType: List.filters.search,
  },
  {
    name: 'data',
    label: 'Data',
    specialFormat: List.specialFormats.json,
    filterType: List.filters.search,
  },
  {
    name: 'ip',
    label: 'IP',
    filterType: List.filters.search,
  },
  {
    name: 'browser',
    label: 'Browser',
    filterType: List.filters.search,
  },
  {
    name: 'operation_system',
    label: 'Operation System',
    filterType: List.filters.search,
  },
];
const tabs = [
  {
    name: 'all',
    label: 'All',
    icon: 'ti-home',
    columns: [
      ...defaultColumns,
      {
        name: 'module.name',
        label: 'Privileges',
        filterType: List.filters.search,
      },
    ],
  },
  {
    name: 'users-balance-edit',
    label: 'Users Balance Edit',
    icon: 'ti-money',
    columns: defaultColumns,
  },
  {
    name: 'users-bonus-balance-edit',
    label: 'Users Bonus Balance Edit',
    icon: 'ti-money',
    columns: defaultColumns,
  },
  {
    name: 'users-balance-casino-edit',
    label: 'Users Casino Balance Edit',
    icon: 'ti-money',
    columns: defaultColumns,
  },
  {
    name: 'users-manual-deposit-add',
    label: 'Users Manual Deposit',
    icon: 'ti-credit-card',
    columns: defaultColumns,
  },
  {
    name: 'users-bonus-balance-manual-deposit-add',
    label: 'Users Bonus Balance Manual Deposit',
    icon: 'ti-credit-card',
    columns: defaultColumns,
  },
  {
    name: 'users-manual-deposit-casino-add',
    label: 'Users Casino Balance Manual Deposit',
    icon: 'ti-credit-card',
    columns: defaultColumns,
  },
];

const Actions = () => {
  const [loading, setLoading] = useState(true);
  const context = useContext(CrmContext);

  useEffect(() => {
    context.verifyToken();
    setLoading(false);
  }, []);

  return loading ? (
    <Loading />
  ) : (
    <div className="row">
      <div className="col-12">
        <List
          tabs={tabs}
          endpoint={LOGS_ACTION_ENDPOINT}
          tabsDatabaseField="module.name"
        />
      </div>
    </div>
  );
};

export default Actions;
