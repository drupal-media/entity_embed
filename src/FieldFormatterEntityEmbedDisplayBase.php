<?php

/**
 * @file
 * Contains \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Field\FieldDefinition;
use Drupal\node\Entity\Node;

abstract class FieldFormatterEntityEmbedDisplayBase extends EntityEmbedDisplayBase {

  /**
   * Get the FieldDefinition object required to render this field's formatter.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   *   The field definition.
   *
   * @see \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase::build()
   */
  abstract public function getFieldDefinition();

  abstract public function getFieldValue(FieldDefinition $definition);

  public function getFormatter(FieldDefinition $definition = NULL) {
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

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Load the node, you can also create a temporary object or have your own
    // dummy object that implements EntityInterface. (Might need ContentEntityInterface, not sure).
    $node = Node::create(array('type' => '_entity_embed'));

    // Create the field definition, some might need more settings, it currently
    // doesn't load in the field type defaults. https://drupal.org/node/2116341
    // Field name is only set to avoid broken CSS classes.
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
    // Prepare, expects an array of items, keyed by parent entity ID, not sure if
    // actually used, just array($items) worked too.
    $formatter->prepareView(array($node->id() => $items));
    $build = $formatter->viewElements($items);
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
}
