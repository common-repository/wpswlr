import React, { useCallback, useEffect, useState } from 'react';
import { toast } from 'react-toastify';
import { tr } from '../i18n';
import { LoaderService } from '../services/LoaderService';
import { useOptions } from '../hooks/options';
import { FacebookConnect } from '../components/FacebookConnect';
import { FacebookInfo } from '../components/FacebookInfo';
import { FacebookSettings } from '../components/FacebookSettings';
import style from './AppOptions.module.scss';

export const AppOptions: React.FC = () => {
    const [info, options, loadData, saveOptions] = useOptions();
    const [reconnect, setReconnect] = useState(false);

    useEffect(() => {
        LoaderService.show();

        loadData()
            .catch(() => toast.error(tr.app.loadFailed, { autoClose: false }))
            .finally(() => LoaderService.hide());
    }, [loadData]);

    const onReconnect = useCallback(() => {
        setReconnect((prev) => !prev);
    }, []);

    if (!info) {
        return null;
    }

    return (
        <>
            {info?.id && info.update_error?.info && (
                <div className="notice notice-error">
                    <p>
                        {tr.fb.info.updateErrorInfo}
                        <strong>{info.update_error.info}</strong>
                        {tr.fb.info.updateError}
                    </p>
                </div>
            )}
            {info?.id && info.update_error?.posts && (
                <div className="notice notice-error">
                    <p>
                        {tr.fb.info.updateErrorPosts}
                        <strong>{info.update_error.posts}</strong>
                        {tr.fb.info.updateError}
                    </p>
                </div>
            )}
            <div className={`card ${style.infoCard}`}>
                {!!info.id && (
                    <FacebookInfo
                        info={info}
                        loadData={loadData}
                        onReconnect={onReconnect}
                    />
                )}
                {!!info.id && reconnect && <div className={style.separator} />}
                {(!info.id || reconnect) && <FacebookConnect loadData={loadData} />}
            </div>
            {!!info.id && (
                <FacebookSettings
                    options={options}
                    saveOptions={saveOptions}
                    loadData={loadData}
                />
            )}
        </>
    );
};
