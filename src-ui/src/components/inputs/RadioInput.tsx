import React from 'react';
import { useInputId } from '../../hooks/id';

interface Props {
    className?: string;
    name: string;
    label: string;
    value: string;
    checked: boolean;
    onChange: (value: string) => void;
}

export const RadioInput: React.FC<Props> = ({ className, name, label, value, checked, onChange }) => {
    const id = useInputId(`${name}-${value}`);

    return (
        <label
            htmlFor={id}
            className={className}
        >
            <input
                type="radio"
                id={id}
                name={name}
                value={value}
                checked={checked}
                onChange={(evt) => onChange(evt.target.value)}
            />
            {label}
        </label>
    );
};

RadioInput.defaultProps = {
    className: undefined,
};
