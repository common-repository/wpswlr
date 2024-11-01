import React, { useMemo } from 'react';
import { tr } from '../../i18n';

interface Props {
    value: string;
    label: string;
    description?: string;
    onChange: (value: string) => void;
}

export const PatternRow: React.FC<Props> = ({ value, label, description, onChange }) => {
    const items = useMemo<string[]>(() => {
        try {
            return value ? JSON.parse(value) : [];
        } catch (e) {
            return [];
        }
    }, [value]);

    function handleChange(newItems: string[]) {
        onChange(JSON.stringify(newItems));
    }

    function itemChanged(val: string, idx: number) {
        const newItems = [...items];
        newItems[idx] = val;
        handleChange(newItems);
    }

    function addItem() {
        handleChange([...items, '']);
    }

    function removeItem(idx: number) {
        const newItems = [...items];
        newItems.splice(idx, 1);
        handleChange(newItems);
    }

    return (
        <tr>
            <th>{label}</th>
            <td>
                {items.map((item, idx) => (
                    <p key={`${idx + 1}`}>
                        <input
                            id="id"
                            type="text"
                            className="regular-text ltr"
                            value={item}
                            onChange={(e) => itemChanged(e.target.value, idx)}
                        />
                        <button
                            type="button"
                            className="button-link button-link-delete"
                            title={tr.fb.settings.removeItem}
                            onClick={() => removeItem(idx)}
                        >
                            &#x2716;
                        </button>
                    </p>
                ))}
                <p>
                    <button
                        type="button"
                        className="button button-add-item"
                        onClick={addItem}
                    >
                        {tr.fb.settings.addItem}
                    </button>
                </p>
                {description && <p>{description}</p>}
            </td>
        </tr>
    );
};

PatternRow.defaultProps = {
    description: undefined,
};
