import React, { useState } from 'react';
import { toast } from 'react-toastify';
import { tr } from '../i18n';
import { Info } from '../model';
import { FacebookService } from '../services/FacebookService';
import { LoaderService } from '../services/LoaderService';
import style from './FacebookInfo.module.scss';

interface Props {
    info: Info;
    loadData: () => Promise<void>;
    onReconnect: () => void;
}

export const FacebookInfo: React.FC<Props> = ({ info, loadData, onReconnect }) => {
    const [updating, setUpdating] = useState(false);

    function disconnect() {
        toast.dismiss();
        LoaderService.show();
        FacebookService.disconnectPage()
            .catch((e) => toast.error(e.message, { autoClose: false }))
            .then(() => loadData())
            .catch(() => toast.error(tr.app.loadFailed, { autoClose: false }))
            .finally(() => LoaderService.hide());
    }

    function updateData() {
        if (updating) {
            return;
        }
        setUpdating(true);
        LoaderService.show();
        toast.dismiss('fb-upd-err');
        FacebookService.updateData()
            .catch((e) => toast.error(e.message, { autoClose: false, toastId: 'fb-upd-err' }))
            .finally(() => {
                setUpdating(false);
                LoaderService.hide();
            });
    }

    return (
        <div>
            <div className={style.cardBody}>
                {info.picture && (
                    <div className={style.cardImage}>
                        <img
                            src={info.picture}
                            alt={info.name}
                        />
                    </div>
                )}
                <div className={style.cardContent}>
                    <h2 className="title">{info.name}</h2>
                    <p>
                        {info.username && (
                            <span>
                                {tr.fb.info.userName} {info.username}
                            </span>
                        )}
                        {info.username && info.about && <br />}
                        {info.about && <span>{info.about}</span>}
                    </p>
                </div>
            </div>
            <div className={style.cardButtons}>
                <button
                    type="button"
                    className="button-link"
                    onClick={disconnect}
                >
                    {tr.fb.info.disconnect}
                </button>
                <button
                    type="button"
                    className="button-link"
                    onClick={onReconnect}
                >
                    {tr.fb.info.reconnect}
                </button>
                <button
                    type="button"
                    className={`button-link ${updating ? style.disabled : ''}`}
                    onClick={updateData}
                >
                    {tr.fb.info.update}
                </button>
            </div>
        </div>
    );
};
