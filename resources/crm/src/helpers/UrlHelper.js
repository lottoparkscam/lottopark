export const getDomainForApi = (context) => {
  const whitelabel = context.choosedWhitelabel;
  const hostname = window.location.hostname.split('.');
  const suffix = hostname.slice(-1);
  return whitelabel.domain || `lottopark.${suffix}`;
};
