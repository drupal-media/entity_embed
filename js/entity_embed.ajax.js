/**
 * @file
 * Provides AJAX commands for saving entity_embed dialogs.
 */

(function ($, Drupal, CKEDITOR) {

  "use strict";

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
    // Open the dialog for the entity embed form.
    Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/entity_embed/' + editor.config.drupal.format), existingValues, null, dialogSettings);
  };

  /**
   * Function to go to back to first step of the embed form.
   */
  Drupal.AjaxCommands.prototype.entityembedSubmitDialogGoBack = function (ajax, response, status) {
    var editor_instance = response.values.editor_instance;
    var editor = CKEDITOR.instances[editor_instance];
    // Configure the dialog for the previous step.
    var dialogSettings = {
      title: 'Embed Entity',
      dialogClass: 'entity-embed-dialog',
      resizable: false,
      minWidth: 800
    };

    // Populate existing values, i.e. embed_method, entity_type and entity.
    var existingValues = {};
    existingValues['entity-type'] = response.values.entity_type;
    existingValues['entity'] = response.values.entity;
    existingValues['editor-id'] = editor.name;
    // Open the dialog for the entity embed form.
    Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/entity_select/' + editor.config.drupal.format), existingValues, null, dialogSettings);
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

      // Set a placeholder.
      entityDiv.innerHTML = response.values.entity_type + ": " + response.values.entity;

      // Generate HTML of the DOM Object.
      var entityHTML = entityDiv.outerHTML;

      var existingContent = editor.getData();
      editor.setData(existingContent + entityHTML);
    }
  };

})(jQuery, Drupal, CKEDITOR);
