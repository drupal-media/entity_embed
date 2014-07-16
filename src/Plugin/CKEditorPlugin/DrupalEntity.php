<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\CKEditorPlugin\EntityEmbedPlugin
 */

namespace Drupal\entity_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

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
   * {@inheritdoc}
   */
  public function getButtons() {
    return array(
      'DrupalEntity' => array(
        'label' => t('Entity'),
        'image' => drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentity/entity.png',
      ),
    );
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
    return array(
      'EntityEmbed_dialogTitleAdd' => t('Insert entity'),
      'EntityEmbed_dialogTitleEdit' => t('Edit entity'),
    );
  }

}
