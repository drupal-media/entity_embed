<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedPermissions.
 */

namespace Drupal\entity_embed;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the entity_embed module.
 */
class EntityEmbedPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new EntityEmbedPermissions instance.
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
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  /**
   * Returns an array of entity_embed permissions.
   *
   * @return array
   */
  public function permissions() {
    $permissions = [];
    // Generate permissions for each embed button
    /** @var \Drupal\entity_embed\EmbedButtonInterface[] $embed_buttons */
    $embed_buttons = $this->entityManager->getStorage('embed_button')->loadByProperties(['status' => TRUE]);
    uasort($embed_buttons, 'Drupal\Core\Config\Entity\ConfigEntityBase::sort');
    foreach ($embed_buttons as $embed_button) {
      $permissions[$embed_button->getPermissionName()] = [
        'title' => $this->t('Use the <a href="@url">@label</a> embed button', ['@url' => $embed_button->url(), '@label' => $embed_button->label()]),
        ];
    }
    return $permissions;
  }

}

