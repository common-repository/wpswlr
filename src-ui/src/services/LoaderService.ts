const $loaderContainer = jQuery('#progress-bar-container');

function show() {
    $loaderContainer.removeClass('invisible');
}

function hide() {
    $loaderContainer.addClass('invisible');
}

export const LoaderService = {
    show,
    hide,
};
