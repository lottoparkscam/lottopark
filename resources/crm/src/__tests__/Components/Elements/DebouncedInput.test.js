import {render, waitFor, screen, fireEvent} from '@testing-library/react';
import '@testing-library/jest-dom';
import DebouncedInput from "../../../components/elements/DebouncedInput";

let newValue = 'oldValue';

beforeEach(() => {
    render(
        <DebouncedInput
            type={'tel'}
            className={'own-class'}
            defaultValue={12345}
            onChange={(value) => newValue = value}
            waitTime={1}
        />
    );
});

test('Change input', async () => {
    // Given
    let input = await waitFor(() => screen.getByDisplayValue('12345'));
    expect(input.type).toBe('tel');
    expect(input.classList.contains('own-class')).toBeTruthy();

    // When
    fireEvent.change(input, {target: {value: 987}});

    // Then
    expect(input.value).toBe('987');
    await waitFor(() => expect(newValue).toBe('987'));
});
