<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Annotation\EntityEmbedWidget.
 */

namespace Drupal\entity_embed\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an entity embed widget annotation object.
 *
 * @ingroup entity_embed_api
 *
 * @Annotation
 */
class EntityEmbedWidget extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the widget plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label = '';

  /**
   * The entity types the widget can apply to.
   *
   * To make the widget plugin valid for all entity types, set this value to
   * FALSE.
   *
   * @var bool|array
   */
  public $entity_types = FALSE;

}
