import { useCallback, useState } from 'react';
import { HttpService } from '../services/HttpService';
import { Info, Options } from '../model';

export const useOptions = (): [
    Info | undefined,
    Options | undefined,
    () => Promise<void>,
    (options: Options) => Promise<void>
] => {
    const [info, setInfo] = useState<Info | undefined>();
    const [options, setOptions] = useState<Options | undefined>();

    const load = useCallback(async () => {
        const [i, o] = await Promise.all([
            HttpService.get<Info>('facebook/info'),
            HttpService.get<Options>('facebook/options'),
        ]);
        setInfo(i);
        setOptions(o);
    }, []);

    const saveOptions = useCallback((o: Options) => {
        return HttpService.post('facebook/options', o);
    }, []);

    return [info, options, load, saveOptions];
};
