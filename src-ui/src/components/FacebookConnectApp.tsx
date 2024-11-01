import React, { useState } from 'react';
import { toast } from 'react-toastify';
import { AccountsResponseData } from '../services/Facebook.types';
import { LoaderService } from '../services/LoaderService';
import { FacebookService } from '../services/FacebookService';
import { NumberInputRow } from './inputs/NumberInputRow';
import { TextInputRow } from './inputs/TextInputRow';
import { tr } from '../i18n';
import commonStyle from './style.module.scss';
import style from './FacebookConnect.module.scss';

interface Props {
    loadData: () => Promise<void>;
}

export const FacebookConnectApp: React.FC<Props> = ({ loadData }) => {
    const [appId, setAppId] = useState<number | undefined>();
    const [appSecret, setAppSecret] = useState<string>('');
    const [accounts, setAccounts] = useState<AccountsResponseData[] | undefined>();
    const [selectedAccount, setSelectedAccount] = useState<AccountsResponseData | undefined>();

    function handleLoadPages() {
        toast.dismiss();

        if (!appId || !appSecret) {
            toast.warn(tr.fb.connect.missingAppData);
            return;
        }
        if (!`${appId}`.match(/^[0-9]+$/)) {
            toast.warn(tr.fb.connect.wrongAppId);
            return;
        }

        LoaderService.show();
        FacebookService.getAccountsFromCustomApp(`${appId}`, appSecret)
            .then((acc) => setAccounts(acc))
            .catch((e) => toast.error(e.message, { autoClose: false }))
            .finally(() => LoaderService.hide());
    }

    function handleConnectApp() {
        toast.dismiss();

        if (!selectedAccount) {
            toast.warn(tr.fb.connect.accountNotSelected);
            return;
        }

        LoaderService.show();
        FacebookService.connectPageWithToken(selectedAccount.access_token, selectedAccount.id)
            .catch((e) => toast.error(e.message, { autoClose: false }))
            .then(() => loadData())
            .catch(() => toast.error(tr.app.loadFailed, { autoClose: false }))
            .finally(() => {
                LoaderService.hide();
                toast.info(tr.app.connectSuccessful);
            });
    }

    function handleConnectAppReset() {
        setAccounts(undefined);
        setSelectedAccount(undefined);
        toast.dismiss();
    }

    return (
        <>
            <table className="form-table">
                <tbody>
                    <NumberInputRow
                        value={appId}
                        name="facebook-app-id"
                        label={tr.fb.connect.appId}
                        onChange={setAppId}
                        errors={undefined}
                    />
                    <TextInputRow
                        value={appSecret}
                        name="facebook-app-secret"
                        label={tr.fb.connect.appSecret}
                        disableAutocomplete
                        onChange={setAppSecret}
                        errors={undefined}
                    />
                </tbody>
            </table>

            {accounts && (
                <div className={style.accountsList}>
                    {!accounts.length && <p className={style.noPages}>{tr.fb.connect.noPages}</p>}
                    {accounts.map((account) => (
                        <button
                            type="button"
                            key={account.id}
                            className={`${style.accountItem} ${selectedAccount === account ? style.active : ''}`}
                            onClick={() => setSelectedAccount(account)}
                        >
                            {account.picture && (
                                <div className={style.accountImage}>
                                    <img
                                        src={account.picture.data.url}
                                        alt={account.name}
                                    />
                                </div>
                            )}
                            <div className={style.accountText}>
                                <div className={style.accountName}>{account.name}</div>
                                {account.about && <div className={style.accountAbout}>{account.about}</div>}
                            </div>
                        </button>
                    ))}
                </div>
            )}

            {accounts ? (
                <p className={`submit ${commonStyle.submit}`}>
                    <button
                        type="button"
                        className={`button button-primary ${style.button}`}
                        disabled={!selectedAccount}
                        onClick={handleConnectApp}
                    >
                        {tr.fb.connect.connectPage}
                    </button>
                    <button
                        type="button"
                        className={`button button-secondary ${style.button}`}
                        onClick={handleConnectAppReset}
                    >
                        {tr.fb.connect.resetPage}
                    </button>
                </p>
            ) : (
                <p className={`submit ${commonStyle.submit}`}>
                    <button
                        type="button"
                        className={`button button-primary ${style.button}`}
                        onClick={handleLoadPages}
                    >
                        {tr.fb.connect.loadPages}
                    </button>
                </p>
            )}
        </>
    );
};
