<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplayManager.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Exception\PluginException;

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
    parent::__construct('EntityEmbedDisplay', $namespaces, $module_handler, 'Drupal\entity_embed\Annotation\EntityEmbedDisplay');
    $this->alterInfo('entity_embed_display_plugins');
    $this->setCacheBackend($cache_backend, $language_manager, 'entity_embed_display_plugins');
  }

  /**
   * Overrides DefaultPluginManager::processDefinition().
   */
  public function processDefinition(&$definition, $plugin_id) {
    $definition += array(
      'entity_types' => FALSE,
    );

    if ($definition['entity_types'] !== FALSE && !is_array($definition['entity_types'])) {
      $definition['entity_types'] = array($definition['entity_types']);
    }
  }

  /**
   * Gets the valid plugin definitions that can be used for this entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return array
   *   An array of plugin definitions that can be used for this entity.
   *
   * @see \Drupal\entity_embed\EntityEmbedDisplayBase::access()
   */
  public function getDefinitionsByEntity(EntityInterface $entity) {
    $definitions = $this->getDefinitions();
    $valid_ids = array_filter(array_keys($definitions), function ($id) use ($entity) {
      try {
        $display = $this->createInstance($id);
        $display->setContextValue('entity', $entity);
        return $display->access();
      }
      catch (PluginException $e) {
        return FALSE;
      }
    });
    return array_intersect_key($definitions, array_flip($valid_ids));
  }

}
