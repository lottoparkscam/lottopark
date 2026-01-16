import React, { useEffect, useState, useContext } from 'react';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';
import List from '../../elements/list';

const tabs = [
  {
    name: 'per_country',
    label: 'Per Country',
    icon: 'ti-home',
    columns: [
      {
        name: 'payment_method_name',
        label: 'Payment Method',
        filterType: List.filters.search,
        specialFormat: List.specialFormats.capitalizeWords,
      },
      {
        name: 'register_country_code',
        label: 'Register Country Code',
        filterType: List.filters.search,
      },
      {
        name: 'register_country',
        label: 'Register Country',
        filterType: List.filters.search,
      },
      {
        name: 'success_count',
        label: 'Success Count',
        filterType: List.filters.integer,
      },
      {
        name: 'failure_count',
        label: 'Failure Count (one per transaction)',
        filterType: List.filters.integer,
      },
      {
        name: 'success_rate',
        label: 'Success Rate',
        filterType: List.filters.percent,
      },
    ],
  },
  {
    name: 'per_method',
    label: 'Per Method',
    icon: 'ti-home',
    columns: [
      {
        name: 'payment_method_name',
        label: 'Payment Method',
        filterType: List.filters.search,
        specialFormat: List.specialFormats.capitalizeWords,
      },
      {
        name: 'success_count',
        label: 'Success Count',
        filterType: List.filters.integer,
      },
      {
        name: 'failure_count',
        label: 'Failure Count',
        filterType: List.filters.integer,
      },
      {
        name: 'success_rate',
        label: 'Success Rate',
        filterType: List.filters.percent,
      },
      {
        name: 'failure_rate',
        label: 'Fail Rate',
        filterType: List.filters.percent,
      },
    ],
  },
];

const AcceptanceRate = () => {
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
          endpoint={'/crm/acceptance_rate_report'}
          tabsDatabaseField=""
          showDatePicker
          showExportButton
        />
      </div>
    </div>
  );
};

export default AcceptanceRate;
