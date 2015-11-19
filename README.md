# Entity Embed Module

[![Travis build status](https://img.shields.io/travis/drupal-media/entity_embed/8.x-1.x.svg)](https://travis-ci.org/drupal-media/entity_embed) [![Scrutinizer code quality](https://img.shields.io/scrutinizer/g/drupal-media/entity_embed/8.x-1.x.svg)](https://scrutinizer-ci.com/g/drupal-media/entity_embed)

[Entity Embed](https://www.drupal.org/project/entity_embed) module allows any entity to be embedded using a WYSIWYG and text format.

## Requirements

* Latest dev release of Drupal 8.x as this module will not work with the last
  alpha release.
* [Embed](https://www.drupal.org/project/embed) module

## Installation

* Entity Embed can be installed via the [standard Drupal installation process](http://drupal.org/node/895232)

## Configuration

* Install and enable [Embed](https://www.drupal.org/project/embed) module.
* Install and enable [Entity Embed](https://www.drupal.org/project/entity_embed) module.
* Enable the entity-embed filter 'Display embedded entities' for the desired text formats from the configuration page: `/admin/config/content/formats`.
* Add ```<drupal-entity>``` to the 'Allowed HTML tags'
* To enable the WYSIWYG plugin, drag and drop the entity-embed 'E' button into the Active toolbar for the desired text formats from the configuration page: `/admin/config/content/formats/manage/<text format>`.

## Usage

* Create a new content, in body click over entity-embed E button.
* Enter title and choose one from suggestions.
* Choose **Display as** from following options:
  * Rendered Entity
  * Entity ID
  * Label
* Choose **View mode** (if chosen **Rendered Entity**) from following options:
  * Default
  * Full content
  * RSS
  * Search index
  * Search result highlighting input
* Choose alignment: none, left, center or right.

## Embedding entities without WYSIWYG

Users should be embedding entities using the CKEditor WYSIWYG button as described above. This section is more technical about the HTML markup that is used to embed the actual entity.

### Embed by UUID (recommended):
```html
<div data-entity-type="node" data-entity-uuid="07bf3a2e-1941-4a44-9b02-2d1d7a41ec0e" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-settings='{"view_mode":"teaser"}' />
```

### Embed by ID (not recommended):
```html
<div data-entity-type="node" data-entity-id="1" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-settings='{"view_mode":"teaser"}' />
```

### Display Plugins

Embedding entities uses an entity embed display plugin, provided in the `data-entity-embed-display` attribute. By default we provide four different display plugins out of the box:

- entity_reference:_formatter_id_: Renders the entity using a specific Entity Reference field formatter.
- entity_reference:_entity_reference_label_: Renders the entity using the "Label" formatter.
- file:_formatter_id_: Renders the entity using a specific File field formatter. This will only work if the entity is a file entity type.
- image:_formatter_id_: Renders the entity using a specific Image field formatter. This will only work if the entity is a file entity type, and the file is an image.

Configuration for the display plugin can be provided by using a `data-entity-embed-settings` attribute, which contains a JSON-encoded array value. Note that care must be used to use single quotes around the attribute value since JSON-encoded arrays typically contain double quotes.

The above examples render the entity using the _entity_reference_entity_view_ formatter from the Entity Reference module, using the _teaser_ view mode.
