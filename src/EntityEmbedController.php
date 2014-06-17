<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedController
 */

namespace Drupal\entity_embed;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for EntityEmbed module routes.
 */
class EntityEmbedController extends ControllerBase {
  use EntityHelperTrait;

  /**
   * Returns an Ajax response to generate preview of an entity.
   *
   * Expects the data attributes to be provided as GET parameters.
   */
  public function preview() {
    $context = \Drupal::request()->query->all();
    $entity_embed_settings = Json::decode($context['data-entity-embed-settings']);
    $entity_output = 'No preview available.';
    try {
      if ($entity = $this->loadEntity($context['data-entity-type'], $context['data-entity-id'])) {
        $entity_output = $this->renderEntityEmbedDisplayPlugin(
          $entity,
          $context['data-entity-embed-display'],
          $entity_embed_settings,
          $context
        );
      }
    }
    catch (\Exception $e) {
      watchdog_exception('entity_embed', $e);
    }
    return new JsonResponse(array(
      'content' => $entity_output,
    ));
  }

}
