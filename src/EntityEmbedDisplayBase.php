<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplayBase.
 */

namespace Drupal\entity_embed;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\entity_embed\EntityEmbedDisplayInterface;
use Drupal\Core\Session\AccountInterface;

abstract class EntityEmbedDisplayBase extends PluginBase implements EntityEmbedDisplayInterface {

  public $entity;

  public $context = array();

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

  public function access(AccountInterface $account = NULL) {
    $definition = $this->getPluginDefinition();
    $entity_type = $this->getEntity()->getEntityTypeId();
    if (!array_intersect($definition['types'], array('entity', $entity_type))) {
      return FALSE;
    }

    return $this->getEntity()->access('view', $account);
  }

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
}
