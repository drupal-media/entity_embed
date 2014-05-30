<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbedDisplay\ImageFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbedDisplay;

use Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase;
use Drupal\Core\Field\FieldDefinition;

/**
 * Embed entity displays for image field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "image",
 *   label = @Translation("Image"),
 *   types = {"file"},
 *   derivative = "Drupal\entity_embed\Plugin\Derivative\FieldFormatter",
 *   field_type = "image",
 *   provider = "image"
 * )
 *
 * @todo Ensure that the 'Link image to: Content' setting is not available.
 */
class ImageFieldFormatter extends FieldFormatterEntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = FieldDefinition::create('image');
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!parent::access($account)) {
      return FALSE;
    }

    $image = \Drupal::service('image.factory')->get($this->entity->getFileUri());
    return $image->isSupported();
  }

}
