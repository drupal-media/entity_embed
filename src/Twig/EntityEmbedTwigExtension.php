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
   *   The machine name of an entity_type like 'node'.
   *
   * @param string $entity_id
   *   The entity ID or entity UUID.
   *
   * @param string $view_mode
   *   (optional) The machine name of an view mode like 'default' or 'teaser'.
   *
   * @param string $langcode
   *   (optional) For which language the entity should be rendered, defaults to
   *   the current content language.
   *
   * @return array
   *   A render array from entity_view().
   */
  public function getRenderArray($entity_type, $entity_id, $view_mode = 'default', $langcode = NULL) {
    $entity = $this->loadEntity($entity_type, $entity_id);
    return $this->renderEntity($entity, $view_mode, $langcode);
  }
}
