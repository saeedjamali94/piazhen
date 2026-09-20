/**
 * Piazhen Settings Admin — media uploader hooks
 *
 * Handles the "Select Image" and "Remove" buttons on the settings page.
 * Uses the WordPress wp.media frame (enqueued via wp_enqueue_media()).
 */
(function ($) {
    $(function () {
        // Open media uploader on "Select Image" button click
        $(document).on('click', '.pzh-media-upload', function (e) {
            e.preventDefault();

            var $btn       = $(this);
            var targetId   = $btn.data('target');
            var previewId  = $btn.data('preview');
            var $input     = $('#' + targetId);
            var $preview   = $('#' + previewId);
            var $removeBtn = $btn.siblings('.pzh-media-remove');

            var frame = wp.media({
                title:    $btn.text(),
                multiple: false,
                library:  { type: 'image' },
                button:   { text: $btn.text() }
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html('<img src="' + attachment.url + '" alt="" style="max-width:200px;max-height:120px;">');
                $removeBtn.show();
            });

            frame.open();
        });

        // Remove image — revert to default
        $(document).on('click', '.pzh-media-remove', function (e) {
            e.preventDefault();

            var $btn       = $(this);
            var targetId   = $btn.data('target');
            var previewId  = $btn.data('preview');
            var defaultSrc = $btn.data('default');
            var $input     = $('#' + targetId);
            var $preview   = $('#' + previewId);

            $input.val('');
            if (defaultSrc) {
                $preview.html('<img src="' + defaultSrc + '" alt="" style="max-width:200px;max-height:120px;">');
            } else {
                $preview.html('');
            }
            $btn.hide();
        });
    });
})(jQuery);