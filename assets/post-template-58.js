(function ($, wp, WPSWLR) {
    const { __ } = wp.i18n;

    function resetBlocks() {
        const blocks = WPSWLR.blocks.map((block) => {
            return wp.blocks.createBlock(block[0], block[1]);
        });

        wp.data.dispatch('core/editor').resetEditorBlocks(blocks);
    }

    wp.data.subscribe(() => {
        const $toolbar = $('.edit-post-header-toolbar');
        if (!$toolbar.length || $('#wpswlr-reset-button').length) {
            return;
        }

        const buttonTitle = __('Reset Template', 'wpswlr');
        const $button = $(`<button id="wpswlr-reset-button" type="button" class="components-button editor-post-preview is-button is-primary">${buttonTitle}</button>`);
        $button.on('click', () => {
            resetBlocks();
        });

        $toolbar.append($button);
    });

    const infoMessage = __('All imported Facebook posts will be generated with this template. Paragraphs with the text "[fb-content]" will be replaced with message from Facebook. Image, gallery, and video will be replaced with media loaded from Facebook.', 'wpswlr');
    wp.data.dispatch('core/notices').createInfoNotice(infoMessage, {
        isDismissible: false,
    });
})(window.jQuery, window.wp, window.WPSWLR);
