/**
 * @file
 * Entity Embed plugin.
 */
(function ($, Drupal, CKEDITOR) {

  "use strict";

  CKEDITOR.plugins.add('entityembed', {
    // Register the icon used for the toolbar button. It must be the same as the
    // name of the widget.
    icons: 'entityembed',

    // The plugin initialization logic goes inside this method.
    init: function (editor) {
      // Custom dialog to specify data attributes.
      editor.addCommand('entityembed_dialog', {
        modes: { wysiwyg : 1 },
        canUndo: true,
        exec: function (editor, override) {
          var dialogSettings = {
            title: 'Entity Embed',
            dialogClass: 'entity-embed-dialog',
            resizable: false,
            minWidth: 800
          };

        var existingValues = {};
        var saveCallback = function(values) {
        };
        // Open the dialog for the entity embed form.
        Drupal.ckeditor.openDialog(editor, Drupal.url('admin/entity-embed/embed'), existingValues, saveCallback, dialogSettings);
      }
      });

      // Register the toolbar button.
      if (editor.ui.addButton) {
        editor.ui.addButton('EntityEmbed', {
          label: Drupal.t('Entity Embed'),
          command: 'entityembed_dialog',
          icon: this.path + '/entity.png',
        });
      }
    }

  });

})(jQuery, Drupal, CKEDITOR);
