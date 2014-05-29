<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplayManager.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;

/**
 * Provides an Entity embed display plugin manager.
 *
 * @see \Drupal\entity_embed\Annotation\EntityEmbedDisplay
 * @see \Drupal\entity_embed\EntityEmbedDisplayInterface
 */
class EntityEmbedDisplayManager extends DefaultPluginManager {

  /**
   * Constructs a new class instance.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EntityEmbedDisplay', $namespaces, $module_handler, 'Drupal\entity_embed\Annotation\EntityEmbedDisplay');
    $this->alterInfo('entity_embed_display_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'entity_embed_display_info');
  }

  /**
   * Gets the plugin definitions for this entity type.
   *
   * @param string $entity_type
   *   The entity type name.
   *
   * @return array
   *   An array of plugin definitions for this entity type.
   */
  public function getDefinitionsByType($type) {
    return array_filter($this->getDefinitions(), function ($definition) use ($type) {
      return (bool) array_intersect($definition['types'], array('entity', $type));
    });
  }

}
