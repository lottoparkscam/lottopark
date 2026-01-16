import React from 'react';
import {render, screen, cleanup} from '@testing-library/react';
import '@testing-library/jest-dom';
import Foo from '../../src/components/Foo';

describe('Foo Component', () => {
    afterEach(cleanup)

    it('renders welcome message', async () => {
        render(<Foo/>);

        expect(await screen.findByText('Hello World!')).toBeInTheDocument();
    });

    it('renders foo list', async () => {
        render(<Foo/>);

        // 'test1' is the first element from API
        expect(await screen.findByText('test1')).toBeInTheDocument();
    });
});
