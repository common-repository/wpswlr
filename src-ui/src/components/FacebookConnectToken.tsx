import React, { useState } from 'react';
import { toast } from 'react-toastify';
import { LoaderService } from '../services/LoaderService';
import { FacebookService } from '../services/FacebookService';
import { tr } from '../i18n';
import { TextInputRow } from './inputs/TextInputRow';
import { NumberInputRow } from './inputs/NumberInputRow';
import commonStyle from './style.module.scss';
import style from './FacebookConnect.module.scss';

interface Props {
    loadData: () => Promise<void>;
}

export const FacebookConnectToken: React.FC<Props> = ({ loadData }) => {
    const [pageId, setPageId] = useState<number | undefined>();
    const [token, setToken] = useState('');

    function handleConnectToken() {
        toast.dismiss();

        if (!token || !pageId) {
            toast.warn(tr.fb.connect.missingTokenData);
            return;
        }
        if (!`${pageId}`.match(/^[0-9]+$/)) {
            toast.warn(tr.fb.connect.wrongPageId);
            return;
        }

        LoaderService.show();
        FacebookService.connectPageWithToken(token, `${pageId}`)
            .catch((e) => toast.error(e.message, { autoClose: false }))
            .then(() => loadData())
            .catch(() => toast.error(tr.app.loadFailed, { autoClose: false }))
            .finally(() => {
                LoaderService.hide();
                toast.info(tr.app.connectSuccessful);
            });
    }

    return (
        <>
            <table className="form-table">
                <tbody>
                    <NumberInputRow
                        value={pageId}
                        name="facebook-page-id"
                        label={tr.fb.connect.pageId}
                        onChange={setPageId}
                        errors={undefined}
                    />
                    <TextInputRow
                        value={token}
                        name="facebook-token"
                        label={tr.fb.connect.token}
                        disableAutocomplete
                        onChange={setToken}
                        errors={undefined}
                    />
                </tbody>
            </table>

            <p className={`submit ${commonStyle.submit}`}>
                <button
                    type="button"
                    className={`button button-primary ${style.button}`}
                    onClick={handleConnectToken}
                >
                    {tr.fb.connect.connectPage}
                </button>
            </p>
        </>
    );
};
