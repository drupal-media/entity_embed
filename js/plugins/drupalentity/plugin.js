/**
 * @file
 * Drupal Entity plugin.
 */

(function ($, Drupal, drupalSettings, CKEDITOR) {

  "use strict";

  CKEDITOR.plugins.add('drupalentity', {
    // This plugin requires the Widgets System defined in the 'widget' plugin.
    requires: 'widget',

    // The plugin initialization logic goes inside this method.
    beforeInit: function (editor) {

      // Generic command for adding/editing entities of all types.
      editor.addCommand('editdrupalentity', {
        modes: { wysiwyg : 1 },
        canUndo: true,
        exec: function (editor, data) {
          data = data || {};

          var existingElement = getSelectedEntity(editor);

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

          var entity_label = data.label ? data.label : existingValues['data-entity-label'];
          var embed_button_id = data.id ? data.id : existingValues['data-embed-button'];

          var dialogSettings = {
            title: existingElement ? 'Edit ' + entity_label : 'Insert ' + entity_label,
            dialogClass: 'entity-select-dialog',
            resizable: false,
            minWidth: 800
          };

          var saveCallback = function (values) {
            var entityElement = editor.document.createElement('drupal-entity');
            var attributes = values.attributes;
            for (var key in attributes) {
              entityElement.setAttribute(key, attributes[key]);
            }
            editor.insertHtml(entityElement.getOuterHtml());
            if (existingElement) {
              // Detach the behaviors that were attached when the entity content
              // was inserted.
              Drupal.detachBehaviors(existingElement.$, drupalSettings);
              existingElement.remove();
            }
          }

          // Open the entity embed dialog for corresponding EmbedButton.
          Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/entity-embed/' + editor.config.drupal.format + '/' + embed_button_id), existingValues, saveCallback, dialogSettings);
        }
      });

      // Register the entity embed widget.
      editor.widgets.add('drupalentity', {
        // Minimum HTML which is required by this widget to work.
        requiredContent: 'div[data-entity-type]',

        // Simply recognize the element as our own. The inner markup if fetched
        // and inserted the init() callback, since it requires the actual DOM
        // element.
        upcast: function (element) {
          var attributes = element.attributes;
          if (attributes['data-entity-type'] === undefined || (attributes['data-entity-id'] === undefined && attributes['data-entity-uuid'] === undefined)) {
            return;
          }
          return element;
        },

        // Fetch the rendered entity.
        init: function () {
          var element = this.element;
          // Use a throwaway Drupal.ajax object to fetch the HTML, so that we
          // can retrieve out-of-band assets (JS, CSS...) and attach behaviors.
          // This requires attaching to an element with a known HTML ID, though.
          // For now, sticking on the admin_toolbar.
          // @todo Can we use something else ? Generate an ID on the fly and
          // assign it to the element itself ?
          var ajax = new Drupal.ajax('toolbar-administration', $('#toolbar-administration'), {
            url: Drupal.url('entity-embed/preview/' + editor.config.drupal.format + '?' + $.param({
              value: element.getOuterHtml()
            })),
            progress: {type: 'none'},
            // The call is triggered programmatically, this event is not used.
            event: 'entity_embed_dummy_event',
            // Add the target directly as a custom property.
            entity_embed_target: element.$
          });
          // Trigger the call manually, and unbind the event to avoid multiple
          // calls. The actual HTML is inserted in our 'entity_embed_insert'
          // Ajax command on success.
          $(ajax.element).trigger('entity_embed_dummy_event');
          $(ajax.element).unbind('entity_embed_dummy_event');
        },

        // Downcast the element. Set the inner html to be empty.
        downcast: function (element) {
          element.setHtml('');
          return element;
        },
      });

      // Register the toolbar buttons.
      if (editor.ui.addButton) {
        for (var key in editor.config.DrupalEntity_buttons) {
          var button = editor.config.DrupalEntity_buttons[key];
          editor.ui.addButton(button.name, {
            label: button.label,
            data: button,
            click: function(editor) {
              editor.execCommand('editdrupalentity', this.data);
            },
            icon: button.image,
          });
        }
      }

      // Register context menu option for editing widget.
      if (editor.contextMenu) {
        editor.addMenuGroup('drupalentity');
        editor.addMenuItem('drupalentity', {
          label: Drupal.t('Edit Entity'),
          icon: this.path + 'entity.png',
          command: 'editdrupalentity',
          group: 'drupalentity'
        });

        editor.contextMenu.addListener(function(element) {
          if (isEntityWidget(editor, element)) {
            return { drupalentity: CKEDITOR.TRISTATE_OFF };
          }
        });
      }

      // Execute widget editing action on double click.
      editor.on('doubleclick', function (evt) {
        var element = getSelectedEntity(editor) || evt.data.element;

        if (isEntityWidget(editor, element)) {
          editor.execCommand('editdrupalentity');
        }
      });
    }
  });

  /**
   * Get the surrounding drupalentity widget element.
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
   * Returns whether or not the given element is a drupalentity widget.
   *
   * @param {CKEDITOR.editor} editor
   * @param {CKEDITOR.htmlParser.element} element
   */
  function isEntityWidget (editor, element) {
    var widget = editor.widgets.getByElement(element, true);
    return widget && widget.name === 'drupalentity';
  }

  /**
   * Ajax 'entity_embed_insert' command: insert the rendered entity.
   *
   * The regular Drupal.ajax.commands.insert() command cannot target elements
   * within iframes. This is a skimmed down equivalent that works whether the
   * CKEditor is in iframe or divarea mode.
   */
  Drupal.AjaxCommands.prototype.entity_embed_insert = function(ajax, response, status) {
    var target = ajax.entity_embed_target;
    // No need to detach behaviors, the widget is created fresh each time.
    $(target).html(response.html);
    Drupal.attachBehaviors(target, response.settings || ajax.settings || drupalSettings);
  };

})(jQuery, Drupal, drupalSettings, CKEDITOR);
