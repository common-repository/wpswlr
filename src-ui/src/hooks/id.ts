import { useMemo } from 'react';

function createId(name: string, prefix?: string) {
    return `wpswlr-${prefix}-${name}`;
}

export function useInputId(name: string) {
    return useMemo(() => createId(name, 'i'), [name]);
}
