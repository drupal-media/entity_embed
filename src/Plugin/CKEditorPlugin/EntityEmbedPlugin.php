<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\CKEditorPlugin\EntityEmbedPlugin
 */

namespace Drupal\entity_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "entityembed" plugin.
 *
 * @CKEditorPlugin(
 *   id = "entityembed",
 *   label = @Translation("Entity Embed"),
 *   module = "entity_embed"
 * )
 */
class EntityEmbedPlugin extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return array(
      'EntityEmbed' => array(
        'label' => t('Entity Embed'),
        'image' => drupal_get_path('module', 'entity_embed') . '/js/plugins/entityembed/entity.png',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'entity_embed') . '/js/plugins/entityembed/plugin.js';
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
      'drupalEntity_dialogTitleAdd' => t('Embed an entity'),
      'drupalEntity_dialogTitleEdit' => t('Edit an embedded entity'),
    );
  }

}
