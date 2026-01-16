import React, { useEffect, useState, useContext } from 'react';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';
import List from '../../elements/list';

const ggrColumns = [
  {
    name: 'bets',
    label: 'Bets',
    filterType: List.filters.amount,
  },
  {
    name: 'wins',
    label: 'Wins',
    filterType: List.filters.amount,
  },
  {
    name: 'ggr',
    label: 'GGR (Bets - Wins)',
    filterType: List.filters.amount,
  },
];

const tabs = [
  {
    name: 'per_whitelabel',
    label: 'GGR Per Whitelabel',
    icon: 'ti-home',
    columns: [
      {
        name: 'whitelabel_name',
        label: 'Whitelabel Name',
        filterType: List.filters.search,
        specialFormat: List.specialFormats.capitalizeWords,
      },
      ...ggrColumns,
    ],
  },
  {
    name: 'per_game',
    label: 'GGR Per Game',
    icon: 'ti-game',
    columns: [
      {
        name: 'game_name',
        label: 'Game Name',
        filterType: List.filters.search,
        specialFormat: List.specialFormats.capitalizeWords,
      },
      ...ggrColumns,
    ],
  },
  {
    name: 'per_game_provider',
    label: 'GGR Per Game Provider',
    icon: 'ti-user',
    columns: [
      {
        name: 'game_provider',
        label: 'Game Provider',
        filterType: List.filters.search,
        specialFormat: List.specialFormats.capitalizeWords,
      },
      ...ggrColumns,
    ],
  },
  {
    name: 'limits',
    label: 'V2 Limits',
    icon: 'ti-cut',
    columns: [
      {
        name: 'whitelabel_name',
        label: 'Whitelabel Name',
        filterType: List.filters.search,
        specialFormat: List.specialFormats.capitalizeWords,
      },
      {
        name: 'left_limit',
        label: 'Left Limit',
        filterType: List.filters.amount,
      },
    ],
    hideDatePicker: true,
    hideSummary: true,
    hideForV1: true,
  },
];

const Casino = () => {
  const CASINO_REPORT_ENDPOINT = '/crm/casino_report';
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
          endpoint={CASINO_REPORT_ENDPOINT}
          tabsDatabaseField=""
          showSummary
          showDatePicker
        />
      </div>
    </div>
  );
};

export default Casino;
