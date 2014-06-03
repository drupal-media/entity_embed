<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\EntityReferenceFieldFormatter.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Field\FieldDefinition;

/**
 * Embed entity displays for entity_reference field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "entity_reference",
 *   label = @Translation("Entity Reference"),
 *   derivative = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "entity_reference",
 *   provider = "entity_reference"
 * )
 */
class EntityReferenceFieldFormatter extends FieldFormatterEntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = FieldDefinition::create('entity_reference');
    $field->setSetting('target_type', $this->getAttributeValue('entity-type'));
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue(FieldDefinition $definition) {
    return array('target_id' => $this->getContextValue('entity')->id());
  }

}
