import React from 'react';
import CrmContext from '../../helpers/context';
import DataWithTooltip from './data-with-tooltip';
import Swal from 'sweetalert2';
import axios from '../../helpers/interceptors';

class TicketLine extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      numbers: null,
      bonusNumbers: null,
      drawNumbers: null,
      drawBonusNumbers: null,
      bextra: null,
      additionalData: null,
      additionalNumbers: null,
    };
  }

  componentDidMount() {
    const { line } = this.props;

    let numbers = null;
    if (line['numbers']) {
      numbers = line['numbers'].split(',');
      numbers.sort();
    }

    let bonusNumbers = null;
    if (line['bnumbers']) {
      bonusNumbers = line['bnumbers'].split(',');
      bonusNumbers.sort();
    }

    let drawNumbers = null;
    if (line['draw_numbers']) {
      drawNumbers = line['draw_numbers'].split(',');
    }

    let drawBonusNumbers = null;
    if (line['draw_bnumbers']) {
      drawBonusNumbers = line['draw_bnumbers'].split(',');
    }

    let bextra = null;
    if (line['bextra']) {
      bextra = line['bextra'];
    }

    let additionalData = null;
    if (line['slip_additional_data']) {
      additionalData = line['slip_additional_data'];
    }

    let additionalNumbers = null;
    if (line['slip_additional_data_numbers']) {
      additionalNumbers = line['slip_additional_data_numbers'];
    }

    this.setState({
      numbers,
      bonusNumbers,
      drawNumbers,
      drawBonusNumbers,
      bextra,
      additionalData,
      additionalNumbers,
    });
  }

  payout(offset) {
    const { gettext } = this.context.textdomain;
    const { ticket, fetchData } = this.props;

    let token = ticket.token;
    let whitelabel = ticket.whitelabel_id;

    Swal.fire({
      title: gettext('Are you sure?'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: gettext('Confirm'),
      cancelButtonText: gettext('Cancel'),
    }).then((result) => {
      if (result.value) {
        axios
          .post('/crm/tickets/payout', {
            token,
            whitelabel,
            offset,
          })
          .then((res) => {
            if (res.data.code == 200) {
              let message = res.data.message;
              this.context.showToast('success', message);
              fetchData();
            }
            if (res.data.code == 400) {
              let message = res.data.message;
              this.context.showToast('error', message);
            }
          });
      }
    });
  }

  getNumberClass(number) {
    const { drawNumbers, drawBonusNumbers, bextra } = this.state;
    let addClass = '';
    let primaryClass = 'ticket-line-number';

    if (drawNumbers) {
      if (drawNumbers.indexOf(number) > -1) {
        addClass = ' ticket-line-number-win';
      } else if (
        (parseInt(bextra) === 1 && drawBonusNumbers.indexOf(number) > -1) ||
        (parseInt(bextra) === 2 && drawBonusNumbers.indexOf(number) > -1)
      ) {
        addClass = ' ticket-line-number-win';
        primaryClass = 'ticket-line-bnumber';
      } else {
        addClass = ' ticket-line-number-nowin';
      }
    }
    return primaryClass + addClass;
  }

  getBonusNumberClass(bnumber) {
    const { drawBonusNumbers } = this.state;

    let addClass = '';
    if (drawBonusNumbers) {
      if (drawBonusNumbers.indexOf(bnumber) > -1) {
        addClass = ' ticket-line-number-win';
      } else {
        addClass = ' ticket-line-number-nowin';
      }
    }
    return addClass;
  }

  render() {
    const { gettext } = this.context.textdomain;
    const { line } = this.props;
    const { showLink } = this.context;
    const {
      numbers,
      bonusNumbers,
      drawNumbers,
      drawBonusNumbers,
      additionalData,
      additionalNumbers,
    } = this.state;

    let keyName = '';
    let ballName = '';
    let ballNameShort = '';
    let ballClassName = '';

    if (additionalData) {
      if (additionalData['refund']) {
        keyName = 'refund';
        ballName = 'Reintegro';
        ballNameShort = 'R';
        ballClassName = 'reintegro';
      } else if (additionalData['super']) {
        keyName = 'super';
        ballName = 'Super';
        ballNameShort = '';
        ballClassName = 'super';
      }
    }

    return (
      <div>
        <div className="ticket-line">
          {numbers &&
            numbers.map((number, i) => (
              <div key={i} className={this.getNumberClass(number)}>
                {number}
              </div>
            ))}
          {bonusNumbers &&
            bonusNumbers.map((bnumber, i) => (
              <div
                key={i}
                className={
                  'ticket-line-bnumber' + this.getBonusNumberClass(bnumber)
                }
              >
                {bnumber}
              </div>
            ))}
          {additionalData &&
            Object.keys(additionalData).map((adnumber, i) => (
              <div
                key={i}
                className={
                  'ticket-line-bnumber ' +
                  (Object.keys(additionalData).indexOf(keyName) > -1
                    ? ballClassName
                    : '') +
                  (additionalNumbers
                    ? additionalNumbers[keyName] == additionalData[adnumber]
                      ? ' ticket-line-number-win'
                      : ' ticket-line-number-nowin'
                    : '')
                }
              >
                {additionalData[adnumber]}
                <span>{ballNameShort}</span>
              </div>
            ))}
        </div>
        {line['draw'] && (
          <span className="d-inline">
            <strong>{gettext('Match') + ': '}</strong>
            {line['match']}{' '}
            {line['lottery_type_data_id'] ? (
              <span>
                <strong>{gettext('Prize') + ': '}</strong>
                <DataWithTooltip
                  additionalClass="d-inline"
                  value={line['prize_display']}
                  tooltipValue={line['other_prizes']}
                />{' '}
                <strong>{gettext('Status') + ': '}</strong>
                {line['payout']}
              </span>
            ) : (
              <span>
                <strong>{gettext('Status') + ': '}</strong>
                {gettext('No Winnings')}
              </span>
            )}
            {line['manual_confirm'] && showLink('tickets-edit') && (
              <button
                onClick={() => this.payout(line['lkey'])}
                className="btn btn-sm btn-success m-l-5"
              >
                <i className="mdi mdi-check"></i>{' '}
                {gettext('Pay out to user balance')}
              </button>
            )}
          </span>
        )}
      </div>
    );
  }
}

TicketLine.contextType = CrmContext;
export default TicketLine;
