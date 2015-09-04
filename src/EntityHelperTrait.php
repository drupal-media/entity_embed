<?php

/**
 * @file
 * Contains Drupal\entity_embed\EntityHelperTrait.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;

/**
 * Wrapper methods for entity loading and rendering.
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
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface.
   */
  protected $moduleHandler;

  /**
   * Loads an entity from the database.
   *
   * @param string $entity_type
   *   The entity type to load, e.g. node or user.
   * @param mixed $id
   *   The id or UUID of the entity to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity object, or NULL if there is no entity with the given id or
   *   UUID.
   */
  protected function loadEntity($entity_type, $id) {
    $entities = $this->loadMultipleEntities($entity_type, array($id));
    return !empty($entities) ? reset($entities) : NULL;
  }

  /**
   * Loads multiple entities from the database.
   *
   * @param string $entity_type
   *   The entity type to load, e.g. node or user.
   * @param array $ids
   *   An array of entity IDs or UUIDs.
   *
   * @return array
   *   An array of entity objects indexed by their ids.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Throws an exception if the entity type does not supports UUIDs.
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
   * Determines if an entity can be rendered.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return bool
   *   TRUE if the entity's type has a view builder controller, otherwise FALSE.
   */
  protected function canRenderEntity(EntityInterface $entity) {
    $entity_type = $entity->getEntityTypeId();
    return $this->canRenderEntityType($entity_type);
  }

  /**
   * Determines if an entity type can be rendered.
   *
   * @param string $entity_type
   *   The entity type id.
   *
   * @return bool
   *   TRUE if the entitys type has a view builder controller, otherwise FALSE.
   */
  protected function canRenderEntityType($entity_type) {
    return $this->entityManager()->hasHandler($entity_type, 'view_builder');
  }

  /**
   * Returns the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager.
   */
  protected function entityManager() {
    if (!isset($this->entityManager)) {
      $this->entityManager = \Drupal::entityManager();
    }
    return $this->entityManager;
  }

  /**
   * Sets the entity manager service.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   *
   * @return self
   */
  public function setEntityManager(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
    return $this;
  }

  /**
   * Returns the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler() {
    if (!isset($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }
    return $this->moduleHandler;
  }

  /**
   * Sets the module handler service.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   *
   * @return self
   */
  public function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
    return $this;
  }
}
