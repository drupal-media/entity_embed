<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\EntityReferenceFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_embed\EntityEmbedDisplay\FieldFormatterEntityEmbedDisplayBase;

/**
 * Embed entity displays for entity_reference field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "entity_reference",
 *   label = @Translation("Entity Reference"),
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "entity_reference",
 *   provider = "entity_reference"
 * )
 */
class EntityReferenceFieldFormatter extends FieldFormatterEntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = BaseFieldDefinition::create('entity_reference');
    $field->setSetting('target_type', $this->getEntityTypeFromContext());
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue(BaseFieldDefinition $definition) {
    return array('target_id' => $this->getContextValue('entity')->id());
  }

}
