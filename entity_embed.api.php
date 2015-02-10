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
 * Alter the display plugin definitions.
 *
 * @param array &$info
 *   An associative array containing the plugin definitions keyed by plugin ID.
 */
function hook_entity_embed_display_plugins_alter(array &$info) {

}

/**
 * Alter the display plugin definitions for a given context.
 *
 * Usually used to remove certain display plugins for specific entities.
 *
 * @param array &$definitions
 *   Remove options from this list if they should not be available for the given
 *   context.
 * @param array $contexts
 *   The provided context, typically an entity.
 */
function hook_entity_embed_display_plugins_for_context_alter(array &$definitions, array $contexts) {
  // Do nothing if no entity is provided.
  if (!isset($contexts['entity'])) {
    return;
  }
  $entity = $contexts['entity'];

  // For video and audio files, limit the available options to the media player.
  if ($entity instanceof \Drupal\file\FileInterface && in_array($entity->bundle(), ['audio', 'video'])) {
    $definitions = array_intersect_key($definitions, array_flip(['file:jwplayer_formatter']));
  }

  // For images, use the image formatter.
  if ($entity instanceof \Drupal\file\FileInterface && in_array($entity->bundle(), ['image'])) {
    $definitions = array_intersect_key($definitions, array_flip(['image:image']));
  }

  // For nodes, use the default option.
  if ($entity instanceof \Drupal\node\NodeInterface) {
    $definitions = array_intersect_key($definitions, array_flip(['default']));
  }
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
function hook_entity_embed_context_alter(array &$context, callable &$callback, \Drupal\Core\Entity\EntityInterface $entity) {

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
function hook_entity_embed_alter(array &$build, \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface $display) {
  // Remove the contextual links on all entites that provide them.
  if (isset($build['#contextual_links'])) {
    unset($build['#contextual_links']);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
