import Loading from '../../elements/loading';
import CrmContext from '../../../helpers/context';
import React, { useEffect, useState, useContext } from 'react';
import ReactDOMServer from 'react-dom/server';
import DebouncedInput from '../../elements/DebouncedInput';
import axios from '../../../helpers/interceptors';
import { getDomainForApi } from '../../../helpers/UrlHelper';

const Generator = () => {
  const context = useContext(CrmContext);
  useEffect(() => {
    context.verifyToken();

    fetchLotteries();
    fetchLanguages();
    fetchCurrencies();

    setLoading(false);
  }, []);

  const whitelabel = context.choosedWhitelabel;
  const domain = getDomainForApi(context);

  const widgetTypes = ['pickNumbers'];

  const [loading, setLoading] = useState(true);
  const [width, setWidth] = useState(400);
  const [height, setHeight] = useState(700);
  const [lotterySlug, setLotterySlug] = useState('powerball');
  const [widgetType, setWidgetType] = useState('pickNumbers');
  const [language, setLanguage] = useState();
  const [orderUrl, setOrderUrl] = useState();
  const [currencyCode, setCurrencyCode] = useState();
  const [lotteries, setLotteries] = useState([]);
  const [languages, setLanguages] = useState([]);
  const [currencies, setCurrencies] = useState([]);

  const fetchLotteries = () => {
    const path = `/crm/enabled_lotteries?whitelabel_id=${whitelabel.id}`;
    axios.get(path).then((response) => {
      const enabledLotteries = Object.entries(response.data);
      const keysToExclude = [
        '__by_id',
        '__by_slug',
        '__sort_lastdate',
        '__sort_nextdate',
      ];
      setLotteries(
        enabledLotteries
          .filter(
            ([key, lottery]) =>
              lottery.type !== 'keno' && !keysToExclude.includes(key),
          )
          .map(([key, lottery]) => lottery),
      );

      const firstLotterySlug = enabledLotteries[0]?.slug || 'powerball';
      setLotterySlug(firstLotterySlug);
    });
  };
  const fetchLanguages = () => {
    const path = `/crm/available_languages?whitelabel_id=${whitelabel.id}`;
    axios.get(path).then((response) => {
      setLanguages(response.data);
    });
  };
  const fetchCurrencies = () => {
    const path = `/crm/available_currencies?whitelabel_id=${whitelabel.id}`;
    axios.get(path).then((response) => {
      setCurrencies(response.data);
    });
  };

  const lotterySlugOptions = lotteries.map((lottery, index) => {
    return (
      <option value={lottery.slug} key={lottery.slug}>
        {lottery.name}
      </option>
    );
  });
  const widgetTypesOptions = widgetTypes.map((widgetType) => {
    return (
      <option value={widgetType} key={widgetType}>
        {widgetType}
      </option>
    );
  });
  const languagesOptions = languages.map(({ code, full_code: fullCode }) => {
    return (
      <option value={fullCode} key={code}>
        {code}
      </option>
    );
  });
  languagesOptions.unshift(
    <option key={'DEFAULT_LANGUAGE'} value={'DEFAULT'} disabled>
      Choose language
    </option>,
  );

  const currenciesOptions = currencies.map(({ code }) => {
    return (
      <option value={code} key={code}>
        {code}
      </option>
    );
  });
  currenciesOptions.unshift(
    <option key={'DEFAULT_CURRENCY'} value={'DEFAULT'} disabled>
      Choose currency
    </option>,
  );

  const widgetUrl = new URL(`https://api.${domain}/api/internal/seoWidgets/`);
  widgetUrl.searchParams.set('lotterySlug', lotterySlug);
  widgetUrl.searchParams.set('widgetType', widgetType);
  if (language) {
    widgetUrl.searchParams.set('language', language);
  }
  if (orderUrl) {
    widgetUrl.searchParams.set('orderUrl', orderUrl);
  }
  if (currencyCode) {
    widgetUrl.searchParams.set('currencyCode', currencyCode);
  }

  const iframe = (
    <iframe
      src={widgetUrl.toString()}
      width="100%"
      height={height}
      style={{ border: 'none', maxWidth: `${width}px` }}
    ></iframe>
  );
  const iframeCode = ReactDOMServer.renderToString(iframe).replace(
    /&amp;/g,
    '&',
  );

  const shortCode = `[seoWidget lottery_slug="${lotterySlug}" widget_type="${widgetType}" width="${width}" height="${height}"]`;

  return loading ? (
    <Loading />
  ) : (
    <div className="d-flex flex-row">
      <div style={{ width: '50%' }}>
        <fieldset>
          <legend>Basic</legend>

          <div className="input-group mb-3">
            <label>
              Lottery:
              <select
                onChange={(e) => setLotterySlug(e.target.value)}
                name="Lottery"
                value={lotterySlug}
                className="custom-select"
              >
                {lotterySlugOptions}
              </select>
            </label>
          </div>
          <div className="input-group mb-3">
            <label>
              Widget Type:
              <select
                onChange={(e) => setWidgetType(e.target.value)}
                name="Widget Type:"
                value={widgetType}
                className="custom-select"
              >
                {widgetTypesOptions}
              </select>
            </label>
          </div>
          <div className="input-group mb-3">
            <label>
              Width (px):
              <DebouncedInput
                className="form-control"
                defaultValue={width}
                onChange={setWidth}
              />
            </label>
          </div>
          <div className="input-group mb-3">
            <label>
              Height (px):
              <DebouncedInput
                className="form-control"
                defaultValue={height}
                onChange={setHeight}
              />
            </label>
          </div>
        </fieldset>
        <fieldset>
          <legend>Only for iframe</legend>

          <div className="input-group mb-3">
            <label>
              Language:
              <select
                onChange={(e) => setLanguage(e.target.value)}
                name="Language:"
                value={language || 'DEFAULT'}
                className="custom-select"
              >
                {languagesOptions}
              </select>
            </label>
          </div>
          <div className="input-group mb-3">
            <label>
              Order Url (with trailing slash at the end):
              <DebouncedInput
                className="form-control"
                defaultValue={''}
                onChange={setOrderUrl}
              />
            </label>
          </div>
          <label>
            Currency Code:
            <select
              onChange={(e) => setCurrencyCode(e.target.value)}
              name="Currency Code:"
              value={currencyCode || 'DEFAULT'}
              className="custom-select"
            >
              {currenciesOptions}
            </select>
          </label>
        </fieldset>
        <section>
          <div style={{ marginTop: '40px', marginRight: '40px' }}>
            Iframe:
            <br />
            <code>{iframeCode}</code>
          </div>
          <div style={{ marginTop: '20px', marginRight: '40px' }}>
            Wordpress ShortCode:
            <br />
            <code>{shortCode}</code>
          </div>
        </section>
      </div>
      {iframe}
    </div>
  );
};

export default Generator;
