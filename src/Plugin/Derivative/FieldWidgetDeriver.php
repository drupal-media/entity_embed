<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\Derivative\FieldWidgetDeriver.
 */

namespace Drupal\entity_embed\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\WidgetPluginManager;

/**
 * Provides entity embed widget plugin definitions for field formatters.
 *
 * @see \Drupal\entity_embed\FieldFormatterEntityEmbedWidgetBase
 */
class FieldWidgetDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The manager for widget plugins.
   *
   * @var \Drupal\Core\Field\WidgetPluginManager.
   */
  protected $widgetManager;

  /**
   * Constructs new FieldFormatterEntityEmbedDisplayBase.
   *
   * @param \Drupal\Core\Field\WidgetPluginManager $widget_manager
   *   The field wkidget plugin manager.
   */
  public function __construct(WidgetPluginManager $widget_manager) {
    $this->widgetManager = $widget_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.field.widget')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \LogicException
   *   Throws an exception if field type is not defined in the annotation of the
   *   display plugin.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // The field type must be defined in the annotation of the display plugin.
    if (!isset($base_plugin_definition['field_type'])) {
      throw new \LogicException("Undefined field_type definition in plugin {$base_plugin_definition['id']}.");
    }
    foreach ($this->formatterManager->getOptions($base_plugin_definition['field_type']) as $formatter => $label) {
      $this->derivatives[$formatter] = $base_plugin_definition;
      $this->derivatives[$formatter]['label'] = $label;
    }
    return $this->derivatives;
  }

}
