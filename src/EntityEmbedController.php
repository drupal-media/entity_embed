<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedController
 */

namespace Drupal\entity_embed;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class EntityEmbedController extends ControllerBase {

  public function preview() {
    return new JsonResponse(array(
      'content' => "Entity preview here!",
    ));
  }

}
