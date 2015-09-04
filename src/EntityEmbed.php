<?php

namespace Drupal\entity_embed;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;

class EntityEmbedService implements EntityEmbedInterface, ContainerInjectionInterface {
  use EntityHelperTrait;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface.
   */
  protected $renderer;

  /**
   * The display plugin manager.
   *
   * @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager.
   */
  protected $displayPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, RendererInterface $renderer, EntityEmbedDisplayManager $display_plugin_manager) {
    $this->setEntityManager($entity_manager);
    $this->setModuleHandler($module_handler);
    $this->renderer = $renderer;
    $this->displayPluginManager = $display_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('renderer'),
      $container->get('plugin.manager.entity_embed.display')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function renderEntityEmbed(EntityInterface $entity, array $context = array()) {
    // Merge in default attributes.
    $context += array(
      'data-entity-id' => $entity->id(),
      'data-entity-type' => $entity->getEntityTypeId(),
      'data-entity-embed-display' => static::DEFAULT_DISPLAY_ID,
      'data-entity-embed-settings' => array(),
    );

    // The default display plugin has been deprecated by the rendered entity
    // field formatter.
    if ($context['data-entity-embed-display'] === 'default') {
      $context['data-entity-embed-display'] = static::DEFAULT_DISPLAY_ID;
    }

    // Support the deprecated view-mode data attribute.
    if (isset($context['data-view-mode']) && $context['data-entity-embed-display'] === static::DEFAULT_DISPLAY_ID && empty($context['data-entity-embed-settings'])) {
      $context['data-entity-embed-settings']['view_mode'] = &$context['data-view-mode'];
    }

    // Allow modules to alter the entity prior to embed rendering.
    $this->moduleHandler()->alter(array("{$context['data-entity-type']}_embed_context", 'entity_embed_context'), $context, $entity);

    // Build and render the display plugin, allowing modules to alter the
    // result before rendering.
    $build = $this->renderEntityEmbedDisplayPlugin(
      $entity,
      $context['data-entity-embed-display'],
      $context['data-entity-embed-settings'],
      $context
    );
    // @todo Should this hook get invoked if $build is an empty array?
    $this->moduleHandler()->alter(array("{$context['data-entity-type']}_embed", 'entity_embed'), $build, $entity, $context);
    $entity_output = $this->renderer->render($build);

    return $entity_output;
  }

  /**
   * {@inheritdoc}
   */
  public function renderEntityEmbedDisplayPlugin(EntityInterface $entity, $plugin_id, array $plugin_configuration = array(), array $context = array()) {
    // Build the display plugin.
    /** @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayBase $display */
    $display = $this->displayPluginManager->createInstance($plugin_id, $plugin_configuration);
    $display->setContextValue('entity', $entity);
    $display->setAttributes($context);

    // Check if the display plugin is accessible. This also checks entity
    // access, which is why we never call $entity->access() here.
    if (!$display->access()) {
      return array();
    }

    return $display->build();
  }
}
