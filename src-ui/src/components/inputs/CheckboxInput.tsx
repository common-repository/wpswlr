import React from 'react';
import { useInputId } from '../../hooks/id';

interface Props {
    name: string;
    label: string;
    checked: boolean;
    onChange: (enabled: boolean) => void;
}

export const CheckboxInput: React.FC<Props> = ({ name, label, checked, onChange }) => {
    const id = useInputId(name);

    return (
        <label htmlFor={id}>
            <input
                type="checkbox"
                id={id}
                name={name}
                checked={checked}
                onChange={() => onChange(!checked)}
            />
            {label}
        </label>
    );
};
