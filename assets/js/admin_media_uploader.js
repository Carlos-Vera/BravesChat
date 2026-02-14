/**
 * Admin Media Uploader
 * Handles WordPress Media Library integration for image fields
 *
 * @package BravesChat
 * @version 1.2.5
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Upload Media Button
        $('body').on('click', '.braves-upload-media', function (e) {
            e.preventDefault();

            var $button = $(this);
            var $container = $button.closest('.braves-media-uploader');
            var $input = $container.find('.braves-media-url');
            var $previewWrapper = $container.find('.braves-media-preview-wrapper');
            var $previewImage = $container.find('.braves-media-preview');

            // Create the media frame.
            var frame = wp.media({
                title: $button.data('title') || 'Seleccionar Imagen',
                button: {
                    text: $button.data('button') || 'Usar esta imagen'
                },
                multiple: false
            });

            // When an image is selected, run a callback.
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();

                // Set the value of the input field
                $input.val(attachment.url).trigger('change');

                // Update preview
                $previewImage.attr('src', attachment.url);
                $previewWrapper.show();
            });

            // Finally, open the modal.
            frame.open();
        });

        // Remove Media Button
        $('body').on('click', '.braves-remove-media', function (e) {
            e.preventDefault();

            var $button = $(this);
            var $container = $button.closest('.braves-media-uploader');
            var $input = $container.find('.braves-media-url');
            var $previewWrapper = $container.find('.braves-media-preview-wrapper');
            var $previewImage = $container.find('.braves-media-preview');

            // Clear input
            $input.val('').trigger('change');

            // Clear preview
            $previewImage.attr('src', '');
            $previewWrapper.hide();
        });
    });

})(jQuery);
