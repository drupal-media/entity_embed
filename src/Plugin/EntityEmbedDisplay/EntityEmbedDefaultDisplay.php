<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbedDisplay\EntityEmbedDefaultDisplay.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbedDisplay;

use Drupal\entity_embed\EntityEmbedDisplayBase;

/**
 * Default embed display, which renders the entity using entity_view().
 *
 * @EntityEmbedDisplay(
 *   id = "default",
 *   label = @Translation("Default"),
 *   types = {"entity"}
 * )
 */
class EntityEmbedDefaultDisplay extends EntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'view-mode' => 'embed',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Clone the entity since we're going to set some additional properties we
    // don't want kept around afterwards.
    $entity = clone $this->entity;
    $entity->entity_embed_context = $this->getContext();

    // Build the rendered entity.
    $build = entity_view($this->entity, $this->getConfigurationValue('view-mode'), $this->getContextValue('langcode'));

    // Hide entity links by default.
    // @todo Make this configurable via data attribute?
    if (isset($build['links'])) {
      $build['links']['#access'] = FALSE;
    }

    return $build;
  }
}
