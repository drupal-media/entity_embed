<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplayBase.
 */

namespace Drupal\entity_embed;

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
   * The entity being embedded.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  public $entity;

  /**
   * The context for the embedded entity.
   *
   * @var array
   */
  public $context = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  public function setEntity(EntityInterface $entity) {
    $this->entity = $entity;
    return $this;
  }

  public function getEntity() {
    return $this->entity;
  }

  public function setContext(array $context) {
    $this->context = $context;
    return $this;
  }

  public function getContext() {
    return $this->context;
  }

  public function getContextValue($name, $default = NULL) {
    $context = $this->getContext();
    return array_key_exists($name, $context) ? $context[$name] : $default;
  }

  public function getConfigurationValue($name, $default = NULL) {
    $configuration = $this->getConfiguration();
    return array_key_exists($name, $configuration) ? $configuration[$name] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    // @todo Add a hook_entity_embed_display_access()?

    // Check that the plugin's registered entity types matches the current
    // entity type.
    if (!$this->entityMatchesAllowedTypes()) {
      return FALSE;
    }

    // Check that the entity itself can be viewed by the user.
    return $this->entity->access('view', $account);
  }

  /**
   * Validate that this display plugin applies to the current entity type.
   *
   * This checks the plugin annotation's 'types' value, which should be an
   * array of entity types that this plugin can process.
   *
   * @return bool
   *   TRUE if the plugin can display the current entity type, or FALSE
   *   otherwise.
   */
  protected function entityMatchesAllowedTypes() {
    $definition = $this->getPluginDefinition();
    $entity_type = $this->entity->getEntityTypeId();
    return (bool) array_intersect($definition['types'], array('entity', $entity_type));
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
}
