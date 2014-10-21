<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Controller\AutocompleteController.
 */

namespace Drupal\entity_embed\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides autocomplete route controllers for entity embed.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Autocomplete callback for entities.
   */
  public function autocompleteEntity(Request $request, $entity_type_id) {
    $string = $request->query->get('q');
    $matches = array();

    $entity_type = \Drupal::entityManager()->getDefinition($entity_type_id);

    // Prevent errors if the entity type has no label key.
    if (!$entity_type->hasKey('label')) {
      return new JsonResponse($matches);
    }

    $ids = \Drupal::entityQuery($entity_type_id)
      ->condition($entity_type->getKey('label'), $string, 'STARTS_WITH')
      ->range(0, 10)
      ->sort($entity_type->getKey('label'))
      ->execute();

    $storage = \Drupal::entityManager()->getStorage($entity_type_id);
    foreach ($storage->loadMultiple($ids) as $entity) {
      $matches[] = array('value' => $entity->uuid(), 'label' => String::checkPlain($entity->label()));
    }

    return new JsonResponse($matches);
  }
}
