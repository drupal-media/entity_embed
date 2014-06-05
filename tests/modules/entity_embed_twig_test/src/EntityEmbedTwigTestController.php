<?php

/**
 * @file
 * Contains \Drupal\entity_embed_twig_test\EntityEmbedTwigTestController.
 */

namespace Drupal\entity_embed_twig_test;

/**
 * Controller routines for Twig theme test routes.
 */
class EntityEmbedTwigTestController {

  /**
   * Menu callback for testing PHP variables in a Twig template.
   */
  public function render() {
    return array('#theme' => 'entity_embed_twig_test');
  }
}
