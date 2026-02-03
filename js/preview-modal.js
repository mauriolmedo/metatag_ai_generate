(function ($, Drupal, once) {
    'use strict';

    /**
     * Behavior for the meta description preview modal.
     *
     * @type {Drupal~behavior}
     */
    Drupal.behaviors.metatagAiPreviewModal = {
        attach: function (context, settings) {
            // Update character count on textarea input.
            $(once('metatag-ai-preview', '#metatag-ai-generated-text', context)).on(
                'input', function () {
                    Drupal.metatagAiGenerate.updateCharacterCount($(this).val());
                }
            );

            // Initial count on load.
            var $textarea = $('#metatag-ai-generated-text', context);
            if ($textarea.length) {
                Drupal.metatagAiGenerate.updateCharacterCount($textarea.val());
            }

            // Bind click handlers to the action buttons.
            // These buttons are in the form-actions container, and Drupal's
            // prepareDialogButtons will trigger clicks on them from the buttonset.
            $(once('metatag-ai-actions', '[data-action]', context)).on(
                'click', function (e) {
                    e.preventDefault();
                    var action = $(this).data('action');

                    if (action === 'approve') {
                        Drupal.metatagAiGenerate.handleApprove();
                    }
                    else if (action === 'regenerate') {
                        Drupal.metatagAiGenerate.handleRegenerate();
                    }
                    else if (action === 'reject') {
                        Drupal.metatagAiGenerate.handleReject();
                    }
                }
            );
        }
    };

    /**
     * Utility functions for meta description generation.
     *
     * @namespace
     */
    Drupal.metatagAiGenerate = Drupal.metatagAiGenerate || {};

    /**
     * Updates the character count display with visual feedback.
     *
     * @param {string} text
     *   The text to count characters for.
     */
    Drupal.metatagAiGenerate.updateCharacterCount = function (text) {
        var count = text ? text.length : 0;
        var $counter = $('#metatag-ai-char-count');

        $counter.text(Drupal.t('@count characters', {'@count': count}));

        // Remove all status classes.
        $counter.removeClass('count-optimal count-warning count-danger');

        // Add appropriate class based on count.
        // SEO Best Practice 2026: 155-160 optimal (visible everywhere), up to 200 accepted.
        if (count >= 155 && count <= 160) {
            // Optimal (green): Guaranteed visible on all devices.
            $counter.addClass('count-optimal');
        }
        else if (count >= 120 && count <= 200) {
            // Warning (orange): Acceptable, may be truncated on mobile.
            $counter.addClass('count-warning');
        }
        else {
            // Danger (red): Too short (<120) or too long (>200).
            $counter.addClass('count-danger');
        }
    };

    /**
     * Handles the Approve button click.
     *
     * Copies the generated text to the metatag description field and closes modal.
     */
    Drupal.metatagAiGenerate.handleApprove = function () {
        var text = $('#metatag-ai-generated-text').val();

        // Try multiple possible selectors for metatag description field.
        // Metatag module uses nested field structure.
        var $descField = $('textarea[name*="field_meta"][name*="description"]').first();
        if (!$descField.length) {
            $descField = $('input[name*="field_meta"][name*="description"]').first();
        }

        if ($descField.length) {
            $descField.val(text).trigger('change');
            Drupal.announce(Drupal.t('Meta description updated'));
        }
        else {
            Drupal.announce(Drupal.t('Could not find meta description field'));
        }

        // Close the modal using Drupal's modal selector.
        var $modal = $('#drupal-modal');
        if ($modal.length && $modal.dialog('instance')) {
            $modal.dialog('close');
        }
    };

    /**
     * Handles the Reject button click.
     *
     * Simply closes the modal without making any changes.
     */
    Drupal.metatagAiGenerate.handleReject = function () {
        var $modal = $('#drupal-modal');
        if ($modal.length && $modal.dialog('instance')) {
            $modal.dialog('close');
        }
    };

    /**
     * Handles the Regenerate button click.
     *
     * Calls the generate endpoint again and updates the modal content.
     */
    Drupal.metatagAiGenerate.handleRegenerate = function () {
        var settings = drupalSettings.metatagAiGenerate || {};
        var nodeId = settings.nodeId || 'new';
        var generateUrl = settings.generateUrl || '/admin/metatag-ai-generate/generate';

        // Show loading state.
        var $textarea = $('#metatag-ai-generated-text');
        var $counter = $('#metatag-ai-char-count');
        $textarea.prop('disabled', TRUE);
        $counter.text(Drupal.t('Regenerating...'));

        // Make AJAX request.
        $.ajax(
            {
                url: generateUrl,
                data: {node_id: nodeId, regenerate: 1},
                dataType: 'json',
                success: function (response) {
                    // The response contains AJAX commands.
                    // We need to extract the new text from the response.
                    if (response && response.length > 0) {
                        // Look for OpenModalDialogCommand in the response.
                        for (var i = 0; i < response.length; i++) {
                            var command = response[i];
                            if (command.command === 'openDialog') {
                                // Extract generated text from the dialog content.
                                var $tempContent = $('<div>').html(command.data);
                                var newText = $tempContent.find('#metatag-ai-generated-text').val();
                                if (newText) {
                                    $textarea.val(newText);
                                    Drupal.metatagAiGenerate.updateCharacterCount(newText);
                                }
                            }
                        }
                    }
                    $textarea.prop('disabled', FALSE);
                },
                error: function (xhr, status, error) {
                    var message = Drupal.t('Error: @error', {'@error': error || status});
                    Drupal.announce(message, 'assertive');
                    $counter.text(message).addClass('count-danger');
                    $textarea.prop('disabled', FALSE);
                }
            }
        );
    };

})(jQuery, Drupal, once);
