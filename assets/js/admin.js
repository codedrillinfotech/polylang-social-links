jQuery(document).ready(function($) {
    'use strict';

    // Initialize sortable
    $('.psl-social-links-sortable').sortable({
        handle: '.psl-sort-handle',
        axis: 'y',
        items: '.psl-social-link-row',
        cursor: 'move',
        opacity: 0.8,
        update: function() {
            // You can add additional logic here if needed when sorting is complete
        }
    });

    // Add new social link
    $('.psl-add-link').on('click', function(e) {
        e.preventDefault();
        
        var $container = $('.psl-social-links-sortable');
        var $lastRow = $container.find('.psl-social-link-row').last();
        var newIndex = $container.find('.psl-social-link-row').length;
        var lang = $('input[name="psl_current_lang"]').val();
        
        // Create a clone of the last row
        var $newRow = $lastRow.clone();
        
        // Clear the values
        $newRow.find('input[type="text"], textarea').val('');
        $newRow.find('input[type="checkbox"]').prop('checked', false);
        $newRow.find('.psl-svg-preview').empty();
        
        // Update the name attributes with the new index
        $newRow.find('[name^="psl_social_links"]').each(function() {
            var name = $(this).attr('name');
            name = name.replace(/\[\d+\]/, '[' + newIndex + ']');
            $(this).attr('name', name);
        });
        
        // Add the new row
        $container.append($newRow);
        
        // Scroll to the new row
        $('html, body').animate({
            scrollTop: $newRow.offset().top - 100
        }, 300);
    });

    // Remove social link
    $(document).on('click', '.psl-remove-link', function(e) {
        e.preventDefault();
        
        var $row = $(this).closest('.psl-social-link-row');
        var $rows = $row.siblings('.psl-social-link-row');
        
        // Don't remove if it's the only row
        if ($rows.length === 0) {
            // Just clear the values instead of removing
            $row.find('input[type="text"], textarea').val('');
            $row.find('input[type="checkbox"]').prop('checked', false);
            $row.find('.psl-svg-preview').empty();
            return;
        }
        
        if (confirm(pslAdmin.i18n.confirmRemove)) {
            $row.fadeOut(200, function() {
                $(this).remove();
                reindexRows();
            });
        }
    });

    // Preview SVG
    $(document).on('input', '.psl-svg-code', function() {
        var $preview = $(this).siblings('.psl-svg-preview');
        $preview.html($(this).val());
    });

    // Reindex all rows to ensure proper indexing before form submission
    $('form.psl-settings-form').on('submit', function() {
        reindexRows();
        return true;
    });

    /**
     * Reindex all rows to ensure sequential numbering
     */
    function reindexRows() {
        $('.psl-social-links-sortable .psl-social-link-row').each(function(index) {
            $(this).find('[name^="psl_social_links"]').each(function() {
                var name = $(this).attr('name');
                name = name.replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', name);
            });
        });
    }

    // Handle tab switching
    $('.nav-tab-wrapper a').on('click', function(e) {
        e.preventDefault();
        
        // Get the language from the href
        var targetUrl = $(this).attr('href');
        
        // Navigate directly to the URL instead of submitting the form
        window.location.href = targetUrl;
    });
});
