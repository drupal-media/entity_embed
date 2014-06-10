<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\FileFieldFormatter.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Field\FieldDefinition;

/**
 * Embed entity displays for file field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "file",
 *   label = @Translation("File"),
 *   entity_types = {"file"},
 *   derivative = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "file",
 *   provider = "file"
 * )
 */
class FileFieldFormatter extends EntityReferenceFieldFormatter {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = FieldDefinition::create('file');
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue(FieldDefinition $definition) {
    $value = parent::getFieldValue($definition);
    $value += array_intersect_key($this->getAttributeValues(), array('description' => ''));
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = parent::defaultConfiguration();
    // Add support to store file description.
    $defaults['description'] = '';
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Description is stored in the configuration since it doesn't map to an
    // actual HTML attribute.
    // @todo Ensure these fields work properly and map to the attributes
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $this->getConfigurationValue('description'),
      '#description' => $this->t('The description may be used as the label of the link to the file.'),
    );

    return $form;
  }

}
