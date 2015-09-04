<?php

/**
 * @file
 * Contains Drupal\entity_embed\EntityEmbedInterface.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;

/**
 * A service class for handling entity embeds.
 *
 * @todo Add more documentation.
 */
interface EntityEmbedInterface {

  const DEFAULT_DISPLAY_ID = 'entity_reference:entity_reference_entity_view';

  /**
   * Constructs a EntityEmbedService object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $display_plugin_manager
   *   The entity embed display plugin manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, RendererInterface $renderer, EntityEmbedDisplayManager $display_plugin_manager);

  /**
   * Renders an embedded entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be rendered.
   * @param array $context
   *   (optional) Array of context values, corresponding to the attributes on
   *   the embed HTML tag.
   *
   * @return string
   *   The HTML of the entity rendered with the display plugin.
   */
  public function renderEntityEmbed(EntityInterface $entity, array $context = array());

  /**
   * Renders an entity using an EntityEmbedDisplay plugin.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be rendered.
   * @param string $plugin_id
   *   The EntityEmbedDisplay plugin ID.
   * @param array $plugin_configuration
   *   (optional) Array of plugin configuration values.
   * @param array $context
   *   (optional) Array of additional context values, usually the embed HTML
   *   tag's attributes.
   *
   * @return array
   *   A render array for the display plugin.
   */
  public function renderEntityEmbedDisplayPlugin(EntityInterface $entity, $plugin_id, array $plugin_configuration = array(), array $context = array());

}
