<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedController
 */

namespace Drupal\entity_embed;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for EntityEmbed module routes.
 */
class EntityEmbedController extends ControllerBase {

  /**
   * Returns an Ajax response to generate preview of an entity.
   *
   * Expects the data attributes to be provided as GET parameters.
   */
  public function preview() {
    return new JsonResponse(array(
      'content' => "Entity preview here!",
    ));
  }

}
