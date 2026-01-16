import React, { useEffect, useState, useContext } from 'react';
import CrmContext from '../../../helpers/context';
import Loading from '../../elements/loading';
import axios from '../../../helpers/interceptors';
import Multiselect from 'multiselect-react-dropdown';
import DatePicker from 'react-date-picker';
import Swal from 'sweetalert2';
import ReactDOMServer from 'react-dom/server';

const LtechManualDraw = () => {
  const [loading, setLoading] = useState(true);
  const [lotteries, setLotteries] = useState([]);
  const [chosenLotterySlug, setLotterySlug] = useState();
  const [selectedNormalNumbers, setSelectedNormalNumbers] = useState();
  const [selectedBonusNumbers, setSelectedBonusNumbers] = useState();
  const [nextJackpot, setNextJackpot] = useState('');
  const [nextDrawDate, setNextDrawDate] = useState('');
  const [prizes, setPrizes] = useState({});
  const [winners, setWinners] = useState({});
  const [additionalNumber, setAdditionalNumber] = useState('');

  const context = useContext(CrmContext);

  const formIsValid = () => {
    const normalNumbersAreValid =
      Array.isArray(selectedNormalNumbers) &&
      selectedNormalNumbers.length ===
        parseInt(currentLottery.normal_numbers_count);
    const bonusNumbersAreValid =
      currentLottery.bonus_numbers_count > 0
        ? Array.isArray(selectedBonusNumbers) &&
          selectedBonusNumbers.length ===
            parseInt(currentLottery.bonus_numbers_count)
        : true;
    const additionalNumberIsValid = currentLottery.additionalNumberName
      ? Array.isArray(additionalNumber) &&
        0 in additionalNumber &&
        additionalNumber[0]?.id >= 0
      : true;
    const isThereNoEmptyPrize =
      Object.entries(prizes).filter(([matchSlug, prize]) => prize === '')
        .length === 0;
    const isThereNoEmptyWin =
      Object.entries(winners).filter(([matchSlug, count]) => count === '')
        .length === 0;
    const isNextDrawAfterCurrentDrawDate =
      nextDrawDate > new Date(currentLottery?.next_date_local);
    return (
      currentLottery &&
      normalNumbersAreValid &&
      bonusNumbersAreValid &&
      nextJackpot > 0 &&
      isNextDrawAfterCurrentDrawDate &&
      Object.entries(prizes).length === currentLottery.tiers.length &&
      Object.entries(winners).length === currentLottery.tiers.length &&
      isThereNoEmptyPrize &&
      isThereNoEmptyWin &&
      additionalNumberIsValid
    );
  };

  const getNextDrawDateFormatted = () => {
    return new Date(
      Date.UTC(
        nextDrawDate.getFullYear(),
        nextDrawDate.getMonth(),
        nextDrawDate.getDate(),
      ),
    )
      .toISOString()
      .slice(0, 10);
  };

  const generateHtmlSummary = () => (
    <div style={{ textAlign: 'left' }}>
      <b>Lottery name:</b> {currentLottery?.name} <br />
      <b>Current draw:</b> {currentLottery?.next_date_local} (
      {currentLottery?.timezone})<br />
      <b>Lottery slug:</b> {currentLottery?.slug} <br />
      <br />
      <b>Main numbers: </b>
      {selectedNormalNumbers.map((number) => number.name).join(', ')} <br />
      {currentLottery?.bonus_numbers_count > 0 && (
        <div>
          <b>Bonus numbers: </b>
          {selectedBonusNumbers.map((number) => number.name).join(', ')}
        </div>
      )}
      {currentLottery?.additionalNumberName && (
        <div>
          <b>{currentLottery.additionalNumberName.toUpperCase()} number: </b>
          {additionalNumber[0].id}
        </div>
      )}
      <br />
      <b>Future Jackpot:</b> {nextJackpot} {currentLottery?.currency_code}
      <br />
      <b>Next draw date:</b> {getNextDrawDateFormatted()} (
      {currentLottery?.timezone})
      <br />
      <br />
      <b>Prizes: </b>(format: match-normal_numbers-bonus_numbers) <br />
      {Object.entries(prizes).map(([matchSlug, prize]) => (
        <div key={`confirm-prizes-${matchSlug}`}>
          <b>{matchSlug}:</b> {prize} {currentLottery?.currency_code}
        </div>
      ))}
      <br />
      <b>Winners: </b>(format: match-normal_numbers-bonus_numbers)
      {Object.entries(winners).map(([matchSlug, amount]) => (
        <div key={`confirm-winners-${matchSlug}`}>
          <b>{matchSlug}:</b> {amount}
        </div>
      ))}
    </div>
  );

  const showSummary = () => {
    Swal.fire({
      title: 'Do you confirm this draw?',
      icon: 'warning',
      input: 'password',
      width: 600,
      html: ReactDOMServer.renderToString(generateHtmlSummary()),
      showLoaderOnConfirm: true,
      showCancelButton: true,
      confirmButtonText: 'I confirm with a password',
      cancelButtonText: 'Cancel',
    }).then((result) => {
      if (result.isConfirmed) {
        const password = result.value;
        axios
          .post('/crm/addLtechManualDraw', {
            normalNumbers: selectedNormalNumbers.map((number) => number.id),
            bonusNumbers: (selectedBonusNumbers || []).map(
              (number) => number.id,
            ),
            nextDrawDate: getNextDrawDateFormatted(),
            nextJackpot,
            prizes,
            winners,
            currentLottery,
            password,
            additionalNumber: Array.isArray(additionalNumber)
              ? additionalNumber[0].id
              : null,
          })
          .then(({ data }) => {
            const isWrongRequest = data.code !== 200;
            if (isWrongRequest) {
              context.showToast(
                'error',
                data.message || 'Something wrong while adding new draw',
              );
              return;
            }

            context.showToast(
              'success',
              'Draw was added to the queue successfully! Wait few minutes until draw processes.',
            );
            clear();
            setLotterySlug(null);
            fetchLotteriesWaitingForDraw();
          });
      }
    });
  };

  const fetchLotteriesWaitingForDraw = () => {
    axios.post('/crm/lotteriesWaitingForDraw').then(({ data }) => {
      setLotteries(data);
    });
  };

  useEffect(() => {
    context.verifyToken();
    setLoading(false);
    fetchLotteriesWaitingForDraw();
  }, []);

  const setPrizeAmount = (matchSlug, amount) => {
    let newPrizes = { ...prizes };
    amount = parseFloat(amount);
    const isEmpty = isNaN(amount);
    if (isEmpty) {
      newPrizes[matchSlug] = '';
      setPrizes(newPrizes);
    } else {
      newPrizes[matchSlug] = amount;
      setPrizes(newPrizes);
    }
  };

  const setWinnersCount = (matchSlug, count) => {
    let newWinners = { ...winners };
    count = parseFloat(count);
    const isEmpty = isNaN(count);
    if (isEmpty) {
      newWinners[matchSlug] = '';
      setWinners(newWinners);
    } else {
      newWinners[matchSlug] = count;
      setWinners(newWinners);
    }
  };

  const isFreeTicketTier = (matchSlug) => {
    return currentLottery.slug === 'lotto-uk' && matchSlug === 'match-2';
  };

  const lotteriesOptions = lotteries.map((lottery) => {
    return (
      <option value={lottery.slug} key={lottery.slug}>
        {lottery.name} ({lottery.next_date_local} {lottery.timezone})
      </option>
    );
  });
  const currentLottery = lotteries.filter(
    (lottery) => lottery.slug === chosenLotterySlug,
  )[0];
  const shouldSetFreeTicketPrize =
    currentLottery?.slug === 'lotto-uk' && (prizes['match-2'] ?? null) !== 0.0;
  if (shouldSetFreeTicketPrize) {
    setPrizeAmount('match-2', 0);
  }

  const normalNumbersRange = chosenLotterySlug
    ? Array.from(
        new Array(parseInt(currentLottery?.normal_numbers_range) || 0),
        (x, i) => i + 1,
      )
    : [];
  const bonusNumbersRange = chosenLotterySlug
    ? Array.from(
        new Array(parseInt(currentLottery?.bonus_numbers_range) || 0),
        (x, i) => i + 1,
      )
    : [];
  const clear = () => {
    setSelectedNormalNumbers(null);
    setSelectedBonusNumbers(null);
    setNextJackpot('');
    setNextDrawDate('');
    setPrizes([]);
    setWinners([]);
    setAdditionalNumber('');
  };

  const normalNumbersOptions = [];
  normalNumbersRange.forEach((number) =>
    normalNumbersOptions.push({
      id: number,
      name: String(number),
    }),
  );
  const bonusNumbersOptions = [];
  bonusNumbersRange.forEach((number) =>
    bonusNumbersOptions.push({
      id: number,
      name: String(number),
    }),
  );

  const additionalNumbersRange = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
  const additionalNumbersOptions = [];
  additionalNumbersRange.forEach((number) =>
    additionalNumbersOptions.push({
      id: number,
      name: String(number),
    }),
  );
  const getMatchSlug = (normalNumbers, bonusNumbers, additionalNumber) => {
    switch (currentLottery.slug) {
      case 'bonoloto':
        if (normalNumbers === 0 && additionalNumber) {
          return 'match-r';
        }

        bonusNumbers = bonusNumbers > 0 ? `-c` : '';
        break;
      case 'el-gordo-primitiva':
        if (normalNumbers === 0 && bonusNumbers === 0 && additionalNumber) {
          return 'match-k';
        }

        const additionalNumbersTiers = [2, 3, 4, 5];
        if (
          additionalNumbersTiers.includes(normalNumbers) &&
          bonusNumbers === 0 &&
          additionalNumber
        ) {
          bonusNumbers = '-k';
        } else {
          bonusNumbers = '';
        }
        break;
      case 'la-primitiva':
        if (normalNumbers === 0 && bonusNumbers === 0 && additionalNumber) {
          return 'match-r';
        }
        if (normalNumbers === 6 && bonusNumbers === 0 && additionalNumber) {
          return 'match-6-r';
        }
        bonusNumbers = bonusNumbers > 0 ? `-c` : '';
        break;
      case 'lotto-6aus49':
        const superNumberTiers = [2, 3, 4, 5, 6];
        if (
          superNumberTiers.includes(normalNumbers) &&
          bonusNumbers === 0 &&
          additionalNumber
        ) {
          bonusNumbers = '-s';
        } else {
          bonusNumbers = '';
        }
        break;
      case 'lotto-america':
        bonusNumbers = bonusNumbers > 0 ? '-s' : '';
        break;
      case 'lotto-at':
        if (normalNumbers === 0 && bonusNumbers === 1) {
          return 'match-a';
        }
        bonusNumbers = bonusNumbers > 0 ? '-a' : '';
        break;
      case 'lotto-fr':
        if (normalNumbers === 0 && bonusNumbers === 1) {
          return 'match-c';
        }
        bonusNumbers = bonusNumbers > 0 ? '-c' : '';
        break;
      case 'mega-millions':
        bonusNumbers = bonusNumbers > 0 ? '-m' : '';
        break;
      case 'powerball':
        bonusNumbers = bonusNumbers > 0 ? '-p' : '';
        break;
      case 'saturday-lotto-au':
        bonusNumbers = bonusNumbers > 0 ? '-s' : '';
        break;
      case 'set-for-life-uk':
        bonusNumbers = bonusNumbers > 0 ? '-l' : '';
        break;
      case 'thunderball':
        bonusNumbers = bonusNumbers > 0 ? '-t' : '';
        break;
      case 'lotto-uk':
        bonusNumbers = bonusNumbers > 0 ? '-b' : '';
        break;
      default:
        bonusNumbers = bonusNumbers > 0 ? `-${bonusNumbers}` : '';
        break;
    }
    return `match-${normalNumbers}${bonusNumbers}`;
  };

  const prizesAndWinnersForm = currentLottery ? (
    <div className="mt-5">
      <h4>Prizes and winners</h4>
      <div className="row pb-3 border-bottom align-items-center">
        <div className="col-4">
          <h4>Tier (match):</h4>
        </div>
        <div className="col-4">
          <h4>Prize amount (per winner):</h4>
        </div>
        <div className="col-4">
          <h4>Winners count:</h4>
        </div>
      </div>
      {currentLottery.tiers.map(
        ({ normal_numbers, bonus_numbers, additional_number }) => {
          const matchSlug = getMatchSlug(
            normal_numbers,
            bonus_numbers,
            additional_number,
          );
          return (
            <div
              className="row mt-2 mb-2 border-bottom align-items-center"
              key={matchSlug}
            >
              <div className="col-4">
                <b>({matchSlug})</b> <br />
                {isFreeTicketTier(matchSlug) ? (
                  <div>Free ticket</div>
                ) : (
                  <div>
                    Main numbers: {normal_numbers} <br />
                    Bonus numbers: {bonus_numbers}
                    {additional_number && (
                      <div>
                        + {currentLottery.additionalNumberName.toUpperCase()}{' '}
                        number
                      </div>
                    )}
                  </div>
                )}
              </div>
              <div className="col-4">
                <div className="input-group">
                  <input
                    type="number"
                    key={`prize-${matchSlug}`}
                    onChange={(e) => {
                      if (isFreeTicketTier(matchSlug)) {
                        return;
                      }

                      setPrizeAmount(matchSlug, e.target.value);
                    }}
                    disabled={isFreeTicketTier(matchSlug)}
                    value={matchSlug in prizes ? prizes[matchSlug] : ''}
                    className="form-control"
                    placeholder="Enter prize amount"
                    min="0"
                    step="0.01"
                  />
                  <div className="input-group-append">
                    <span className="input-group-text">
                      {currentLottery.currency_sign}
                    </span>
                  </div>
                </div>
              </div>
              <div className="col-4">
                <input
                  type="text"
                  inputMode="numeric"
                  key={`winners-${matchSlug}`}
                  onChange={(e) => {
                    setWinnersCount(matchSlug, e.target.value);
                  }}
                  value={matchSlug in winners ? winners[matchSlug] : ''}
                  className="form-control"
                  placeholder="Enter winners count"
                  min="0"
                  step="1"
                />
              </div>
            </div>
          );
        },
      )}
    </div>
  ) : null;

  const drawForm = currentLottery ? (
    <div>
      <div className="mt-4">
        <label>
          Main numbers:
          <Multiselect
            options={normalNumbersOptions}
            selectedValues={selectedNormalNumbers}
            onSelect={setSelectedNormalNumbers}
            onRemove={setSelectedNormalNumbers}
            selectionLimit={parseInt(currentLottery?.normal_numbers_count) || 0}
            displayValue="name"
            placeholder="Select main numbers..."
          />
        </label>
      </div>
      {currentLottery?.bonus_numbers_count > 0 && (
        <div className="mt-2">
          <label>
            Bonus numbers:
            <Multiselect
              options={bonusNumbersOptions}
              selectedValues={selectedBonusNumbers}
              onSelect={setSelectedBonusNumbers}
              onRemove={setSelectedBonusNumbers}
              selectionLimit={
                parseInt(currentLottery?.bonus_numbers_count) || 0
              }
              displayValue="name"
              placeholder="Select bonus numbers..."
            />
          </label>
        </div>
      )}
      {currentLottery?.additionalNumberName && (
        <div className="mt-2">
          <label>
            {currentLottery.additionalNumberName.toUpperCase()} number:
            <Multiselect
              options={additionalNumbersOptions}
              selectedValues={additionalNumber}
              onSelect={setAdditionalNumber}
              onRemove={setAdditionalNumber}
              selectionLimit={1}
              displayValue="name"
              placeholder="Select additional number..."
            />
          </label>
        </div>
      )}
      <div className="mt-2">
        <label>
          Future jackpot (next draw):
          <div className="input-group mb-3">
            <input
              type="number"
              value={nextJackpot}
              onChange={(e) => {
                const value = parseFloat(e.target.value);
                setNextJackpot(isNaN(value) ? '' : value);
              }}
              className="form-control"
              placeholder="Enter future jackpot"
              min="0"
              step="0.01"
            />
            <div className="input-group-append">
              <span className="input-group-text">
                {currentLottery.currency_sign}
              </span>
            </div>
          </div>
        </label>
      </div>
      <div className="mt-2">
        <label>
          Next draw date (timezone: {currentLottery.timezone}):
          <DatePicker
            className="form-control"
            calendarIcon={null}
            onChange={setNextDrawDate}
            value={nextDrawDate}
          />
        </label>
      </div>
    </div>
  ) : null;

  return loading ? (
    <Loading />
  ) : (
    <div>
      <div>
        <label>
          <h3>Draw:</h3>
          <select
            className="form-control"
            onChange={(e) => {
              clear();
              setLotterySlug(e.target.value);
            }}
            value={chosenLotterySlug || 'DEFAULT'}
          >
            <option disabled value={'DEFAULT'}>
              Choose draw
            </option>
            {lotteriesOptions}
          </select>
        </label>
      </div>
      {drawForm}
      {prizesAndWinnersForm}
      {currentLottery && (
        <button
          className="btn btn-danger btn-lg btn-block mt-5 p-l-20 m-r-20"
          onClick={showSummary}
          disabled={!formIsValid()}
        >
          {'Add draw'}
        </button>
      )}
    </div>
  );
};

export default LtechManualDraw;
