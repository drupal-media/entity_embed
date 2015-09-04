<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManagerInterface.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an Entity embed display plugin manager.
 *
 * @see \Drupal\entity_embed\Annotation\EntityEmbedDisplay
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 */
interface EntityEmbedDisplayManagerInterface extends PluginManagerInterface {

  /**
   * The default display plugin.
   *
   * @var string
   */
  const DEFAULT_PLUGIN_ID = 'entity_reference:entity_reference_entity_view';

  /**
   * Determines plugins whose constraints are satisfied by a set of contexts.
   *
   * @param array $contexts
   *   An array of contexts.
   *
   * @return array
   *   An array of plugin definitions.
   *
   * @todo At some point convert this to use ContextAwarePluginManagerTrait
   * @see https://drupal.org/node/2277981
   */
  public function getDefinitionsForContexts(array $contexts = array());

  /**
   * Provides a list of plugins that can be used for a certain entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity object.
   *
   * @return array
   *   An array of valid plugin labels, keyed by plugin ID.
   */
  public function getDefinitionOptionsForEntity(EntityInterface $entity);

  /**
   * Provides a list of plugins that can be used for a certain entity type.
   *
   * @param string $entity_type
   *   The entity type id.
   *
   * @return array
   *   An array of valid plugin labels, keyed by plugin ID.
   */
  public function getDefinitionOptionsForEntityType($entity_type);

}
