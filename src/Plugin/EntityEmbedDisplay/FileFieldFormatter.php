<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbedDisplay\FileFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbedDisplay;

use Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase;
use Drupal\Core\Field\FieldDefinition;

/**
 * Embed entity displays for file field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "file",
 *   label = @Translation("File"),
 *   types = {"file"},
 *   derivative = "Drupal\entity_embed\Plugin\Derivative\FieldFormatter",
 *   field_type = "file",
 *   provider = "file"
 * )
 */
class FileFieldFormatter extends FieldFormatterEntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = FieldDefinition::create('file');
    return $field;
  }

}
