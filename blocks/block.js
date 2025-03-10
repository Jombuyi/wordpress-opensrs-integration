(function (blocks, element, i18n, components) {
    var el = element.createElement;

    blocks.registerBlockType('opensrs/integration-form', {
        title: i18n.__('OpenSRS Integration Form', 'opensrs'),
        icon: 'shield',
        category: 'widgets',
        edit: function () {
            return el(
                'div',
                { className: 'opensrs-block-editor' },
                i18n.__('OpenSRS Integration Form will be displayed on the frontend.', 'opensrs')
            );
        },
        save: function () {
            // Save is handled dynamically via PHP render callback.
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.i18n,
    window.wp.components
);
