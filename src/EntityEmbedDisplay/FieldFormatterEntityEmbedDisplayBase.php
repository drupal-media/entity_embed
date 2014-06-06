<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\FieldFormatterEntityEmbedDisplayBase.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Field\FieldDefinition;
use Drupal\node\Entity\Node;

abstract class FieldFormatterEntityEmbedDisplayBase extends EntityEmbedDisplayBase {

  /**
   * Get the FieldDefinition object required to render this field's formatter.
   *
   * @return \Drupal\Core\Field\FieldDefinition
   *   The field definition.
   *
   * @see \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase::build()
   */
  abstract public function getFieldDefinition();

  /**
   * Get the field value required to pass into the field formatter.
   *
   * @param \Drupal\Core\Field\FieldDefinition $definition
   *   The field definition.
   *
   * @return mixed
   *   The field value.
   */
  abstract public function getFieldValue(FieldDefinition $definition);

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Create a temporary node object to which our fake field value can be
    // added.
    $node = Node::create(array('type' => '_entity_embed'));

    // Create the field definition, some might need more settings, it currently
    // doesn't load in the field type defaults. https://drupal.org/node/2116341
    $definition = $this->getFieldDefinition();
    // Ensure that the field name is unique each time this is run.
    $definition->setName('_entity_embed_' . $this->getAttributeValue('token'));

    /* @var \Drupal\Core\Field\FieldItemListInterface $items $items */
    // Create a field item list object, 1 is the value, array('target_id' => 1)
    // would work too, or multiple values. 1 is passed down from the list to the
    // field item, which knows that an integer is the ID.
    $items = \Drupal::typedDataManager()->create(
      $definition,
      $this->getFieldValue($definition),
      $definition->getName(),
      $node
    );

    if ($langcode = $this->getAttributeValue('langcode')) {
      $items->setLangcode($langcode);
    }

    $formatter = $this->getFormatter($definition);
    // Prepare, expects an array of items, keyed by parent entity ID.
    $formatter->prepareView(array($node->id() => $items));
    $build = $formatter->viewElements($items);
    // For some reason $build[0]['#printed'] is TRUE, which means it will fail
    // to render later. So for now we manually fix that.
    // @todo Investigate why this is needed.
    show($build[0]);
    return $build[0];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $definition = \Drupal::service('plugin.manager.field.formatter')->getDefinition($this->getDerivativeId());
    return $definition['class']::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $this->getFormatter()->settingsForm($form, $form_state);
  }

  /**
   * Constructs a \Drupal\Core\Field\FormatterInterface object.
   *
   * @param \Drupal\Core\Field\FieldDefinition $definition
   *   The field definition.
   *
   * @return \Drupal\Core\Field\FormatterInterface
   *   The formatter object.
   */
  protected function getFormatter(FieldDefinition $definition = NULL) {
    if (!isset($definition)) {
      $definition = $this->getFieldDefinition();
    }

    // Ensure that the field name is unique each time this is run.
    $definition->setName('_entity_embed_' . $this->getAttributeValue('token'));

    $display = array(
      'type' => $this->getDerivativeId(),
      'settings' => $this->getConfiguration(),
      'label' => 'hidden',
    );

    /* @var \Drupal\Core\Field\FormatterInterface $formatter */
    // Create the formatter plugin. Will use the default formatter for that field
    // type if none is passed.
    return \Drupal::service('plugin.manager.field.formatter')->getInstance(array(
      'field_definition' => $definition,
      'view_mode' => '_entity_embed',
      'configuration' => $display,
    ));
  }
}
