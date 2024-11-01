export interface FacebookResponseError {
    message: string;
    type: string;
    code: number;
    error_subcode: number;
    error_user_title: string;
    error_user_msg: string;
    fbtrace_id: string;
}

export interface FacebookResponsePaging {
    cursors: {
        after?: string;
        before?: string;
    };
    previous?: string;
    next?: string;
}

export interface FacebookResponse {
    error?: FacebookResponseError;
}

export interface AccessTokenResponse extends FacebookResponse {
    access_token: string;
    token_type: string;
    expires_in: number;
}

export interface AccountsResponseData {
    id: string;
    access_token: string;
    name: string;
    about: string;
    picture: {
        data: {
            url: string;
        };
    };
}

export interface AccountsResponse extends FacebookResponse {
    data: AccountsResponseData[];
    paging: FacebookResponsePaging;
    summary: {
        total_count: number;
    };
}

export interface DebugTokenResponse extends FacebookResponse {
    data: {
        // app_id: string;
        // application: string;
        expires_at: number;
        scopes: string[];
    };
}
