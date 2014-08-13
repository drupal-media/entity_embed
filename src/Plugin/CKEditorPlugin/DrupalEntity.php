<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\CKEditorPlugin\DrupalEntity.
 */

namespace Drupal\entity_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;
use Drupal\entity_embed\Entity\EmbedButton;

/**
 * Defines the "drupalentity" plugin.
 *
 * @CKEditorPlugin(
 *   id = "drupalentity",
 *   label = @Translation("Entity"),
 *   module = "entity_embed"
 * )
 */
class DrupalEntity extends CKEditorPluginBase {

  /**
   * Constructs a Drupal\entity_embed\Plugin\CKEditorPlugin\DrupalEntity object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->embed_buttons = \Drupal::entityQuery('embed_button')->execute();
  }


  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $buttons = array();

    foreach ($this->embed_buttons as $embed_button) {
      $button = EmbedButton::load($embed_button);
      $buttons[$button->label()] = array(
        'label' => $button->getButtonLabel(),
        'image' => drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentity/entity.png',
      );
    }

    return $buttons;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentity/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return array(
      'core/drupal.ajax',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    $buttons = array();

    foreach ($this->embed_buttons as $embed_button) {
      $button = EmbedButton::load($embed_button);
      $buttons[$button->label()] = array(
        'id' => $button->id(),
        'name' => $button->label(),
        'label' => $button->getButtonLabel(),
        'entity_type' => $button->getEntityTypeMachineName(),
        'image' => $button->getButtonImage(),
      );
    }

    return array(
      'DrupalEntity_dialogTitleAdd' => t('Insert entity'),
      'DrupalEntity_dialogTitleEdit' => t('Edit entity'),
      'DrupalEntity_buttons' => $buttons,
    );
  }

}
