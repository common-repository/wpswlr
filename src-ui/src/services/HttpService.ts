import { HttpError } from '../errors/HttpError';

function request<T>(path: string, method: 'POST' | 'GET', data?: any): Promise<T> {
    const url =
        path.startsWith('/') || path.startsWith('http://') || path.startsWith('https://')
            ? path
            : `${window.WPSWLR.api.url}/${path}`;

    return new Promise((resolve, reject) => {
        jQuery
            .ajax({
                method: method || 'GET',
                url,
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', window.WPSWLR.api.nonce);
                },
                data,
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json',
            })
            .done((r: T) => {
                resolve(r);
            })
            .fail((jqXHR) => {
                reject(
                    new HttpError(
                        jqXHR.status,
                        jqXHR.statusText,
                        (jqXHR.responseJSON && jqXHR.responseJSON.message) || jqXHR.statusText
                    )
                );
            });
    });
}

function get<T = void>(path: string): Promise<T> {
    return request(path, 'GET');
}

function post<T = void>(path: string, data?: any): Promise<T> {
    return request(path, 'POST', data);
}

export const HttpService = {
    get,
    post,
};
