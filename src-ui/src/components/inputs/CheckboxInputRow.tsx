import React from 'react';
import { useInputId } from '../../hooks/id';

interface Props {
    name: string;
    label: string;
    description?: string;
    checked: boolean;
    onChange: (enabled: boolean) => void;
}

export const CheckboxInputRow: React.FC<Props> = ({ name, label, description, checked, onChange }) => {
    const id = useInputId(name);

    return (
        <tr>
            <th>
                <label htmlFor={id}>{label}</label>
            </th>
            <td>
                <label htmlFor={id}>
                    <input
                        type="checkbox"
                        id={id}
                        name={name}
                        checked={checked}
                        onChange={() => onChange(!checked)}
                        className="regular-text"
                    />
                    {description}
                </label>
            </td>
        </tr>
    );
};

CheckboxInputRow.defaultProps = {
    description: undefined,
};
