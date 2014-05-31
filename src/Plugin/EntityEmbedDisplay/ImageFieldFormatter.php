<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbedDisplay\ImageFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbedDisplay;

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
  public function getFieldValue(FieldDefinition $definition) {
    $value = parent::getFieldValue($definition);
    // File field support descriptions, but images do not.
    unset($value['description']);
    $value += array_intersect_key($this->getContext(), array('alt' => '', 'title' => ''));
    return $value;
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

    // File field support descriptions, but images do not.
    unset($form['description']);

    // Ensure that the 'Link image to: Content' setting is not available.
    if ($this->getDerivativeId() == 'image') {
      unset($form['image_link']['#options']['content']);
    }

    // Add support for alternate and title text
    // @todo Ensure these fields work properly and map to the attributes
    $form['alt'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Alternate text'),
      '#default_value' => $this->getContextValue('alt', ''),
      '#description' => $this->t('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
      '#parents' => array('attributes', 'alt'),
      // @see http://www.gawds.org/show.php?contentid=28
      '#maxlength' => 512,
    );
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->getContextValue('title', ''),
      '#description' => t('The title is used as a tool tip when the user hovers the mouse over the image.'),
      '#parents' => array('attributes', 'title'),
      '#maxlength' => 1024,
    );

    return $form;
  }

}
