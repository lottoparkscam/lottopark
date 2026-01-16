import React from 'react';
import { Redirect, Route } from 'react-router-dom';
import DefaultLayout from '../components/layouts/default';
import { CrmConsumer } from '../helpers/context';

export const PrivateRoute = ({ pageName, showTitle, children, ...rest }) => {
  return (
    <CrmConsumer>
      {(modContext) =>
        modContext && (
          <Route
            {...rest}
            render={({ location, props }) =>
              modContext.token ? (
                <DefaultLayout pageName={pageName} showTitle={showTitle}>
                  {React.Children.map(children, (el) =>
                    React.cloneElement(el, { ...props }),
                  )}
                </DefaultLayout>
              ) : (
                <Redirect
                  to={{
                    pathname: '/login',
                    state: { from: location },
                  }}
                />
              )
            }
          />
        )
      }
    </CrmConsumer>
  );
};

export const AdminRoute = ({ pageName, showTitle, children, ...rest }) => {
  return (
    <CrmConsumer>
      {(modContext) =>
        modContext && (
          <Route
            {...rest}
            render={({ location, props }) =>
              modContext.token ? (
                modContext.user.role_id === '1' ||
                modContext.user.role_id === '3' ? (
                  <DefaultLayout pageName={pageName} showTitle={showTitle}>
                    {React.Children.map(children, (el) =>
                      React.cloneElement(el, { ...props }),
                    )}
                  </DefaultLayout>
                ) : (
                  <Redirect
                    to={{
                      pathname: '/403',
                      state: { from: location },
                    }}
                  />
                )
              ) : (
                <Redirect
                  to={{
                    pathname: '/login',
                    state: { from: location },
                  }}
                />
              )
            }
          />
        )
      }
    </CrmConsumer>
  );
};

export const ProtectedRoute = ({
  pageName,
  showTitle,
  modName,
  children,
  ...rest
}) => {
  return (
    <CrmConsumer>
      {(modContext) =>
        modContext && (
          <Route
            {...rest}
            render={({ location, props }) =>
              modContext.token ? (
                modContext.user.role_id == 1 ||
                modContext.modules.findIndex(
                  (x) =>
                    x.module_name == modName &&
                    x.whitelabel_id == modContext.choosedWhitelabel.id,
                ) != -1 ? (
                  <DefaultLayout pageName={pageName} showTitle={showTitle}>
                    {React.Children.map(children, (el) =>
                      React.cloneElement(el, { ...props }),
                    )}
                  </DefaultLayout>
                ) : (
                  <Redirect
                    to={{
                      pathname: '/403',
                      state: { from: location },
                    }}
                  />
                )
              ) : (
                <Redirect
                  to={{
                    pathname: '/login',
                    state: { from: location },
                  }}
                />
              )
            }
          />
        )
      }
    </CrmConsumer>
  );
};
