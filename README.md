# Entity Embed Module

## Requirements

* Drupal 8 core must have a current patch from https://drupal.org/node/2217877
  applied.

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
