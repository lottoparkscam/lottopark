import React from 'react';
import SwaggerUI from 'swagger-ui-react';
import 'swagger-ui-react/swagger-ui.css';

const ApiDoc = () => {
  const hostname = window.location.hostname.split('.');
  const suffix = hostname.slice(-1);
  const swaggerUrl = `https://api.lottopark.${suffix}/api/doc.json`;
  return <SwaggerUI url={swaggerUrl} />;
};

export default ApiDoc;
