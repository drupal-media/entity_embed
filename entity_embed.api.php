<?php

/**
 * @file
 * Hooks provided by the Entity Embed module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the post_render_cache placeholder context for an embedded entity.
 *
 * @param array &$context
 *   The context array.
 * @param callable &$callback
 *   The post-render callback to be used.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity being rendered.
 */
function hook_entity_embed_context_alter(array &$context, &$callback, \Drupal\Core\Entity\EntityInterface $entity) {

}

/**
 * Alter the result of \Drupal\entity_embed\EntityEmbedDisplayBase::build().
 *
 * This hook is called after the content has been assembled in a structured
 * array and may be used for doing processing which requires that the complete
 * block content structure has been built.
 *
 * @param array &$build
 *   A renderable array of data, as returned from the build() implementation of
 *   the plugin that defined the display.
 * @param \Drupal\entity_embed\EntityEmbedDisplayInterface $display
 *   The entity embed display plugin instance.
 */
function hook_entity_embed_alter(array &$build, \Drupal\entity_embed\EntityEmbedDisplayInterface $display) {
  // Remove the contextual links on all entites that provide them.
  if (isset($build['#contextual_links'])) {
    unset($build['#contextual_links']);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
