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
    beforeInit: function (editor) {
      // Custom dialog to specify data attributes.
      editor.addCommand('entityembed_dialog', {
        modes: { wysiwyg : 1 },
        canUndo: true,
        exec: function (editor) {
          var existingElement = getSelectedEntity(editor);

          var dialogSettings = {
            title: existingElement ? editor.config.EntityEmbed_dialogTitleEdit : editor.config.EntityEmbed_dialogTitleAdd,
            dialogClass: 'entity-select-dialog',
            resizable: false,
            minWidth: 800
          };

          var existingValues = {};
          if (existingElement && existingElement.$ && existingElement.$.firstChild) {
            var entityDOMElement = existingElement.$.firstChild;
            // Populate array with the entity's current attributes.
            var attribute = null, attributeName;
            for (var key = 0; key < entityDOMElement.attributes.length; key++) {
              attribute = entityDOMElement.attributes.item(key);
              attributeName = attribute.nodeName.toLowerCase();
              if (attributeName.substring(0, 15) === 'data-cke-saved-') {
                continue;
              }
              existingValues[attributeName] = existingElement.data('cke-saved-' + attributeName) || attribute.nodeValue;
            }
          }

          var saveCallback = function (values) {
            var entityElement = editor.document.createElement('entity_embed');
            var attributes = values.attributes;
            for (var key in attributes) {
              entityElement.setAttribute(key, attributes[key]);
            }
            editor.insertHtml(entityElement.getOuterHtml());
            if (existingElement) {
              existingElement.remove();
            }
          }

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

        // Downcast the element. Set the inner html to be empty.
        downcast: function (element) {
          element.setHtml('');
          return element;
        },
      });

      // Register the toolbar button.
      if (editor.ui.addButton) {
        editor.ui.addButton('EntityEmbed', {
          label: Drupal.t('Entity Embed'),
          command: 'entityembed_dialog',
          icon: this.path + '/entity.png',
        });
      }

      // Register context menu option for editing widget.
      if (editor.contextMenu) {
        editor.addMenuGroup('entity_embed');
        editor.addMenuItem('entity_embed', {
          label: Drupal.t('Edit Entity'),
          icon: this.path + 'entity.png',
          command: 'entityembed_dialog',
          group: 'entity_embed'
        });

        editor.contextMenu.addListener(function(element) {
          if (isEntityWidget(editor, element)) {
            return { entity_embed: CKEDITOR.TRISTATE_OFF };
          }
        });
      }

      // Execute widget editing action on double click.
      editor.on('doubleclick', function (evt) {
        var element = getSelectedEntity(editor) || evt.data.element;

        if (isEntityWidget(editor, element)) {
          editor.execCommand('entityembed_dialog');
        }
      });
    }
  });

  /**
   * Get the surrounding entity_embed widget element.
   *
   * @param {CKEDITOR.editor} editor
   */
  function getSelectedEntity(editor) {
    var selection = editor.getSelection();
    var selectedElement = selection.getSelectedElement();
    if (isEntityWidget(editor, selectedElement)) {
      return selectedElement;
    }

    return null;
  }

  /**
   * Returns whether or not the given element is a entity_embed widget.
   *
   * @param {CKEDITOR.editor} editor
   * @param {CKEDITOR.htmlParser.element} element
   */
  function isEntityWidget (editor, element) {
    var widget = editor.widgets.getByElement(element, true);
    return widget && widget.name === 'entity_embed';
  }

})(jQuery, Drupal, CKEDITOR);
