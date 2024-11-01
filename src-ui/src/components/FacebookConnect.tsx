import React, { useState } from 'react';
import { tr } from '../i18n';
import { RadioInputGroup } from './inputs/RadioInputGroup';
import { FacebookConnectApp } from './FacebookConnectApp';
import { FacebookConnectToken } from './FacebookConnectToken';

interface Props {
    loadData: () => Promise<void>;
}

export const FacebookConnect: React.FC<Props> = ({ loadData }) => {
    const [method, setMethod] = useState<string>('TOKEN');

    return (
        <div>
            <RadioInputGroup
                name="method"
                options={{ TOKEN: tr.fb.connect.customToken, APP: tr.fb.connect.customApp }}
                value={method}
                onChange={setMethod}
            />

            {method === 'TOKEN' && (
                <>
                    <FacebookConnectToken loadData={loadData} />
                    <p>
                        {tr.fb.connect.appHint}
                        &nbsp;
                        <strong>
                            <a
                                href="https://wpswlr.bttrs.org/how-to/create-facebook-access-token"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {tr.fb.connect.tokenHintLink}
                            </a>
                        </strong>
                        .
                    </p>
                </>
            )}
            {method === 'APP' && (
                <>
                    <FacebookConnectApp loadData={loadData} />
                    <p>
                        {tr.fb.connect.tokenHint}
                        &nbsp;
                        <strong>
                            <a
                                href="https://wpswlr.bttrs.org/how-to/create-facebook-access-token"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {tr.fb.connect.tokenHintLink}
                            </a>
                        </strong>
                        .
                    </p>
                </>
            )}
        </div>
    );
};
