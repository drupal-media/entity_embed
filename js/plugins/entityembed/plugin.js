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
          existingValues['editor-id'] = editor.name;
          var saveCallback = function(values) {
          };
          // Open the dialog for the entity embed form.
          Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/embed/' + editor.config.drupal.format), existingValues, saveCallback, dialogSettings);
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

  /**
   * Function to save the data attributes specified in the modal.
   */
  Drupal.AjaxCommands.prototype.entityembedDialogSave = function (ajax, response, status) {
    var editor_instance = response.values.editor_instance;
    var editor = CKEDITOR.instances[editor_instance];
    if (editor.mode == 'wysiwyg') {
      // Prepare the data attributes from supplied values.
      var entityDiv = document.createElement('div');

      // Set entity type.
      entityDiv.setAttribute('data-entity-type', response.values.entity_type);

      // Set entity UUID/ID depending on which method was chosen.
      if(response.values.embed_method == 'uuid') {
        entityDiv.setAttribute('data-entity-uuid', response.values.entity);
      } else {
        entityDiv.setAttribute('data-entity-id', response.values.entity);
      }

      // Set view mode.
      entityDiv.setAttribute('data-view-mode', response.values.view_mode);

      // Set show caption attribute, only if its set in the form.
      if(response.values.show_caption == 1) {
        entityDiv.setAttribute('data-show-caption', 'data-show-caption');
      }

      // Set display links attribute, only if its set in the form.
      if(response.values.display_links == 1) {
        entityDiv.setAttribute('data-display-links', 'data-display-links');
      }

      // Set a placeholder.
      entityDiv.innerHTML = response.values.entity_type + ": " + response.values.entity;

      // Generate HTML of the DOM Object.
      var entityHTML = entityDiv.outerHTML;

      var existingContent = editor.getData();
      editor.setData(existingContent + entityHTML);
    }
  };

})(jQuery, Drupal, CKEDITOR);
