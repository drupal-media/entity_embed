/**
 * @file
 * Provides AJAX commands for saving entity_embed dialogs.
 */

(function ($, Drupal, CKEDITOR) {

  "use strict";

  /**
   * Function to save the data attributes specified in the modal.
   */
  Drupal.AjaxCommands.prototype.entityembedDialogSave = function (ajax, response, status) {
    var editor_instance = response.values.editor_instance;
    var editor = CKEDITOR.instances[editor_instance];
    if (editor.mode == 'wysiwyg') {
      var attributes = response.values.attributes;
      console.log(attributes);
      // Prepare the data attributes from supplied values.
      var entityDiv = document.createElement('div');

      // Set entity type.
      entityDiv.setAttribute('data-entity-type', attributes.data_entity_type);

      // Set entity UUID.
      entityDiv.setAttribute('data-entity-uuid', attributes.data_entity_uuid);

      // Set view mode.
      entityDiv.setAttribute('data-view-mode', attributes.data_view_mode);

      // Set show caption attribute, only if its set in the form.
      if(attributes.show_caption == 1) {
        entityDiv.setAttribute('data-show-caption', 'data-show-caption');
      }

      // Set a placeholder.
      entityDiv.innerHTML = attributes.data_entity_type + ": " + attributes.data_entity_uuid;

      // Generate HTML of the DOM Object.
      var entityHTML = entityDiv.outerHTML;

      var existingContent = editor.getData();
      editor.setData(existingContent + entityHTML);
    }
  };

})(jQuery, Drupal, CKEDITOR);
