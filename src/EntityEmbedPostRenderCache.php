<?php

/**
 * @file Contains Drupal\entity_embed\EntityEmbedPostRenderCache.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;

/**
 * Defines a service for embedded entity post render cache callbacks.
 */
class EntityEmbedPostRenderCache {
  use EntityHelperTrait;

  /**
   * Constructs a EntityEmbedPostRenderCache object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module Handler.
   * @param \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $plugin_manager
   *   The Module Handler.
   */
  public function __construct(EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, EntityEmbedDisplayManager $plugin_manager) {
    $this->setEntityManager($entity_manager);
    $this->setModuleHandler($module_handler);
    $this->setDisplayPluginManager($plugin_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('plugin.manager.entity_embed.display')
    );
  }

  /**
   * #post_render_cache callback; renders an embedded entity.
   *
   * Replaces the #post_render_cache placeholder with an embedded entity.
   *
   * @param array $element
   *   The renderable array that contains the to be replaced placeholder.
   * @param array $context
   *   An array with the following keys:
   *   - entity-type: The entity type.
   *   - entity-id: The entity ID.
   *   - token: The placeholder token generated in buildPlaceholder().
   *
   * @return array
   *   A renderable array representing the placeholder replaced with the
   *   rendered entity.
   */
  public function renderEmbed(array $element, array $context) {
    $callback = 'entity_embed.post_render_cache:renderEmbed';
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context);

    // Do not bother rendering the entity if the placeholder cannot be found.
    if (strpos($element['#markup'], $placeholder) === FALSE) {
      return $element;
    }

    $entity_output = '';
    try {
      $id = $context['data-entity-uuid'] ?: $context['data-entity-id'];
      if ($entity = $this->loadEntity($context['data-entity-type'], $id)) {
        $entity_output = $this->renderEntityEmbedDisplayPlugin(
          $entity,
          $context['data-entity-embed-display'],
          $context['data-entity-embed-settings'],
          $context
        );
      }
    }
    catch (\Exception $e) {
      watchdog_exception('entity_embed', $e);
    }

    $element['#markup'] = str_replace($placeholder, $entity_output, $element['#markup']);
    return $element;
  }
}
