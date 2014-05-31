<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbedDisplay\ImageFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbedDisplay;

use Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Session\AccountInterface;

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
 */
class ImageFieldFormatter extends FileFieldFormatter {

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

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Ensure that the 'Link image to: Content' setting is not available.
    if ($this->getDerivativeId() == 'image') {
      unset($form['image_link']['#options']['content']);
    }

    return $form;
  }

}
