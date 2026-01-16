import React from 'react';
import { withRouter } from 'react-router-dom';
import CrmContext from '../../../helpers/context';
import axios from '../../../helpers/interceptors';
import DataWithTooltip from '../../elements/data-with-tooltip';
import Loading from '../../elements/loading';
import RaffleTicketLine from '../../elements/raffle-ticket-line';

class RaffleTicketDetails extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      ticket: {},
      lines: [],
      images: [],
    };
  }

  componentDidMount() {
    this.context.verifyToken();
    this.fetchData();
  }

  fetchData() {
    let token = this.props.match.params.id;
    let whitelabel_id = this.context.choosedWhitelabel.id;
    axios
      .post('/crm/raffle_tickets/details', {
        token,
        whitelabel_id,
      })
      .then((res) => {
        if (res.data.code == 200) {
          let ticket = res.data.details;
          let lines = res.data.lines;
          let images = res.data.images;
          this.setState({
            ticket,
            lines,
            images,
            loading: false,
          });
        }
      });
  }

  redirect() {
    this.props.history.goBack();
  }

  render() {
    const { ticket, lines, images, loading } = this.state;
    const { gettext } = this.context.textdomain;

    let whitelabel = this.context.whitelabels.find(
      (x) => ticket.whitelabel_id == x.id,
    );

    return loading ? (
      <Loading />
    ) : (
      <div className="row">
        <div className="col-lg-12">
          <div className="card">
            <div className="card-body">
              <h3 className="card-title">{ticket['full_token']}</h3>
              <h6 className="card-subtitle">
                {gettext('Here you can view raffle ticket details.')}
              </h6>
              <div className="row">
                <div className="col-sm-12">
                  <p>
                    <button
                      className="btn btn-primary btn-rounded"
                      onClick={this.redirect.bind(this)}
                    >
                      {gettext('Go Back')}
                    </button>
                  </p>
                </div>
                <div className="col-lg-12 col-md-12 col-sm-12">
                  <div className="table-responsive">
                    <table className="table">
                      <tbody>
                        <tr>
                          <td width="390">{gettext('ID')}</td>
                          <td>{ticket['full_token']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Transaction ID')}</td>
                          <td>{ticket['transaction_full_token']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('User ID')}</td>
                          <td>{ticket['user_full_token']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('First Name')}</td>
                          <td>{ticket['name']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Last Name')}</td>
                          <td>{ticket['surname']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('E-mail')}</td>
                          <td>{ticket['email']}</td>
                        </tr>
                        {whitelabel.use_logins_for_users == '1' && (
                          <tr>
                            <td>{gettext('Login')}</td>
                            <td>{ticket['user_login']}</td>
                          </tr>
                        )}
                        <tr>
                          <td>{gettext('Raffle')}</td>
                          <td>{ticket['rname']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Date')}</td>
                          <td>{ticket['created_at']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Draw date')}</td>
                          <td>{ticket['draw_date']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Amount')}</td>
                          <td>
                            <DataWithTooltip
                              value={ticket['amount_display']}
                              tooltipValue={ticket['amounts_other']}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Bonus amount')}</td>
                          <td>
                            <DataWithTooltip
                              value={ticket['bonus_amount_display']}
                              tooltipValue={ticket['bonus_amounts_other']}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Cost')}</td>
                          <td>
                            <DataWithTooltip
                              value={ticket['cost_display']}
                              tooltipValue={ticket['costs_other']}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Income')}</td>
                          <td>
                            <DataWithTooltip
                              value={ticket['income_display']}
                              tooltipValue={ticket['incomes_other']}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Royalties')}</td>
                          <td>
                            <DataWithTooltip
                              value={ticket['margin_display']}
                              tooltipValue={ticket['margins_other']}
                            />
                          </td>
                        </tr>
                        <tr>
                          <td>{gettext('Status')}</td>
                          <td>{ticket['status_display']}</td>
                        </tr>
                        <tr>
                          <td>{gettext('Paid out')}</td>
                          <td className={ticket['payout_class']}>
                            {ticket['payout_text']}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div className="card">
            <div className="card-body">
              <h4 className="card-title m-t-10 p-b-20">{gettext('Lines')}</h4>
              {lines.map((line, i) => (
                <RaffleTicketLine key={i} line={line} />
              ))}
            </div>
          </div>
          {ticket['status_win'] && (
            <div className="card">
              <div className="card-body">
                <h4 className="card-title m-t-10 p-b-20">
                  {gettext('Total Prize')}
                </h4>
                {ticket['jackpot_prize_text']}{' '}
                <DataWithTooltip
                  additionalClass={'d-inline'}
                  value={ticket['prize_display']}
                  tooltipValue={ticket['prizes_other']}
                />
              </div>
            </div>
          )}
          {/* images.length > 0 && (
            <div className="card">
              <div className="card-body">
                <h4 className="card-title m-t-10 p-b-20">
                  {gettext("Slip images")}
                </h4>
                {images.map((image, i) => (
                  <img key={i} src={image} alt="" className="img-thumbnail" />
                ))}
              </div>
            </div>
          ) */}
        </div>
      </div>
    );
  }
}

RaffleTicketDetails.contextType = CrmContext;
export default withRouter(RaffleTicketDetails);
