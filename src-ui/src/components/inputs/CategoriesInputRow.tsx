import React, { useEffect } from 'react';
import { useInputId } from '../../hooks/id';
import { useCategories } from '../../hooks/categories';
import { tr } from '../../i18n';

interface Props {
    name: string;
    label: string;
    description?: string;
    value: number;
    onChange: (value: number) => void;
}

export const CategoriesInputRow: React.FC<Props> = ({ name, label, description, value, onChange }) => {
    const id = useInputId(name);
    const [categories, loadCategories] = useCategories();

    useEffect(() => {
        loadCategories();
    }, [loadCategories]);

    return (
        <tr>
            <th>
                <label htmlFor={id}>{label}</label>
            </th>
            <td>
                {categories && (
                    <select
                        id={id}
                        name={name}
                        value={value}
                        onChange={(e) => onChange(Number.parseInt(e.target.value, 10))}
                    >
                        <option value={0}>--</option>
                        {categories.map((c) => (
                            <option
                                key={c.id}
                                value={c.id}
                            >
                                {'&nbsp;'.repeat(c.level * 3)}
                                {c.name}
                            </option>
                        ))}
                    </select>
                )}
                {!categories && (
                    <select id={id}>
                        <option value={0}>{tr.app.loading}</option>
                    </select>
                )}
                {description && <p className="description">{description}</p>}
            </td>
        </tr>
    );
};

CategoriesInputRow.defaultProps = {
    description: undefined,
};
