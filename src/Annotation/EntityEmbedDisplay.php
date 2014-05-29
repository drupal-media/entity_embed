<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Annotation\EntityEmbedDisplay.
 */

namespace Drupal\entity_embed\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an entity embed display annotation object.
 *
 * @ingroup entity_embed_api
 *
 * @Annotation
 */
class EntityEmbedDisplay extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the display plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label = '';

  /**
   * The entity types the display can apply to.
   *
   * @todo Replace with \Drupal\Core\Plugin\Context\Context.
   *
   * @var array
   */
  public $types = array();

}
