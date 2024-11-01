import React, { useEffect, useMemo, useState } from 'react';
import { toast } from 'react-toastify';
import { fmt, tr } from '../i18n';
import { Options } from '../model';
import { LoaderService } from '../services/LoaderService';
import { CategoriesInputRow } from './inputs/CategoriesInputRow';
import { CheckboxInput } from './inputs/CheckboxInput';
import { CheckboxInputRow } from './inputs/CheckboxInputRow';
import { NumberInputRow } from './inputs/NumberInputRow';
import { PatternRow } from './inputs/PatternRow';
import { TextInputRow } from './inputs/TextInputRow';

interface Props {
    options: Options | undefined;
    loadData: () => Promise<void>;
    saveOptions: (options: Options) => Promise<void>;
}

function formatTemplateUrl({ id, type }: { id: number; type: string }) {
    return id
        ? window.WPSWLR.post_edit_url.replace('{post_id}', `${id}`)
        : window.WPSWLR.post_create_url.replace('{post_type}', type);
}

function validate(form: Options): Record<string, string[]> {
    const messages: Record<string, string[]> = {};
    if (form.post_excerpt_length === null || form.post_excerpt_length === undefined) {
        messages.post_excerpt_length = [tr.app.requiredInvalid];
    } else if (form.post_excerpt_length < 5) {
        messages.post_excerpt_length = [fmt(tr.app.minInvalid, 5)];
    }

    if (!form.post_excerpt_text) {
        messages.post_excerpt_text = [tr.app.requiredInvalid];
    }

    if (!form.posts_slug) {
        messages.posts_slug = [tr.app.requiredInvalid];
    } else {
        const m: string[] = [];
        if (form.posts_slug.length < 3) {
            m.push(fmt(tr.app.minLengthInvalid, 3));
        } else if (form.posts_slug.length > 20) {
            m.push(fmt(tr.app.maxLengthInvalid, 20));
        }
        if (!/^[\da-z]([\da-z-]*[\da-z])*$/.test(form.posts_slug)) {
            m.push(tr.app.slugInvalid);
        }
        if (m.length) {
            messages.posts_slug = m;
        }
    }

    if (form.posts_limit === null || form.posts_limit === undefined) {
        messages.posts_limit = [tr.app.requiredInvalid];
    } else if (form.posts_limit < 0) {
        messages.posts_limit = [fmt(tr.app.minInvalid, 0)];
    }

    return messages;
}

export const FacebookSettings: React.FC<Props> = ({ options, loadData, saveOptions }) => {
    const [form, setForm] = useState<Options | undefined>();
    const [errors, setErrors] = useState<Record<string, string[]>>({});

    useEffect(() => {
        if (options) {
            setForm({ ...options });
        }
    }, [options]);

    const templateUrls = useMemo(() => {
        if (!options?.templates) {
            return {};
        }
        const { general, album, video } = options.templates;
        return {
            general: formatTemplateUrl(general),
            album: formatTemplateUrl(album),
            video: formatTemplateUrl(video),
        };
    }, [options]);

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        if (!form) {
            return;
        }
        const errorMessages = validate(form);
        setErrors(errorMessages);
        if (Object.keys(errorMessages).length) {
            return;
        }

        LoaderService.show();
        toast.dismiss('fb-sv-done');
        toast.dismiss('fb-sv-err');
        saveOptions(form)
            .then(() => toast.info(tr.fb.settings.saveDone, { toastId: 'fb-sv-done' }))
            .catch(() => toast.error(tr.fb.settings.saveFailed, { autoClose: false, toastId: 'fb-sv-err' }))
            .then(() => loadData())
            .catch(() => toast.error(tr.app.loadFailed, { autoClose: false, toastId: 'fb-sv-err' }))
            .finally(() => LoaderService.hide());
    }

    if (!options || !form) {
        return null;
    }

    return (
        <form onSubmit={handleSubmit}>
            <h2 className="title">{tr.fb.settings.renderingTitle}</h2>
            <table className="form-table">
                <tbody>
                    <TextInputRow
                        name="post"
                        label={tr.fb.settings.postsTitle}
                        description={tr.fb.settings.postsTitleDesc}
                        value={form.posts_title ?? ''}
                        onChange={(v) => setForm((prev) => prev && { ...prev, posts_title: v })}
                        errors={errors.posts_title}
                    />
                    <CheckboxInputRow
                        name="load-thumbnails"
                        label={tr.fb.settings.loadThumbnails}
                        description={tr.fb.settings.loadThumbnailsDesc}
                        checked={form.load_thumbnails}
                        onChange={(v) => setForm((prev) => prev && { ...prev, load_thumbnails: v })}
                    />
                    <CheckboxInputRow
                        name="load-thumbnails-album"
                        label={tr.fb.settings.loadThumbnailsAlbum}
                        description={tr.fb.settings.loadThumbnailsAlbumDesc}
                        checked={form.load_thumbnails_album}
                        onChange={(v) => setForm((prev) => prev && { ...prev, load_thumbnails_album: v })}
                    />
                    <CheckboxInputRow
                        name="post-excerpt"
                        label={tr.fb.settings.postExcerpt}
                        description={tr.fb.settings.postExcerptDesc}
                        checked={form.post_excerpt}
                        onChange={(v) => setForm((prev) => prev && { ...prev, post_excerpt: v })}
                    />
                    <NumberInputRow
                        name="post-excerpt-length"
                        label={tr.fb.settings.postExcerptLength}
                        value={form.post_excerpt_length}
                        onChange={(v) => setForm((prev) => prev && { ...prev, post_excerpt_length: v })}
                        errors={errors.post_excerpt_length}
                    />
                    <TextInputRow
                        name="post-excerpt-text"
                        label={tr.fb.settings.postExcerptText}
                        value={form.post_excerpt_text}
                        onChange={(v) => setForm((prev) => prev && { ...prev, post_excerpt_text: v })}
                        errors={errors.post_excerpt_text}
                    />
                </tbody>
            </table>

            <h2 className="title">{tr.fb.settings.importTitle}</h2>
            <table className="form-table">
                <tbody>
                    <TextInputRow
                        name="posts-slug"
                        label={tr.fb.settings.postsSlug}
                        description={tr.fb.settings.postsSlugDesc}
                        value={form.posts_slug}
                        onChange={(v) => setForm((prev) => prev && { ...prev, posts_slug: v })}
                        errors={errors.posts_slug}
                    />
                    <tr>
                        <th>{tr.fb.settings.enabledPosts}</th>
                        <td>
                            <fieldset>
                                <CheckboxInput
                                    name="load-videos"
                                    label={tr.fb.settings.loadVideos}
                                    checked={form.load_video_posts}
                                    onChange={(v) => setForm((prev) => prev && { ...prev, load_video_posts: v })}
                                />
                                <br />
                                <CheckboxInput
                                    name="load-albums"
                                    label={tr.fb.settings.loadAlbums}
                                    checked={form.load_album_posts}
                                    onChange={(v) => setForm((prev) => prev && { ...prev, load_album_posts: v })}
                                />
                            </fieldset>
                        </td>
                    </tr>
                    <PatternRow
                        label={tr.fb.settings.includePatterns}
                        description={tr.fb.settings.includePatternsDesc}
                        value={form.load_include_patterns}
                        onChange={(v) => setForm((prev) => prev && { ...prev, load_include_patterns: v })}
                    />
                    <PatternRow
                        label={tr.fb.settings.excludePatterns}
                        description={tr.fb.settings.excludePatternsDesc}
                        value={form.load_exclude_patterns}
                        onChange={(v) => setForm((prev) => prev && { ...prev, load_exclude_patterns: v })}
                    />
                    <NumberInputRow
                        name="max-loaded-posts"
                        label={tr.fb.settings.postsLimit}
                        description={tr.fb.settings.postsLimitDesc}
                        value={form.posts_limit}
                        onChange={(v) => setForm((prev) => prev && { ...prev, posts_limit: v })}
                        errors={errors.posts_limit}
                    />
                </tbody>
            </table>

            <h2 className="title">{tr.fb.settings.categoriesTitle}</h2>
            <p>{tr.fb.settings.categoriesDesc}</p>
            <table className="form-table">
                <tbody>
                    <CategoriesInputRow
                        name="posts-cat-id-general"
                        label={tr.fb.settings.generalCid}
                        description={tr.fb.settings.generalCidDesc}
                        value={form.post_category_general}
                        onChange={(v) => setForm((prev) => prev && { ...prev, post_category_general: v })}
                    />
                    <CategoriesInputRow
                        name="posts-cat-id-album"
                        label={tr.fb.settings.albumCid}
                        value={form.post_category_album}
                        onChange={(v) => setForm((prev) => prev && { ...prev, post_category_album: v })}
                    />
                    <CategoriesInputRow
                        name="posts-cat-id-video"
                        label={tr.fb.settings.videoCid}
                        value={form.post_category_video}
                        onChange={(v) => setForm((prev) => prev && { ...prev, post_category_video: v })}
                    />
                </tbody>
            </table>

            <h2 className="title">{tr.fb.settings.templatesTitle}</h2>
            <p>{tr.fb.settings.templatesDescription}</p>
            <ul>
                <li>
                    <a href={templateUrls.general}>{tr.fb.settings.generalTemplate}</a>
                </li>
                <li>
                    <a href={templateUrls.album}>{tr.fb.settings.albumTemplate}</a>
                </li>
                <li>
                    <a href={templateUrls.video}>{tr.fb.settings.videoTemplate}</a>
                </li>
            </ul>

            <p className="submit">
                <button
                    type="submit"
                    className="button button-primary"
                >
                    {tr.fb.settings.save}
                </button>
            </p>
        </form>
    );
};
