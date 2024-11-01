import React from 'react';
import { RadioInput } from './RadioInput';
import style from './RadioInputGroup.module.scss';

interface Props {
    name: string;
    options: Record<string, string>;
    value: string;
    onChange: (value: string) => void;
    className?: string;
}

export const RadioInputGroup: React.FC<Props> = ({ name, options, value, onChange, className }) => {
    return (
        <div className={className}>
            {Object.entries(options).map(([v, label]) => (
                <RadioInput
                    className={style.input}
                    key={v}
                    name={name}
                    label={label}
                    value={v}
                    checked={value === v}
                    onChange={onChange}
                />
            ))}
        </div>
    );
};

RadioInputGroup.defaultProps = {
    className: undefined,
};
