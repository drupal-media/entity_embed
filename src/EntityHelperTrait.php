<?php

/**
 * @file Contains Drupal\entity_embed\EntityHelperTrait.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageException;

/**
 * Wrapper methods for the Link Generator and Url Generator.
 *
 * This utility trait should only be used in application-level code, such as
 * classes that would implement ContainerInjectionInterface. Services registered
 * in the Container should not use this trait but inject the appropriate service
 * directly for easier testing.
 */
trait EntityHelperTrait {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @todo Document.
   */
  protected function loadEntity($entity_type, $id) {
    $entities = $this->loadMultipleEntities($entity_type, array($id));
    return reset($entities);
  }

  /**
   * @todo Document.
   */
  protected function loadMultipleEntities($entity_type, array $ids) {
    $entities = array();
    $storage = $this->entityManager()->getStorage($entity_type);

    $uuids = array_filter($ids, 'Drupal\Component\Uuid\Uuid::isValid');
    if (!empty($uuids)) {
      $definition = $this->entityManager()->getDefinition($entity_type);
      if (!$uuid_key = $definition->getKey('uuid')) {
        throw new EntityStorageException("Entity type $entity_type does not support UUIDs.");
      }
      $entities += $storage->loadByProperties(array($uuid_key => $uuids));
    }

    if ($remaining_ids = array_diff($ids, $uuids)) {
      $entities += $storage->loadMultiple($remaining_ids);
    }

    return $entities;
  }

  /**
   * @todo Document.
   */
  protected function canRenderEntity(EntityInterface $entity) {
    $entity_type = $entity->getEntityTypeId();
    return $this->entityManager()->hasController($entity_type, 'view_builder');
  }

  /**
   * @todo Document.
   */
  protected function renderEntity(EntityInterface $entity, $view_mode = 'default', $langcode = NULL) {
    $render_controller = $this->entityManager()->getViewBuilder($entity->getEntityTypeId());
    return $render_controller->view($entity, $view_mode, $langcode);
  }

  /**
   * Returns the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager.
   */
  protected function entityManager() {
    if (!isset($this->entityManager)) {
      $this->entityManager = \Drupal::service('entity.manager');
    }
    return $this->entityManager;
  }

  /**
   * Sets the entity manager service.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager service.
   *
   * @return self
   */
  public function setEntityManager(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
    return $this;
  }
}
