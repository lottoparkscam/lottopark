import React from 'react';
import CrmContext from '../../helpers/context';
import DataWithTooltip from './data-with-tooltip';

class PricingValues extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { ticket } = this.props;
    const { gettext } = this.context.textdomain;

    return [
      ticket['model_name'] && (
        <p className="m-b-0" key="model">
          {gettext('Model') + ': ' + ticket['model_name']}
        </p>
      ),
      <p className="m-b-0" key="tier">
        {ticket['tier_display']}
      </p>,
      <DataWithTooltip
        key="cost"
        title={gettext('Cost')}
        value={ticket['cost_display']}
        tooltipValue={ticket['costs_other']}
      />,
      <DataWithTooltip
        key="income"
        title={gettext('Income')}
        value={ticket['income_display']}
        tooltipValue={ticket['incomes_other']}
      />,
      <DataWithTooltip
        key="royalties"
        title={gettext('Royalties')}
        value={ticket['margin_display']}
        tooltipValue={ticket['margins_other']}
      />,
      ticket['bonus_cost_display'] && (
        <DataWithTooltip
          key="bonus"
          title={gettext('Bonus')}
          value={ticket['bonus_cost_display']}
          tooltipValue={ticket['bonus_cost_other']}
        />
      ),
    ];
  }
}

PricingValues.contextType = CrmContext;
export default PricingValues;
