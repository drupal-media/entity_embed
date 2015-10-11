<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbed\Widget\EntityReferenceFieldWidget.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbed\Widget;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_embed\EntityEmbedDisplay\FieldFormatterEntityEmbedDisplayBase;

/**
 * Entity displays for entity_reference field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "entity_reference",
 *   label = @Translation("Entity Reference"),
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\FieldWidgetDeriver",
 *   field_type = "entity_reference"
 * )
 */
class EntityReferenceFieldWidget extends FieldWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    if (!isset($this->fieldDefinition)) {
      $this->fieldDefinition = parent::getFieldDefinition();
      $this->fieldDefinition->setSetting('target_type', $this->getEntityTypeFromContext());
    }
    return $this->fieldDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue() {
    return array('target_id' => $this->getContextValue('entity')->id());
  }

}
