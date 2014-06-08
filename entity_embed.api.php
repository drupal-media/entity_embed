<?php

/**
 * @file
 * Hooks provided by the Entity Embed module.
 */

/**
 * @addtogroup hooks
 * @{
 */

// @todo Document
function hook_entity_embed_display_plugins_alter(&$info) {

}

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
 * Act on an entity before it is about to be rendered in an embed.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity object.
 * @param array $context
 *   The context array.
 */
function hook_entity_preembed(\Drupal\Core\Entity\EntityInterface $entity, array $context) {
  if (isset($context['overrides']) && is_array($context['overrides'])) {
    foreach ($context['overrides'] as $key => $value) {
      $entity->key = $value;
    }
  }
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
