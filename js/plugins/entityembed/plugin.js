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
            title: 'Select the entity to be embedded',
            dialogClass: 'entity-select-dialog',
            resizable: false,
            minWidth: 800
          };

          var existingValues = {};
          existingValues['editor-id'] = editor.name;
          var saveCallback = function(values) {
          };
          // Open the dialog for the entity embed form.
          Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/entity_select/' + editor.config.drupal.format), existingValues, saveCallback, dialogSettings);
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
   * Function to allow selection of entity to be embedded.
   */
  Drupal.AjaxCommands.prototype.entityembedSelectDialogSave = function (ajax, response, status) {
    var editor_instance = response.values.editor_instance;
    var editor = CKEDITOR.instances[editor_instance];
    // Configure the dialog for the next step.
    var dialogSettings = {
      title: 'Embed Entity',
      dialogClass: 'entity-embed-dialog',
      resizable: false,
      minWidth: 800
    };

    // Populate existing values, i.e. embed_method, entity_type and entity.
    var existingValues = {};
    existingValues['entity-type'] = response.values.entity_type;
    existingValues['embed-method'] = response.values.embed_method;
    existingValues['entity'] = response.values.entity;
    existingValues['editor-id'] = editor.name;
    var saveCallback = function(values) {
    };
    // Open the dialog for the entity embed form.
    Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/entity_embed/' + editor.config.drupal.format), existingValues, saveCallback, dialogSettings);
  };


  /**
   * Function to save the data attributes specified in the modal.
   */
  Drupal.AjaxCommands.prototype.entityembedSubmitDialogSave = function (ajax, response, status) {
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
