import React from 'react';
import { CrmConsumer } from '../../helpers/context';

function FormValid() {
  return (
    <CrmConsumer>
      {(modContext) =>
        modContext && (
          <div className="valid-feedback">
            {modContext.textdomain.gettext('Looks good!')}
          </div>
        )
      }
    </CrmConsumer>
  );
}

export default FormValid;
