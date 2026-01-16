import {getDomainForApi} from '../../helpers/UrlHelper';

let context;
beforeEach(() => {
    context = {
        choosedWhitelabel: {}
    };
});

describe('Get domain for api in CRM context', () => {
    test('default domain', () => {
        // Given
        delete window.location
        window.location = new URL('https://www.example.com')

        // When
        const domain = getDomainForApi(context);

        // Then
        expect(domain).toBe('lottopark.com');
    });

    test('default domain', () => {
        // Given
        delete window.location
        window.location = new URL('https://www.example.work')
        context.choosedWhitelabel.domain = 'hostname.test';

        // When
        const domain = getDomainForApi(context);

        // Then
        expect(domain).toBe('hostname.test');
    });
});
