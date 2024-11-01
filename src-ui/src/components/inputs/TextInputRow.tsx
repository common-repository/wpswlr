import React from 'react';
import { useInputId } from '../../hooks/id';

interface Props {
    name: string;
    label: string;
    description?: string;
    value: string;
    onChange: (value: string) => void;
    disableAutocomplete?: boolean;
    errors: string[] | undefined;
}

export const TextInputRow: React.FC<Props> = ({
    name,
    label,
    description,
    value,
    onChange,
    disableAutocomplete,
    errors,
}) => {
    const id = useInputId(name);

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
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
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

TextInputRow.defaultProps = {
    description: undefined,
    disableAutocomplete: false,
};
