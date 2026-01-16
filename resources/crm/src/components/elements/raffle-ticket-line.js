import React from 'react';
import CrmContext from '../../helpers/context';
import DataWithTooltip from './data-with-tooltip';

class RaffleTicketLine extends React.Component {
  constructor(props) {
    super(props);
  }

  getNumberClass(number) {
    const { line } = this.props;
    let drawNumbers = line.draw_numbers;
    let addClass = '';
    let primaryClass = 'raffle-number';

    if (drawNumbers) {
      if (drawNumbers.indexOf(number) > -1) {
        addClass = ' ticket-line-number-win';
      } else {
        addClass = ' ticket-line-number-nowin';
      }
    }
    return primaryClass + addClass;
  }

  render() {
    const { line } = this.props;
    const { gettext } = this.context.textdomain;

    return (
      <div>
        <span className={this.getNumberClass(line.number)}>{line.number}</span>
        {line['draw'] && (
          <span className="d-inline">
            {line['raffle_prize_id'] ? (
              <span>
                <strong>{gettext('Status') + ': '}</strong>
                {gettext('Win')} <strong>{gettext('Prize') + ': '}</strong>
                {
                  <DataWithTooltip
                    additionalClass="d-inline"
                    value={line['prize_display']}
                    tooltipValue={line['other_prizes']}
                  />
                }{' '}
              </span>
            ) : (
              <span>
                <strong>{gettext('Status') + ': '}</strong>
                {gettext('No Winnings')}
              </span>
            )}
          </span>
        )}
      </div>
    );
  }
}

RaffleTicketLine.contextType = CrmContext;
export default RaffleTicketLine;
