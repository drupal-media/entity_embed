<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Twig\EntityEmbedTwigExtension.
 */

namespace Drupal\entity_embed\Twig;

use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Provide entity embedding function within Twig templates.
 */
class EntityEmbedTwigExtension extends \Twig_Extension {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new EntityFormBuilder.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
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
      new \Twig_SimpleFunction('entity_embed', array($this, 'getRenderArrayByMachineName')),
    );
  }

  /**
   * Return the render array for a block instance.
   *
   * @param string $entity_type
   *   The machine name of an entity_type like 'block'.
   *
   * The function is used in conjunction with Twig to be able to call in a Twig
   * file something like {{ entity_embed('machine_name_of_block') }}
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
   *   A render array from entity_view()
   */
  public function getRenderArrayByMachineName($entity_type, $entity_id, $view_mode = 'default') {

    // These lines are essentially entity_load() and entity_view() without
    // the reset option(). The injected entityManager property is used
    // instead of procedural functions for eventual unit-testability.
    $controller = $this->entityManager->getStorage($entity_type);
    $entity = $controller->load($entity_id);
    // entity_view equivilent.
    $render_controller = $this->entityManager->getViewBuilder($entity->getEntityTypeId());
    // @todo, handling for langcode variable.
    $build = $render_controller->view($entity, $view_mode);

    return $build;
  }
}
