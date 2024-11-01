/* eslint-disable @typescript-eslint/naming-convention */
import { toast } from 'react-toastify';
import { FacebookError } from '../errors/FacebookError';
import { fmt, tr } from '../i18n';
import { AccessTokenResponse, AccountsResponse, DebugTokenResponse, FacebookResponse } from './Facebook.types';
import { HttpService } from './HttpService';

const SDK_URL = 'https://connect.facebook.net/en_US/sdk.js';
let SDK_LOADED = false;

async function ensureScriptLoaded() {
    if (SDK_LOADED) {
        return Promise.resolve();
    }
    SDK_LOADED = true;
    return new Promise((resolve, reject) => {
        jQuery
            .getScript(SDK_URL)
            .done(() => resolve(null))
            .fail(() => {
                SDK_LOADED = false;
                reject(new FacebookError(0, tr.fb.sdkLoadFailed));
            });
    });
}

async function initSDK(appId?: string) {
    await ensureScriptLoaded();
    FB.init({
        appId,
        version: window.WPSWLR.facebook_info.api_version,
    });
}

async function facebookRequest<T extends FacebookResponse>(
    path: string,
    params: Record<string, number | string>
): Promise<T> {
    return new Promise<T>((resolve, reject) => {
        FB.api<Record<string, number | string>, T>(path, params, (response: T) => {
            if (!response) {
                reject(new FacebookError(0, tr.fb.unknownError));
            } else if (response.error) {
                reject(new FacebookError(response.error.code, response.error.message));
            } else {
                resolve(response);
            }
        });
    });
}

async function getExtendedTokenWithCustomApp(appId: string, appSecret: string) {
    const loginPromise: Promise<string | null> = new Promise((resolve, reject) => {
        FB.login(
            (loginResponse) => {
                if (loginResponse.status === 'unknown' || loginResponse.status === 'not_authorized') {
                    resolve(null);
                } else if (loginResponse.status === 'authorization_expired') {
                    reject(new FacebookError(0, tr.fb.authExpired));
                } else if (!loginResponse.authResponse || !loginResponse.authResponse.accessToken) {
                    reject(new FacebookError(0, tr.fb.tokenFailed));
                } else {
                    resolve(loginResponse.authResponse.accessToken);
                }
            },
            {
                auth_type: 'reauthorize',
                scope: window.WPSWLR.facebook_info.scope,
                return_scopes: true,
            }
        );
    });

    const userAccessToken = await loginPromise;
    if (!userAccessToken) {
        return null;
    }
    const tokenResponse = await facebookRequest<AccessTokenResponse>('/oauth/access_token', {
        grant_type: 'fb_exchange_token',
        client_id: appId,
        client_secret: appSecret,
        fb_exchange_token: userAccessToken,
    });
    if (!tokenResponse.access_token) {
        throw new FacebookError(0, tr.fb.tokenFailed);
    }
    return tokenResponse.access_token;
}

async function getUserAccounts(accessToken: string) {
    const response = await facebookRequest<AccountsResponse>('/me/accounts', {
        access_token: accessToken,
        fields: 'id,access_token,name,about,picture{url}',
        limit: '100',
    });
    return response.data;
}

async function debugToken(token: string) {
    const response = await facebookRequest<DebugTokenResponse>('/debug_token', {
        access_token: token,
        input_token: token,
    });
    if (!response.data) {
        throw new FacebookError(0, tr.fb.unknownError);
    }
    const { expires_at, scopes } = response.data;
    if (scopes.indexOf('pages_read_engagement') < 0 || scopes.indexOf('pages_read_user_content') < 0) {
        throw new FacebookError(0, tr.fb.missingScopes);
    }
    if (expires_at) {
        const now = Date.now() / 1000;
        // expired
        if (now > expires_at) {
            throw new FacebookError(0, tr.fb.tokenExpired);
        }
        // will expire soon (30 days)
        if (expires_at - now < 2592000) {
            const expiration = new Date(expires_at * 1000).toLocaleString();
            toast.warn(fmt(tr.fb.tokenAlmostExpired, expiration), { autoClose: false });
        }
    }
}

async function getAccountsFromCustomApp(appId: string, appSecret: string) {
    await initSDK(appId);
    const accessToken = await getExtendedTokenWithCustomApp(appId, appSecret);
    return accessToken ? getUserAccounts(accessToken) : undefined;
}

async function connectPageWithToken(token: string, pageId: string) {
    await initSDK();
    await debugToken(token);
    return HttpService.post('facebook/connect', { token, id: pageId });
}

async function disconnectPage() {
    return HttpService.post('facebook/disconnect');
}

async function updateData() {
    return HttpService.post('facebook/update');
}

export const FacebookService = {
    getAccountsFromCustomApp,
    connectPageWithToken,
    disconnectPage,
    updateData,
};
