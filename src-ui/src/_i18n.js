// file used only for wp i18n extractor
/* eslint-disable @typescript-eslint/naming-convention */
const { __ } = window.wp.i18n;

const tr = {
    app: {
        categoriesLoadFailed: __('Failed to import categories.', 'wpswlr'),
        loadFailed: __('Loading of options failed', 'wpswlr'),
        loading: __('Loading...', 'wpswlr'),
        connectSuccessful: __('Facebook page was successfully connected.', 'wpswlr'),
        minInvalid: __('This field must be at least {0}.', 'wpswlr'),
        maxInvalid: __('This field must be at most {0}.', 'wpswlr'),
        minLengthInvalid: __('This field must be at least {0} character long.', 'wpswlr'),
        maxLengthInvalid: __('This field must be at most {0} characters long.', 'wpswlr'),
        requiredInvalid: __('This field is required.', 'wpswlr'),
        slugInvalid: __(
            "This field must contain only numbers, lowercase letters and - (dash). It can't start or end with dash.",
            'wpswlr'
        ),
        errorBoundaryHeader: __('Unexpected Error', 'wpswlr'),
        errorBoundaryText1: __('You can try to', 'wpswlr'),
        errorBoundaryText2: __('reload page', 'wpswlr'),
    },
    fb: {
        authExpired: __('Authorization Expired', 'wpswlr'),
        tokenExpired: __('Token is expired', 'wpswlr'),
        missingScopes: __('Missing required scopes: pages_read_engagement, pages_read_user_content', 'wpswlr'),
        sdkLoadFailed: __("Facebook SDK can't be loaded", 'wpswlr'),
        tokenAlmostExpired: __(
            'Access token will expire on {0}. Consider using an access token without expiration.',
            'wpswlr'
        ),
        tokenErrorReturned: __("Token can't be validated. Facebook returned an error: {0}.", 'wpswlr'),
        tokenFailed: __('Failed to get access token', 'wpswlr'),
        unknownError: __('Unknown Error', 'wpswlr'),
        info: {
            disconnect: __('Disconnect', 'wpswlr'),
            reconnect: __('Reconnect', 'wpswlr'),
            update: __('Update Facebook Data', 'wpswlr'),
            updateError: __(
                "This can be caused by an issue on the Facebook side. If this message won't disappear in 5 days, it could be an issue with your access token.",
                'wpswlr'
            ),
            updateErrorInfo: __('Loading page info from Facebook failed with an error:', 'wpswlr'),
            updateErrorPosts: __('Importing posts from Facebook failed with an error:', 'wpswlr'),
            userName: __('Username:', 'wpswlr'),
        },
        connect: {
            accountNotSelected: __('You need to select account you want to import.', 'wpswlr'),
            appId: __('App ID', 'wpswlr'),
            appSecret: __('App Secret', 'wpswlr'),
            connectPage: __('Connect Page', 'wpswlr'),
            customApp: __('Facebook App', 'wpswlr'),
            customToken: __('Token', 'wpswlr'),
            loadPages: __('Load Pages', 'wpswlr'),
            missingAppData: __('Both App ID and App Secret have to be set.', 'wpswlr'),
            missingTokenData: __('Both Token and Page ID have to be set.', 'wpswlr'),
            noPages: __("You don't have any facebook pages.", 'wpswlr'),
            pageId: __('Page ID', 'wpswlr'),
            resetPage: __('Reset', 'wpswlr'),
            token: __('Token', 'wpswlr'),
            tokenHint: __(
                "If you don't have a Facebook Access Token you can generate one by following our step-by-step tutorial",
                'wpswlr'
            ),
            appHint: __(
                "If you don't have a Facebook App you can create one by following our step-by-step tutorial",
                'wpswlr'
            ),
            tokenHintLink: __('step-by-step guide', 'wpswlr'),
            wrongAppId: __('App ID must be a numeric value.', 'wpswlr'),
            wrongPageId: __('Page ID must be a numeric value.', 'wpswlr'),
        },
        settings: {
            addItem: __('Add New Item', 'wpswlr'),
            albumCid: __('Album Category', 'wpswlr'),
            albumTemplate: __('Album Template', 'wpswlr'),
            categoriesDesc: __('Selected categories will be assigned to posts imported from Facebook', 'wpswlr'),
            categoriesTitle: __('Default categories', 'wpswlr'),
            enabledPosts: __('Enabled Post Types', 'wpswlr'),
            excludePatterns: __('Exclude Patterns', 'wpswlr'),
            excludePatternsDesc: __(
                "Only import posts that don't match any of exclude patterns. If no patterns are defined, posts won't be filtered. Patterns are compared case insensitive and can contain wildcard characters * (asterisk) and ? (question mark). * matches any number of any characters (non-greedy). ? matches a single character.",
                'wpswlr'
            ),
            generalCid: __('General Category', 'wpswlr'),
            generalCidDesc: __('Will be used if no other category applies.', 'wpswlr'),
            generalTemplate: __('General Template', 'wpswlr'),
            imageCid: __('Image Category', 'wpswlr'),
            includePatterns: __('Include Patterns', 'wpswlr'),
            includePatternsDesc: __(
                "Only import posts that match at least one of include patterns. If no patterns are defined, posts won't be filtered. Patterns are compared case insensitive and can contain wildcard characters * (asterisk) and ? (question mark). * matches any number of any characters (non-greedy). ? matches a single character.",
                'wpswlr'
            ),
            loadAlbums: __('Albums', 'wpswlr'),
            loadThumbnails: __('Photos as Featured Images', 'wpswlr'),
            loadThumbnailsAlbum: __('Galleries with Featured Images', 'wpswlr'),
            loadThumbnailsAlbumDesc: __('Create a Feature image from the first photo in album.', 'wpswlr'),
            loadThumbnailsDesc: __(
                'Import photos as Featured Images. If disabled, photos will be added to the post body.',
                'wpswlr'
            ),
            loadVideos: __('Videos', 'wpswlr'),
            postsLimit: __('Max Imported Posts', 'wpswlr'),
            postsLimitDesc: __('A value larger than 100 can cause performance issues.', 'wpswlr'),
            postsSlug: __('Posts slug prefix', 'wpswlr'),
            postsSlugDesc: __(
                'Prefix used in single post URL. For example: https://www.mysite.sk/wpswlr-facebook-post/my-post',
                'wpswlr'
            ),
            removeItem: __('Remove Item', 'wpswlr'),
            save: __('Save Changes', 'wpswlr'),
            saveDone: __('Settings successfully saved', 'wpswlr'),
            saveFailed: __('Failed to save settings', 'wpswlr'),
            templatesDescription: __(
                'You can modify the appearance of imported posts by editing templates. You can reorder blocks or change their format. For example, change text-align or set it bold. When posts are imported, image/album/video and text will be replaced with content from Facebook. Click on the links below to edit templates:',
                'wpswlr'
            ),
            importTitle: __('Import Options', 'wpswlr'),
            renderingTitle: __('Display Options', 'wpswlr'),
            postExcerpt: __('Show Excerpt in List', 'wpswlr'),
            postExcerptDesc: __(
                'Show only specified number of characters fom post in a list of posts. If post is longer, add link to post.',
                'wpswlr'
            ),
            postsTitle: __('Posts title', 'wpswlr'),
            postsTitleDesc: __(
                'Title assigned to each imported post. Placeholder "{date}" will be replaced by date when post was published.',
                'wpswlr'
            ),
            postExcerptLength: __('Excerpt Length', 'wpswlr'),
            postExcerptText: __('Excerpt Link Text', 'wpswlr'),
            templatesTitle: __('Post Templates', 'wpswlr'),
            videoCid: __('Video Category', 'wpswlr'),
            videoTemplate: __('Video Template', 'wpswlr'),
        },
    },
    errorContainer: {
        dismiss: __('Dismiss this alert.', 'wpswlr'),
    },
};
