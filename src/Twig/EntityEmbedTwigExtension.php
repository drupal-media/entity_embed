<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Twig\EntityEmbedTwigExtension.
 */

namespace Drupal\entity_embed\Twig;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\entity_embed\EntityHelperTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;

/**
 * Provide entity embedding function within Twig templates.
 */
class EntityEmbedTwigExtension extends \Twig_Extension {
  use EntityHelperTrait;

  /**
   * Constructs a new EntityEmbedTwigExtension.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $plugin_manager
   *   The plugin manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, EntityEmbedDisplayManager $plugin_manager) {
    $this->setEntityManager($entity_manager);
    $this->setModuleHandler($module_handler);
    $this->setDisplayPluginManager($plugin_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'entity_embed.twig.entity_embed_twig_extension';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('entity_embed', array($this, 'getRenderArray')),
    );
  }

  /**
   * Return the render array for an entity.
   *
   * @param string $entity_type
   *   The machine name of an entity_type like 'block'.
   *
   * The function is used in conjunction with Twig to be able to call in a Twig
   * file something like {{ entity_embed('block', 'machine_name_of_block') }}
   *
   * @param string $entity_id
   *   The machine name of a block instance like 'poweredbydrupal_2' or
   *   'views_block__content_recent_block_1' or an entity_id.
   *
   * @todo, Need to support uuid
   *
   * @param string $view_mode
   *   The machine name of an view mode like 'default' or 'teaser'.
   *
   * @return array
   *   A render array from entity_view().
   */
  public function getRenderArray($entity_type, $entity_id, $view_mode = 'default') {

    // These lines are essentially entity_load() and entity_view() without
    // the reset option(). The injected entityManager property is used
    // instead of procedural functions for eventual unit-testability.
    $controller = $this->entityManager->getStorage($entity_type);
    $entity = $this->loadEntity($entity_type, $entity_id);

    // @todo, handling for langcode variable.
    $plugin_configuration = array(
      'view_mode'=> $view_mode,
    );
    // @todo, handling for changing plugin_ids.
    $plugin_id = 'default';
    $build = $this->renderEntityEmbedDisplayPlugin($entity, $plugin_id, $plugin_configuration);

    return $build;
  }
}
