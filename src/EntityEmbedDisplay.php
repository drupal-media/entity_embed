<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay.
 *
 * @todo Convert this to a plugin type and plugin class.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_embed\EntityEmbedDisplayInterface;
use Drupal\entity_reference\RecursiveRenderingException;

abstract class EntityEmbedDisplay implements EntityEmbedDisplayInterface {

  public $entity;
  public $context;

  public function __construct(EntityInterface $entity, array $context = array()) {
    $this->entity = $entity;
    $this->context = $context;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    return array();
  }

  public function access() {
    return $this->entity->access('view');
  }

  public function getContext($name = NULL) {
    if (isset($name)) {
      return isset($this->context[$name]) ? $this->context[$name] : NULL;
    }
    else {
      return $this->context;
    }
  }

  public function getSettings() {
    $settings = isset($this->context['entity-embed-settings']) ? $this->context['entity-embed-settings'] : array();
    $settings += static::defaultSettings();
    return $settings;
  }

  public function getSetting($name) {
    $settings = $this->getSettings();
    return isset($settings[$name]) ? $settings[$name] : NULL;
  }

  public static function postRender(array $element, array $context) {
    $callback = get_called_class() . '::postRender';
    $placeholder = drupal_render_cache_generate_placeholder($callback, array(), $context['token']);

    // Do not bother rendering the entity if the placeholder cannot be found.
    if (strpos($element['#markup'], $placeholder) === FALSE) {
      return $element;
    }

    $entity_output = '';
    try {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        throw new RecursiveRenderingException(format_string('Recursive rendering detected when rendering entity @entity_type(@entity_id). Aborting rendering.', array('@entity_type' => $this->entity->getEntityTypeId(), '@entity_id' => $this->entity->id())));
      }

      if ($entity = entity_load($context['entity-type'], $context['entity-id'])) {
        $instance = new static($entity, $context);
        if ($instance->access()) {
          $build = $instance->viewEntity();
          // Allow modules to alter the rendered embedded entity.
          \Drupal::moduleHandler()->alter('entity_embed', $build, $entity, $context);
          $entity_output = drupal_render($build);
        }
      }

      $depth--;
    }
    catch (\Exception $e) {
      watchdog_exception('entity_embed', $e);
    }

    $element['#markup'] = str_replace($placeholder, $entity_output, $element['#markup']);
    return $element;
  }

  abstract public function viewEntity();
}
