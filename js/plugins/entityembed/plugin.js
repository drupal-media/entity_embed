/**
 * @file
 * Entity Embed CKEditor plugin.
 */

(function ($, Drupal, CKEDITOR) {

  "use strict";

  CKEDITOR.plugins.add('entityembed', {
    // This plugin requires the Widgets System defined in the 'widget' plugin.
    requires: 'widget',

    // The plugin initialization logic goes inside this method.
    init: function (editor) {
      // Custom dialog to specify data attributes.
      editor.addCommand('entityembed_dialog', {
        modes: { wysiwyg : 1 },
        canUndo: true,
        exec: function (editor, override) {
          var dialogSettings = {
            title: 'Insert Entity',
            dialogClass: 'entity-select-dialog',
            resizable: false,
            minWidth: 800
          };

          var existingValues = {};

          var saveCallback = function (values) {
            var element = editor.document.createElement('entity_embed');
            var attributes = values.attributes;
            for (var key in attributes) {
              element.setAttribute(key, attributes[key]);
            }
            editor.insertHtml(element.getOuterHtml());
          }

          existingValues['editor-id'] = editor.name;
          // Open the dialog for the entity embed form.
          Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/entity-embed/' + editor.config.drupal.format), existingValues, saveCallback, dialogSettings);
        }
      });

      // Register the entity embed widget.
      editor.widgets.add('entity_embed', {
        // Minimum HTML which is required by this widget to work.
        requiredContent: 'div[data-entity-type]',

        // Generate the preview of the element and render it.
        upcast: function (element) {
          var attributes = element.attributes;
          if (attributes['data-entity-type'] === undefined || (attributes['data-entity-id'] === undefined && attributes['data-entity-uuid'] === undefined)) {
            return;
          }
          var request = {};
          request['value'] = element.getOuterHtml();
          jQuery.ajax({
            url: Drupal.url('entity-embed/preview/' + editor.config.drupal.format + '?' + jQuery.param(request)),
            dataType: 'json',
            async: false,
            success: function (data) {
              element.setHtml(data.content);
            }
          });
          return element;
        },

        // Downcast the element. Do not set the html to be empty, otherwise the
        // div element will be discarded by the CKEditor.
        // Using entity_type:entity_id as placeholder.
        downcast: function (element) {
          var attributes = element.attributes;
          element.setHtml(attributes['data-entity-type'] + ':' + attributes['data-entity-id']);
          return element;
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
