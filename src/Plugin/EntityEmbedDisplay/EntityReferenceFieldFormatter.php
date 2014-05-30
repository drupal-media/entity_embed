<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbedDisplay\EntityReferenceFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbedDisplay;

use Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase;
use Drupal\Core\Field\FieldDefinition;

/**
 * Embed entity displays for entity_reference field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "entity_reference",
 *   label = @Translation("Entity Reference"),
 *   types = {"entity"},
 *   derivative = "Drupal\entity_embed\Plugin\Derivative\FieldFormatter",
 *   field_type = "entity_reference",
 *   provider = "entity_reference"
 * )
 */
class EntityReferenceFieldFormatter extends FieldFormatterEntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = FieldDefinition::create('entity_reference')
      ->setSetting('target_type', $this->getContextValue('entity-type'));
    return $field;
  }

}
