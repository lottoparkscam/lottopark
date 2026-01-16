import React from 'react';

const CrmContext = React.createContext({});

export const CrmProvider = CrmContext.Provider;
export const CrmConsumer = CrmContext.Consumer;
export default CrmContext;
