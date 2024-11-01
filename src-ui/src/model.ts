export type NoticeLevel = 'error' | 'warning' | 'info';

export interface Notice {
    level: NoticeLevel;
    text: string;
}

export interface Info {
    id: string;
    name: string;
    token: string;
    about?: string;
    picture?: string;
    update_error?: Record<string, string>;
    username?: string;
}

export interface Template {
    id: number;
    type: string;
}

export interface Options {
    load_video_posts: boolean;
    load_album_posts: boolean;
    load_include_patterns: string;
    load_exclude_patterns: string;
    load_thumbnails: boolean;
    load_thumbnails_album: boolean;
    posts_slug: string;
    posts_limit?: number;
    posts_title?: string;
    post_category_general: number;
    post_category_album: number;
    post_category_video: number;
    post_excerpt: boolean;
    post_excerpt_length?: number;
    post_excerpt_text: string;
    templates: {
        general: Template;
        album: Template;
        video: Template;
    };
}
