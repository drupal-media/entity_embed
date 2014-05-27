<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDefaultDisplay.
 */

namespace Drupal\entity_embed;

use Drupal\entity_embed\EntityEmbedDisplay;

class EntityEmbedDefaultDisplay extends EntityEmbedDisplay {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'view-mode' => 'embed',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewEntity() {
    // Clone the entity since we're going to set some additional properties we
    // don't want kept around afterwards.
    $entity = clone $this->entity;
    $entity->entity_embed_context = $this->getContext();

    // Build the rendered entity.
    $build = entity_view($this->entity, $this->getSetting('view-mode'), $this->getContext('langcode'));

    // Hide entity links by default.
    // @todo Make this configurable via data attribute?
    if (isset($build['links'])) {
      $build['links']['#access'] = FALSE;
    }

    return $build;
  }
}
