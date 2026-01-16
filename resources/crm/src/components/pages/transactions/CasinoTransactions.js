import React, { useState, useContext, useEffect } from 'react';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';
import List from '../../elements/list';

const ENDPOINT = '/crm/casino_transactions';

const CasinoTransactions = () => {
  const [loading, setLoading] = useState(true);
  const [tabs, setTabs] = useState([]);
  const context = useContext(CrmContext);

  useEffect(() => {
    context.verifyToken();
    setLoading(false);
  }, []);

  useEffect(() => {
    prepareTabs();
  }, [context.isSuperadmin()]);

  const prepareTabs = () => {
    let defaultColumns = [
      {
        name: 'token',
        label: 'Token',
        filterType: List.filters.search,
      },
      {
        name: 'is_canceled',
        label: 'Is Canceled',
        specialFormat: List.specialFormats.boolean,
        filterType: List.filters.boolean,
      },
      {
        name: 'created_at',
        label: 'Date',
        filterType: List.filters.date,
      },
      {
        name: 'canceled_at',
        label: 'Canceled At',
        filterType: List.filters.date,
      },
      {
        name: 'amount',
        label: 'Amount',
        filterType: List.filters.amount,
      },
      {
        name: 'type',
        label: 'Type',
        filterType: List.filters.search,
      },
      {
        name: 'provider_transaction_id',
        label: "Slot Provider's Id",
        filterType: List.filters.search,
      },
      {
        name: 'whitelabel_user.email',
        label: 'User email',
        filterType: List.filters.search,
      },
      {
        name: 'whitelabel_user.login',
        label: 'User Login',
        filterType: List.filters.search,
      },
      {
        name: 'slot_game.name',
        label: 'Game Name',
        filterType: List.filters.search,
      },
      {
        name: 'slot_game.provider',
        label: 'Game Provider',
        filterType: List.filters.search,
      },
      {
        name: 'currency.code',
        label: 'Currency',
        filterType: List.filters.search,
      },
    ];

    if (context.isSuperadmin()) {
      defaultColumns.splice(0, 0, {
        name: 'whitelabel_slot_provider.whitelabel.name',
        label: 'Whitelabel Name',
        filterType: List.filters.search,
      });
      defaultColumns.push(
        {
          name: 'whitelabel_slot_provider.slot_provider.slug',
          label: 'Slot Provider',
          specialFormat: List.specialFormats.beautifySlug,
          filterType: List.filters.search,
        },
        {
          name: 'additional_data',
          label: 'Additional Data',
          specialFormat: List.specialFormats.json,
          filterType: List.filters.search,
        },
      );
    }

    const tabs = [
      {
        name: 'all',
        label: 'All',
        icon: 'ti-home',
        columns: [
          ...defaultColumns,
          {
            name: 'action',
            label: 'Action',
            filterType: List.filters.search,
          },
        ],
      },
      {
        name: 'bet',
        label: 'Bet',
        icon: 'ti-shopping-cart',
        columns: defaultColumns,
      },
      {
        name: 'win',
        label: 'Win',
        icon: 'ti-cup',
        columns: defaultColumns,
      },
      {
        name: 'refund',
        label: 'Refund',
        icon: 'ti-back-right',
        columns: defaultColumns,
      },
      {
        name: 'rollback',
        label: 'Rollback',
        icon: 'ti-back-right',
        columns: defaultColumns,
      },
    ];

    setTabs(tabs);
  };

  return loading ? (
    <Loading />
  ) : (
    <div className="row">
      <div className="col-12">
        <List
          tabs={tabs}
          endpoint={ENDPOINT}
          tabsDatabaseField="action"
          columnNameToFilterByDate="created_at"
          showDatePicker
        />
      </div>
    </div>
  );
};

export default CasinoTransactions;
