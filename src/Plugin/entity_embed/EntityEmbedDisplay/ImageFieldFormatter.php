<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\ImageFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Embed entity displays for image field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "image",
 *   label = @Translation("Image"),
 *   entity_types = {"file"},
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "image",
 *   provider = "image"
 * )
 */
class ImageFieldFormatter extends FileFieldFormatter {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = BaseFieldDefinition::create('image');
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue(BaseFieldDefinition $definition) {
    $value = parent::getFieldValue($definition);
    // File field support descriptions, but images do not.
    unset($value['description']);
    $value += array_intersect_key($this->getAttributeValues(), array('alt' => '', 'title' => ''));
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!parent::access($account)) {
      return FALSE;
    }

    $uri = $this->getContextValue('entity')->getFileUri();
    $image = \Drupal::service('image.factory')->get($uri);
    return $image->isValid();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // File field support descriptions, but images do not.
    unset($form['description']);

    // Ensure that the 'Link image to: Content' setting is not available.
    if ($this->getDerivativeId() == 'image') {
      unset($form['image_link']['#options']['content']);
    }

    // Add support for editing the alternate and title text attributes.
    // @todo Ensure these fields work properly and map to the attributes.
    $form['alt'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Alternate text'),
      '#default_value' => $this->getAttributeValue('alt', ''),
      '#description' => $this->t('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
      '#parents' => array('attributes', 'alt'),
      // @see http://www.gawds.org/show.php?contentid=28
      '#maxlength' => 512,
    );
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->getAttributeValue('title', ''),
      '#description' => t('The title is used as a tool tip when the user hovers the mouse over the image.'),
      '#parents' => array('attributes', 'title'),
      '#maxlength' => 1024,
    );

    return $form;
  }

}
