<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\Derivative\FieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FormatterPluginManager;

/**
 * Provides block plugin definitions for custom menus.
 *
 * @see \Drupal\system\Plugin\Block\SystemMenuBlock
 */
class FieldFormatter extends DerivativeBase implements ContainerDerivativeInterface {

  /**
   * The manager for formatter plugins.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager.
   */
  protected $formatterManager;

  /**
   * Constructs new SystemMenuBlock.
   *
   * @param \Drupal\Core\Field\FormatterPluginManager $menu_storage
   *   The menu storage.
   */
  public function __construct(FormatterPluginManager $formatterManager) {
    $this->formatterManager = $formatterManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.field.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if (!isset($base_plugin_definition['field_type'])) {
      throw new \LogicException("Undefined field_type definition in plugin {$base_plugin_defintion['id']}.");
    }
    foreach ($this->formatterManager->getOptions($base_plugin_definition['field_type']) as $formatter => $label) {
      $this->derivatives[$formatter] = $base_plugin_definition;
      $this->derivatives[$formatter]['label'] = $label;
    }
    return $this->derivatives;
  }

}
