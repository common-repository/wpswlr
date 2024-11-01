(function (wp) {
    const { lockPostAutosaving } = wp.data.dispatch('core/editor');
    if (lockPostAutosaving) {
        lockPostAutosaving('wpswlr-block-autosave');
    }

    const { removeEditorPanel, switchEditorMode } = wp.data.dispatch('core/edit-post');
    [
        // 'taxonomy-panel-category', // category
        'taxonomy-panel-post_tag', // tags
        'featured-image', // featured image
        'post-link', // permalink
        'post-excerpt', // Excerpt
        'discussion-panel', // Discussion
        // 'page-attributes', // page attributes
    ].forEach(b => removeEditorPanel(b));
    switchEditorMode('visual');
})(window.wp);
