<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Controller\AutocompleteController.
 */

namespace Drupal\entity_embed\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\entity_embed\EmbedButtonInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\FilterFormatInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides autocomplete route controllers for entity embed.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs an AutocompleteController.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *
   */
  public function __construct(QueryFactory $query_factory) {
    $this->queryFactory =$query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.query'));
  }

  /**
   * Autocomplete callback for entities.
   */
  public function autocompleteEntity(Request $request, FilterFormatInterface $filter_format, EmbedButtonInterface $embed_button) {
    $string = $request->query->get('q');
    $matches = array();

    $entity_type_id = $embed_button->getEntityTypeMachineName();
    $entity_type = $this->entityManager()->getDefinition($entity_type_id);

    // Prevent errors if the entity type has no label key.
    if (!$entity_type->hasKey('label')) {
      return new JsonResponse($matches);
    }

    $ids = $this->queryFactory->get($entity_type_id)
      ->condition($entity_type->getKey('label'), $string, 'STARTS_WITH')
      ->range(0, 10)
      ->sort($entity_type->getKey('label'))
      ->execute();

    $storage = $this->entityManager()->getStorage($entity_type_id);
    foreach ($storage->loadMultiple($ids) as $entity) {
      $matches[] = array('value' => $entity->uuid(), 'label' => String::checkPlain($entity->label()));
    }

    return new JsonResponse($matches);
  }
}
