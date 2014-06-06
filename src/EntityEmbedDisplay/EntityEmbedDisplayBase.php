<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayBase.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines a base display implementation that most display plugins will extend.
 *
 * @ingroup entity_embed_api
 */
abstract class EntityEmbedDisplayBase extends PluginBase implements EntityEmbedDisplayInterface {

 /**
  * The context for the plugin.
  *
  * @var array
  */
  public $context = array();

 /**
  * The attributes on the embedded entity.
  *
  * @var array
  */
  public $attributes = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    // @todo Add a hook_entity_embed_display_access()?

    // Check that the plugin's registered entity types matches the current
    // entity type.
    if (!$this->isValidEntityType()) {
      return FALSE;
    }

    // Check that the entity itself can be viewed by the user.
    return $this->getContextValue('entity')->access('view', $account);
  }

  /**
   * Validates that this display plugin applies to the current entity type.
   *
   * This checks the plugin annotation's 'entity_types' value, which should be
   * an array of entity types that this plugin can process, or FALSE if the
   * plugin applies to all entity types.
   *
   * @return bool
   *   TRUE if the plugin can display the current entity type, or FALSE
   *   otherwise.
   */
  protected function isValidEntityType() {
    $definition = $this->getPluginDefinition();
    if ($definition['entity_types'] === FALSE) {
      return TRUE;
    }
    else {
      $entity_type = $this->getContextValue('entity')->getEntityTypeId();
      return in_array($entity_type, $definition['entity_types']);
    }
  }

  /**
   * {@inheritdoc}
   */
  abstract public function build();

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    if (!form_get_errors($form_state)) {
      $this->configuration = array_intersect_key($form_state['values'], $this->defaultConfiguration());
    }
  }

  /**
   * Gets a configuration value.
   *
   * @param string $name
   *   The name of the plugin configuration value.
   * @param mixed $default
   *   The default value to return if the configuration value does not exist.
   *
   * @return mixed
   *    The currently set configuration value, or the value of $default if the
   *    configuration value is not set.
   */
  public function getConfigurationValue($name, $default = NULL) {
    $configuration = $this->getConfiguration();
    return array_key_exists($name, $configuration) ? $configuration[$name] : $default;
  }

  /**
   * Sets the value for a defined context.
   *
   * @param string $name
   *   The name of the context in the plugin definition.
   * @param mixed $value
   *   The value to set the context to. The value has to validate against the
   *   provided context definition.
   */
  public function setContextValue($name, $value) {
    $this->context[$name] = $value;
  }

  /**
   * Gets the values for all defined contexts.
   *
   * @return array
   *   An array of set context values, keyed by context name.
   */
  public function getContextValues() {
    return $this->context;
  }

  /**
   * Gets the value for a defined context.
   *
   * @param string $name
   *   The name of the context in the plugin configuration.
   *
   * @return mixed
   *    The currently set context value.
   */
  public function getContextValue($name) {
    return $this->context[$name];
  }

  /**
   * Sets the values for all attributes.
   *
   * @param array $attributes
   *   An array of attributes, keyed by attribute name.
   */
  public function setAttributes(array $attributes) {
    $this->attributes = $attributes;
  }

  /**
   * Gets the values for all attributes.
   *
   * @return array
   *   An array of set attribute values, keyed by attribute name.
   */
  public function getAttributeValues() {
    return $this->attributes;
  }

  /**
   * Gets the value for an attribute.
   *
   * @param string $name
   *   The name of the attribute.
   *
   * @return mixed
   *    The currently set attribute value.
   */
  public function getAttributeValue($name, $default = NULL) {
    $attributes = $this->getAttributeValues();
    return array_key_exists($name, $attributes) ? $attributes[$name] : $default;
  }
}
