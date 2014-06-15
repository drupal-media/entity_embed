<?php

/**
 * @file
 * Contains \Drupal\entity_embed\RecursiveRenderingException.
 */

namespace Drupal\entity_embed;

/**
 * Exception thrown when the embed entity post_render_cache callback goes into
 * a potentially infinite loop.
 */
class RecursiveRenderingException extends \Exception {}
