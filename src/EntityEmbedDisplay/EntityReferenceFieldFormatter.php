<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\EntityReferenceFieldFormatter.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_embed\EntityHelperTrait;

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
  use EntityHelperTrait;

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

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!parent::access($account)) {
      return FALSE;
    }

    switch ($this->getDerivativeId()) {
      case 'entity_reference_entity_view':
        // Cannot render an entity if it does not have a view controller.
        // @todo Remove when https://drupal.org/node/2204325 is fixed in core.
        // The entity manager is not injected via dependency here because
        // ideally we resolve the core issue here rather than a concern about
        // proper testability here on a one-off basis.
        return $this->canRenderEntity($this->getContextValue('entity'));

      default:
        return TRUE;

    }
  }

}
