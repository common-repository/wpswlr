import type { I18n } from '@wordpress/i18n';

declare global {
    interface Window {
        WPSWLR: {
            public_url: string;
            rest_url: string;
            post_create_url: string;
            post_edit_url: string;
            api: {
                url: string;
                nonce: string;
            };
            facebook_info: {
                api_version: string;
                scope: string;
            };
        };
        wp: {
            i18n: I18n;
        };
    }
}
