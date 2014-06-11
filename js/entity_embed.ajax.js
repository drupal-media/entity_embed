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
      // Prepare the data attributes from supplied values.
      var entityDiv = document.createElement('div');

      // Set all the supplied data attributes.
      for (var key in attributes) {
        entityDiv.setAttribute(key, attributes[key]);
      }

      // Set a placeholder text in the entity div, otherwise the div will be
      // discarded by CKEditor.
      entityDiv.innerHTML = attributes['data-entity-type'] + ": " + attributes['data-entity-uuid'];
      // Generate HTML of the DOM Object.
      var entityHTML = entityDiv.outerHTML;

      var existingContent = editor.getData();
      editor.setData(existingContent + entityHTML);
    }
  };

})(jQuery, Drupal, CKEDITOR);
