import React, { useCallback, useEffect, useState } from 'react';
import { useInputId } from '../../hooks/id';

const numberRegex = /^[+-]?\d+([.,]\d*)?$/;

interface Props {
    name: string;
    label: string;
    description?: string;
    value: number | undefined;
    onChange: (value: number | undefined) => void;
    disableAutocomplete?: boolean;
    errors: string[] | undefined;
}

export const NumberInputRow: React.FC<Props> = ({
    name,
    label,
    description,
    value,
    onChange,
    disableAutocomplete,
    errors,
}) => {
    const [internalValue, setInternalValue] = useState('');
    const id = useInputId(name);

    useEffect(() => {
        setInternalValue((prevValue) => {
            if (value === undefined || value === null) {
                if (prevValue !== '-') {
                    return '';
                }
            } else {
                return `${value}`;
            }

            return prevValue;
        });
    }, [value]);

    const handleChange = useCallback(
        (event: React.ChangeEvent<HTMLInputElement>) => {
            const newValue = event.target.value;

            if (newValue !== undefined && onChange) {
                let numberValue: number | undefined;

                if (!newValue || newValue === '-') {
                    setInternalValue(newValue);
                    numberValue = undefined;
                } else if (newValue) {
                    const isNumber = numberRegex.test(newValue);
                    if (!isNumber) {
                        return;
                    }
                    setInternalValue(newValue);
                    numberValue = Number.parseFloat(newValue);
                }

                onChange(numberValue);
            }
        },
        [onChange]
    );

    return (
        <tr>
            <th>
                <label htmlFor={id}>{label}</label>
            </th>
            <td>
                <input
                    id={id}
                    name={name}
                    className="regular-text ltr"
                    autoComplete={disableAutocomplete ? 'off' : undefined}
                    value={internalValue}
                    onChange={handleChange}
                />
                {description && <p className="description">{description}</p>}
                {errors &&
                    errors.map((e) => (
                        <p
                            key="error"
                            className="description text-error"
                        >
                            {e}
                        </p>
                    ))}
            </td>
        </tr>
    );
};

NumberInputRow.defaultProps = {
    description: undefined,
    disableAutocomplete: false,
};
