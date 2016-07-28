<?php

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Set the schema version.
$connection->merge('key_value')
  ->condition('collection', 'system.schema')
  ->condition('name', 'entity_embed')
  ->fields([
    'collection' => 'system.schema',
    'name' => 'entity_embed',
    'value' => 's:4:"8001";',
  ])
  ->execute();

// Update core.extension.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions);
$extensions['module']['entity_embed'] = 8001;
$extensions['module']['embed'] = 8000;
$connection->update('config')
  ->fields([
    'data' => serialize($extensions),
  ])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();
