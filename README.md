# Entity Embed Module

## Requirements

* Latest dev release of Drupal 8.x as this module will not work with the last
  alpha release.

## Usage

Allows any entity to be embedded using a WYSIWYG and text format.

### Embed by UUID (recommended):
```html
<div data-entity-type="node" data-entity-uuid="07bf3a2e-1941-4a44-9b02-2d1d7a41ec0e" data-view-mode="teaser" />
```

### Embed by ID (not recommended):
```html
<div data-entity-type="node" data-entity-id="1" data-view-mode="teaser" />
```

### Display Plugins
```html
<div data-entity-type="node" data-entity-id="1" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-settings='{"view_mode":"teaser"}' />
```

A more advanced use for embedding entities uses an entity embed display plugin, provided in the `data-entity-embed-display` attribute. By default we provide four different display plugins out of the box:

- default: Renders the entity using entity_view().
- entity_reference:_formatter_id_: Renders the entity using a specific Entity Reference field formatter. For example, entity_reference:entity_reference_label renders the entity using the "Label" formatter.
- file:_formatter_id_: Renders the entity using a specific File field formatter. This will only work if the entity is a file entity type.
- image:_formatter_id_: Renders the entity using a specific Image field formatter. This will only work if the entity is a file entity type, and the file is an image.

Configuration for the display plugin can be provided by using a data-entity-embed-settings attribute, which contains a JSON-encoded array value. Note that care must be used to use single quotes around the attribute value since JSON-encoded arrays typically contain double quotes.

The above example renders the entity using the _entity_reference_entity_view_ formatter from the Entity Reference module, using the _teaser_ view mode.
